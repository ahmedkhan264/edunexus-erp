<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentProfile extends Model
{
    protected $fillable = [
        'student_id',
        'user_id',
        'father_name',
        'father_cnic',
        'father_phone',
        'father_occupation',
        'father_email',
        'mother_name',
        'mother_cnic',
        'mother_phone',
        'mother_occupation',
        'mother_email',
        'guardian_name',
        'guardian_cnic',
        'guardian_phone',
        'guardian_occupation',
        'guardian_email',
        'guardian_relation',
        'guardian_address',
        'is_primary_guardian',
        'notes',
    ];

    protected $casts = [
        'is_primary_guardian' => 'boolean',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes for common queries
    public function scopePrimaryGuardian($query)
    {
        return $query->where('is_primary_guardian', true);
    }

    public function scopeByCnic($query, $cnic)
    {
        return $query->where(function ($q) use ($cnic) {
            $q->where('father_cnic', $cnic)
              ->orWhere('mother_cnic', $cnic)
              ->orWhere('guardian_cnic', $cnic);
        });
    }

    // Helper methods
    public function getPrimaryGuardianNameAttribute(): string
    {
        if ($this->is_primary_guardian && $this->guardian_name) {
            return $this->guardian_name;
        }
        
        if ($this->father_name) {
            return $this->father_name;
        }
        
        if ($this->mother_name) {
            return $this->mother_name;
        }
        
        return 'N/A';
    }

    public function getPrimaryGuardianPhoneAttribute(): string
    {
        if ($this->is_primary_guardian && $this->guardian_phone) {
            return $this->guardian_phone;
        }
        
        if ($this->father_phone) {
            return $this->father_phone;
        }
        
        if ($this->mother_phone) {
            return $this->mother_phone;
        }
        
        return 'N/A';
    }

    public function getPrimaryGuardianEmailAttribute(): ?string
    {
        if ($this->is_primary_guardian && $this->guardian_email) {
            return $this->guardian_email;
        }
        
        if ($this->father_email) {
            return $this->father_email;
        }
        
        if ($this->mother_email) {
            return $this->mother_email;
        }
        
        return null;
    }

    public function getPrimaryGuardianOccupationAttribute(): string
    {
        if ($this->is_primary_guardian && $this->guardian_occupation) {
            return $this->guardian_occupation;
        }
        
        if ($this->father_occupation) {
            return $this->father_occupation;
        }
        
        if ($this->mother_occupation) {
            return $this->mother_occupation;
        }
        
        return 'N/A';
    }

    public function getPrimaryGuardianCnicAttribute(): ?string
    {
        if ($this->is_primary_guardian && $this->guardian_cnic) {
            return $this->guardian_cnic;
        }
        
        if ($this->father_cnic) {
            return $this->father_cnic;
        }
        
        if ($this->mother_cnic) {
            return $this->mother_cnic;
        }
        
        return null;
    }

    public function getGuardianTypeAttribute(): string
    {
        if ($this->is_primary_guardian && $this->guardian_name) {
            return 'Guardian';
        }
        
        if ($this->father_name) {
            return 'Father';
        }
        
        if ($this->mother_name) {
            return 'Mother';
        }
        
        return 'N/A';
    }

    public function hasFather(): bool
    {
        return !empty($this->father_name);
    }

    public function hasMother(): bool
    {
        return !empty($this->mother_name);
    }

    public function hasGuardian(): bool
    {
        return !empty($this->guardian_name);
    }

    public function getCompleteInfoAttribute(): array
    {
        return [
            'primary_guardian' => [
                'name' => $this->primary_guardian_name,
                'phone' => $this->primary_guardian_phone,
                'email' => $this->primary_guardian_email,
                'occupation' => $this->primary_guardian_occupation,
                'cnic' => $this->primary_guardian_cnic,
                'type' => $this->guardian_type,
            ],
            'father' => [
                'name' => $this->father_name,
                'cnic' => $this->father_cnic,
                'phone' => $this->father_phone,
                'email' => $this->father_email,
                'occupation' => $this->father_occupation,
            ],
            'mother' => [
                'name' => $this->mother_name,
                'cnic' => $this->mother_cnic,
                'phone' => $this->mother_phone,
                'email' => $this->mother_email,
                'occupation' => $this->mother_occupation,
            ],
            'guardian' => [
                'name' => $this->guardian_name,
                'cnic' => $this->guardian_cnic,
                'phone' => $this->guardian_phone,
                'email' => $this->guardian_email,
                'occupation' => $this->guardian_occupation,
                'relation' => $this->guardian_relation,
                'address' => $this->guardian_address,
                'is_primary' => $this->is_primary_guardian,
            ],
        ];
    }
}
