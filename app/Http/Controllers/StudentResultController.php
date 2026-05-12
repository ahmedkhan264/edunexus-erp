<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Student;
use App\Services\GradeCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class StudentResultController extends Controller
{
    /**
     * Display the student's results.
     */
    public function index(): View
    {
        $student = auth()->user()->student;
        
        // Get all exam results for the student, grouped by exam
        $examResults = ExamResult::where('student_id', $student->id)
            ->with(['exam.schoolClass', 'exam.subject'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('exam_id');

        // Calculate statistics for each exam
        $examStatistics = [];
        foreach ($examResults as $examId => $results) {
            $exam = $results->first()->exam;
            
            $totalObtained = $results->sum('marks_obtained');
            $totalMax = $results->sum('total_marks');
            $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
            $grade = GradeCalculator::calculateGrade($percentage);
            $gradeColor = GradeCalculator::getGradeColor($grade);
            
            $examStatistics[$examId] = [
                'exam' => $exam,
                'results' => $results,
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $percentage,
                'grade' => $grade,
                'grade_color' => $gradeColor,
                'status' => $results->first()->status,
                'formatted_date' => $exam->getFormattedExamDate(),
                'formatted_time' => $exam->getFullDateTime()
            ];
        }

        // Calculate overall statistics
        $overallStats = [
            'total_exams' => count($examStatistics),
            'total_marks_obtained' => array_sum(array_column($examStatistics, 'total_obtained')),
            'total_marks_max' => array_sum(array_column($examStatistics, 'total_max')),
            'average_percentage' => 0,
            'grade_distribution' => []
        ];

        if ($overallStats['total_marks_max'] > 0) {
            $overallStats['average_percentage'] = ($overallStats['total_marks_obtained'] / $overallStats['total_marks_max']) * 100;
        }

        // Calculate grade distribution
        foreach ($examStatistics as $stat) {
            $grade = $stat['grade'];
            if (!isset($overallStats['grade_distribution'][$grade])) {
                $overallStats['grade_distribution'][$grade] = 0;
            }
            $overallStats['grade_distribution'][$grade]++;
        }

        return view('student.results.index', compact('examStatistics', 'overallStats'));
    }

    /**
     * Display a specific exam result.
     */
    public function show(Exam $exam): View
    {
        $student = auth()->user()->student;
        
        // Verify this exam belongs to the student
        $examResults = ExamResult::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->with(['exam.schoolClass', 'exam.subject'])
            ->get();

        if ($examResults->isEmpty()) {
            abort(404, 'Exam results not found');
        }

        // Calculate statistics
        $totalObtained = $examResults->sum('marks_obtained');
        $totalMax = $examResults->sum('total_marks');
        $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
        $grade = GradeCalculator::calculateGrade($percentage);
        $gradeColor = GradeCalculator::getGradeColor($grade);

        $examStatistics = [
            'exam' => $exam,
            'results' => $examResults,
            'total_obtained' => $totalObtained,
            'total_max' => $totalMax,
            'percentage' => $percentage,
            'grade' => $grade,
            'grade_color' => $gradeColor,
            'status' => $examResults->first()->status,
            'formatted_date' => $exam->getFormattedExamDate(),
            'formatted_time' => $exam->getFullDateTime()
        ];

        return view('student.results.show', compact('examStatistics'));
    }

    /**
     * Export student results.
     */
    public function export(): JsonResponse
    {
        $student = auth()->user()->student;
        
        // Get all exam results for the student
        $examResults = ExamResult::where('student_id', $student->id)
            ->with(['exam.schoolClass', 'exam.subject'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('exam_id');

        // Calculate statistics for each exam
        $examStatistics = [];
        foreach ($examResults as $examId => $results) {
            $exam = $results->first()->exam;
            
            $totalObtained = $results->sum('marks_obtained');
            $totalMax = $results->sum('total_marks');
            $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
            $grade = GradeCalculator::calculateGrade($percentage);
            
            $examStatistics[$examId] = [
                'exam' => $exam,
                'results' => $results,
                'total_obtained' => $totalObtained,
                'total_max' => $totalMax,
                'percentage' => $percentage,
                'grade' => $grade,
                'status' => $results->first()->status,
                'formatted_date' => $exam->getFormattedExamDate(),
                'formatted_time' => $exam->getFullDateTime()
            ];
        }

        // Create CSV data
        $csvData = [];
        $csvData[] = ['Exam Title', 'Subject', 'Date', 'Total Marks', 'Obtained Marks', 'Percentage', 'Grade', 'Status'];

        foreach ($examStatistics as $stat) {
            $csvData[] = [
                $stat['exam']->title,
                $stat['exam']->subject->name,
                $stat['formatted_date'],
                $stat['total_max'],
                $stat['total_obtained'],
                number_format($stat['percentage'], 1) . '%',
                $stat['grade'],
                ucfirst($stat['status'])
            ];
        }

        $filename = 'student_results_' . $student->id . '_' . date('Y-m-d') . '.csv';
        
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
}
