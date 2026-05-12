<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LiveClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'class_id',
        'section',
        'subject_id',
        'teacher_id',
        'meeting_link',
        'start_time',
        'end_time',
        'duration',
        'notification_sent'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'integer',
        'notification_sent' => 'boolean'
    ];

    /**
     * Get the class that owns the live class.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the subject that owns the live class.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher that owns the live class.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the students for this live class.
     */
    public function students()
    {
        return $this->hasManyThrough(
            Student::class,
            [SchoolClass::class],
            'id',
            'class_id',
            'class_id',
            'id'
        )->where('section', $this->section);
    }

    /**
     * Scope a query to only include upcoming classes.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    /**
     * Scope a query to only include past classes.
     */
    public function scopePast($query)
    {
        return $query->where('start_time', '<=', now());
    }

    /**
     * Scope a query to only include classes for a specific class and section.
     */
    public function scopeForClass($query, $classId, $section)
    {
        return $query->where('class_id', $classId)->where('section', $section);
    }

    /**
     * Scope a query to only include classes for a specific teacher.
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope a query to only include classes on a specific date.
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('start_time', $date);
    }

    /**
     * Check if the class is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time > now();
    }

    /**
     * Check if the class is in progress.
     */
    public function isInProgress(): bool
    {
        return now()->between($this->start_time, $this->end_time);
    }

    /**
     * Check if the class is completed.
     */
    public function isCompleted(): bool
    {
        return $this->end_time < now();
    }

    /**
     * Check if the class starts within the next 15 minutes.
     */
    public function startsSoon(): bool
    {
        return $this->start_time->diffInMinutes(now(), false) <= 15 && $this->start_time > now();
    }

    /**
     * Get the status of the class.
     */
    public function getStatus(): string
    {
        if ($this->isCompleted()) {
            return 'completed';
        } elseif ($this->isInProgress()) {
            return 'in_progress';
        } else {
            return 'upcoming';
        }
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->getStatus()) {
            'completed' => 'secondary',
            'in_progress' => 'success',
            'upcoming' => 'primary',
            default => 'secondary'
        };
    }

    /**
     * Get the status display text.
     */
    public function getStatusDisplay(): string
    {
        return match($this->getStatus()) {
            'completed' => 'Completed',
            'in_progress' => 'In Progress',
            'upcoming' => 'Upcoming',
            default => 'Unknown'
        };
    }

    /**
     * Get the meeting platform from the link.
     */
    public function getMeetingPlatform(): string
    {
        $url = strtolower($this->meeting_link);

        if (str_contains($url, 'zoom.us')) {
            return 'Zoom';
        } elseif (str_contains($url, 'meet.google.com')) {
            return 'Google Meet';
        } elseif (str_contains($url, 'teams.microsoft.com')) {
            return 'Microsoft Teams';
        } elseif (str_contains($url, 'webex.com')) {
            return 'Cisco Webex';
        } else {
            return 'Other';
        }
    }

    /**
     * Get the meeting platform icon.
     */
    public function getMeetingPlatformIcon(): string
    {
        return match($this->getMeetingPlatform()) {
            'Zoom' => 'fas fa-video',
            'Google Meet' => 'fab fa-google',
            'Microsoft Teams' => 'fab fa-microsoft',
            'Cisco Webex' => 'fas fa-video',
            default => 'fas fa-link'
        };
    }

    /**
     * Get the meeting platform color.
     */
    public function getMeetingPlatformColor(): string
    {
        return match($this->getMeetingPlatform()) {
            'Zoom' => 'info',
            'Google Meet' => 'success',
            'Microsoft Teams' => 'primary',
            'Cisco Webex' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get formatted start time.
     */
    public function getFormattedStartTime(): string
    {
        return $this->start_time->format('M j, Y g:i A');
    }

    /**
     * Get formatted end time.
     */
    public function getFormattedEndTime(): string
    {
        return $this->end_time->format('g:i A');
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDuration(): string
    {
        $hours = floor($this->duration / 60);
        $minutes = $this->duration % 60;

        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        } else {
            return $minutes . ' minutes';
        }
    }

    /**
     * Get time until class starts.
     */
    public function getTimeUntilStart(): string
    {
        if (!$this->isUpcoming()) {
            return '';
        }

        $diff = $this->start_time->diffForHumans(now(), true);

        return $diff;
    }

    /**
     * Check for time clash with another class.
     */
    public function hasTimeClashWith(LiveClass $other): bool
    {
        // Same class and section
        if ($this->class_id !== $other->class_id || $this->section !== $other->section) {
            return false;
        }

        // Same date
        if ($this->start_time->toDateString() !== $other->start_time->toDateString()) {
            return false;
        }

        // Check time overlap
        return (
            ($this->start_time < $other->end_time && $this->end_time > $other->start_time)
        );
    }

    /**
     * Find classes that clash with this class.
     */
    public function findClashingClasses()
    {
        return self::where('class_id', $this->class_id)
            ->where('section', $this->section)
            ->whereDate('start_time', $this->start_time->toDateString())
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('start_time', '<', $this->end_time)
                      ->where('end_time', '>', $this->start_time);
                });
            })
            ->where('id', '!=', $this->id)
            ->get();
    }
}
