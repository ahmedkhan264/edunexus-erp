<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teacher extends Model
{
    protected $fillable = [
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
        'cnic',
        'date_of_birth',
        'gender',
        'phone_number',
        'email',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'nationality',
        'blood_group',
        'religion',
        'qualification',
        'specialization',
        'experience_years',
        'previous_institution',
        'joining_date',
        'basic_salary',
        'employment_type',
        'is_active',
        'resignation_date',
        'resignation_reason',
        'notes',
        'profile_image',
        'documents',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'resignation_date' => 'date',
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
        'documents' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject');
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_class');
    }

    // Scopes for common queries
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeByQualification($query, $qualification)
    {
        return $query->where('qualification', 'like', '%' . $qualification . '%');
    }

    public function scopeBySpecialization($query, $specialization)
    {
        return $query->where('specialization', 'like', '%' . $specialization . '%');
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    // Helper methods
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getFormattedPhoneAttribute(): string
    {
        return $this->phone_number;
    }

    public function getFormattedJoiningDateAttribute(): string
    {
        return $this->joining_date->format('M d, Y');
    }

    public function getFormattedDateOfBirthAttribute(): string
    {
        return $this->date_of_birth->format('M d, Y');
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    public function getFormattedSalaryAttribute(): string
    {
        return 'PKR ' . number_format($this->basic_salary, 2);
    }

    public function getEmploymentTypeBadgeAttribute(): string
    {
        $badges = [
            'permanent' => '<span class="badge bg-success">Permanent</span>',
            'contract' => '<span class="badge bg-warning">Contract</span>',
            'part-time' => '<span class="badge bg-info">Part-Time</span>',
        ];

        return $badges[$this->employment_type] ?? '<span class="badge bg-secondary">' . ucfirst($this->employment_type) . '</span>';
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->resignation_date) {
            return '<span class="badge bg-danger">Resigned</span>';
        }

        return $this->is_active ? 
            '<span class="badge bg-success">Active</span>' : 
            '<span class="badge bg-danger">Inactive</span>';
    }

    public function getGenderBadgeAttribute(): string
    {
        $badges = [
            'Male' => '<span class="badge bg-primary">Male</span>',
            'Female' => '<span class="badge bg-danger">Female</span>',
        ];

        return $badges[$this->gender] ?? '<span class="badge bg-secondary">' . $this->gender . '</span>';
    }

    public function getFullAddressAttribute(): string
    {
        $address = $this->address;
        
        if ($this->city) {
            $address .= ', ' . $this->city;
        }
        
        if ($this->state) {
            $address .= ', ' . $this->state;
        }
        
        if ($this->postal_code) {
            $address .= ' - ' . $this->postal_code;
        }
        
        if ($this->country) {
            $address .= ', ' . $this->country;
        }
        
        return $address;
    }

    public function getExperienceAttribute(): string
    {
        return $this->experience_years . ' years';
    }

    public function getSubjectNamesAttribute(): string
    {
        return $this->subjects->pluck('name')->implode(', ');
    }

    public function getClassNamesAttribute(): string
    {
        return $this->classes->pluck('name')->implode(', ');
    }

    public function hasResigned(): bool
    {
        return !is_null($this->resignation_date);
    }

    public function isPermanent(): bool
    {
        return $this->employment_type === 'permanent';
    }

    public function isContract(): bool
    {
        return $this->employment_type === 'contract';
    }

    public function isPartTime(): bool
    {
        return $this->employment_type === 'part-time';
    }

    public function getYearsOfServiceAttribute(): int
    {
        if ($this->hasResigned()) {
            return $this->joining_date->diffInYears($this->resignation_date);
        }
        
        return $this->joining_date->diffInYears(now());
    }

    public function getProfileImageUrlAttribute(): ?string
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        
        return null;
    }

    public function getProfileAvatarAttribute(): string
    {
        if ($this->profile_image) {
            return '<img src="' . $this->profile_image_url . '" alt="' . $this->full_name . '" class="rounded-circle" width="40" height="40">';
        }
        
        return '<div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">' . 
               strtoupper(substr($this->first_name, 0, 1)) . '</div>';
    }
}
