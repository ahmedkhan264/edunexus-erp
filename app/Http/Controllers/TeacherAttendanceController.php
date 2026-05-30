<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class TeacherAttendanceController extends Controller
{
    /**
     * Display a listing of teacher attendance records (for HR).
     */
    public function index(Request $request): View
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $departmentId = $request->get('department_id');
        $status = $request->get('status', 'all');
        
        // Build query for teacher attendance using Attendance model
        // Filter by role_id = 3 (teachers)
        $query = Attendance::whereHas('user', function($q) {
            $q->where('role_id', 3);
        })->with(['user', 'user.department']);
        
        // Apply date filter
        $query->whereDate('date', $date);
        
        // Apply status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Apply department filter
        if ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $attendances = $query->orderBy('user.name')->paginate(15);
        
        // Calculate KPI statistics
        $totalTeachers = User::where('role_id', 3)->where('is_active', true)->count();
        
        $presentCount = Attendance::whereHas('user', function($q) {
            $q->where('role_id', 3);
        })->whereDate('date', $date)->where('status', 'present')->count();
        
        $lateCount = Attendance::whereHas('user', function($q) {
            $q->where('role_id', 3);
        })->whereDate('date', $date)->where('status', 'late')->count();
        
        $absentCount = Attendance::whereHas('user', function($q) {
            $q->where('role_id', 3);
        })->whereDate('date', $date)->where('status', 'absent')->count();
        
        $attendancePercentage = $totalTeachers > 0 
            ? (($presentCount + $lateCount) / $totalTeachers) * 100 
            : 0;
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
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
     * Show the teacher check-in/check-out page.
     */
    public function checkinPage(): View
    {
        $teacher = auth()->user();
        $todayAttendance = Attendance::where('user_id', $teacher->id)
            ->whereDate('date', now()->format('Y-m-d'))
            ->first();
        
        // Get recent attendance history (last 7 days)
        $recentAttendance = Attendance::where('user_id', $teacher->id)
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
        $today = now()->format('Y-m-d');
        $now = now();
        
        try {
            // Check if already checked in
            $existing = Attendance::where('user_id', $teacherId)
                ->whereDate('date', $today)
                ->first();
            
            if ($existing && $existing->check_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already checked in today.'
                ], 400);
            }
            
            // Define check-in time (e.g., 9:00 AM)
            $checkInDeadline = Carbon::parse($today . ' 09:00:00');
            $lateMinutes = 0;
            $status = 'present';
            
            if ($now->gt($checkInDeadline)) {
                $lateMinutes = $checkInDeadline->diffInMinutes($now);
                $status = 'late';
            }
            
            $attendance = Attendance::updateOrCreate(
                [
                    'user_id' => $teacherId,
                    'date' => $today,
                ],
                [
                    'check_in_time' => $now->format('H:i:s'),
                    'status' => $status,
                    'remarks' => $status == 'late' ? "Checked in late by {$lateMinutes} minutes" : null,
                ]
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Check-in successful!',
                'data' => [
                    'check_in_time' => $attendance->check_in_time,
                    'status' => $attendance->status,
                    'late_minutes' => $lateMinutes,
                    'status_display' => ucfirst($attendance->status),
                    'status_color' => $status == 'late' ? 'warning' : 'success'
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
        $today = now()->format('Y-m-d');
        $now = now();
        
        try {
            $attendance = Attendance::where('user_id', $teacherId)
                ->whereDate('date', $today)
                ->first();
            
            if (!$attendance || !$attendance->check_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must check-in before checking out.'
                ], 400);
            }
            
            if ($attendance->check_out_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already checked out today.'
                ], 400);
            }
            
            // Calculate working hours
            $checkInTime = Carbon::parse($today . ' ' . $attendance->check_in_time);
            $workingHours = $checkInTime->diffInHours($now);
            
            $attendance->update([
                'check_out_time' => $now->format('H:i:s'),
                'working_hours' => $workingHours
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Check-out successful!',
                'data' => [
                    'check_out_time' => $attendance->check_out_time,
                    'working_hours' => $workingHours,
                    'status' => $attendance->status,
                    'status_display' => ucfirst($attendance->status),
                    'status_color' => 'info'
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
        $today = now()->format('Y-m-d');
        $attendance = Attendance::where('user_id', $teacherId)
            ->whereDate('date', $today)
            ->first();
        
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
                'has_checked_in' => !is_null($attendance->check_in_time),
                'has_checked_out' => !is_null($attendance->check_out_time),
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
                'working_hours' => $attendance->working_hours ?? 0,
                'status' => $attendance->status,
                'status_display' => ucfirst($attendance->status),
                'status_color' => $attendance->status == 'late' ? 'warning' : ($attendance->status == 'present' ? 'success' : 'secondary'),
                'late_minutes' => 0
            ]
        ]);
    }

    /**
     * Get attendance timeline for today (AJAX).
     */
    public function getTimeline(): JsonResponse
    {
        $teacherId = auth()->id();
        $today = now()->format('Y-m-d');
        $attendance = Attendance::where('user_id', $teacherId)
            ->whereDate('date', $today)
            ->first();
        
        $timeline = [];
        
        if ($attendance) {
            if ($attendance->check_in_time) {
                $timeline[] = [
                    'time' => $attendance->check_in_time,
                    'action' => 'Checked In',
                    'icon' => 'fa-sign-in-alt',
                    'color' => $attendance->status === 'late' ? 'warning' : 'success',
                    'description' => $attendance->status === 'late' ? 'Checked in late' : 'Checked in on time'
                ];
            }
            
            if ($attendance->check_out_time) {
                $timeline[] = [
                    'time' => $attendance->check_out_time,
                    'action' => 'Checked Out',
                    'icon' => 'fa-sign-out-alt',
                    'color' => 'info',
                    'description' => "Working hours: {$attendance->working_hours} hours"
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
        return $this->index($request);
    }

    /**
     * Get attendance details for AJAX modal.
     */
    public function details($id): JsonResponse
    {
        $attendance = Attendance::with(['user', 'user.department'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'attendance' => [
                'id' => $attendance->id,
                'date' => $attendance->date,
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
                'formatted_working_hours' => $attendance->working_hours ? $attendance->working_hours . ' hours' : '0 hours',
                'status' => $attendance->status,
                'status_display' => ucfirst($attendance->status),
                'status_color' => $attendance->status == 'late' ? 'warning' : ($attendance->status == 'present' ? 'success' : 'danger'),
                'late_minutes' => 0,
                'attendance_method' => 'system',
                'remarks' => $attendance->remarks,
                'teacher' => [
                    'name' => $attendance->user->name,
                    'email' => $attendance->user->email,
                    'employee_code' => $attendance->user->employee_code ?? 'N/A',
                    'department' => $attendance->user->department ? ['name' => $attendance->user->department->name] : null,
                ],
                'marker' => null,
            ]
        ]);
    }

    /**
     * Manual attendance entry for HR.
     */
    public function manualCreate(): View
    {
        $teachers = User::where('role_id', 3)->where('is_active', true)
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
            'status' => 'required|in:present,absent,late',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'remarks' => 'nullable|string|max:500'
        ]);
        
        $teacherId = $validated['teacher_id'];
        $date = $validated['date'];
        
        // Check if attendance already exists
        $existing = Attendance::where('user_id', $teacherId)
            ->whereDate('date', $date)
            ->first();
        
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance already exists for this teacher on this date.',
                'existing' => true
            ], 400);
        }
        
        // Create attendance record
        $attendance = Attendance::create([
            'user_id' => $teacherId,
            'date' => $date,
            'check_in_time' => $validated['check_in_time'] ?? null,
            'check_out_time' => $validated['check_out_time'] ?? null,
            'status' => $validated['status'],
            'remarks' => $validated['remarks'] ?? null,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully!',
            'data' => $attendance
        ]);
    }

    /**
     * Edit attendance record.
     */
    public function edit($id): View
    {
        $attendance = Attendance::with('user')->findOrFail($id);
        $teachers = User::where('role_id', 3)->get();
        $statusOptions = ['present', 'absent', 'late'];
        
        return view('hr.teacher-attendance.edit', compact('attendance', 'teachers', 'statusOptions'));
    }

    /**
     * Update attendance record.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $attendance = Attendance::findOrFail($id);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'check_in_time' => 'nullable',
            'check_out_time' => 'nullable',
            'remarks' => 'nullable|string|max:500'
        ]);
        
        $attendance->update($validated);
        
        return redirect()->route('hr.teacher-attendance.index')
            ->with('success', 'Attendance record updated successfully!');
    }

    /**
     * Delete attendance record.
     */
    public function destroy($id): RedirectResponse
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();
        
        return redirect()->route('hr.teacher-attendance.index')
            ->with('success', 'Attendance record deleted successfully!');
    }
}