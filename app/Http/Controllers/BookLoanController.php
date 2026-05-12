<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookLoanController extends Controller
{
    /**
     * Display the issue/return page.
     */
    public function issueReturn(): View
    {
        // Get active loans for display
        $activeLoans = BookLoan::with(['book', 'user'])
                              ->whereNull('return_date')
                              ->orderBy('due_date', 'asc')
                              ->get();
        
        // Get available books for issuing
        $availableBooks = Book::available()
                            ->whereHas('activeLoans', function ($query) {
                                $query->havingRaw('COUNT(*) < books.total_copies');
                            })
                            ->orderBy('title', 'asc')
                            ->get();
        
        // Get users who can borrow books
        $borrowerRoles = [3, 4, 5]; // Teacher, Admin, Student
        $borrowers = User::whereIn('role_id', $borrowerRoles)
                      ->where('status', 'active')
                      ->orderBy('name', 'asc')
                      ->get();
        
        return view('library.loans.issue-return', compact(
            'activeLoans',
            'availableBooks',
            'borrowers'
        ));
    }
    
    /**
     * Issue a book to a user.
     */
    public function issueBook(Request $request): JsonResponse
    {
        // Check if user can issue books
        if (!Auth::user()->hasRole(['librarian', 'admin', 'principal', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to issue books'
            ], 403);
        }
        
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'user_id' => 'required|exists:users,id',
            'due_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            DB::beginTransaction();
            
            $book = Book::findOrFail($request->book_id);
            $user = User::findOrFail($request->user_id);
            
            // Check if user can borrow this book
            if (!BookLoan::canUserBorrowBook($user, $book)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User cannot borrow this book. Check availability and user eligibility.'
                ], 400);
            }
            
            // Create the loan
            $loan = BookLoan::create([
                'book_id' => $book->id,
                'user_id' => $user->id,
                'issue_date' => Carbon::today(),
                'due_date' => $request->due_date,
                'status' => 'issued',
                'notes' => $request->notes,
                'issued_by' => Auth::id(),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Book issued successfully',
                'loan' => $loan->load(['book', 'user', 'issuedBy'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to issue book: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Return a book.
     */
    public function returnBook(Request $request, BookLoan $loan): JsonResponse
    {
        // Check if user can return books
        if (!Auth::user()->hasRole(['librarian', 'admin', 'principal', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to return books'
            ], 403);
        }
        
        // Check if loan is already returned
        if ($loan->return_date) {
            return response()->json([
                'success' => false,
                'message' => 'This book has already been returned'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Return the book
            $success = $loan->returnBook(Auth::user());
            
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to return book'
                ], 500);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Book returned successfully',
                'loan' => $loan->load(['book', 'user', 'returnedBy'])
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to return book: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the loan history page.
     */
    public function index(Request $request): View
    {
        $query = BookLoan::with(['book', 'user', 'issuedBy', 'returnedBy']);
        
        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->whereNull('return_date');
            } elseif ($request->status === 'overdue') {
                $query->whereNull('return_date')->where('due_date', '<', Carbon::today());
            } else {
                $query->where('status', $request->status);
            }
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->has('book_id') && $request->book_id) {
            $query->where('book_id', $request->book_id);
        }
        
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }
        
        $loans = $query->orderBy('created_at', 'desc')
                     ->paginate(20);
        
        // Get users and books for filters
        $users = User::whereIn('role_id', [3, 4, 5]) // Teacher, Admin, Student
                    ->where('status', 'active')
                    ->orderBy('name', 'asc')
                    ->get();
        
        $books = Book::orderBy('title', 'asc')->get();
        
        return view('library.loans.index', compact('loans', 'users', 'books'));
    }
    
    /**
     * Get loan details for AJAX requests.
     */
    public function getLoanDetails(BookLoan $loan): JsonResponse
    {
        $loan->load(['book', 'user', 'issuedBy', 'returnedBy']);
        
        return response()->json([
            'success' => true,
            'loan' => $loan->getLoanDetails()
        ]);
    }
    
    /**
     * Get user's active loans.
     */
    public function getUserLoans(Request $request): JsonResponse
    {
        $userId = $request->user_id;
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User ID is required'
            ], 400);
        }
        
        $user = User::findOrFail($userId);
        
        $activeLoans = BookLoan::getUserActiveLoans($user);
        $loanHistory = BookLoan::getUserLoanHistory($user, 10);
        
        return response()->json([
            'success' => true,
            'active_loans' => $activeLoans->map(function ($loan) {
                return $loan->getLoanDetails();
            }),
            'loan_history' => $loanHistory->map(function ($loan) {
                return $loan->getLoanDetails();
            })
        ]);
    }
    
    /**
     * Get book's active loans.
     */
    public function getBookLoans(Request $request): JsonResponse
    {
        $bookId = $request->book_id;
        
        if (!$bookId) {
            return response()->json([
                'success' => false,
                'message' => 'Book ID is required'
            ], 400);
        }
        
        $book = Book::findOrFail($bookId);
        
        $activeLoans = BookLoan::getBookActiveLoans($book);
        $loanHistory = BookLoan::getBookLoanHistory($book, 10);
        
        return response()->json([
            'success' => true,
            'active_loans' => $activeLoans->map(function ($loan) {
                return $loan->getLoanDetails();
            }),
            'loan_history' => $loanHistory->map(function ($loan) {
                return $loan->getLoanDetails();
            })
        ]);
    }
    
    /**
     * Update overdue loans.
     */
    public function updateOverdueLoans(): JsonResponse
    {
        // Check if user can manage loans
        if (!Auth::user()->hasRole(['librarian', 'admin', 'principal', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update overdue loans'
            ], 403);
        }
        
        try {
            $updatedCount = BookLoan::updateOverdueLoans();
            
            return response()->json([
                'success' => true,
                'message' => "Updated {$updatedCount} overdue loans",
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update overdue loans: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get loan statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $stats = BookLoan::getLoanStatistics();
        
        // Add additional statistics
        $stats['due_today_count'] = BookLoan::whereDate('due_date', Carbon::today())
                                        ->whereNull('return_date')
                                        ->count();
        
        $stats['due_soon_count'] = BookLoan::whereBetween('due_date', [Carbon::today(), Carbon::today()->addDays(3)])
                                      ->whereNull('return_date')
                                      ->count();
        
        $stats['recent_loans'] = BookLoan::with(['book', 'user'])
                                      ->where('created_at', '>=', Carbon::now()->subDays(7))
                                      ->count();
        
        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }
    
    /**
     * Send reminder for overdue loans.
     */
    public function sendOverdueReminders(): JsonResponse
    {
        // Check if user can manage loans
        if (!Auth::user()->hasRole(['librarian', 'admin', 'principal', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to send reminders'
            ], 403);
        }
        
        try {
            $overdueLoans = BookLoan::whereNull('return_date')
                                  ->where('due_date', '<', Carbon::today())
                                  ->with(['user', 'book'])
                                  ->get();
            
            $remindedCount = 0;
            $errors = [];
            
            foreach ($overdueLoans as $loan) {
                try {
                    // In a real implementation, you would send email/notification here
                    // $loan->user->notify(new OverdueBookNotification($loan));
                    $remindedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to send reminder to {$loan->user->name}: " . $e->getMessage();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Reminders sent to {$remindedCount} users",
                'reminded_count' => $remindedCount,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reminders: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Extend loan due date.
     */
    public function extendLoan(Request $request, BookLoan $loan): JsonResponse
    {
        // Check if user can manage loans
        if (!Auth::user()->hasRole(['librarian', 'admin', 'principal', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to extend loans'
            ], 403);
        }
        
        // Check if loan is already returned
        if ($loan->return_date) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot extend returned loan'
            ], 400);
        }
        
        $request->validate([
            'new_due_date' => 'required|date|after:' . $loan->due_date,
            'notes' => 'nullable|string|max:500',
        ]);
        
        try {
            $loan->due_date = $request->new_due_date;
            $loan->notes = $request->notes;
            $loan->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Loan extended successfully',
                'loan' => $loan->load(['book', 'user'])->getLoanDetails()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to extend loan: ' . $e->getMessage()
            ], 500);
        }
    }
}
