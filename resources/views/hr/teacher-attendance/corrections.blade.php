@extends('layouts.app')

@section('title', 'Attendance Correction Requests')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Attendance Correction Requests</h1>
            <p class="text-muted mb-0">Review and manage teacher attendance correction requests</p>
        </div>
        <div class="text-end">
            <button type="button" class="btn btn-outline-secondary" onclick="refreshTable()">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="card mb-4">
        <div class="card-body p-0">
            <ul class="nav nav-tabs nav-fill" id="statusTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $status === 'pending' ? 'active' : '' }}" 
                            data-status="pending" onclick="filterByStatus('pending')">
                        <i class="fas fa-clock me-2"></i>
                        Pending
                        <span class="badge bg-warning ms-2">{{ $counts['pending'] }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $status === 'approved' ? 'active' : '' }}" 
                            data-status="approved" onclick="filterByStatus('approved')">
                        <i class="fas fa-check-circle me-2"></i>
                        Approved
                        <span class="badge bg-success ms-2">{{ $counts['approved'] }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" 
                            data-status="rejected" onclick="filterByStatus('rejected')">
                        <i class="fas fa-times-circle me-2"></i>
                        Rejected
                        <span class="badge bg-danger ms-2">{{ $counts['rejected'] }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $status === 'all' ? 'active' : '' }}" 
                            data-status="all" onclick="filterByStatus('all')">
                        <i class="fas fa-list me-2"></i>
                        All
                        <span class="badge bg-primary ms-2">{{ $counts['all'] }}</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card">
        <div class="card-body">
            <div id="correctionsTableContainer">
                @include('hr.teacher-attendance.partials.corrections-table')
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel">Reject Correction Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="rejectCorrectionId" name="correction_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone. The teacher will be notified of the rejection.
                    </div>
                    
                    <div class="mb-3">
                        <label for="rejectionReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="4" 
                                  placeholder="Please provide a detailed reason for rejecting this correction request..." required></textarea>
                        <div class="form-text">Minimum 10 characters required.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-2"></i>Reject Request
                    </button>
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
    <div class="mt-2">Processing...</div>
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

.nav-tabs .nav-link {
    color: #495057;
    border: 1px solid #dee2e6;
    border-bottom: none;
}

.nav-tabs .nav-link.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.nav-tabs .nav-link:hover:not(.active) {
    background-color: #f8f9fa;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

.btn {
    border-radius: 6px;
    padding: 6px 12px;
    font-weight: 500;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.75rem;
    }
    
    .btn-sm {
        padding: 3px 6px;
        font-size: 0.75rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Handle reject form submission
    const rejectForm = document.getElementById('rejectForm');
    rejectForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const correctionId = document.getElementById('rejectCorrectionId').value;
        const rejectionReason = document.getElementById('rejectionReason').value;
        
        if (!rejectionReason || rejectionReason.length < 10) {
            showToast('Error', 'Rejection reason must be at least 10 characters.', 'error');
            return;
        }
        
        rejectCorrection(correctionId, rejectionReason);
    });
});

function filterByStatus(status) {
    showLoading();
    
    // Update active tab
    document.querySelectorAll('#statusTabs .nav-link').forEach(tab => {
        tab.classList.remove('active');
        if (tab.dataset.status === status) {
            tab.classList.add('active');
        }
    });
    
    // Update URL
    const url = new URL(window.location);
    url.searchParams.set('status', status);
    window.history.pushState({}, '', url);
    
    // Load filtered data
    loadCorrections(status);
}

function loadCorrections(status = 'pending') {
    fetch(`/hr/teacher-attendance/corrections?status=${status}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('correctionsTableContainer').innerHTML = html;
        hideLoading();
        
        // Re-initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    })
    .catch(error => {
        console.error('Error loading corrections:', error);
        hideLoading();
        showToast('Error', 'Failed to load correction requests.', 'error');
    });
}

function approveCorrection(correctionId) {
    if (!confirm('Are you sure you want to approve this correction request?')) {
        return;
    }
    
    showLoading();
    
    fetch(`/hr/teacher-attendance/corrections/${correctionId}/approve`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('Success', data.message, 'success');
            refreshTable();
        } else {
            showToast('Error', data.message || 'Failed to approve correction request.', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error approving correction:', error);
        showToast('Error', 'Failed to approve correction request.', 'error');
    });
}

function showRejectModal(correctionId) {
    document.getElementById('rejectCorrectionId').value = correctionId;
    document.getElementById('rejectionReason').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function rejectCorrection(correctionId, rejectionReason) {
    showLoading();
    
    fetch(`/hr/teacher-attendance/corrections/${correctionId}/reject`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            rejection_reason: rejectionReason
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('rejectModal'));
        modal.hide();
        
        if (data.success) {
            showToast('Success', data.message, 'success');
            refreshTable();
        } else {
            showToast('Error', data.message || 'Failed to reject correction request.', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error rejecting correction:', error);
        showToast('Error', 'Failed to reject correction request.', 'error');
    });
}

function refreshTable() {
    const activeTab = document.querySelector('#statusTabs .nav-link.active');
    const status = activeTab ? activeTab.dataset.status : 'pending';
    loadCorrections(status);
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
