<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class ExamController extends Controller
{
    /**
     * Display a listing of exams for teachers.
     */
    public function index(): View
    {
        $exams = Exam::where('teacher_id', auth()->id())
            ->with(['schoolClass', 'subject', 'examResults'])
            ->orderBy('exam_date', 'desc')
            ->get();

        return view('teacher.exams.index', compact('exams'));
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create(): View
    {
        $classes = SchoolClass::orderBy('grade_level')->get();
        $subjects = Subject::orderBy('name')->get();
        
        return view('teacher.exams.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created exam in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'section' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date|after:today',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'total_marks' => 'required|integer|min:1|max:1000',
            'passing_marks' => 'required|integer|min:0|max:total_marks',
            'exam_type' => 'required|in:midterm,final,quiz,assignment,practical',
            'instructions' => 'nullable|string',
            'allow_retake' => 'boolean',
            'max_attempts' => 'required|integer|min:1|max:5'
        ]);

        try {
            $exam = Exam::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'class_id' => $validated['class_id'],
                'section' => $validated['section'],
                'subject_id' => $validated['subject_id'],
                'teacher_id' => auth()->id(),
                'exam_date' => $validated['exam_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'duration_minutes' => $validated['duration_minutes'],
                'total_marks' => $validated['total_marks'],
                'passing_marks' => $validated['passing_marks'],
                'exam_type' => $validated['exam_type'],
                'status' => 'scheduled',
                'instructions' => $validated['instructions'],
                'allow_retake' => $validated['allow_retake'],
                'max_attempts' => $validated['max_attempts']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Exam created successfully!',
                'exam' => $exam
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create exam: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified exam.
     */
    public function show(Exam $exam): View
    {
        // Check if user owns this exam
        if ($exam->teacher_id !== auth()->id()) {
            abort(403, 'You are not authorized to view this exam.');
        }

        $exam->load(['schoolClass', 'subject', 'examResults.student.user']);

        // Calculate statistics
        $statistics = [
            'total_students' => $exam->schoolClass->students()->count(),
            'total_results' => $exam->examResults()->count(),
            'passed_results' => $exam->examResults()->where('status', 'pass')->count(),
            'failed_results' => $exam->examResults()->where('status', 'fail')->count(),
            'absent_results' => $exam->examResults()->where('status', 'absent')->count(),
            'average_score' => $exam->getAverageScore(),
            'highest_score' => $exam->getHighestScore(),
            'lowest_score' => $exam->getLowestScore(),
            'pass_rate' => $exam->getPassRate(),
            'attendance_rate' => $exam->getAttendanceRate()
        ];

        return view('teacher.exams.show', compact('exam', 'statistics'));
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit(Exam $exam): View
    {
        // Check if user owns this exam
        if ($exam->teacher_id !== auth()->id()) {
            abort(403, 'You are not authorized to edit this exam.');
        }

        if ($exam->status !== 'scheduled') {
            abort(403, 'Cannot edit exam that has started or completed.');
        }

        $classes = SchoolClass::orderBy('grade_level')->get();
        $subjects = Subject::orderBy('name')->get();

        return view('teacher.exams.edit', compact('exam', 'classes', 'subjects'));
    }

    /**
     * Update the specified exam in storage.
     */
    public function update(Request $request, Exam $exam): JsonResponse
    {
        // Check if user owns this exam
        if ($exam->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to edit this exam.'
            ], 403);
        }

        if ($exam->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit exam that has started or completed.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'class_id' => 'required|exists:classes,id',
            'section' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'exam_date' => 'required|date|after:today',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'total_marks' => 'required|integer|min:1|max:1000',
            'passing_marks' => 'required|integer|min:0|max:total_marks',
            'exam_type' => 'required|in:midterm,final,quiz,assignment,practical',
            'instructions' => 'nullable|string',
            'allow_retake' => 'boolean',
            'max_attempts' => 'required|integer|min:1|max:5'
        ]);

        try {
            $exam->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Exam updated successfully!',
                'exam' => $exam
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update exam: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified exam from storage.
     */
    public function destroy(Exam $exam): JsonResponse
    {
        // Check if user owns this exam
        if ($exam->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this exam.'
            ], 403);
        }

        if ($exam->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete exam that has started or completed.'
            ], 403);
        }

        try {
            $exam->delete();

            return response()->json([
                'success' => true,
                'message' => 'Exam deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete exam: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sections for a class.
     */
    public function getSections(Request $request): JsonResponse
    {
        $classId = $request->input('class_id');
        $class = SchoolClass::find($classId);
        
        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found.'
            ], 404);
        }

        $sections = $class->getSections();

        return response()->json([
            'success' => true,
            'sections' => $sections
        ]);
    }

    /**
     * Get subjects for a class.
     */
    public function getSubjects(Request $request): JsonResponse
    {
        $classId = $request->input('class_id');
        $class = SchoolClass::find($classId);
        
        if (!$class) {
            return response()->json([
                'success' => false,
                'message' => 'Class not found.'
            ], 404);
        }

        $subjects = $class->getSubjects();

        return response()->json([
            'success' => true,
            'subjects' => $subjects
        ]);
    }

    /**
     * Start an exam.
     */
    public function startExam(Exam $exam): JsonResponse
    {
        // Check if user owns this exam
        if ($exam->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to start this exam.'
            ], 403);
        }

        if (!$exam->canStart()) {
            return response()->json([
                'success' => false,
                'message' => 'Exam cannot be started at this time.'
            ], 403);
        }

        try {
            $exam->update(['status' => 'ongoing']);

            return response()->json([
                'success' => true,
                'message' => 'Exam started successfully!',
                'exam' => $exam
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start exam: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * End an exam.
     */
    public function endExam(Exam $exam): JsonResponse
    {
        // Check if user owns this exam
        if ($exam->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to end this exam.'
            ], 403);
        }

        if ($exam->status !== 'ongoing') {
            return response()->json([
                'success' => false,
                'message' => 'Exam is not currently ongoing.'
            ], 403);
        }

        try {
            $exam->update(['status' => 'completed']);

            return response()->json([
                'success' => true,
                'message' => 'Exam ended successfully!',
                'exam' => $exam
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to end exam: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an exam.
     */
    public function cancelExam(Exam $exam): JsonResponse
    {
        // Check if user owns this exam
        if ($exam->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to cancel this exam.'
            ], 403);
        }

        if ($exam->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel completed exam.'
            ], 403);
        }

        try {
            $exam->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Exam cancelled successfully!',
                'exam' => $exam
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel exam: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display exam results for teachers.
     */
    public function results(Exam $exam): View
    {
        // Check if user owns this exam
        if ($exam->teacher_id !== auth()->id()) {
            abort(403, 'You are not authorized to view these results.');
        }

        $results = $exam->examResults()
            ->with(['student.user'])
            ->orderBy('percentage', 'desc')
            ->get();

        return view('teacher.exams.results', compact('exam', 'results'));
    }

    /**
     * Export exam results.
     */
    public function exportResults(Exam $exam): JsonResponse
    {
        // Check if user owns this exam
        if ($exam->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to export these results.'
            ], 403);
        }

        try {
            $results = $exam->examResults()
                ->with(['student.user'])
                ->orderBy('percentage', 'desc')
                ->get();

            $csvData = [];
            $csvData[] = ['Roll Number', 'Student Name', 'Marks Obtained', 'Total Marks', 'Percentage', 'Grade', 'Status', 'Remarks'];

            foreach ($results as $result) {
                $csvData[] = [
                    $result->student->roll_number ?? 'N/A',
                    $result->student->user->name,
                    $result->marks_obtained,
                    $result->total_marks,
                    $result->getFormattedPercentage(),
                    $result->grade,
                    $result->getStatusDisplay(),
                    $result->remarks ?? ''
                ];
            }

            $filename = 'exam_results_' . $exam->id . '_' . date('Y-m-d') . '.csv';
            
            $callback = function() use ($csvData) {
                $file = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export results: ' . $e->getMessage()
            ], 500);
        }
    }
}
