<?php

namespace App\Http\Controllers;

use App\Http\Requests\CorrectionRequestRequest;
use App\Models\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\TeacherClassAssignment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class AttendanceCorrectionController extends Controller
{
    /**
     * Show the attendance correction request form.
     */
    public function index(): View
    {
        $teacher = auth()->user();
        $assignedClasses = $teacher->assignedClasses;
        
        if ($assignedClasses->isEmpty()) {
            return view('teacher.attendance.no-classes');
        }

        $classes = SchoolClass::whereIn('id', $assignedClasses->pluck('class_id'))
            ->where('is_active', true)
            ->orderBy('grade_level')
            ->orderBy('name')
            ->get();

        // Get teacher's correction requests
        $myRequests = AttendanceCorrectionRequest::with(['student.user', 'attendance'])
            ->forTeacher($teacher->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('teacher.attendance.corrections', compact('classes', 'myRequests'));
    }

    /**
     * Get attendance records for a specific date (AJAX).
     */
    public function getAttendanceRecords(Request $request): JsonResponse
    {
        $classId = $request->get('class_id');
        $date = $request->get('date');

        if (!$classId || !$date) {
            return response()->json([
                'success' => false,
                'message' => 'Class and date are required'
            ]);
        }

        // Verify teacher is assigned to this class
        if (auth()->user()->hasRole('teacher')) {
            $isAssigned = TeacherClassAssignment::where('teacher_id', auth()->id())
                ->where('class_id', $classId)
                ->where('is_active', true)
                ->exists();
            
            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to this class'
                ]);
            }
        }

        $students = Student::with(['user', 'class'])
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('roll_number')
            ->orderBy('user.name')
            ->get();

        $attendanceRecords = Attendance::where('date', $date)
            ->whereIn('user_id', $students->pluck('user_id'))
            ->get()
            ->keyBy('user_id');

        $studentsData = $students->map(function ($student) use ($attendanceRecords) {
            $attendance = $attendanceRecords->get($student->user_id);
            
            if (!$attendance) {
                return null; // Skip students with no attendance record
            }

            return [
                'id' => $student->id,
                'user_id' => $student->user_id,
                'roll_number' => $student->roll_number,
                'name' => $student->user->name,
                'attendance_id' => $attendance->id,
                'current_status' => $attendance->status,
                'check_in_time' => $attendance->check_in_time,
                'remarks' => $attendance->remarks
            ];
        })->filter();

        return response()->json([
            'success' => true,
            'students' => $studentsData
        ]);
    }

    /**
     * Store a new attendance correction request.
     */
    public function store(CorrectionRequestRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $teacherId = auth()->id();

        // Check if there's already a pending request for this attendance
        $existingRequest = AttendanceCorrectionRequest::where('attendance_id', $validated['attendance_id'])
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'A correction request for this attendance record is already pending approval.'
            ]);
        }

        // Get the attendance record
        $attendance = Attendance::find($validated['attendance_id']);
        
        // Create correction request
        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_id' => $validated['attendance_id'],
            'student_id' => $attendance->user_id,
            'teacher_id' => $teacherId,
            'current_status' => $attendance->status,
            'requested_status' => $validated['requested_status'],
            'reason' => $validated['reason'],
            'status' => 'pending'
        ]);

        // Notify admin (you can implement notification logic here)
        // $this->notifyAdmins($correctionRequest);

        return response()->json([
            'success' => true,
            'message' => 'Correction request submitted successfully! It will be reviewed by the administration.',
            'request' => $correctionRequest->load(['student.user'])
        ]);
    }

    /**
     * Get sections for a class (AJAX).
     */
    public function getSections($classId): JsonResponse
    {
        $sections = \App\Models\Section::where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'sections' => $sections
        ]);
    }

    /**
     * Get subjects for a teacher's class assignment (AJAX).
     */
    public function getSubjects(Request $request): JsonResponse
    {
        $classId = $request->get('class_id');
        $teacherId = auth()->id();

        $assignments = TeacherClassAssignment::where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('is_active', true)
            ->with('subject')
            ->get();

        $subjects = $assignments->map(function ($assignment) {
            return [
                'id' => $assignment->subject_id,
                'name' => $assignment->subject->name
            ];
        })->filter();

        return response()->json([
            'success' => true,
            'subjects' => $subjects
        ]);
    }
}
