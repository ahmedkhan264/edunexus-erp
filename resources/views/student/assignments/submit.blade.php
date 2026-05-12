@extends('layouts.app')

@section('title', 'Submit Assignment')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Submit Assignment</h1>
            <p class="text-muted mb-0">{{ $assignment->title }}</p>
        </div>
        <div class="text-end">
            <a href="{{ route('student.assignments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Assignments
            </a>
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
                            <td><strong>Teacher:</strong></td>
                            <td>{{ $assignment->teacher->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Due Date:</strong></td>
                            <td>
                                <span class="badge bg-{{ $assignment->getStatusBadgeColor() }}">
                                    {{ $assignment->getStatusDisplay() }}
                                </span>
                                <br>
                                <small class="text-muted">{{ $assignment->getFormattedDueDate() }}</small>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Total Marks:</strong></td>
                            <td>{{ $assignment->total_marks }}</td>
                        </tr>
                        <tr>
                            <td><strong>Time Remaining:</strong></td>
                            <td>
                                <span class="text-{{ $assignment->isDueSoon() ? 'warning' : 'success' }}">
                                    {{ $assignment->getTimeRemaining() }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Resubmission:</strong></td>
                            <td>
                                @if($assignment->allow_resubmission)
                                    <span class="badge bg-success">Allowed</span>
                                @else
                                    <span class="badge bg-secondary">Not Allowed</span>
                                @endif
                            </td>
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
            
            @if($assignment->files->count() > 0)
                <div class="mt-3">
                    <h6>Assignment Files:</h6>
                    <div class="list-group">
                        @foreach($assignment->files as $file)
                            <div class="list-group-item d-flex align-items-center">
                                <div class="me-3">
                                    <i class="{{ $file->getFileIcon() }} text-{{ $file->getFileColor() }} fa-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium">{{ $file->original_name }}</div>
                                    <small class="text-muted">{{ $file->getFormattedSize() }}</small>
                                </div>
                                <div>
                                    <a href="{{ $file->getDownloadUrl() }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Submission Form -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-edit me-2"></i>
                @if($existingSubmission)
                    Update Your Submission
                @else
                    Submit Your Assignment
                @endif
            </h5>
        </div>
        <div class="card-body">
            @if($existingSubmission)
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-2"></i>Existing Submission</h6>
                    <p class="mb-0">
                        You have already submitted this assignment on {{ $existingSubmission->getFormattedSubmissionDate() }}.
                        @if($assignment->allow_resubmission && $existingSubmission->canBeEdited())
                            You can update your submission until {{ $existingSubmission->created_at->copy()->addHours(24)->format('M j, Y g:i A') }}.
                        @else
                            @if($assignment->allow_resubmission)
                                The resubmission window has closed.
                            @else
                                Resubmission is not allowed for this assignment.
                            @endif
                        @endif
                    </p>
                </div>
            @endif

            <form id="submissionForm" enctype="multipart/form-data">
                @csrf
                
                <!-- Content -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="content" class="form-label">
                            Your Answer/Comments
                            <small class="text-muted">(Optional - Max 2000 characters)</small>
                        </label>
                        <textarea class="form-control" id="content" name="content" rows="6"
                                  placeholder="Write your answer or comments here..." maxlength="2000"
                                  @if($existingSubmission) value="{{ $existingSubmission->content }}" @endif></textarea>
                        <div class="form-text">You can write your answer or add comments about your submission.</div>
                    </div>
                </div>

                <!-- File Upload -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-paperclip me-2"></i>Submission Files
                            <small class="text-muted">(Optional - Max 5 files, 10MB each)</small>
                        </label>
                        <div class="file-upload-area" id="fileUploadArea">
                            <input type="file" class="form-control" id="files" name="files[]" 
                                   multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.zip"
                                   style="display: none;">
                            <div class="upload-placeholder text-center p-4 border border-2 border-dashed rounded">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <p class="mb-2">Drag and drop files here or click to browse</p>
                                <p class="text-muted small mb-0">
                                    Supported formats: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, PNG, GIF, ZIP
                                </p>
                            </div>
                        </div>
                        
                        <!-- File Preview List -->
                        <div id="filePreviewList" class="mt-3" style="display: none;">
                            <h6 class="mb-3">Uploaded Files:</h6>
                            <div class="list-group" id="fileList"></div>
                        </div>

                        <!-- Existing Files -->
                        @if($existingSubmission && $existingSubmission->files->count() > 0)
                            <div class="mt-3">
                                <h6>Previously Submitted Files:</h6>
                                <div class="list-group">
                                    @foreach($existingSubmission->files as $file)
                                        <div class="list-group-item d-flex align-items-center">
                                            <div class="me-3">
                                                <i class="{{ $file->getFileIcon() }} text-{{ $file->getFileColor() }} fa-lg"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-medium">{{ $file->original_name }}</div>
                                                <small class="text-muted">{{ $file->getFormattedSize() }}</small>
                                            </div>
                                            <div>
                                                <a href="{{ $file->getDownloadUrl() }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="alert alert-warning mt-2">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Note:</strong> When you update your submission, all previously uploaded files will be replaced with the new files.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('student.assignments.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Cancel
                                </a>
                            </div>
                            <div>
                                @if($assignment->allow_resubmission || !$existingSubmission)
                                    @if($existingSubmission && $existingSubmission->canBeEdited())
                                        <button type="submit" class="btn btn-warning" id="submitBtn">
                                            <i class="fas fa-edit me-2"></i>Update Submission
                                        </button>
                                    @elseif(!$existingSubmission)
                                        <button type="submit" class="btn btn-primary" id="submitBtn">
                                            <i class="fas fa-paper-plane me-2"></i>Submit Assignment
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-secondary" disabled>
                                            <i class="fas fa-lock me-2"></i>Submission Locked
                                        </button>
                                    @endif
                                @else
                                    <button type="button" class="btn btn-secondary" disabled>
                                        <i class="fas fa-lock me-2"></i>Resubmission Not Allowed
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="mt-2">Submitting assignment...</div>
</div>

<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    color: white;
}

.file-upload-area {
    position: relative;
}

.upload-placeholder {
    cursor: pointer;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
}

.upload-placeholder:hover {
    background-color: #e9ecef;
    border-color: #007bff !important;
}

.upload-placeholder.dragover {
    background-color: #e3f2fd;
    border-color: #2196f3 !important;
}

.file-item {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-bottom: 0.5rem;
    background-color: #fff;
}

.file-item:hover {
    background-color: #f8f9fa;
}

.file-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    margin-right: 1rem;
    font-size: 1.25rem;
}

.file-info {
    flex: 1;
}

.file-name {
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.file-size {
    font-size: 0.875rem;
    color: #6c757d;
}

.file-remove {
    color: #dc3545;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    transition: background-color 0.2s;
}

.file-remove:hover {
    background-color: #f8d7da;
}

@media (max-width: 768px) {
    .file-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .file-icon {
        margin-right: 0;
        margin-bottom: 0.5rem;
    }
    
    .file-remove {
        align-self: flex-end;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    initializeFileUpload();
});

function initializeForm() {
    const form = document.getElementById('submissionForm');

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            submitForm();
        }
    });
}

