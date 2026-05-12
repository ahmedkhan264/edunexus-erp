<?php

namespace App\Services;

use App\Models\TeacherAttendance;
use App\Models\PayrollDeduction;
use Carbon\Carbon;

class PayrollDeductionService
{
    /**
     * Calculate and create payroll deductions based on attendance.
     */
    public function calculateDeductions(TeacherAttendance $attendance): array
    {
        $deductions = [];
        
        // Calculate late arrival deduction
        if ($attendance->status === 'late' && $attendance->late_minutes > 0) {
            $lateDeduction = $this->calculateLateArrivalDeduction($attendance);
            if ($lateDeduction > 0) {
                $deductions[] = $this->createDeduction(
                    $attendance,
                    'late_arrival',
                    $lateDeduction,
                    $attendance->late_minutes,
                    "Late arrival by {$attendance->late_minutes} minutes"
                );
            }
        }
        
        // Calculate absenteeism deduction
        if ($attendance->status === 'absent') {
            $absentDeduction = $this->calculateAbsenteeismDeduction($attendance);
            if ($absentDeduction > 0) {
                $deductions[] = $this->createDeduction(
                    $attendance,
                    'absenteeism',
                    $absentDeduction,
                    0,
                    'Absent from duty without authorization'
                );
            }
        }
        
        // Calculate half day deduction
        if ($attendance->status === 'half_day') {
            $halfDayDeduction = $this->calculateHalfDayDeduction($attendance);
            if ($halfDayDeduction > 0) {
                $deductions[] = $this->createDeduction(
                    $attendance,
                    'half_day',
                    $halfDayDeduction,
                    0,
                    'Half day attendance'
                );
            }
        }
        
        // Calculate early departure deduction
        if ($attendance->check_out_time && $this->isEarlyDeparture($attendance)) {
            $earlyDeduction = $this->calculateEarlyDepartureDeduction($attendance);
            if ($earlyDeduction > 0) {
                $deductions[] = $this->createDeduction(
                    $attendance,
                    'early_departure',
                    $earlyDeduction,
                    0,
                    'Early departure from duty'
                );
            }
        }
        
        return $deductions;
    }
    
    /**
     * Calculate late arrival deduction amount.
     */
    private function calculateLateArrivalDeduction(TeacherAttendance $attendance): float
    {
        $lateMinutes = $attendance->late_minutes;
        
        // Get deduction rates from config or use defaults
        $perMinuteRate = config('payroll.late_arrival_per_minute', 5.00);
        $maxLateDeduction = config('payroll.max_late_arrival_deduction', 100.00);
        
        $deduction = $lateMinutes * $perMinuteRate;
        
        return min($deduction, $maxLateDeduction);
    }
    
    /**
     * Calculate absenteeism deduction amount.
     */
    private function calculateAbsenteeismDeduction(TeacherAttendance $attendance): float
    {
        // Daily salary rate based on monthly salary
        $dailyRate = $this->getDailyRate($attendance->teacher_id);
        $absenteeismRate = config('payroll.absenteeism_rate', 1.0); // 100% of daily rate
        
        return $dailyRate * $absenteeismRate;
    }
    
    /**
     * Calculate half day deduction amount.
     */
    private function calculateHalfDayDeduction(TeacherAttendance $attendance): float
    {
        $dailyRate = $this->getDailyRate($attendance->teacher_id);
        $halfDayRate = config('payroll.half_day_rate', 0.5); // 50% of daily rate
        
        return $dailyRate * $halfDayRate;
    }
    
    /**
     * Calculate early departure deduction amount.
     */
    private function calculateEarlyDepartureDeduction(TeacherAttendance $attendance): float
    {
        $earlyMinutes = $this->getEarlyDepartureMinutes($attendance);
        
        $perMinuteRate = config('payroll.early_departure_per_minute', 3.00);
        $maxEarlyDeduction = config('payroll.max_early_departure_deduction', 80.00);
        
        $deduction = $earlyMinutes * $perMinuteRate;
        
        return min($deduction, $maxEarlyDeduction);
    }
    
    /**
     * Create a payroll deduction record.
     */
    private function createDeduction(
        TeacherAttendance $attendance,
        string $type,
        float $amount,
        int $lateMinutes = 0,
        string $reason = ''
    ): PayrollDeduction {
        // Check if deduction already exists for this attendance and type
        $existing = PayrollDeduction::where('teacher_attendance_id', $attendance->id)
            ->where('deduction_type', $type)
            ->first();
        
        if ($existing) {
            return $existing;
        }
        
        return PayrollDeduction::create([
            'teacher_attendance_id' => $attendance->id,
            'deduction_type' => $type,
            'deduction_amount' => $amount,
            'late_minutes' => $lateMinutes,
            'reason' => $reason,
            'status' => 'pending'
        ]);
    }
    
