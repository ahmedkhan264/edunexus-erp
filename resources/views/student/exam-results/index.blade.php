@extends('layouts.app')

@section('title', 'My Results')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">My Results</h1>
            <p class="text-muted mb-0">View your exam results and performance</p>
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
                    <h4 class="mb-0">{{ $overallStats['total_exams'] }}</h4>
                    <small>Total Exams</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ number_format($overallStats['average_percentage'], 1) }}%</h4>
                    <small>Average Score</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $overallStats['total_marks_obtained'] }}</h4>
                    <small>Total Obtained</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-trophy fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $overallStats['total_marks_max'] }}</h4>
                    <small>Total Marks</small>
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
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-trophy me-2"></i>Performance Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="performance-stats">
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Total Marks:</span>
                                <strong>{{ $overallStats['total_marks_max'] }}</strong>
                            </div>
                        </div>
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Obtained Marks:</span>
                                <strong>{{ $overallStats['total_marks_obtained'] }}</strong>
                            </div>
                        </div>
                        <div class="stat-item mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Percentage:</span>
                                <strong>{{ number_format($overallStats['average_percentage'], 1) }}%</strong>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="d-flex justify-content-between">
                                <span>Performance:</span>
                                <strong>{{ \App\Services\GradeCalculator::getPerformanceLevel($overallStats['average_percentage']) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Results Cards -->
    <div class="row">
        @forelse($examStatistics as $examId => $stats)
            <div class="col-lg-6 col-xl-4 mb-4">
                <div class="card h-100 exam-result-card">
                    <div class="card-header bg-{{ $stats['grade_color'] }} text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $stats['exam']->title }}</h6>
                            <span class="badge bg-light text-dark">{{ $stats['grade'] }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Exam Details -->
                        <div class="exam-details mb-3">
                            <div class="row g-2 text-muted small">
                                <div class="col-6">
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    Grade {{ $stats['exam']->schoolClass->grade_level }} - {{ $stats['exam']->section }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-book me-1"></i>
                                    {{ $stats['exam']->subject->name }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $stats['formatted_date'] }}
                                </div>
                                <div class="col-6">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $stats['exam']->getFormattedStartTime() }}
                                </div>
                            </div>
                        </div>

                        <!-- Results Summary -->
                        <div class="results-summary mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h5 mb-0 text-primary">{{ $stats['total_obtained'] }}</div>
                                    <small class="text-muted">Obtained</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 text-info">{{ $stats['total_max'] }}</div>
                                    <small class="text-muted">Total</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 text-success">{{ number_format($stats['percentage'], 1) }}%</div>
                                    <small class="text-muted">Percentage</small>
                                </div>
                            </div>
                            
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-{{ $stats['grade_color'] }}" style="width: {{ $stats['percentage'] }}%"></div>
                            </div>
                        </div>

                        <!-- Subject-wise Results -->
                        <div class="subject-results mb-3">
                            <h6 class="text-muted mb-2">Subject-wise Results</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Subject</th>
                                            <th>Marks</th>
                                            <th>Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['results'] as $result)
                                            <tr>
                                                <td>{{ $result->exam->subject->name }}</td>
                                                <td>
                                                    <span class="badge bg-{{ \App\Services\GradeCalculator::getGradeColor($result->grade) }}">
                                                        {{ $result->marks_obtained }} / {{ $result->total_marks }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ \App\Services\GradeCalculator::getGradeColor($result->grade) }}">
                                                        {{ $result->grade }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-outline-primary btn-sm" onclick="viewDetails({{ $examId }})">
                                    <i class="fas fa-eye me-1"></i>View Details
                                </button>
                                <button class="btn btn-outline-success btn-sm" onclick="printResult({{ $examId }})">
                                    <i class="fas fa-print me-1"></i>Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Exam Results</h5>
                    <p class="text-muted">You don't have any exam results yet.</p>
                </div>
            </div>
        @endforelse
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
                <button type="button" class="btn btn-success" id="printModalBtn">
                    <i class="fas fa-print me-2"></i>Print Result
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style media="print">
    .no-print {
        display: none !important;
    }
    
    .exam-result-card {
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
</style>

<style>
.exam-result-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
}

.exam-result-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.exam-details {
    border-top: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.results-summary {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.75rem;
}

.action-buttons {
    border-top: 1px solid #dee2e6;
    padding-top: 0.75rem;
}

.performance-stats .stat-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.performance-stats .stat-item:last-child {
    border-bottom: none;
}

.grade-distribution .progress-bar {
    font-weight: 500;
}

@media (max-width: 768px) {
    .exam-result-card {
        margin-bottom: 1rem;
    }
    
    .action-buttons .d-flex {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .action-buttons .btn {
        width: 100%;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any dynamic functionality
});

function viewDetails(examId) {
    const modal = new bootstrap.Modal(document.getElementById('examDetailsModal'));
    const content = document.getElementById('examDetailsContent');
    const printBtn = document.getElementById('printModalBtn');
    
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
        printBtn.onclick = function() {
            printResult(examId);
        };
    }, 500);
    
    modal.show();
}

function printResult(examId) {
    // Hide elements that shouldn't be printed
    const elementsToHide = document.querySelectorAll('.no-print');
    elementsToHide.forEach(el => el.classList.add('no-print-temp'));
    
    // Show only the specific exam card
    const allCards = document.querySelectorAll('.exam-result-card');
    allCards.forEach(card => {
        if (!card.querySelector(`[onclick*="${examId}"]`)) {
            card.style.display = 'none';
        }
    });
    
    // Trigger print
    window.print();
    
    // Restore visibility after print
    setTimeout(() => {
        allCards.forEach(card => {
            card.style.display = '';
        });
        
        const tempElements = document.querySelectorAll('.no-print-temp');
        tempElements.forEach(el => el.classList.remove('no-print-temp'));
    }, 1000);
}

function exportResults() {
    fetch('/student/exam-results/export', {
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
