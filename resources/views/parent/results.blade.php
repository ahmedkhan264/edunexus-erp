@extends('layouts.app')

@section('title', 'Results - ' . $student->user->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Results - {{ $student->user->name }}</h1>
            <p class="text-muted mb-0">Grade {{ $student->schoolClass->grade_level }} - {{ $student->schoolClass->section }}</p>
        </div>
        <div class="text-end">
            <a href="{{ route('parent.dashboard', ['child_id' => $student->id]) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <button class="btn btn-outline-success" onclick="exportResults()">
                <i class="fas fa-download me-2"></i>Export Results
            </button>
        </div>
    </div>

    <!-- Exam Selector -->
    @if(!empty($examList))
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label for="examSelector" class="form-label">Select Exam:</label>
                    </div>
                    <div class="col-md-9">
                        <select class="form-select" id="examSelector" onchange="switchExam(this.value)">
                            @foreach($examList as $examId => $exam)
                                <option value="{{ $examId }}" {{ $selectedExam && $selectedExam['exam']->id == $examId ? 'selected' : '' }}>
                                    {{ $exam['title'] }} - {{ $exam['date'] }} ({{ $exam['grade'] }} - {{ $exam['percentage'] }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($selectedExam)
        <!-- Selected Exam Result -->
        <div class="card mb-4">
            <div class="card-header bg-{{ $selectedExam['grade_color'] }} text-white">
                <h6 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>{{ $selectedExam['exam']->title }}
                </h6>
            </div>
            <div class="card-body">
                <!-- Exam Details -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="exam-details">
                            <div class="detail-item mb-2">
                                <label class="text-muted small">Date:</label>
                                <div class="fw-medium">{{ $selectedExam['formatted_date'] }}</div>
                            </div>
                            <div class="detail-item mb-2">
                                <label class="text-muted small">Time:</label>
                                <div class="fw-medium">{{ $selectedExam['exam']->getFormattedStartTime() }} - {{ $selectedExam['exam']->getFormattedEndTime() }}</div>
                            </div>
                            <div class="detail-item mb-2">
                                <label class="text-muted small">Duration:</label>
                                <div class="fw-medium">{{ $selectedExam['exam']->duration_minutes }} minutes</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="result-summary text-center">
                            <div class="display-4 text-{{ $selectedExam['grade_color'] }} mb-2">{{ $selectedExam['grade'] }}</div>
                            <div class="h5 mb-1">{{ number_format($selectedExam['percentage'], 1) }}%</div>
                            <div class="text-muted">{{ $selectedExam['total_obtained'] }} / {{ $selectedExam['total_max'] }} marks</div>
                            <div class="mt-2">
                                <span class="badge bg-{{ $selectedExam['status'] === 'pass' ? 'success' : 'danger' }}">
                                    {{ ucfirst($selectedExam['status']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subject-wise Results -->
                <div class="subject-results">
                    <h6 class="text-muted mb-3">Subject-wise Results</h6>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Subject</th>
                                    <th>Marks Obtained</th>
                                    <th>Total Marks</th>
                                    <th>Percentage</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedExam['results'] as $result)
                                    @php
                                        $subjectPercentage = $result->total_marks > 0 ? ($result->marks_obtained / $result->total_marks) * 100 : 0;
                                        $subjectGrade = \App\Services\GradeCalculator::calculateGrade($subjectPercentage);
                                        $subjectGradeColor = \App\Services\GradeCalculator::getGradeColor($subjectGrade);
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $result->exam->subject->name }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-medium">{{ $result->marks_obtained }}</div>
                                        </td>
                                        <td>
                                            <div class="text-muted">{{ $result->total_marks }}</div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <strong>{{ number_format($subjectPercentage, 1) }}%</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $subjectGradeColor }}">
                                                {{ $subjectGrade }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($result->status === 'pass')
                                                <span class="badge bg-success">Pass</span>
                                            @elseif($result->status === 'fail')
                                                <span class="badge bg-danger">Fail</span>
                                            @else
                                                <span class="badge bg-warning">Absent</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $result->remarks ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Print Result Button -->
                <div class="text-center mt-4">
                    <button class="btn btn-outline-primary" onclick="printResult()">
                        <i class="fas fa-print me-2"></i>Print Result
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Overall Performance Summary -->
    @if($overallStats['total_exams'] > 0)
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>Overall Performance Summary
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center mb-4">
                    <div class="col-md-2 col-4 mb-3">
                        <div class="stat-box">
                            <div class="h4 mb-0 text-primary">{{ $overallStats['total_exams'] }}</div>
                            <small class="text-muted">Total Exams</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="stat-box">
                            <div class="h4 mb-0 text-success">{{ $overallStats['passed_exams'] }}</div>
                            <small class="text-muted">Passed</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="stat-box">
                            <div class="h4 mb-0 text-danger">{{ $overallStats['failed_exams'] }}</div>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="stat-box">
                            <div class="h4 mb-0 text-info">{{ number_format($overallStats['average_percentage'], 1) }}%</div>
                            <small class="text-muted">Average</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="stat-box">
                            <div class="h4 mb-0 text-warning">{{ $overallStats['best_grade'] }}</div>
                            <small class="text-muted">Best Grade</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <div class="stat-box">
                            <div class="h4 mb-0 text-secondary">{{ $overallStats['worst_grade'] }}</div>
                            <small class="text-muted">Worst Grade</small>
                        </div>
                    </div>
                </div>

                <!-- Grade Distribution -->
                <div class="row">
                    <div class="col-md-8">
                        <h6 class="text-muted mb-3">Grade Distribution</h6>
                        <div class="grade-distribution">
                            @foreach($overallStats['grade_distribution'] as $grade => $count)
                                @if($count > 0)
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-3" style="width: 60px;">
                                            <span class="badge bg-{{ $grade[0] === 'A' ? 'success' : ($grade[0] === 'B' ? 'info' : ($grade[0] === 'C' ? 'warning' : ($grade[0] === 'D' ? 'danger' : 'dark')) }}">
                                                {{ $grade }}
                                            </span>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $grade[0] === 'A' ? 'success' : ($grade[0] === 'B' ? 'info' : ($grade[0] === 'C' ? 'warning' : ($grade[0] === 'D' ? 'danger' : 'dark')) }}" 
                                                     style="width: {{ ($count / $overallStats['total_exams']) * 100 }}%">
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
                        <h6 class="text-muted mb-3">Performance Metrics</h6>
                        <div class="performance-metrics">
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Pass Rate:</span>
                                    <strong>{{ number_format($overallStats['pass_rate'], 1) }}%</strong>
                                </div>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: {{ $overallStats['pass_rate'] }}%"></div>
                                </div>
                            </div>
                            <div class="metric-item mb-3">
                                <div class="d-flex justify-content-between">
                                    <span>Total Marks:</span>
                                    <strong>{{ $overallStats['total_marks_max'] }}</strong>
                                </div>
                            </div>
                            <div class="metric-item">
                                <div class="d-flex justify-content-between">
                                    <span>Obtained Marks:</span>
                                    <strong>{{ $overallStats['total_marks_obtained'] }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Previous Exams History -->
    @if(!empty($examStatistics))
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>Previous Exams
                </h6>
            </div>
            <div class="card-body">
                <div class="accordion" id="examHistoryAccordion">
                    @foreach($examStatistics as $examId => $stats)
                        @php
                            $isCurrent = $selectedExam && $selectedExam['exam']->id == $stats['exam']->id;
                        @endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $examId }}">
                                <button class="accordion-button {{ $isCurrent ? '' : 'collapsed' }}" 
                                        type="button" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#collapse{{ $examId }}"
                                        {{ $isCurrent ? 'aria-expanded="true"' : 'aria-expanded="false"' }}>
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div>
                                            <strong>{{ $stats['exam']->title }}</strong>
                                            <small class="text-muted ms-2">{{ $stats['formatted_date'] }}</small>
                                        </div>
                                        <div>
                                            <span class="badge bg-{{ $stats['grade_color'] }} me-2">{{ $stats['grade'] }}</span>
                                            <span class="text-muted">{{ number_format($stats['percentage'], 1) }}%</span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $examId }}" 
                                 class="accordion-collapse collapse {{ $isCurrent ? 'show' : '' }}" 
                                 aria-labelledby="heading{{ $examId }}"
                                 data-bs-parent="#examHistoryAccordion">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="exam-info">
                                                <div class="info-item mb-2">
                                                    <label class="text-muted small">Subject:</label>
                                                    <div>{{ $stats['exam']->subject->name }}</div>
                                                </div>
                                                <div class="info-item mb-2">
                                                    <label class="text-muted small">Class:</label>
                                                    <div>Grade {{ $stats['exam']->schoolClass->grade_level }} - {{ $stats['exam']->section }}</div>
                                                </div>
                                                <div class="info-item mb-2">
                                                    <label class="text-muted small">Duration:</label>
                                                    <div>{{ $stats['exam']->duration_minutes }} minutes</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="result-info">
                                                <div class="info-item mb-2">
                                                    <label class="text-muted small">Total Marks:</label>
                                                    <div>{{ $stats['total_max'] }}</div>
                                                </div>
                                                <div class="info-item mb-2">
                                                    <label class="text-muted small">Obtained Marks:</label>
                                                    <div>{{ $stats['total_obtained'] }}</div>
                                                </div>
                                                <div class="info-item mb-2">
                                                    <label class="text-muted small">Status:</label>
                                                    <div>
                                                        <span class="badge bg-{{ $stats['status'] === 'pass' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($stats['status']) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!$isCurrent)
                                        <div class="text-center mt-3">
                                            <button class="btn btn-outline-primary btn-sm" onclick="viewExamDetails({{ $examId }})">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <!-- No Results State -->
        <div class="text-center py-5">
            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Exam Results</h5>
            <p class="text-muted">{{ $student->user->name }} doesn't have any exam results yet.</p>
        </div>
    @endif
