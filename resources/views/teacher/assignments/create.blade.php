@extends('layouts.app')

@section('title', 'Create Assignment')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Create Assignment</h1>
            <p class="text-muted mb-0">Create and assign homework to your students</p>
        </div>
        <div class="text-end">
            <a href="{{ route('teacher.assignments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Assignments
            </a>
        </div>
    </div>

    <!-- Assignment Form -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-file-alt me-2"></i>Assignment Details
            </h5>
        </div>
        <div class="card-body">
            <form id="assignmentForm" enctype="multipart/form-data">
                @csrf
                
                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="title" class="form-label">Assignment Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required
                               placeholder="Enter assignment title..." maxlength="255">
                        <div class="invalid-feedback">Please provide an assignment title.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="total_marks" class="form-label">Total Marks <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="total_marks" name="total_marks" required
                               min="0" max="1000" value="100">
                        <div class="form-text">Maximum marks for this assignment</div>
                        <div class="invalid-feedback">Please provide total marks.</div>
                    </div>
                </div>

                <!-- Class and Subject Selection -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                        <select class="form-select" id="class_id" name="class_id" required>
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">
                                    Grade {{ $class->grade_level }} - {{ $class->section }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Please select a class.</div>
                    </div>
                    <div class="col-md-4">
                        <label for="section" class="form-label">Section <span class="text-danger">*</span></label>
                        <select class="form-select" id="section" name="section" required disabled>
                            <option value="">Select Section</option>
                        </select>
                        <div class="invalid-feedback">Please select a section.</div>
                    </div>
                    <div class="col-md-4">
                        <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                        <select class="form-select" id="subject_id" name="subject_id" required disabled>
                            <option value="">Select Subject</option>
                        </select>
                        <div class="invalid-feedback">Please select a subject.</div>
                    </div>
                </div>

                <!-- Due Date and Time -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="due_date" class="form-label">Due Date & Time <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="due_date" name="due_date" required>
                        <div class="form-text">Assignment must be submitted before this date and time</div>
                        <div class="invalid-feedback">Please provide a due date.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assignment Options</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allow_resubmission" name="allow_resubmission">
                            <label class="form-check-label" for="allow_resubmission">
                                <i class="fas fa-redo me-2"></i>Allow Resubmission
                                <small class="text-muted">(Students can resubmit their work)</small>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                                  placeholder="Enter assignment instructions or description..." maxlength="1000"></textarea>
                        <div class="form-text">Optional: Detailed instructions for students (max 1000 characters)</div>
                    </div>
                </div>

                <!-- File Upload -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label class="form-label">
                            <i class="fas fa-paperclip me-2"></i>Assignment Files
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
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                    <i class="fas fa-redo me-2"></i>Reset Form
                                </button>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Create Assignment
                                </button>
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
    <div class="mt-2">Creating assignment...</div>
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

.form-control.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: none;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

.form-control.is-invalid ~ .invalid-feedback {
    display: block;
}

.form-select.is-invalid {
    border-color: #dc3545;
}

.form-select.is-invalid ~ .invalid-feedback {
    display: block;
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
    setMinDateTime();
});

function initializeForm() {
    const form = document.getElementById('assignmentForm');
    const classSelect = document.getElementById('class_id');
    const sectionSelect = document.getElementById('section');
    const subjectSelect = document.getElementById('subject_id');

    // Handle class selection
    classSelect.addEventListener('change', function() {
        const classId = this.value;
        
        // Reset dependent fields
        sectionSelect.innerHTML = '<option value="">Select Section</option>';
        sectionSelect.disabled = true;
        subjectSelect.innerHTML = '<option value="">Select Subject</option>';
        subjectSelect.disabled = true;
        
        if (classId) {
            loadSections(classId);
            loadSubjects(classId);
        }
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            submitForm();
        }
    });
}

function setMinDateTime() {
    const dueDateInput = document.getElementById('due_date');
    const now = new Date();
    const offset = now.getTimezoneOffset();
    now.setMinutes(now.getMinutes() - offset);
    
    dueDateInput.min = now.toISOString().slice(0, 16);
}

function loadSections(classId) {
    fetch(`/teacher/assignments/sections/${classId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const sectionSelect = document.getElementById('section');
                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                
                data.sections.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section;
                    option.textContent = section;
                    sectionSelect.appendChild(option);
                });
                
                sectionSelect.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error loading sections:', error);
            showToast('Error', 'Failed to load sections', 'error');
        });
}

function loadSubjects(classId) {
    fetch(`/teacher/assignments/subjects/${classId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const subjectSelect = document.getElementById('subject_id');
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                
                data.subjects.forEach(subject => {
                    const option = document.createElement('option');
                    option.value = subject.id;
                    option.textContent = subject.name;
                    subjectSelect.appendChild(option);
                });
                
                subjectSelect.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error loading subjects:', error);
            showToast('Error', 'Failed to load subjects', 'error');
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
    const form = document.getElementById('assignmentForm');
    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    // Validate due date is in future
    const dueDateInput = document.getElementById('due_date');
    if (dueDateInput.value) {
        const dueDate = new Date(dueDateInput.value);
        const now = new Date();
        
        if (dueDate <= now) {
            dueDateInput.classList.add('is-invalid');
            isValid = false;
            showToast('Error', 'Due date must be in the future.', 'error');
        } else {
            dueDateInput.classList.remove('is-invalid');
        }
    }

    if (!isValid) {
        showToast('Error', 'Please fill in all required fields.', 'error');
    }

    return isValid;
}

function submitForm() {
    const form = document.getElementById('assignmentForm');
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

    fetch('/teacher/assignments', {
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
                window.location.href = '/teacher/assignments';
            }, 2000);
        } else {
            showToast('Error', data.message || 'Failed to create assignment.', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        submitBtn.disabled = false;
        console.error('Error submitting form:', error);
        showToast('Error', 'Failed to create assignment. Please try again.', 'error');
    });
}

function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.getElementById('assignmentForm').reset();
        document.getElementById('section').disabled = true;
        document.getElementById('subject_id').disabled = true;
        document.getElementById('fileList').innerHTML = '';
        document.getElementById('filePreviewList').style.display = 'none';
        window.uploadedFiles = {};
        
        // Remove validation classes
        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
        
        // Reset min datetime
        setMinDateTime();
    }
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
