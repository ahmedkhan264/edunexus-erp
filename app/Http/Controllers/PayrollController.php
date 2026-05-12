<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    /**
     * Display the payroll processing page.
     */
    public function index(Request $request): View
    {
        $month = $request->month ?? now()->subMonth()->month;
        $year = $request->year ?? now()->subMonth()->year;
        
        // Get existing payroll records for the selected month
        $existingPayrolls = Payroll::forMonth($month, $year)
                                ->with(['user', 'processor', 'finalizer'])
                                ->get();
        
        // Get employee roles (excluding students and parents)
        $employeeRoles = [2, 3, 4, 5, 6, 7, 8, 9]; // Principal, Admin, Teacher, Student, Parent, Accountant, HR Manager, Librarian, Timetable Coordinator
        $employees = User::whereIn('role_id', $employeeRoles)
                      ->where('status', 'active')
                      ->orderBy('name')
                      ->get();
        
        // Check if payroll is already finalized for this month
        $isFinalized = $existingPayrolls->where('status', 'finalized')->count() > 0;
        
        // Get months and years for dropdowns
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        
        $years = range(now()->year - 2, now()->year + 1);
        
        return view('hr.payroll.index', compact(
            'existingPayrolls',
            'employees',
            'month',
            'year',
            'isFinalized',
            'months',
            'years'
        ));
    }
    
    /**
     * Process payroll for the specified month.
     */
    public function processMonth(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
        ]);
        
        $month = $request->month;
        $year = $request->year;
        
        // Check if payroll is already finalized for this month
        $finalizedCount = Payroll::forMonth($month, $year)
                              ->where('status', 'finalized')
                              ->count();
        
        if ($finalizedCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll for ' . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . ' is already finalized'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Get employee roles
            $employeeRoles = [2, 3, 4, 5, 6, 7, 8, 9];
            $employees = User::whereIn('role_id', $employeeRoles)
                          ->where('status', 'active')
                          ->get();
            
            $processedCount = 0;
            $errors = [];
            
            foreach ($employees as $employee) {
                try {
                    $payrollData = PayrollService::calculatePayroll($employee, $month, $year);
                    
                    // Create or update payroll record
                    Payroll::updateOrCreate(
                        [
                            'user_id' => $employee->id,
                            'month' => $month,
                            'year' => $year,
                        ],
                        [
                            'basic_salary' => $payrollData['basic_salary'],
                            'allowances' => $payrollData['allowances'],
                            'deductions' => $payrollData['deductions'],
                            'net_salary' => $payrollData['net_salary'],
                            'working_days' => $payrollData['working_days'],
                            'present_days' => $payrollData['present_days'],
                            'absent_days' => $payrollData['absent_days'],
                            'late_days' => $payrollData['late_days'],
                            'leave_days' => $payrollData['leave_days'],
                            'status' => 'draft',
                            'processed_by' => Auth::id(),
                            'processed_at' => now(),
                        ]
                    );
                    
                    $processedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Error processing payroll for {$employee->name}: " . $e->getMessage();
                }
            }
            
            DB::commit();
            
            if ($processedCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Payroll processed successfully for {$processedCount} employees",
                    'processed_count' => $processedCount,
                    'errors' => $errors
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No employees were processed',
                    'errors' => $errors
                ], 400);
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payroll: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Finalize payroll for the specified month.
     */
    public function finalize(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'remarks' => 'nullable|string|max:500',
        ]);
        
        $month = $request->month;
        $year = $request->year;
        
        // Get draft payrolls for the month
        $draftPayrolls = Payroll::forMonth($month, $year)
                            ->where('status', 'draft')
                            ->get();
        
        if ($draftPayrolls->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No draft payroll records found for ' . date('F Y', mktime(0, 0, 0, $month, 1, $year))
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            $finalizedCount = 0;
            
            foreach ($draftPayrolls as $payroll) {
                if ($payroll->finalize(Auth::id(), $request->remarks)) {
                    $finalizedCount++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Payroll finalized successfully for {$finalizedCount} employees",
                'finalized_count' => $finalizedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to finalize payroll: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get payroll summary for the specified month.
     */
    public function getSummary(Request $request): JsonResponse
    {
        $month = $request->month ?? now()->subMonth()->month;
        $year = $request->year ?? now()->subMonth()->year;
        
        $payrolls = Payroll::forMonth($month, $year)->get();
        
        $summary = [
            'total_employees' => $payrolls->count(),
            'total_basic_salary' => $payrolls->sum('basic_salary'),
            'total_allowances' => $payrolls->sum('allowances'),
            'total_deductions' => $payrolls->sum('deductions'),
            'total_net_salary' => $payrolls->sum('net_salary'),
            'draft_count' => $payrolls->where('status', 'draft')->count(),
            'finalized_count' => $payrolls->where('status', 'finalized')->count(),
            'average_salary' => $payrolls->avg('net_salary'),
            'total_working_days' => $payrolls->sum('working_days'),
            'total_present_days' => $payrolls->sum('present_days'),
            'total_absent_days' => $payrolls->sum('absent_days'),
            'total_late_days' => $payrolls->sum('late_days'),
            'total_leave_days' => $payrolls->sum('leave_days'),
        ];
        
        $summary['attendance_rate'] = $summary['total_working_days'] > 0 
            ? ($summary['total_present_days'] / $summary['total_working_days']) * 100 
            : 0;
        
        return response()->json([
            'success' => true,
            'summary' => $summary
        ]);
    }
    
    /**
     * Show the payroll details for a specific employee.
     */
    public function show(Payroll $payroll): View
    {
        // Check if user can view this payroll
        if (!$payroll->canBeViewedBy(Auth::user())) {
            abort(403, 'You are not authorized to view this payroll');
        }
        
        $payroll->load(['user', 'processor', 'finalizer']);
        
        // Get attendance details for the month
        $attendanceDetails = Attendance::where('user_id', $payroll->user_id)
                                    ->whereMonth('created_at', $payroll->month)
                                    ->whereYear('created_at', $payroll->year)
                                    ->orderBy('created_at')
                                    ->get();
        
        // Get leave details for the month
        $leaveDetails = LeaveRequest::where('user_id', $payroll->user_id)
                                ->where('status', 'approved')
                                ->whereMonth('start_date', $payroll->month)
                                ->whereYear('start_date', $payroll->year)
                                ->get();
        
        return view('hr.payroll.show', compact('payroll', 'attendanceDetails', 'leaveDetails'));
    }
    
    /**
     * Update payroll record.
     */
    public function update(Request $request, Payroll $payroll): JsonResponse
    {
        // Check if user can manage this payroll
        if (!$payroll->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this payroll'
            ], 403);
        }
        
        // Check if payroll can be edited
        if (!$payroll->canBeEdited()) {
            return response()->json([
                'success' => false,
                'message' => 'Finalized payroll cannot be edited'
            ], 400);
        }
        
        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);
        
        $payroll->update([
            'basic_salary' => $request->basic_salary,
            'allowances' => $request->allowances,
            'remarks' => $request->remarks,
        ]);
        
        // Recalculate deductions and net salary
        $payroll->recalculate();
        
        return response()->json([
            'success' => true,
            'message' => 'Payroll updated successfully',
            'payroll' => $payroll->load(['user', 'processor', 'finalizer'])
        ]);
    }
    
    /**
     * Delete payroll record.
     */
    public function destroy(Payroll $payroll): JsonResponse
    {
        // Check if user can manage this payroll
        if (!$payroll->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this payroll'
            ], 403);
        }
        
        // Check if payroll can be deleted
        if ($payroll->isFinalized()) {
            return response()->json([
                'success' => false,
                'message' => 'Finalized payroll cannot be deleted'
            ], 400);
        }
        
        $payroll->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Payroll deleted successfully'
        ]);
    }
}
