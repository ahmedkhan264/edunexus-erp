<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HrDashboardService
{
    /**
     * Get HR dashboard data with caching.
     */
    public static function getDashboardData(): array
    {
        return Cache::remember('hr_dashboard_data', 900, function () {
            return [
                'kpi_cards' => self::getKpiCards(),
                'attendance_trend' => self::getAttendanceTrend(),
                'recent_leave_requests' => self::getRecentLeaveRequests(),
                'payroll_alert' => self::getPayrollAlert(),
            ];
        });
    }
    
    /**
     * Get KPI cards data.
     */
    private static function getKpiCards(): array
    {
        // Get employee roles (excluding students and parents)
        $employeeRoles = [2, 3, 4, 5, 6, 7, 8, 9]; // Principal, Admin, Teacher, Student, Parent, Accountant, HR Manager, Librarian, Timetable Coordinator
        $employees = User::whereIn('role_id', $employeeRoles)->where('status', 'active');
        
        $totalEmployees = $employees->count();
        
        // Today's attendance
        $todayAttendance = Attendance::whereDate('created_at', Carbon::today())
            ->whereIn('user_id', $employees->pluck('id'))
            ->get();
            
        $presentToday = $todayAttendance->where('status', 'present')->count();
        $onLeaveToday = $todayAttendance->where('status', 'leave')->count();
        
        // Late teachers (role_id 3)
        $lateTeachers = Attendance::whereDate('created_at', Carbon::today())
            ->where('status', 'late')
            ->whereHas('user', function ($query) {
                $query->where('role_id', 3)->where('status', 'active');
            })
            ->count();
        
        // Pending payroll (mock data - would need Payroll model)
        $pendingPayroll = Payroll::where('status', 'draft')
            ->where('month', Carbon::now()->subMonth()->month)
            ->where('year', Carbon::now()->subMonth()->year)
            ->count();
        
        return [
            'total_employees' => $totalEmployees,
            'present_today' => $presentToday,
            'on_leave_today' => $onLeaveToday,
            'pending_payroll' => $pendingPayroll,
            'late_teachers' => $lateTeachers,
        ];
    }
    
    /**
     * Get monthly attendance trend for teachers and staff.
     */
    private static function getAttendanceTrend(): array
    {
        $trend = [];
        
        // Get last 6 months data
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('M Y');
            
            // Teacher attendance (role_id 3)
            $teacherAttendance = Attendance::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->whereHas('user', function ($query) {
                    $query->where('role_id', 3)->where('status', 'active');
                })
                ->get();
                
            $teacherPresent = $teacherAttendance->where('status', 'present')->count();
            $teacherTotal = $teacherAttendance->count();
            $teacherPercentage = $teacherTotal > 0 ? ($teacherPresent / $teacherTotal) * 100 : 0;
            
            // Staff attendance (other employee roles)
            $staffAttendance = Attendance::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->whereHas('user', function ($query) {
                    $query->whereIn('role_id', [2, 4, 5, 6, 7, 8, 9])->where('status', 'active');
                })
                ->get();
                
            $staffPresent = $staffAttendance->where('status', 'present')->count();
            $staffTotal = $staffAttendance->count();
            $staffPercentage = $staffTotal > 0 ? ($staffPresent / $staffTotal) * 100 : 0;
            
            $trend[] = [
                'month' => $month,
                'teacher_attendance' => round($teacherPercentage, 1),
                'staff_attendance' => round($staffPercentage, 1),
            ];
        }
        
        return $trend;
    }
    
    /**
     * Get recent leave requests pending approval.
     */
    private static function getRecentLeaveRequests(): array
    {
        // Mock data - would need LeaveRequest model
        $leaveRequests = [
            [
                'id' => 1,
                'employee_name' => 'John Smith',
                'employee_role' => 'Teacher',
                'leave_type' => 'Sick Leave',
                'start_date' => Carbon::parse('2026-05-03')->format('M j, Y'),
                'end_date' => Carbon::parse('2026-05-05')->format('M j, Y'),
                'days' => 3,
                'reason' => 'Medical appointment and recovery',
            ],
            [
                'id' => 2,
                'employee_name' => 'Sarah Johnson',
                'employee_role' => 'Accountant',
                'leave_type' => 'Casual Leave',
                'start_date' => Carbon::parse('2026-05-04')->format('M j, Y'),
                'end_date' => Carbon::parse('2026-05-04')->format('M j, Y'),
                'days' => 1,
                'reason' => 'Personal work',
            ],
            [
                'id' => 3,
                'employee_name' => 'Michael Brown',
                'employee_role' => 'Teacher',
                'leave_type' => 'Earned Leave',
                'start_date' => Carbon::parse('2026-05-06')->format('M j, Y'),
                'end_date' => Carbon::parse('2026-05-08')->format('M j, Y'),
                'days' => 3,
                'reason' => 'Family vacation',
            ],
        ];
        
        return $leaveRequests;
    }
    
    /**
     * Get upcoming payroll generation alert.
     */
    private static function getPayrollAlert(): array
    {
        $lastMonth = Carbon::now()->subMonth();
        $currentMonth = Carbon::now();
        
        // Check if payroll for last month is finalized
        $lastMonthPayroll = Payroll::where('month', $lastMonth->month)
            ->where('year', $lastMonth->year)
            ->where('status', 'finalized')
            ->count();
        
        $alert = [
            'message' => 'Payroll for ' . $lastMonth->format('F Y') . ' needs to be processed',
            'status' => 'pending', // pending, processing, completed
            'month' => $lastMonth->format('F Y'),
            'days_until_processing' => $currentMonth->diffInDays($currentMonth->endOfMonth()),
        ];
        
        if ($lastMonthPayroll > 0) {
            $alert['status'] = 'completed';
            $alert['message'] = 'Payroll for ' . $lastMonth->format('F Y') . ' has been processed';
        }
        
        return $alert;
    }
    
    /**
     * Clear HR dashboard cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('hr_dashboard_data');
    }
}
