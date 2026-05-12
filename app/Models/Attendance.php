<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $table = 'attendance';
    
    protected $fillable = [
        'user_id',
        'class_id',
        'date',
        'status',
        'check_in_time',
        'check_out_time',
        'marked_by',
        'remarks',
        'attendance_method',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function markedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    // Scopes for common queries
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeHoliday($query)
    {
        return $query->where('status', 'holiday');
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeManual($query)
    {
        return $query->where('attendance_method', 'manual');
    }

    public function scopeBarcode($query)
    {
        return $query->where('attendance_method', 'barcode');
    }

    public function scopeApi($query)
    {
        return $query->where('attendance_method', 'api');
    }

    // Helper methods
    public function isPresent(): bool
    {
        return $this->status === 'present';
    }

    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }

    public function isLate(): bool
    {
        return $this->status === 'late';
    }

    public function isHoliday(): bool
    {
        return $this->status === 'holiday';
    }

    public function getDurationAttribute(): ?string
    {
        if ($this->check_in_time && $this->check_out_time) {
            $duration = $this->check_in_time->diff($this->check_out_time);
            return $duration->format('%H:%I:%S');
        }
        return null;
    }

    public function getFormattedCheckInAttribute(): string
    {
        return $this->check_in_time ? $this->check_in_time->format('h:i A') : 'N/A';
    }

    public function getFormattedCheckOutAttribute(): string
    {
        return $this->check_out_time ? $this->check_out_time->format('h:i A') : 'N/A';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'present' => '<span class="badge bg-success">Present</span>',
            'absent' => '<span class="badge bg-danger">Absent</span>',
            'late' => '<span class="badge bg-warning">Late</span>',
            'holiday' => '<span class="badge bg-info">Holiday</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getMethodBadgeAttribute(): string
    {
        return match($this->attendance_method) {
            'manual' => '<span class="badge bg-primary">Manual</span>',
            'barcode' => '<span class="badge bg-success">Barcode</span>',
            'api' => '<span class="badge bg-info">API</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->attendance_method) . '</span>',
        };
    }
}
