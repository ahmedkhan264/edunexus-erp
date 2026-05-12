<?php

namespace App\Http\Controllers;

use App\Models\FeeChallan;
use App\Models\FeePayment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\FeeReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class PrincipalFeeReportController extends Controller
{
    /**
     * Display the fee recovery report.
     */
    public function index(Request $request): View
    {
        $academicYear = $request->input('academic_year', date('Y') . '-' . (date('Y') + 1));
        $month = $request->input('month');
        
        // Validate academic year format
        if (!preg_match('/^\d{4}-\d{4}$/', $academicYear)) {
            $academicYear = date('Y') . '-' . (date('Y') + 1);
        }
        
        // Validate month
        if ($month && ($month < 1 || $month > 12)) {
            $month = null;
        }
        
        $reportData = FeeReportService::getFeeRecoveryData($academicYear, $month);
        
        return view('principal.reports.fee-recovery', compact(
            'reportData',
            'academicYear',
            'month'
        ));
    }
    
    /**
     * Export fee recovery report to PDF.
     */
    public function exportPdf(Request $request)
    {
        $academicYear = $request->input('academic_year', date('Y') . '-' . (date('Y') + 1));
        $month = $request->input('month');
        
        $reportData = FeeReportService::getFeeRecoveryData($academicYear, $month);
        
        // In a real implementation, you would use DomPDF here
        // For now, returning a mock response
        return response()->json([
            'success' => true,
            'message' => 'PDF export would be generated here',
            'academic_year' => $academicYear,
            'month' => $month,
            'data_summary' => [
                'total_challaned' => $reportData['kpi_cards']['total_challaned'],
                'total_collected' => $reportData['kpi_cards']['total_collected'],
                'total_outstanding' => $reportData['kpi_cards']['total_outstanding'],
                'recovery_percentage' => $reportData['kpi_cards']['recovery_percentage']
            ]
        ]);
    }
    
    /**
     * Export fee recovery report to Excel.
     */
    public function exportExcel(Request $request)
    {
        $academicYear = $request->input('academic_year', date('Y') . '-' . (date('Y') + 1));
        $month = $request->input('month');
        
        $reportData = FeeReportService::getFeeRecoveryData($academicYear, $month);
        
        // In a real implementation, you would use Laravel Excel here
        // For now, returning a mock response
        return response()->json([
            'success' => true,
            'message' => 'Excel export would be generated here',
            'academic_year' => $academicYear,
            'month' => $month,
            'data_summary' => [
                'total_challaned' => $reportData['kpi_cards']['total_challaned'],
                'total_collected' => $reportData['kpi_cards']['total_collected'],
                'total_outstanding' => $reportData['kpi_cards']['total_outstanding'],
                'recovery_percentage' => $reportData['kpi_cards']['recovery_percentage']
            ]
        ]);
    }
}
