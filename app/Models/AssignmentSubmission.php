<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'marks_obtained',
        'feedback',
        'graded_at',
        'graded_by'
    ];

    protected $casts = [
        'graded_at' => 'datetime',
        'marks_obtained' => 'integer'
    ];

    /**
     * Get the assignment that owns the submission.
     */
    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the student that owns the submission.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the user (student) that owns the submission.
     */
    public function user()
    {
        return $this->student->user;
    }

    /**
     * Get the files for the submission.
     */
    public function files()
    {
        return $this->hasMany(AssignmentSubmissionFile::class);
    }

    /**
     * Get the teacher who graded the submission.
     */
    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Scope a query to only include submissions that are graded.
     */
    public function scopeGraded($query)
    {
        return $query->whereNotNull('graded_at');
    }

    /**
     * Scope a query to only include submissions that are not graded.
     */
    public function scopeNotGraded($query)
    {
        return $query->whereNull('graded_at');
    }

    /**
     * Scope a query to only include submissions for a specific assignment.
     */
    public function scopeForAssignment($query, $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    /**
     * Scope a query to only include submissions for a specific student.
     */
    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Check if the submission is graded.
     */
    public function isGraded(): bool
    {
        return !is_null($this->graded_at);
    }

    /**
     * Check if the submission is late.
     */
    public function isLate(): bool
    {
        return $this->created_at > $this->assignment->due_date;
    }

    /**
     * Check if the submission can be edited (within 24 hours of submission).
     */
    public function canBeEdited(): bool
    {
        if (!$this->assignment->allow_resubmission) {
            return false;
        }

        return $this->created_at->diffInHours(now()) < 24;
    }

    /**
     * Get the submission status.
     */
    public function getStatus(): string
    {
        if ($this->isGraded()) {
            return 'graded';
        } elseif ($this->isLate()) {
            return 'late';
        } else {
            return 'submitted';
        }
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->getStatus()) {
            'graded' => 'success',
            'late' => 'warning',
            'submitted' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Get the status display text.
     */
    public function getStatusDisplay(): string
    {
        return match($this->getStatus()) {
            'graded' => 'Graded',
            'late' => 'Late Submission',
            'submitted' => 'Submitted',
            default => 'Unknown'
        };
    }

    /**
     * Get the formatted submission date.
     */
    public function getFormattedSubmissionDate(): string
    {
        return $this->created_at->format('M j, Y g:i A');
    }

    /**
     * Get the formatted graded date.
     */
    public function getFormattedGradedDate(): string
    {
        return $this->graded_at ? $this->graded_at->format('M j, Y g:i A') : 'Not Graded';
    }

    /**
     * Get the percentage score.
     */
    public function getPercentageScore(): float
    {
        if ($this->marks_obtained === null || $this->assignment->total_marks === 0) {
            return 0;
        }

        return ($this->marks_obtained / $this->assignment->total_marks) * 100;
    }

    /**
     * Get the grade based on percentage.
     */
    public function getGrade(): string
    {
        $percentage = $this->getPercentageScore();

        return match(true) {
            $percentage >= 90 => 'A+',
            $percentage >= 85 => 'A',
            $percentage >= 80 => 'A-',
            $percentage >= 75 => 'B+',
            $percentage >= 70 => 'B',
            $percentage >= 65 => 'B-',
            $percentage >= 60 => 'C+',
            $percentage >= 55 => 'C',
            $percentage >= 50 => 'C-',
            $percentage >= 45 => 'D+',
            $percentage >= 40 => 'D',
            $percentage >= 35 => 'D-',
            $percentage >= 0 => 'F',
            default => 'N/A'
        };
    }

    /**
     * Get the grade color.
     */
    public function getGradeColor(): string
    {
        $percentage = $this->getPercentageScore();

        return match(true) {
            $percentage >= 80 => 'success',
            $percentage >= 60 => 'info',
            $percentage >= 40 => 'warning',
            $percentage >= 0 => 'danger',
            default => 'secondary'
        };
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
     * Get the time until submission deadline.
     */
    public function getTimeUntilDeadline(): string
    {
        if ($this->isLate()) {
            return 'Late';
        }

        return $this->assignment->due_date->diffForHumans($this->created_at, true) . ' early';
    }

    /**
     * Get the submission feedback summary.
     */
    public function getFeedbackSummary(): string
    {
        if (!$this->feedback) {
            return 'No feedback provided';
        }

        return Str::limit($this->feedback, 100);
    }
}
