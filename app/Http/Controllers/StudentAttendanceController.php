<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarkStudentAttendanceRequest;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\TeacherClassAssignment;
use App\Jobs\SendAbsenceSMS;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class StudentAttendanceController extends Controller
{
    /**
     * Show the attendance marking form for teachers.
     */
    public function classForm(): View
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

        return view('teacher.attendance.mark-student', compact('classes'));
    }

    /**
     * Get students for a specific class and section (AJAX).
     */
    public function getStudents(Request $request): JsonResponse
    {
        $classId = $request->get('class_id');
        $sectionId = $request->get('section_id');
        $date = $request->get('date', now()->format('Y-m-d'));

        $query = Student::with(['user', 'class'])
            ->where('class_id', $classId)
            ->where('is_active', true);

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $students = $query->orderBy('roll_number')
            ->orderBy('user.name')
            ->get();

        // Get existing attendance for the date
        $existingAttendance = Attendance::where('date', $date)
            ->whereIn('user_id', $students->pluck('user_id'))
            ->get()
            ->keyBy('user_id');

        $studentsData = $students->map(function ($student) use ($existingAttendance) {
            $attendance = $existingAttendance->get($student->user_id);
            return [
                'id' => $student->id,
                'user_id' => $student->user_id,
                'roll_number' => $student->roll_number,
                'name' => $student->user->name,
                'current_status' => $attendance?->status,
                'current_remarks' => $attendance?->remarks,
                'attendance_id' => $attendance?->id
            ];
        });

        return response()->json([
            'success' => true,
            'students' => $studentsData
        ]);
    }

    /**
     * Mark attendance for students.
     */
    public function markClass(MarkStudentAttendanceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $teacherId = auth()->id();
        $date = $validated['date'];

        $attendanceRecords = [];
        $absentStudents = [];

        foreach ($validated['attendance'] as $attendanceData) {
            $student = Student::find($attendanceData['student_id']);
            
            // Check if attendance already exists
            $existingAttendance = Attendance::where('user_id', $student->user_id)
                ->where('date', $date)
                ->first();

            if ($existingAttendance) {
                // Update existing record
                $existingAttendance->update([
                    'status' => $attendanceData['status'],
                    'remarks' => $attendanceData['remarks'] ?? null,
                    'marked_by' => $teacherId,
                    'check_in_time' => $attendanceData['status'] === 'present' ? now()->format('H:i:s') : null,
                ]);
            } else {
                // Create new record
                $attendance = Attendance::create([
                    'user_id' => $student->user_id,
                    'class_id' => $validated['class_id'],
                    'date' => $date,
                    'status' => $attendanceData['status'],
                    'remarks' => $attendanceData['remarks'] ?? null,
                    'marked_by' => $teacherId,
                    'check_in_time' => $attendanceData['status'] === 'present' ? now()->format('H:i:s') : null,
                    'attendance_method' => 'manual'
                ]);
            }

            // Queue SMS for absent students
            if ($attendanceData['status'] === 'absent' && $student->parentProfile) {
                $absentStudents[] = [
                    'student_name' => $student->user->name,
                    'parent_phone' => $student->parentProfile->father_phone ?? $student->parentProfile->mother_phone,
                    'class_name' => $student->class->name,
                    'date' => $date
                ];
            }

            $attendanceRecords[] = [
                'student_id' => $attendanceData['student_id'],
                'status' => $attendanceData['status']
            ];
        }

        // Dispatch SMS jobs for absent students
        if (!empty($absentStudents)) {
            foreach ($absentStudents as $absentStudent) {
                if ($absentStudent['parent_phone']) {
                    SendAbsenceSMS::dispatch($absentStudent);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully!',
            'records_count' => count($attendanceRecords),
            'absent_count' => count($absentStudents)
        ]);
    }

    /**
     * Get sections for a class (AJAX).
     */
    public function getSections($classId): JsonResponse
    {
        $sections = Section::where('class_id', $classId)
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
