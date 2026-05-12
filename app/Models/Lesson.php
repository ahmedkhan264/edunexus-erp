<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'class_id',
        'section',
        'subject_id',
        'chapter',
        'description',
        'status',
        'teacher_id'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    /**
     * Get the class that owns the lesson.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the subject that owns the lesson.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher that owns the lesson.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the files for the lesson.
     */
    public function files()
    {
        return $this->hasMany(LessonFile::class)->orderBy('order');
    }

    /**
     * Get the lesson views for the lesson.
     */
    public function lessonViews()
    {
        return $this->hasMany(LessonView::class);
    }

    /**
     * Scope a query to only include published lessons.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft lessons.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to filter by class and section.
     */
    public function scopeForClass($query, $classId, $section)
    {
        return $query->where('class_id', $classId)->where('section', $section);
    }

    /**
     * Scope a query to filter by teacher.
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Check if lesson is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if lesson is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        return match($this->status) {
            'published' => 'success',
            'draft' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get the status display text.
     */
    public function getStatusDisplay(): string
    {
        return match($this->status) {
            'published' => 'Published',
            'draft' => 'Draft',
            default => 'Unknown'
        };
    }

    /**
     * Get total file size for the lesson.
     */
    public function getTotalFileSize(): int
    {
        return $this->files->sum('size');
    }

    /**
     * Get formatted total file size.
     */
    public function getFormattedFileSize(): string
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
     * Check if student has viewed this lesson.
     */
    public function isViewedByStudent($studentId): bool
    {
        return $this->lessonViews()->where('student_id', $studentId)->exists();
    }
}
