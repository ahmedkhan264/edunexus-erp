@extends('layouts.app')

@section('title', 'Assignment Results')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Assignment Results</h1>
            <p class="text-muted mb-0">View your assignment grades and performance</p>
        </div>
        <div class="text-end">
            <button class="btn btn-outline-success" onclick="exportResults()">
                <i class="fas fa-download me-2"></i>Export Results
            </button>
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
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $statistics['graded_assignments'] }}</h4>
                    <small>Graded</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $statistics['pending_assignments'] }}</h4>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $statistics['average_score'] ? number_format($statistics['average_score'], 1) : 'N/A' }}</h4>
                    <small>Average Score</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Grade Distribution Chart -->
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
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $grade === 'Not Graded' ? 'secondary' : ($grade[0] === 'A' ? 'success' : ($grade[0] === 'B' ? 'info' : ($grade[0] === 'C' ? 'warning' : ($grade[0] === 'D' ? 'danger' : 'dark'))) }}" 
                                                 style="width: {{ ($count / $statistics['total_assignments']) * 100 }}%">
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
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>Performance Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="performance-stats">
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Total Marks:</span>
                                <strong>{{ $statistics['total_marks'] }}</strong>
                            </div>
                        </div>
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Obtained Marks:</span>
                                <strong>{{ $statistics['obtained_marks'] }}</strong>
                            </div>
                        </div>
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Percentage:</span>
                                <strong>{{ $statistics['total_marks'] > 0 ? number_format(($statistics['obtained_marks'] / $statistics['total_marks']) * 100, 1) : 0 }}%</strong>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="d-flex justify-content-between">
                                <span>Completion Rate:</span>
                                <strong>{{ $statistics['total_assignments'] > 0 ? number_format(($statistics['graded_assignments'] / $statistics['total_assignments']) * 100, 1) : 0 }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subject Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-book me-2"></i>Subject Performance
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($statistics['subject_performance'] as $subject => $performance)
                            <div class="col-md-4 mb-3">
                                <div class="subject-card border rounded p-3">
                                    <h6 class="text-primary mb-2">{{ $subject }}</h6>
                                    <div class="subject-stats">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Assignments:</small>
                                            <strong>{{ $performance['total'] }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Graded:</small>
                                            <strong>{{ $performance['graded'] }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <small>Average:</small>
                                            <strong>{{ $performance['average_score'] ? number_format($performance['average_score'], 1) : 'N/A' }}</strong>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" style="width: {{ $performance['total'] > 0 ? ($performance['graded'] / $performance['total']) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
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
        <div class="card-header bg-warning text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>Assignment Results
                </h6>
                <div>
                    <select class="form-select form-select-sm" id="filterSelect">
                        <option value="">All Assignments</option>
                        <option value="graded">Graded</option>
                        <option value="pending">Pending</option>
                        <option value="late">Late</option>
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
                            <th>Subject</th>
                            <th>Submitted</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submissions as $submission)
                            <tr class="result-row" 
                                data-status="{{ $submission->isGraded() ? 'graded' : 'pending' }}"
                                data-late="{{ $submission->isLate() ? 'true' : 'false' }}">
                                <td>
                                    <div class="fw-medium">{{ $submission->assignment->title }}</div>
                                    <small class="text-muted">Due: {{ $submission->assignment->getFormattedDueDate() }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $submission->assignment->subject->name }}</span>
                                </td>
                                <td>
                                    <div>{{ $submission->getFormattedSubmissionDate() }}</div>
                                    @if($submission->isLate())
                                        <small class="text-warning">Late submission</small>
                                    @endif
                                </td>
                                <td>
                                    @if($submission->isGraded())
                                        <div class="marks-display">
                                            <span class="badge bg-{{ $submission->getGradeColor() }}">
                                                {{ $submission->marks_obtained }} / {{ $submission->assignment->total_marks }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted">Not graded</span>
                                    @endif
                                </td>
                                <td>
                                    @if($submission->isGraded())
                                        <span class="badge bg-{{ $submission->getGradeColor() }}">
                                            {{ $submission->getGrade() }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($submission->isGraded())
                                        <span class="badge bg-success">Graded</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewDetails({{ $submission->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($submission->isGraded() && $submission->feedback)
                                            <button class="btn btn-outline-info" onclick="viewFeedback({{ $submission->id }})">
                                                <i class="fas fa-comment"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            @if($submissions->count() === 0)
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Assignment Results</h5>
                    <p class="text-muted">You haven't submitted any assignments yet.</p>
                    <a href="{{ route('student.assignments.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>View Assignments
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Assignment Details Modal -->
<div class="modal fade" id="assignmentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
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

<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Teacher Feedback</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="feedbackContent">
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

.subject-card {
    transition: transform 0.2s ease;
    cursor: pointer;
}

.subject-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.result-row:hover {
    background-color: #f8f9fa;
}

.performance-stats .stat-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.performance-stats .stat-item:last-child {
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
        filterResults();
    });
}

function filterResults() {
    const filterValue = document.getElementById('filterSelect').value;
    const rows = document.querySelectorAll('.result-row');
    
    rows.forEach(row => {
        const status = row.dataset.status;
        const isLate = row.dataset.late === 'true';
        
        let show = true;
        
        if (filterValue === 'graded' && status !== 'graded') {
            show = false;
        } else if (filterValue === 'pending' && status !== 'pending') {
            show = false;
        } else if (filterValue === 'late' && !isLate) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function viewDetails(submissionId) {
    const modal = new bootstrap.Modal(document.getElementById('assignmentDetailsModal'));
    const content = document.getElementById('assignmentDetailsContent');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Fetch assignment details (you could implement AJAX here)
    // For now, let's just show the modal with basic info
    setTimeout(() => {
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                <h6>Assignment Details</h6>
                <p class="text-muted">Submission ID: ${submissionId}</p>
                <p class="text-muted">Detailed view would be loaded here via AJAX.</p>
            </div>
        `;
    }, 500);
    
    modal.show();
}

function viewFeedback(submissionId) {
    const modal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    const content = document.getElementById('feedbackContent');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Fetch feedback (you could implement AJAX here)
    setTimeout(() => {
        content.innerHTML = `
            <div class="alert alert-info">
                <h6><i class="fas fa-comment me-2"></i>Teacher Feedback</h6>
                <p class="mb-0">Great work! Your assignment shows excellent understanding of the concepts. Keep up the good effort!</p>
            </div>
        `;
    }, 500);
    
    modal.show();
}

function exportResults() {
    fetch('/student/results/export', {
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
            // You could trigger download here
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
