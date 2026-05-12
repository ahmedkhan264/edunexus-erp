<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'head_of_department',
        'contact_email',
        'contact_phone',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get the users (teachers) in this department.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the teachers in this department.
     */
    public function teachers()
    {
        return $this->hasMany(User::class)->whereHas('role', function($query) {
            $query->where('slug', 'teacher');
        });
    }

    /**
     * Scope a query to only include active departments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive departments.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get the active status badge color.
     */
    public function getStatusBadgeColor(): string
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Get the active status display text.
     */
    public function getStatusDisplay(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    /**
     * Get the number of active teachers in this department.
     */
    public function getActiveTeachersCountAttribute(): int
    {
        return $this->teachers()->where('is_active', true)->count();
    }

    /**
     * Get the department's full display name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->code} - {$this->name}";
    }
}
