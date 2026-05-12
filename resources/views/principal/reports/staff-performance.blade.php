@extends('layouts.app')

@section('title', 'Staff Performance Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Staff Performance Report</h1>
            <p class="text-muted mb-0">Teacher performance analysis and metrics tracking</p>
        </div>
        <div class="text-end">
            <button class="btn btn-outline-success me-2" onclick="exportPdf()">
                <i class="fas fa-file-pdf me-2"></i>Export PDF
            </button>
            <button class="btn btn-outline-primary me-2" onclick="exportExcel()">
                <i class="fas fa-file-excel me-2"></i>Export Excel
            </button>
            <button class="btn btn-outline-warning" onclick="refreshReport()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('principal.reports.staff-performance') }}">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="month" class="form-label">Month</label>
                        <select class="form-select" id="month" name="month">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ Carbon::createFromDate(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="year" class="form-label">Year</label>
                        <select class="form-select" id="year" name="year">
                            @for($y = 2020; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="department" class="form-label">Department (Optional)</label>
                        <select class="form-select" id="department" name="department">
                            <option value="">All Departments</option>
                            <option value="Teaching" {{ $department === 'Teaching' ? 'selected' : '' }}>Teaching</option>
                            <option value="Science" {{ $department === 'Science' ? 'selected' : '' }}>Science</option>
                            <option value="Mathematics" {{ $department === 'Mathematics' ? 'selected' : '' }}>Mathematics</option>
                            <option value="English" {{ $department === 'English' ? 'selected' : '' }}>English</option>
                            <option value="Computer Science" {{ $department === 'Computer Science' ? 'selected' : '' }}>Computer Science</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <!-- Average Teacher Attendance -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Avg Teacher Attendance</h6>
                            <h4 class="mb-0">{{ number_format($reportData['kpi_cards']['avg_teacher_attendance'], 1) }}%</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ $reportData['kpi_cards']['avg_teacher_attendance'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avg Tasks Completed -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Avg Tasks Completed</h6>
                            <h4 class="mb-0">{{ number_format($reportData['kpi_cards']['avg_tasks_completed'], 1) }}%</h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ $reportData['kpi_cards']['avg_tasks_completed'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Avg Late Days -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Avg Late Days</h6>
                            <h4 class="mb-0">{{ number_format($reportData['kpi_cards']['avg_late_days'], 1) }}</h4>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Grading Count -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Pending Grading</h6>
                            <h4 class="mb-0">{{ $reportData['kpi_cards']['pending_grading_count'] }}</h4>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-clipboard-check fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teacher Performance Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i>Teacher Performance Details
                </h6>
                <div class="badge bg-white text-primary">
                    {{ count($reportData['teacher_performance']) }} Teachers
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Teacher Name</th>
                            <th>Department</th>
                            <th>Attendance %</th>
                            <th>Late Count</th>
                            <th>Classes Taken</th>
                            <th>Tasks Completed</th>
                            <th>Pending Grading</th>
                            <th>Performance Score</th>
                            <th>Performance Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData['teacher_performance'] as $teacher)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $teacher['name'] }}</div>
                                    <small class="text-muted">{{ $teacher['email'] }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $teacher['department'] }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ number_format($teacher['attendance_percentage'], 1) }}%</span>
                                        <div class="progress" style="width: 60px; height: 6px;">
                                            <div class="progress-bar bg-{{ $teacher['attendance_percentage'] >= 80 ? 'success' : ($teacher['attendance_percentage'] >= 60 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $teacher['attendance_percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <span class="badge bg-{{ $teacher['late_count'] > 0 ? 'warning' : 'success' }}">
                                            {{ $teacher['late_count'] }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">{{ $teacher['classes_taken'] }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ $teacher['tasks_completed'] }} / {{ $teacher['tasks_total'] }}</span>
                                        <div class="progress" style="width: 60px; height: 6px;">
                                            <div class="progress-bar bg-{{ $teacher['tasks_completion_percentage'] >= 80 ? 'success' : ($teacher['tasks_completion_percentage'] >= 60 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $teacher['tasks_completion_percentage'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <span class="badge bg-{{ $teacher['pending_grading'] > 0 ? 'danger' : 'success' }}">
                                            {{ $teacher['pending_grading'] }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <div class="fw-medium">{{ number_format($teacher['performance_score'], 1) }}</div>
                                        <small class="text-muted">/ 100</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $teacher['performance_color'] }}">
                                        {{ $teacher['performance_level'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                    <div class="text-muted">No teacher performance data found</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Performance Summary -->
    @if(!empty($reportData['teacher_performance']))
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Performance Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            @php
                                $performanceLevels = [
                                    'Excellent' => 0,
                                    'Good' => 0,
                                    'Satisfactory' => 0,
                                    'Needs Improvement' => 0
                                ];
                                
                                foreach ($reportData['teacher_performance'] as $teacher) {
                                    $performanceLevels[$teacher['performance_level']]++;
                                }
                            @endphp
                            
                            @foreach($performanceLevels as $level => $count)
                                <div class="col-md-3 col-6 mb-3">
                                    <div class="stat-box">
                                        <div class="h4 mb-0 text-{{ $level === 'Excellent' ? 'success' : ($level === 'Good' ? 'info' : ($level === 'Satisfactory' ? 'warning' : 'danger')) }}">
                                            {{ $count }}
                                        </div>
                                        <small class="text-muted">{{ $level }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
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

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.progress {
    background-color: #e9ecef;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .progress {
        width: 40px !important;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializePerformanceReport();
});

function initializePerformanceReport() {
    // Auto-refresh every 5 minutes
    setInterval(refreshReport, 300000);
}

function exportPdf() {
    const currentUrl = new URL(window.location);
    const params = currentUrl.searchParams;
    
    fetch(`/principal/reports/staff-performance/pdf?${params.toString()}`, {
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
            // In a real implementation, you would trigger the PDF download here
            setTimeout(() => {
                showToast('Info', 'PDF download would start here', 'info');
            }, 1000);
        } else {
            showToast('Error', data.message || 'Failed to export PDF', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to export PDF', 'error');
    });
}

function exportExcel() {
    const currentUrl = new URL(window.location);
    const params = currentUrl.searchParams;
    
    fetch(`/principal/reports/staff-performance/excel?${params.toString()}`, {
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
            // In a real implementation, you would trigger the Excel download here
            setTimeout(() => {
                showToast('Info', 'Excel download would start here', 'info');
            }, 1000);
        } else {
            showToast('Error', data.message || 'Failed to export Excel', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to export Excel', 'error');
    });
}

function refreshReport() {
    const refreshBtn = document.querySelector('button[onclick="refreshReport()"]');
    const originalText = refreshBtn.innerHTML;
    
    // Show loading state
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    refreshBtn.disabled = true;
    
    fetch('/principal/reports/staff-performance/refresh', {
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
            showToast('Error', 'Failed to refresh report', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to refresh report', 'error');
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
