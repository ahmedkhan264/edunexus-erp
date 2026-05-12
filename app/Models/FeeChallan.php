<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeChallan extends Model
{
    protected $fillable = [
        'student_id',
        'challan_number',
        'academic_year',
        'month',
        'total_amount',
        'due_date',
        'status',
        'late_fine_applied',
        'remarks',
        'generated_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'late_fine_applied' => 'decimal:2',
        'due_date' => 'date',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'partially_paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeWaived($query)
    {
        return $query->where('status', 'waived');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    public function scopeByMonth($query, $month)
    {
        return $query->where('month', $month);
    }

    public function scopeDueDate($query, $date)
    {
        return $query->where('due_date', $date);
    }

    public function scopeOverdueDate($query)
    {
        return $query->where('due_date', '<', now())->where('status', 'pending');
    }

    public function scopeForCurrentAcademicYear($query)
    {
        return $query->where('academic_year', config('fee.current_academic_year', '2024-2025'));
    }

    // Helper methods
    public function getFormattedTotalAmountAttribute(): string
    {
        return 'PKR ' . number_format($this->total_amount, 2);
    }

    public function getFormattedLateFineAttribute(): string
    {
        return 'PKR ' . number_format($this->late_fine_applied, 2);
    }

    public function getTotalAmountWithFineAttribute(): float
    {
        return $this->total_amount + $this->late_fine_applied;
    }

    public function getFormattedTotalAmountWithFineAttribute(): string
    {
        return 'PKR ' . number_format($this->total_amount_with_fine, 2);
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => '<span class="badge bg-warning">Pending</span>',
            'paid' => '<span class="badge bg-success">Paid</span>',
            'partially_paid' => '<span class="badge bg-info">Partially Paid</span>',
            'overdue' => '<span class="badge bg-danger">Overdue</span>',
            'waived' => '<span class="badge bg-secondary">Waived</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">' . ucfirst($this->status) . '</span>';
    }

    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        return $months[$this->month] ?? 'Unknown';
    }

    public function getFormattedDueDateAttribute(): string
    {
        return $this->due_date->format('M d, Y');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->feePayments()->sum('amount_paid');
    }

    public function getFormattedTotalPaidAttribute(): string
    {
        return 'PKR ' . number_format($this->total_paid, 2);
    }

    public function getRemainingAmountAttribute(): float
    {
        return $this->total_amount_with_fine - $this->total_paid;
    }

    public function getFormattedRemainingAmountAttribute(): string
    {
        return 'PKR ' . number_format($this->remaining_amount, 2);
    }

    public function getPaymentStatusAttribute(): string
    {
        if ($this->status === 'paid') {
            return 'Fully Paid';
        } elseif ($this->status === 'partially_paid') {
            return 'Partially Paid';
        } elseif ($this->status === 'waived') {
            return 'Waived';
        } elseif ($this->status === 'overdue') {
            return 'Overdue';
        } else {
            return 'Pending';
        }
    }

    public function getPaymentProgressPercentageAttribute(): int
    {
        if ($this->total_amount_with_fine == 0) {
            return 0;
        }

        return min(100, ($this->total_paid / $this->total_amount_with_fine) * 100);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === 'partially_paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function isWaived(): bool
    {
        return $this->status === 'waived';
    }

    public function isLate(): bool
    {
        return $this->due_date < now() && !$this->isPaid();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isLate()) {
            return 0;
        }

        return now()->diffInDays($this->due_date);
    }

    public function markAsPaid(): void
    {
        $this->status = 'paid';
        $this->save();
    }

    public function markAsPartiallyPaid(): void
    {
        $this->status = 'partially_paid';
        $this->save();
    }

    public function markAsOverdue(): void
    {
        if ($this->status === 'pending') {
            $this->status = 'overdue';
            $this->save();
        }
    }

    public function markAsWaived(): void
    {
        $this->status = 'waived';
        $this->save();
    }

    public function generateChallanNumber(): string
    {
        $year = date('Y');
        $month = str_pad($this->month, 2, '0', STR_PAD_LEFT);
        $sequence = str_pad(self::whereMonth('created_at', $this->month)->count() + 1, 4, '0', STR_PAD_LEFT);
        
        return "CH-{$year}-{$month}-{$sequence}";
    }

    public function applyLateFine(): void
    {
        if ($this->isLate()) {
            $daysOverdue = $this->days_overdue;
            // Apply late fine based on fee structures
            $this->late_fine_applied = $daysOverdue * 50; // Default PKR 50 per day
            $this->save();
        }
    }
}