function initializeFileUpload() {
    const fileInput = document.getElementById('files');
    const uploadArea = document.getElementById('fileUploadArea');
    const uploadPlaceholder = uploadArea.querySelector('.upload-placeholder');
    const filePreviewList = document.getElementById('filePreviewList');
    const fileList = document.getElementById('fileList');

    // Click to upload
    uploadPlaceholder.addEventListener('click', function() {
        fileInput.click();
    });

    // File selection
    fileInput.addEventListener('change', function() {
        handleFileSelection(this.files);
    });

    // Drag and drop
    uploadPlaceholder.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    uploadPlaceholder.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    uploadPlaceholder.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        handleFileSelection(files);
    });
}

function handleFileSelection(files) {
    const filePreviewList = document.getElementById('filePreviewList');
    const fileList = document.getElementById('fileList');
    const maxFiles = 5;
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed'
    ];

    // Check file count
    const currentFiles = fileList.children.length;
    if (currentFiles + files.length > maxFiles) {
        showToast('Error', `You can upload a maximum of ${maxFiles} files.`, 'error');
        return;
    }

    Array.from(files).forEach(file => {
        // Check file size
        if (file.size > maxSize) {
            showToast('Error', `File "${file.name}" exceeds the maximum size of 10MB.`, 'error');
            return;
        }

        // Check file type
        if (!allowedTypes.includes(file.type)) {
            showToast('Error', `File "${file.name}" is not a supported format.`, 'error');
            return;
        }

        // Add file to preview
        addFilePreview(file);
    });

    // Show/hide preview list
    filePreviewList.style.display = fileList.children.length > 0 ? 'block' : 'none';
}

