<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportDashboardController extends Controller
{
    /**
     * Display the reports dashboard.
     */
    public function index(): View
    {
        // Get comprehensive dashboard data
        $dashboardData = $this->getDashboardData();
        
        return view('reports.dashboard', compact('dashboardData'));
    }
    
    /**
     * Get comprehensive dashboard data.
     */
    private function getDashboardData(): array
    {
        // Student Statistics
        $studentStats = [
            'total_students' => Student::count(),
            'active_students' => Student::where('status', 'active')->count(),
            'new_students_this_month' => Student::where('created_at', '>=', now()->subMonth())->count(),
            'students_by_grade' => Student::join('classes', 'students.class_id', '=', 'classes.id')
                                          ->select('classes.grade_level', DB::raw('count(*) as count'))
                                          ->groupBy('classes.grade_level')
                                          ->orderBy('classes.grade_level')
                                          ->pluck('count', 'grade_level')
                                          ->toArray(),
        ];
        
        // Attendance Statistics
        $attendanceStats = [
            'total_attendance_records' => Attendance::count(),
            'present_today' => Attendance::whereDate('created_at', today())
                                        ->where('status', 'present')
                                        ->count(),
            'absent_today' => Attendance::whereDate('created_at', today())
                                       ->where('status', 'absent')
                                       ->count(),
            'late_today' => Attendance::whereDate('created_at', today())
                                      ->where('status', 'late')
                                      ->count(),
            'attendance_rate_this_month' => $this->getAttendanceRateThisMonth(),
            'monthly_attendance_trend' => $this->getMonthlyAttendanceTrend(),
        ];
        
        // HR Statistics
        $hrStats = [
            'total_employees' => User::whereIn('role_id', [3, 4, 8, 9])->count(), // Teacher, Admin, HR Manager, Librarian
            'active_employees' => User::whereIn('role_id', [3, 4, 8, 9])
                                     ->where('status', 'active')
                                     ->count(),
            'leave_requests_this_month' => LeaveRequest::whereMonth('created_at', now()->month)
                                                 ->whereYear('created_at', now()->year)
                                                 ->count(),
            'pending_leave_requests' => LeaveRequest::where('status', 'pending')->count(),
            'payroll_processed_this_month' => Payroll::whereMonth('created_at', now()->month)
                                                  ->whereYear('created_at', now()->year)
                                                  ->where('status', 'finalized')
                                                  ->count(),
            'total_payroll_amount' => Payroll::where('status', 'finalized')
                                           ->sum('net_salary'),
        ];
        
        // Library Statistics
        $libraryStats = [
            'total_books' => Book::count(),
            'available_books' => Book::available()->count(),
            'books_issued' => BookLoan::whereNull('return_date')->count(),
            'overdue_books' => BookLoan::whereNull('return_date')
                                   ->where('due_date', '<', now())
                                   ->count(),
            'total_loans' => BookLoan::count(),
            'books_by_category' => Book::select('category', DB::raw('count(*) as count'))
                                     ->groupBy('category')
                                     ->orderBy('count', 'desc')
                                     ->pluck('count', 'category')
                                     ->toArray(),
        ];
        
        // System Statistics
        $systemStats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_classes' => DB::table('classes')->count(),
            'total_attendance_records' => Attendance::count(),
            'system_uptime' => $this->getSystemUptime(),
            'database_size' => $this->getDatabaseSize(),
        ];
        
        // Recent Activities
        $recentActivities = [
            'recent_students' => Student::with('user')
                                      ->orderBy('created_at', 'desc')
                                      ->limit(5)
                                      ->get(),
            'recent_attendance' => Attendance::with('user', 'class')
                                          ->orderBy('created_at', 'desc')
                                          ->limit(5)
                                          ->get(),
            'recent_leave_requests' => LeaveRequest::with('user')
                                            ->orderBy('created_at', 'desc')
                                            ->limit(5)
                                            ->get(),
            'recent_book_loans' => BookLoan::with('book', 'user')
                                        ->orderBy('created_at', 'desc')
                                        ->limit(5)
                                        ->get(),
        ];
        
        return [
            'student_stats' => $studentStats,
            'attendance_stats' => $attendanceStats,
            'hr_stats' => $hrStats,
            'library_stats' => $libraryStats,
            'system_stats' => $systemStats,
            'recent_activities' => $recentActivities,
            'charts_data' => [
                'student_enrollment' => $this->getStudentEnrollmentData(),
                'attendance_trends' => $this->getAttendanceTrendsData(),
                'hr_metrics' => $this->getHrMetricsData(),
                'library_usage' => $this->getLibraryUsageData(),
            ],
        ];
    }
    
    /**
     * Get attendance rate for current month.
     */
    private function getAttendanceRateThisMonth(): float
    {
        $totalRecords = Attendance::whereMonth('created_at', now()->month)
                                 ->whereYear('created_at', now()->year)
                                 ->count();
        
        if ($totalRecords === 0) {
            return 0;
        }
        
        $presentRecords = Attendance::whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year)
                                   ->where('status', 'present')
                                   ->count();
        
        return round(($presentRecords / $totalRecords) * 100, 1);
    }
    
    /**
     * Get monthly attendance trend for last 6 months.
     */
    private function getMonthlyAttendanceTrend(): array
    {
        $trend = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $totalRecords = Attendance::whereMonth('created_at', $month->month)
                                     ->whereYear('created_at', $month->year)
                                     ->count();
            
            $presentRecords = Attendance::whereMonth('created_at', $month->month)
                                       ->whereYear('created_at', $month->year)
                                       ->where('status', 'present')
                                       ->count();
            
            $rate = $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 1) : 0;
            
            $trend[] = [
                'month' => $month->format('M Y'),
                'rate' => $rate,
                'total' => $totalRecords,
                'present' => $presentRecords,
            ];
        }
        
        return $trend;
    }
    
    /**
     * Get student enrollment data for charts.
     */
    private function getStudentEnrollmentData(): array
    {
        $data = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = Student::whereMonth('created_at', $month->month)
                             ->whereYear('created_at', $month->year)
                             ->count();
            
            $data[] = [
                'month' => $month->format('M'),
                'count' => $count,
            ];
        }
        
        return $data;
    }
    
    /**
     * Get attendance trends data for charts.
     */
    private function getAttendanceTrendsData(): array
    {
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $present = Attendance::whereMonth('created_at', $month->month)
                                 ->whereYear('created_at', $month->year)
                                 ->where('status', 'present')
                                 ->count();
            
            $absent = Attendance::whereMonth('created_at', $month->month)
                                 ->whereYear('created_at', $month->year)
                                 ->where('status', 'absent')
                                 ->count();
            
            $late = Attendance::whereMonth('created_at', $month->month)
                               ->whereYear('created_at', $month->year)
                               ->where('status', 'late')
                               ->count();
            
            $data[] = [
                'month' => $month->format('M'),
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
            ];
        }
        
        return $data;
    }
    
    /**
     * Get HR metrics data for charts.
     */
    private function getHrMetricsData(): array
    {
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $leaveRequests = LeaveRequest::whereMonth('created_at', $month->month)
                                         ->whereYear('created_at', $month->year)
                                         ->count();
            
            $approvedLeaves = LeaveRequest::whereMonth('created_at', $month->month)
                                         ->whereYear('created_at', $month->year)
                                         ->where('status', 'approved')
                                         ->count();
            
            $payrollAmount = Payroll::whereMonth('created_at', $month->month)
                                  ->whereYear('created_at', $month->year)
                                  ->where('status', 'finalized')
                                  ->sum('net_salary');
            
            $data[] = [
                'month' => $month->format('M'),
                'leave_requests' => $leaveRequests,
                'approved_leaves' => $approvedLeaves,
                'payroll_amount' => $payrollAmount,
            ];
        }
        
        return $data;
    }
    
    /**
     * Get library usage data for charts.
     */
    private function getLibraryUsageData(): array
    {
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $issuedBooks = BookLoan::whereMonth('created_at', $month->month)
                                  ->whereYear('created_at', $month->year)
                                  ->count();
            
            $returnedBooks = BookLoan::whereMonth('return_date', $month->month)
                                    ->whereYear('return_date', $month->year)
                                    ->count();
            
            $overdueBooks = BookLoan::whereMonth('created_at', $month->month)
                                   ->whereYear('created_at', $month->year)
                                   ->where('due_date', '<', now())
                                   ->whereNull('return_date')
                                   ->count();
            
            $data[] = [
                'month' => $month->format('M'),
                'issued' => $issuedBooks,
                'returned' => $returnedBooks,
                'overdue' => $overdueBooks,
            ];
        }
        
        return $data;
    }
    
    /**
     * Get system uptime (placeholder).
     */
    private function getSystemUptime(): string
    {
        // This would typically come from a monitoring system
        // For now, return a placeholder
        return '99.9%';
    }
    
    /**
     * Get database size (placeholder).
     */
    private function getDatabaseSize(): string
    {
        // This would typically calculate actual database size
        // For now, return a placeholder
        return '2.5 MB';
    }
    
    /**
     * Refresh dashboard data.
     */
    public function refresh(): JsonResponse
    {
        $dashboardData = $this->getDashboardData();
        
        return response()->json([
            'success' => true,
            'message' => 'Reports dashboard data refreshed successfully',
            'data' => $dashboardData
        ]);
    }
    
    /**
     * Export dashboard data.
     */
    public function export(Request $request): JsonResponse
    {
        $type = $request->get('type', 'summary');
        
        switch ($type) {
            case 'students':
                $data = $this->exportStudentData();
                break;
            case 'attendance':
                $data = $this->exportAttendanceData();
                break;
            case 'hr':
                $data = $this->exportHrData();
                break;
            case 'library':
                $data = $this->exportLibraryData();
                break;
            default:
                $data = $this->exportSummaryData();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Report exported successfully',
            'data' => $data
        ]);
    }
    
    /**
     * Export summary data.
     */
    private function exportSummaryData(): array
    {
        $dashboardData = $this->getDashboardData();
        
        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'summary' => [
                'total_students' => $dashboardData['student_stats']['total_students'],
                'total_employees' => $dashboardData['hr_stats']['total_employees'],
                'total_books' => $dashboardData['library_stats']['total_books'],
                'attendance_rate' => $dashboardData['attendance_stats']['attendance_rate_this_month'],
                'system_uptime' => $dashboardData['system_stats']['system_uptime'],
            ]
        ];
    }
    
    /**
     * Export student data.
     */
    private function exportStudentData(): array
    {
        return Student::with('user')
                    ->get()
                    ->map(function ($student) {
                        return [
                            'name' => $student->user->name,
                            'email' => $student->user->email,
                            'roll_number' => $student->roll_number,
                            'grade' => $student->class->grade_level ?? 'N/A',
                            'section' => $student->class->section ?? 'N/A',
                            'status' => $student->status,
                            'enrollment_date' => $student->created_at->format('Y-m-d'),
                        ];
                    })
                    ->toArray();
    }
    
    /**
     * Export attendance data.
     */
    private function exportAttendanceData(): array
    {
        return Attendance::with(['user', 'class'])
                       ->whereMonth('created_at', now()->month)
                       ->whereYear('created_at', now()->year)
                       ->get()
                       ->map(function ($attendance) {
                           return [
                               'student_name' => $attendance->user->name,
                               'class' => $attendance->class->grade_level . '-' . $attendance->class->section,
                               'date' => $attendance->created_at->format('Y-m-d'),
                               'status' => $attendance->status,
                           ];
                       })
                       ->toArray();
    }
    
    /**
     * Export HR data.
     */
    private function exportHrData(): array
    {
        return [
            'employees' => User::whereIn('role_id', [3, 4, 8, 9])
                           ->get()
                           ->map(function ($user) {
                               return [
                                   'name' => $user->name,
                                   'email' => $user->email,
                                   'role' => ucfirst($user->role),
                                   'status' => $user->status,
                               ];
                           })
                           ->toArray(),
            'leave_requests' => LeaveRequest::with('user')
                                     ->whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)
                                     ->get()
                                     ->map(function ($leave) {
                                         return [
                                             'employee' => $leave->user->name,
                                             'type' => $leave->leave_type,
                                             'start_date' => $leave->start_date,
                                             'end_date' => $leave->end_date,
                                             'status' => $leave->status,
                                         ];
                                     })
                                     ->toArray(),
        ];
    }
    
    /**
     * Export library data.
     */
    private function exportLibraryData(): array
    {
        return [
            'books' => Book::all()
                       ->map(function ($book) {
                           return [
                               'title' => $book->title,
                               'author' => $book->author,
                               'isbn' => $book->isbn,
                               'category' => $book->category,
                               'total_copies' => $book->total_copies,
                               'status' => $book->status,
                           ];
                       })
                       ->toArray(),
            'loans' => BookLoan::with(['book', 'user'])
                          ->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year)
                          ->get()
                          ->map(function ($loan) {
                              return [
                                  'book_title' => $loan->book->title,
                                  'borrower' => $loan->user->name,
                                  'issue_date' => $loan->issue_date,
                                  'due_date' => $loan->due_date,
                                  'return_date' => $loan->return_date,
                                  'status' => $loan->status,
                              ];
                          })
                          ->toArray(),
        ];
    }
}
