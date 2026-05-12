@extends('layouts.app')

@section('title', 'Reports Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Reports Dashboard</h1>
            <p class="text-muted mb-0">Comprehensive system analytics and reports</p>
        </div>
        <div class="text-end">
            <div class="btn-group" role="group">
                <button class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-download me-2"></i>Export Reports
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportReport('summary')">
                        <i class="fas fa-file-alt me-2"></i>Summary Report
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('students')">
                        <i class="fas fa-graduation-cap me-2"></i>Student Report
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('attendance')">
                        <i class="fas fa-calendar-check me-2"></i>Attendance Report
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('hr')">
                        <i class="fas fa-users me-2"></i>HR Report
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportReport('library')">
                        <i class="fas fa-book me-2"></i>Library Report
                    </a></li>
                </ul>
            </div>
            <button class="btn btn-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- System Overview Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Students</h6>
                            <h2 class="mb-0">{{ $dashboardData['student_stats']['total_students'] }}</h2>
                            <small class="text-white-50">
                                <i class="fas fa-arrow-up me-1"></i>
                                {{ $dashboardData['student_stats']['new_students_this_month'] }} new this month
                            </small>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-graduation-cap fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Attendance Rate</h6>
                            <h2 class="mb-0">{{ $dashboardData['attendance_stats']['attendance_rate_this_month'] }}%</h2>
                            <small class="text-white-50">
                                <i class="fas fa-calendar-check me-1"></i>
                                {{ $dashboardData['attendance_stats']['present_today'] }} present today
                            </small>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-chart-line fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Employees</h6>
                            <h2 class="mb-0">{{ $dashboardData['hr_stats']['total_employees'] }}</h2>
                            <small class="text-dark">
                                <i class="fas fa-users me-1"></i>
                                {{ $dashboardData['hr_stats']['active_employees'] }} active
                            </small>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-user-tie fa-2x text-dark opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Library Books</h6>
                            <h2 class="mb-0">{{ $dashboardData['library_stats']['total_books'] }}</h2>
                            <small class="text-white-50">
                                <i class="fas fa-book me-1"></i>
                                {{ $dashboardData['library_stats']['available_books'] }} available
                            </small>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-book-open fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Student Enrollment Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Student Enrollment Trend
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="studentEnrollmentChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Attendance Trends Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Attendance Trends
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="attendanceTrendsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- HR Metrics Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-area me-2"></i>HR Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="hrMetricsChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Library Usage Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Library Usage
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="libraryUsageChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics -->
    <div class="row mb-4">
        <!-- Student Statistics -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Student Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <h5 class="text-primary">{{ $dashboardData['student_stats']['total_students'] }}</h5>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-6 mb-2">
                            <h5 class="text-success">{{ $dashboardData['student_stats']['active_students'] }}</h5>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                    <hr>
                    <h6 class="text-muted">By Grade Level</h6>
                    @foreach($dashboardData['student_stats']['students_by_grade'] as $grade => $count)
                        <div class="d-flex justify-content-between mb-1">
                            <span>Grade {{ $grade }}</span>
                            <span class="badge bg-secondary">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Attendance Statistics -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>Attendance Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4 mb-2">
                            <h5 class="text-success">{{ $dashboardData['attendance_stats']['present_today'] }}</h5>
                            <small class="text-muted">Present</small>
                        </div>
                        <div class="col-4 mb-2">
                            <h5 class="text-danger">{{ $dashboardData['attendance_stats']['absent_today'] }}</h5>
                            <small class="text-muted">Absent</small>
                        </div>
                        <div class="col-4 mb-2">
                            <h5 class="text-warning">{{ $dashboardData['attendance_stats']['late_today'] }}</h5>
                            <small class="text-muted">Late</small>
                        </div>
                    </div>
                    <hr>
                    <h6 class="text-muted">Monthly Trend</h6>
                    @foreach(array_slice($dashboardData['attendance_stats']['monthly_attendance_trend'], 0, 3) as $trend)
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $trend['month'] }}</span>
                            <span class="badge bg-{{ $trend['rate'] >= 90 ? 'success' : ($trend['rate'] >= 80 ? 'warning' : 'danger') }}">
                                {{ $trend['rate'] }}%
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- HR Statistics -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>HR Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <h5 class="text-warning">{{ $dashboardData['hr_stats']['total_employees'] }}</h5>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-6 mb-2">
                            <h5 class="text-success">{{ $dashboardData['hr_stats']['active_employees'] }}</h5>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Leave Requests (This Month)</span>
                        <span class="badge bg-info">{{ $dashboardData['hr_stats']['leave_requests_this_month'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Pending Leaves</span>
                        <span class="badge bg-warning">{{ $dashboardData['hr_stats']['pending_leave_requests'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Payroll Processed</span>
                        <span class="badge bg-success">{{ $dashboardData['hr_stats']['payroll_processed_this_month'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Library Statistics -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-book me-2"></i>Library Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <h5 class="text-info">{{ $dashboardData['library_stats']['total_books'] }}</h5>
                            <small class="text-muted">Total Books</small>
                        </div>
                        <div class="col-6 mb-2">
                            <h5 class="text-success">{{ $dashboardData['library_stats']['available_books'] }}</h5>
                            <small class="text-muted">Available</small>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Books Issued</span>
                        <span class="badge bg-primary">{{ $dashboardData['library_stats']['books_issued'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Overdue Books</span>
                        <span class="badge bg-danger">{{ $dashboardData['library_stats']['overdue_books'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Total Loans</span>
                        <span class="badge bg-secondary">{{ $dashboardData['library_stats']['total_loans'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <!-- Recent Students -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Recent Students
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($dashboardData['recent_activities']['recent_students'] as $student)
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                {{ substr($student->user->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <small class="d-block">{{ $student->user->name }}</small>
                                <small class="text-muted">{{ $student->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <small>No recent students</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Recent Attendance -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>Recent Attendance
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($dashboardData['recent_activities']['recent_attendance'] as $attendance)
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-sm bg-{{ $attendance->status == 'present' ? 'success' : ($attendance->status == 'absent' ? 'danger' : 'warning') }} text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                {{ substr($attendance->user->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <small class="d-block">{{ $attendance->user->name }}</small>
                                <small class="text-muted">{{ ucfirst($attendance->status) }} • {{ $attendance->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <small>No recent attendance</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Recent Leave Requests -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-times me-2"></i>Recent Leave Requests
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($dashboardData['recent_activities']['recent_leave_requests'] as $leave)
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-sm bg-{{ $leave->status == 'approved' ? 'success' : ($leave->status == 'rejected' ? 'danger' : 'warning') }} text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                {{ substr($leave->user->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <small class="d-block">{{ $leave->user->name }}</small>
                                <small class="text-muted">{{ ucfirst($leave->status) }} • {{ $leave->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <small>No recent leave requests</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        
        <!-- Recent Book Loans -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-book-reader me-2"></i>Recent Book Loans
                    </h6>
                </div>
                <div class="card-body">
                    @forelse($dashboardData['recent_activities']['recent_book_loans'] as $loan)
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar-sm bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                {{ substr($loan->user->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <small class="d-block">{{ $loan->user->name }}</small>
                                <small class="text-muted">{{ $loan->book->title }} • {{ $loan->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted">
                            <small>No recent book loans</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 12px;
    font-weight: bold;
}

.card-body {
    padding: 1rem;
}

.card-header {
    padding: 0.75rem 1rem;
    font-weight: 600;
}

.text-dark {
    color: #343a40 !important;
}

@media (max-width: 768px) {
    .card-body {
        padding: 0.75rem;
    }
    
    .card-header {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts
    initializeCharts();
});

function initializeCharts() {
    // Student Enrollment Chart
    const studentEnrollmentCtx = document.getElementById('studentEnrollmentChart').getContext('2d');
    const studentEnrollmentData = @json($dashboardData['charts_data']['student_enrollment']);
    
    new Chart(studentEnrollmentCtx, {
        type: 'line',
        data: {
            labels: studentEnrollmentData.map(item => item.month),
            datasets: [{
                label: 'New Students',
                data: studentEnrollmentData.map(item => item.count),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
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
                    beginAtZero: true
                }
            }
        }
    });
    
    // Attendance Trends Chart
    const attendanceTrendsCtx = document.getElementById('attendanceTrendsChart').getContext('2d');
    const attendanceTrendsData = @json($dashboardData['charts_data']['attendance_trends']);
    
    new Chart(attendanceTrendsCtx, {
        type: 'bar',
        data: {
            labels: attendanceTrendsData.map(item => item.month),
            datasets: [
                {
                    label: 'Present',
                    data: attendanceTrendsData.map(item => item.present),
                    backgroundColor: 'rgba(40, 167, 69, 0.8)'
                },
                {
                    label: 'Absent',
                    data: attendanceTrendsData.map(item => item.absent),
                    backgroundColor: 'rgba(220, 53, 69, 0.8)'
                },
                {
                    label: 'Late',
                    data: attendanceTrendsData.map(item => item.late),
                    backgroundColor: 'rgba(255, 193, 7, 0.8)'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true,
                    beginAtZero: true
                }
            }
        }
    });
    
    // HR Metrics Chart
    const hrMetricsCtx = document.getElementById('hrMetricsChart').getContext('2d');
    const hrMetricsData = @json($dashboardData['charts_data']['hr_metrics']);
    
    new Chart(hrMetricsCtx, {
        type: 'line',
        data: {
            labels: hrMetricsData.map(item => item.month),
            datasets: [
                {
                    label: 'Leave Requests',
                    data: hrMetricsData.map(item => item.leave_requests),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    yAxisID: 'y'
                },
                {
                    label: 'Payroll Amount',
                    data: hrMetricsData.map(item => item.payroll_amount),
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
    
    // Library Usage Chart
    const libraryUsageCtx = document.getElementById('libraryUsageChart').getContext('2d');
    const libraryUsageData = @json($dashboardData['charts_data']['library_usage']);
    
    new Chart(libraryUsageCtx, {
        type: 'doughnut',
        data: {
            labels: ['Issued', 'Returned', 'Overdue'],
            datasets: [{
                data: [
                    libraryUsageData.reduce((sum, item) => sum + item.issued, 0),
                    libraryUsageData.reduce((sum, item) => sum + item.returned, 0),
                    libraryUsageData.reduce((sum, item) => sum + item.overdue, 0)
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function refreshDashboard() {
    const refreshBtn = event.target;
    const originalText = refreshBtn.innerHTML;
    
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    refreshBtn.disabled = true;
    
    fetch('{{ route('reports.dashboard.refresh') }}', {
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
        refreshBtn.innerHTML = originalText;
        refreshBtn.disabled = false;
    });
}

function exportReport(type) {
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Exporting...';
    btn.disabled = true;
    
    fetch('{{ route('reports.dashboard.export') }}', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            type: type
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            
            // Download the data as JSON file
            const dataStr = JSON.stringify(data.data, null, 2);
            const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
            
            const exportFileDefaultName = `report_${type}_${new Date().toISOString().split('T')[0]}.json`;
            
            const linkElement = document.createElement('a');
            linkElement.setAttribute('href', dataUri);
            linkElement.setAttribute('download', exportFileDefaultName);
            linkElement.click();
        } else {
            showToast('Error', 'Failed to export report', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to export report', 'error');
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
