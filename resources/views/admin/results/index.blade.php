@extends('layouts.app')

@section('title', 'Assignment Results Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Assignment Results Dashboard</h1>
            <p class="text-muted mb-0">Comprehensive assignment performance analytics</p>
        </div>
        <div class="text-end">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-success me-2" onclick="exportResults()">
                    <i class="fas fa-download me-2"></i>Export Results
                </button>
                <button class="btn btn-outline-primary me-2" onclick="refreshData()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
                <button class="btn btn-outline-info" onclick="printReport()">
                    <i class="fas fa-print me-2"></i>Print Report
                </button>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $statistics['total_assignments'] }}</h4>
                            <small>Total Assignments</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-clipboard-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $statistics['total_submissions'] }}</h4>
                            <small>Total Submissions</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ $statistics['graded_submissions'] }}</h4>
                            <small>Graded</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">{{ number_format($statistics['average_score'], 1) }}</h4>
                            <small>Average Score</small>
                        </div>
                        <div class="ms-3">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Analytics -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Grade Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <div class="grade-distribution">
                        @foreach($statistics['grade_distribution'] as $grade => $count)
                            @if($count > 0)
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-3" style="width: 60px;">
                                        <span class="badge bg-{{ $grade === 'Not Graded' ? 'secondary' : ($grade[0] === 'A' ? 'success' : ($grade[0] === 'B' ? 'info' : ($grade[0] === 'C' ? 'warning' : ($grade[0] === 'D' ? 'danger' : 'dark'))) }}">
                                            {{ $grade }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <small>{{ $count }} assignments</small>
                                            <small>{{ $statistics['total_submissions'] > 0 ? number_format(($count / $statistics['total_submissions']) * 100, 1) : 0 }}%</small>
                                        </div>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $grade === 'Not Graded' ? 'secondary' : ($grade[0] === 'A' ? 'success' : ($grade[0] === 'B' ? 'info' : ($grade[0] === 'C' ? 'warning' : ($grade[0] === 'D' ? 'danger' : 'dark'))) }}" 
                                                 style="width: {{ $statistics['total_submissions'] > 0 ? ($count / $statistics['total_submissions']) * 100 : 0 }}%">
                                                {{ $count }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-percentage me-2"></i>Performance Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="performance-metrics">
                        <div class="metric-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Grading Completion</span>
                                <strong>{{ $statistics['total_submissions'] > 0 ? number_format(($statistics['graded_submissions'] / $statistics['total_submissions']) * 100, 1) : 0 }}%</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $statistics['total_submissions'] > 0 ? ($statistics['graded_submissions'] / $statistics['total_submissions']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="metric-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Submission Rate</span>
                                <strong>{{ number_format(($statistics['total_submissions'] / ($statistics['total_assignments'] * 30)) * 100, 1) }}%</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: {{ number_format(($statistics['total_submissions'] / ($statistics['total_assignments'] * 30)) * 100, 1) }}%"></div>
                            </div>
                        </div>
                        <div class="metric-item mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Pending Grading</span>
                                <strong>{{ $statistics['pending_submissions'] }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: {{ $statistics['total_submissions'] > 0 ? ($statistics['pending_submissions'] / $statistics['total_submissions']) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <div class="metric-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Quality Score</span>
                                <strong>{{ $statistics['average_score'] >= 80 ? 'Excellent' : ($statistics['average_score'] >= 60 ? 'Good' : 'Needs Improvement') }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-{{ $statistics['average_score'] >= 80 ? 'success' : ($statistics['average_score'] >= 60 ? 'info' : 'warning') }}" style="width: {{ min($statistics['average_score'], 100) }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Breakdown -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Class Performance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="class-performance">
                        @foreach($statistics['class_performance'] as $grade => $performance)
                            <div class="performance-card mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Grade {{ $grade }}</h6>
                                    <span class="badge bg-info">{{ $performance['total_assignments'] }} assignments</span>
                                </div>
                                <div class="row text-center mb-2">
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-primary">{{ $performance['total_submissions'] }}</div>
                                        <small class="text-muted">Submissions</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-success">{{ $performance['average_score'] ? number_format($performance['average_score'], 1) : 'N/A' }}</div>
                                        <small class="text-muted">Avg Score</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-info">{{ number_format($performance['submission_rate'], 1) }}%</div>
                                        <small class="text-muted">Rate</small>
                                    </div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: {{ $performance['submission_rate'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-book me-2"></i>Subject Performance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="subject-performance">
                        @foreach($statistics['subject_performance'] as $subject => $performance)
                            <div class="performance-card mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">{{ $subject }}</h6>
                                    <span class="badge bg-primary">{{ $performance['total_assignments'] }} assignments</span>
                                </div>
                                <div class="row text-center mb-2">
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-primary">{{ $performance['total_submissions'] }}</div>
                                        <small class="text-muted">Submissions</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-success">{{ $performance['average_score'] ? number_format($performance['average_score'], 1) : 'N/A' }}</div>
                                        <small class="text-muted">Avg Score</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-info">{{ number_format($performance['submission_rate'], 1) }}%</div>
                                        <small class="text-muted">Rate</small>
                                    </div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $performance['submission_rate'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chalkboard-teacher me-2"></i>Teacher Performance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="teacher-performance">
                        @foreach($statistics['teacher_performance'] as $teacher => $performance)
                            <div class="performance-card mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">{{ $teacher }}</h6>
                                    <span class="badge bg-success">{{ $performance['total_assignments'] }} assignments</span>
                                </div>
                                <div class="row text-center mb-2">
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-primary">{{ $performance['total_submissions'] }}</div>
                                        <small class="text-muted">Submissions</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-success">{{ $performance['average_score'] ? number_format($performance['average_score'], 1) : 'N/A' }}</div>
                                        <small class="text-muted">Avg Score</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="h6 mb-0 text-info">{{ number_format($performance['grading_efficiency'], 1) }}%</div>
                                        <small class="text-muted">Efficiency</small>
                                    </div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $performance['grading_efficiency'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Monthly Trends
                    </h6>
                </div>
                <div class="card-body">
                    <div class="monthly-trends">
                        <div class="row">
                            @foreach($statistics['monthly_trends'] as $month => $trend)
                                <div class="col-md-2 col-sm-4 col-6 mb-3">
                                    <div class="trend-card border rounded p-3 text-center">
                                        <h6 class="text-primary mb-2">{{ $month }}</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="h6 mb-0 text-info">{{ $trend['assignments'] }}</div>
                                                <small class="text-muted">Assignments</small>
                                            </div>
                                            <div class="col-6">
                                                <div class="h6 mb-0 text-success">{{ $trend['graded'] }}</div>
                                                <small class="text-muted">Graded</small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">Avg: {{ $trend['average_score'] ? number_format($trend['average_score'], 1) : 'N/A' }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Results Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-table me-2"></i>Detailed Assignment Results
                </h6>
                <div>
                    <select class="form-select form-select-sm" id="filterSelect">
                        <option value="">All Assignments</option>
                        <option value="completed">Fully Graded</option>
                        <option value="partial">Partially Graded</option>
                        <option value="pending">Not Graded</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="resultsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Assignment</th>
                            <th>Teacher</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Due Date</th>
                            <th>Submissions</th>
                            <th>Graded</th>
                            <th>Avg Score</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                            @php
                                $totalSubmissions = $assignment->submissions->count();
                                $gradedSubmissions = $assignment->submissions->whereNotNull('graded_at')->count();
                                $pendingSubmissions = $totalSubmissions - $gradedSubmissions;
                                $avgScore = $assignment->submissions->whereNotNull('marks_obtained')->avg('marks_obtained');
                                $status = $pendingSubmissions > 0 ? ($gradedSubmissions > 0 ? 'partial' : 'pending') : 'completed';
                            @endphp
                            <tr class="assignment-row" 
                                data-status="{{ $status }}"
                                data-submissions="{{ $totalSubmissions }}"
                                data-graded="{{ $gradedSubmissions }}">
                                <td>
                                    <div class="fw-medium">{{ $assignment->title }}</div>
                                    <small class="text-muted">{{ Str::limit($assignment->description, 50) }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ $assignment->teacher->name }}&background=random" 
                                             class="rounded-circle me-2" width="24" height="24">
                                        <div>
                                            <div class="fw-medium">{{ $assignment->teacher->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        Grade {{ $assignment->schoolClass->grade_level }} - {{ $assignment->section }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $assignment->subject->name }}</span>
                                </td>
                                <td>
                                    <div>{{ $assignment->due_date->format('M j, Y') }}</div>
                                    <small class="text-muted">{{ $assignment->due_date->format('g:i A') }}</small>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <strong>{{ $totalSubmissions }}</strong>
                                        @if($totalSubmissions > 0)
                                            <div class="progress" style="height: 4px; margin-top: 4px;">
                                                <div class="progress-bar bg-info" style="width: {{ ($gradedSubmissions / $totalSubmissions) * 100 }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <strong>{{ $gradedSubmissions }}</strong>
                                        @if($totalSubmissions > 0)
                                            <div class="small text-muted">{{ number_format(($gradedSubmissions / $totalSubmissions) * 100, 0) }}%</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <strong>{{ $avgScore ? number_format($avgScore, 1) : 'N/A' }}</strong>
                                        @if($avgScore)
                                            <div class="small text-muted">/ {{ $assignment->total_marks }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($status === 'completed')
                                        <span class="badge bg-success">Fully Graded</span>
                                    @elseif($status === 'partial')
                                        <span class="badge bg-warning">Partially Graded</span>
                                    @else
                                        <span class="badge bg-secondary">Not Graded</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewDetails({{ $assignment->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-outline-info" onclick="viewAnalytics({{ $assignment->id }})">
                                            <i class="fas fa-chart-bar"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="exportAssignment({{ $assignment->id }})">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            @if($assignments->count() === 0)
                <div class="text-center py-5">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Assignment Results</h5>
                    <p class="text-muted">No assignments have been created yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Assignment Details Modal -->
<div class="modal fade" id="assignmentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Assignment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="assignmentDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.stat-box {
    border-radius: 0.5rem;
    transition: transform 0.2s ease;
}

.stat-box:hover {
    transform: translateY(-2px);
}

.grade-distribution .progress-bar {
    font-weight: 500;
}

.performance-card {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1rem;
    transition: transform 0.2s ease;
}

.performance-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.trend-card {
    transition: transform 0.2s ease;
}

.trend-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.assignment-row:hover {
    background-color: #f8f9fa;
}

.progress {
    border-radius: 0.25rem;
}

.metric-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.metric-item:last-child {
    border-bottom: none;
}

@media (max-width: 768px) {
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
});

function initializeFilters() {
    const filterSelect = document.getElementById('filterSelect');
    
    filterSelect.addEventListener('change', function() {
        filterAssignments();
    });
}

function filterAssignments() {
    const filterValue = document.getElementById('filterSelect').value;
    const rows = document.querySelectorAll('.assignment-row');
    
    rows.forEach(row => {
        const status = row.dataset.status;
        
        let show = true;
        
        if (filterValue === 'completed' && status !== 'completed') {
            show = false;
        } else if (filterValue === 'partial' && status !== 'partial') {
            show = false;
        } else if (filterValue === 'pending' && status !== 'pending') {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function viewDetails(assignmentId) {
    const modal = new bootstrap.Modal(document.getElementById('assignmentDetailsModal'));
    const content = document.getElementById('assignmentDetailsContent');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Fetch assignment details (you could implement AJAX here)
    setTimeout(() => {
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                <h6>Assignment Details</h6>
                <p class="text-muted">Assignment ID: ${assignmentId}</p>
                <p class="text-muted">Detailed view would be loaded here via AJAX.</p>
            </div>
        `;
    }, 500);
    
    modal.show();
}

function viewAnalytics(assignmentId) {
    const modal = new bootstrap.Modal(document.getElementById('assignmentDetailsModal'));
    const content = document.getElementById('assignmentDetailsContent');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Fetch analytics (you could implement AJAX here)
    setTimeout(() => {
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                <h6>Assignment Analytics</h6>
                <p class="text-muted">Assignment ID: ${assignmentId}</p>
                <p class="text-muted">Analytics would be loaded here via AJAX.</p>
            </div>
        `;
    }, 500);
    
    modal.show();
}

function exportAssignment(assignmentId) {
    fetch('/admin/results/export', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ assignment_id: assignmentId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => {
                window.location.href = data.download_url;
            }, 1000);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to export assignment. Please try again.', 'error');
    });
}

function exportResults() {
    fetch('/admin/results/export', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => {
                window.location.href = data.download_url;
            }, 1000);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to export results. Please try again.', 'error');
    });
}

function refreshData() {
    showToast('Info', 'Refreshing data...', 'info');
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function printReport() {
    window.print();
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
