<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Payroll extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'working_days',
        'present_days',
        'absent_days',
        'late_days',
        'leave_days',
        'status',
        'remarks',
        'processed_by',
        'processed_at',
        'finalized_by',
        'finalized_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'working_days' => 'integer',
        'present_days' => 'integer',
        'absent_days' => 'integer',
        'late_days' => 'integer',
        'leave_days' => 'integer',
        'processed_at' => 'datetime',
        'finalized_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the payroll.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who processed the payroll.
     */
    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Get the user who finalized the payroll.
     */
    public function finalizer()
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * Scope a query to only include draft payrolls.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to only include finalized payrolls.
     */
    public function scopeFinalized($query)
    {
        return $query->where('status', 'finalized');
    }

    /**
     * Scope a query to only include payrolls for a specific month and year.
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Scope a query to only include payrolls for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if the payroll is in draft status.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the payroll is finalized.
     */
    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    /**
     * Get the status display text.
     */
    public function getStatusDisplay(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'finalized' => 'Finalized',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'draft' => 'warning',
            'finalized' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Get the formatted month and year.
     */
    public function getFormattedMonth(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * Get the attendance percentage.
     */
    public function getAttendancePercentage(): float
    {
        if ($this->working_days === 0) {
            return 0;
        }

        return ($this->present_days / $this->working_days) * 100;
    }

    /**
     * Get the formatted attendance percentage.
     */
    public function getFormattedAttendancePercentage(): string
    {
        return number_format($this->getAttendancePercentage(), 1) . '%';
    }

    /**
     * Get the gross salary (basic + allowances).
     */
    public function getGrossSalary(): float
    {
        return $this->basic_salary + $this->allowances;
    }

    /**
     * Get the formatted gross salary.
     */
    public function getFormattedGrossSalary(): string
    {
        return number_format($this->getGrossSalary(), 2);
    }

    /**
     * Get the formatted basic salary.
     */
    public function getFormattedBasicSalary(): string
    {
        return number_format($this->basic_salary, 2);
    }

    /**
     * Get the formatted allowances.
     */
    public function getFormattedAllowances(): string
    {
        return number_format($this->allowances, 2);
    }

    /**
     * Get the formatted deductions.
     */
    public function getFormattedDeductions(): string
    {
        return number_format($this->deductions, 2);
    }

    /**
     * Get the formatted net salary.
     */
    public function getFormattedNetSalary(): string
    {
        return number_format($this->net_salary, 2);
    }

    /**
     * Get the attendance summary.
     */
    public function getAttendanceSummary(): array
    {
        return [
            'working_days' => $this->working_days,
            'present_days' => $this->present_days,
            'absent_days' => $this->absent_days,
            'late_days' => $this->late_days,
            'leave_days' => $this->leave_days,
            'percentage' => $this->getAttendancePercentage(),
        ];
    }

    /**
     * Finalize the payroll.
     */
    public function finalize(int $finalizedBy, ?string $remarks = null): bool
    {
        if ($this->isFinalized()) {
            return false;
        }

        return $this->update([
            'status' => 'finalized',
            'finalized_by' => $finalizedBy,
            'finalized_at' => now(),
            'remarks' => $remarks,
        ]);
    }

    /**
     * Check if a user can manage this payroll.
     */
    public function canBeManagedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // HR Manager, Principal, and Super Admin can manage all payrolls
        if (in_array($user->role_id, [1, 2, 8])) { // Super admin, principal, HR manager
            return true;
        }

        return false;
    }

    /**
     * Check if a user can view this payroll.
     */
    public function canBeViewedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // If user can manage, they can view
        if ($this->canBeManagedBy($user)) {
            return true;
        }

        // User can view their own payroll
        if ($this->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Check if the payroll can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    /**
     * Check if the payroll can be finalized.
     */
    public function canBeFinalized(): bool
    {
        return $this->isDraft() && $this->net_salary > 0;
    }

    /**
     * Get the deduction breakdown.
     */
    public function getDeductionBreakdown(): array
    {
        $dailySalary = $this->working_days > 0 ? $this->basic_salary / $this->working_days : 0;
        $absentDeductions = $this->absent_days * $dailySalary;
        $lateDeductions = floor($this->late_days / 3) * $dailySalary; // 3 late days = 1 day deduction
        $totalDeductions = $absentDeductions + $lateDeductions;

        return [
            'daily_salary' => $dailySalary,
            'absent_days' => $this->absent_days,
            'absent_deductions' => $absentDeductions,
            'late_days' => $this->late_days,
            'late_deductions' => $lateDeductions,
            'total_deductions' => $totalDeductions,
        ];
    }

    /**
     * Recalculate the payroll based on attendance.
     */
    public function recalculate(): bool
    {
        if ($this->isFinalized()) {
            return false;
        }

        // Get deduction breakdown
        $deductionBreakdown = $this->getDeductionBreakdown();
        
        // Update payroll with calculated values
        return $this->update([
            'deductions' => $deductionBreakdown['total_deductions'],
            'net_salary' => $this->basic_salary + $this->allowances - $deductionBreakdown['total_deductions'],
        ]);
    }
}
