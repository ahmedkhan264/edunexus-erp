<?php

namespace App\Http\Controllers;

use App\Models\FeeChallan;
use App\Models\FeePayment;
use App\Models\ParentProfile;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class ParentFeeController extends Controller
{
    /**
     * Display the child's fee status.
     */
    public function show(Student $student): View
    {
        $parent = auth()->user()->parentProfile;
        
        // Verify the child belongs to this parent
        if (!$parent->students()->where('students.id', $student->id)->exists()) {
            abort(403, 'You are not authorized to view this child\'s fee status.');
        }
        
        // Get all fee challans for the student
        $feeChallans = FeeChallan::where('student_id', $student->id)
            ->with(['feePayments'])
            ->orderBy('due_date', 'desc')
            ->get();
        
        // Calculate aggregated totals
        $feeSummary = $this->calculateFeeSummary($feeChallans);
        
        // Group challans by status for better organization
        $groupedChallans = $this->groupChallansByStatus($feeChallans);
        
        // Get payment history
        $paymentHistory = $this->getPaymentHistory($student->id);
        
        return view('parent.fees', compact(
            'student',
            'feeChallans',
            'feeSummary',
            'groupedChallans',
            'paymentHistory'
        ));
    }
    
    /**
     * Calculate aggregated fee totals.
     */
    private function calculateFeeSummary($feeChallans): array
    {
        $totalChallaned = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;
        $totalLateFine = 0;
        $paidChallans = 0;
        $pendingChallans = 0;
        $partialChallans = 0;
        
        foreach ($feeChallans as $challan) {
            $totalChallaned += $challan->amount;
            $totalPaid += $challan->paid_amount;
            $totalLateFine += $challan->late_fine;
            
            $remaining = $challan->amount - $challan->paid_amount;
            $totalOutstanding += $remaining;
            
            // Determine challan status
            if ($challan->paid_amount >= $challan->amount) {
                $paidChallans++;
            } elseif ($challan->paid_amount > 0) {
                $partialChallans++;
            } else {
                $pendingChallans++;
            }
        }
        
        return [
            'total_challaned' => $totalChallaned,
            'total_paid' => $totalPaid,
            'total_outstanding' => $totalOutstanding,
            'total_late_fine' => $totalLateFine,
            'paid_challans' => $paidChallans,
            'pending_challans' => $pendingChallans,
            'partial_challans' => $partialChallans,
            'total_challans' => $feeChallans->count(),
            'payment_rate' => $totalChallaned > 0 ? ($totalPaid / $totalChallaned) * 100 : 0
        ];
    }
    
    /**
     * Group challans by status for better organization.
     */
    private function groupChallansByStatus($feeChallans): array
    {
        $grouped = [
            'paid' => [],
            'partial' => [],
            'pending' => [],
            'overdue' => []
        ];
        
        foreach ($feeChallans as $challan) {
            $status = $this->getChallanStatus($challan);
            $grouped[$status][] = $challan;
        }
        
        return $grouped;
    }
    
    /**
     * Determine challan status.
     */
    private function getChallanStatus(FeeChallan $challan): string
    {
        if ($challan->paid_amount >= $challan->amount) {
            return 'paid';
        } elseif ($challan->paid_amount > 0) {
            return 'partial';
        } elseif ($challan->due_date < Carbon::now()) {
            return 'overdue';
        } else {
            return 'pending';
        }
    }
    
    /**
     * Get payment history for the student.
     */
    private function getPaymentHistory(int $studentId): array
    {
        $payments = FeePayment::whereHas('feeChallan', function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })
        ->with(['feeChallan'])
        ->orderBy('payment_date', 'desc')
        ->take(10) // Last 10 payments
        ->get();
        
        $history = [];
        foreach ($payments as $payment) {
            $history[] = [
                'receipt_number' => $payment->receipt_number ?? 'RCPT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
                'payment_date' => $payment->payment_date ? $payment->payment_date->format('M j, Y') : 'N/A',
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method ?? 'Cash',
                'remarks' => $payment->remarks ?? 'Fee payment',
                'challan_month' => $payment->feeChallan->month ?? 'N/A',
                'challan_year' => $payment->feeChallan->year ?? 'N/A'
            ];
        }
        
        return $history;
    }
    
    /**
     * Download challan PDF.
     */
    public function downloadChallan(FeeChallan $challan)
    {
        $parent = auth()->user()->parentProfile;
        
        // Verify the challan belongs to a child of this parent
        if (!$parent->students()->where('students.id', $challan->student_id)->exists()) {
            abort(403, 'You are not authorized to download this challan.');
        }
        
        // In a real implementation, you would generate and return the PDF
        // For now, let's return a mock response
        return response()->json([
            'success' => true,
            'message' => 'Challan download would be implemented here',
            'challan_id' => $challan->id
        ]);
    }
    
    /**
     * Download fee ledger PDF.
     */
    public function downloadLedger(Student $student)
    {
        $parent = auth()->user()->parentProfile;
        
        // Verify the child belongs to this parent
        if (!$parent->students()->where('students.id', $student->id)->exists()) {
            abort(403, 'You are not authorized to download this fee ledger.');
        }
        
        // In a real implementation, you would generate and return the PDF ledger
        // For now, let's return a mock response
        return response()->json([
            'success' => true,
            'message' => 'Fee ledger download would be implemented here',
            'student_id' => $student->id
        ]);
    }
    
    /**
     * Get status display text.
     */
    private function getStatusDisplay(string $status): string
    {
        return match($status) {
            'paid' => 'Paid',
            'partial' => 'Partial',
            'pending' => 'Pending',
            'overdue' => 'Overdue',
            default => 'Unknown'
        };
    }
    
    /**
     * Get status color for display.
     */
    private function getStatusColor(string $status): string
    {
        return match($status) {
            'paid' => 'success',
            'partial' => 'warning',
            'pending' => 'info',
            'overdue' => 'danger',
            default => 'secondary'
        };
    }
    
    /**
     * Get payment method display.
     */
    private function getPaymentMethodDisplay(string $method): string
    {
        return match($method) {
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque',
            'online' => 'Online Payment',
            'credit_card' => 'Credit Card',
            default => ucfirst($method)
        };
    }
}
