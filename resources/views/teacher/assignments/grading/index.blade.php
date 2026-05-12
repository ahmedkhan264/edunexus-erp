@extends('layouts.app')

@section('title', 'Assignment Grading')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Assignment Grading</h1>
            <p class="text-muted mb-0">Grade and manage student submissions</p>
        </div>
        <div class="text-end">
            <a href="{{ route('teacher.assignments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Assignments
            </a>
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
                        <option value="pending">Pending Grading</option>
                        <option value="partially">Partially Graded</option>
                        <option value="completed">Fully Graded</option>
                        <option value="overdue">Overdue</option>
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
                        <option value="submissions">Submission Count</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignments List -->
    <div class="row" id="assignmentsContainer">
        @foreach($assignments as $assignment)
            @php
                $totalSubmissions = $assignment->submissions->count();
                $gradedSubmissions = $assignment->submissions->whereNotNull('graded_at')->count();
                $pendingSubmissions = $totalSubmissions - $gradedSubmissions;
                $isOverdue = $assignment->isOverdue();
                $status = $pendingSubmissions > 0 ? 'pending' : ($gradedSubmissions > 0 ? 'completed' : 'none');
            @endphp
            <div class="col-lg-6 col-xl-4 mb-4" 
                 data-status="{{ $status }}"
                 data-subject="{{ $assignment->subject->name }}"
                 data-submissions="{{ $totalSubmissions }}">
                <div class="card h-100 assignment-card">
                    <div class="card-body">
                        <!-- Assignment Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <h6 class="card-title text-truncate mb-1">{{ $assignment->title }}</h6>
                                <span class="badge bg-info">{{ $assignment->subject->name }}</span>
                            </div>
                            <div class="text-end">
                                @if($isOverdue)
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
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    {{ $assignment->schoolClass->grade_level }} - {{ $assignment->section }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-star me-1"></i>
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

                        <!-- Submission Statistics -->
                        <div class="submission-stats mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="h5 mb-0 text-primary">{{ $totalSubmissions }}</div>
                                        <small class="text-muted">Total</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="h5 mb-0 text-success">{{ $gradedSubmissions }}</div>
                                        <small class="text-muted">Graded</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="h5 mb-0 text-warning">{{ $pendingSubmissions }}</div>
                                        <small class="text-muted">Pending</small>
                                    </div>
                                </div>
                            </div>
                            
                            @if($totalSubmissions > 0)
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: {{ ($gradedSubmissions / $totalSubmissions) * 100 }}%"></div>
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
                                    @if($totalSubmissions > 0)
                                        <a href="{{ route('teacher.assignments.grading.grade', $assignment->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-graduation-cap me-1"></i>Grade Now
                                        </a>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="fas fa-inbox me-1"></i>No Submissions
                                        </button>
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
            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Assignments</h5>
            <p class="text-muted">You don't have any assignments to grade.</p>
            <a href="{{ route('teacher.assignments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Assignment
            </a>
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
                <button type="button" class="btn btn-primary" id="modalGradeBtn" style="display: none;">
                    <i class="fas fa-graduation-cap me-2"></i>Grade Assignment
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

.submission-stats {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.75rem;
}

.stat-item {
    padding: 0.25rem 0;
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
        if (statusFilter) {
            if (statusFilter === 'pending' && status !== 'pending') show = false;
            if (statusFilter === 'completed' && status !== 'completed') show = false;
            if (statusFilter === 'partially' && status !== 'pending') show = false;
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
    const gradeBtn = document.getElementById('modalGradeBtn');
    
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
        
        // Show grade button
        gradeBtn.style.display = 'inline-block';
        gradeBtn.onclick = function() {
            window.location.href = `/teacher/assignments/grading/${assignmentId}`;
        };
    }, 500);
    
    modal.show();
}
</script>
@endpush
