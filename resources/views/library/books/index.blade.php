@extends('layouts.app')

@section('title', 'Book Catalog')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Book Catalog</h1>
            <p class="text-muted mb-0">Manage library books and inventory</p>
        </div>
        <div class="text-end">
            @if(auth()->user()->hasRole(['librarian', 'admin', 'principal', 'super_admin']))
                <a href="{{ route('library.books.create') }}" class="btn btn-primary me-2">
                    <i class="fas fa-plus me-2"></i>Add Book
                </a>
            @endif
            <button class="btn btn-outline-info" onclick="refreshBooks()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="searchForm" method="GET" action="{{ route('library.books.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Books</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Search by title, author, ISBN...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="all">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all">All Status</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="unavailable" {{ request('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="language" class="form-label">Language</label>
                        <select class="form-select" id="language" name="language">
                            <option value="all">All Languages</option>
                            @foreach($languages as $language)
                                <option value="{{ $language }}" {{ request('language') == $language ? 'selected' : '' }}>
                                    {{ $language }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Books Grid -->
    <div class="row">
        @forelse($books as $book)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 book-card">
                    <div class="book-cover">
                        <img src="{{ $book->getCoverImageUrl() }}" alt="{{ $book->title }}" class="card-img-top">
                        <div class="book-status">
                            <span class="badge bg-{{ $book->getAvailabilityColor() }}">
                                {{ $book->getAvailabilityStatus() }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h6 class="card-title text-truncate" title="{{ $book->title }}">
                            {{ $book->title }}
                        </h6>
                        <p class="card-text text-muted small mb-2">
                            <i class="fas fa-user-edit me-1"></i>{{ $book->author }}
                        </p>
                        <p class="card-text text-muted small mb-2">
                            <i class="fas fa-barcode me-1"></i>{{ $book->isbn }}
                        </p>
                        <p class="card-text text-muted small mb-2">
                            <i class="fas fa-tag me-1"></i>{{ $book->category }}
                        </p>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">
                                    <i class="fas fa-book me-1"></i>{{ $book->available_copies }}/{{ $book->total_copies }} available
                                </small>
                                <span class="badge bg-{{ $book->getStatusColor() }}">
                                    {{ $book->getStatusDisplay() }}
                                </span>
                            </div>
                            
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('library.books.show', $book) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->user()->hasRole(['librarian', 'admin', 'principal', 'super_admin']))
                                    <a href="{{ route('library.books.edit', $book) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteBook({{ $book->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No books found</h4>
                    <p class="text-muted">Try adjusting your search criteria or add some books to the catalog.</p>
                    @if(auth()->user()->hasRole(['librarian', 'admin', 'principal', 'super_admin']))
                        <a href="{{ route('library.books.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add Your First Book
                        </a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($books->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $books->links() }}
        </div>
    @endif
</div>

<style>
.book-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.book-cover {
    position: relative;
    height: 250px;
    overflow: hidden;
}

.book-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.book-card:hover .book-cover img {
    transform: scale(1.05);
}

.book-status {
    position: absolute;
    top: 10px;
    right: 10px;
}

.card-title {
    font-size: 0.9rem;
    font-weight: 600;
    line-height: 1.3;
    min-height: 2.6rem;
}

.btn-group .btn {
    flex: 1;
}

@media (max-width: 768px) {
    .book-cover {
        height: 200px;
    }
    
    .card-title {
        font-size: 0.8rem;
        min-height: 2rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
function refreshBooks() {
    const refreshBtn = document.querySelector('button[onclick="refreshBooks()"]');
    const originalText = refreshBtn.innerHTML;
    
    // Show loading state
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    refreshBtn.disabled = true;
    
    fetch('/library/books/statistics', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Book catalog refreshed successfully', 'success');
            // Reload the page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('Error', 'Failed to refresh book catalog', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to refresh book catalog', 'error');
    })
    .finally(() => {
        // Restore button state
        refreshBtn.innerHTML = originalText;
        refreshBtn.disabled = false;
    });
}

function deleteBook(bookId) {
    if (!confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/library/books/${bookId}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            // Remove the book card from the page
            const bookCard = document.querySelector(`[onclick="deleteBook(${bookId})"]`).closest('.col-lg-3');
            if (bookCard) {
                bookCard.style.transition = 'opacity 0.3s ease';
                bookCard.style.opacity = '0';
                setTimeout(() => {
                    bookCard.remove();
                }, 300);
            }
        } else {
            showToast('Error', data.message || 'Failed to delete book', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to delete book', 'error');
    });
}

function showToast(title, message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <strong>${title}:</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Auto-submit form on filter change
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('searchForm');
    const inputs = form.querySelectorAll('select');
    
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            form.submit();
        });
    });
});
</script>
@endpush
