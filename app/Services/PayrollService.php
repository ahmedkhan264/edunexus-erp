<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Calculate payroll data for an employee for a specific month and year.
     */
    public static function calculatePayroll(User $employee, int $month, int $year): array
    {
        // Get the first and last day of the month
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        // Calculate working days (excluding weekends)
        $workingDays = self::calculateWorkingDays($startDate, $endDate);
        
        // Get attendance data for the month
        $attendanceData = self::getAttendanceData($employee->id, $month, $year);
        
        // Get leave data for the month
        $leaveData = self::getLeaveData($employee->id, $month, $year);
        
        // Calculate attendance counts
        $presentDays = $attendanceData['present_days'];
        $absentDays = $attendanceData['absent_days'];
        $lateDays = $attendanceData['late_days'];
        $leaveDays = $leaveData['total_days'];
        
        // Get employee salary (mock data - would come from employee profile)
        $basicSalary = self::getEmployeeBasicSalary($employee);
        
        // Calculate allowances (mock data - would come from employee profile)
        $allowances = self::getEmployeeAllowances($employee);
        
        // Calculate deductions based on attendance
        $deductions = self::calculateDeductions($basicSalary, $workingDays, $absentDays, $lateDays);
        
        // Calculate net salary
        $netSalary = $basicSalary + $allowances - $deductions;
        
        return [
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'leave_days' => $leaveDays,
        ];
    }
    
    /**
     * Calculate working days for a month (excluding weekends).
     */
    private static function calculateWorkingDays(Carbon $startDate, Carbon $endDate): int
    {
        $workingDays = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            // Exclude weekends (Saturday = 6, Sunday = 0)
            if ($currentDate->dayOfWeek !== 0 && $currentDate->dayOfWeek !== 6) {
                $workingDays++;
            }
            $currentDate->addDay();
        }
        
        return $workingDays;
    }
    
    /**
     * Get attendance data for an employee for a specific month and year.
     */
    private static function getAttendanceData(int $employeeId, int $month, int $year): array
    {
        $attendance = Attendance::where('user_id', $employeeId)
                              ->whereMonth('created_at', $month)
                              ->whereYear('created_at', $year)
                              ->get();
        
        return [
            'present_days' => $attendance->where('status', 'present')->count(),
            'absent_days' => $attendance->where('status', 'absent')->count(),
            'late_days' => $attendance->where('status', 'late')->count(),
            'leave_days' => $attendance->where('status', 'leave')->count(),
        ];
    }
    
    /**
     * Get leave data for an employee for a specific month and year.
     */
    private static function getLeaveData(int $employeeId, int $month, int $year): array
    {
        $leaves = LeaveRequest::where('user_id', $employeeId)
                            ->where('status', 'approved')
                            ->whereYear('start_date', $year)
                            ->whereMonth('start_date', $month)
                            ->get();
        
        $totalDays = 0;
        
        foreach ($leaves as $leave) {
            $totalDays += $leave->days;
        }
        
        return [
            'total_days' => $totalDays,
            'leaves' => $leaves,
        ];
    }
    
    /**
     * Get employee basic salary (mock data).
     */
    private static function getEmployeeBasicSalary(User $employee): float
    {
        // Mock salary based on role - would come from employee profile in real implementation
        $salaryByRole = [
            2 => 80000, // Principal
            3 => 35000, // Teacher
            4 => 45000, // Admin
            5 => 0,     // Student (no salary)
            6 => 0,     // Parent (no salary)
            7 => 50000, // Accountant
            8 => 60000, // HR Manager
            9 => 40000, // Librarian
        ];
        
        return $salaryByRole[$employee->role_id] ?? 30000;
    }
    
    /**
     * Get employee allowances (mock data).
     */
    private static function getEmployeeAllowances(User $employee): float
    {
        // Mock allowances based on role - would come from employee profile in real implementation
        $allowancesByRole = [
            2 => 15000, // Principal
            3 => 5000,  // Teacher
            4 => 8000,  // Admin
            5 => 0,     // Student
            6 => 0,     // Parent
            7 => 10000, // Accountant
            8 => 12000, // HR Manager
            9 => 6000,  // Librarian
        ];
        
        return $allowancesByRole[$employee->role_id] ?? 5000;
    }
    
    /**
     * Calculate deductions based on attendance.
     */
    private static function calculateDeductions(float $basicSalary, int $workingDays, int $absentDays, int $lateDays): float
    {
        if ($workingDays === 0) {
            return 0;
        }
        
        // Calculate daily salary
        $dailySalary = $basicSalary / $workingDays;
        
        // Calculate absent deductions (full day deduction for each absent)
        $absentDeductions = $absentDays * $dailySalary;
        
        // Calculate late deductions (3 late days = 1 day deduction)
        $lateDeductionDays = floor($lateDays / 3);
        $lateDeductions = $lateDeductionDays * $dailySalary;
        
        return $absentDeductions + $lateDeductions;
    }
    
    /**
     * Generate salary slip data for an employee.
     */
    public static function generateSalarySlipData(Payroll $payroll): array
    {
        $employee = $payroll->user;
        
        return [
            'employee' => [
                'name' => $employee->name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'role' => self::getRoleName($employee->role_id),
                'joining_date' => $employee->created_at->format('d M Y'),
            ],
            'payroll' => [
                'month' => $payroll->getFormattedMonth(),
                'basic_salary' => $payroll->getFormattedBasicSalary(),
                'allowances' => $payroll->getFormattedAllowances(),
                'gross_salary' => $payroll->getFormattedGrossSalary(),
                'deductions' => $payroll->getFormattedDeductions(),
                'net_salary' => $payroll->getFormattedNetSalary(),
                'net_salary_words' => self::numberToWords($payroll->net_salary),
            ],
            'attendance' => $payroll->getAttendanceSummary(),
            'deduction_breakdown' => $payroll->getDeductionBreakdown(),
            'processed_by' => $payroll->processor->name ?? 'System',
            'finalized_by' => $payroll->finalizer->name ?? null,
            'finalized_at' => $payroll->finalized_at?->format('d M Y H:i'),
        ];
    }
    
    /**
     * Get role name by role ID.
     */
    private static function getRoleName(int $roleId): string
    {
        $roles = [
            1 => 'Super Admin',
            2 => 'Principal',
            3 => 'Teacher',
            4 => 'Admin',
            5 => 'Student',
            6 => 'Parent',
            7 => 'Accountant',
            8 => 'HR Manager',
            9 => 'Librarian',
        ];
        
        return $roles[$roleId] ?? 'Unknown';
    }
    
    /**
     * Convert number to words (simplified version).
     */
    private static function numberToWords(float $number): string
    {
        // This is a simplified implementation
        // In a real application, you would use a proper number-to-words library
        $formatted = number_format($number, 2);
        return ucwords(str_replace(['.', '-'], [' Point ', ' '], $formatted)) . ' Only';
    }
    
    /**
     * Validate payroll data before processing.
     */
    public static function validatePayrollData(array $payrollData): array
    {
        $errors = [];
        
        if ($payrollData['basic_salary'] <= 0) {
            $errors[] = 'Basic salary must be greater than 0';
        }
        
        if ($payrollData['allowances'] < 0) {
            $errors[] = 'Allowances cannot be negative';
        }
        
        if ($payrollData['deductions'] < 0) {
            $errors[] = 'Deductions cannot be negative';
        }
        
        if ($payrollData['net_salary'] < 0) {
            $errors[] = 'Net salary cannot be negative';
        }
        
        if ($payrollData['working_days'] <= 0) {
            $errors[] = 'Working days must be greater than 0';
        }
        
        if ($payrollData['present_days'] < 0 || $payrollData['present_days'] > $payrollData['working_days']) {
            $errors[] = 'Present days must be between 0 and working days';
        }
        
        if ($payrollData['absent_days'] < 0 || $payrollData['absent_days'] > $payrollData['working_days']) {
            $errors[] = 'Absent days must be between 0 and working days';
        }
        
        return $errors;
    }
}