    /**
     * Check if departure is early.
     */
    private function isEarlyDeparture(TeacherAttendance $attendance): bool
    {
        if (!$attendance->check_out_time) {
            return false;
        }
        
        $checkoutTime = Carbon::parse($attendance->date . ' ' . $attendance->check_out_time);
        $workdayEnd = Carbon::parse($attendance->date . ' ' . config('attendance.workday_end', '17:00:00'));
        
        return $checkoutTime < $workdayEnd;
    }
    
    /**
     * Get early departure minutes.
     */
    private function getEarlyDepartureMinutes(TeacherAttendance $attendance): int
    {
        if (!$attendance->check_out_time) {
            return 0;
        }
        
        $checkoutTime = Carbon::parse($attendance->date . ' ' . $attendance->check_out_time);
        $workdayEnd = Carbon::parse($attendance->date . ' ' . config('attendance.workday_end', '17:00:00'));
        
        if ($checkoutTime >= $workdayEnd) {
            return 0;
        }
        
        return $workdayEnd->diffInMinutes($checkoutTime);
    }
    
    /**
     * Get daily rate for a teacher.
     */
    private function getDailyRate(int $teacherId): float
    {
        // This would typically come from teacher's salary data
        // For now, using a default daily rate
        $monthlySalary = config('payroll.default_monthly_salary', 30000.00);
        $workingDaysPerMonth = config('payroll.working_days_per_month', 22);
        
        return $monthlySalary / $workingDaysPerMonth;
    }
    
    /**
     * Get total deductions for a teacher in a date range.
     */
    public function getTotalDeductions(int $teacherId, string $fromDate, string $toDate): array
    {
        $attendances = TeacherAttendance::forTeacher($teacherId)
            ->dateRange($fromDate, $toDate)
            ->with('payrollDeductions')
            ->get();
        
        $totalDeductions = 0;
        $pendingDeductions = 0;
        $approvedDeductions = 0;
        $rejectedDeductions = 0;
        
        foreach ($attendances as $attendance) {
            foreach ($attendance->payrollDeductions as $deduction) {
                $totalDeductions += $deduction->deduction_amount;
                
                switch ($deduction->status) {
                    case 'pending':
                        $pendingDeductions += $deduction->deduction_amount;
                        break;
                    case 'approved':
                        $approvedDeductions += $deduction->deduction_amount;
                        break;
                    case 'rejected':
                        $rejectedDeductions += $deduction->deduction_amount;
                        break;
                }
            }
        }
        
        return [
            'total' => $totalDeductions,
            'pending' => $pendingDeductions,
            'approved' => $approvedDeductions,
            'rejected' => $rejectedDeductions
        ];
    }
    
    /**
     * Get deduction summary by type for a date range.
     */
    public function getDeductionSummaryByType(string $fromDate, string $toDate): array
    {
        $deductions = PayrollDeduction::whereHas('teacherAttendance', function ($query) use ($fromDate, $toDate) {
            $query->dateRange($fromDate, $toDate);
        })->with('teacherAttendance.teacher')
        ->get()
        ->groupBy('deduction_type');
        
        $summary = [];
        
        foreach ($deductions as $type => $typeDeductions) {
            $summary[$type] = [
                'type' => $type,
                'type_display' => $typeDeductions->first()->getDeductionTypeDisplay(),
                'count' => $typeDeductions->count(),
                'total_amount' => $typeDeductions->sum('deduction_amount'),
                'pending_amount' => $typeDeductions->where('status', 'pending')->sum('deduction_amount'),
                'approved_amount' => $typeDeductions->where('status', 'approved')->sum('deduction_amount'),
                'rejected_amount' => $typeDeductions->where('status', 'rejected')->sum('deduction_amount')
            ];
        }
        
        return $summary;
    }
    
    /**
     * Auto-calculate deductions for all attendance records in a date range.
     */
    public function autoCalculateDeductions(string $fromDate, string $toDate): array
    {
        $attendances = TeacherAttendance::dateRange($fromDate, $toDate)
            ->whereIn('status', ['late', 'absent', 'half_day'])
            ->whereDoesntHave('payrollDeductions')
            ->get();
        
        $processedCount = 0;
        $totalDeductions = 0;
        
        foreach ($attendances as $attendance) {
            $deductions = $this->calculateDeductions($attendance);
            $processedCount++;
            $totalDeductions += collect($deductions)->sum('deduction_amount');
        }
        
        return [
            'processed_attendances' => $processedCount,
            'total_deductions' => $totalDeductions,
            'date_range' => [$fromDate, $toDate]
        ];
    }
    
    /**
     * Get pending deductions for approval.
     */
    public function getPendingDeductions(int $limit = 50)
    {
        return PayrollDeduction::pending()
            ->with(['teacherAttendance.teacher', 'teacherAttendance'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
