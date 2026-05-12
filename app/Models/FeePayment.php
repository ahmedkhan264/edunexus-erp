<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    protected $fillable = [
        'challan_id',
        'student_id',
        'payment_date',
        'amount_paid',
        'payment_method',
        'transaction_id',
        'receipt_number',
        'received_by',
        'remarks',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // Relationships
    public function challan(): BelongsTo
    {
        return $this->belongsTo(FeeChallan::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Scopes
    public function scopeByChallan($query, $challanId)
    {
        return $query->where('challan_id', $challanId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('payment_date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    public function scopeByMonth($query, $month, $year = null)
    {
        $year = $year ?? now()->year;
        return $query->whereMonth('payment_date', $month)->whereYear('payment_date', $year);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('payment_date', $year);
    }

    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }

    public function scopeBankTransfer($query)
    {
        return $query->where('payment_method', 'bank_transfer');
    }

    public function scopeCreditCard($query)
    {
        return $query->where('payment_method', 'credit_card');
    }

    public function scopeOnline($query)
    {
        return $query->where('payment_method', 'online');
    }

    // Helper methods
    public function getFormattedAmountPaidAttribute(): string
    {
        return 'PKR ' . number_format($this->amount_paid, 2);
    }

    public function getPaymentMethodBadgeAttribute(): string
    {
        $badges = [
            'cash' => '<span class="badge bg-success">Cash</span>',
            'bank_transfer' => '<span class="badge bg-primary">Bank Transfer</span>',
            'credit_card' => '<span class="badge bg-info">Credit Card</span>',
            'online' => '<span class="badge bg-warning">Online</span>',
        ];

        return $badges[$this->payment_method] ?? '<span class="badge bg-secondary">' . ucfirst($this->payment_method) . '</span>';
    }

    public function getFormattedPaymentDateAttribute(): string
    {
        return $this->payment_date->format('M d, Y h:i A');
    }

    public function getFormattedPaymentDateOnlyAttribute(): string
    {
        return $this->payment_date->format('M d, Y');
    }

    public function getPaymentTimeAttribute(): string
    {
        return $this->payment_date->format('h:i A');
    }

    public function getPaymentMethodDisplayNameAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'Cash',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card',
            'online' => 'Online Payment',
            default => ucfirst($this->payment_method),
        };
    }

    public function hasTransactionId(): bool
    {
        return !empty($this->transaction_id);
    }

    public function isCashPayment(): bool
    {
        return $this->payment_method === 'cash';
    }

    public function isBankTransfer(): bool
    {
        return $this->payment_method === 'bank_transfer';
    }

    public function isCreditCard(): bool
    {
        return $this->payment_method === 'credit_card';
    }

    public function isOnlinePayment(): bool
    {
        return $this->payment_method === 'online';
    }

    public function generateReceiptNumber(): string
    {
        $date = $this->payment_date->format('Ymd');
        $sequence = str_pad(self::whereDate('payment_date', $this->payment_date->format('Y-m-d'))->count() + 1, 4, '0', STR_PAD_LEFT);
        
        return "RCPT-{$date}-{$sequence}";
    }

    public function getPaymentDetailsAttribute(): array
    {
        return [
            'amount' => $this->formatted_amount_paid,
            'method' => $this->payment_method_display_name,
            'date' => $this->formatted_payment_date,
            'receipt' => $this->receipt_number,
            'transaction_id' => $this->transaction_id,
            'received_by' => $this->receiver?->name ?? 'Unknown',
        ];
    }

    public function getPaymentSummaryAttribute(): string
    {
        $details = [];
        $details[] = $this->formatted_amount_paid;
        $details[] = $this->payment_method_display_name;
        $details[] = $this->formatted_payment_date_only;
        
        if ($this->hasTransactionId()) {
            $details[] = "TXN: {$this->transaction_id}";
        }
        
        return implode(' | ', $details);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', now());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('payment_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('payment_date', now()->year);
    }

    public function scopeLastMonth($query)
    {
        return $query->whereMonth('payment_date', now()->subMonth()->month)
                    ->whereYear('payment_date', now()->subMonth()->year);
    }

    public function scopeLastYear($query)
    {
        return $query->whereYear('payment_date', now()->subYear()->year);
    }

    public function scopeWithTransactionId($query)
    {
        return $query->whereNotNull('transaction_id');
    }

    public function scopeWithoutTransactionId($query)
    {
        return $query->whereNull('transaction_id');
    }

    public function getPaymentIconAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => '<i class="fas fa-money-bill-wave text-success"></i>',
            'bank_transfer' => '<i class="fas fa-university text-primary"></i>',
            'credit_card' => '<i class="fas fa-credit-card text-info"></i>',
            'online' => '<i class="fas fa-globe text-warning"></i>',
            default => '<i class="fas fa-receipt text-secondary"></i>',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_method) {
            'cash' => 'success',
            'bank_transfer' => 'primary',
            'credit_card' => 'info',
            'online' => 'warning',
            default => 'secondary',
        };
    }
}
