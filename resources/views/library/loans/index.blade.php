@extends('layouts.app')

@section('title', 'Loan History')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Loan History</h1>
            <p class="text-muted mb-0">View and manage all book loans</p>
        </div>
        <div class="text-end">
            <a href="{{ route('library.loans.issue-return') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus-circle me-2"></i>Issue/Return
            </a>
            <button class="btn btn-outline-warning" onclick="updateOverdueLoans()">
                <i class="fas fa-sync-alt me-2"></i>Update Overdue
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-book fa-2x mb-2"></i>
                    <h6 class="card-title">Total Loans</h6>
                    <h4 class="mb-0" id="totalLoans">-</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-hand-holding-book fa-2x mb-2"></i>
                    <h6 class="card-title">Active</h6>
                    <h4 class="mb-0" id="activeLoans">-</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h6 class="card-title">Overdue</h6>
                    <h4 class="mb-0" id="overdueLoans">-</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h6 class="card-title">Returned</h6>
                    <h4 class="mb-0" id="returnedLoans">-</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h6 class="card-title">Due Today</h6>
                    <h4 class="mb-0" id="dueTodayLoans">-</h4>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-percentage fa-2x mb-2"></i>
                    <h6 class="card-title">Overdue Rate</h6>
                    <h4 class="mb-0" id="overdueRate">-</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('library.loans.index') }}">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                            <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                            <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="user_id" class="form-label">Borrower</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">All Borrowers</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="book_id" class="form-label">Book</label>
                        <select class="form-select" id="book_id" name="book_id">
                            <option value="">All Books</option>
                            @foreach($books as $book)
                                <option value="{{ $book->id }}" {{ request('book_id') == $book->id ? 'selected' : '' }}>
                                    {{ $book->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
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

    <!-- Loans Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Loan Records
                <span class="badge bg-secondary ms-2">{{ $loans->total() }} records</span>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Borrower</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Fine</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr class="loan-row" data-loan-id="{{ $loan->id }}">
                                <td>
                                    <div>
                                        <strong>{{ $loan->book->title }}</strong><br>
                                        <small class="text-muted">{{ $loan->book->author }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $loan->user->name }}</strong><br>
                                        <small class="text-muted">{{ ucfirst($loan->user->role) }}</small>
                                    </div>
                                </td>
                                <td>{{ $loan->issue_date->format('M j, Y') }}</td>
                                <td>
                                    {{ $loan->due_date->format('M j, Y') }}
                                    @if($loan->isOverdue())
                                        <span class="badge bg-danger ms-1">Overdue</span>
                                    @elseif($loan->isDueToday())
                                        <span class="badge bg-warning ms-1">Today</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $loan->return_date ? $loan->return_date->format('M j, Y') : '-' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $loan->getStatusColor() }}">
                                        {{ $loan->getStatusDisplay() }}
                                    </span>
                                </td>
                                <td>
                                    @if($loan->isOverdue())
                                        ${{ number_format($loan->getFineAmount(), 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-info" onclick="viewLoanDetails({{ $loan->id }})" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($loan->isActive())
                                            <button class="btn btn-sm btn-outline-success" onclick="returnBook({{ $loan->id }})" title="Return Book">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-warning" onclick="extendLoan({{ $loan->id }})" title="Extend Loan">
                                                <i class="fas fa-calendar-plus"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-hand-holding-book fa-2x text-muted mb-2"></i>
                                    <div class="text-muted">No loan records found</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($loans->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $loans->links() }}
                </div>
            @endif
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
                <button type="button" class="btn btn-warning" id="modalExtendBtn" onclick="extendLoanFromModal()">
                    <i class="fas fa-calendar-plus me-2"></i>Extend Loan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Extend Loan Modal -->
<div class="modal fade" id="extendLoanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Extend Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="extendLoanForm">
                    @csrf
                    <input type="hidden" id="extendLoanId" name="loan_id">
                    <div class="mb-3">
                        <label for="new_due_date" class="form-label">New Due Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="new_due_date" name="new_due_date" required>
                        <div class="form-text">Select a new due date after the current due date</div>
                    </div>
                    <div class="mb-3">
                        <label for="extend_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="extend_notes" name="notes" rows="2" placeholder="Optional notes for the extension..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" onclick="submitExtendLoan()">
                    <i class="fas fa-calendar-plus me-2"></i>Extend Loan
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.loan-row:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75rem;
}

.table-responsive {
    border-radius: 0.375rem;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
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
    // Load statistics
    loadStatistics();
    
    // Auto-submit filters on change
    const filterForm = document.getElementById('filterForm');
    const filterSelects = filterForm.querySelectorAll('select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            filterForm.submit();
        });
    });
});

