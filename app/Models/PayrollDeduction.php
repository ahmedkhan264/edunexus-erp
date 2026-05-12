<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_attendance_id',
        'deduction_type',
        'deduction_amount',
        'late_minutes',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approval_remarks'
    ];

    protected $casts = [
        'deduction_amount' => 'decimal:2',
        'late_minutes' => 'integer',
        'approved_at' => 'datetime'
    ];

    /**
     * Get the teacher attendance record.
     */
    public function teacherAttendance()
    {
        return $this->belongsTo(TeacherAttendance::class);
    }

    /**
     * Get the user who approved the deduction.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the teacher through the attendance record.
     */
    public function teacher()
    {
        return $this->hasOneThrough(User::class, TeacherAttendance::class, 'id', 'id', 'teacher_attendance_id', 'teacher_id');
    }

    /**
     * Scope a query to only include pending deductions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved deductions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected deductions.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope a query to only include late arrival deductions.
     */
    public function scopeLateArrival($query)
    {
        return $query->where('deduction_type', 'late_arrival');
    }

    /**
     * Scope a query to only include absenteeism deductions.
     */
    public function scopeAbsenteeism($query)
    {
        return $query->where('deduction_type', 'absenteeism');
    }

    /**
     * Get the deduction type display text.
     */
    public function getDeductionTypeDisplay(): string
    {
        return match($this->deduction_type) {
            'late_arrival' => 'Late Arrival',
            'early_departure' => 'Early Departure',
            'absenteeism' => 'Absenteeism',
            'half_day' => 'Half Day',
            'unauthorized_leave' => 'Unauthorized Leave',
            default => 'Unknown'
        };
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
     * Check if the deduction is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the deduction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the deduction is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the deduction.
     */
    public function approve($approverId, $remarks = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'approval_remarks' => $remarks
        ]);
    }

    /**
     * Reject the deduction.
     */
    public function reject($approverId, $remarks = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'approval_remarks' => $remarks
        ]);
    }
}
