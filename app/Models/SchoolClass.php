<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $table = 'classes';
    
    protected $fillable = [
        'name',
        'class_code',
        'section',
        'grade_level',
        'capacity',
        'description',
        'is_active',
        'teacher_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'grade_level' => 'integer',
        'capacity' => 'integer',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByGradeLevel($query, $gradeLevel)
    {
        return $query->where('grade_level', $gradeLevel);
    }

    public function getFullNameAttribute(): string
    {
        $name = $this->name;
        if ($this->section) {
            $name .= ' - ' . $this->section;
        }
        return $name;
    }

    public function getAvailableSlotsAttribute(): int
    {
        return $this->capacity - $this->students()->count();
    }
}
