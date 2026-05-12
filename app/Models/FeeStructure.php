<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeStructure extends Model
{
    protected $fillable = [
        'class_id',
        'academic_year',
        'fee_type',
        'amount',
        'is_optional',
        'payment_frequency',
        'due_day',
        'late_fine_per_day',
        'description',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_optional' => 'boolean',
        'late_fine_per_day' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function class(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function feeChallans(): HasMany
    {
        return $this->hasMany(FeeChallan::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeByAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    public function scopeByFeeType($query, $feeType)
    {
        return $query->where('fee_type', $feeType);
    }

    public function scopeByPaymentFrequency($query, $frequency)
    {
        return $query->where('payment_frequency', $frequency);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_optional', false);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_optional', true);
    }

    // Helper methods
    public function getFormattedAmountAttribute(): string
    {
        return 'PKR ' . number_format($this->amount, 2);
    }

    public function getFormattedLateFineAttribute(): string
    {
        return 'PKR ' . number_format($this->late_fine_per_day, 2);
    }

    public function getFeeTypeBadgeAttribute(): string
    {
        $badges = [
            'tuition' => '<span class="badge bg-primary">Tuition</span>',
            'admission' => '<span class="badge bg-success">Admission</span>',
            'exam' => '<span class="badge bg-warning">Exam</span>',
            'library' => '<span class="badge bg-info">Library</span>',
            'sports' => '<span class="badge bg-secondary">Sports</span>',
            'transport' => '<span class="badge bg-dark">Transport</span>',
            'late_fine' => '<span class="badge bg-danger">Late Fine</span>',
        ];

        return $badges[$this->fee_type] ?? '<span class="badge bg-secondary">' . ucfirst($this->fee_type) . '</span>';
    }

    public function getPaymentFrequencyBadgeAttribute(): string
    {
        $badges = [
            'monthly' => '<span class="badge bg-primary">Monthly</span>',
            'quarterly' => '<span class="badge bg-info">Quarterly</span>',
            'yearly' => '<span class="badge bg-warning">Yearly</span>',
            'one_time' => '<span class="badge bg-success">One Time</span>',
        ];

        return $badges[$this->payment_frequency] ?? '<span class="badge bg-secondary">' . ucfirst($this->payment_frequency) . '</span>';
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->is_active ? 
            '<span class="badge bg-success">Active</span>' : 
            '<span class="badge bg-danger">Inactive</span>';
    }

    public function getOptionalBadgeAttribute(): string
    {
        return $this->is_optional ? 
            '<span class="badge bg-warning">Optional</span>' : 
            '<span class="badge bg-success">Required</span>';
    }

    public function getDueDateForMonth($month, $year): string
    {
        return sprintf('%04d-%02d-%02d', $year, $month, $this->due_day);
    }

    public function calculateLateFine($daysLate): float
    {
        return $this->late_fine_per_day * $daysLate;
    }

    public function isMonthly(): bool
    {
        return $this->payment_frequency === 'monthly';
    }

    public function isQuarterly(): bool
    {
        return $this->payment_frequency === 'quarterly';
    }

    public function isYearly(): bool
    {
        return $this->payment_frequency === 'yearly';
    }

    public function isOneTime(): bool
    {
        return $this->payment_frequency === 'one_time';
    }

    public function getDisplayNameAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->fee_type));
    }

    public function getFrequencyDisplayNameAttribute(): string
    {
        return ucfirst($this->payment_frequency);
    }

    public function scopeForCurrentAcademicYear($query)
    {
        return $query->where('academic_year', config('fee.current_academic_year', '2024-2025'));
    }
}
