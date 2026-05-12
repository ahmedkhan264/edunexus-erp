<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherAttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_attendance_id',
        'teacher_id',
        'current_status',
        'requested_status',
        'reason',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime'
    ];

    /**
     * Get the teacher attendance record.
     */
    public function teacherAttendance()
    {
        return $this->belongsTo(TeacherAttendance::class);
    }

    /**
     * Get the teacher who made the request.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the user who reviewed the request.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the activity logs for this correction request.
     */
    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    /**
     * Scope a query to only include pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
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
            default => 'Unknown'
        };
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the correction request.
     */
    public function approve($reviewerId): bool
    {
        // Update the original attendance record
        $this->teacherAttendance->update(['status' => $this->requested_status]);

        // Update the correction request
        return $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now()
        ]);
    }

    /**
     * Reject the correction request.
     */
    public function reject($reviewerId, $rejectionReason): bool
    {
        return $this->update([
            'status' => 'rejected',
            'rejection_reason' => $rejectionReason,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now()
        ]);
    }

    /**
     * Log activity for this correction request.
     */
    public function logActivity($userId, $action, $description, $properties = null)
    {
        return $this->activityLogs()->create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'properties' => $properties
        ]);
    }
}
