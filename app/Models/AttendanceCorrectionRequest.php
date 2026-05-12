<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'student_id',
        'teacher_id',
        'current_status',
        'requested_status',
        'reason',
        'status',
        'admin_remarks',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime'
    ];

    /**
     * Get the attendance record being corrected.
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the student whose attendance is being corrected.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the teacher who requested the correction.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the admin who reviewed the request.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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
     * Scope a query to only include requests for a specific teacher.
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Check if the request can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending' && $this->attendance;
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
}
