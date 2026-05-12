<?php

namespace App\Http\Controllers;

use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TeacherAttendanceController extends Controller
{
    /**
     * Show the teacher check-in/check-out page.
     */
    public function checkinPage(): View
    {
        $teacher = auth()->user();
        $todayAttendance = TeacherAttendance::getTodayStatus($teacher->id);
        
        // Get recent attendance history (last 7 days)
        $recentAttendance = TeacherAttendance::forTeacher($teacher->id)
            ->with('marker')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        return view('teacher.attendance.checkin', compact('teacher', 'todayAttendance', 'recentAttendance'));
    }

    /**
     * Process teacher check-in.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $teacherId = auth()->id();
        
        try {
            $attendance = TeacherAttendance::checkIn($teacherId);
            
            return response()->json([
                'success' => true,
                'message' => 'Check-in successful!',
                'data' => [
                    'check_in_time' => $attendance->check_in_time,
                    'status' => $attendance->status,
                    'late_minutes' => $attendance->late_minutes,
                    'status_display' => $attendance->getStatusDisplay(),
                    'status_color' => $attendance->getStatusBadgeColor()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check-in: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process teacher check-out.
     */
    public function checkOut(Request $request): JsonResponse
    {
        $teacherId = auth()->id();
        
        try {
            $attendance = TeacherAttendance::checkOut($teacherId);
            
            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must check-in before checking out.'
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Check-out successful!',
                'data' => [
                    'check_out_time' => $attendance->check_out_time,
                    'working_hours' => $attendance->formatted_working_hours,
                    'status' => $attendance->status,
                    'status_display' => $attendance->getStatusDisplay(),
                    'status_color' => $attendance->getStatusBadgeColor()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check-out: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current attendance status (AJAX).
     */
    public function getCurrentStatus(): JsonResponse
    {
        $teacherId = auth()->id();
        $attendance = TeacherAttendance::getTodayStatus($teacherId);
        
        if (!$attendance) {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_checked_in' => false,
                    'has_checked_out' => false,
                    'status' => 'not_checked_in',
                    'status_display' => 'Not Checked In',
                    'status_color' => 'secondary'
                ]
            ]);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'has_checked_in' => $attendance->hasCheckedIn(),
                'has_checked_out' => $attendance->hasCheckedOut(),
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
                'working_hours' => $attendance->formatted_working_hours,
                'status' => $attendance->status,
                'status_display' => $attendance->getStatusDisplay(),
                'status_color' => $attendance->getStatusBadgeColor(),
                'late_minutes' => $attendance->late_minutes
            ]
        ]);
    }

    /**
     * Get attendance timeline for today (AJAX).
     */
    public function getTimeline(): JsonResponse
    {
        $teacherId = auth()->id();
        $attendance = TeacherAttendance::getTodayStatus($teacherId);
        
        $timeline = [];
        
        if ($attendance) {
            if ($attendance->check_in_time) {
                $timeline[] = [
                    'time' => $attendance->check_in_time,
                    'action' => 'Checked In',
                    'icon' => 'fa-sign-in-alt',
                    'color' => $attendance->status === 'late' ? 'warning' : 'success',
                    'description' => $attendance->status === 'late' 
                        ? "Checked in late ({$attendance->late_minutes} minutes)" 
                        : 'Checked in on time'
                ];
            }
            
            if ($attendance->check_out_time) {
                $timeline[] = [
                    'time' => $attendance->check_out_time,
                    'action' => 'Checked Out',
                    'icon' => 'fa-sign-out-alt',
                    'color' => 'info',
                    'description' => "Working hours: {$attendance->formatted_working_hours}"
                ];
            }
        }
        
        return response()->json([
            'success' => true,
            'timeline' => $timeline
        ]);
    }

    /**
     * HR Dashboard - Teacher attendance overview.
     */
    public function hrIndex(Request $request): View
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $departmentId = $request->get('department_id');
        $status = $request->get('status', 'all');
        
        // Build query for teacher attendance
        $query = TeacherAttendance::with(['teacher', 'marker'])
            ->forDate($date)
            ->whereHas('teacher', function($q) {
                $q->where('is_active', true);
            });
        
        // Apply filters
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        if ($departmentId) {
            $query->whereHas('teacher', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $attendances = $query->orderBy('teacher.name')
            ->paginate(15);
        
        // Calculate KPI statistics
        $totalTeachers = User::whereHas('role', function($q) {
            $q->where('slug', 'teacher');
        })->where('is_active', true)->count();
        
        $presentCount = TeacherAttendance::forDate($date)->present()->count();
        $lateCount = TeacherAttendance::forDate($date)->late()->count();
        $absentCount = TeacherAttendance::forDate($date)->absent()->count();
        
        $attendancePercentage = $totalTeachers > 0 
            ? (($presentCount + $lateCount) / $totalTeachers) * 100 
            : 0;
        
        // Get departments for filter
        $departments = \App\Models\Department::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('hr.teacher-attendance.index', compact(
            'attendances',
            'totalTeachers',
            'presentCount',
            'lateCount',
            'absentCount',
            'attendancePercentage',
            'departments',
            'date',
            'departmentId',
            'status'
        ));
    }

    /**
     * Manual attendance entry for HR.
     */
    public function manualCreate(): View
    {
        $teachers = User::whereHas('role', function($q) {
            $q->where('slug', 'teacher');
        })->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('hr.teacher-attendance.manual', compact('teachers'));
    }

    /**
     * Store manual attendance entry.
     */
    public function manualStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:present,absent,late,half_day',
            'check_in_time' => 'nullable|required_if:status,present,late,half_day|date_format:H:i',
            'check_out_time' => 'nullable|required_if:status,present,half_day|date_format:H:i|after:check_in_time',
            'reason' => 'required_if:status,absent,late|string|max:255',
            'remarks' => 'nullable|string|max:500'
        ]);
        
        $teacherId = $validated['teacher_id'];
        $date = $validated['date'];
        
        // Check if attendance already exists
        $existing = TeacherAttendance::forTeacher($teacherId)->forDate($date)->first();
        
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance already exists for this teacher on this date.',
                'existing' => true
            ], 400);
        }
        
        // Create attendance record
        $attendance = TeacherAttendance::create([
            'teacher_id' => $teacherId,
            'date' => $date,
            'check_in_time' => $validated['check_in_time'] ?? null,
            'check_out_time' => $validated['check_out_time'] ?? null,
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
            'marked_by' => auth()->id(),
            'attendance_method' => 'manual'
        ]);
        
        // Send notification to teacher
        // $this->notifyTeacher($attendance);
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully!',
            'data' => $attendance
        ]);
    }
}
