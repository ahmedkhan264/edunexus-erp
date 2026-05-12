<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Services\GradeCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class ParentResultController extends Controller
{
    /**
     * Display the child's results.
     */
    public function show(Student $student, Request $request): View
    {
        $parent = auth()->user()->parentProfile;
        
        // Verify the child belongs to this parent
        if (!$parent->students()->where('students.id', $student->id)->exists()) {
            abort(403, 'You are not authorized to view this child\'s results.');
        }
        
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
        
        // Get selected exam from request
        $selectedExamId = $request->input('exam_id');
        $selectedExam = null;
        
        if ($selectedExamId && isset($examStatistics[$selectedExamId])) {
            $selectedExam = $examStatistics[$selectedExamId];
        } elseif (!empty($examStatistics)) {
            // Default to most recent exam
            $selectedExam = reset($examStatistics);
        }
        
        // Calculate overall statistics
        $overallStats = $this->calculateOverallStats($examStatistics);
        
        // Get exam list for dropdown
        $examList = $this->getExamList($examStatistics);
        
        return view('parent.results', compact(
            'student',
            'examStatistics',
            'selectedExam',
            'overallStats',
            'examList'
        ));
    }
    
    /**
     * Calculate overall statistics for all exams.
     */
    private function calculateOverallStats(array $examStatistics): array
    {
        if (empty($examStatistics)) {
            return [
                'total_exams' => 0,
                'total_marks_obtained' => 0,
                'total_marks_max' => 0,
                'average_percentage' => 0,
                'grade_distribution' => [],
                'passed_exams' => 0,
                'failed_exams' => 0,
                'best_grade' => 'N/A',
                'worst_grade' => 'N/A'
            ];
        }
        
        $totalExams = count($examStatistics);
        $totalMarksObtained = array_sum(array_column($examStatistics, 'total_obtained'));
        $totalMarksMax = array_sum(array_column($examStatistics, 'total_max'));
        $averagePercentage = $totalMarksMax > 0 ? ($totalMarksObtained / $totalMarksMax) * 100 : 0;
        
        // Calculate grade distribution
        $gradeDistribution = [];
        $passedExams = 0;
        $failedExams = 0;
        $grades = [];
        
        foreach ($examStatistics as $stat) {
            $grade = $stat['grade'];
            $grades[] = $grade;
            
            if (!isset($gradeDistribution[$grade])) {
                $gradeDistribution[$grade] = 0;
            }
            $gradeDistribution[$grade]++;
            
            if ($stat['status'] === 'pass') {
                $passedExams++;
            } else {
                $failedExams++;
            }
        }
        
        // Sort grades to find best and worst
        $gradeOrder = ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'F'];
        $sortedGrades = [];
        
        foreach ($grades as $grade) {
            $position = array_search($grade, $gradeOrder);
            if ($position !== false) {
                $sortedGrades[] = $position;
            }
        }
        
        $bestGrade = !empty($sortedGrades) ? $gradeOrder[min($sortedGrades)] : 'N/A';
        $worstGrade = !empty($sortedGrades) ? $gradeOrder[max($sortedGrades)] : 'N/A';
        
        return [
            'total_exams' => $totalExams,
            'total_marks_obtained' => $totalMarksObtained,
            'total_marks_max' => $totalMarksMax,
            'average_percentage' => round($averagePercentage, 1),
            'grade_distribution' => $gradeDistribution,
            'passed_exams' => $passedExams,
            'failed_exams' => $failedExams,
            'best_grade' => $bestGrade,
            'worst_grade' => $worstGrade,
            'pass_rate' => $totalExams > 0 ? ($passedExams / $totalExams) * 100 : 0
        ];
    }
    
    /**
     * Get exam list for dropdown.
     */
    private function getExamList(array $examStatistics): array
    {
        $examList = [];
        
        foreach ($examStatistics as $examId => $stat) {
            $examList[$examId] = [
                'title' => $stat['exam']->title,
                'date' => $stat['formatted_date'],
                'grade' => $stat['grade'],
                'percentage' => round($stat['percentage'], 1)
            ];
        }
        
        return $examList;
    }
    
    /**
     * Export student results.
     */
    public function export(Student $student)
    {
        $parent = auth()->user()->parentProfile;
        
        // Verify the child belongs to this parent
        if (!$parent->students()->where('students.id', $student->id)->exists()) {
            abort(403, 'You are not authorized to export this child\'s results.');
        }
        
        // Get all exam results for the student
        $examResults = ExamResult::where('student_id', $student->id)
            ->with(['exam.schoolClass', 'exam.subject'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('exam_id');
        
        // Create CSV data
        $csvData = [];
        $csvData[] = ['Exam Title', 'Subject', 'Date', 'Total Marks', 'Obtained Marks', 'Percentage', 'Grade', 'Status'];
        
        foreach ($examResults as $examId => $results) {
            $exam = $results->first()->exam;
            
            $totalObtained = $results->sum('marks_obtained');
            $totalMax = $results->sum('total_marks');
            $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
            $grade = GradeCalculator::calculateGrade($percentage);
            
            $csvData[] = [
                $exam->title,
                $exam->subject->name,
                $exam->getFormattedExamDate(),
                $totalMax,
                $totalObtained,
                number_format($percentage, 1) . '%',
                $grade,
                ucfirst($results->first()->status)
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
    
    /**
     * Get performance level based on percentage.
     */
    private function getPerformanceLevel(float $percentage): string
    {
        return GradeCalculator::getPerformanceLevel($percentage);
    }
    
    /**
     * Get performance color based on percentage.
     */
    private function getPerformanceColor(float $percentage): string
    {
        return GradeCalculator::getPerformanceColor($percentage);
    }
    
    /**
     * Get grade remarks.
     */
    private function getGradeRemarks(string $grade): string
    {
        return GradeCalculator::getGradeRemarks($grade);
    }
}
