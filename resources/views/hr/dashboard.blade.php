@extends('layouts.app')

@section('title', 'HR Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">HR Dashboard</h1>
            <p class="text-muted mb-0">Employee management and payroll overview</p>
        </div>
        <div class="text-end">
            <button class="btn btn-outline-warning" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <!-- Total Employees -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Employees</h6>
                            <h4 class="mb-0">{{ $dashboardData['kpi_cards']['total_employees'] }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Present Today -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Present Today</h6>
                            <h4 class="mb-0">{{ $dashboardData['kpi_cards']['present_today'] }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- On Leave Today -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">On Leave Today</h6>
                            <h4 class="mb-0">{{ $dashboardData['kpi_cards']['on_leave_today'] }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-calendar-times fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payroll -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Pending Payroll</h6>
                            <h4 class="mb-0">{{ $dashboardData['kpi_cards']['pending_payroll'] }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-money-check-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Late Teachers Alert -->
    @if($dashboardData['kpi_cards']['late_teachers'] > 0)
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Attention:</strong> {{ $dashboardData['kpi_cards']['late_teachers'] }} teachers are late today.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Charts and Tables Row -->
    <div class="row mb-4">
        <!-- Monthly Attendance Trend Chart -->
        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Monthly Attendance Trend
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="attendanceTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Payroll Alert -->
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header bg-{{ $dashboardData['payroll_alert']['status'] === 'completed' ? 'success' : 'warning' }} text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>Payroll Status
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-{{ $dashboardData['payroll_alert']['status'] === 'completed' ? 'check-circle text-success' : 'exclamation-triangle text-warning' }} fa-3x"></i>
                    </div>
                    <h6 class="mb-2">{{ $dashboardData['payroll_alert']['message'] }}</h6>
                    @if($dashboardData['payroll_alert']['status'] !== 'completed')
                        <p class="text-muted mb-3">
                            {{ $dashboardData['payroll_alert']['days_until_processing'] }} days until processing deadline
                        </p>
                        <a href="{{ route('hr.payroll.index') }}" class="btn btn-{{ $dashboardData['payroll_alert']['status'] === 'completed' ? 'success' : 'warning' }}">
                            <i class="fas fa-cog me-2"></i>{{ $dashboardData['payroll_alert']['status'] === 'completed' ? 'View Payroll' : 'Process Payroll' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Leave Requests -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>Recent Leave Requests
                        </h6>
                        <a href="{{ route('hr.leaves.index') }}" class="btn btn-sm btn-light">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Role</th>
                                    <th>Leave Type</th>
                                    <th>Duration</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dashboardData['recent_leave_requests'] as $leaveRequest)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $leaveRequest['employee_name'] }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $leaveRequest['employee_role'] }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $leaveRequest['leave_type'] }}</span>
                                        </td>
                                        <td>
                                            <div>{{ $leaveRequest['start_date'] }}</div>
                                            <small class="text-muted">to {{ $leaveRequest['end_date'] }}</small>
                                        </td>
                                        <td>
                                            <div class="text-center">{{ $leaveRequest['days'] }}</div>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ Str::limit($leaveRequest['reason'], 30) }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-success" onclick="approveLeave({{ $leaveRequest['id'] }})">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-danger" onclick="rejectLeave({{ $leaveRequest['id'] }})">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="fas fa-calendar-check fa-2x text-muted mb-2"></i>
                                            <div class="text-muted">No pending leave requests</div>
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
                            <a href="{{ route('hr.employees.index') }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-users me-2"></i>Employee List
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('hr.leaves.index') }}" class="btn btn-outline-info w-100">
                                <i class="fas fa-calendar-alt me-2"></i>Leave Management
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('hr.payroll.index') }}" class="btn btn-outline-success w-100">
                                <i class="fas fa-money-bill-wave me-2"></i>Payroll
                            </a>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <a href="{{ route('hr.reports.index') }}" class="btn btn-outline-warning w-100">
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

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.border-left-info {
    border-left: 4px solid #0dcaf0 !important;
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
    const ctx = document.getElementById('attendanceTrendChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json(array_column($dashboardData['attendance_trend'], 'month')),
            datasets: [{
                label: 'Teacher Attendance',
                data: @json(array_column($dashboardData['attendance_trend'], 'teacher_attendance')),
                backgroundColor: '#0d6efd'
            }, {
                label: 'Staff Attendance',
                data: @json(array_column($dashboardData['attendance_trend'], 'staff_attendance')),
                backgroundColor: '#198754'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + '%';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
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
    
    fetch('/hr/dashboard/refresh', {
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

function approveLeave(leaveId) {
    if (!confirm('Are you sure you want to approve this leave request?')) {
        return;
    }
    
    fetch(`/hr/leaves/${leaveId}/approve`, {
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
            showToast('Error', data.message || 'Failed to approve leave', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to approve leave', 'error');
    });
}

function rejectLeave(leaveId) {
    const reason = prompt('Please enter rejection reason:');
    if (!reason) {
        return;
    }
    
    fetch(`/hr/leaves/${leaveId}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            reason: reason
        })
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
            showToast('Error', data.message || 'Failed to reject leave', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to reject leave', 'error');
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
