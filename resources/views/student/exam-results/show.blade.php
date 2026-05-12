@extends('layouts.app')

@section('title', 'Exam Result - ' . $examStatistics['exam']->title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $examStatistics['exam']->title }}</h1>
            <p class="text-muted mb-0">Exam Result Details</p>
        </div>
        <div class="text-end">
            <a href="{{ route('student.exam-results.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Results
            </a>
            <button class="btn btn-success" onclick="printResult()">
                <i class="fas fa-print me-2"></i>Print Result
            </button>
        </div>
    </div>

    <!-- Exam Information -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Exam Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Exam Title</label>
                                <div class="fw-medium">{{ $examStatistics['exam']->title }}</div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="text-muted small">Class</label>
                                <div class="fw-medium">Grade {{ $examStatistics['exam']->schoolClass->grade_level }} - {{ $examStatistics['exam']->section }}</div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="text-muted small">Subject</label>
                                <div class="fw-medium">{{ $examStatistics['exam']->subject->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="text-muted small">Exam Date</label>
                                <div class="fw-medium">{{ $examStatistics['formatted_date'] }}</div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="text-muted small">Time</label>
                                <div class="fw-medium">{{ $examStatistics['exam']->getFormattedStartTime() }} - {{ $examStatistics['exam']->getFormattedEndTime() }}</div>
                            </div>
                            <div class="info-item mb-3">
                                <label class="text-muted small">Duration</label>
                                <div class="fw-medium">{{ $examStatistics['exam']->duration_minutes }} minutes</div>
                            </div>
                        </div>
                    </div>
                    @if($examStatistics['exam']->description)
                        <div class="mt-3">
                            <label class="text-muted small">Description</label>
                            <div>{{ $examStatistics['exam']->description }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-{{ $examStatistics['grade_color'] }} text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>Result Summary
                    </h6>
                </div>
                <div class="card-body text-center">
                    <div class="result-grade mb-3">
                        <h1 class="display-4 text-{{ $examStatistics['grade_color'] }}">{{ $examStatistics['grade'] }}</h1>
                        <p class="text-muted mb-0">{{ \App\Services\GradeCalculator::getGradeRemarks($examStatistics['grade']) }}</p>
                    </div>
                    <div class="result-details">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h5 mb-0 text-primary">{{ $examStatistics['total_obtained'] }}</div>
                                <small class="text-muted">Obtained</small>
                            </div>
                            <div class="col-4">
                                <div class="h5 mb-0 text-info">{{ $examStatistics['total_max'] }}</div>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="col-4">
                                <div class="h5 mb-0 text-success">{{ number_format($examStatistics['percentage'], 1) }}%</div>
                                <small class="text-muted">Percentage</small>
                            </div>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 10px;">
                        <div class="progress-bar bg-{{ $examStatistics['grade_color'] }}" style="width: {{ $examStatistics['percentage'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subject-wise Results -->
    <div class="card">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Subject-wise Results
            </h6>
        </div>
        <div class="card-body">
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
                        @foreach($examStatistics['results'] as $result)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $result->exam->subject->name }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ \App\Services\GradeCalculator::getGradeColor($result->grade) }}">
                                        {{ $result->marks_obtained }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $result->total_marks }}</span>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <strong>{{ number_format(($result->marks_obtained / $result->total_marks) * 100, 1) }}%</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ \App\Services\GradeCalculator::getGradeColor($result->grade) }}">
                                        {{ $result->grade }}
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
    </div>

    <!-- Performance Analysis -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Performance Analysis
                    </h6>
                </div>
                <div class="card-body">
                    <div class="analysis-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Performance Level</span>
                            <strong>{{ \App\Services\GradeCalculator::getPerformanceLevel($examStatistics['percentage']) }}</strong>
                        </div>
                        <div class="progress mt-1" style="height: 6px;">
                            <div class="progress-bar bg-{{ \App\Services\GradeCalculator::getPerformanceColor($examStatistics['percentage']) }}" style="width: {{ $examStatistics['percentage'] }}%"></div>
                        </div>
                    </div>
                    <div class="analysis-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Grade Points</span>
                            <strong>{{ \App\Services\GradeCalculator::getGradePoints($examStatistics['grade']) }}</strong>
                        </div>
                    </div>
                    <div class="analysis-item">
                        <div class="d-flex justify-content-between">
                            <span>Result Status</span>
                            <strong>{{ \App\Services\GradeCalculator::isPassing($examStatistics['percentage']) ? 'Passed' : 'Failed' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Recommendations
                    </h6>
                </div>
                <div class="card-body">
                    <div class="recommendations">
                        @if($examStatistics['percentage'] >= 80)
                            <div class="alert alert-success mb-2">
                                <i class="fas fa-star me-2"></i>
                                <strong>Excellent Performance!</strong> Keep up the great work.
                            </div>
                        @elseif($examStatistics['percentage'] >= 60)
                            <div class="alert alert-info mb-2">
                                <i class="fas fa-thumbs-up me-2"></i>
                                <strong>Good Performance!</strong> Focus on weak areas for improvement.
                            </div>
                        @else
                            <div class="alert alert-warning mb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Needs Improvement!</strong> Consider additional study and practice.
                            </div>
                        @endif
                        
                        <div class="small text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Consult with your teachers for personalized guidance on areas that need attention.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
    
    .card-header {
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
    
    h1, h3, h6 {
        color: #000 !important;
    }
</style>

<style>
.info-item {
    border-bottom: 1px solid #eee;
    padding-bottom: 0.75rem;
}

.info-item:last-child {
    border-bottom: none;
}

.result-grade .display-4 {
    font-weight: 700;
}

.analysis-item {
    border-bottom: 1px solid #eee;
    padding-bottom: 0.75rem;
}

.analysis-item:last-child {
    border-bottom: none;
}

.recommendations .alert {
    border-radius: 0.5rem;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .text-end {
        text-align: center !important;
    }
}
</style>
@endsection

@push('scripts')
<script>
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
</script>
@endpush
