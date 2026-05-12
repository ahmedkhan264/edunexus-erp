@extends('layouts.app')

@section('title', 'Create Exam')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Create Exam</h1>
            <p class="text-muted mb-0">Schedule a new exam for your students</p>
        </div>
        <div class="text-end">
            <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Exams
            </a>
        </div>
    </div>

    <!-- Exam Creation Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Exam Details
                    </h5>
                </div>
                <div class="card-body">
                    <form id="examForm">
                        @csrf
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Exam Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                    <div class="form-text">Enter a descriptive title for the exam</div>
                                </div>

                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                                    <select class="form-select" id="class_id" name="class_id" required>
                                        <option value="">Select Class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">Grade {{ $class->grade_level }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="section" class="form-label">Section <span class="text-danger">*</span></label>
                                    <select class="form-select" id="section" name="section" required>
                                        <option value="">Select Class First</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                                    <select class="form-select" id="subject_id" name="subject_id" required>
                                        <option value="">Select Subject</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="exam_type" class="form-label">Exam Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="exam_type" name="exam_type" required>
                                        <option value="">Select Exam Type</option>
                                        <option value="midterm">Midterm Exam</option>
                                        <option value="final">Final Exam</option>
                                        <option value="quiz">Quiz</option>
                                        <option value="assignment">Assignment</option>
                                        <option value="practical">Practical Exam</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="exam_date" class="form-label">Exam Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="exam_date" name="exam_date" required>
                                </div>

                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                                </div>

                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" id="end_time" name="end_time" required>
                                </div>

                                <div class="mb-3">
                                    <label for="duration_minutes" class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="duration_minutes" name="duration_minutes" min="15" max="480" required>
                                    <div class="form-text">Between 15 and 480 minutes</div>
                                </div>

                                <div class="mb-3">
                                    <label for="total_marks" class="form-label">Total Marks <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="total_marks" name="total_marks" min="1" max="1000" required>
                                </div>
                            </div>
                        </div>

                        <!-- Second Row -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="passing_marks" class="form-label">Passing Marks <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="passing_marks" name="passing_marks" min="0" required>
                                    <div class="form-text">Minimum marks to pass</div>
                                </div>

                                <div class="mb-3">
                                    <label for="max_attempts" class="form-label">Maximum Attempts <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="max_attempts" name="max_attempts" min="1" max="5" value="1" required>
                                    <div class="form-text">Number of attempts allowed</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="allow_retake" name="allow_retake">
                                        <label class="form-check-label" for="allow_retake">
                                            Allow Retake
                                        </label>
                                        <div class="form-text">Allow students to retake the exam</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" maxlength="1000" placeholder="Enter exam description (optional)"></textarea>
                            <div class="form-text">
                                <span id="descCharCount">0</span>/1000 characters
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="mb-3">
                            <label for="instructions" class="form-label">Exam Instructions</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="4" maxlength="2000" placeholder="Enter exam instructions for students (optional)"></textarea>
                            <div class="form-text">
                                <span id="instCharCount">0</span>/2000 characters
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('teacher.exams.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Create Exam
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Sidebar - Tips & Guidelines -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Exam Guidelines
                    </h6>
                </div>
                <div class="card-body">
                    <h6>Best Practices</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Schedule exams at least 7 days in advance
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Allow sufficient time based on exam complexity
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Set appropriate passing marks (typically 40-50%)
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Provide clear instructions for students
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Consider allowing retakes for practice exams
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Important Notes
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-clock text-warning me-2"></i>
                            Exams can only be edited before they start
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-lock text-warning me-2"></i>
                            Completed exams cannot be deleted
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-users text-warning me-2"></i>
                            Only students from the selected class can take the exam
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-calendar text-warning me-2"></i>
                            Exam date must be in the future
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Quick Tips
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Duration Guidelines</h6>
                        <div class="small">
                            <div>Quiz: 15-30 minutes</div>
                            <div>Assignment: 60-120 minutes</div>
                            <div>Midterm: 90-180 minutes</div>
                            <div>Final: 120-240 minutes</div>
                            <div>Practical: 60-180 minutes</div>
                        </div>
                    </div>
                    <div>
                        <h6>Marking Guidelines</h6>
                        <div class="small">
                            <div>Standard passing: 40-50%</div>
                            <div>Good performance: 70%+</div>
                            <div>Excellent: 85%+</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.form-label {
    font-weight: 500;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }
    
    .d-flex.justify-content-between > div {
        text-align: center;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
});

