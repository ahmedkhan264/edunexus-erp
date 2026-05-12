<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    /**
     * Display a listing of the books.
     */
    public function index(Request $request): View
    {
        $query = Book::with(['addedBy']);
        
        // Apply filters
        if ($request->has('category') && $request->category !== 'all') {
            $query->byCategory($request->category);
        }
        
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('language') && $request->language !== 'all') {
            $query->where('language', $request->language);
        }
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->search($request->search);
        }
        
        $books = $query->orderBy('title', 'asc')
                      ->paginate(20);
        
        // Get categories for dropdown
        $categories = Book::distinct()->pluck('category')->sort()->values();
        
        // Get languages for dropdown
        $languages = Book::distinct()->pluck('language')->sort()->values();
        
        return view('library.books.index', compact('books', 'categories', 'languages'));
    }
    
    /**
     * Show the form for creating a new book.
     */
    public function create(): View
    {
        // Check if user can manage books
        if (!Auth::user()->hasRole(['librarian', 'admin', 'principal', 'super_admin'])) {
            abort(403, 'You are not authorized to add books');
        }
        
        $categories = Book::distinct()->pluck('category')->sort()->values();
        $languages = ['English', 'Urdu', 'Arabic', 'French', 'German', 'Spanish', 'Chinese', 'Japanese'];
        
        return view('library.books.create', compact('categories', 'languages'));
    }
    
    /**
     * Store a newly created book in storage.
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user can manage books
        if (!Auth::user()->hasRole(['librarian', 'admin', 'principal', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to add books'
            ], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:20|unique:books,isbn',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'language' => 'required|string|max:50',
            'pages' => 'nullable|integer|min:1|max:10000',
            'total_copies' => 'required|integer|min:1|max:1000',
            'location' => 'nullable|string|max:100',
            'status' => 'required|in:available,unavailable,maintenance',
            'notes' => 'nullable|string|max:1000',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        try {
            DB::beginTransaction();
            
            $bookData = $request->all();
            $bookData['added_by'] = Auth::id();
            
            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                $coverImage = $request->file('cover_image');
                $coverImagePath = $coverImage->store('book-covers', 'public');
                $bookData['cover_image'] = $coverImagePath;
            }
            
            $book = Book::create($bookData);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Book added successfully',
                'book' => $book->load('addedBy')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add book: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified book.
     */
    public function show(Book $book): View
    {
        $book->load(['addedBy', 'loans' => function ($query) {
            $query->with('user')->orderBy('created_at', 'desc')->limit(10);
        }]);
        
        return view('library.books.show', compact('book'));
    }
    
    /**
     * Show the form for editing the specified book.
     */
    public function edit(Book $book): View
    {
        // Check if user can manage books
        if (!$book->canBeManagedBy(Auth::user())) {
            abort(403, 'You are not authorized to edit this book');
        }
        
        $categories = Book::distinct()->pluck('category')->sort()->values();
        $languages = ['English', 'Urdu', 'Arabic', 'French', 'German', 'Spanish', 'Chinese', 'Japanese'];
        
        return view('library.books.edit', compact('book', 'categories', 'languages'));
    }
    
    /**
     * Update the specified book in storage.
     */
    public function update(Request $request, Book $book): JsonResponse
    {
        // Check if user can manage books
        if (!$book->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to edit this book'
            ], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|max:20|unique:books,isbn,' . $book->id,
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'language' => 'required|string|max:50',
            'pages' => 'nullable|integer|min:1|max:10000',
            'total_copies' => 'required|integer|min:1|max:1000',
            'location' => 'nullable|string|max:100',
            'status' => 'required|in:available,unavailable,maintenance',
            'notes' => 'nullable|string|max:1000',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        try {
            DB::beginTransaction();
            
            $bookData = $request->all();
            
            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                // Delete old cover image if exists
                if ($book->cover_image) {
                    Storage::disk('public')->delete($book->cover_image);
                }
                
                $coverImage = $request->file('cover_image');
                $coverImagePath = $coverImage->store('book-covers', 'public');
                $bookData['cover_image'] = $coverImagePath;
            }
            
            $book->update($bookData);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Book updated successfully',
                'book' => $book->load('addedBy')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update book: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified book from storage.
     */
    public function destroy(Book $book): JsonResponse
    {
        // Check if user can manage books
        if (!$book->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to delete this book'
            ], 403);
        }
        
        // Check if book has active loans
        if ($book->activeLoans()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete book with active loans'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            // Delete cover image if exists
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            
            $book->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Book deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete book: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get book details for AJAX requests.
     */
    public function getBookDetails(Book $book): JsonResponse
    {
        return response()->json([
            'success' => true,
            'book' => $book->getFullDetails()
        ]);
    }
    
    /**
     * Toggle book status.
     */
    public function toggleStatus(Request $request, Book $book): JsonResponse
    {
        // Check if user can manage books
        if (!$book->canBeManagedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to manage this book'
            ], 403);
        }
        
        $request->validate([
            'status' => 'required|in:available,unavailable,maintenance'
        ]);
        
        $book->update(['status' => $request->status]);
        
        return response()->json([
            'success' => true,
            'message' => 'Book status updated successfully',
            'book' => $book->getFullDetails()
        ]);
    }
    
    /**
     * Get books for autocomplete/search.
     */
    public function searchBooks(Request $request): JsonResponse
    {
        $search = $request->get('search');
        $limit = $request->get('limit', 10);
        
        $books = Book::search($search)
                    ->available()
                    ->withCount(['activeLoans'])
                    ->having('active_loans_count', '<', \DB::raw('total_copies'))
                    ->limit($limit)
                    ->get(['id', 'title', 'author', 'isbn', 'total_copies']);
        
        $booksData = $books->map(function ($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'isbn' => $book->isbn,
                'total_copies' => $book->total_copies,
                'available_copies' => $book->total_copies - $book->active_loans_count,
                'display_text' => "{$book->title} - {$book->author} (ISBN: {$book->isbn})"
            ];
        });
        
        return response()->json([
            'success' => true,
            'books' => $booksData
        ]);
    }
    
    /**
     * Get book statistics.
     */
    public function getStatistics(): JsonResponse
    {
        $stats = [
            'total_books' => Book::count(),
            'available_books' => Book::available()->count(),
            'unavailable_books' => Book::unavailable()->count(),
            'maintenance_books' => Book::maintenance()->count(),
            'total_copies' => Book::sum('total_copies'),
            'issued_copies' => Book::withCount('activeLoans')->get()->sum('active_loans_count'),
            'categories' => Book::distinct()->pluck('category')->count(),
            'recently_added' => Book::where('created_at', '>=', now()->subDays(30))->count(),
        ];
        
        $stats['available_copies'] = $stats['total_copies'] - $stats['issued_copies'];
        $stats['utilization_rate'] = $stats['total_copies'] > 0 
            ? round(($stats['issued_copies'] / $stats['total_copies']) * 100, 1) 
            : 0;
        
        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }
}
