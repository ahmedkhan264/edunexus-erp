@extends('layouts.app')

@section('title', 'Grade Assignment')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Grade Assignment</h1>
            <p class="text-muted mb-0">{{ $assignment->title }}</p>
        </div>
        <div class="text-end">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-success me-2" onclick="exportGrades()">
                    <i class="fas fa-download me-1"></i>Export Grades
                </button>
                <a href="{{ route('teacher.assignments.grading.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Grading
                </a>
            </div>
        </div>
    </div>

    <!-- Assignment Details -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-info-circle me-2"></i>Assignment Details
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Subject:</strong></td>
                            <td>{{ $assignment->subject->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Class:</strong></td>
                            <td>Grade {{ $assignment->schoolClass->grade_level }} - {{ $assignment->section }}</td>
                        </tr>
                        <tr>
                            <td><strong>Due Date:</strong></td>
                            <td>{{ $assignment->getFormattedDueDate() }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Marks:</strong></td>
                            <td>{{ $assignment->total_marks }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Submissions:</strong></td>
                            <td>{{ $statistics['total_submissions'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Graded:</strong></td>
                            <td>{{ $statistics['graded_submissions'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Pending:</strong></td>
                            <td>{{ $statistics['pending_submissions'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Average Score:</strong></td>
                            <td>{{ $statistics['average_score'] ? number_format($statistics['average_score'], 1) : 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            @if($assignment->description)
                <div class="mt-3">
                    <h6>Description:</h6>
                    <div class="alert alert-light">
                        {{ $assignment->description }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Grading Statistics -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i>Grading Statistics
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h6>Grade Distribution</h6>
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
                                                 style="width: {{ ($count / $statistics['total_submissions']) * 100 }}%">
                                                {{ $count }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="col-md-4">
                    <h6>Performance Summary</h6>
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="stat-box bg-success text-white p-3 rounded">
                                <div class="h4 mb-0">{{ $statistics['highest_score'] ?? 'N/A' }}</div>
                                <small>Highest</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="stat-box bg-danger text-white p-3 rounded">
                                <div class="h4 mb-0">{{ $statistics['lowest_score'] ?? 'N/A' }}</div>
                                <small>Lowest</small>
                            </div>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="stat-box bg-primary text-white p-3 rounded">
                            <div class="h4 mb-0">{{ $statistics['average_score'] ? number_format($statistics['average_score'], 1) : 'N/A' }}</div>
                            <small>Average</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submissions List -->
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-users me-2"></i>Student Submissions
                </h5>
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="toggleBulkMode()">
                        <i class="fas fa-check-square me-1"></i>Bulk Grade
                    </button>
                    <button class="btn btn-sm btn-outline-success ms-2" onclick="saveBulkGrades()" id="bulkSaveBtn" style="display: none;">
                        <i class="fas fa-save me-1"></i>Save Bulk Grades
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Filter and Search -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by student name...">
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Submissions</option>
                        <option value="graded">Graded</option>
                        <option value="pending">Pending</option>
                        <option value="late">Late</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="sortBy">
                        <option value="name">Sort by Name</option>
                        <option value="submission">Sort by Submission Date</option>
                        <option value="grade">Sort by Grade</option>
                    </select>
                </div>
            </div>

            <!-- Submissions Table -->
            <div class="table-responsive">
                <table class="table table-hover" id="submissionsTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Student</th>
                            <th>Submission Date</th>
                            <th>Files</th>
                            <th>Content</th>
                            <th width="120">Marks</th>
                            <th width="100">Grade</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submissions as $submission)
                            <tr class="submission-row" 
                                data-submission-id="{{ $submission->id }}"
                                data-status="{{ $submission->isGraded() ? 'graded' : 'pending' }}"
                                data-student="{{ $submission->student->user->name }}"
                                data-submission-date="{{ $submission->created_at->format('Y-m-d H:i:s') }}"
                                data-grade="{{ $submission->isGraded() ? $submission->getPercentageScore() : 0 }}">
                                <td>
                                    <input type="checkbox" class="form-check-input submission-checkbox" value="{{ $submission->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <img src="https://ui-avatars.com/api/?name={{ $submission->student->user->name }}&background=random" 
                                                 class="rounded-circle" width="32" height="32">
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $submission->student->user->name }}</div>
                                            <small class="text-muted">{{ $submission->student->roll_number ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $submission->getFormattedSubmissionDate() }}</div>
                                    @if($submission->isLate())
                                        <small class="text-warning">Late submission</small>
                                    @endif
                                </td>
                                <td>
                                    @if($submission->files->count() > 0)
                                        <div class="file-list">
                                            @foreach($submission->files as $file)
                                                <div class="d-flex align-items-center mb-1">
                                                    <i class="{{ $file->getFileIcon() }} text-{{ $file->getFileColor() }} me-1"></i>
                                                    <small>{{ $file->original_name }}</small>
                                                    <a href="{{ $file->getDownloadUrl() }}" class="ms-auto">
                                                        <i class="fas fa-download text-primary"></i>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">No files</span>
                                    @endif
                                </td>
                                <td>
                                    @if($submission->content)
                                        <div class="content-preview">
                                            <small>{{ Str::limit($submission->content, 50) }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">No content</span>
                                    @endif
                                </td>
                                <td>
                                    @if($submission->isGraded())
                                        <div class="marks-display">
                                            <span class="badge bg-{{ $submission->getGradeColor() }}">
                                                {{ $submission->marks_obtained }} / {{ $assignment->total_marks }}
                                            </span>
                                        </div>
                                    @else
                                        <input type="number" 
                                               class="form-control form-control-sm bulk-marks" 
                                               min="0" 
                                               max="{{ $assignment->total_marks }}"
                                               placeholder="0-{{ $assignment->total_marks }}"
                                               style="display: none;">
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
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewSubmission({{ $submission->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(!$submission->isGraded())
                                            <button class="btn btn-success" onclick="quickGrade({{ $submission->id }})">
                                                <i class="fas fa-graduation-cap"></i>
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
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Submissions</h5>
                    <p class="text-muted">No students have submitted this assignment yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Quick Grade Modal -->
<div class="modal fade" id="quickGradeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Quick Grade</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickGradeForm">
                    @csrf
                    <input type="hidden" id="quickGradeSubmissionId">
                    
                    <div class="mb-3">
                        <label for="quickMarks" class="form-label">Marks Obtained</label>
                        <input type="number" class="form-control" id="quickMarks" 
                               min="0" max="{{ $assignment->total_marks }}" required>
                        <div class="form-text">Out of {{ $assignment->total_marks }} marks</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="quickFeedback" class="form-label">Feedback</label>
                        <textarea class="form-control" id="quickFeedback" rows="4" 
                                  placeholder="Provide feedback to the student..." maxlength="1000"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveQuickGrade()">
                    <i class="fas fa-save me-2"></i>Save Grade
                </button>
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

.file-list {
    max-height: 80px;
    overflow-y: auto;
}

.content-preview {
    max-height: 40px;
    overflow: hidden;
}

.bulk-marks {
    width: 80px;
}

.submission-row:hover {
    background-color: #f8f9fa;
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
    initializeBulkMode();
});

function initializeFilters() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const sortBy = document.getElementById('sortBy');
    
    [searchInput, statusFilter, sortBy].forEach(filter => {
        filter.addEventListener('input change', function() {
            filterSubmissions();
        });
    });
}

function filterSubmissions() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const sortBy = document.getElementById('sortBy').value;
    
    const rows = document.querySelectorAll('.submission-row');
    
    rows.forEach(row => {
        const studentName = row.dataset.student.toLowerCase();
        const status = row.dataset.status;
        
        let show = true;
        
        // Search filter
        if (searchTerm && !studentName.includes(searchTerm)) {
            show = false;
        }
        
        // Status filter
        if (statusFilter && status !== statusFilter) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
    
    // Sort visible rows
    if (sortBy) {
        sortSubmissions(sortBy);
    }
}

function sortSubmissions(sortBy) {
    const tbody = document.querySelector('#submissionsTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        switch(sortBy) {
            case 'name':
                return a.dataset.student.localeCompare(b.dataset.student);
            case 'submission':
                return new Date(b.dataset.submissionDate) - new Date(a.dataset.submissionDate);
            case 'grade':
                return parseFloat(b.dataset.grade) - parseFloat(a.dataset.grade);
            default:
                return 0;
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

function initializeBulkMode() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.submission-checkbox');
    
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            
            selectAll.checked = allChecked;
            selectAll.indeterminate = anyChecked && !allChecked;
        });
    });
}

function toggleBulkMode() {
    const bulkMarks = document.querySelectorAll('.bulk-marks');
    const bulkSaveBtn = document.getElementById('bulkSaveBtn');
    const isVisible = bulkMarks[0].style.display !== 'none';
    
    bulkMarks.forEach(input => {
        input.style.display = isVisible ? 'none' : 'block';
        
        if (!isVisible) {
            const row = input.closest('tr');
            const submissionId = row.dataset.submissionId;
            const marksCell = row.querySelector('.marks-display');
            
            if (marksCell) {
                const marksText = marksCell.textContent;
                const marks = marksText.match(/(\d+)/);
                input.value = marks ? marks[1] : '';
            }
        }
    });
    
    bulkSaveBtn.style.display = isVisible ? 'none' : 'inline-block';
}

function saveBulkGrades() {
    const checkboxes = document.querySelectorAll('.submission-checkbox:checked');
    const grades = [];
    
    checkboxes.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const submissionId = row.dataset.submissionId;
        const marksInput = row.querySelector('.bulk-marks');
        
        if (marksInput && marksInput.value) {
            grades.push({
                submission_id: submissionId,
                marks_obtained: parseInt(marksInput.value),
                feedback: ''
            });
        }
    });
    
    if (grades.length === 0) {
        showToast('Warning', 'Please enter marks for selected submissions.', 'warning');
        return;
    }
    
    fetch(`/teacher/assignments/grading/{{ $assignment->id }}/bulk-grade`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ grades: grades })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to save grades. Please try again.', 'error');
    });
}

function viewSubmission(submissionId) {
    window.location.href = `/teacher/assignments/grading/{{ $assignment->id }}/submission/${submissionId}`;
}

function quickGrade(submissionId) {
    document.getElementById('quickGradeSubmissionId').value = submissionId;
    document.getElementById('quickMarks').value = '';
    document.getElementById('quickFeedback').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('quickGradeModal'));
    modal.show();
}

function saveQuickGrade() {
    const submissionId = document.getElementById('quickGradeSubmissionId').value;
    const marks = document.getElementById('quickMarks').value;
    const feedback = document.getElementById('quickFeedback').value;
    
    if (!marks) {
        showToast('Error', 'Please enter marks.', 'error');
        return;
    }
    
    fetch(`/teacher/assignments/grading/{{ $assignment->id }}/submission/${submissionId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            marks_obtained: parseInt(marks),
            feedback: feedback
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('quickGradeModal')).hide();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to save grade. Please try again.', 'error');
    });
}

function exportGrades() {
    window.location.href = `/teacher/assignments/grading/{{ $assignment->id }}/export`;
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
