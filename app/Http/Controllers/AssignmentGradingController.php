<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionFile;
use App\Notifications\AssignmentGradedNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AssignmentGradingController extends Controller
{
    /**
     * Display a listing of assignments for grading.
     */
    public function index(): View
    {
        $assignments = Assignment::where('teacher_id', auth()->id())
            ->with(['schoolClass', 'subject', 'submissions' => function($query) {
                $query->with(['student.user', 'files']);
            }])
            ->orderBy('due_date', 'desc')
            ->get();

        return view('teacher.assignments.grading.index', compact('assignments'));
    }

    /**
     * Show the grading interface for a specific assignment.
     */
    public function grade(Assignment $assignment): View
    {
        // Check if user owns this assignment
        if ($assignment->teacher_id !== auth()->id()) {
            abort(403, 'You are not authorized to grade this assignment.');
        }

        $submissions = $assignment->submissions()
            ->with(['student.user', 'files'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate grading statistics
        $statistics = [
            'total_submissions' => $submissions->count(),
            'graded_submissions' => $submissions->whereNotNull('graded_at')->count(),
            'pending_submissions' => $submissions->whereNull('graded_at')->count(),
            'average_score' => $submissions->whereNotNull('marks_obtained')->avg('marks_obtained'),
            'highest_score' => $submissions->whereNotNull('marks_obtained')->max('marks_obtained'),
            'lowest_score' => $submissions->whereNotNull('marks_obtained')->min('marks_obtained'),
            'grade_distribution' => $this->getGradeDistribution($submissions)
        ];

        return view('teacher.assignments.grading.grade', compact('assignment', 'submissions', 'statistics'));
    }

    /**
     * Show the form for grading a specific submission.
     */
    public function showSubmission(Assignment $assignment, AssignmentSubmission $submission): View
    {
        // Check if user owns this assignment
        if ($assignment->teacher_id !== auth()->id()) {
            abort(403, 'You are not authorized to grade this assignment.');
        }

        // Check if submission belongs to this assignment
        if ($submission->assignment_id !== $assignment->id) {
            abort(404, 'Submission not found for this assignment.');
        }

        $submission->load(['student.user', 'files']);

        return view('teacher.assignments.grading.submission', compact('assignment', 'submission'));
    }

    /**
     * Grade a specific submission.
     */
    public function gradeSubmission(Request $request, Assignment $assignment, AssignmentSubmission $submission): JsonResponse
    {
        // Check if user owns this assignment
        if ($assignment->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to grade this assignment.'
            ], 403);
        }

        // Check if submission belongs to this assignment
        if ($submission->assignment_id !== $assignment->id) {
            return response()->json([
                'success' => false,
                'message' => 'Submission not found for this assignment.'
            ], 404);
        }

        $validated = $request->validate([
            'marks_obtained' => 'required|integer|min:0|max:' . $assignment->total_marks,
            'feedback' => 'nullable|string|max:1000'
        ]);

        try {
            // Update submission
            $submission->marks_obtained = $validated['marks_obtained'];
            $submission->feedback = $validated['feedback'];
            $submission->graded_at = now();
            $submission->graded_by = auth()->id();
            $submission->save();

            // Send notification to student
            $submission->student->user->notify(new AssignmentGradedNotification($submission));

            return response()->json([
                'success' => true,
                'message' => 'Assignment graded successfully!',
                'submission' => $submission->load(['student.user'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to grade assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk grade multiple submissions.
     */
    public function bulkGrade(Request $request, Assignment $assignment): JsonResponse
    {
        // Check if user owns this assignment
        if ($assignment->teacher_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to grade this assignment.'
            ], 403);
        }

        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.submission_id' => 'required|exists:assignment_submissions,id',
            'grades.*.marks_obtained' => 'required|integer|min:0|max:' . $assignment->total_marks,
            'grades.*.feedback' => 'nullable|string|max:1000'
        ]);

        try {
            $gradedCount = 0;
            
            foreach ($validated['grades'] as $gradeData) {
                $submission = AssignmentSubmission::find($gradeData['submission_id']);
                
                if ($submission && $submission->assignment_id === $assignment->id) {
                    $submission->marks_obtained = $gradeData['marks_obtained'];
                    $submission->feedback = $gradeData['feedback'] ?? null;
                    $submission->graded_at = now();
                    $submission->graded_by = auth()->id();
                    $submission->save();
                    
                    // Send notification to student
                    $submission->student->user->notify(new AssignmentGradedNotification($submission));
                    
                    $gradedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully graded {$gradedCount} submissions!",
                'graded_count' => $gradedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to grade assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a submission file.
     */
    public function downloadFile($fileId)
    {
        $file = AssignmentSubmissionFile::findOrFail($fileId);
        $submission = $file->assignmentSubmission;
        $assignment = $submission->assignment;
        
        // Check if user owns this assignment
        if ($assignment->teacher_id !== auth()->id()) {
            abort(403);
        }

        $filePath = Storage::disk('private')->path($file->file_path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, $file->original_name);
    }

    /**
     * Preview an image file.
     */
    public function previewFile($fileId)
    {
        $file = AssignmentSubmissionFile::findOrFail($fileId);
        
        if (!$file->isImage()) {
            abort(404);
        }

        $submission = $file->assignmentSubmission;
        $assignment = $submission->assignment;
        
        // Check if user owns this assignment
        if ($assignment->teacher_id !== auth()->id()) {
            abort(403);
        }

        $filePath = Storage::disk('private')->path($file->file_path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    }

    /**
     * Export grading results.
     */
    public function exportGrades(Assignment $assignment)
    {
        // Check if user owns this assignment
        if ($assignment->teacher_id !== auth()->id()) {
            abort(403, 'You are not authorized to export grades for this assignment.');
        }

        $submissions = $assignment->submissions()
            ->with(['student.user'])
            ->orderBy('student.roll_number')
            ->get();

        $csvData = [];
        $csvData[] = ['Roll Number', 'Student Name', 'Submission Date', 'Marks Obtained', 'Total Marks', 'Percentage', 'Grade', 'Status', 'Feedback'];

        foreach ($submissions as $submission) {
            $csvData[] = [
                $submission->student->roll_number ?? 'N/A',
                $submission->student->user->name,
                $submission->getFormattedSubmissionDate(),
                $submission->marks_obtained ?? 'Not Graded',
                $assignment->total_marks,
                $submission->isGraded() ? $submission->getPercentageScore() . '%' : 'N/A',
                $submission->isGraded() ? $submission->getGrade() : 'N/A',
                $submission->getStatusDisplay(),
                $submission->feedback ?? ''
            ];
        }

        $filename = 'assignment_grades_' . $assignment->id . '_' . date('Y-m-d') . '.csv';
        
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
    }

    /**
     * Get grade distribution statistics.
     */
    private function getGradeDistribution($submissions): array
    {
        $distribution = [
            'A+' => 0, 'A' => 0, 'A-' => 0,
            'B+' => 0, 'B' => 0, 'B-' => 0,
            'C+' => 0, 'C' => 0, 'C-' => 0,
            'D+' => 0, 'D' => 0, 'D-' => 0,
            'F' => 0, 'Not Graded' => 0
        ];

        foreach ($submissions as $submission) {
            if ($submission->isGraded()) {
                $grade = $submission->getGrade();
                $distribution[$grade]++;
            } else {
                $distribution['Not Graded']++;
            }
        }

        return $distribution;
    }
}
