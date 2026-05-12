@extends('layouts.app')

@section('title', 'Assignments')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Assignments</h1>
            <p class="text-muted mb-0">View and submit your assignments</p>
        </div>
        <div class="text-end">
            <div class="d-flex align-items-center">
                <div class="me-3">
                    <span class="badge bg-primary">Total: {{ $assignments->count() }}</span>
                    <span class="badge bg-success">Submitted: {{ $assignments->where('submissions', '!=', null)->count() }}</span>
                    <span class="badge bg-warning">Pending: {{ $assignments->where('submissions', '=', null)->where('due_date', '>', now())->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label for="statusFilter" class="form-label">Filter by Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Assignments</option>
                        <option value="submitted">Submitted</option>
                        <option value="pending">Pending Submission</option>
                        <option value="overdue">Overdue</option>
                        <option value="graded">Graded</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="subjectFilter" class="form-label">Filter by Subject</label>
                    <select class="form-select" id="subjectFilter">
                        <option value="">All Subjects</option>
                        @foreach($assignments->pluck('subject.name')->unique() as $subject)
                            <option value="{{ $subject }}">{{ $subject }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="sortBy" class="form-label">Sort By</label>
                    <select class="form-select" id="sortBy">
                        <option value="due_date">Due Date</option>
                        <option value="title">Title</option>
                        <option value="subject">Subject</option>
                        <option value="status">Status</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments List -->
    <div class="row" id="assignmentsContainer">
        @foreach($assignments as $assignment)
            <div class="col-lg-6 col-xl-4 mb-4" 
                 data-status="{{ $assignment->submissions->first() ? ($assignment->submissions->first()->isGraded() ? 'graded' : 'submitted') : ($assignment->isOverdue() ? 'overdue' : 'pending') }}"
                 data-subject="{{ $assignment->subject->name }}">
                <div class="card h-100 assignment-card">
                    <div class="card-body">
                        <!-- Assignment Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <h6 class="card-title text-truncate mb-1">{{ $assignment->title }}</h6>
                                <span class="badge bg-info">{{ $assignment->subject->name }}</span>
                            </div>
                            <div class="text-end">
                                @if($assignment->isOverdue())
                                    <span class="badge bg-danger">Overdue</span>
                                @elseif($assignment->isDueSoon())
                                    <span class="badge bg-warning">Due Soon</span>
                                @else
                                    <span class="badge bg-success">Open</span>
                                @endif
                            </div>
                        </div>

                        <!-- Assignment Details -->
                        <div class="assignment-details mb-3">
                            <div class="row g-2 text-muted small">
                                <div class="col-6">
                                    <i class="fas fa-user-tie me-1"></i>
                                    {{ $assignment->teacher->name }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    {{ $assignment->total_marks }} Marks
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $assignment->due_date->format('M j, Y') }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $assignment->due_date->format('g:i A') }}
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($assignment->description)
                            <div class="mb-3">
                                <p class="card-text small text-muted mb-0">
                                    {{ Str::limit($assignment->description, 80) }}
                                </p>
                            </div>
                        @endif

                        <!-- Files Indicator -->
                        @if($assignment->files->count() > 0)
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-paperclip me-1"></i>
                                    {{ $assignment->files->count() }} file(s) attached
                                </small>
                            </div>
                        @endif

                        <!-- Submission Status -->
                        <div class="submission-status mb-3">
                            @php
                                $submission = $assignment->submissions->first();
                            @endphp
                            @if($submission)
                                <div class="alert alert-{{ $submission->isGraded() ? 'success' : ($submission->isLate() ? 'warning' : 'info') }} py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $submission->getStatusDisplay() }}</strong>
                                            @if($submission->isGraded())
                                                <span class="badge bg-{{ $submission->getGradeColor() }} ms-2">
                                                    {{ $submission->getGrade() }} ({{ $submission->marks_obtained }}/{{ $assignment->total_marks }})
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <small>{{ $submission->getFormattedSubmissionDate() }}</small>
                                        </div>
                                    </div>
                                    @if($submission->feedback)
                                        <div class="mt-2">
                                            <small class="text-muted">{{ $submission->getFeedbackSummary() }}</small>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="alert alert-light py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>Not Submitted</strong>
                                            @if($assignment->isOverdue())
                                                <span class="badge bg-danger ms-2">Overdue</span>
                                            @else
                                                <span class="badge bg-warning ms-2">Pending</span>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <small>{{ $assignment->getTimeRemaining() }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button class="btn btn-outline-primary btn-sm" onclick="showAssignmentDetails({{ $assignment->id }})">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </button>
                                </div>
                                <div>
                                    @if($submission)
                                        <button class="btn btn-outline-info btn-sm" onclick="viewSubmission({{ $assignment->id }})">
                                            <i class="fas fa-file-alt me-1"></i>View Submission
                                        </button>
                                    @else
                                        @if(!$assignment->isOverdue())
                                            <a href="{{ route('student.assignments.submit', $assignment->id) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-paper-plane me-1"></i>Submit
                                            </a>
                                        @else
                                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                                <i class="fas fa-lock me-1"></i>Overdue
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Empty State -->
    @if($assignments->count() === 0)
        <div class="text-center py-5">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Assignments</h5>
            <p class="text-muted">You don't have any assignments at the moment.</p>
        </div>
    @endif
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
                <button type="button" class="btn btn-primary" id="modalSubmitBtn" style="display: none;">
                    <i class="fas fa-paper-plane me-2"></i>Submit Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.assignment-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
}

.assignment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.assignment-details {
    border-top: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.submission-status {
    min-height: 60px;
}

.action-buttons {
    border-top: 1px solid #dee2e6;
    padding-top: 0.75rem;
}

@media (max-width: 768px) {
    .assignment-card {
        margin-bottom: 1rem;
    }
    
    .action-buttons .d-flex {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-buttons .btn {
        width: 100%;
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
    const subjectFilter = document.getElementById('subjectFilter');
    const sortBy = document.getElementById('sortBy');
    
    [statusFilter, subjectFilter, sortBy].forEach(filter => {
        filter.addEventListener('change', function() {
            filterAssignments();
        });
    });
}

function filterAssignments() {
    const statusFilter = document.getElementById('statusFilter').value;
    const subjectFilter = document.getElementById('subjectFilter').value;
    const sortBy = document.getElementById('sortBy').value;
    
    const cards = document.querySelectorAll('.assignment-card').forEach(card => {
        const parentDiv = card.parentElement;
        const status = parentDiv.dataset.status;
        const subject = parentDiv.dataset.subject;
        
        let show = true;
        
        // Filter by status
        if (statusFilter && status !== status) {
            show = false;
        }
        
        // Filter by subject
        if (subjectFilter && subject !== subjectFilter) {
            show = false;
        }
        
        parentDiv.style.display = show ? '' : 'none';
    });
    
    // Update count
    const visibleCards = document.querySelectorAll('.assignment-card').length;
    const totalCards = document.querySelectorAll('#assignmentsContainer > div').length;
    
    // You could update a counter here if needed
}

function showAssignmentDetails(assignmentId) {
    const modal = new bootstrap.Modal(document.getElementById('assignmentDetailsModal'));
    const content = document.getElementById('assignmentDetailsContent');
    const submitBtn = document.getElementById('modalSubmitBtn');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Fetch assignment details (you could implement AJAX here)
    // For now, let's just show the modal with basic info
    setTimeout(() => {
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                <h6>Assignment Details</h6>
                <p class="text-muted">Assignment ID: ${assignmentId}</p>
                <p class="text-muted">Detailed view would be loaded here via AJAX.</p>
            </div>
        `;
        
        // Show submit button if assignment can be submitted
        submitBtn.style.display = 'none';
    }, 500);
    
    modal.show();
}

function viewSubmission(assignmentId) {
    window.location.href = `/student/assignments/${assignmentId}`;
}

// Auto-refresh for time-sensitive assignments
setInterval(() => {
    // Update time remaining for pending assignments
    document.querySelectorAll('[data-status="pending"]').forEach(card => {
        // You could update time remaining here
    });
}, 60000); // Update every minute
</script>
@endpush
