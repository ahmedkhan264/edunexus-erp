<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    /**
     * Display a listing of the tasks.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        
        // Get all tasks with relationships
        $query = Task::with(['assignee', 'assigner']);
        
        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->status($request->status);
        }
        
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->priority($request->priority);
        }
        
        if ($request->has('assignee') && $request->assignee !== 'all') {
            $query->assignedTo($request->assignee);
        }
        
        // Principal can see all tasks, others see only their assigned tasks
        if ($user->role_id !== 2) { // Not principal
            $query->assignedTo($user->id);
        }
        
        $tasks = $query->orderBy('due_date', 'asc')
                    ->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);
        
        // Get users for assignee dropdown
        $assignees = User::where('status', 'active')
                      ->whereIn('role_id', [3, 4]) // Teachers and staff
                      ->orderBy('name')
                      ->get();
        
        return view('principal.tasks.index', compact('tasks', 'assignees'));
    }
    
    /**
     * Store a newly created task in storage.
     */
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'assigned_by' => Auth::id(),
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status' => 'pending',
            'remarks' => $request->remarks,
        ]);
        
        // Load relationships for response
        $task->load(['assignee', 'assigner']);
        
        // In a real implementation, you would send notification here
        // $task->assignee->notify(new TaskAssignedNotification($task));
        
        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'task' => $task
        ]);
    }
    
    /**
     * Update the specified task in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        // Check if user can update this task
        if (!$task->canBeUpdatedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this task'
            ], 403);
        }
        
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'remarks' => $request->remarks,
        ]);
        
        // Load relationships for response
        $task->load(['assignee', 'assigner']);
        
        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'task' => $task
        ]);
    }
    
    /**
     * Update the status of the specified task.
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        // Check if user can update this task status
        if (!$task->canBeUpdatedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this task status'
            ], 403);
        }
        
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,overdue'
        ]);
        
        $oldStatus = $task->status;
        $task->updateStatus($request->status);
        
        // Load relationships for response
        $task->load(['assignee', 'assigner']);
        
        // In a real implementation, you would send notification here
        // if ($oldStatus !== $task->status) {
        //     $task->assigner->notify(new TaskStatusChangedNotification($task, $oldStatus));
        // }
        
        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully',
            'task' => $task
        ]);
    }
    
    /**
     * Remove the specified task from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        // Only principal or task creator can delete
        if (!Auth::user()->isPrincipal() && $task->assigned_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this task'
            ], 403);
        }
        
        $task->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }
    
    /**
     * Get task statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $user = Auth::user();
        
        $query = Task::query();
        
        // Principal can see all tasks, others see only their assigned tasks
        if ($user->role_id !== 2) { // Not principal
            $query->assignedTo($user->id);
        }
        
        $stats = [
            'total' => $query->count(),
            'pending' => $query->status('pending')->count(),
            'in_progress' => $query->status('in_progress')->count(),
            'completed' => $query->status('completed')->count(),
            'overdue' => $query->overdue()->count(),
            'urgent' => $query->priority('urgent')->count(),
            'high' => $query->priority('high')->count(),
            'medium' => $query->priority('medium')->count(),
            'low' => $query->priority('low')->count(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
