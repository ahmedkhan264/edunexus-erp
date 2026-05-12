@extends('layouts.app')

@section('title', 'Fee Status - ' . $student->user->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Fee Status - {{ $student->user->name }}</h1>
            <p class="text-muted mb-0">Grade {{ $student->schoolClass->grade_level }} - {{ $student->schoolClass->section }}</p>
        </div>
        <div class="text-end">
            <a href="{{ route('parent.dashboard', ['child_id' => $student->id]) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <button class="btn btn-outline-success" onclick="downloadLedger()">
                <i class="fas fa-download me-2"></i>Download Ledger
            </button>
        </div>
    </div>

    <!-- Fee Summary Card -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0">
                <i class="fas fa-calculator me-2"></i>Fee Summary
            </h6>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-box">
                        <div class="h4 mb-0 text-primary">Rs. {{ number_format($feeSummary['total_challaned'], 0) }}</div>
                        <small class="text-muted">Total Challaned</small>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-box">
                        <div class="h4 mb-0 text-success">Rs. {{ number_format($feeSummary['total_paid'], 0) }}</div>
                        <small class="text-muted">Total Paid</small>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-box">
                        <div class="h4 mb-0 text-danger">Rs. {{ number_format($feeSummary['total_outstanding'], 0) }}</div>
                        <small class="text-muted">Outstanding</small>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="stat-box">
                        <div class="h4 mb-0 text-warning">Rs. {{ number_format($feeSummary['total_late_fine'], 0) }}</div>
                        <small class="text-muted">Late Fine</small>
                    </div>
                </div>
            </div>
            
            <!-- Payment Progress Bar -->
            <div class="mt-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted">Payment Progress</small>
                    <small class="text-muted">{{ number_format($feeSummary['payment_rate'], 1) }}%</small>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" style="width: {{ $feeSummary['payment_rate'] }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Challan History Table -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-list me-2"></i>Challan History
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Challan Number</th>
                            <th>Month/Year</th>
                            <th>Due Date</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Status</th>
                            <th>Late Fine</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($feeChallans as $challan)
                            @php
                                $status = $challan->paid_amount >= $challan->amount ? 'paid' : 
                                         ($challan->paid_amount > 0 ? 'partial' : 
                                         ($challan->due_date < Carbon::now() ? 'overdue' : 'pending'));
                                $statusColor = $status === 'paid' ? 'success' : 
                                               ($status === 'partial' ? 'warning' : 
                                               ($status === 'overdue' ? 'danger' : 'info');
                            @endphp
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $challan->challan_number ?? 'CH-' . str_pad($challan->id, 6, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td>
                                    <div>{{ $challan->month ?? 'N/A' }} / {{ $challan->year ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div>{{ $challan->due_date ? $challan->due_date->format('M j, Y') : 'N/A' }}</div>
                                    @if($challan->due_date && $challan->due_date < Carbon::now() && $status !== 'paid')
                                        <small class="text-danger">Overdue</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium">Rs. {{ number_format($challan->amount, 0) }}</div>
                                </td>
                                <td>
                                    <div>Rs. {{ number_format($challan->paid_amount, 0) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td>
                                    <div>Rs. {{ number_format($challan->late_fine, 0) }}</div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="downloadChallan({{ $challan->id }})">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-receipt fa-2x text-muted mb-2"></i>
                                    <div class="text-muted">No fee challans found</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>Payment History
                </h6>
                <button class="btn btn-sm btn-outline-light" onclick="togglePaymentHistory()">
                    <i class="fas fa-chevron-down" id="paymentHistoryIcon"></i>
                </button>
            </div>
        </div>
        <div class="card-body" id="paymentHistoryContent" style="display: none;">
            @forelse($paymentHistory as $payment)
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div>
                        <div class="fw-medium">{{ $payment['receipt_number'] }}</div>
                        <small class="text-muted">{{ $payment['payment_date'] }} - {{ $payment['challan_month'] }}/{{ $payment['challan_year'] }}</small>
                    </div>
                    <div class="text-center">
                        <div class="fw-medium">Rs. {{ number_format($payment['amount'], 0) }}</div>
                        <small class="text-muted">{{ $payment['payment_method'] }}</small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">{{ $payment['remarks'] }}</small>
                    </div>
                </div>
            @empty
                <div class="text-center py-4">
                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                    <div class="text-muted">No payment history found</div>
                </div>
            @endforelse
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
    
    .table-responsive {
        overflow: visible !important;
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
.stat-box {
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: transform 0.2s ease;
}

.stat-box:hover {
    transform: translateY(-2px);
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
}

.badge {
    font-size: 0.75rem;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.border-left-primary {
    border-left: 4px solid #0d6efd !important;
}

.border-left-success {
    border-left: 4px solid #198754 !important;
}

.border-left-danger {
    border-left: 4px solid #dc3545 !important;
}

.border-left-warning {
    border-left: 4px solid #ffc107 !important;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-sm {
        padding: 0.125rem 0.25rem;
        font-size: 0.625rem;
    }
    
    .stat-box .h4 {
        font-size: 1.25rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFeeStatus();
});

function initializeFeeStatus() {
    // Auto-refresh every 5 minutes
    setInterval(refreshFeeStatus, 300000);
}

function togglePaymentHistory() {
    const content = document.getElementById('paymentHistoryContent');
    const icon = document.getElementById('paymentHistoryIcon');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
    } else {
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
    }
}

function downloadChallan(challanId) {
    fetch(`/parent/fees/challan/${challanId}/download`, {
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
            // In a real implementation, you would trigger the file download here
            setTimeout(() => {
                showToast('Info', 'PDF download would start here', 'info');
            }, 1000);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to download challan. Please try again.', 'error');
    });
}

function downloadLedger() {
    fetch(`/parent/children/{{ $student->id }}/fees/ledger/download`, {
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
            // In a real implementation, you would trigger the file download here
            setTimeout(() => {
                showToast('Info', 'PDF ledger download would start here', 'info');
            }, 1000);
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        showToast('Error', 'Failed to download ledger. Please try again.', 'error');
    });
}

function refreshFeeStatus() {
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

// Helper function for number formatting (similar to PHP's number_format)
function number_format(number, decimals) {
    return parseFloat(number).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
</script>
@endpush
