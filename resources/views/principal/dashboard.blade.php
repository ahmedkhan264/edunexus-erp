@extends('layouts.app')

@section('title', 'Executive Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Executive Dashboard</h1>
            <p class="text-muted mb-0">School performance overview and key metrics</p>
        </div>
        <div class="text-end">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt me-2"></i>Refresh Data
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <!-- Total Students -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Students</h6>
                            <h4 class="mb-0">{{ number_format($dashboardData['kpi_cards']['total_students']) }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-user-graduate fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Teachers -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Teachers</h6>
                            <h4 class="mb-0">{{ number_format($dashboardData['kpi_cards']['total_teachers']) }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-chalkboard-teacher fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Classes -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Classes</h6>
                            <h4 class="mb-0">{{ number_format($dashboardData['kpi_cards']['total_classes']) }}</h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-school fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Tasks -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Pending Tasks</h6>
                            <h4 class="mb-0">{{ number_format($dashboardData['kpi_cards']['pending_tasks']) }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance and Fee Cards -->
    <div class="row mb-4">
        <!-- Today's Student Attendance -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Student Attendance</h6>
                            <h4 class="mb-0">{{ number_format($dashboardData['kpi_cards']['today_student_attendance'], 1) }}%</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Today</small>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ $dashboardData['kpi_cards']['today_student_attendance'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Teacher Attendance -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Teacher Attendance</h6>
                            <h4 class="mb-0">{{ number_format($dashboardData['kpi_cards']['today_teacher_attendance'], 1) }}%</h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-chalkboard fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Today</small>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-info" style="width: {{ $dashboardData['kpi_cards']['today_teacher_attendance'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Fee Collection -->
        <div class="col-lg-6 mb-3">
            <div class="card h-100 border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Monthly Fee Collection</h6>
                            <h4 class="mb-0">Rs. {{ number_format($dashboardData['kpi_cards']['monthly_fee_collection']['collected']) }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-money-bill-wave fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">Target: Rs. {{ number_format($dashboardData['kpi_cards']['monthly_fee_collection']['target']) }} ({{ number_format($dashboardData['kpi_cards']['monthly_fee_collection']['percentage'], 1) }}%)</small>
                        <div class="progress mt-1" style="height: 4px;">
                            <div class="progress-bar bg-warning" style="width: {{ $dashboardData['kpi_cards']['monthly_fee_collection']['percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Student Attendance Trend -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Student Attendance Trend (Last 7 Days)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="studentAttendanceChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Teacher Attendance Trend -->
        <div class="col-lg-6 mb-3">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Teacher Attendance Trend (Last 7 Days)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="teacherAttendanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Collection and Defaulters -->
    <div class="row mb-4">
        <!-- Fee Collection Trend -->
        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Fee Collection vs Target (Last 6 Months)
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="feeCollectionChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Defaulters -->
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Top 5 Defaulters
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($dashboardData['charts']['top_defaulters']))
                        <div class="defaulters-list">
                            @foreach($dashboardData['charts']['top_defaulters'] as $defaulter)
                                <div class="defaulter-item d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <div>
                                        <div class="fw-medium">{{ $defaulter['name'] }}</div>
                                        <small class="text-muted">Roll: {{ $defaulter['roll_number'] }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-medium text-danger">Rs. {{ number_format($defaulter['outstanding']) }}</div>
                                        <small class="text-muted">{{ $defaulter['days_overdue'] }} days</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <div class="text-muted">No defaulters found</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <!-- Attendance Correction Requests -->
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-user-clock me-2"></i>Attendance Corrections
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($dashboardData['recent_activities']['attendance_correction_requests']))
                        <div class="activity-list">
                            @foreach($dashboardData['recent_activities']['attendance_correction_requests'] as $request)
                                <div class="activity-item mb-2 pb-2 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-medium">{{ $request['student_name'] }}</div>
                                            <small class="text-muted">{{ $request['date'] }} - {{ $request['reason'] }}</small>
                                        </div>
                                        <span class="badge bg-warning">Pending</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <div class="text-muted">No pending requests</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Fee Challans -->
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-file-invoice-dollar me-2"></i>Pending Challans
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($dashboardData['recent_activities']['pending_fee_challans']))
                        <div class="activity-list">
                            @foreach($dashboardData['recent_activities']['pending_fee_challans'] as $challan)
                                <div class="activity-item mb-2 pb-2 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-medium">{{ $challan['student_name'] }}</div>
                                            <small class="text-muted">{{ $challan['class'] }} - Due: {{ $challan['due_date'] }}</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-medium">Rs. {{ number_format($challan['amount']) }}</div>
                                            <small class="text-muted">{{ $challan['days_overdue'] }} days</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <div class="text-muted">No pending challans</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Completed Tasks -->
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-check-double me-2"></i>Completed Tasks
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($dashboardData['recent_activities']['completed_tasks']))
                        <div class="activity-list">
                            @foreach($dashboardData['recent_activities']['completed_tasks'] as $task)
                                <div class="activity-item mb-2 pb-2 border-bottom">
                                    <div>
                                        <div class="fw-medium">{{ $task['title'] }}</div>
                                        <small class="text-muted">{{ $task['assignee'] }} - {{ $task['completed_at'] }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-tasks fa-2x text-muted mb-2"></i>
                            <div class="text-muted">No completed tasks</div>
                        </div>
                    @endif
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

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}

.stat-box {
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: transform 0.2s ease;
}

.stat-box:hover {
    transform: translateY(-2px);
}

.activity-list .activity-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.defaulters-list .defaulter-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .h4 {
        font-size: 1.25rem;
    }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
});

function initializeCharts() {
    // Student Attendance Trend Chart
    const studentAttendanceCtx = document.getElementById('studentAttendanceChart').getContext('2d');
    new Chart(studentAttendanceCtx, {
        type: 'line',
        data: {
            labels: @json(array_column($dashboardData['charts']['student_attendance_trend'], 'date')),
            datasets: [{
                label: 'Attendance %',
                data: @json(array_column($dashboardData['charts']['student_attendance_trend'], 'percentage')),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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

    // Teacher Attendance Trend Chart
    const teacherAttendanceCtx = document.getElementById('teacherAttendanceChart').getContext('2d');
    new Chart(teacherAttendanceCtx, {
        type: 'line',
        data: {
            labels: @json(array_column($dashboardData['charts']['teacher_attendance_trend'], 'date')),
            datasets: [{
                label: 'Attendance %',
                data: @json(array_column($dashboardData['charts']['teacher_attendance_trend'], 'percentage')),
                borderColor: '#0dcaf0',
                backgroundColor: 'rgba(13, 202, 240, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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

    // Fee Collection Trend Chart
    const feeCollectionCtx = document.getElementById('feeCollectionChart').getContext('2d');
    new Chart(feeCollectionCtx, {
        type: 'bar',
        data: {
            labels: @json(array_column($dashboardData['charts']['fee_collection_trend'], 'month')),
            datasets: [{
                label: 'Collected',
                data: @json(array_column($dashboardData['charts']['fee_collection_trend'], 'collected')),
                backgroundColor: '#198754'
            }, {
                label: 'Target',
                data: @json(array_column($dashboardData['charts']['fee_collection_trend'], 'target')),
                backgroundColor: '#ffc107'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rs. ' + value.toLocaleString();
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
    
    fetch('/principal/dashboard/refresh', {
        method: 'GET',
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
