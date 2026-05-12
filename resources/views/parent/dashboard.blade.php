@extends('layouts.app')

@section('title', 'Parent Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Parent Dashboard</h1>
            <p class="text-muted mb-0">Monitor your child's progress and activities</p>
        </div>
        <div class="text-end">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Child Selector -->
    @if($children->count() > 1)
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label for="childSelector" class="form-label">Select Child:</label>
                    </div>
                    <div class="col-md-9">
                        <select class="form-select" id="childSelector" onchange="switchChild(this.value)">
                            @foreach($children as $child)
                                <option value="{{ $child->id }}" {{ $selectedChild && $selectedChild->id == $child->id ? 'selected' : '' }}>
                                    {{ $child->user->name }} - Grade {{ $child->schoolClass->grade_level }} ({{ $child->schoolClass->section }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($selectedChild)
        <!-- Dashboard Widgets -->
        <div class="row mb-4">
            <!-- Attendance Card -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100 border-left-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Today's Attendance</h6>
                                <h4 class="mb-0">
                                    <span class="badge bg-{{ $dashboardData['attendance']['today_status_color'] }}">
                                        {{ $dashboardData['attendance']['today_status_display'] }}
                                    </span>
                                </h4>
                            </div>
                            <div class="text-primary">
                                <i class="fas fa-calendar-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Monthly: {{ $dashboardData['attendance']['monthly_percentage'] }}%</small>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-primary" style="width: {{ $dashboardData['attendance']['monthly_percentage'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fee Card -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100 border-left-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Outstanding Fees</h6>
                                <h4 class="mb-0">Rs. {{ number_format($dashboardData['fees']['outstanding_amount'], 0) }}</h4>
                            </div>
                            <div class="text-warning">
                                <i class="fas fa-money-bill-wave fa-2x"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Next Due: {{ $dashboardData['fees']['next_due_date'] }}</small>
                            @if($dashboardData['fees']['next_due_amount'] > 0)
                                <div class="text-danger small">Rs. {{ number_format($dashboardData['fees']['next_due_amount'], 0) }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Latest Result Card -->
            <div class="col-lg-3 col-md-6 mb-3">
                @if($dashboardData['latest_result'])
                    <div class="card h-100 border-left-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Latest Result</h6>
                                    <h4 class="mb-0">
                                        <span class="badge bg-{{ $dashboardData['latest_result']['grade_color'] }}">
                                            {{ $dashboardData['latest_result']['grade'] }}
                                        </span>
                                    </h4>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">{{ $dashboardData['latest_result']['exam_title'] }}</small>
                                <div class="text-primary small">{{ number_format($dashboardData['latest_result']['percentage'], 1) }}%</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card h-100 border-left-secondary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Latest Result</h6>
                                    <h4 class="mb-0 text-muted">N/A</h4>
                                </div>
                                <div class="text-secondary">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted">No results available</small>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Performance Summary Card -->
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card h-100 border-left-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Performance</h6>
                                <h4 class="mb-0">Good</h4>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-trophy fa-2x"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">Overall Progress</small>
                            <div class="progress mt-1" style="height: 4px;">
                                <div class="progress-bar bg-info" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links and Notifications -->
        <div class="row">
            <!-- Quick Links -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-link me-2"></i>Quick Links
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ $dashboardData['quick_links']['attendance'] }}" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-alt me-2"></i>View Attendance
                            </a>
                            <a href="{{ $dashboardData['quick_links']['fees'] }}" class="btn btn-outline-warning">
                                <i class="fas fa-receipt me-2"></i>Fee Details
                            </a>
                            <a href="{{ $dashboardData['quick_links']['results'] }}" class="btn btn-outline-success">
                                <i class="fas fa-chart-bar me-2"></i>View Results
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-bell me-2"></i>Recent Notifications
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="notification-list">
                            @foreach($dashboardData['notifications'] as $notification)
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="flex-shrink-0">
                                        <div class="notification-icon bg-{{ $notification['color'] }} bg-opacity-10 rounded-circle p-2">
                                            <i class="{{ $notification['icon'] }} text-{{ $notification['color'] }}"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="fw-medium">{{ $notification['message'] }}</div>
                                        <small class="text-muted">{{ $notification['date'] }}</small>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <button class="btn btn-sm btn-outline-secondary" onclick="viewNotification('{{ $notification['type'] }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Child Information -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-user me-2"></i>{{ $selectedChild->user->name }}'s Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="text-muted small">Grade & Section</label>
                                    <div class="fw-medium">Grade {{ $selectedChild->schoolClass->grade_level }} - {{ $selectedChild->schoolClass->section }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="text-muted small">Roll Number</label>
                                    <div class="fw-medium">{{ $selectedChild->roll_number ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="text-muted small">Monthly Attendance</label>
                                    <div class="fw-medium">{{ $dashboardData['attendance']['monthly_present_days'] }} / {{ $dashboardData['attendance']['monthly_total_days'] }} days</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-item">
                                    <label class="text-muted small">Status</label>
                                    <div class="fw-medium">
                                        <span class="badge bg-success">Active</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- No Children State -->
        <div class="text-center py-5">
            <i class="fas fa-child fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Children Linked</h5>
            <p class="text-muted">You don't have any children linked to your account yet.</p>
            <p class="text-muted">Please contact the school administration to link your children.</p>
        </div>
    @endif
</div>

<style>
.border-left-primary {
    border-left: 4px solid #0d6efd !important;
}

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.border-left-success {
    border-left: 4px solid #198754 !important;
}

.border-left-info {
    border-left: 4px solid #0dcaf0 !important;
}

.border-left-secondary {
    border-left: 4px solid #6c757d !important;
}

.notification-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-list .d-flex:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.info-item {
    margin-bottom: 1rem;
}

.info-item:last-child {
    margin-bottom: 0;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .d-grid.gap-2 .btn {
        font-size: 0.875rem;
    }
    
    .notification-list .d-flex {
        flex-direction: column;
        text-align: center;
    }
    
    .notification-list .ms-3 {
        margin-left: 0 !important;
        margin-top: 0.5rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    // Auto-refresh dashboard every 5 minutes
    setInterval(refreshDashboard, 300000);
}

function switchChild(childId) {
    // Show loading state
    const dashboardContent = document.querySelector('.container-fluid');
    dashboardContent.style.opacity = '0.5';
    
    // Navigate to new child's dashboard
    window.location.href = `/parent/dashboard?child_id=${childId}`;
}

function refreshDashboard() {
    const childId = document.getElementById('childSelector')?.value;
    
    if (!childId) {
        return;
    }
    
    fetch(`/parent/dashboard/child-data`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ child_id: childId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateDashboardWidgets(data.data);
            showToast('Success', 'Dashboard refreshed', 'success');
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to refresh dashboard', 'error');
    });
}

function updateDashboardWidgets(data) {
    // Update attendance widget
    const attendanceStatus = document.querySelector('.border-left-primary .badge');
    const attendancePercentage = document.querySelector('.border-left-primary .progress-bar');
    const attendanceText = document.querySelector('.border-left-primary .text-muted');
    
    if (attendanceStatus) {
        attendanceStatus.className = `badge bg-${data.attendance.today_status_color}`;
        attendanceStatus.textContent = data.attendance.today_status_display;
    }
    
    if (attendancePercentage) {
        attendancePercentage.style.width = `${data.attendance.monthly_percentage}%`;
    }
    
    if (attendanceText) {
        attendanceText.textContent = `Monthly: ${data.attendance.monthly_percentage}%`;
    }
    
    // Update fee widget
    const feeAmount = document.querySelector('.border-left-warning h4');
    const feeDueDate = document.querySelector('.border-left-warning .text-muted');
    
    if (feeAmount) {
        feeAmount.textContent = `Rs. ${number_format(data.fees.outstanding_amount, 0)}`;
    }
    
    if (feeDueDate) {
        feeDueDate.textContent = `Next Due: ${data.fees.next_due_date}`;
    }
    
    // Update latest result widget
    if (data.latest_result) {
        const resultGrade = document.querySelector('.border-left-success .badge');
        const resultExam = document.querySelector('.border-left-success .text-muted');
        const resultPercentage = document.querySelector('.border-left-success .text-primary');
        
        if (resultGrade) {
            resultGrade.className = `badge bg-${data.latest_result.grade_color}`;
            resultGrade.textContent = data.latest_result.grade;
        }
        
        if (resultExam) {
            resultExam.textContent = data.latest_result.exam_title;
        }
        
        if (resultPercentage) {
            resultPercentage.textContent = `${number_format(data.latest_result.percentage, 1)}%`;
        }
    }
}

function viewNotification(type) {
    // Handle notification click based on type
    switch(type) {
        case 'fee_reminder':
            window.location.href = document.querySelector('.btn-outline-warning').href;
            break;
        case 'attendance_alert':
            window.location.href = document.querySelector('.btn-outline-primary').href;
            break;
        case 'new_assignment':
            window.location.href = '/student/assignments';
            break;
        case 'exam_result':
            window.location.href = document.querySelector('.btn-outline-success').href;
            break;
        case 'general_notice':
            showToast('Notice', 'This would open a detailed view of the notice', 'info');
            break;
        default:
            showToast('Info', 'Notification details would appear here', 'info');
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

// Helper function for number formatting (similar to PHP's number_format)
function number_format(number, decimals) {
    return parseFloat(number).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
</script>
@endpush
