<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the leave requests.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        // Get leave requests based on user role
        $query = LeaveRequest::with(['user', 'approver']);
        
        // HR, Principal, and Super Admin can see all leave requests
        if (!in_array($user->role_id, [1, 2, 8])) { // Not super admin, principal, or HR manager
            // Other users can only see their own leave requests
            $query->where('user_id', $user->id);
        }
        
        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('leave_type') && $request->leave_type !== 'all') {
            $query->where('leave_type', $request->leave_type);
        }
        
        if ($request->has('month') && $request->month !== 'all') {
            $query->whereMonth('start_date', $request->month)
                   ->whereYear('start_date', $request->year ?? now()->year);
        }
        
        $leaveRequests = $query->orderBy('created_at', 'desc')
                             ->paginate(15);
        
        // Get leave types for dropdown
        $leaveTypes = [
            'sick' => 'Sick Leave',
            'casual' => 'Casual Leave',
            'earned' => 'Earned Leave',
            'maternity' => 'Maternity Leave',
            'paternity' => 'Paternity Leave',
        ];
        
        // Get months for dropdown
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        
        return view('hr.leaves.index', compact('leaveRequests', 'leaveTypes', 'months'));
    }
    
    /**
     * Store a newly created leave request in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'leave_type' => 'required|in:sick,casual,earned,maternity,paternity',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
        ]);
        
        // Calculate days
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $days = $startDate->diffInDays($endDate) + 1;
        
        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days' => $days,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);
        
        // Load relationships for response
        $leaveRequest->load(['user', 'approver']);
        
        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully',
            'leave_request' => $leaveRequest
        ]);
    }
    
    /**
     * Approve the specified leave request.
     */
    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        // Check if user can approve leave requests
        if (!$leaveRequest->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to approve this leave request'
            ], 403);
        }
        
        // Check if leave request is pending
        if (!$leaveRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending leave requests can be approved'
            ], 400);
        }
        
        $success = $leaveRequest->approve(Auth::id(), $request->remarks);
        
        if ($success) {
            // Update attendance records for the leave period
            $this->updateAttendanceForLeave($leaveRequest);
            
            // In a real implementation, you would send notification here
            // $leaveRequest->user->notify(new LeaveApprovedNotification($leaveRequest));
            
            return response()->json([
                'success' => true,
                'message' => 'Leave request approved successfully',
                'leave_request' => $leaveRequest->load(['user', 'approver'])
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve leave request'
            ], 500);
        }
    }
    
    /**
     * Reject the specified leave request.
     */
    public function reject(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        // Check if user can reject leave requests
        if (!$leaveRequest->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to reject this leave request'
            ], 403);
        }
        
        // Check if leave request is pending
        if (!$leaveRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending leave requests can be rejected'
            ], 400);
        }
        
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        $success = $leaveRequest->reject(Auth::id(), $request->rejection_reason, $request->remarks);
        
        if ($success) {
            // In a real implementation, you would send notification here
            // $leaveRequest->user->notify(new LeaveRejectedNotification($leaveRequest));
            
            return response()->json([
                'success' => true,
                'message' => 'Leave request rejected successfully',
                'leave_request' => $leaveRequest->load(['user', 'approver'])
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject leave request'
            ], 500);
        }
    }
    
    /**
     * Show the specified leave request.
     */
    public function show(LeaveRequest $leaveRequest): View
    {
        // Check if user can view this leave request
        if (!$leaveRequest->canBeViewedBy(Auth::user())) {
            abort(403, 'You are not authorized to view this leave request');
        }
        
        $leaveRequest->load(['user', 'approver']);
        
        return view('hr.leaves.show', compact('leaveRequest'));
    }
    
    /**
     * Update attendance records for approved leave.
     */
    private function updateAttendanceForLeave(LeaveRequest $leaveRequest): void
    {
        $startDate = $leaveRequest->start_date;
        $endDate = $leaveRequest->end_date;
        
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            // Check if attendance record already exists for this date
            $existingAttendance = Attendance::where('user_id', $leaveRequest->user_id)
                                         ->whereDate('created_at', $currentDate)
                                         ->first();
            
            if ($existingAttendance) {
                // Update existing attendance record
                $existingAttendance->update(['status' => 'leave']);
            } else {
                // Create new attendance record
                Attendance::create([
                    'user_id' => $leaveRequest->user_id,
                    'status' => 'leave',
                    'created_at' => $currentDate,
                    'updated_at' => $currentDate,
                ]);
            }
            
            $currentDate->addDay();
        }
    }
    
    /**
     * Get leave balance summary for a user.
     */
    public function getLeaveBalance(Request $request): JsonResponse
    {
        $userId = $request->user_id ?? Auth::id();
        $year = $request->year ?? now()->year;
        
        // Mock leave balance calculation - would need to be based on actual policy
        $leaveBalances = [
            'sick' => [
                'total' => 12,
                'used' => LeaveRequest::where('user_id', $userId)
                                  ->where('leave_type', 'sick')
                                  ->where('status', 'approved')
                                  ->whereYear('start_date', $year)
                                  ->sum('days'),
                'remaining' => 0,
            ],
            'casual' => [
                'total' => 10,
                'used' => LeaveRequest::where('user_id', $userId)
                                  ->where('leave_type', 'casual')
                                  ->where('status', 'approved')
                                  ->whereYear('start_date', $year)
                                  ->sum('days'),
                'remaining' => 0,
            ],
            'earned' => [
                'total' => 15,
                'used' => LeaveRequest::where('user_id', $userId)
                                  ->where('leave_type', 'earned')
                                  ->where('status', 'approved')
                                  ->whereYear('start_date', $year)
                                  ->sum('days'),
                'remaining' => 0,
            ],
        ];
        
        // Calculate remaining days
        foreach ($leaveBalances as $type => &$balance) {
            $balance['remaining'] = $balance['total'] - $balance['used'];
        }
        
        return response()->json([
            'success' => true,
            'balances' => $leaveBalances
        ]);
    }
}
