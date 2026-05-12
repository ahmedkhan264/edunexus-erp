<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MeetingParticipant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'meeting_id',
        'user_id',
        'attendance_status',
        'attended_at',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'attended_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the meeting that the participant belongs to.
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Get the user that is the participant.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include present participants.
     */
    public function scopePresent($query)
    {
        return $query->where('attendance_status', 'present');
    }

    /**
     * Scope a query to only include absent participants.
     */
    public function scopeAbsent($query)
    {
        return $query->where('attendance_status', 'absent');
    }

    /**
     * Scope a query to only include pending participants.
     */
    public function scopePending($query)
    {
        return $query->where('attendance_status', 'pending');
    }

    /**
     * Check if the participant is present.
     */
    public function isPresent(): bool
    {
        return $this->attendance_status === 'present';
    }

    /**
     * Check if the participant is absent.
     */
    public function isAbsent(): bool
    {
        return $this->attendance_status === 'absent';
    }

    /**
     * Check if the participant's attendance is pending.
     */
    public function isPending(): bool
    {
        return $this->attendance_status === 'pending';
    }

    /**
     * Get the attendance status display text.
     */
    public function getStatusDisplay(): string
    {
        return match($this->attendance_status) {
            'present' => 'Present',
            'absent' => 'Absent',
            'pending' => 'Pending',
            default => 'Pending'
        };
    }

    /**
     * Get the attendance status color for display.
     */
    public function getStatusColor(): string
    {
        return match($this->attendance_status) {
            'present' => 'success',
            'absent' => 'danger',
            'pending' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Mark the participant as present.
     */
    public function markPresent(?string $remarks = null): bool
    {
        return $this->update([
            'attendance_status' => 'present',
            'attended_at' => now(),
            'remarks' => $remarks,
        ]);
    }

    /**
     * Mark the participant as absent.
     */
    public function markAbsent(?string $remarks = null): bool
    {
        return $this->update([
            'attendance_status' => 'absent',
            'remarks' => $remarks,
        ]);
    }

    /**
     * Reset attendance to pending.
     */
    public function resetAttendance(): bool
    {
        return $this->update([
            'attendance_status' => 'pending',
            'attended_at' => null,
        ]);
    }
}
