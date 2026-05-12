<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'class_id',
        'student_id',
        'first_name',
        'last_name',
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
        'religion',
        'blood_group',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'admission_date',
        'admission_number',
        'previous_school_gpa',
        'previous_school_name',
        'is_active',
        'graduation_date',
        'status',
        'notes',
        'profile_image',
        'documents',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'graduation_date' => 'date',
        'previous_school_gpa' => 'decimal:2',
        'is_active' => 'boolean',
        'documents' => 'array',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'user_id');
    }

    public function parentProfile(): BelongsTo
    {
        return $this->belongsTo(ParentProfile::class);
    }

    // Scopes for common queries
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEnrolled($query)
    {
        return $query->where('status', 'enrolled');
    }

    public function scopeGraduated($query)
    {
        return $query->where('status', 'graduated');
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('student_id', 'like', "%{$search}%")
              ->orWhere('admission_number', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone_number', 'like', "%{$search}%");
        });
    }

    // Helper methods
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age;
    }

    public function getFormattedDateOfBirthAttribute(): string
    {
        return $this->date_of_birth->format('M d, Y');
    }

    public function getFormattedAdmissionDateAttribute(): string
    {
        return $this->admission_date->format('M d, Y');
    }

    public function getFormattedPhoneAttribute(): ?string
    {
        return $this->phone_number ? $this->phone_number : 'N/A';
    }

    public function getFullAddressAttribute(): ?string
    {
        $address = [];
        
        if ($this->address) $address[] = $this->address;
        if ($this->city) $address[] = $this->city;
        if ($this->state) $address[] = $this->state;
        if ($this->postal_code) $address[] = $this->postal_code;
        if ($this->country) $address[] = $this->country;
        
        return empty($address) ? null : implode(', ', $address);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'enrolled' => '<span class="badge bg-success">Enrolled</span>',
            'graduated' => '<span class="badge bg-primary">Graduated</span>',
            'suspended' => '<span class="badge bg-warning">Suspended</span>',
            'withdrawn' => '<span class="badge bg-danger">Withdrawn</span>',
            default => '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>',
        };
    }

    public function getGenderBadgeAttribute(): string
    {
        $color = match(strtolower($this->gender)) {
            'male' => 'info',
            'female' => 'danger',
            default => 'secondary'
        };
        
        return '<span class="badge bg-' . $color . '">' . ucfirst($this->gender) . '</span>';
    }

    public function getAttendancePercentageAttribute(): float
    {
        $totalAttendance = $this->attendance()->count();
        if ($totalAttendance === 0) return 0;
        
        $presentAttendance = $this->attendance()->present()->count();
        return round(($presentAttendance / $totalAttendance) * 100, 2);
    }

    public function getAttendanceStatsAttribute(): array
    {
        $stats = [
            'total' => $this->attendance()->count(),
            'present' => $this->attendance()->present()->count(),
            'absent' => $this->attendance()->absent()->count(),
            'late' => $this->attendance()->late()->count(),
            'holiday' => $this->attendance()->holiday()->count(),
        ];
        
        $stats['percentage'] = $stats['total'] > 0 
            ? round(($stats['present'] / $stats['total']) * 100, 2) 
            : 0;
            
        return $stats;
    }

    public function isEnrolled(): bool
    {
        return $this->status === 'enrolled' && $this->is_active;
    }

    public function hasGraduated(): bool
    {
        return $this->status === 'graduated';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isWithdrawn(): bool
    {
        return $this->status === 'withdrawn';
    }
}
