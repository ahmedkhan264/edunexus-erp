<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $table = 'teacher_attendances';

    protected $fillable = [
        'teacher_id',
        'date',
        'check_in_time',
        'check_out_time',
        'status',
        'late_minutes',
        'working_hours',
        'remarks',
        'marked_by',
        'attendance_method'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'late_minutes' => 'integer',
        'working_hours' => 'decimal:2'
    ];

    /**
     * Get the teacher (user) associated with this attendance.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the user who marked this attendance.
     */
    public function marker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    /**
     * Scope for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope for a specific teacher.
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope for present teachers.
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope for late teachers.
     */
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    /**
     * Scope for absent teachers.
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Get today's attendance status for a teacher.
     */
    public static function getTodayStatus($teacherId)
    {
        return self::forTeacher($teacherId)
            ->forDate(now()->format('Y-m-d'))
            ->first();
    }

    /**
     * Check if teacher has checked in today.
     */
    public function hasCheckedIn()
    {
        return !is_null($this->check_in_time);
    }

    /**
     * Check if teacher has checked out today.
     */
    public function hasCheckedOut()
    {
        return !is_null($this->check_out_time);
    }

    /**
     * Process check-in for a teacher.
     */
    public static function checkIn($teacherId)
    {
        $today = now()->format('Y-m-d');
        $now = now();
        
        // Check if already checked in
        $existing = self::forTeacher($teacherId)->forDate($today)->first();
        
        if ($existing && $existing->hasCheckedIn()) {
            throw new \Exception('You have already checked in today.');
        }
        
        // Define check-in time (e.g., 9:00 AM)
        $checkInDeadline = Carbon::parse($today . ' 09:00:00');
        $lateMinutes = 0;
        $status = 'present';
        
        if ($now->gt($checkInDeadline)) {
            $lateMinutes = $checkInDeadline->diffInMinutes($now);
            $status = 'late';
        }
        
        return self::updateOrCreate(
            [
                'teacher_id' => $teacherId,
                'date' => $today,
            ],
            [
                'check_in_time' => $now,
                'status' => $status,
                'late_minutes' => $lateMinutes,
                'attendance_method' => 'system',
                'marked_by' => $teacherId
            ]
        );
    }

    /**
     * Process check-out for a teacher.
     */
    public static function checkOut($teacherId)
    {
        $today = now()->format('Y-m-d');
        $now = now();
        
        $attendance = self::forTeacher($teacherId)->forDate($today)->first();
        
        if (!$attendance || !$attendance->hasCheckedIn()) {
            return null;
        }
        
        if ($attendance->hasCheckedOut()) {
            throw new \Exception('You have already checked out today.');
        }
        
        // Calculate working hours
        $checkInTime = Carbon::parse($attendance->check_in_time);
        $workingHours = $checkInTime->diffInHours($now);
        
        $attendance->update([
            'check_out_time' => $now,
            'working_hours' => $workingHours
        ]);
        
        return $attendance;
    }

    /**
     * Get formatted working hours.
     */
    public function getFormattedWorkingHoursAttribute()
    {
        if (!$this->working_hours) {
            return '0h 0m';
        }
        
        $hours = floor($this->working_hours);
        $minutes = ($this->working_hours - $hours) * 60;
        
        return "{$hours}h " . round($minutes) . "m";
    }

    /**
     * Get status display text.
     */
    public function getStatusDisplay()
    {
        return ucfirst($this->status);
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeColor()
    {
        return match($this->status) {
            'present' => 'success',
            'late' => 'warning',
            'absent' => 'danger',
            'half_day' => 'info',
            default => 'secondary'
        };
    }
}