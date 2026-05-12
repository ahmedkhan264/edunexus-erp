<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class AssignmentResultsController extends Controller
{
    /**
     * Display assignment results for students.
     */
    public function studentResults(): View
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $submissions = AssignmentSubmission::where('student_id', $student->id)
            ->with(['assignment.subject', 'assignment.schoolClass'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics
        $statistics = [
            'total_assignments' => $submissions->count(),
            'graded_assignments' => $submissions->whereNotNull('graded_at')->count(),
            'pending_assignments' => $submissions->whereNull('graded_at')->count(),
            'average_score' => $submissions->whereNotNull('marks_obtained')->avg('marks_obtained'),
            'total_marks' => $submissions->sum('assignment.total_marks'),
            'obtained_marks' => $submissions->sum('marks_obtained'),
            'grade_distribution' => $this->getStudentGradeDistribution($submissions),
            'subject_performance' => $this->getSubjectPerformance($submissions)
        ];

        return view('student.results.index', compact('submissions', 'statistics'));
    }

    /**
     * Display assignment results for teachers.
     */
    public function teacherResults(): View
    {
        $assignments = Assignment::where('teacher_id', auth()->id())
            ->with(['schoolClass', 'subject', 'submissions' => function($query) {
                $query->with(['student.user']);
            }])
            ->orderBy('due_date', 'desc')
            ->get();

        // Calculate overall statistics
        $statistics = [
            'total_assignments' => $assignments->count(),
            'total_submissions' => $assignments->sum(function($assignment) {
                return $assignment->submissions->count();
            }),
            'graded_submissions' => $assignments->sum(function($assignment) {
                return $assignment->submissions->whereNotNull('graded_at')->count();
            }),
            'pending_submissions' => $assignments->sum(function($assignment) {
                return $assignment->submissions->whereNull('graded_at')->count();
            }),
            'average_score' => $assignments->flatMap->submissions->whereNotNull('marks_obtained')->avg('marks_obtained'),
            'grade_distribution' => $this->getTeacherGradeDistribution($assignments),
            'class_performance' => $this->getClassPerformance($assignments),
            'subject_performance' => $this->getTeacherSubjectPerformance($assignments)
        ];

        return view('teacher.results.index', compact('assignments', 'statistics'));
    }

    /**
     * Display assignment results for administrators.
     */
    public function adminResults(): View
    {
        $assignments = Assignment::with(['schoolClass', 'subject', 'teacher', 'submissions' => function($query) {
            $query->with(['student.user']);
        }])
        ->orderBy('due_date', 'desc')
        ->get();

        // Calculate comprehensive statistics
        $statistics = [
            'total_assignments' => $assignments->count(),
            'total_submissions' => $assignments->sum(function($assignment) {
                return $assignment->submissions->count();
            }),
            'graded_submissions' => $assignments->sum(function($assignment) {
                return $assignment->submissions->whereNotNull('graded_at')->count();
            }),
            'pending_submissions' => $assignments->sum(function($assignment) {
                return $assignment->submissions->whereNull('graded_at')->count();
            }),
            'average_score' => $assignments->flatMap->submissions->whereNotNull('marks_obtained')->avg('marks_obtained'),
            'grade_distribution' => $this->getAdminGradeDistribution($assignments),
            'class_performance' => $this->getAdminClassPerformance($assignments),
            'subject_performance' => $this->getAdminSubjectPerformance($assignments),
            'teacher_performance' => $this->getTeacherPerformance($assignments),
            'monthly_trends' => $this->getMonthlyTrends($assignments)
        ];

        return view('admin.results.index', compact('assignments', 'statistics'));
    }

    /**
     * Show detailed results for a specific assignment.
     */
    public function showAssignmentResults(Assignment $assignment): View
    {
        // Check authorization based on user role
        if (auth()->user()->hasRole(['teacher']) && $assignment->teacher_id !== auth()->id()) {
            abort(403, 'You are not authorized to view these results.');
        }
        
        if (auth()->user()->hasRole(['student'])) {
            $student = auth()->user()->student;
            if (!$student || $assignment->class_id !== $student->class_id || $assignment->section !== $student->section) {
                abort(403, 'You are not authorized to view these results.');
            }
        }

        $submissions = $assignment->submissions()
            ->with(['student.user', 'files'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate assignment statistics
        $statistics = [
            'total_submissions' => $submissions->count(),
            'graded_submissions' => $submissions->whereNotNull('graded_at')->count(),
            'pending_submissions' => $submissions->whereNull('graded_at')->count(),
            'average_score' => $submissions->whereNotNull('marks_obtained')->avg('marks_obtained'),
            'highest_score' => $submissions->whereNotNull('marks_obtained')->max('marks_obtained'),
            'lowest_score' => $submissions->whereNotNull('marks_obtained')->min('marks_obtained'),
            'grade_distribution' => $this->getGradeDistribution($submissions),
            'submission_rate' => $this->getSubmissionRate($assignment),
            'late_submissions' => $submissions->where('created_at', '>', $assignment->due_date)->count()
        ];

        return view('shared.results.assignment', compact('assignment', 'submissions', 'statistics'));
    }

    /**
     * Export results to CSV.
     */
    public function exportResults(Request $request): JsonResponse
    {
        $role = auth()->user()->getRoleNames()->first();
        $assignmentId = $request->input('assignment_id');
        $format = $request->input('format', 'csv');

        try {
            $filename = $this->generateResultsExport($role, $assignmentId, $format);
            
            return response()->json([
                'success' => true,
                'message' => 'Results exported successfully!',
                'filename' => $filename,
                'download_url' => route('results.download', $filename)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download exported results file.
     */
    public function downloadExport($filename)
    {
        $filePath = storage_path('app/exports/' . $filename);
        
        if (!file_exists($filePath)) {
            abort(404, 'Export file not found.');
        }

        return response()->download($filePath, $filename);
    }

    /**
     * Get results data for charts and visualizations.
     */
    public function getResultsData(Request $request): JsonResponse
    {
        $type = $request->input('type', 'overview');
        $period = $request->input('period', 'month');
        
        try {
            $data = $this->getChartData($type, $period);
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get results data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student grade distribution.
     */
    private function getStudentGradeDistribution($submissions): array
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

    /**
     * Get subject performance for student.
     */
    private function getSubjectPerformance($submissions): array
    {
        $performance = [];
        
        foreach ($submissions->groupBy('assignment.subject.name') as $subjectName => $subjectSubmissions) {
            $gradedSubmissions = $subjectSubmissions->whereNotNull('graded_at');
            
            $performance[$subjectName] = [
                'total' => $subjectSubmissions->count(),
                'graded' => $gradedSubmissions->count(),
                'average_score' => $gradedSubmissions->avg('marks_obtained'),
                'total_marks' => $subjectSubmissions->sum('assignment.total_marks'),
                'obtained_marks' => $gradedSubmissions->sum('marks_obtained')
            ];
        }

        return $performance;
    }

    /**
     * Get teacher grade distribution.
     */
    private function getTeacherGradeDistribution($assignments): array
    {
        $allSubmissions = $assignments->flatMap->submissions;
        return $this->getGradeDistribution($allSubmissions);
    }

    /**
     * Get class performance for teacher.
     */
    private function getClassPerformance($assignments): array
    {
        $performance = [];
        
        foreach ($assignments->groupBy('schoolClass.grade_level') as $grade => $gradeAssignments) {
            $allSubmissions = $gradeAssignments->flatMap->submissions;
            $gradedSubmissions = $allSubmissions->whereNotNull('graded_at');
            
            $performance[$grade] = [
                'total_assignments' => $gradeAssignments->count(),
                'total_submissions' => $allSubmissions->count(),
                'graded_submissions' => $gradedSubmissions->count(),
                'average_score' => $gradedSubmissions->avg('marks_obtained'),
                'submission_rate' => $allSubmissions->count() > 0 ? ($gradedSubmissions->count() / $allSubmissions->count()) * 100 : 0
            ];
        }

        return $performance;
    }

    /**
     * Get subject performance for teacher.
     */
    private function getTeacherSubjectPerformance($assignments): array
    {
        $performance = [];
        
        foreach ($assignments->groupBy('subject.name') as $subjectName => $subjectAssignments) {
            $allSubmissions = $subjectAssignments->flatMap->submissions;
            $gradedSubmissions = $allSubmissions->whereNotNull('graded_at');
            
            $performance[$subjectName] = [
                'total_assignments' => $subjectAssignments->count(),
                'total_submissions' => $allSubmissions->count(),
                'graded_submissions' => $gradedSubmissions->count(),
                'average_score' => $gradedSubmissions->avg('marks_obtained'),
                'submission_rate' => $allSubmissions->count() > 0 ? ($gradedSubmissions->count() / $allSubmissions->count()) * 100 : 0
            ];
        }

        return $performance;
    }

    /**
     * Get admin grade distribution.
     */
    private function getAdminGradeDistribution($assignments): array
    {
        $allSubmissions = $assignments->flatMap->submissions;
        return $this->getGradeDistribution($allSubmissions);
    }

    /**
     * Get class performance for admin.
     */
    private function getAdminClassPerformance($assignments): array
    {
        $performance = [];
        
        foreach ($assignments->groupBy('schoolClass.grade_level') as $grade => $gradeAssignments) {
            $allSubmissions = $gradeAssignments->flatMap->submissions;
            $gradedSubmissions = $allSubmissions->whereNotNull('graded_at');
            
            $performance[$grade] = [
                'total_assignments' => $gradeAssignments->count(),
                'total_submissions' => $allSubmissions->count(),
                'graded_submissions' => $gradedSubmissions->count(),
                'average_score' => $gradedSubmissions->avg('marks_obtained'),
                'submission_rate' => $allSubmissions->count() > 0 ? ($gradedSubmissions->count() / $allSubmissions->count()) * 100 : 0
            ];
        }

        return $performance;
    }

    /**
     * Get subject performance for admin.
     */
    private function getAdminSubjectPerformance($assignments): array
    {
        $performance = [];
        
        foreach ($assignments->groupBy('subject.name') as $subjectName => $subjectAssignments) {
            $allSubmissions = $subjectAssignments->flatMap->submissions;
            $gradedSubmissions = $allSubmissions->whereNotNull('graded_at');
            
            $performance[$subjectName] = [
                'total_assignments' => $subjectAssignments->count(),
                'total_submissions' => $allSubmissions->count(),
                'graded_submissions' => $gradedSubmissions->count(),
                'average_score' => $gradedSubmissions->avg('marks_obtained'),
                'submission_rate' => $allSubmissions->count() > 0 ? ($gradedSubmissions->count() / $allSubmissions->count()) * 100 : 0
            ];
        }

        return $performance;
    }

    /**
     * Get teacher performance metrics.
     */
    private function getTeacherPerformance($assignments): array
    {
        $performance = [];
        
        foreach ($assignments->groupBy('teacher.name') as $teacherName => $teacherAssignments) {
            $allSubmissions = $teacherAssignments->flatMap->submissions;
            $gradedSubmissions = $allSubmissions->whereNotNull('graded_at');
            
            $performance[$teacherName] = [
                'total_assignments' => $teacherAssignments->count(),
                'total_submissions' => $allSubmissions->count(),
                'graded_submissions' => $gradedSubmissions->count(),
                'average_score' => $gradedSubmissions->avg('marks_obtained'),
                'grading_efficiency' => $gradedSubmissions->count() > 0 ? ($gradedSubmissions->count() / $allSubmissions->count()) * 100 : 0
            ];
        }

        return $performance;
    }

    /**
     * Get monthly trends.
     */
    private function getMonthlyTrends($assignments): array
    {
        $trends = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthAssignments = $assignments->where('created_at', '>=', $month->startOfMonth())
                                         ->where('created_at', '<=', $month->endOfMonth());
            
            $allSubmissions = $monthAssignments->flatMap->submissions;
            $gradedSubmissions = $allSubmissions->whereNotNull('graded_at');
            
            $trends[$month->format('M Y')] = [
                'assignments' => $monthAssignments->count(),
                'submissions' => $allSubmissions->count(),
                'graded' => $gradedSubmissions->count(),
                'average_score' => $gradedSubmissions->avg('marks_obtained')
            ];
        }

        return $trends;
    }

    /**
     * Get grade distribution for submissions.
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

    /**
     * Get submission rate for assignment.
     */
    private function getSubmissionRate(Assignment $assignment): float
    {
        $totalStudents = $assignment->students()->count();
        $submissionCount = $assignment->submissions()->count();
        
        if ($totalStudents === 0) {
            return 0;
        }
        
        return ($submissionCount / $totalStudents) * 100;
    }

    /**
     * Generate results export.
     */
    private function generateResultsExport($role, $assignmentId, $format): string
    {
        // Implementation for generating export files
        // This would create CSV/Excel files based on the role and filters
        $filename = "results_{$role}_" . date('Y-m-d_H-i-s') . ".{$format}";
        
        // Generate the actual export file here
        
        return $filename;
    }

    /**
     * Get chart data for visualizations.
     */
    private function getChartData($type, $period): array
    {
        // Implementation for getting chart data
        // This would return data formatted for Chart.js or similar libraries
        return [
            'labels' => [],
            'datasets' => []
        ];
    }
}
