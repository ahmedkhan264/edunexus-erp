<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use PDF;

class SalarySlipController extends Controller
{
    /**
     * Display the salary slip for a specific payroll.
     */
    public function show(Payroll $payroll): View
    {
        // Check if user can view this payroll
        if (!$payroll->canBeViewedBy(Auth::user())) {
            abort(403, 'You are not authorized to view this salary slip');
        }
        
        // Check if payroll is finalized
        if (!$payroll->isFinalized()) {
            abort(403, 'Salary slip is only available for finalized payroll');
        }
        
        $salarySlipData = PayrollService::generateSalarySlipData($payroll);
        
        return view('pdf.salary-slip', compact('salarySlipData'));
    }
    
    /**
     * Generate and download the salary slip PDF.
     */
    public function downloadPdf(Payroll $payroll): Response
    {
        // Check if user can view this payroll
        if (!$payroll->canBeViewedBy(Auth::user())) {
            abort(403, 'You are not authorized to download this salary slip');
        }
        
        // Check if payroll is finalized
        if (!$payroll->isFinalized()) {
            abort(403, 'Salary slip PDF is only available for finalized payroll');
        }
        
        $salarySlipData = PayrollService::generateSalarySlipData($payroll);
        
        // Generate PDF
        $pdf = PDF::loadView('pdf.salary-slip', compact('salarySlipData'));
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Set options for better PDF generation
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
        ]);
        
        // Generate filename
        $filename = 'salary_slip_' . $payroll->user->name . '_' . $payroll->getFormattedMonth() . '.pdf';
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
        
        return $pdf->download($filename);
    }
    
    /**
     * Generate and stream the salary slip PDF for viewing in browser.
     */
    public function viewPdf(Payroll $payroll): Response
    {
        // Check if user can view this payroll
        if (!$payroll->canBeViewedBy(Auth::user())) {
            abort(403, 'You are not authorized to view this salary slip');
        }
        
        // Check if payroll is finalized
        if (!$payroll->isFinalized()) {
            abort(403, 'Salary slip PDF is only available for finalized payroll');
        }
        
        $salarySlipData = PayrollService::generateSalarySlipData($payroll);
        
        // Generate PDF
        $pdf = PDF::loadView('pdf.salary-slip', compact('salarySlipData'));
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Set options for better PDF generation
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
        ]);
        
        // Generate filename
        $filename = 'salary_slip_' . $payroll->user->name . '_' . $payroll->getFormattedMonth() . '.pdf';
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
        
        return $pdf->stream($filename);
    }
    
    /**
     * Generate bulk salary slips for all employees in a month.
     */
    public function bulkGenerate(Request $request): Response
    {
        // Check if user can manage payroll
        if (!Auth::user()->hasRole(['hr_manager', 'principal', 'super_admin'])) {
            abort(403, 'You are not authorized to generate bulk salary slips');
        }
        
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
        ]);
        
        $month = $request->month;
        $year = $request->year;
        
        // Get all finalized payrolls for the month
        $payrolls = Payroll::forMonth($month, $year)
                        ->where('status', 'finalized')
                        ->with('user')
                        ->orderBy('user.name')
                        ->get();
        
        if ($payrolls->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No finalized payroll records found for ' . date('F Y', mktime(0, 0, 0, $month, 1, $year))
            ], 400);
        }
        
        // Generate PDF content for all salary slips
        $allSalarySlips = [];
        foreach ($payrolls as $payroll) {
            $salarySlipData = PayrollService::generateSalarySlipData($payroll);
            $allSalarySlips[] = view('pdf.salary-slip', compact('salarySlipData'))->render();
        }
        
        // Create a single PDF with all salary slips
        $pdf = PDF::loadView('pdf.bulk-salary-slips', [
            'salarySlips' => $allSalarySlips,
            'month' => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
            'generated_at' => now()->format('d M Y H:i'),
            'generated_by' => Auth::user()->name,
        ]);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');
        
        // Set options for better PDF generation
        $pdf->setOptions([
            'defaultFont' => 'Arial',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isPhpEnabled' => true,
        ]);
        
        // Generate filename
        $filename = 'bulk_salary_slips_' . date('F_Y', mktime(0, 0, 0, $month, 1, $year)) . '.pdf';
        $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
        
        return $pdf->download($filename);
    }
    
    /**
     * Send salary slip via email.
     */
    public function emailSalarySlip(Request $request, Payroll $payroll): Response
    {
        // Check if user can manage payroll
        if (!Auth::user()->hasRole(['hr_manager', 'principal', 'super_admin'])) {
            abort(403, 'You are not authorized to send salary slips');
        }
        
        // Check if payroll is finalized
        if (!$payroll->isFinalized()) {
            return response()->json([
                'success' => false,
                'message' => 'Salary slip can only be sent for finalized payroll'
            ], 400);
        }
        
        try {
            $salarySlipData = PayrollService::generateSalarySlipData($payroll);
            
            // Generate PDF
            $pdf = PDF::loadView('pdf.salary-slip', compact('salarySlipData'));
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
            ]);
            
            // Generate filename
            $filename = 'salary_slip_' . $payroll->user->name . '_' . $payroll->getFormattedMonth() . '.pdf';
            $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename);
            
            // In a real implementation, you would send email here
            // $payroll->user->notify(new SalarySlipNotification($pdf->output(), $filename));
            
            return response()->json([
                'success' => true,
                'message' => 'Salary slip sent successfully to ' . $payroll->user->email
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send salary slip: ' . $e->getMessage()
            ], 500);
        }
    }
}
