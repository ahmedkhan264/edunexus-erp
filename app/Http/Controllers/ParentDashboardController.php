<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ExamResult;
use App\Models\FeeChallan;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Services\GradeCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class ParentDashboardController extends Controller
{
    /**
     * Display the parent dashboard.
     */
    public function index(): View
    {
        $parent = auth()->user()->parentProfile;
        
        // Get all linked children
        $children = $parent->students()->with(['user', 'schoolClass'])->get();
        
        // Default to first child if no selection
        $selectedChildId = request('child_id', $children->first()->id ?? null);
        $selectedChild = $children->find($selectedChildId);
        
        if (!$selectedChild) {
            return view('parent.dashboard', compact('children', 'selectedChild'));
        }
        
        // Get dashboard data for selected child
        $dashboardData = $this->getChildDashboardData($selectedChild);
        
        return view('parent.dashboard', compact('children', 'selectedChild', 'dashboardData'));
    }
    
    /**
     * Get dashboard data for a specific child via AJAX.
     */
    public function childData(Request $request): JsonResponse
    {
        $parent = auth()->user()->parentProfile;
        $childId = $request->input('child_id');
        
        // Verify the child belongs to this parent
        $child = $parent->students()->find($childId);
        
        if (!$child) {
            return response()->json([
                'success' => false,
                'message' => 'Child not found or not linked to this parent.'
            ], 404);
        }
        
        $dashboardData = $this->getChildDashboardData($child);
        
        return response()->json([
            'success' => true,
            'data' => $dashboardData
        ]);
    }
    
    /**
     * Get dashboard data for a specific child.
     */
    private function getChildDashboardData(Student $child): array
    {
        // Today's attendance status
        $todayAttendance = Attendance::where('user_id', $child->user_id)
            ->whereDate('created_at', Carbon::today())
            ->first();
        
        // Monthly attendance percentage
        $monthlyAttendance = Attendance::where('user_id', $child->user_id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->get();
        
        $presentDays = $monthlyAttendance->where('status', 'present')->count();
        $totalDays = $monthlyAttendance->count();
        $attendancePercentage = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;
        
        // Outstanding fees
        $outstandingFees = FeeChallan::where('student_id', $child->id)
            ->where('status', '!=', 'paid')
            ->sum('amount') - FeeChallan::where('student_id', $child->id)
            ->where('status', '!=', 'paid')
            ->sum('paid_amount');
        
        // Next due date
        $nextDueChallan = FeeChallan::where('student_id', $child->id)
            ->where('status', '!=', 'paid')
            ->orderBy('due_date', 'asc')
            ->first();
        
        $nextDueDate = $nextDueChallan ? $nextDueChallan->due_date->format('M j, Y') : 'N/A';
        
        // Latest result
        $latestResult = ExamResult::where('student_id', $child->id)
            ->with(['exam.subject'])
            ->orderBy('created_at', 'desc')
            ->first();
        
        $latestExamData = null;
        if ($latestResult) {
            $latestExamData = [
                'exam_title' => $latestResult->exam->title,
                'subject' => $latestResult->exam->subject->name,
                'percentage' => $latestResult->percentage,
                'grade' => $latestResult->grade,
                'grade_color' => GradeCalculator::getGradeColor($latestResult->grade),
                'status' => $latestResult->status,
                'date' => $latestResult->exam->getFormattedExamDate()
            ];
        }
        
        // Recent notifications (mock data for now)
        $recentNotifications = [
            [
                'type' => 'fee_reminder',
                'message' => 'Fee payment due for ' . Carbon::now()->format('F'),
                'date' => Carbon::now()->subDays(2)->format('M j, Y'),
                'icon' => 'fas fa-money-bill',
                'color' => 'warning'
            ],
            [
                'type' => 'attendance_alert',
                'message' => $child->user->name . ' was absent yesterday',
                'date' => Carbon::yesterday()->format('M j, Y'),
                'icon' => 'fas fa-calendar-times',
                'color' => 'danger'
            ],
            [
                'type' => 'new_assignment',
                'message' => 'New assignment posted for Mathematics',
                'date' => Carbon::now()->subDays(3)->format('M j, Y'),
                'icon' => 'fas fa-book',
                'color' => 'info'
            ],
            [
                'type' => 'exam_result',
                'message' => 'Science exam results are available',
                'date' => Carbon::now()->subDays(5)->format('M j, Y'),
                'icon' => 'fas fa-chart-bar',
                'color' => 'success'
            ],
            [
                'type' => 'general_notice',
                'message' => 'Parent-teacher meeting scheduled for next week',
                'date' => Carbon::now()->subWeek()->format('M j, Y'),
                'icon' => 'fas fa-bullhorn',
                'color' => 'primary'
            ]
        ];
        
        return [
            'child' => $child,
            'attendance' => [
                'today_status' => $todayAttendance ? $todayAttendance->status : 'not_marked',
                'today_status_display' => $this->getAttendanceStatusDisplay($todayAttendance ? $todayAttendance->status : 'not_marked'),
                'today_status_color' => $this->getAttendanceStatusColor($todayAttendance ? $todayAttendance->status : 'not_marked'),
                'monthly_percentage' => round($attendancePercentage, 1),
                'monthly_present_days' => $presentDays,
                'monthly_total_days' => $totalDays
            ],
            'fees' => [
                'outstanding_amount' => $outstandingFees,
                'next_due_date' => $nextDueDate,
                'next_due_amount' => $nextDueChallan ? $nextDueChallan->amount - $nextDueChallan->paid_amount : 0
            ],
            'latest_result' => $latestExamData,
            'notifications' => $recentNotifications,
            'quick_links' => [
                'attendance' => route('parent.attendance', $child->id),
                'fees' => route('parent.fees', $child->id),
                'results' => route('parent.results', $child->id)
            ]
        ];
    }
    
    /**
     * Get attendance status display text.
     */
    private function getAttendanceStatusDisplay(string $status): string
    {
        return match($status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'holiday' => 'Holiday',
            'not_marked' => 'Not Marked',
            default => 'Unknown'
        };
    }
    
    /**
     * Get attendance status color.
     */
    private function getAttendanceStatusColor(string $status): string
    {
        return match($status) {
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'holiday' => 'secondary',
            'not_marked' => 'info',
            default => 'secondary'
        };
    }
}
