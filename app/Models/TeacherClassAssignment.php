<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TeacherClassAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'academic_year',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the teacher (user) for the assignment.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the class for the assignment.
     */
    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the subject for the assignment.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Scope a query to only include active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include assignments for a specific academic year.
     */
    public function scopeForAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * Scope a query to only include assignments for a specific teacher.
     */
    public function scopeForTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }
}
