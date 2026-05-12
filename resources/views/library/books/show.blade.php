@extends('layouts.app')

@section('title', $book->title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $book->title }}</h1>
            <p class="text-muted mb-0">Book details and loan history</p>
        </div>
        <div class="text-end">
            <a href="{{ route('library.books.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Catalog
            </a>
            @if(auth()->user()->hasRole(['librarian', 'admin', 'principal', 'super_admin']))
                <a href="{{ route('library.books.edit', $book) }}" class="btn btn-warning me-2">
                    <i class="fas fa-edit me-2"></i>Edit Book
                </a>
                <button class="btn btn-outline-danger" onclick="deleteBook({{ $book->id }})">
                    <i class="fas fa-trash me-2"></i>Delete
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Book Details -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="book-cover-large">
                                <img src="{{ $book->getCoverImageUrl() }}" alt="{{ $book->title }}" class="img-fluid rounded shadow">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h4 class="mb-3">{{ $book->title }}</h4>
                            <div class="book-details mb-4">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <strong>Author:</strong><br>
                                        <span class="text-muted">{{ $book->author }}</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>ISBN:</strong><br>
                                        <span class="text-muted">{{ $book->isbn }}</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Publisher:</strong><br>
                                        <span class="text-muted">{{ $book->publisher ?: 'N/A' }}</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Publication Year:</strong><br>
                                        <span class="text-muted">{{ $book->getFormattedPublicationYear() }}</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Category:</strong><br>
                                        <span class="badge bg-secondary">{{ $book->category }}</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Language:</strong><br>
                                        <span class="text-muted">{{ $book->language }}</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Pages:</strong><br>
                                        <span class="text-muted">{{ $book->getFormattedPages() }}</span>
                                    </div>
                                    <div class="col-6">
                                        <strong>Location:</strong><br>
                                        <span class="text-muted">{{ $book->location ?: 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="availability-info mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-{{ $book->getAvailabilityColor() }} fs-6">
                                            {{ $book->getAvailabilityStatus() }}
                                        </span>
                                    </div>
                                    <div class="text-muted">
                                        <i class="fas fa-book me-1"></i>
                                        {{ $book->available_copies }} of {{ $book->total_copies }} copies available
                                    </div>
                                </div>
                            </div>
                            
                            @if($book->description)
                                <div class="description mb-3">
                                    <h6>Description</h6>
                                    <p class="text-muted">{{ $book->description }}</p>
                                </div>
                            @endif
                            
                            @if($book->notes)
                                <div class="notes mb-3">
                                    <h6>Additional Notes</h6>
                                    <p class="text-muted">{{ $book->notes }}</p>
                                </div>
                            @endif
                            
                            <div class="book-meta">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>Added by {{ $book->addedBy?->name ?: 'Unknown' }}
                                    <i class="fas fa-calendar ms-3 me-1"></i>{{ $book->created_at->format('M j, Y') }}
                                    <i class="fas fa-clock ms-3 me-1"></i>Updated {{ $book->updated_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Loan History -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-history me-2"></i>Recent Loan History
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($book->loans as $loan)
                        <div class="loan-item mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $loan->user->name }}</strong>
                                    <span class="badge bg-secondary ms-2">{{ $loan->user->role }}</span>
                                    <br>
                                    <small class="text-muted">
                                        Issued: {{ $loan->created_at->format('M j, Y') }}
                                        @if($loan->returned_at)
                                            | Returned: {{ $loan->returned_at->format('M j, Y') }}
                                        @else
                                            | Due: {{ $loan->due_date->format('M j, Y') }}
                                            @if($loan->due_date < now())
                                                <span class="text-danger">(Overdue)</span>
                                            @endif
                                        @endif
                                    </small>
                                </div>
                                <div>
                                    <span class="badge bg-{{ $loan->getStatusColor() }}">
                                        {{ $loan->getStatusDisplay() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-hand-holding-book fa-2x text-muted mb-2"></i>
                            <div class="text-muted">No loan history available</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Actions Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-cog me-2"></i>Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(auth()->user()->hasRole(['librarian', 'admin', 'principal', 'super_admin']))
                            <button class="btn btn-outline-primary" onclick="toggleBookStatus()">
                                <i class="fas fa-exchange-alt me-2"></i>Toggle Status
                            </button>
                            <button class="btn btn-outline-info" onclick="printBookLabel()">
                                <i class="fas fa-print me-2"></i>Print Label
                            </button>
                            <button class="btn btn-outline-success" onclick="duplicateBook()">
                                <i class="fas fa-copy me-2"></i>Duplicate Entry
                            </button>
                        @endif
                        
                        <button class="btn btn-outline-warning" onclick="exportBookData()">
                            <i class="fas fa-download me-2"></i>Export Data
                        </button>
                        
                        <a href="{{ route('library.books.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>View Catalog
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Statistics -->
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stat-value">{{ $book->total_copies }}</div>
                            <div class="stat-label">Total Copies</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-value text-success">{{ $book->available_copies }}</div>
                            <div class="stat-label">Available</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-value text-warning">{{ $book->issued_copies }}</div>
                            <div class="stat-label">Issued</div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-value text-info">{{ $book->getTotalBorrowsCount() }}</div>
                            <div class="stat-label">Total Borrows</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            @if($book->hasAvailableCopies() && auth()->user()->hasRole(['student', 'teacher', 'admin', 'principal', 'super_admin']))
                <div class="card mt-3">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-hand-holding-book me-2"></i>Borrow This Book
                        </h6>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-warning w-100" onclick="borrowBook()">
                            <i class="fas fa-book-reader me-2"></i>Borrow Now
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.book-cover-large {
    text-align: center;
}

.book-cover-large img {
    max-width: 100%;
    height: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
}

.book-details strong {
    color: #495057;
    font-weight: 600;
}

.loan-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .book-cover-large {
        margin-bottom: 20px;
    }
}
</style>
@endsection

@push('scripts')
<script>
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
            setTimeout(() => {
                window.location.href = '{{ route('library.books.index') }}';
            }, 1500);
        } else {
            showToast('Error', data.message || 'Failed to delete book', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to delete book', 'error');
    });
}

function toggleBookStatus() {
    const currentStatus = '{{ $book->status }}';
    const newStatus = currentStatus === 'available' ? 'unavailable' : 'available';
    
    if (!confirm(`Are you sure you want to change the status to ${newStatus}?`)) {
        return;
    }
    
    fetch(`/library/books/{{ $book->id }}/toggle-status`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('Error', data.message || 'Failed to update status', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to update status', 'error');
    });
}

function printBookLabel() {
    window.print();
}

function duplicateBook() {
    showToast('Info', 'Duplicate book feature coming soon', 'info');
}

function exportBookData() {
    const bookData = {
        title: '{{ $book->title }}',
        author: '{{ $book->author }}',
        isbn: '{{ $book->isbn }}',
        publisher: '{{ $book->publisher }}',
        category: '{{ $book->category }}',
        language: '{{ $book->language }}',
        total_copies: {{ $book->total_copies }},
        available_copies: {{ $book->available_copies }},
        status: '{{ $book->getStatusDisplay() }}',
        location: '{{ $book->location }}',
        added_by: '{{ $book->addedBy?->name }}',
        created_at: '{{ $book->created_at->format('Y-m-d') }}'
    };
    
    const dataStr = JSON.stringify(bookData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    
    const exportFileDefaultName = `book_{{ $book->id }}_{{ Str::slug($book->title) }}.json`;
    
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
    
    showToast('Success', 'Book data exported successfully', 'success');
}

function borrowBook() {
    showToast('Info', 'Book borrowing feature will be available in the next module', 'info');
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
</script>
@endpush
