<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\LibraryFine;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LibraryDashboardService
{
    /**
     * Get library dashboard data with caching.
     */
    public static function getDashboardData(): array
    {
        return Cache::remember('library_dashboard_data', 3600, function () {
            return [
                'kpi_cards' => self::getKpiCards(),
                'recent_loans' => self::getRecentLoans(),
                'low_stock_books' => self::getLowStockBooks(),
                'overdue_books' => self::getOverdueBooks(),
                'popular_books' => self::getPopularBooks(),
                'monthly_stats' => self::getMonthlyStats(),
            ];
        });
    }
    
    /**
     * Get KPI cards data.
     */
    private static function getKpiCards(): array
    {
        // Total books
        $totalBooks = Book::count();
        
        // Books currently issued
        $booksIssued = BookLoan::whereNull('returned_at')->count();
        
        // Overdue books
        $overdueBooks = BookLoan::whereNull('returned_at')
                              ->where('due_date', '<', Carbon::today())
                              ->count();
        
        // Fines collected this month
        $finesCollected = LibraryFine::whereMonth('created_at', Carbon::now()->month)
                                  ->whereYear('created_at', Carbon::now()->year)
                                  ->where('status', 'paid')
                                  ->sum('amount');
        
        return [
            'total_books' => $totalBooks,
            'books_issued' => $booksIssued,
            'overdue_books' => $overdueBooks,
            'fines_collected' => $finesCollected,
        ];
    }
    
    /**
     * Get recent loan activity.
     */
    private static function getRecentLoans(): array
    {
        $recentLoans = BookLoan::with(['book', 'user'])
                            ->orderBy('created_at', 'desc')
                            ->limit(5)
                            ->get();
        
        return $recentLoans->map(function ($loan) {
            return [
                'id' => $loan->id,
                'book_title' => $loan->book->title,
                'book_author' => $loan->book->author,
                'borrower_name' => $loan->user->name,
                'borrower_role' => self::getRoleName($loan->user->role_id),
                'issue_date' => $loan->created_at->format('M j, Y'),
                'due_date' => $loan->due_date->format('M j, Y'),
                'status' => $loan->getStatusDisplay(),
                'status_color' => $loan->getStatusColor(),
                'is_overdue' => $loan->isOverdue(),
            ];
        })->toArray();
    }
    
    /**
     * Get low stock books (less than 3 copies available).
     */
    private static function getLowStockBooks(): array
    {
        $lowStockBooks = Book::withCount(['loans' => function ($query) {
                                    $query->whereNull('returned_at');
                                }])
                                ->get()
                                ->filter(function ($book) {
                                    $availableCopies = $book->total_copies - $book->loans_count;
                                    return $availableCopies < 3;
                                })
                                ->take(10);
        
        return $lowStockBooks->map(function ($book) {
            $availableCopies = $book->total_copies - $book->loans_count;
            
            return [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn,
                'total_copies' => $book->total_copies,
                'available_copies' => $availableCopies,
                'issued_copies' => $book->loans_count,
                'status' => $availableCopies === 0 ? 'Out of Stock' : 'Low Stock',
                'status_color' => $availableCopies === 0 ? 'danger' : 'warning',
            ];
        })->toArray();
    }
    
    /**
     * Get overdue books.
     */
    private static function getOverdueBooks(): array
    {
        $overdueBooks = BookLoan::with(['book', 'user'])
                              ->whereNull('returned_at')
                              ->where('due_date', '<', Carbon::today())
                              ->orderBy('due_date', 'asc')
                              ->limit(10)
                              ->get();
        
        return $overdueBooks->map(function ($loan) {
            $daysOverdue = Carbon::today()->diffInDays($loan->due_date);
            $fineAmount = $daysOverdue * 10; // Assuming $10 per day fine
            
            return [
                'id' => $loan->id,
                'book_title' => $loan->book->title,
                'borrower_name' => $loan->user->name,
                'borrower_role' => self::getRoleName($loan->user->role_id),
                'due_date' => $loan->due_date->format('M j, Y'),
                'days_overdue' => $daysOverdue,
                'fine_amount' => $fineAmount,
                'fine_status' => $loan->hasUnpaidFines() ? 'Unpaid' : 'Paid',
                'fine_color' => $loan->hasUnpaidFines() ? 'danger' : 'success',
            ];
        })->toArray();
    }
    
    /**
     * Get popular books (most borrowed).
     */
    private static function getPopularBooks(): array
    {
        $popularBooks = Book::withCount(['loans' => function ($query) {
                                    $query->where('created_at', '>=', Carbon::now()->subMonths(6));
                                }])
                                ->orderBy('loans_count', 'desc')
                                ->limit(5)
                                ->get();
        
        return $popularBooks->map(function ($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'category' => $book->category,
                'times_borrowed' => $book->loans_count,
                'available_copies' => $book->total_copies - $book->loans()->whereNull('returned_at')->count(),
            ];
        })->toArray();
    }
    
    /**
     * Get monthly statistics for the last 6 months.
     */
    private static function getMonthlyStats(): array
    {
        $stats = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->format('M Y');
            
            // Books issued in this month
            $booksIssued = BookLoan::whereMonth('created_at', $date->month)
                                 ->whereYear('created_at', $date->year)
                                 ->count();
            
            // Books returned in this month
            $booksReturned = BookLoan::whereMonth('returned_at', $date->month)
                                   ->whereYear('returned_at', $date->year)
                                   ->count();
            
            // Fines collected in this month
            $finesCollected = LibraryFine::whereMonth('created_at', $date->month)
                                      ->whereYear('created_at', $date->year)
                                      ->where('status', 'paid')
                                      ->sum('amount');
            
            $stats[] = [
                'month' => $month,
                'books_issued' => $booksIssued,
                'books_returned' => $booksReturned,
                'fines_collected' => $finesCollected,
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get role name by role ID.
     */
    private static function getRoleName(int $roleId): string
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
     * Clear library dashboard cache.
     */
    public static function clearCache(): void
    {
        Cache::forget('library_dashboard_data');
    }
}