function initializeForm() {
    const form = document.getElementById('examForm');
    const classSelect = document.getElementById('class_id');
    const sectionSelect = document.getElementById('section');
    const subjectSelect = document.getElementById('subject_id');
    const examDate = document.getElementById('exam_date');
    const startTime = document.getElementById('start_time');
    const endTime = document.getElementById('end_time');
    const durationInput = document.getElementById('duration_minutes');
    const totalMarks = document.getElementById('total_marks');
    const passingMarks = document.getElementById('passing_marks');
    const description = document.getElementById('description');
    const instructions = document.getElementById('instructions');
    const descCharCount = document.getElementById('descCharCount');
    const instCharCount = document.getElementById('instCharCount');

    // Set minimum date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    examDate.min = tomorrow.toISOString().split('T')[0];

    // Handle class selection
    classSelect.addEventListener('change', function() {
        loadSections(this.value);
        loadSubjects(this.value);
    });

    // Handle time changes to auto-calculate duration
    startTime.addEventListener('change', calculateDuration);
    endTime.addEventListener('change', calculateDuration);

    // Handle duration change to auto-calculate end time
    durationInput.addEventListener('change', calculateEndTime);

    // Handle total marks change to update passing marks max
    totalMarks.addEventListener('input', function() {
        passingMarks.max = this.value;
        if (passingMarks.value > this.value) {
            passingMarks.value = Math.floor(this.value * 0.4); // Default to 40%
        }
    });

    // Character counters
    description.addEventListener('input', function() {
        descCharCount.textContent = this.value.length;
    });

    instructions.addEventListener('input', function() {
        instCharCount.textContent = this.value.length;
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitExam();
    });
}

function loadSections(classId) {
    const sectionSelect = document.getElementById('section');
    
    if (!classId) {
        sectionSelect.innerHTML = '<option value="">Select Class First</option>';
        return;
    }

    // Mock sections - in real app, this would come from API
    const sections = ['A', 'B', 'C', 'D'];
    
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    sections.forEach(section => {
        sectionSelect.innerHTML += `<option value="${section}">Section ${section}</option>`;
    });
}

function loadSubjects(classId) {
    const subjectSelect = document.getElementById('subject_id');
    
    if (!classId) {
        return;
    }

    // In real app, this would filter subjects based on class
    // For now, we'll keep all subjects available
}

function calculateDuration() {
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const durationInput = document.getElementById('duration_minutes');

    if (startTime && endTime) {
        const start = new Date(`2000-01-01T${startTime}`);
        const end = new Date(`2000-01-01T${endTime}`);
        
        if (end > start) {
            const duration = Math.round((end - start) / (1000 * 60));
            durationInput.value = duration;
        }
    }
}

function calculateEndTime() {
    const startTime = document.getElementById('start_time').value;
    const duration = document.getElementById('duration_minutes').value;
    const endTime = document.getElementById('end_time');

    if (startTime && duration) {
        const start = new Date(`2000-01-01T${startTime}`);
        const end = new Date(start.getTime() + (duration * 60 * 1000));
        
        const hours = end.getHours().toString().padStart(2, '0');
        const minutes = end.getMinutes().toString().padStart(2, '0');
        
        endTime.value = `${hours}:${minutes}`;
    }
}

function validateForm() {
    const title = document.getElementById('title').value.trim();
    const classId = document.getElementById('class_id').value;
    const section = document.getElementById('section').value;
    const subjectId = document.getElementById('subject_id').value;
    const examDate = document.getElementById('exam_date').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const duration = document.getElementById('duration_minutes').value;
    const totalMarks = document.getElementById('total_marks').value;
    const passingMarks = document.getElementById('passing_marks').value;

    // Basic validation
    if (!title) {
        showToast('Error', 'Please enter exam title', 'error');
        return false;
    }

    if (!classId) {
        showToast('Error', 'Please select a class', 'error');
        return false;
    }

    if (!section) {
        showToast('Error', 'Please select a section', 'error');
        return false;
    }

    if (!subjectId) {
        showToast('Error', 'Please select a subject', 'error');
        return false;
    }

    if (!examDate) {
        showToast('Error', 'Please select exam date', 'error');
        return false;
    }

    if (!startTime || !endTime) {
        showToast('Error', 'Please select start and end times', 'error');
        return false;
    }

    if (startTime >= endTime) {
        showToast('Error', 'End time must be after start time', 'error');
        return false;
    }

    if (!duration || duration < 15 || duration > 480) {
        showToast('Error', 'Duration must be between 15 and 480 minutes', 'error');
        return false;
    }

    if (!totalMarks || totalMarks < 1 || totalMarks > 1000) {
        showToast('Error', 'Total marks must be between 1 and 1000', 'error');
        return false;
    }

    if (!passingMarks || passingMarks < 0 || passingMarks > totalMarks) {
        showToast('Error', 'Passing marks must be between 0 and total marks', 'error');
        return false;
    }

    return true;
}

function submitExam() {
    if (!validateForm()) {
        return;
    }

    const formData = new FormData(document.getElementById('examForm'));
    const submitBtn = document.getElementById('submitBtn');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';

    fetch('/teacher/exams', {
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
                window.location.href = '/teacher/exams';
            }, 1500);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to create exam. Please try again.', 'error');
    })
    .finally(() => {
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Create Exam';
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
