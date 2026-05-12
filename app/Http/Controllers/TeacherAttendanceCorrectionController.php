<?php

namespace App\Http\Controllers;

use App\Models\TeacherAttendanceCorrectionRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TeacherAttendanceCorrectionController extends Controller
{
    /**
     * Display the correction requests page.
     */
    public function index(Request $request): View
    {
        $status = $request->get('status', 'pending');
        
        $query = TeacherAttendanceCorrectionRequest::with([
            'teacherAttendance',
            'teacher',
            'reviewer'
        ]);

        // Apply status filter
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $corrections = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        // Get counts for each status
        $counts = [
            'pending' => TeacherAttendanceCorrectionRequest::pending()->count(),
            'approved' => TeacherAttendanceCorrectionRequest::approved()->count(),
            'rejected' => TeacherAttendanceCorrectionRequest::rejected()->count(),
            'all' => TeacherAttendanceCorrectionRequest::count()
        ];

        return view('hr.teacher-attendance.corrections', compact(
            'corrections',
            'status',
            'counts'
        ));
    }

    /**
     * Approve a correction request.
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $correction = TeacherAttendanceCorrectionRequest::findOrFail($id);

        if ($correction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been processed.'
            ], 400);
        }

        // Approve the correction
        $success = $correction->approve(auth()->id());

        if ($success) {
            // Log the activity
            $correction->logActivity(
                auth()->id(),
                'approved',
                "Correction request approved by " . auth()->user()->name,
                [
                    'original_status' => $correction->current_status,
                    'new_status' => $correction->requested_status,
                    'approved_by' => auth()->id()
                ]
            );

            // Send notification to teacher
            $this->sendNotificationToTeacher($correction, 'approved');

            return response()->json([
                'success' => true,
                'message' => 'Correction request approved successfully!',
                'data' => $correction->fresh()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to approve correction request.'
        ], 500);
    }

    /**
     * Reject a correction request.
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $correction = TeacherAttendanceCorrectionRequest::findOrFail($id);

        if ($correction->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been processed.'
            ], 400);
        }

        $request->validate([
            'rejection_reason' => 'required|string|min:10'
        ]);

        // Reject the correction
        $success = $correction->reject(auth()->id(), $request->rejection_reason);

        if ($success) {
            // Log the activity
            $correction->logActivity(
                auth()->id(),
                'rejected',
                "Correction request rejected by " . auth()->user()->name,
                [
                    'original_status' => $correction->current_status,
                    'requested_status' => $correction->requested_status,
                    'rejection_reason' => $request->rejection_reason,
                    'rejected_by' => auth()->id()
                ]
            );

            // Send notification to teacher
            $this->sendNotificationToTeacher($correction, 'rejected');

            return response()->json([
                'success' => true,
                'message' => 'Correction request rejected successfully!',
                'data' => $correction->fresh()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to reject correction request.'
        ], 500);
    }

    /**
     * Send notification to the teacher about the correction request status.
     */
    private function sendNotificationToTeacher(TeacherAttendanceCorrectionRequest $correction, string $action): void
    {
        $teacher = $correction->teacher;
        $title = $action === 'approved' ? 'Attendance Correction Approved' : 'Attendance Correction Rejected';
        
        $message = $action === 'approved' 
            ? "Your attendance correction request for {$correction->teacherAttendance->date} has been approved."
            : "Your attendance correction request for {$correction->teacherAttendance->date} has been rejected. Reason: {$correction->rejection_reason}";

        // Create notification (assuming you have a notification system)
        $teacher->notifications()->create([
            'title' => $title,
            'message' => $message,
            'type' => 'attendance_correction',
            'data' => [
                'correction_id' => $correction->id,
                'date' => $correction->teacherAttendance->date,
                'action' => $action
            ]
        ]);
    }

    /**
     * Refresh the corrections table with AJAX.
     */
    public function refresh(Request $request): JsonResponse
    {
        $status = $request->get('status', 'pending');
        
        $query = TeacherAttendanceCorrectionRequest::with([
            'teacherAttendance',
            'teacher',
            'reviewer'
        ]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $corrections = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        $html = view('hr.teacher-attendance.partials.corrections-table', compact('corrections'))->render();

        return response()->json([
            'success' => true,
            'html' => $html
        ]);
    }
}
