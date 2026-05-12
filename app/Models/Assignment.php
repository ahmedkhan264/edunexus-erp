<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'class_id',
        'section',
        'subject_id',
        'teacher_id',
        'due_date',
        'total_marks',
        'allow_resubmission'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'total_marks' => 'integer',
        'allow_resubmission' => 'boolean'
    ];

    /**
     * Get the class that owns the assignment.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the subject that owns the assignment.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher that owns the assignment.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the files for the assignment.
     */
    public function files()
    {
        return $this->hasMany(AssignmentFile::class);
    }

    /**
     * Get the submissions for the assignment.
     */
    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

    /**
     * Get the students for this assignment.
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
     * Scope a query to only include assignments for a specific class and section.
     */
    public function scopeForClass($query, $classId, $section)
    {
        return $query->where('class_id', $classId)->where('section', $section);
    }

    /**
     * Scope a query to only include assignments for a specific teacher.
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope a query to only include assignments that are overdue.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now());
    }

    /**
     * Scope a query to only include assignments that are due soon (within 24 hours).
     */
    public function scopeDueSoon($query)
    {
        return $query->where('due_date', '<=', now()->addHours(24))
                    ->where('due_date', '>', now());
    }

    /**
     * Scope a query to only include assignments that are upcoming (not due yet).
     */
    public function scopeUpcoming($query)
    {
        return $query->where('due_date', '>', now());
    }

    /**
     * Check if the assignment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date < now();
    }

    /**
     * Check if the assignment is due soon.
     */
    public function isDueSoon(): bool
    {
        return $this->due_date->diffInHours(now(), false) <= 24 && $this->due_date > now();
    }

    /**
     * Get the time remaining until due date.
     */
    public function getTimeRemaining(): string
    {
        if ($this->isOverdue()) {
            return 'Overdue';
        }

        return $this->due_date->diffForHumans(now(), true);
    }

    /**
     * Get formatted due date.
     */
    public function getFormattedDueDate(): string
    {
        return $this->due_date->format('M j, Y g:i A');
    }

    /**
     * Get due date in a short format.
     */
    public function getShortDueDate(): string
    {
        return $this->due_date->format('M j, g:i A');
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        if ($this->isOverdue()) {
            return 'danger';
        } elseif ($this->isDueSoon()) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    /**
     * Get the status display text.
     */
    public function getStatusDisplay(): string
    {
        if ($this->isOverdue()) {
            return 'Overdue';
        } elseif ($this->isDueSoon()) {
            return 'Due Soon';
        } else {
            return 'Open';
        }
    }

    /**
     * Get the total file size of all attached files.
     */
    public function getTotalFileSize(): int
    {
        return $this->files()->sum('size');
    }

    /**
     * Get the formatted total file size.
     */
    public function getFormattedTotalFileSize(): string
    {
        $bytes = $this->getTotalFileSize();
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Get the number of submissions.
     */
    public function getSubmissionCount(): int
    {
        return $this->submissions()->count();
    }

    /**
     * Get the number of graded submissions.
     */
    public function getGradedCount(): int
    {
        return $this->submissions()->whereNotNull('graded_at')->count();
    }

    /**
     * Get the submission rate as percentage.
     */
    public function getSubmissionRate(): float
    {
        $totalStudents = $this->students()->count();
        $submissionCount = $this->getSubmissionCount();
        
        if ($totalStudents === 0) {
            return 0;
        }
        
        return ($submissionCount / $totalStudents) * 100;
    }

    /**
     * Check if a student has submitted this assignment.
     */
    public function hasStudentSubmitted($studentId): bool
    {
        return $this->submissions()->where('student_id', $studentId)->exists();
    }

    /**
     * Get the submission for a specific student.
     */
    public function getStudentSubmission($studentId)
    {
        return $this->submissions()->where('student_id', $studentId)->first();
    }
}