function loadStatistics() {
    fetch('{{ route('library.loans.statistics') }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const stats = data.statistics;
            document.getElementById('totalLoans').textContent = stats.total_loans;
            document.getElementById('activeLoans').textContent = stats.active_loans;
            document.getElementById('overdueLoans').textContent = stats.overdue_loans;
            document.getElementById('returnedLoans').textContent = stats.returned_loans;
            document.getElementById('dueTodayLoans').textContent = stats.due_today_count;
            document.getElementById('overdueRate').textContent = stats.overdue_rate + '%';
        }
    })
    .catch(error => {
        console.error('Failed to load statistics:', error);
    });
}

function updateOverdueLoans() {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
    btn.disabled = true;
    
    fetch('{{ route('library.loans.update-overdue') }}', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            // Reload the page to show updated status
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('Error', data.message || 'Failed to update overdue loans', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to update overdue loans', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
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
                        <p><strong>Return Date:</strong> ${loan.return_date}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${loan.status_color}">${loan.status}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Additional Information</h6>
                        <p><strong>Loan Duration:</strong> ${loan.loan_duration} days</p>
                        <p><strong>Days Overdue:</strong> ${loan.days_overdue}</p>
                        <p><strong>Fine Amount:</strong> $${loan.fine_amount}</p>
                        <p><strong>Issued By:</strong> ${loan.issued_by || 'N/A'}</p>
                        <p><strong>Returned By:</strong> ${loan.returned_by || 'N/A'}</p>
                    </div>
                </div>
                ${loan.notes ? `<hr><p><strong>Notes:</strong> ${loan.notes}</p>` : ''}
            `;
            
            document.getElementById('loanDetailsContent').innerHTML = content;
            
            // Show/hide action buttons based on loan status
            const returnBtn = document.getElementById('modalReturnBtn');
            const extendBtn = document.getElementById('modalExtendBtn');
            
            returnBtn.style.display = loan.is_active ? 'inline-block' : 'none';
            extendBtn.style.display = (loan.is_active && !loan.is_overdue) ? 'inline-block' : 'none';
            
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
            // Reload the page to show updated status
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('Error', data.message || 'Failed to return book', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to return book', 'error');
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

function extendLoan(loanId) {
    currentLoanId = loanId;
    
    // Get current due date from the row
    const row = document.querySelector(`[data-loan-id="${loanId}"]`);
    const dueDateCell = row.querySelector('td:nth-child(4)');
    const dueDateText = dueDateCell.textContent.trim();
    
    // Parse the current due date
    const currentDate = new Date();
    const currentYear = currentDate.getFullYear();
    
    // Extract date from text (e.g., "Dec 15, 2024")
    const dateMatch = dueDateText.match(/(\w{3})\s+(\d{1,2}),\s*(\d{4})/);
    if (dateMatch) {
        const month = new Date(dateMatch[1] + ' 1, 2000').getMonth();
        const day = parseInt(dateMatch[2]);
        const year = parseInt(dateMatch[3]);
        
        const currentDueDate = new Date(year, month, day);
        
        // Set minimum date to current due date + 1 day
        const minDate = new Date(currentDueDate);
        minDate.setDate(minDate.getDate() + 1);
        
        // Set default date to current due date + 7 days
        const defaultDate = new Date(currentDueDate);
        defaultDate.setDate(defaultDate.getDate() + 7);
        
        document.getElementById('new_due_date').min = minDate.toISOString().split('T')[0];
        document.getElementById('new_due_date').value = defaultDate.toISOString().split('T')[0];
    }
    
    // Show extend modal
    const modal = new bootstrap.Modal(document.getElementById('extendLoanModal'));
    modal.show();
}

function extendLoanFromModal() {
    if (currentLoanId) {
        // Close details modal
        const detailsModal = bootstrap.Modal.getInstance(document.getElementById('loanDetailsModal'));
        if (detailsModal) {
            detailsModal.hide();
        }
        
        // Show extend modal
        extendLoan(currentLoanId);
    }
}

function submitExtendLoan() {
    const form = document.getElementById('extendLoanForm');
    const formData = new FormData(form);
    formData.append('loan_id', currentLoanId);
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Extending...';
    btn.disabled = true;
    
    fetch(`/library/loans/${currentLoanId}/extend`, {
        method: 'POST',
        body: JSON.stringify({
            new_due_date: formData.get('new_due_date'),
            notes: formData.get('notes')
        }),
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
            // Close modal and reload page
            const modal = bootstrap.Modal.getInstance(document.getElementById('extendLoanModal'));
            modal.hide();
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('Error', data.message || 'Failed to extend loan', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to extend loan', 'error');
    })
    .finally(() => {
        btn.innerHTML = originalText;
        btn.disabled = false;
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
</script>
@endpush
