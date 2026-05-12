@extends('layouts.app')

@section('title', 'Assignment Results')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Assignment Results</h1>
            <p class="text-muted mb-0">View and analyze assignment performance</p>
        </div>
        <div class="text-end">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-success me-2" onclick="exportResults()">
                    <i class="fas fa-download me-2"></i>Export Results
                </button>
                <a href="{{ route('teacher.assignments.grading.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Grading
                </a>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $statistics['total_assignments'] }}</h4>
                    <small>Total Assignments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $statistics['total_submissions'] }}</h4>
                    <small>Total Submissions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $statistics['graded_submissions'] }}</h4>
                    <small>Graded</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $statistics['pending_submissions'] }}</h4>
                    <small>Pending</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Distribution and Performance -->
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
                                        <span class="badge bg-{{ $grade === 'Not Graded' ? 'secondary' : (isset($grade[0]) && $grade[0] === 'A' ? 'success' : (isset($grade[0]) && $grade[0] === 'B' ? 'info' : (isset($grade[0]) && $grade[0] === 'C' ? 'warning' : (isset($grade[0]) && $grade[0] === 'D' ? 'danger' : 'dark')))) }}">
                                            {{ $grade }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $grade === 'Not Graded' ? 'secondary' : (isset($grade[0]) && $grade[0] === 'A' ? 'success' : (isset($grade[0]) && $grade[0] === 'B' ? 'info' : (isset($grade[0]) && $grade[0] === 'C' ? 'warning' : (isset($grade[0]) && $grade[0] === 'D' ? 'danger' : 'dark')))) }}" 
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
                        <i class="fas fa-chart-line me-2"></i>Performance Metrics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="performance-stats">
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Average Score:</span>
                                <strong>{{ $statistics['average_score'] ? number_format($statistics['average_score'], 1) : 'N/A' }}</strong>
                            </div>
                        </div>
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Grading Rate:</span>
                                <strong>{{ $statistics['total_submissions'] > 0 ? number_format(($statistics['graded_submissions'] / $statistics['total_submissions']) * 100, 1) : 0 }}%</strong>
                            </div>
                        </div>
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Submission Rate:</span>
                                <strong>{{ $statistics['total_assignments'] > 0 ? number_format(($statistics['total_submissions'] / ($statistics['total_assignments'] * 30)) * 100, 1) : 0 }}%</strong>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="d-flex justify-content-between">
                                <span>Pending Grading:</span>
                                <strong>{{ $statistics['pending_submissions'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class Performance -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Class Performance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="class-performance">
                        @foreach($statistics['class_performance'] as $grade => $performance)
                            <div class="performance-item mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">Grade {{ $grade }}</h6>
                                    <div class="text-end">
                                        <small class="text-muted">{{ $performance['total_assignments'] }} assignments</small>
                                    </div>
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
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-book me-2"></i>Subject Performance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="subject-performance">
                        @foreach($statistics['subject_performance'] as $subject => $performance)
                            <div class="performance-item mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0">{{ $subject }}</h6>
                                    <div class="text-end">
                                        <small class="text-muted">{{ $performance['total_assignments'] }} assignments</small>
                                    </div>
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
    </div>

    <!-- Assignment Results List -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>Assignment Results Summary
                </h6>
                <div>
                    <select class="form-select form-select-sm" id="statusFilter">
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
                                        <a href="{{ route('teacher.assignments.grading.grade', $assignment->id) }}" class="btn btn-outline-primary">
                                            <i class="fas fa-graduation-cap"></i>
                                        </a>
                                        <button class="btn btn-outline-info" onclick="viewResults({{ $assignment->id }})">
                                            <i class="fas fa-chart-bar"></i>
                                        </button>
                                        <button class="btn btn-outline-success" onclick="exportAssignmentResults({{ $assignment->id }})">
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
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Assignment Results</h5>
                    <p class="text-muted">You haven't created any assignments yet.</p>
                    <a href="{{ route('teacher.assignments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Assignment
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Assignment Results Modal -->
<div class="modal fade" id="assignmentResultsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Assignment Results</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="assignmentResultsContent">
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

.performance-item {
    border-bottom: 1px solid #eee;
    padding-bottom: 1rem;
}

.performance-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.assignment-row:hover {
    background-color: #f8f9fa;
}

.progress {
    border-radius: 0.25rem;
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
    const statusFilter = document.getElementById('statusFilter');
    
    statusFilter.addEventListener('change', function() {
        filterAssignments();
    });
}

function filterAssignments() {
    const filterValue = document.getElementById('statusFilter').value;
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

function viewResults(assignmentId) {
    const modal = new bootstrap.Modal(document.getElementById('assignmentResultsModal'));
    const content = document.getElementById('assignmentResultsContent');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Fetch assignment results (you could implement AJAX here)
    setTimeout(() => {
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                <h6>Assignment Results</h6>
                <p class="text-muted">Assignment ID: ${assignmentId}</p>
                <p class="text-muted">Detailed results would be loaded here via AJAX.</p>
            </div>
        `;
    }, 500);
    
    modal.show();
}

function exportAssignmentResults(assignmentId) {
    fetch('/teacher/results/export', {
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
        showToast('Error', 'Failed to export results. Please try again.', 'error');
    });
}

function exportResults() {
    fetch('/teacher/results/export', {
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
