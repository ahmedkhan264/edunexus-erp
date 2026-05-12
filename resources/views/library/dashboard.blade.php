@extends('layouts.app')

@section('title', 'Library Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Library Dashboard</h1>
            <p class="text-muted mb-0">Book management and circulation overview</p>
        </div>
        <div class="text-end">
            <button class="btn btn-outline-warning" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <!-- Total Books -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Books</h6>
                            <h4 class="mb-0">{{ $dashboardData['kpi_cards']['total_books'] }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-book fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Books Issued -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Books Issued</h6>
                            <h4 class="mb-0">{{ $dashboardData['kpi_cards']['books_issued'] }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-hand-holding-book fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Books -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Overdue Books</h6>
                            <h4 class="mb-0">{{ $dashboardData['kpi_cards']['overdue_books'] }}</h4>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fines Collected -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Fines Collected</h6>
                            <h4 class="mb-0">Rs. {{ number_format($dashboardData['kpi_cards']['fines_collected'], 0) }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Books Alert -->
    @if($dashboardData['kpi_cards']['overdue_books'] > 0)
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Attention:</strong> {{ $dashboardData['kpi_cards']['overdue_books'] }} books are overdue. Please follow up with borrowers.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Charts and Tables Row -->
    <div class="row mb-4">
        <!-- Monthly Statistics Chart -->
        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Monthly Library Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyStatsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Popular Books -->
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-fire me-2"></i>Popular Books
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($dashboardData['popular_books'] as $book)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <div class="fw-medium">{{ Str::limit($book['title'], 30) }}</div>
                                <small class="text-muted">{{ $book['author'] }}</small>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-primary">{{ $book['times_borrowed'] }}</div>
                                <div class="text-muted small">{{ $book['available_copies'] }} left</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-book fa-2x text-muted mb-2"></i>
                            <div class="text-muted">No popular books data available</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Loan Activity -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Recent Loan Activity
                        </h6>
                        <a href="{{ route('library.loans.index') }}" class="btn btn-sm btn-light">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>Borrower</th>
                                    <th>Role</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dashboardData['recent_loans'] as $loan)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ Str::limit($loan['book_title'], 25) }}</div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ Str::limit($loan['book_author'], 20) }}</small>
                                        </td>
                                        <td>
                                            <div>{{ $loan['borrower_name'] }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $loan['borrower_role'] }}</span>
                                        </td>
                                        <td>
                                            <small>{{ $loan['issue_date'] }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $loan['due_date'] }}</small>
                                            @if($loan['is_overdue'])
                                                <div class="text-danger small">Overdue</div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $loan['status_color'] }}">{{ $loan['status'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-hand-holding-book fa-2x text-muted mb-2"></i>
                                            <div class="text-muted">No recent loan activity</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    @if(!empty($dashboardData['low_stock_books']))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alert
                            </h6>
                            <span class="badge bg-dark">{{ count($dashboardData['low_stock_books']) }} books</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>ISBN</th>
                                        <th>Total Copies</th>
                                        <th>Available</th>
                                        <th>Issued</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashboardData['low_stock_books'] as $book)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">{{ Str::limit($book['title'], 30) }}</div>
                                            </td>
                                            <td>
                                                <small>{{ Str::limit($book['author'], 20) }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $book['isbn'] }}</small>
                                            </td>
                                            <td>{{ $book['total_copies'] }}</td>
                                            <td>
                                                <span class="badge bg-{{ $book['available_copies'] === 0 ? 'danger' : 'warning' }}">
                                                    {{ $book['available_copies'] }}
                                                </span>
                                            </td>
                                            <td>{{ $book['issued_copies'] }}</td>
                                            <td>
                                                <span class="badge bg-{{ $book['status_color'] }}">{{ $book['status'] }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('library.books.edit', $book['id']) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Overdue Books -->
    @if(!empty($dashboardData['overdue_books']))
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-circle me-2"></i>Overdue Books
                            </h6>
                            <span class="badge bg-light text-dark">{{ count($dashboardData['overdue_books']) }} books</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Book Title</th>
                                        <th>Borrower</th>
                                        <th>Role</th>
                                        <th>Due Date</th>
                                        <th>Days Overdue</th>
                                        <th>Fine Amount</th>
                                        <th>Fine Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dashboardData['overdue_books'] as $overdue)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">{{ Str::limit($overdue['book_title'], 30) }}</div>
                                            </td>
                                            <td>{{ $overdue['borrower_name'] }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $overdue['borrower_role'] }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $overdue['due_date'] }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger">{{ $overdue['days_overdue'] }} days</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">Rs. {{ number_format($overdue['fine_amount'], 0) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $overdue['fine_color'] }}">{{ $overdue['fine_status'] }}</span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="sendReminder({{ $overdue['id'] }})">
                                                    <i class="fas fa-bell"></i> Remind
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-link me-2"></i>Quick Links
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('library.books.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-book me-2"></i>Book Catalog
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('library.loans.issue-return') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-exchange-alt me-2"></i>Issue/Return
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('library.books.create') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-plus me-2"></i>Add Book
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('library.reports.index') }}" class="btn btn-outline-warning w-100">
                                <i class="fas fa-chart-pie me-2"></i>Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #0d6efd !important;
}

.border-left-success {
    border-left: 4px solid #198754 !important;
}

.border-left-info {
    border-left: 4px solid #0dcaf0 !important;
}

.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
});

function initializeChart() {
    const ctx = document.getElementById('monthlyStatsChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json(array_column($dashboardData['monthly_stats'], 'month')),
            datasets: [{
                label: 'Books Issued',
                data: @json(array_column($dashboardData['monthly_stats'], 'books_issued')),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4
            }, {
                label: 'Books Returned',
                data: @json(array_column($dashboardData['monthly_stats'], 'books_returned')),
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4
            }, {
                label: 'Fines Collected',
                data: @json(array_column($dashboardData['monthly_stats'], 'fines_collected')),
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.dataset.label === 'Fines Collected') {
                                    label += 'Rs. ' + context.parsed.y;
                                } else {
                                    label += context.parsed.y;
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Books'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Fines (Rs.)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
}

function refreshDashboard() {
    const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
    const originalText = refreshBtn.innerHTML;
    
    // Show loading state
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    refreshBtn.disabled = true;
    
    fetch('/library/dashboard/refresh', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            // Reload the page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('Error', 'Failed to refresh dashboard', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to refresh dashboard', 'error');
    })
    .finally(() => {
        // Restore button state
        refreshBtn.innerHTML = originalText;
        refreshBtn.disabled = false;
    });
}

function sendReminder(loanId) {
    if (!confirm('Are you sure you want to send a reminder for this overdue book?')) {
        return;
    }
    
    fetch(`/library/loans/${loanId}/remind`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
        } else {
            showToast('Error', data.message || 'Failed to send reminder', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to send reminder', 'error');
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
