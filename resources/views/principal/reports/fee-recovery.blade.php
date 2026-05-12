@extends('layouts.app')

@section('title', 'Fee Recovery Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Fee Recovery Report</h1>
            <p class="text-muted mb-0">Comprehensive fee collection analysis and defaulters tracking</p>
        </div>
        <div class="text-end">
            <button class="btn btn-outline-success me-2" onclick="exportPdf()">
                <i class="fas fa-file-pdf me-2"></i>Export PDF
            </button>
            <button class="btn btn-outline-primary me-2" onclick="exportExcel()">
                <i class="fas fa-file-excel me-2"></i>Export Excel
            </button>
            <button class="btn btn-outline-warning" onclick="refreshReport()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('principal.reports.fee-recovery') }}">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="academicYear" class="form-label">Academic Year</label>
                        <select class="form-select" id="academicYear" name="academic_year">
                            @for($year = 2020; $year <= date('Y') + 2; $year++)
                                <option value="{{ $year }}-{{ $year + 1 }}" 
                                        {{ $academicYear === ($year . '-' . ($year + 1)) ? 'selected' : '' }}>
                                    {{ $year }}-{{ $year + 1 }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="month" class="form-label">Month (Optional)</label>
                        <select class="form-select" id="month" name="month">
                            <option value="">All Months</option>
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ Carbon::createFromDate(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-2"></i>Apply Filters
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('principal.reports.fee-recovery') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-2"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <!-- Total Challaned -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Challaned</h6>
                            <h4 class="mb-0">Rs. {{ number_format($reportData['kpi_cards']['total_challaned']) }}</h4>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Collected -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Collected</h6>
                            <h4 class="mb-0">Rs. {{ number_format($reportData['kpi_cards']['total_collected']) }}</h4>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-money-check-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Outstanding -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total Outstanding</h6>
                            <h4 class="mb-0">Rs. {{ number_format($reportData['kpi_cards']['total_outstanding']) }}</h4>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recovery Percentage -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card h-100 border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Recovery %</h6>
                            <h4 class="mb-0">{{ number_format($reportData['kpi_cards']['recovery_percentage'], 1) }}%</h4>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-chart-pie fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ $reportData['kpi_cards']['recovery_percentage'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Class-wise Collection Table and Chart -->
    <div class="row mb-4">
        <!-- Class-wise Collection Table -->
        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-table me-2"></i>Class-wise Collection
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Class</th>
                                    <th>Total Students</th>
                                    <th>Total Challaned</th>
                                    <th>Total Paid</th>
                                    <th>Recovery %</th>
                                    <th>Outstanding</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData['class_wise_collection'] as $class)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $class['class_name'] }}</div>
                                        </td>
                                        <td>
                                            <div class="text-center">{{ $class['total_students'] }}</div>
                                        </td>
                                        <td>
                                            <div class="text-end">Rs. {{ number_format($class['total_challaned']) }}</div>
                                        </td>
                                        <td>
                                            <div class="text-end">Rs. {{ number_format($class['total_paid']) }}</div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <span class="me-2">{{ number_format($class['recovery_percentage'], 1) }}%</span>
                                                    <div class="progress" style="width: 60px; height: 6px;">
                                                        <div class="progress-bar bg-{{ $class['recovery_percentage'] >= 80 ? 'success' : ($class['recovery_percentage'] >= 60 ? 'warning' : 'danger') }}" 
                                                             style="width: {{ $class['recovery_percentage'] }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-end text-danger fw-medium">
                                                Rs. {{ number_format($class['outstanding']) }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <div class="text-muted">No fee collection data found</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Defaulters -->
        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Top 5 Defaulters
                    </h6>
                </div>
                <div class="card-body">
                    @if(!empty($reportData['top_defaulters']))
                        <div class="defaulters-list">
                            @foreach($reportData['top_defaulters'] as $defaulter)
                                <div class="defaulter-item d-flex justify-content-between align-items-start mb-3 pb-3 border-bottom">
                                    <div>
                                        <div class="fw-medium">{{ $defaulter['name'] }}</div>
                                        <small class="text-muted">{{ $defaulter['class'] }}</small>
                                        <br>
                                        <small class="text-muted">Roll: {{ $defaulter['roll_number'] }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-medium text-danger">Rs. {{ number_format($defaulter['total_due']) }}</div>
                                        <small class="text-muted">{{ $defaulter['days_overdue'] }} days</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                            <div class="text-muted">No defaulters found</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Collection Trend Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Monthly Collection Trend - {{ $academicYear }}
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyCollectionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 4px solid #0d6efd !important;
}

.border-left-success {
    border-left: 4px solid #198754 !important;
}

.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}

.border-left-info {
    border-left: 4px solid #0dcaf0 !important;
}

.defaulters-list .defaulter-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .progress {
        width: 40px !important;
    }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
});

function initializeChart() {
    const ctx = document.getElementById('monthlyCollectionChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json(array_column($reportData['monthly_collection_trend'], 'month')),
            datasets: [{
                label: 'Collected',
                data: @json(array_column($reportData['monthly_collection_trend'], 'collected')),
                backgroundColor: '#198754'
            }, {
                label: 'Target',
                data: @json(array_column($reportData['monthly_collection_trend'], 'target')),
                backgroundColor: '#ffc107'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': Rs. ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rs. ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
}

function exportPdf() {
    const currentUrl = new URL(window.location);
    const params = currentUrl.searchParams;
    
    fetch(`/principal/reports/fee-recovery/pdf?${params.toString()}`, {
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
            // In a real implementation, you would trigger the PDF download here
            setTimeout(() => {
                showToast('Info', 'PDF download would start here', 'info');
            }, 1000);
        } else {
            showToast('Error', data.message || 'Failed to export PDF', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to export PDF', 'error');
    });
}

function exportExcel() {
    const currentUrl = new URL(window.location);
    const params = currentUrl.searchParams;
    
    fetch(`/principal/reports/fee-recovery/excel?${params.toString()}`, {
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
            // In a real implementation, you would trigger the Excel download here
            setTimeout(() => {
                showToast('Info', 'Excel download would start here', 'info');
            }, 1000);
        } else {
            showToast('Error', data.message || 'Failed to export Excel', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to export Excel', 'error');
    });
}

function refreshReport() {
    const refreshBtn = document.querySelector('button[onclick="refreshReport()"]');
    const originalText = refreshBtn.innerHTML;
    
    // Show loading state
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Refreshing...';
    refreshBtn.disabled = true;
    
    fetch('/principal/reports/fee-recovery/refresh', {
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
            // Reload the page to show updated data
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showToast('Error', 'Failed to refresh report', 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to refresh report', 'error');
    })
    .finally(() => {
        // Restore button state
        refreshBtn.innerHTML = originalText;
        refreshBtn.disabled = false;
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
