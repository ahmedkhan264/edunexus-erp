<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Check if user has a specific role (or any of multiple roles).
     *
     * @param string|array $roleSlug
     * @return bool
     */
    public function hasRole(string|array $roleSlug): bool
    {
        if (!$this->role) {
            return false;
        }

        if (is_array($roleSlug)) {
            return in_array($this->role->slug, $roleSlug);
        }

        return $this->role->slug === $roleSlug;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getDashboardRoute(): string
    {
        return match($this->role->slug) {
            'super_admin' => '/admin/dashboard',
            'principal' => '/principal/dashboard',
            'admin' => '/admin/dashboard',
            'teacher' => '/teacher/dashboard',
            'student' => '/student/dashboard',
            'parent' => '/parent/dashboard',
            'accountant' => '/accountant/dashboard',
            'hr_manager' => '/hr/dashboard',
            'librarian' => '/library/dashboard',
            'timetable_coordinator' => '/timetable/dashboard',
            default => '/login'
        };
    }

    /**
     * Get the class assignments for this teacher.
     */
    public function assignedClasses()
    {
        return $this->hasMany(TeacherClassAssignment::class, 'teacher_id')
            ->with(['class', 'subject'])
            ->active()
            ->forAcademicYear(config('app.academic_year', date('Y') . '-' . (date('Y') + 1)));
    }
}