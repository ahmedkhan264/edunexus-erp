<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingParticipant;
use App\Models\User;
use App\Http\Requests\StoreMeetingRequest;
use App\Http\Requests\UpdateMeetingRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MeetingController extends Controller
{
    /**
     * Display a listing of the meetings.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        // Get meetings based on user role
        $query = Meeting::with(['creator', 'participants.user']);
        
        // Principal and admin can see all meetings
        if (!in_array($user->role_id, [1, 2])) { // Not super admin or principal
            // Other users can only see meetings they are invited to
            $query->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        // Apply date filter
        if ($request->has('date_filter')) {
            $dateFilter = $request->date_filter;
            if ($dateFilter === 'today') {
                $query->today();
            } elseif ($dateFilter === 'upcoming') {
                $query->upcoming();
            } elseif ($dateFilter === 'past') {
                $query->past();
            }
        } else {
            // Default to upcoming meetings first
            $query->upcoming();
        }
        
        $meetings = $query->orderBy('meeting_date', 'asc')
                       ->orderBy('start_time', 'asc')
                       ->paginate(15);
        
        // Get users for participant dropdown
        $users = User::where('status', 'active')
                     ->whereIn('role_id', [3, 4]) // Teachers and staff
                     ->orderBy('name')
                     ->get();
        
        return view('principal.meetings.index', compact('meetings', 'users'));
    }
    
    /**
     * Store a newly created meeting in storage.
     */
    public function store(StoreMeetingRequest $request): JsonResponse
    {
        $meeting = Meeting::create([
            'title' => $request->title,
            'description' => $request->description,
            'agenda' => $request->agenda,
            'meeting_date' => $request->meeting_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'created_by' => Auth::id(),
            'send_reminder_24h' => $request->has('send_reminder_24h'),
            'send_reminder_1h' => $request->has('send_reminder_1h'),
        ]);
        
        // Add participants
        if ($request->has('participants')) {
            foreach ($request->participants as $participantId) {
                $meeting->addParticipant($participantId);
                
                // In a real implementation, you would send notification here
                // $participant = User::find($participantId);
                // $participant->notify(new MeetingInvitationNotification($meeting));
            }
        }
        
        // Load relationships for response
        $meeting->load(['creator', 'participants.user']);
        
        return response()->json([
            'success' => true,
            'message' => 'Meeting created successfully',
            'meeting' => $meeting
        ]);
    }
    
    /**
     * Display the specified meeting.
     */
    public function show(Meeting $meeting): View
    {
        $user = Auth::user();
        
        // Check if user can view this meeting
        if (!$meeting->canBeViewedBy($user)) {
            abort(403, 'You are not authorized to view this meeting');
        }
        
        $meeting->load(['creator', 'participants.user']);
        
        // Get users for participant dropdown
        $users = User::where('status', 'active')
                     ->whereIn('role_id', [3, 4]) // Teachers and staff
                     ->orderBy('name')
                     ->get();
        
        return view('principal.meetings.show', compact('meeting', 'users'));
    }
    
    /**
     * Update the specified meeting in storage.
     */
    public function update(UpdateMeetingRequest $request, Meeting $meeting): JsonResponse
    {
        // Check if user can update this meeting
        if (!$meeting->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this meeting'
            ], 403);
        }
        
        $meeting->update([
            'title' => $request->title,
            'description' => $request->description,
            'agenda' => $request->agenda,
            'meeting_date' => $request->meeting_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'send_reminder_24h' => $request->has('send_reminder_24h'),
            'send_reminder_1h' => $request->has('send_reminder_1h'),
        ]);
        
        // Update participants if provided
        if ($request->has('participants')) {
            // Remove existing participants
            $meeting->participants()->delete();
            
            // Add new participants
            foreach ($request->participants as $participantId) {
                $meeting->addParticipant($participantId);
                
                // In a real implementation, you would send notification here
                // $participant = User::find($participantId);
                // $participant->notify(new MeetingUpdatedNotification($meeting));
            }
        }
        
        // Load relationships for response
        $meeting->load(['creator', 'participants.user']);
        
        return response()->json([
            'success' => true,
            'message' => 'Meeting updated successfully',
            'meeting' => $meeting
        ]);
    }
    
    /**
     * Remove the specified meeting from storage.
     */
    public function destroy(Meeting $meeting): JsonResponse
    {
        // Check if user can delete this meeting
        if (!$meeting->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this meeting'
            ], 403);
        }
        
        $meeting->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Meeting deleted successfully'
        ]);
    }
    
    /**
     * Mark attendance for meeting participants.
     */
    public function markAttendance(Request $request, Meeting $meeting): JsonResponse
    {
        // Check if user can manage this meeting
        if (!$meeting->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage this meeting'
            ], 403);
        }
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:present,absent',
            'remarks' => 'nullable|string|max:500'
        ]);
        
        $success = $meeting->markAttendance(
            $request->user_id,
            $request->status,
            $request->remarks
        );
        
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance'
            ], 400);
        }
    }
    
    /**
     * Save meeting minutes.
     */
    public function saveMinutes(Request $request, Meeting $meeting): JsonResponse
    {
        // Check if user can manage this meeting
        if (!$meeting->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage this meeting'
            ], 403);
        }
        
        $request->validate([
            'minutes' => 'required|string|max:5000'
        ]);
        
        $meeting->update(['minutes' => $request->minutes]);
        
        return response()->json([
            'success' => true,
            'message' => 'Meeting minutes saved successfully'
        ]);
    }
    
    /**
     * Get meeting statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $user = Auth::user();
        
        $query = Meeting::query();
        
        // Principal and admin can see all meetings
        if (!in_array($user->role_id, [1, 2])) { // Not super admin or principal
            // Other users can only see meetings they are invited to
            $query->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $stats = [
            'total' => $query->count(),
            'today' => $query->today()->count(),
            'upcoming' => $query->upcoming()->count(),
            'past' => $query->past()->count(),
            'in_progress' => $query->where('meeting_date', now()->toDateString())->count(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
