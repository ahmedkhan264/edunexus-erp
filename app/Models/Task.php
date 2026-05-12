<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'assigned_by',
        'due_date',
        'priority',
        'status',
        'completed_at',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who assigned the task.
     */
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user who is assigned the task.
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope a query to only include tasks with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include tasks with a specific priority.
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope a query to only include overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', Carbon::today())
                   ->where('status', '!=', 'completed');
    }

    /**
     * Scope a query to only include tasks assigned to a specific user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope a query to only include tasks assigned by a specific user.
     */
    public function scopeAssignedBy($query, $userId)
    {
        return $query->where('assigned_by', $userId);
    }

    /**
     * Scope a query to only include tasks due within a specific number of days.
     */
    public function scopeDueWithin($query, $days)
    {
        return $query->where('due_date', '<=', Carbon::now()->addDays($days))
                   ->where('due_date', '>=', Carbon::today())
                   ->where('status', '!=', 'completed');
    }

    /**
     * Check if the task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date < Carbon::today() && $this->status !== 'completed';
    }

    /**
     * Check if the task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get the priority color for display.
     */
    public function getPriorityColor(): string
    {
        return match($this->priority) {
            'low' => 'success',
            'medium' => 'info',
            'high' => 'warning',
            'urgent' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'in_progress' => 'info',
            'completed' => 'success',
            'overdue' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get the priority display text.
     */
    public function getPriorityDisplay(): string
    {
        return match($this->priority) {
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
            default => 'Medium'
        };
    }

    /**
     * Get the status display text.
     */
    public function getStatusDisplay(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'overdue' => 'Overdue',
            default => 'Pending'
        };
    }

    /**
     * Get the formatted due date.
     */
    public function getFormattedDueDate(): string
    {
        return $this->due_date ? $this->due_date->format('M j, Y') : 'N/A';
    }

    /**
     * Get the number of days until due.
     */
    public function getDaysUntilDue(): int
    {
        if (!$this->due_date) {
            return 0;
        }
        
        return Carbon::today()->diffInDays($this->due_date, false);
    }

    /**
     * Get the due date status text.
     */
    public function getDueDateStatus(): string
    {
        if (!$this->due_date) {
            return 'No due date';
        }

        if ($this->isCompleted()) {
            return 'Completed';
        }

        $daysUntilDue = $this->getDaysUntilDue();

        if ($daysUntilDue < 0) {
            return abs($daysUntilDue) . ' days overdue';
        } elseif ($daysUntilDue === 0) {
            return 'Due today';
        } elseif ($daysUntilDue === 1) {
            return 'Due tomorrow';
        } elseif ($daysUntilDue <= 7) {
            return $daysUntilDue . ' days left';
        } else {
            return $daysUntilDue . ' days left';
        }
    }

    /**
     * Mark the task as completed.
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Update the task status.
     */
    public function updateStatus(string $status): bool
    {
        $updateData = ['status' => $status];
        
        if ($status === 'completed') {
            $updateData['completed_at'] = now();
        }
        
        return $this->update($updateData);
    }

    /**
     * Check if the task can be updated by the current user.
     */
    public function canBeUpdatedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Principal can update all tasks
        if ($user->role_id === 2) { // Assuming role_id 2 is principal
            return true;
        }

        // Assignee can update status of their own tasks
        if ($this->assigned_to === $user->id) {
            return true;
        }

        // Creator can update their own tasks
        if ($this->assigned_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Get the task duration in days.
     */
    public function getDuration(): int
    {
        if (!$this->due_date || !$this->created_at) {
            return 0;
        }

        return $this->created_at->diffInDays($this->due_date);
    }

    /**
     * Get the task completion percentage (mock implementation).
     */
    public function getCompletionPercentage(): int
    {
        return match($this->status) {
            'completed' => 100,
            'in_progress' => 50,
            'pending' => 0,
            'overdue' => 0,
            default => 0
        };
    }
}
