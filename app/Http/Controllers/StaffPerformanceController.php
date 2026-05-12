<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Task;
use App\Models\User;
use App\Services\StaffPerformanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class StaffPerformanceController extends Controller
{
    /**
     * Display the staff performance report.
     */
    public function index(Request $request): View
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $department = $request->input('department');
        
        // Validate month and year
        if ($month < 1 || $month > 12) {
            $month = date('n');
        }
        
        if ($year < 2020 || $year > date('Y') + 1) {
            $year = date('Y');
        }
        
        $reportData = StaffPerformanceService::getStaffPerformanceData($month, $year, $department);
        
        return view('principal.reports.staff-performance', compact(
            'reportData',
            'month',
            'year',
            'department'
        ));
    }
    
    /**
     * Export staff performance report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $department = $request->input('department');
        
        $reportData = StaffPerformanceService::getStaffPerformanceData($month, $year, $department);
        
        // In a real implementation, you would use DomPDF here
        // For now, returning a mock response
        return response()->json([
            'success' => true,
            'message' => 'PDF export would be generated here',
            'month' => $month,
            'year' => $year,
            'department' => $department,
            'data_summary' => [
                'total_teachers' => count($reportData['teacher_performance']),
                'avg_attendance' => $reportData['kpi_cards']['avg_teacher_attendance'],
                'avg_tasks_completed' => $reportData['kpi_cards']['avg_tasks_completed'],
                'avg_performance_score' => $reportData['kpi_cards']['avg_performance_score']
            ]
        ]);
    }
    
    /**
     * Export staff performance report to Excel.
     */
    public function exportExcel(Request $request)
    {
        $month = $request->input('month', date('n'));
        $year = $request->input('year', date('Y'));
        $department = $request->input('department');
        
        $reportData = StaffPerformanceService::getStaffPerformanceData($month, $year, $department);
        
        // In a real implementation, you would use Laravel Excel here
        // For now, returning a mock response
        return response()->json([
            'success' => true,
            'message' => 'Excel export would be generated here',
            'month' => $month,
            'year' => $year,
            'department' => $department,
            'data_summary' => [
                'total_teachers' => count($reportData['teacher_performance']),
                'avg_attendance' => $reportData['kpi_cards']['avg_teacher_attendance'],
                'avg_tasks_completed' => $reportData['kpi_cards']['avg_tasks_completed'],
                'avg_performance_score' => $reportData['kpi_cards']['avg_performance_score']
            ]
        ]);
    }
}