</div>

<!-- Print Styles -->
<style media="print">
    .no-print {
        display: none !important;
    }
    
    .card {
        break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 1rem;
    }
    
    .accordion-button {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
    
    .badge {
        background-color: #000 !important;
        color: #fff !important;
    }
    
    .progress-bar {
        background-color: #000 !important;
    }
    
    body {
        font-size: 12px;
    }
    
    .container-fluid {
        max-width: 100%;
        padding: 0;
    }
    
    .accordion-collapse {
        display: block !important;
    }
    
    .accordion-button::after {
        display: none !important;
    }
</style>

<style>
.stat-box {
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: transform 0.2s ease;
}

.stat-box:hover {
    transform: translateY(-2px);
}

.exam-details .detail-item,
.result-summary .detail-item,
.exam-info .info-item,
.result-info .info-item,
.performance-metrics .metric-item {
    border-bottom: 1px solid #eee;
    padding-bottom: 0.5rem;
}

.exam-details .detail-item:last-child,
.result-summary .detail-item:last-child,
.exam-info .info-item:last-child,
.result-info .info-item:last-child,
.performance-metrics .metric-item:last-child {
    border-bottom: none;
}

.grade-distribution .progress-bar {
    font-weight: 500;
}

.accordion-button:focus {
    box-shadow: none;
}

.accordion-button:not(.collapsed) {
    background-color: #f8f9fa;
    color: #000;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .stat-box .h4 {
        font-size: 1.25rem;
    }
    
    .accordion-button {
        font-size: 0.875rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeResults();
});

function initializeResults() {
    // Auto-refresh every 5 minutes
    setInterval(refreshResults, 300000);
}

function switchExam(examId) {
    const studentId = {{ $student->id }};
    window.location.href = `/parent/children/${studentId}/results?exam_id=${examId}`;
}

function viewExamDetails(examId) {
    const studentId = {{ $student->id }};
    window.location.href = `/parent/children/${studentId}/results?exam_id=${examId}`;
}

function printResult() {
    // Hide elements that shouldn't be printed
    const elementsToHide = document.querySelectorAll('.no-print');
    elementsToHide.forEach(el => el.classList.add('no-print-temp'));
    
    // Trigger print
    window.print();
    
    // Restore visibility after print
    setTimeout(() => {
        const tempElements = document.querySelectorAll('.no-print-temp');
        tempElements.forEach(el => el.classList.remove('no-print-temp'));
    }, 1000);
}

function exportResults() {
    fetch(`/parent/children/{{ $student->id }}/results/export`, {
        method: 'GET',
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

function refreshResults() {
    // Refresh the current page to get updated data
    window.location.reload();
}

function showToast(title, message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3 no-print`;
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