function addFilePreview(file) {
    const fileList = document.getElementById('fileList');
    const fileId = Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    const fileItem = document.createElement('div');
    fileItem.className = 'file-item';
    fileItem.dataset.fileId = fileId;
    
    const fileIcon = getFileIcon(file.type);
    const fileSize = formatFileSize(file.size);
    
    fileItem.innerHTML = `
        <div class="file-icon bg-${fileIcon.color} text-white">
            <i class="${fileIcon.icon}"></i>
        </div>
        <div class="file-info">
            <div class="file-name">${file.name}</div>
            <div class="file-size">${fileSize}</div>
        </div>
        <div class="file-remove" onclick="removeFile('${fileId}')">
            <i class="fas fa-times"></i>
        </div>
    `;
    
    fileList.appendChild(fileItem);
    
    // Store file reference
    if (!window.uploadedFiles) {
        window.uploadedFiles = {};
    }
    window.uploadedFiles[fileId] = file;
}

function removeFile(fileId) {
    const fileList = document.getElementById('fileList');
    const fileItem = fileList.querySelector(`[data-file-id="${fileId}"]`);
    
    if (fileItem) {
        fileItem.remove();
        delete window.uploadedFiles[fileId];
        
        // Hide preview list if empty
        const filePreviewList = document.getElementById('filePreviewList');
        filePreviewList.style.display = fileList.children.length > 0 ? 'block' : 'none';
    }
}

function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) {
        return { icon: 'fas fa-image', color: 'info' };
    } else if (mimeType === 'application/pdf') {
        return { icon: 'fas fa-file-pdf', color: 'danger' };
    } else if (mimeType.includes('word') || mimeType.includes('document')) {
        return { icon: 'fas fa-file-word', color: 'primary' };
    } else if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) {
        return { icon: 'fas fa-file-excel', color: 'success' };
    } else if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) {
        return { icon: 'fas fa-file-powerpoint', color: 'warning' };
    } else if (mimeType.includes('zip') || mimeType.includes('rar') || mimeType.includes('7z')) {
        return { icon: 'fas fa-file-archive', color: 'dark' };
    } else {
        return { icon: 'fas fa-file', color: 'secondary' };
    }
}

function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toFixed(2) + ' GB';
    } else if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    } else {
        return bytes + ' bytes';
    }
}

function validateForm() {
    const content = document.getElementById('content').value;
    
    // Check if content is provided or files are uploaded
    if (!content.trim() && (!window.uploadedFiles || Object.keys(window.uploadedFiles).length === 0)) {
        showToast('Error', 'Please provide content or upload at least one file.', 'error');
        return false;
    }

    return true;
}

function submitForm() {
    const form = document.getElementById('submissionForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    
    // Add uploaded files
    if (window.uploadedFiles) {
        Object.values(window.uploadedFiles).forEach(file => {
            formData.append('files[]', file);
        });
    }

    showLoading();
    submitBtn.disabled = true;

    fetch(`/student/assignments/{{ $assignment->id }}/submit`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        submitBtn.disabled = false;

        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => {
                window.location.href = '/student/assignments';
            }, 2000);
        } else {
            showToast('Error', data.message || 'Failed to submit assignment.', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        submitBtn.disabled = false;
        console.error('Error submitting form:', error);
        showToast('Error', 'Failed to submit assignment. Please try again.', 'error');
    });
}

function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
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
