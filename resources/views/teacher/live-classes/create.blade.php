@extends('layouts.app')

@section('title', 'Schedule Live Class')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Schedule Live Class</h1>
            <p class="text-muted mb-0">Create and schedule online live classes for your students</p>
        </div>
        <div class="text-end">
            <a href="{{ route('teacher.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Live Class Form -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-video me-2"></i>Live Class Details
            </h5>
        </div>
        <div class="card-body">
            <form id="liveClassForm">
                @csrf
                
                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="title" class="form-label">Class Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required
                               placeholder="Enter live class title..." maxlength="255">
                        <div class="invalid-feedback">Please provide a class title.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="meeting_link" class="form-label">Meeting Link <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-link"></i>
                            </span>
                            <input type="url" class="form-control" id="meeting_link" name="meeting_link" required
                                   placeholder="https://zoom.us/meeting/..." 
                                   pattern="https://(www\.)?(zoom\.us|meet\.google\.com|teams\.microsoft\.com)/.*">
                        </div>
                        <div class="form-text">Supported platforms: Zoom, Google Meet, Microsoft Teams</div>
                        <div class="invalid-feedback">Please provide a valid meeting link from supported platforms.</div>
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

                <!-- Date and Time -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" required
                               min="{{ now()->format('Y-m-d') }}">
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>
                    <div class="col-md-4">
                        <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="start_time" name="start_time" required>
                        <div class="invalid-feedback">Please select a start time.</div>
                    </div>
                    <div class="col-md-4">
                        <label for="duration" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="duration" name="duration" required
                               min="1" max="480" value="60">
                        <div class="form-text">Duration between 1 and 480 minutes</div>
                        <div class="invalid-feedback">Please provide a valid duration.</div>
                    </div>
                </div>

                <!-- Description -->
                <div class="row mb-4">
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                                  placeholder="Enter class description or agenda..." maxlength="1000"></textarea>
                        <div class="form-text">Optional: Brief description of the live class (max 1000 characters)</div>
                    </div>
                </div>

                <!-- Clash Warning -->
                <div id="clashWarning" class="alert alert-warning d-none" role="alert">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle me-2"></i>Time Clash Detected
                    </h6>
                    <p class="mb-2">The selected time conflicts with existing classes:</p>
                    <ul id="clashList" class="mb-0"></ul>
                </div>

                <!-- Notification Option -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="notify_students" 
                                   name="notify_students" checked>
                            <label class="form-check-label" for="notify_students">
                                <i class="fas fa-bell me-2"></i>Notify students about this live class
                                <small class="text-muted">(Students will receive notification immediately)</small>
                            </label>
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
                                    <i class="fas fa-calendar-plus me-2"></i>Schedule Live Class
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
    <div class="mt-2">Scheduling live class...</div>
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

.input-group .form-control.is-invalid {
    border-color: #dc3545;
}

.input-group .form-control.is-invalid ~ .invalid-feedback {
    display: block;
}

.alert {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.35rem;
}

.alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

.alert-heading {
    color: inherit;
}

@media (max-width: 768px) {
    .btn-block {
        display: block;
        width: 100%;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
    initializeDateTimePickers();
});

function initializeForm() {
    const form = document.getElementById('liveClassForm');
    const classSelect = document.getElementById('class_id');
    const sectionSelect = document.getElementById('section');
    const subjectSelect = document.getElementById('subject_id');
    const dateInput = document.getElementById('date');
    const startTimeInput = document.getElementById('start_time');
    const durationInput = document.getElementById('duration');

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

    // Handle date/time changes to check for clashes
    [dateInput, startTimeInput, durationInput].forEach(input => {
        input.addEventListener('change', checkForClash);
    });

    // Handle form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateForm()) {
            submitForm();
        }
    });
}

function initializeDateTimePickers() {
    const dateInput = document.getElementById('date');
    const startTimeInput = document.getElementById('start_time');
    
    // Set minimum date to today
    dateInput.min = new Date().toISOString().split('T')[0];
    
    // Set default time to current time + 1 hour
    const now = new Date();
    now.setHours(now.getHours() + 1);
    startTimeInput.value = now.toTimeString().slice(0, 5);
}

function loadSections(classId) {
    fetch(`/teacher/live-classes/sections/${classId}`)
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
    fetch(`/teacher/live-classes/subjects/${classId}`)
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

function checkForClash() {
    const classId = document.getElementById('class_id').value;
    const section = document.getElementById('section').value;
    const date = document.getElementById('date').value;
    const startTime = document.getElementById('start_time').value;
    const duration = document.getElementById('duration').value;

    if (!classId || !section || !date || !startTime || !duration) {
        hideClashWarning();
        return;
    }

    const formData = new FormData();
    formData.append('class_id', classId);
    formData.append('section', section);
    formData.append('date', date);
    formData.append('start_time', startTime);
    formData.append('duration', duration);

    fetch('/teacher/live-classes/check-clash', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.has_clash) {
            showClashWarning(data.clashes);
        } else {
            hideClashWarning();
        }
    })
    .catch(error => {
        console.error('Error checking clash:', error);
        hideClashWarning();
    });
}

function showClashWarning(clashes) {
    const clashWarning = document.getElementById('clashWarning');
    const clashList = document.getElementById('clashList');
    
    clashList.innerHTML = '';
    clashes.forEach(clash => {
        const li = document.createElement('li');
        li.textContent = `${clash.title} (${clash.subject} - ${clash.start_time} to ${clash.end_time})`;
        clashList.appendChild(li);
    });
    
    clashWarning.classList.remove('d-none');
}

function hideClashWarning() {
    const clashWarning = document.getElementById('clashWarning');
    clashWarning.classList.add('d-none');
}

function validateForm() {
    const form = document.getElementById('liveClassForm');
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

    if (!isValid) {
        showToast('Error', 'Please fill in all required fields.', 'error');
    }

    return isValid;
}

function submitForm() {
    const form = document.getElementById('liveClassForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    
    showLoading();
    submitBtn.disabled = true;

    fetch('/teacher/live-classes', {
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
                window.location.href = '/teacher/dashboard';
            }, 2000);
        } else {
            if (data.clashes) {
                showClashWarning(data.clashes);
            }
            showToast('Error', data.message || 'Failed to schedule live class.', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        submitBtn.disabled = false;
        console.error('Error submitting form:', error);
        showToast('Error', 'Failed to schedule live class. Please try again.', 'error');
    });
}

function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.getElementById('liveClassForm').reset();
        document.getElementById('section').disabled = true;
        document.getElementById('subject_id').disabled = true;
        hideClashWarning();
        
        // Remove validation classes
        document.querySelectorAll('.is-invalid').forEach(element => {
            element.classList.remove('is-invalid');
        });
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
