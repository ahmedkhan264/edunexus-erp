@extends('layouts.app')

@section('title', 'Grade Submission')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Grade Submission</h1>
            <p class="text-muted mb-0">{{ $submission->student->user->name }} - {{ $assignment->title }}</p>
        </div>
        <div class="text-end">
            <a href="{{ route('teacher.assignments.grading.grade', $assignment->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Grading
            </a>
        </div>
    </div>

    <!-- Student and Assignment Info -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>Student Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="https://ui-avatars.com/api/?name={{ $submission->student->user->name }}&background=random" 
                             class="rounded-circle me-3" width="60" height="60">
                        <div>
                            <h5 class="mb-1">{{ $submission->student->user->name }}</h5>
                            <p class="text-muted mb-0">Roll Number: {{ $submission->student->roll_number ?? 'N/A' }}</p>
                            <p class="text-muted mb-0">Grade {{ $assignment->schoolClass->grade_level }} - {{ $assignment->section }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>Assignment Details
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Title:</strong></td>
                            <td>{{ $assignment->title }}</td>
                        </tr>
                        <tr>
                            <td><strong>Subject:</strong></td>
                            <td>{{ $assignment->subject->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Due Date:</strong></td>
                            <td>{{ $assignment->getFormattedDueDate() }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Marks:</strong></td>
                            <td>{{ $assignment->total_marks }}</td>
                        </tr>
                        <tr>
                            <td><strong>Submitted:</strong></td>
                            <td>{{ $submission->getFormattedSubmissionDate() }}</td>
                        </tr>
                        @if($submission->isLate())
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td><span class="badge bg-warning">Late Submission</span></td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Submission Content -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Submission Content
                    </h6>
                </div>
                <div class="card-body">
                    @if($submission->content)
                        <div class="submission-content">
                            <div class="bg-light p-3 rounded">
                                {{ nl2br(e($submission->content)) }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-file-alt fa-2x mb-2"></i>
                            <p>No written content provided</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Submitted Files -->
    @if($submission->files->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-paperclip me-2"></i>Submitted Files
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($submission->files as $file)
                                <div class="col-md-6 mb-3">
                                    <div class="file-item border rounded p-3">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <div class="file-icon bg-{{ $file->getFileColor() }} text-white rounded p-2">
                                                    <i class="{{ $file->getFileIcon() }} fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium">{{ $file->original_name }}</div>
                                                <small class="text-muted">{{ $file->getFormattedSize() }}</small>
                                                <div class="mt-1">
                                                    <span class="badge bg-light text-dark">{{ $file->getFileTypeDescription() }}</span>
                                                </div>
                                            </div>
                                            <div class="ms-3">
                                                <div class="btn-group-vertical">
                                                    @if($file->canBePreviewed())
                                                        <button class="btn btn-sm btn-outline-info mb-1" onclick="previewFile({{ $file->id }})">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    @endif
                                                    <a href="{{ $file->getDownloadUrl() }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
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
    @endif

    <!-- Grading Form -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Grade Submission
                    </h6>
                </div>
                <div class="card-body">
                    <form id="gradingForm">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="marks_obtained" class="form-label">Marks Obtained <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="marks_obtained" 
                                           name="marks_obtained" 
                                           min="0" 
                                           max="{{ $assignment->total_marks }}"
                                           value="{{ $submission->marks_obtained ?? '' }}"
                                           required>
                                    <span class="input-group-text">/ {{ $assignment->total_marks }}</span>
                                </div>
                                <div class="form-text">Enter marks obtained out of {{ $assignment->total_marks }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Grade Preview</label>
                                <div class="grade-preview">
                                    <div class="h4 text-center">
                                        <span id="gradeDisplay" class="badge bg-{{ $submission->isGraded() ? $submission->getGradeColor() : 'secondary' }}">
                                            {{ $submission->isGraded() ? $submission->getGrade() : 'Not Graded' }}
                                        </span>
                                    </div>
                                    <div class="text-center">
                                        <small id="percentageDisplay" class="text-muted">
                                            {{ $submission->isGraded() ? number_format($submission->getPercentageScore(), 1) . '%' : '0%' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="feedback" class="form-label">Feedback</label>
                            <textarea class="form-control" 
                                      id="feedback" 
                                      name="feedback" 
                                      rows="6" 
                                      placeholder="Provide feedback to the student..." 
                                      maxlength="1000">{{ $submission->feedback ?? '' }}</textarea>
                            <div class="form-text">
                                <span id="charCount">0</span>/1000 characters
                            </div>
                        </div>

                        <!-- Quick Feedback Templates -->
                        <div class="mb-4">
                            <label class="form-label">Quick Feedback Templates</label>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <button type="button" class="btn btn-outline-sm btn-outline-primary w-100" onclick="addFeedback('Excellent work! Well done.')">
                                        Excellent Work
                                    </button>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <button type="button" class="btn btn-outline-sm btn-outline-info w-100" onclick="addFeedback('Good effort. Keep it up!')">
                                        Good Effort
                                    </button>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <button type="button" class="btn btn-outline-sm btn-outline-warning w-100" onclick="addFeedback('Needs improvement. Please review the requirements.')">
                                        Needs Improvement
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('teacher.assignments.grading.grade', $assignment->id) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back
                                </a>
                            </div>
                            <div>
                                @if($submission->isGraded())
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-edit me-2"></i>Update Grade
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Save Grade
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Grading Guidelines -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Grading Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <div class="grading-scale">
                        <h6>Grade Scale</h6>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-success">A+</span>
                            <span>90-100%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-success">A</span>
                            <span>85-89%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-success">A-</span>
                            <span>80-84%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-info">B+</span>
                            <span>75-79%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-info">B</span>
                            <span>70-74%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-info">B-</span>
                            <span>65-69%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-warning">C+</span>
                            <span>60-64%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-warning">C</span>
                            <span>55-59%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-warning">C-</span>
                            <span>50-54%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-danger">D+</span>
                            <span>45-49%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-danger">D</span>
                            <span>40-44%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-danger">D-</span>
                            <span>35-39%</span>
                        </div>
                        <div class="scale-item d-flex justify-content-between mb-2">
                            <span class="badge bg-dark">F</span>
                            <span>0-34%</span>
                        </div>
                    </div>
                    
                    @if($submission->isLate())
                        <div class="alert alert-warning mt-3">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Late Submission</h6>
                            <p class="mb-0">This submission was submitted {{ $submission->getTimeUntilDeadline() }} after the due date.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- File Preview Modal -->
<div class="modal fade" id="filePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">File Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="filePreviewContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.file-item {
    transition: transform 0.2s ease;
}

.file-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.file-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.grade-preview {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 0.5rem;
    padding: 1rem;
    border: 2px solid #dee2e6;
}

.scale-item {
    padding: 0.25rem 0;
}

.submission-content {
    max-height: 300px;
    overflow-y: auto;
}

@media (max-width: 768px) {
    .btn-group-vertical {
        flex-direction: row;
    }
    
    .btn-group-vertical .btn {
        margin-bottom: 0;
        margin-right: 0.25rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeGradingForm();
});

function initializeGradingForm() {
    const form = document.getElementById('gradingForm');
    const marksInput = document.getElementById('marks_obtained');
    const feedbackTextarea = document.getElementById('feedback');
    const charCount = document.getElementById('charCount');
    
    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitGrade();
    });
    
    // Update grade preview on marks change
    marksInput.addEventListener('input', function() {
        updateGradePreview();
    });
    
    // Update character count
    feedbackTextarea.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });
    
    // Initialize grade preview
    updateGradePreview();
}

function updateGradePreview() {
    const marks = parseFloat(document.getElementById('marks_obtained').value) || 0;
    const totalMarks = {{ $assignment->total_marks }};
    const percentage = (marks / totalMarks) * 100;
    
    // Update percentage
    document.getElementById('percentageDisplay').textContent = percentage.toFixed(1) + '%';
    
    // Update grade
    const grade = calculateGrade(percentage);
    const gradeColor = getGradeColor(grade);
    
    const gradeDisplay = document.getElementById('gradeDisplay');
    gradeDisplay.textContent = grade;
    gradeDisplay.className = `badge bg-${gradeColor}`;
}

function calculateGrade(percentage) {
    if (percentage >= 90) return 'A+';
    if (percentage >= 85) return 'A';
    if (percentage >= 80) return 'A-';
    if (percentage >= 75) return 'B+';
    if (percentage >= 70) return 'B';
    if (percentage >= 65) return 'B-';
    if (percentage >= 60) return 'C+';
    if (percentage >= 55) return 'C';
    if (percentage >= 50) return 'C-';
    if (percentage >= 45) return 'D+';
    if (percentage >= 40) return 'D';
    if (percentage >= 35) return 'D-';
    return 'F';
}

function getGradeColor(grade) {
    if (grade[0] === 'A') return 'success';
    if (grade[0] === 'B') return 'info';
    if (grade[0] === 'C') return 'warning';
    if (grade[0] === 'D') return 'danger';
    return 'dark';
}

function addFeedback(template) {
    const feedbackTextarea = document.getElementById('feedback');
    const currentFeedback = feedbackTextarea.value;
    
    if (currentFeedback) {
        feedbackTextarea.value = currentFeedback + '\n\n' + template;
    } else {
        feedbackTextarea.value = template;
    }
    
    // Update character count
    document.getElementById('charCount').textContent = feedbackTextarea.value.length;
}

function submitGrade() {
    const form = document.getElementById('gradingForm');
    const formData = new FormData(form);
    
    fetch(`/teacher/assignments/grading/{{ $assignment->id }}/submission/{{ $submission->id }}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => {
                window.location.href = `/teacher/assignments/grading/{{ $assignment->id }}`;
            }, 1500);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to save grade. Please try again.', 'error');
    });
}

function previewFile(fileId) {
    const modal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
    const content = document.getElementById('filePreviewContent');
    
    // Show loading state
    content.innerHTML = '<i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Loading preview...</p>';
    
    // Fetch file preview
    fetch(`/teacher/assignments/grading/submissions/files/${fileId}/preview`)
        .then(response => {
            if (response.ok) {
                return response.blob();
            }
            throw new Error('Preview not available');
        })
        .then(blob => {
            const url = URL.createObjectURL(blob);
            content.innerHTML = `<img src="${url}" class="img-fluid" alt="File preview">`;
        })
        .catch(error => {
            content.innerHTML = '<div class="text-center text-muted"><i class="fas fa-eye-slash fa-2x mb-2"></i><p>Preview not available</p></div>';
        });
    
    modal.show();
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
