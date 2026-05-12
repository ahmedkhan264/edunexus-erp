<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'publisher',
        'publication_year',
        'category',
        'description',
        'language',
        'pages',
        'total_copies',
        'location',
        'cover_image',
        'status',
        'notes',
        'added_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'publication_year' => 'integer',
        'pages' => 'integer',
        'total_copies' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who added the book.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get the loans for the book.
     */
    public function loans()
    {
        return $this->hasMany(BookLoan::class);
    }

    /**
     * Get the active loans for the book.
     */
    public function activeLoans()
    {
        return $this->hasMany(BookLoan::class)->whereNull('returned_at');
    }

    /**
     * Scope a query to only include available books.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope a query to only include unavailable books.
     */
    public function scopeUnavailable($query)
    {
        return $query->where('status', 'unavailable');
    }

    /**
     * Scope a query to only include books in maintenance.
     */
    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to search books.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('author', 'like', "%{$search}%")
              ->orWhere('isbn', 'like', "%{$search}%")
              ->orWhere('publisher', 'like', "%{$search}%");
        });
    }

    /**
     * Check if the book is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if the book is unavailable.
     */
    public function isUnavailable(): bool
    {
        return $this->status === 'unavailable';
    }

    /**
     * Check if the book is in maintenance.
     */
    public function isInMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    /**
     * Get the status display text.
     */
    public function getStatusDisplay(): string
    {
        return match($this->status) {
            'available' => 'Available',
            'unavailable' => 'Unavailable',
            'maintenance' => 'Maintenance',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get the status color for display.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'available' => 'success',
            'unavailable' => 'danger',
            'maintenance' => 'warning',
            default => 'secondary'
        };
    }

    /**
     * Get the number of available copies.
     */
    public function getAvailableCopiesAttribute(): int
    {
        return $this->total_copies - $this->activeLoans()->count();
    }

    /**
     * Get the number of issued copies.
     */
    public function getIssuedCopiesAttribute(): int
    {
        return $this->activeLoans()->count();
    }

    /**
     * Check if any copies are available for borrowing.
     */
    public function hasAvailableCopies(): bool
    {
        return $this->isAvailable() && $this->available_copies > 0;
    }

    /**
     * Get the availability status with copy count.
     */
    public function getAvailabilityStatus(): string
    {
        if (!$this->isAvailable()) {
            return $this->getStatusDisplay();
        }

        $available = $this->available_copies;
        $total = $this->total_copies;

        if ($available === 0) {
            return 'All Copies Issued';
        } elseif ($available < $total) {
            return "{$available} of {$total} Available";
        } else {
            return 'All Copies Available';
        }
    }

    /**
     * Get the availability color.
     */
    public function getAvailabilityColor(): string
    {
        if (!$this->isAvailable()) {
            return $this->getStatusColor();
        }

        $available = $this->available_copies;
        $total = $this->total_copies;

        if ($available === 0) {
            return 'danger';
        } elseif ($available < $total) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    /**
     * Get the formatted publication year.
     */
    public function getFormattedPublicationYear(): string
    {
        return $this->publication_year ? (string) $this->publication_year : 'N/A';
    }

    /**
     * Get the formatted pages count.
     */
    public function getFormattedPages(): string
    {
        return $this->pages ? number_format($this->pages) : 'N/A';
    }

    /**
     * Get the cover image URL or placeholder.
     */
    public function getCoverImageUrl(): string
    {
        if ($this->cover_image) {
            return asset('storage/' . $this->cover_image);
        }

        // Return placeholder image URL
        return 'https://via.placeholder.com/200x300/007bff/ffffff?text=No+Cover';
    }

    /**
     * Get the book's full details as an array.
     */
    public function getFullDetails(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'publisher' => $this->publisher,
            'publication_year' => $this->getFormattedPublicationYear(),
            'category' => $this->category,
            'description' => $this->description,
            'language' => $this->language,
            'pages' => $this->getFormattedPages(),
            'total_copies' => $this->total_copies,
            'available_copies' => $this->available_copies,
            'issued_copies' => $this->issued_copies,
            'location' => $this->location,
            'status' => $this->getStatusDisplay(),
            'status_color' => $this->getStatusColor(),
            'availability_status' => $this->getAvailabilityStatus(),
            'availability_color' => $this->getAvailabilityColor(),
            'notes' => $this->notes,
            'added_by' => $this->addedBy?->name,
            'created_at' => $this->created_at->format('M j, Y'),
            'updated_at' => $this->updated_at->format('M j, Y'),
        ];
    }

    /**
     * Check if a user can manage this book.
     */
    public function canBeManagedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Librarian, Admin, Principal, and Super Admin can manage books
        if (in_array($user->role_id, [1, 2, 4, 9])) { // Super admin, principal, admin, librarian
            return true;
        }

        return false;
    }

    /**
     * Check if a user can borrow this book.
     */
    public function canBeBorrowedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Check if book is available and has copies
        if (!$this->hasAvailableCopies()) {
            return false;
        }

        // Check if user has any overdue books
        $hasOverdueBooks = BookLoan::where('user_id', $user->id)
                                ->whereNull('returned_at')
                                ->where('due_date', '<', now())
                                ->exists();

        if ($hasOverdueBooks) {
            return false;
        }

        return true;
    }

    /**
     * Get the total number of times this book has been borrowed.
     */
    public function getTotalBorrowsCount(): int
    {
        return $this->loans()->count();
    }

    /**
     * Get the average rating (placeholder for future implementation).
     */
    public function getAverageRating(): float
    {
        // This would be implemented when we add a rating system
        return 0.0;
    }

    /**
     * Get the total number of reviews (placeholder for future implementation).
     */
    public function getTotalReviews(): int
    {
        // This would be implemented when we add a review system
        return 0;
    }
}
