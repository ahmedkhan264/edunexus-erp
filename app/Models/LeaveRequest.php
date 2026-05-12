<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'days',
        'reason',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the leave request.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who approved the leave request.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope a query to only include pending leave requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved leave requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected leave requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include leave requests for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include leave requests within a date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($subQ) use ($startDate, $endDate) {
                  $subQ->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }

    /**
     * Check if the leave request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the leave request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the leave request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Get the leave type display text.
     */
    public function getLeaveTypeDisplay(): string
    {
        return match($this->leave_type) {
            'sick' => 'Sick Leave',
            'casual' => 'Casual Leave',
            'earned' => 'Earned Leave',
            'maternity' => 'Maternity Leave',
            'paternity' => 'Paternity Leave',
            default => ucfirst($this->leave_type)
        };
    }

    /**
     * Get the status display text.
     */
    public function getStatusDisplay(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get the leave type color for display.
     */
    public function getLeaveTypeColor(): string
    {
        return match($this->leave_type) {
            'sick' => 'danger',
            'casual' => 'info',
            'earned' => 'success',
            'maternity' => 'primary',
            'paternity' => 'secondary',
            default => 'secondary'
        };
    }

    /**
     * Get the formatted date range.
     */
    public function getFormattedDateRange(): string
    {
        if ($this->start_date->format('Y-m-d') === $this->end_date->format('Y-m-d')) {
            return $this->start_date->format('M j, Y');
        }

        return $this->start_date->format('M j') . ' - ' . $this->end_date->format('M j, Y');
    }

    /**
     * Check if the leave request is currently active.
     */
    public function isActive(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        $today = Carbon::today();
        return $today->between($this->start_date, $this->end_date);
    }

    /**
     * Check if the leave request is upcoming.
     */
    public function isUpcoming(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        return $this->start_date > Carbon::today();
    }

    /**
     * Check if the leave request is past.
     */
    public function isPast(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        return $this->end_date < Carbon::today();
    }

    /**
     * Approve the leave request.
     */
    public function approve(int $approvedBy, ?string $remarks = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'remarks' => $remarks,
        ]);
    }

    /**
     * Reject the leave request.
     */
    public function reject(int $approvedBy, string $rejectionReason, ?string $remarks = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'rejection_reason' => $rejectionReason,
            'remarks' => $remarks,
        ]);
    }

    /**
     * Check if a user can manage this leave request.
     */
    public function canBeManagedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // HR Manager, Principal, and Super Admin can manage all leave requests
        if (in_array($user->role_id, [1, 2, 8])) { // Super admin, principal, HR manager
            return true;
        }

        return false;
    }

    /**
     * Check if a user can view this leave request.
     */
    public function canBeViewedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // If user can manage, they can view
        if ($this->canBeManagedBy($user)) {
            return true;
        }

        // User can view their own leave requests
        if ($this->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Get the remaining days for this leave request.
     */
    public function getRemainingDays(): int
    {
        if (!$this->isApproved() || $this->isPast()) {
            return 0;
        }

        $today = Carbon::today();
        $endDate = $this->end_date;
        
        if ($this->isActive()) {
            return $endDate->diffInDays($today) + 1;
        }

        if ($this->isUpcoming()) {
            return $this->days;
        }

        return 0;
    }
}
