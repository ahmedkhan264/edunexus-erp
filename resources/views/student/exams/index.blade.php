@extends('layouts.app')

@section('title', 'Exams')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">My Exams</h1>
            <p class="text-muted mb-0">View your upcoming and completed exams</p>
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
                    <h4 class="mb-0">{{ $statistics['total_exams'] }}</h4>
                    <small>Total Exams</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $statistics['passed_exams'] }}</h4>
                    <small>Passed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $statistics['failed_exams'] }}</h4>
                    <small>Failed</small>
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

    <!-- Grade Distribution -->
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
                                        <span class="badge bg-{{ $grade === 'Absent' ? 'secondary' : ($grade[0] === 'A' ? 'success' : ($grade[0] === 'B' ? 'info' : ($grade[0] === 'C' ? 'warning' : ($grade[0] === 'D' ? 'danger' : 'dark'))) }}">
                                            {{ $grade }}
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $grade === 'Absent' ? 'secondary' : ($grade[0] === 'A' ? 'success' : ($grade[0] === 'B' ? 'info' : ($grade[0] === 'C' ? 'warning' : ($grade[0] === 'D' ? 'danger' : 'dark'))) }}" 
                                                 style="width: {{ ($count / $statistics['total_exams']) * 100 }}%">
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
                <div class="card-header bg-info text-white">
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
                                <span>Pass Rate:</span>
                                <strong>{{ $statistics['total_exams'] > 0 ? number_format(($statistics['passed_exams'] / $statistics['total_exams']) * 100, 1) : 0 }}%</strong>
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
                <div class="card-header bg-success text-white">
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
                                            <small>Exams:</small>
                                            <strong>{{ $performance['total'] }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small>Average:</small>
                                            <strong>{{ $performance['average_score'] ? number_format($performance['average_score'], 1) : 'N/A' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <small>Pass Rate:</small>
                                            <strong>{{ $performance['passed'] > 0 ? number_format(($performance['passed'] / $performance['total']) * 100, 1) : 0 }}%</strong>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-success" style="width: {{ $performance['total'] > 0 ? ($performance['passed'] / $performance['total']) * 100 : 0 }}%"></div>
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

    <!-- Exams List -->
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>Exam History
                </h6>
                <div>
                    <select class="form-select form-select-sm" id="filterSelect">
                        <option value="">All Exams</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="completed">Completed</option>
                        <option value="passed">Passed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="examsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Exam</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                            @php
                                $result = $exam->examResults->first();
                                $status = $result ? ($result->isAbsent() ? 'absent' : ($result->isPassed() ? 'passed' : 'failed')) : 'upcoming';
                            @endphp
                            <tr class="exam-row" 
                                data-status="{{ $status }}"
                                data-type="{{ $exam->exam_type }}"
                                data-date="{{ $exam->exam_date->format('Y-m-d') }}">
                                <td>
                                    <div class="fw-medium">{{ $exam->title }}</div>
                                    <small class="text-muted">Grade {{ $exam->schoolClass->grade_level }} - {{ $exam->section }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $exam->subject->name }}</span>
                                </td>
                                <td>
                                    <div>{{ $exam->getFormattedExamDate() }}</div>
                                    <small class="text-muted">{{ $exam->getFormattedStartTime() }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $exam->getExamTypeColor() }}">{{ $exam->getExamTypeDisplay() }}</span>
                                </td>
                                <td>
                                    @if($result && !$result->isAbsent())
                                        <div class="marks-display">
                                            <span class="badge bg-{{ $result->getGradeColor() }}">
                                                {{ $result->getMarksDisplay() }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-muted">{{ $result ? 'Absent' : 'Not Taken' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($result && !$result->isAbsent())
                                        <span class="badge bg-{{ $result->getGradeColor() }}">
                                            {{ $result->grade }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">{{ $result ? 'Absent' : 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($status === 'upcoming')
                                        <span class="badge bg-primary">Upcoming</span>
                                    @elseif($status === 'absent')
                                        <span class="badge bg-warning">Absent</span>
                                    @elseif($status === 'passed')
                                        <span class="badge bg-success">Passed</span>
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" onclick="viewDetails({{ $exam->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($result && !$result->isAbsent())
                                            <button class="btn btn-outline-info" onclick="viewResult({{ $result->id }})">
                                                <i class="fas fa-chart-bar"></i>
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
            @if($exams->count() === 0)
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Exams</h5>
                    <p class="text-muted">You don't have any exams scheduled yet.</p>
                </div>
            @endif
        </div>
    </div>
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

<!-- Result Details Modal -->
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Exam Result</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultContent">
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

.exam-row:hover {
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
        filterExams();
    });
}

function filterExams() {
    const filterValue = document.getElementById('filterSelect').value;
    const rows = document.querySelectorAll('.exam-row');
    
    rows.forEach(row => {
        const status = row.dataset.status;
        
        let show = true;
        
        if (filterValue === 'upcoming' && status !== 'upcoming') {
            show = false;
        } else if (filterValue === 'completed' && status === 'upcoming') {
            show = false;
        } else if (filterValue === 'passed' && status !== 'passed') {
            show = false;
        } else if (filterValue === 'failed' && status !== 'failed') {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
    });
}

function viewDetails(examId) {
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

function viewResult(resultId) {
    const modal = new bootstrap.Modal(document.getElementById('resultModal'));
    const content = document.getElementById('resultContent');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Fetch result details (you could implement AJAX here)
    setTimeout(() => {
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                <h6>Exam Result</h6>
                <p class="text-muted">Result ID: ${resultId}</p>
                <p class="text-muted">Detailed result would be loaded here via AJAX.</p>
            </div>
        `;
    }, 500);
    
    modal.show();
}

function exportResults() {
    fetch('/student/exams/export', {
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
