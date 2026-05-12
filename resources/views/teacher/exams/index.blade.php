@extends('layouts.app')

@section('title', 'Exams')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Exams</h1>
            <p class="text-muted mb-0">Manage your scheduled exams</p>
        </div>
        <div class="text-end">
            <a href="{{ route('teacher.exams.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Exam
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">Filter by Status</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Exams</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="typeFilter" class="form-label">Filter by Type</label>
                    <select class="form-select" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="midterm">Midterm</option>
                        <option value="final">Final</option>
                        <option value="quiz">Quiz</option>
                        <option value="assignment">Assignment</option>
                        <option value="practical">Practical</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="classFilter" class="form-label">Filter by Class</label>
                    <select class="form-select" id="classFilter">
                        <option value="">All Classes</option>
                        @foreach($exams->pluck('schoolClass.grade_level')->unique() as $grade)
                            <option value="{{ $grade }}">Grade {{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sortBy" class="form-label">Sort By</label>
                    <select class="form-select" id="sortBy">
                        <option value="exam_date">Exam Date</option>
                        <option value="title">Title</option>
                        <option value="type">Type</option>
                        <option value="status">Status</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Exams List -->
    <div class="row" id="examsContainer">
        @foreach($exams as $exam)
            <div class="col-lg-6 col-xl-4 mb-4" 
                 data-status="{{ $exam->status }}"
                 data-type="{{ $exam->exam_type }}"
                 data-class="{{ $exam->schoolClass->grade_level }}"
                 data-date="{{ $exam->exam_date->format('Y-m-d') }}">
                <div class="card h-100 exam-card">
                    <div class="card-body">
                        <!-- Exam Header -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <h6 class="card-title text-truncate mb-1">{{ $exam->title }}</h6>
                                <span class="badge bg-{{ $exam->getExamTypeColor() }}">{{ $exam->getExamTypeDisplay() }}</span>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $exam->getStatusColor() }}">{{ $exam->getStatusDisplay() }}</span>
                            </div>
                        </div>

                        <!-- Exam Details -->
                        <div class="exam-details mb-3">
                            <div class="row g-2 text-muted small">
                                <div class="col-6">
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    Grade {{ $exam->schoolClass->grade_level }} - {{ $exam->section }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-book me-1"></i>
                                    {{ $exam->subject->name }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $exam->getFormattedExamDate() }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $exam->getFormattedStartTime() }} - {{ $exam->getFormattedEndTime() }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-hourglass-half me-1"></i>
                                    {{ $exam->duration_minutes }} minutes
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-star me-1"></i>
                                    {{ $exam->total_marks }} marks
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        @if($exam->description)
                            <div class="mb-3">
                                <p class="card-text small text-muted mb-0">
                                    {{ Str::limit($exam->description, 80) }}
                                </p>
                            </div>
                        @endif

                        <!-- Results Statistics -->
                        <div class="results-stats mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="h5 mb-0 text-primary">{{ $exam->getTotalResults() }}</div>
                                        <small class="text-muted">Total</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="h5 mb-0 text-success">{{ $exam->getPassedResults() }}</div>
                                        <small class="text-muted">Passed</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <div class="h5 mb-0 text-info">{{ number_format($exam->getPassRate(), 1) }}%</div>
                                        <small class="text-muted">Pass Rate</small>
                                    </div>
                                </div>
                            </div>
                            
                            @if($exam->getTotalResults() > 0)
                                <div class="progress mt-2" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: {{ $exam->getPassRate() }}%"></div>
                                </div>
                            @endif
                        </div>

                        <!-- Time Remaining -->
                        <div class="time-remaining mb-3">
                            <div class="alert alert-{{ $exam->isOngoing() ? 'success' : ($exam->isUpcoming() ? 'primary' : ($exam->isCompleted() ? 'info' : 'danger')) }} py-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock me-2"></i>
                                    <div class="flex-grow-1">
                                        <strong>{{ $exam->getTimeRemaining() }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button class="btn btn-outline-primary btn-sm" onclick="showExamDetails({{ $exam->id }})">
                                        <i class="fas fa-eye me-1"></i>View
                                    </button>
                                </div>
                                <div>
                                    @if($exam->status === 'scheduled')
                                        <button class="btn btn-success btn-sm" onclick="startExam({{ $exam->id }})">
                                            <i class="fas fa-play me-1"></i>Start
                                        </button>
                                    @elseif($exam->status === 'ongoing')
                                        <button class="btn btn-warning btn-sm" onclick="endExam({{ $exam->id }})">
                                            <i class="fas fa-stop me-1"></i>End
                                        </button>
                                    @endif
                                    
                                    <button class="btn btn-outline-info btn-sm" onclick="viewResults({{ $exam->id }})">
                                        <i class="fas fa-chart-bar me-1"></i>Results
                                    </button>
                                    
                                    @if($exam->status === 'scheduled')
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-warning" onclick="editExam({{ $exam->id }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteExam({{ $exam->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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
    @if($exams->count() === 0)
        <div class="text-center py-5">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Exams Scheduled</h5>
            <p class="text-muted">You haven't scheduled any exams yet.</p>
            <a href="{{ route('teacher.exams.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Your First Exam
            </a>
        </div>
    @endif
</div>

<!-- Exam Details Modal -->
<div class="modal fade" id="examDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Exam Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="examDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Results Modal -->
<div class="modal fade" id="resultsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Exam Results</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="exportResultsBtn" style="display: none;">
                    <i class="fas fa-download me-2"></i>Export Results
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.exam-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
}

.exam-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.exam-details {
    border-top: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.results-stats {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.75rem;
}

.stat-item {
    padding: 0.25rem 0;
}

.time-remaining {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.75rem;
}

.action-buttons {
    border-top: 1px solid #dee2e6;
    padding-top: 0.75rem;
}

@media (max-width: 768px) {
    .exam-card {
        margin-bottom: 1rem;
    }
    
    .action-buttons .d-flex {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
    
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
    const typeFilter = document.getElementById('typeFilter');
    const classFilter = document.getElementById('classFilter');
    const sortBy = document.getElementById('sortBy');
    
    [statusFilter, typeFilter, classFilter, sortBy].forEach(filter => {
        filter.addEventListener('change', function() {
            filterExams();
        });
    });
}

function filterExams() {
    const statusFilter = document.getElementById('statusFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    const classFilter = document.getElementById('classFilter').value;
    const sortBy = document.getElementById('sortBy').value;
    
    const cards = document.querySelectorAll('.exam-card').forEach(card => {
        const parentDiv = card.parentElement;
        const status = parentDiv.dataset.status;
        const type = parentDiv.dataset.type;
        const grade = parentDiv.dataset.class;
        
        let show = true;
        
        // Filter by status
        if (statusFilter && status !== statusFilter) {
            show = false;
        }
        
        // Filter by type
        if (typeFilter && type !== typeFilter) {
            show = false;
        }
        
        // Filter by class
        if (classFilter && grade !== classFilter) {
            show = false;
        }
        
        parentDiv.style.display = show ? '' : 'none';
    });
    
    // Sort visible cards
    if (sortBy) {
        sortExams(sortBy);
    }
}

function sortExams(sortBy) {
    const container = document.getElementById('examsContainer');
    const cards = Array.from(container.querySelectorAll('.exam-card'));
    
    cards.sort((a, b) => {
        const parentA = a.parentElement;
        const parentB = b.parentElement;
        
        switch(sortBy) {
            case 'exam_date':
                return new Date(parentA.dataset.date) - new Date(parentB.dataset.date);
            case 'title':
                return parentA.querySelector('.card-title').textContent.localeCompare(
                    parentB.querySelector('.card-title').textContent
                );
            case 'type':
                return parentA.dataset.type.localeCompare(parentB.dataset.type);
            case 'status':
                return parentA.dataset.status.localeCompare(parentB.dataset.status);
            default:
                return 0;
        }
    });
    
    cards.forEach(card => container.appendChild(card));
}

function showExamDetails(examId) {
    const modal = new bootstrap.Modal(document.getElementById('examDetailsModal'));
    const content = document.getElementById('examDetailsContent');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Fetch exam details (you could implement AJAX here)
    setTimeout(() => {
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-info-circle fa-3x text-muted mb-3"></i>
                <h6>Exam Details</h6>
                <p class="text-muted">Exam ID: ${examId}</p>
                <p class="text-muted">Detailed view would be loaded here via AJAX.</p>
            </div>
        `;
    }, 500);
    
    modal.show();
}

function viewResults(examId) {
    const modal = new bootstrap.Modal(document.getElementById('resultsModal'));
    const content = document.getElementById('resultsContent');
    const exportBtn = document.getElementById('exportResultsBtn');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Fetch results (you could implement AJAX here)
    setTimeout(() => {
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                <h6>Exam Results</h6>
                <p class="text-muted">Exam ID: ${examId}</p>
                <p class="text-muted">Results would be loaded here via AJAX.</p>
            </div>
        `;
        exportBtn.style.display = 'inline-block';
        exportBtn.onclick = function() {
            exportResults(examId);
        };
    }, 500);
    
    modal.show();
}

function startExam(examId) {
    if (!confirm('Are you sure you want to start this exam?')) {
        return;
    }
    
    fetch(`/teacher/exams/${examId}/start`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to start exam. Please try again.', 'error');
    });
}

function endExam(examId) {
    if (!confirm('Are you sure you want to end this exam?')) {
        return;
    }
    
    fetch(`/teacher/exams/${examId}/end`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to end exam. Please try again.', 'error');
    });
}

function editExam(examId) {
    window.location.href = `/teacher/exams/${examId}/edit`;
}

function deleteExam(examId) {
    if (!confirm('Are you sure you want to delete this exam? This action cannot be undone.')) {
        return;
    }
    
    fetch(`/teacher/exams/${examId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', data.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to delete exam. Please try again.', 'error');
    });
}

function exportResults(examId) {
    window.location.href = `/teacher/exams/${examId}/export`;
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
