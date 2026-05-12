<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class BookLoan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'book_id',
        'user_id',
        'issue_date',
        'due_date',
        'return_date',
        'status',
        'notes',
        'issued_by',
        'returned_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'return_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the book that was loaned.
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * Get the user who borrowed the book.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who issued the book.
     */
    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get the user who returned the book.
     */
    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by');
    }

    /**
     * Scope a query to only include issued loans.
     */
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope a query to only include returned loans.
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }

    /**
     * Scope a query to only include overdue loans.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    /**
     * Scope a query to only include active loans (not returned).
     */
    public function scopeActive($query)
    {
        return $query->whereNull('return_date');
    }

    /**
     * Scope a query to only include loans due today.
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', Carbon::today());
    }

    /**
     * Scope a query to only include overdue loans (due date passed and not returned).
     */
    public function scopePastDue($query)
    {
        return $query->where('due_date', '<', Carbon::today())
                    ->whereNull('return_date');
    }

    /**
     * Scope a query to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to filter by book.
     */
    public function scopeByBook($query, $bookId)
    {
        return $query->where('book_id', $bookId);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    /**
     * Check if the loan is currently active (not returned).
     */
    public function isActive(): bool
    {
        return is_null($this->return_date);
    }

    /**
     * Check if the loan is overdue.
     */
    public function isOverdue(): bool
    {
        if ($this->return_date) {
            return false; // Already returned
        }
        
        return $this->due_date < Carbon::today();
    }

    /**
     * Check if the loan is due today.
     */
    public function isDueToday(): bool
    {
        return $this->due_date->isToday();
    }

    /**
     * Check if the loan is due soon (within 3 days).
     */
    public function isDueSoon(): bool
    {
        if ($this->return_date) {
            return false; // Already returned
        }
        
        return $this->due_date->between(Carbon::today(), Carbon::today()->addDays(3));
    }

    /**
     * Get the number of days overdue.
     */
    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        return Carbon::today()->diffInDays($this->due_date);
    }

    /**
     * Get the number of days until due.
     */
    public function getDaysUntilDue(): int
    {
        if ($this->return_date) {
            return 0; // Already returned
        }
        
        return max(0, Carbon::today()->diffInDays($this->due_date, false));
    }

    /**
     * Get the fine amount (assuming $10 per day overdue).
     */
    public function getFineAmount(): float
    {
        $daysOverdue = $this->getDaysOverdue();
        return $daysOverdue * 10; // $10 per day
    }

    /**
     * Get the status display text.
     */
    public function getStatusDisplay(): string
    {
        return match($this->status) {
            'issued' => 'Issued',
            'returned' => 'Returned',
            'overdue' => 'Overdue',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColor(): string
    {
        if ($this->isOverdue()) {
            return 'danger';
        }
        
        if ($this->isDueSoon()) {
            return 'warning';
        }
        
        return match($this->status) {
            'issued' => 'primary',
            'returned' => 'success',
            'overdue' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get the formatted issue date.
     */
    public function getFormattedIssueDate(): string
    {
        return $this->issue_date->format('M j, Y');
    }

    /**
     * Get the formatted due date.
     */
    public function getFormattedDueDate(): string
    {
        return $this->due_date->format('M j, Y');
    }

    /**
     * Get the formatted return date.
     */
    public function getFormattedReturnDate(): string
    {
        return $this->return_date ? $this->return_date->format('M j, Y') : 'Not returned';
    }

    /**
     * Get the loan duration in days.
     */
    public function getLoanDuration(): int
    {
        $endDate = $this->return_date ?: Carbon::today();
        return $this->issue_date->diffInDays($endDate);
    }

    /**
     * Get the loan details as an array.
     */
    public function getLoanDetails(): array
    {
        return [
            'id' => $this->id,
            'book_title' => $this->book->title,
            'book_author' => $this->book->author,
            'book_isbn' => $this->book->isbn,
            'borrower_name' => $this->user->name,
            'borrower_role' => $this->getRoleName($this->user->role_id),
            'issue_date' => $this->getFormattedIssueDate(),
            'due_date' => $this->getFormattedDueDate(),
            'return_date' => $this->getFormattedReturnDate(),
            'status' => $this->getStatusDisplay(),
            'status_color' => $this->getStatusColor(),
            'is_active' => $this->isActive(),
            'is_overdue' => $this->isOverdue(),
            'is_due_today' => $this->isDueToday(),
            'is_due_soon' => $this->isDueSoon(),
            'days_overdue' => $this->getDaysOverdue(),
            'days_until_due' => $this->getDaysUntilDue(),
            'fine_amount' => $this->getFineAmount(),
            'loan_duration' => $this->getLoanDuration(),
            'notes' => $this->notes,
            'issued_by' => $this->issuedBy?->name,
            'returned_by' => $this->returnedBy?->name,
            'created_at' => $this->created_at->format('M j, Y H:i'),
            'updated_at' => $this->updated_at->format('M j, Y H:i'),
        ];
    }

    /**
     * Get role name by role ID.
     */
    private function getRoleName(int $roleId): string
    {
        $roles = [
            1 => 'Super Admin',
            2 => 'Principal',
            3 => 'Teacher',
            4 => 'Admin',
            5 => 'Student',
            6 => 'Parent',
            7 => 'Accountant',
            8 => 'HR Manager',
            9 => 'Librarian',
        ];
        
        return $roles[$roleId] ?? 'Unknown';
    }

    /**
     * Return the book.
     */
    public function returnBook(?User $returnedBy = null): bool
    {
        if ($this->return_date) {
            return false; // Already returned
        }
        
        $this->return_date = Carbon::today();
        $this->status = $this->isOverdue() ? 'overdue' : 'returned';
        $this->returned_by = $returnedBy?->id;
        
        return $this->save();
    }

    /**
     * Mark as overdue.
     */
    public function markAsOverdue(): bool
    {
        if ($this->return_date) {
            return false; // Already returned
        }
        
        if (!$this->isOverdue()) {
            return false; // Not overdue yet
        }
        
        $this->status = 'overdue';
        return $this->save();
    }

    /**
     * Check if a user can borrow this book.
     */
    public static function canUserBorrowBook(User $user, Book $book): bool
    {
        // Check if book has available copies
        if (!$book->hasAvailableCopies()) {
            return false;
        }
        
        // Check if user has overdue books
        $hasOverdueBooks = BookLoan::where('user_id', $user->id)
                                ->whereNull('return_date')
                                ->where('due_date', '<', Carbon::today())
                                ->exists();
        
        if ($hasOverdueBooks) {
            return false;
        }
        
        // Check if user already has this book
        $hasActiveLoan = BookLoan::where('user_id', $user->id)
                              ->where('book_id', $book->id)
                              ->whereNull('return_date')
                              ->exists();
        
        if ($hasActiveLoan) {
            return false;
        }
        
        return true;
    }

    /**
     * Create a new book loan.
     */
    public static function issueBook(Book $book, User $user, ?User $issuedBy = null, int $loanDays = 14): ?BookLoan
    {
        if (!self::canUserBorrowBook($user, $book)) {
            return null;
        }
        
        return self::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'issue_date' => Carbon::today(),
            'due_date' => Carbon::today()->addDays($loanDays),
            'status' => 'issued',
            'issued_by' => $issuedBy?->id,
        ]);
    }

    /**
     * Get user's active loans.
     */
    public static function getUserActiveLoans(User $user)
    {
        return self::where('user_id', $user->id)
                  ->whereNull('return_date')
                  ->with('book')
                  ->get();
    }

    /**
     * Get user's loan history.
     */
    public static function getUserLoanHistory(User $user, int $limit = 10)
    {
        return self::where('user_id', $user->id)
                  ->with('book')
                  ->orderBy('created_at', 'desc')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Get book's active loans.
     */
    public static function getBookActiveLoans(Book $book)
    {
        return self::where('book_id', $book->id)
                  ->whereNull('return_date')
                  ->with('user')
                  ->get();
    }

    /**
     * Get book's loan history.
     */
    public static function getBookLoanHistory(Book $book, int $limit = 10)
    {
        return self::where('book_id', $book->id)
                  ->with('user')
                  ->orderBy('created_at', 'desc')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Update overdue loans.
     */
    public static function updateOverdueLoans(): int
    {
        return self::whereNull('return_date')
                  ->where('due_date', '<', Carbon::today())
                  ->where('status', '!=', 'overdue')
                  ->update(['status' => 'overdue']);
    }

    /**
     * Get loan statistics.
     */
    public static function getLoanStatistics(): array
    {
        $totalLoans = self::count();
        $activeLoans = self::whereNull('return_date')->count();
        $overdueLoans = self::whereNull('return_date')
                            ->where('due_date', '<', Carbon::today())
                            ->count();
        $returnedLoans = self::whereNotNull('return_date')->count();
        $dueTodayLoans = self::whereDate('due_date', Carbon::today())
                           ->whereNull('return_date')
                           ->count();
        $dueSoonLoans = self::whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(3)])
                          ->whereNull('return_date')
                          ->count();
        
        return [
            'total_loans' => $totalLoans,
            'active_loans' => $activeLoans,
            'overdue_loans' => $overdueLoans,
            'returned_loans' => $returnedLoans,
            'due_today_loans' => $dueTodayLoans,
            'due_soon_loans' => $dueSoonLoans,
            'overdue_rate' => $activeLoans > 0 ? round(($overdueLoans / $activeLoans) * 100, 1) : 0,
        ];
    }
}
