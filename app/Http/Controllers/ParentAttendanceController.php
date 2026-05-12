<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class ParentAttendanceController extends Controller
{
    /**
     * Display the child's attendance.
     */
    public function show(Student $student, Request $request): View
    {
        $parent = auth()->user()->parentProfile;
        
        // Verify the child belongs to this parent
        if (!$parent->students()->where('students.id', $student->id)->exists()) {
            abort(403, 'You are not authorized to view this child\'s attendance.');
        }
        
        // Get month and year from request, default to current month
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        
        // Validate month and year
        if ($month < 1 || $month > 12 || $year < 2020 || $year > Carbon::now()->year + 1) {
            abort(400, 'Invalid month or year provided.');
        }
        
        // Create Carbon instance for the requested month
        $currentDate = Carbon::createFromDate($year, $month, 1);
        
        // Get attendance records for the month
        $attendanceRecords = Attendance::where('user_id', $student->user_id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('created_at')
            ->get()
            ->keyBy(function($record) {
                return Carbon::parse($record->created_at)->day;
            });
        
        // Build calendar data
        $calendarData = $this->buildCalendarData($currentDate, $attendanceRecords);
        
        // Calculate monthly statistics
        $monthlyStats = $this->calculateMonthlyStats($attendanceRecords, $currentDate);
        
        // Get navigation data
        $navigationData = $this->getNavigationData($currentDate);
        
        return view('parent.attendance', compact(
            'student',
            'calendarData',
            'monthlyStats',
            'navigationData',
            'currentDate'
        ));
    }
    
    /**
     * Build calendar data for the attendance view.
     */
    private function buildCalendarData(Carbon $currentDate, $attendanceRecords): array
    {
        $calendar = [];
        $daysInMonth = $currentDate->daysInMonth;
        $firstDayOfMonth = $currentDate->copy()->startOfMonth();
        $startingDayOfWeek = $firstDayOfMonth->dayOfWeek; // 0 = Sunday, 6 = Saturday
        
        // Add empty cells for days before month starts
        for ($i = 0; $i < $startingDayOfWeek; $i++) {
            $calendar[] = [
                'day' => null,
                'date' => null,
                'status' => 'empty',
                'is_today' => false,
                'is_weekend' => false,
                'is_holiday' => false
            ];
        }
        
        // Add days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentDate->copy()->day($day);
            $attendance = $attendanceRecords->get($day);
            
            $status = 'not_marked';
            if ($attendance) {
                $status = $attendance->status;
            } elseif ($date->isSunday()) {
                $status = 'holiday'; // Sunday as holiday
            } elseif ($this->isPublicHoliday($date)) {
                $status = 'holiday';
            }
            
            $calendar[] = [
                'day' => $day,
                'date' => $date,
                'status' => $status,
                'is_today' => $date->isToday(),
                'is_weekend' => $date->isWeekend(),
                'is_holiday' => $status === 'holiday',
                'attendance' => $attendance
            ];
        }
        
        return $calendar;
    }
    
    /**
     * Calculate monthly attendance statistics.
     */
    private function calculateMonthlyStats($attendanceRecords, Carbon $currentDate): array
    {
        $totalDays = $currentDate->daysInMonth;
        $workingDays = 0;
        $presentDays = 0;
        $absentDays = 0;
        $lateDays = 0;
        $holidayDays = 0;
        $notMarkedDays = 0;
        
        for ($day = 1; $day <= $totalDays; $day++) {
            $date = $currentDate->copy()->day($day);
            
            // Skip Sundays as holidays
            if ($date->isSunday()) {
                $holidayDays++;
                continue;
            }
            
            // Check if it's a public holiday
            if ($this->isPublicHoliday($date)) {
                $holidayDays++;
                continue;
            }
            
            $workingDays++;
            $attendance = $attendanceRecords->get($day);
            
            if ($attendance) {
                switch ($attendance->status) {
                    case 'present':
                        $presentDays++;
                        break;
                    case 'absent':
                        $absentDays++;
                        break;
                    case 'late':
                        $lateDays++;
                        break;
                }
            } else {
                $notMarkedDays++;
            }
        }
        
        $attendancePercentage = $workingDays > 0 ? ($presentDays / $workingDays) * 100 : 0;
        
        return [
            'total_days' => $totalDays,
            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'holiday_days' => $holidayDays,
            'not_marked_days' => $notMarkedDays,
            'attendance_percentage' => round($attendancePercentage, 1)
        ];
    }
    
    /**
     * Get navigation data for month/year navigation.
     */
    private function getNavigationData(Carbon $currentDate): array
    {
        $previousMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
        
        // Don't allow navigation to future months
        $nextMonthAllowed = $nextMonth->lte(Carbon::now());
        
        return [
            'current_month_name' => $currentDate->format('F'),
            'current_year' => $currentDate->year,
            'previous_month' => [
                'month' => $previousMonth->month,
                'year' => $previousMonth->year,
                'name' => $previousMonth->format('F Y')
            ],
            'next_month' => [
                'month' => $nextMonth->month,
                'year' => $nextMonth->year,
                'name' => $nextMonth->format('F Y'),
                'allowed' => $nextMonthAllowed
            ]
        ];
    }
    
    /**
     * Check if a date is a public holiday.
     * This is a simplified version - in production, you'd have a holidays table.
     */
    private function isPublicHoliday(Carbon $date): bool
    {
        // Add common holidays (you can expand this based on your region)
        $holidays = [
            // Fixed date holidays
            '01-01', // New Year's Day
            '03-23', // Pakistan Day
            '08-14', // Independence Day
            '12-25', // Christmas Day
            
            // You can add more holidays based on your school's calendar
        ];
        
        $dateString = $date->format('m-d');
        return in_array($dateString, $holidays);
    }
    
    /**
     * Get status display text.
     */
    private function getAttendanceStatusDisplay(string $status): string
    {
        return match($status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'holiday' => 'Holiday',
            'not_marked' => 'Not Marked',
            'empty' => '',
            default => 'Unknown'
        };
    }
    
    /**
     * Get status color for calendar display.
     */
    private function getAttendanceStatusColor(string $status): string
    {
        return match($status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'holiday' => 'secondary',
            'not_marked' => 'info',
            'empty' => 'light',
            default => 'secondary'
        };
    }
}
