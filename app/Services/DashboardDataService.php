<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\FeeChallan;
use App\Models\FeePayment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardDataService
{
    /**
     * Get dashboard data with caching.
     */
    public static function getDashboardData(): array
    {
        return Cache::remember('principal_dashboard_data', 3600, function () {
            return [
                'kpi_cards' => self::getKpiCards(),
                'charts' => self::getChartData(),
                'recent_activities' => self::getRecentActivities(),
            ];
        });
    }
    
    /**
     * Clear dashboard cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('principal_dashboard_data');
    }
    
    /**
     * Get KPI cards data.
     */
    private static function getKpiCards(): array
    {
        // Total Students
        $totalStudents = Student::where('status', 'active')->count();
        
        // Total Teachers
        $totalTeachers = User::where('role_id', 3)->where('status', 'active')->count(); // Assuming role_id 3 is teacher
        
        // Total Classes
        $totalClasses = SchoolClass::count();
        
        // Today's Student Attendance %
        $todayStudentAttendance = self::getTodayStudentAttendancePercentage();
        
        // Today's Teacher Attendance %
        $todayTeacherAttendance = self::getTodayTeacherAttendancePercentage();
        
        // Monthly Fee Collection (vs target)
        $monthlyFeeCollection = self::getMonthlyFeeCollection();
        
        // Pending Tasks / Approvals count
        $pendingTasks = self::getPendingTasksCount();
        
        return [
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_classes' => $totalClasses,
            'today_student_attendance' => $todayStudentAttendance,
            'today_teacher_attendance' => $todayTeacherAttendance,
            'monthly_fee_collection' => $monthlyFeeCollection,
            'pending_tasks' => $pendingTasks,
        ];
    }
    
    /**
     * Get chart data.
     */
    private static function getChartData(): array
    {
        return [
            'student_attendance_trend' => self::getStudentAttendanceTrend(),
            'teacher_attendance_trend' => self::getTeacherAttendanceTrend(),
            'fee_collection_trend' => self::getFeeCollectionTrend(),
            'top_defaulters' => self::getTopDefaulters(),
        ];
    }
    
    /**
     * Get recent activities.
     */
    private static function getRecentActivities(): array
    {
        return [
            'attendance_correction_requests' => self::getAttendanceCorrectionRequests(),
            'pending_fee_challans' => self::getPendingFeeChallans(),
            'completed_tasks' => self::getCompletedTasks(),
        ];
    }
    
    /**
     * Get today's student attendance percentage.
     */
    private static function getTodayStudentAttendancePercentage(): float
    {
        $today = Carbon::today();
        
        $totalStudents = Student::where('status', 'active')->count();
        if ($totalStudents === 0) {
            return 0;
        }
        
        $presentStudents = Attendance::whereDate('created_at', $today)
            ->where('status', 'present')
            ->whereHas('user', function ($query) {
                $query->whereHas('student');
            })
            ->count();
        
        return ($presentStudents / $totalStudents) * 100;
    }
    
    /**
     * Get today's teacher attendance percentage.
     */
    private static function getTodayTeacherAttendancePercentage(): float
    {
        $today = Carbon::today();
        
        $totalTeachers = User::where('role_id', 3)->where('status', 'active')->count();
        if ($totalTeachers === 0) {
            return 0;
        }
        
        $presentTeachers = Attendance::whereDate('created_at', $today)
            ->where('status', 'present')
            ->whereHas('user', function ($query) {
                $query->where('role_id', 3);
            })
            ->count();
        
        return ($presentTeachers / $totalTeachers) * 100;
    }
    
    /**
     * Get monthly fee collection data.
     */
    private static function getMonthlyFeeCollection(): array
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Total collected this month
        $totalCollected = FeePayment::whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->sum('amount');
        
        // Target amount (you can set this based on your fee structure)
        $targetAmount = 1000000; // Example target, adjust as needed
        
        return [
            'collected' => $totalCollected,
            'target' => $targetAmount,
            'percentage' => $targetAmount > 0 ? ($totalCollected / $targetAmount) * 100 : 0,
        ];
    }
    
    /**
     * Get pending tasks count.
     */
    private static function getPendingTasksCount(): int
    {
        return Task::whereIn('status', ['pending', 'in_progress'])->count();
    }
    
    /**
     * Get student attendance trend for last 7 days.
     */
    private static function getStudentAttendanceTrend(): array
    {
        $trend = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            $totalStudents = Student::where('status', 'active')->count();
            if ($totalStudents === 0) {
                $percentage = 0;
            } else {
                $presentStudents = Attendance::whereDate('created_at', $date)
                    ->where('status', 'present')
                    ->whereHas('user', function ($query) {
                        $query->whereHas('student');
                    })
                    ->count();
                
                $percentage = ($presentStudents / $totalStudents) * 100;
            }
            
            $trend[] = [
                'date' => $date->format('M j'),
                'percentage' => round($percentage, 1),
            ];
        }
        
        return $trend;
    }
    
    /**
     * Get teacher attendance trend for last 7 days.
     */
    private static function getTeacherAttendanceTrend(): array
    {
        $trend = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            
            $totalTeachers = User::where('role_id', 3)->where('status', 'active')->count();
            if ($totalTeachers === 0) {
                $percentage = 0;
            } else {
                $presentTeachers = Attendance::whereDate('created_at', $date)
                    ->where('status', 'present')
                    ->whereHas('user', function ($query) {
                        $query->where('role_id', 3);
                    })
                    ->count();
                
                $percentage = ($presentTeachers / $totalTeachers) * 100;
            }
            
            $trend[] = [
                'date' => $date->format('M j'),
                'percentage' => round($percentage, 1),
            ];
        }
        
        return $trend;
    }
    
    /**
     * Get fee collection trend for last 6 months.
     */
    private static function getFeeCollectionTrend(): array
    {
        $trend = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;
            
            $collected = FeePayment::whereMonth('payment_date', $month)
                ->whereYear('payment_date', $year)
                ->sum('amount');
            
            $target = 1000000; // Example target per month
            
            $trend[] = [
                'month' => $date->format('M Y'),
                'collected' => $collected,
                'target' => $target,
            ];
        }
        
        return $trend;
    }
    
    /**
     * Get top 5 defaulters by outstanding amount.
     */
    private static function getTopDefaulters(): array
    {
        return DB::table('fee_challans as fc')
            ->join('students as s', 'fc.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->select(
                'u.name',
                's.roll_number',
                DB::raw('SUM(fc.amount - fc.paid_amount) as outstanding'),
                DB::raw('MIN(fc.due_date) as earliest_due_date')
            )
            ->where('fc.status', '!=', 'paid')
            ->where('fc.due_date', '<', Carbon::now())
            ->groupBy('s.id', 'u.name', 's.roll_number')
            ->orderBy('outstanding', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($defaulter) {
                return [
                    'name' => $defaulter->name,
                    'roll_number' => $defaulter->roll_number,
                    'outstanding' => $defaulter->outstanding,
                    'days_overdue' => Carbon::parse($defaulter->earliest_due_date)->diffInDays(Carbon::now()),
                ];
            })
            ->toArray();
    }
    
    /**
     * Get latest 5 attendance correction requests.
     */
    private static function getAttendanceCorrectionRequests(): array
    {
        // This is a placeholder - you'd need to create an AttendanceCorrectionRequest model
        // For now, returning mock data
        return [
            [
                'id' => 1,
                'student_name' => 'John Doe',
                'date' => '2024-01-15',
                'reason' => 'Medical leave',
                'status' => 'pending',
                'requested_at' => '2024-01-16 10:30:00',
            ],
            [
                'id' => 2,
                'student_name' => 'Jane Smith',
                'date' => '2024-01-14',
                'reason' => 'Family emergency',
                'status' => 'pending',
                'requested_at' => '2024-01-15 14:20:00',
            ],
        ];
    }
    
    /**
     * Get latest 5 pending fee challans.
     */
    private static function getPendingFeeChallans(): array
    {
        return FeeChallan::where('status', '!=', 'paid')
            ->with(['student.user', 'student.schoolClass'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($challan) {
                return [
                    'id' => $challan->id,
                    'student_name' => $challan->student->user->name,
                    'class' => $challan->student->schoolClass->grade_level . ' - ' . $challan->student->schoolClass->section,
                    'amount' => $challan->amount,
                    'due_date' => $challan->due_date->format('M j, Y'),
                    'days_overdue' => $challan->due_date->diffInDays(Carbon::now(), false),
                ];
            })
            ->toArray();
    }
    
    /**
     * Get latest 5 completed tasks.
     */
    private static function getCompletedTasks(): array
    {
        return Task::where('status', 'completed')
            ->with(['assignee', 'creator'])
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'title' => $task->title,
                    'assignee' => $task->assignee->name,
                    'completed_at' => $task->completed_at->format('M j, Y H:i'),
                ];
            })
            ->toArray();
    }
}
