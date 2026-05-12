<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'late_minutes',
        'remarks',
        'marked_by',
        'attendance_method'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime:H:i:s',
        'check_out_time' => 'datetime:H:i:s',
        'late_minutes' => 'integer'
    ];

    /**
     * Get the teacher (user) for the attendance.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the user who marked the attendance.
     */
    public function marker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Scope a query to only include attendance for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', $date);
    }

    /**
     * Scope a query to only include attendance for a specific teacher.
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope a query to only include attendance within a date range.
     */
    public function scopeDateRange($query, $fromDate, $toDate)
    {
        return $query->whereBetween('date', [$fromDate, $toDate]);
    }

    /**
     * Scope a query to only include present teachers.
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope a query to only include late teachers.
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope a query to only include absent teachers.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Check if teacher has checked in today.
     */
    public function hasCheckedIn(): bool
    {
        return !is_null($this->check_in_time);
    }

    /**
     * Check if teacher has checked out today.
     */
    public function hasCheckedOut(): bool
    {
        return !is_null($this->check_out_time);
    }

    /**
     * Calculate working hours.
     */
    public function getWorkingHoursAttribute(): float
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 0;
        }

        $checkIn = Carbon::parse($this->date . ' ' . $this->check_in_time);
        $checkOut = Carbon::parse($this->date . ' ' . $this->check_out_time);
        
        // Handle overnight checkout
        if ($checkOut < $checkIn) {
            $checkOut->addDay();
        }

        return $checkOut->diffInMinutes($checkIn) / 60;
    }

    /**
     * Get formatted working hours.
     */
    public function getFormattedWorkingHoursAttribute(): string
    {
        $hours = $this->working_hours;
        $wholeHours = floor($hours);
        $minutes = round(($hours - $wholeHours) * 60);
        
        return sprintf('%d:%02d', $wholeHours, $minutes);
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'present' => 'success',
            'late' => 'warning',
            'absent' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get status display text.
     */
    public function getStatusDisplay(): string
    {
        return match($this->status) {
            'present' => 'Present',
            'late' => 'Late',
            'absent' => 'Absent',
            default => 'Unknown'
        };
    }

    /**
     * Get current attendance status for today.
     */
    public static function getTodayStatus($teacherId): ?self
    {
        return self::forTeacher($teacherId)
            ->forDate(now()->format('Y-m-d'))
            ->first();
    }

    /**
     * Mark check-in for teacher.
     */
    public static function checkIn($teacherId, $checkInTime = null): self
    {
        $today = now()->format('Y-m-d');
        $checkInTime = $checkInTime ?? now()->format('H:i:s');
        
        // Get late cutoff time from settings
        $lateCutoff = config('attendance.late_cutoff', '08:30:00');
        
        $status = 'present';
        $lateMinutes = 0;
        
        if ($checkInTime > $lateCutoff) {
            $status = 'late';
            $lateMinutes = Carbon::parse($checkInTime)->diffInMinutes($lateCutoff);
        }
        
        return self::updateOrCreate(
            ['teacher_id' => $teacherId, 'date' => $today],
            [
                'check_in_time' => $checkInTime,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'attendance_method' => 'system'
            ]
        );
    }

    /**
     * Mark check-out for teacher.
     */
    public static function checkOut($teacherId, $checkOutTime = null): ?self
    {
        $today = now()->format('Y-m-d');
        $checkOutTime = $checkOutTime ?? now()->format('H:i:s');
        
        $attendance = self::forTeacher($teacherId)
            ->forDate($today)
            ->first();
        
        if ($attendance && $attendance->hasCheckedIn()) {
            $attendance->update([
                'check_out_time' => $checkOutTime
            ]);
            
            return $attendance->fresh();
        }
        
        return null;
    }

    /**
     * Auto-mark absent for teachers who didn't check in.
     */
    public static function markAbsentForNoCheckIn(): int
    {
        $today = now()->format('Y-m-d');
        $teacherIds = User::whereHas('role', function($query) {
            $query->where('slug', 'teacher');
        })->where('is_active', true)->pluck('id');
        
        $markedCount = 0;
        
        foreach ($teacherIds as $teacherId) {
            $existing = self::forTeacher($teacherId)->forDate($today)->first();
            
            if (!$existing) {
                self::create([
                    'teacher_id' => $teacherId,
                    'date' => $today,
                    'status' => 'absent',
                    'attendance_method' => 'system'
                ]);
                
                $markedCount++;
            }
        }
        
        return $markedCount;
    }
}
