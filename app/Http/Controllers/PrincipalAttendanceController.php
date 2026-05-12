<?php

namespace App\Http\Controllers;

use App\Models\TeacherAttendance;
use App\Models\PayrollDeduction;
use App\Models\User;
use App\Models\Department;
use App\Services\PayrollDeductionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PrincipalAttendanceController extends Controller
{
    protected $payrollDeductionService;

    public function __construct(PayrollDeductionService $payrollDeductionService)
    {
        $this->payrollDeductionService = $payrollDeductionService;
    }

    /**
     * Show the principal's teacher attendance dashboard.
     */
    public function index(Request $request): View
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $departmentId = $request->get('department_id');
        $deductionStatus = $request->get('deduction_status', 'all');

        // Build query for teacher attendance with deductions
        $query = TeacherAttendance::with(['teacher.department', 'payrollDeductions'])
            ->dateRange($fromDate, $toDate)
            ->whereHas('teacher', function($q) {
                $q->where('is_active', true);
            });

        // Apply department filter
        if ($departmentId) {
            $query->whereHas('teacher', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $attendances = $query->orderBy('date', 'desc')
            ->orderBy('teacher.name')
            ->paginate(20);

        // Calculate summary statistics
        $summary = $this->calculateSummary($fromDate, $toDate, $departmentId);

        // Get deduction summary by type
        $deductionSummary = $this->payrollDeductionService->getDeductionSummaryByType($fromDate, $toDate);

        // Get pending deductions for approval
        $pendingDeductions = $this->payrollDeductionService->getPendingDeductions(10);

        // Get departments for filter
        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('principal.teacher-attendance.index', compact(
            'attendances',
            'summary',
            'deductionSummary',
            'pendingDeductions',
            'departments',
            'fromDate',
            'toDate',
            'departmentId',
            'deductionStatus'
        ));
    }

    /**
     * Show deduction details and approval interface.
     */
    public function deductions(): View
    {
        $pendingDeductions = $this->payrollDeductionService->getPendingDeductions(50);
        $recentDeductions = PayrollDeduction::with(['teacherAttendance.teacher', 'approver'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Get monthly deduction summary
        $thisMonth = now()->format('Y-m');
        $monthlySummary = $this->getMonthlyDeductionSummary($thisMonth);

        return view('principal.teacher-attendance.deductions', compact(
            'pendingDeductions',
            'recentDeductions',
            'monthlySummary'
        ));
    }

    /**
     * Approve a deduction.
     */
    public function approveDeduction(Request $request): JsonResponse
    {
        $deductionId = $request->get('deduction_id');
        $remarks = $request->get('remarks', '');

        $deduction = PayrollDeduction::findOrFail($deductionId);

        if ($deduction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Deduction has already been processed.'
            ], 400);
        }

        $success = $deduction->approve(auth()->id(), $remarks);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Deduction approved successfully!',
                'data' => $deduction->fresh()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to approve deduction.'
        ], 500);
    }

    /**
     * Reject a deduction.
     */
    public function rejectDeduction(Request $request): JsonResponse
    {
        $deductionId = $request->get('deduction_id');
        $remarks = $request->get('remarks', '');

        $deduction = PayrollDeduction::findOrFail($deductionId);

        if ($deduction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Deduction has already been processed.'
            ], 400);
        }

        $success = $deduction->reject(auth()->id(), $remarks);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Deduction rejected successfully!',
                'data' => $deduction->fresh()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to reject deduction.'
        ], 500);
    }

    /**
     * Bulk approve deductions.
     */
    public function bulkApproveDeductions(Request $request): JsonResponse
    {
        $deductionIds = $request->get('deduction_ids', []);
        $remarks = $request->get('remarks', '');

        if (empty($deductionIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No deductions selected.'
            ], 400);
        }

        $approvedCount = 0;
        $failedCount = 0;

        foreach ($deductionIds as $deductionId) {
            $deduction = PayrollDeduction::find($deductionId);
            
            if ($deduction && $deduction->status === 'pending') {
                if ($deduction->approve(auth()->id(), $remarks)) {
                    $approvedCount++;
                } else {
                    $failedCount++;
                }
            } else {
                $failedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Approved {$approvedCount} deductions. {$failedCount} failed.",
            'approved_count' => $approvedCount,
            'failed_count' => $failedCount
        ]);
    }

    /**
     * Get teacher attendance statistics for charts.
     */
    public function getAttendanceStats(Request $request): JsonResponse
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));

        $stats = $this->calculateAttendanceStats($fromDate, $toDate);

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Calculate summary statistics.
     */
    private function calculateSummary(string $fromDate, string $toDate, ?int $departmentId): array
    {
        $query = TeacherAttendance::dateRange($fromDate, $toDate)
            ->whereHas('teacher', function($q) use ($departmentId) {
                $q->where('is_active', true);
                if ($departmentId) {
                    $q->where('department_id', $departmentId);
                }
            });

        $totalRecords = $query->count();
        $presentCount = $query->where('status', 'present')->count();
        $lateCount = $query->where('status', 'late')->count();
        $absentCount = $query->where('status', 'absent')->count();
        $halfDayCount = $query->where('status', 'half_day')->count();

        // Get deduction totals
        $deductionQuery = PayrollDeduction::whereHas('teacherAttendance', function($q) use ($fromDate, $toDate, $departmentId) {
            $q->dateRange($fromDate, $toDate);
            if ($departmentId) {
                $q->whereHas('teacher', function($subQ) use ($departmentId) {
                    $subQ->where('department_id', $departmentId);
                });
            }
        });

        $totalDeductions = $deductionQuery->sum('deduction_amount');
        $pendingDeductions = $deductionQuery->where('status', 'pending')->sum('deduction_amount');
        $approvedDeductions = $deductionQuery->where('status', 'approved')->sum('deduction_amount');

        return [
            'total_records' => $totalRecords,
            'present' => $presentCount,
            'late' => $lateCount,
            'absent' => $absentCount,
            'half_day' => $halfDayCount,
            'attendance_rate' => $totalRecords > 0 ? (($presentCount + $lateCount) / $totalRecords) * 100 : 0,
            'total_deductions' => $totalDeductions,
            'pending_deductions' => $pendingDeductions,
            'approved_deductions' => $approvedDeductions
        ];
    }

    /**
     * Calculate attendance statistics for charts.
     */
    private function calculateAttendanceStats(string $fromDate, string $toDate): array
    {
        $attendances = TeacherAttendance::dateRange($fromDate, $toDate)
            ->with('teacher.department')
            ->get();

        $dailyStats = [];
        $departmentStats = [];
        $deductionStats = [];

        // Daily statistics
        foreach ($attendances as $attendance) {
            $date = $attendance->date;
            
            if (!isset($dailyStats[$date])) {
                $dailyStats[$date] = [
                    'date' => $date,
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'half_day' => 0
                ];
            }
            
            $dailyStats[$date][$attendance->status]++;
        }

        // Department statistics
        foreach ($attendances as $attendance) {
            $deptName = $attendance->teacher->department->name ?? 'Unknown';
            
            if (!isset($departmentStats[$deptName])) {
                $departmentStats[$deptName] = [
                    'department' => $deptName,
                    'present' => 0,
                    'late' => 0,
                    'absent' => 0,
                    'half_day' => 0,
                    'total_deductions' => 0
                ];
            }
            
            $departmentStats[$deptName][$attendance->status]++;
            $departmentStats[$deptName]['total_deductions'] += $attendance->payrollDeductions->sum('deduction_amount');
        }

        return [
            'daily_stats' => array_values($dailyStats),
            'department_stats' => array_values($departmentStats)
        ];
    }

    /**
     * Get monthly deduction summary.
     */
    private function getMonthlyDeductionSummary(string $yearMonth): array
    {
        $deductions = PayrollDeduction::whereHas('teacherAttendance', function($query) use ($yearMonth) {
            $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$yearMonth]);
        })->get();

        $summary = [
            'total_deductions' => $deductions->sum('deduction_amount'),
            'pending_count' => $deductions->where('status', 'pending')->count(),
            'approved_count' => $deductions->where('status', 'approved')->count(),
            'rejected_count' => $deductions->where('status', 'rejected')->count(),
            'by_type' => []
        ];

        foreach ($deductions->groupBy('deduction_type') as $type => $typeDeductions) {
            $summary['by_type'][$type] = [
                'count' => $typeDeductions->count(),
                'amount' => $typeDeductions->sum('deduction_amount')
            ];
        }

        return $summary;
    }
}
