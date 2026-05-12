@extends('layouts.app')

@section('title', 'Issue & Return Books')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Issue & Return Books</h1>
            <p class="text-muted mb-0">Manage book loans and returns</p>
        </div>
        <div class="text-end">
            <a href="{{ route('library.loans.index') }}" class="btn btn-outline-info me-2">
                <i class="fas fa-history me-2"></i>Loan History
            </a>
            <a href="{{ route('library.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-hand-holding-book fa-2x mb-2"></i>
                    <h5 class="card-title">Active Loans</h5>
                    <h2 class="mb-0">{{ $activeLoans->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h5 class="card-title">Overdue</h5>
                    <h2 class="mb-0">{{ $activeLoans->where('due_date', '<', now())->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h5 class="card-title">Due Today</h5>
                    <h2 class="mb-0">{{ $activeLoans->where('due_date', now()->format('Y-m-d'))->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-book fa-2x mb-2"></i>
                    <h5 class="card-title">Available Books</h5>
                    <h2 class="mb-0">{{ $availableBooks->count() }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Issue Book Form -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Issue New Book
                    </h6>
                </div>
                <div class="card-body">
                    <form id="issueBookForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="book_id" class="form-label">Select Book <span class="text-danger">*</span></label>
                                <select class="form-select" id="book_id" name="book_id" required>
                                    <option value="">Choose a book...</option>
                                    @foreach($availableBooks as $book)
                                        <option value="{{ $book->id }}" data-title="{{ $book->title }}" data-author="{{ $book->author }}" data-isbn="{{ $book->isbn }}" data-copies="{{ $book->available_copies }}">
                                            {{ $book->title }} - {{ $book->author }}
                                            ({{ $book->available_copies }}/{{ $book->total_copies }} available)
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Only books with available copies are shown</div>
                            </div>
                            <div class="col-12">
                                <label for="user_id" class="form-label">Borrower <span class="text-danger">*</span></label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Select borrower...</option>
                                    @foreach($borrowers as $borrower)
                                        <option value="{{ $borrower->id }}" data-role="{{ $borrower->role }}">
                                            {{ $borrower->name }} ({{ ucfirst($borrower->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Teachers, Admins, and Students can borrow books</div>
                            </div>
                            <div class="col-md-6">
                                <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="due_date" name="due_date" required>
                                <div class="form-text">Default: 14 days from today</div>
                            </div>
                            <div class="col-md-6">
                                <label for="loan_duration" class="form-label">Loan Duration</label>
                                <select class="form-select" id="loan_duration" name="loan_duration">
                                    <option value="7">7 days</option>
                                    <option value="14" selected>14 days</option>
                                    <option value="21">21 days</option>
                                    <option value="30">30 days</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Optional notes about this loan..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary me-2" onclick="resetIssueForm()">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </button>
                                    <button type="submit" class="btn btn-success" id="issueBtn">
                                        <i class="fas fa-plus me-2"></i>Issue Book
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Active Loans -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>Active Loans
                        <span class="badge bg-danger ms-2">{{ $activeLoans->count() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="searchLoans" placeholder="Search active loans...">
                        </div>
                    </div>
                    
                    <div class="active-loans-list" style="max-height: 400px; overflow-y: auto;">
                        @forelse($activeLoans as $loan)
                            <div class="loan-item mb-3 p-3 border rounded loan-card" data-loan-id="{{ $loan->id }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $loan->book->title }}</h6>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-user me-1"></i>{{ $loan->user->name }}
                                            <span class="badge bg-secondary ms-2">{{ ucfirst($loan->user->role) }}</span>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>Due: {{ $loan->due_date->format('M j, Y') }}
                                                @if($loan->isOverdue())
                                                    <span class="text-danger ms-2">(Overdue by {{ $loan->getDaysOverdue() }} days)</span>
                                                @elseif($loan->isDueToday())
                                                    <span class="text-warning ms-2">(Due today)</span>
                                                @elseif($loan->isDueSoon())
                                                    <span class="text-warning ms-2">(Due in {{ $loan->getDaysUntilDue() }} days)</span>
                                                @endif
                                            </small>
                                            <span class="badge bg-{{ $loan->getStatusColor() }}">
                                                {{ $loan->getStatusDisplay() }}
                                            </span>
                                        </div>
                                        @if($loan->notes)
                                            <small class="text-muted">
                                                <i class="fas fa-sticky-note me-1"></i>{{ $loan->notes }}
                                            </small>
                                        @endif
                                    </div>
                                    <div class="ms-3">
                                        <button class="btn btn-sm btn-outline-primary me-1" onclick="viewLoanDetails({{ $loan->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success" onclick="returnBook({{ $loan->id }})">
                                            <i class="fas fa-undo"></i> Return
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="fas fa-hand-holding-book fa-2x text-muted mb-2"></i>
                                <div class="text-muted">No active loans</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loan Details Modal -->
<div class="modal fade" id="loanDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Loan Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="loanDetailsContent">
                <!-- Loan details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="modalReturnBtn" onclick="returnBookFromModal()">
                    <i class="fas fa-undo me-2"></i>Return Book
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.loan-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.loan-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.loan-card.overdue {
    border-left-color: #dc3545;
    background-color: #fff5f5;
}

.loan-card.due-today {
    border-left-color: #ffc107;
    background-color: #fffdf5;
}

.loan-card.due-soon {
    border-left-color: #fd7e14;
    background-color: #fff8f0;
}

.active-loans-list::-webkit-scrollbar {
    width: 6px;
}

.active-loans-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.active-loans-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.active-loans-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.card-body {
    position: relative;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .loan-item {
        font-size: 0.875rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
let currentLoanId = null;

document.addEventListener('DOMContentLoaded', function() {
    // Set default due date (14 days from today)
    const dueDateInput = document.getElementById('due_date');
    const loanDurationSelect = document.getElementById('loan_duration');
    
    function updateDueDate() {
        const days = parseInt(loanDurationSelect.value);
        const dueDate = new Date();
        dueDate.setDate(dueDate.getDate() + days);
        dueDateInput.value = dueDate.toISOString().split('T')[0];
    }
    
    loanDurationSelect.addEventListener('change', updateDueDate);
    updateDueDate();
    
    // Book selection
    const bookSelect = document.getElementById('book_id');
    bookSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const copies = selectedOption.dataset.copies;
            if (copies === '0') {
                showToast('Warning', 'This book has no available copies', 'warning');
                this.value = '';
            }
        }
    });
    
    // Issue book form submission
    const issueForm = document.getElementById('issueBookForm');
    const issueBtn = document.getElementById('issueBtn');
    
    issueForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        issueBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Issuing...';
        issueBtn.disabled = true;
        
        const formData = new FormData(issueForm);
        
        fetch('{{ route('library.loans.issue') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Success', data.message, 'success');
                resetIssueForm();
                // Reload the page to show updated active loans
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast('Error', data.message || 'Failed to issue book', 'error');
            }
        })
        .catch(error => {
            showToast('Error', 'Failed to issue book', 'error');
        })
        .finally(() => {
            issueBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Issue Book';
            issueBtn.disabled = false;
        });
    });
    
    // Search active loans
    const searchInput = document.getElementById('searchLoans');
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const loanItems = document.querySelectorAll('.loan-item');
        
        loanItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    // Add overdue styling to loan cards
    updateLoanCardStyles();
});

function updateLoanCardStyles() {
    const loanCards = document.querySelectorAll('.loan-card');
    loanCards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes('overdue')) {
            card.classList.add('overdue');
        } else if (text.includes('due today')) {
            card.classList.add('due-today');
        } else if (text.includes('due in')) {
            card.classList.add('due-soon');
        }
    });
}

function resetIssueForm() {
    document.getElementById('issueBookForm').reset();
    // Reset due date to default
    const loanDurationSelect = document.getElementById('loan_duration');
    loanDurationSelect.value = '14';
    loanDurationSelect.dispatchEvent(new Event('change'));
}

function returnBook(loanId) {
    if (!confirm('Are you sure you want to return this book?')) {
        return;
    }
    
    fetch(`/library/loans/${loanId}/return`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            // Remove the loan card from the list
            const loanCard = document.querySelector(`[data-loan-id="${loanId}"]`);
            if (loanCard) {
                loanCard.style.transition = 'opacity 0.3s ease';
                loanCard.style.opacity = '0';
                setTimeout(() => {
                    loanCard.remove();
                }, 300);
            }
            // Update active loans count
            updateActiveLoansCount();
        } else {
            showToast('Error', data.message || 'Failed to return book', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to return book', 'error');
    });
}

function viewLoanDetails(loanId) {
    currentLoanId = loanId;
    
    fetch(`/library/loans/${loanId}/details`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const loan = data.loan;
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Book Information</h6>
                        <p><strong>Title:</strong> ${loan.book_title}</p>
                        <p><strong>Author:</strong> ${loan.book_author}</p>
                        <p><strong>ISBN:</strong> ${loan.book_isbn}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Borrower Information</h6>
                        <p><strong>Name:</strong> ${loan.borrower_name}</p>
                        <p><strong>Role:</strong> ${loan.borrower_role}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Loan Details</h6>
                        <p><strong>Issue Date:</strong> ${loan.issue_date}</p>
                        <p><strong>Due Date:</strong> ${loan.due_date}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${loan.status_color}">${loan.status}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Additional Information</h6>
                        <p><strong>Loan Duration:</strong> ${loan.loan_duration} days</p>
                        <p><strong>Days Overdue:</strong> ${loan.days_overdue}</p>
                        <p><strong>Fine Amount:</strong> $${loan.fine_amount}</p>
                    </div>
                </div>
                ${loan.notes ? `<hr><p><strong>Notes:</strong> ${loan.notes}</p>` : ''}
            `;
            
            document.getElementById('loanDetailsContent').innerHTML = content;
            
            // Show/hide return button based on loan status
            const returnBtn = document.getElementById('modalReturnBtn');
            returnBtn.style.display = loan.is_active ? 'inline-block' : 'none';
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('loanDetailsModal'));
            modal.show();
        } else {
            showToast('Error', 'Failed to load loan details', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to load loan details', 'error');
    });
}

function returnBookFromModal() {
    if (currentLoanId) {
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('loanDetailsModal'));
        modal.hide();
        
        // Return the book
        returnBook(currentLoanId);
    }
}

function updateActiveLoansCount() {
    const count = document.querySelectorAll('.loan-item').length;
    const badge = document.querySelector('.card-header .badge');
    if (badge) {
        badge.textContent = count;
    }
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
