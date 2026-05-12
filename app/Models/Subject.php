<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'grade_level',
        'is_active',
        'credits',
        'department',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credits' => 'integer',
    ];

    // Relationships
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subject');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subject');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    // Helper methods
    public function getFormattedCreditsAttribute(): string
    {
        return $this->credits . ' credit' . ($this->credits > 1 ? 's' : '');
    }

    public function getGradeLevelBadgeAttribute(): string
    {
        if (!$this->grade_level) {
            return '<span class="badge bg-secondary">All Grades</span>';
        }

        return '<span class="badge bg-primary">' . $this->grade_level . '</span>';
    }

    public function getDepartmentBadgeAttribute(): string
    {
        if (!$this->department) {
            return '<span class="badge bg-secondary">General</span>';
        }

        $colors = [
            'Science' => 'bg-success',
            'Mathematics' => 'bg-primary',
            'English' => 'bg-info',
            'Urdu' => 'bg-warning',
            'Computer Science' => 'bg-dark',
            'Social Studies' => 'bg-secondary',
        ];

        $color = $colors[$this->department] ?? 'bg-secondary';

        return '<span class="badge ' . $color . '">' . $this->department . '</span>';
    }

    public function getTeacherCountAttribute(): int
    {
        return $this->teachers()->count();
    }

    public function getClassCountAttribute(): int
    {
        return $this->classes()->count();
    }
}
