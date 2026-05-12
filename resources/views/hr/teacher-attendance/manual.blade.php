@extends('layouts.app')

@section('title', 'Manual Teacher Attendance Entry')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Manual Teacher Attendance Entry</h1>
            <p class="text-muted mb-0">Record attendance manually for teachers</p>
        </div>
        <div class="text-end">
            <a href="{{ route('hr.teacher-attendance.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Manual Entry Form -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Attendance Entry Form</h5>
        </div>
        <div class="card-body">
            <form id="manualAttendanceForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="teacher_id" class="form-label">Teacher <span class="text-danger">*</span></label>
                        <select class="form-select" id="teacher_id" name="teacher_id" required>
                            <option value="">Select Teacher</option>
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}">{{ $teacher->name }} ({{ $teacher->employee_code ?? 'N/A' }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" required max="{{ now()->format('Y-m-d') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                            <option value="half_day">Half Day</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="check_in_time" class="form-label">Check In Time</label>
                        <input type="time" class="form-control" id="check_in_time" name="check_in_time">
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="check_out_time" class="form-label">Check Out Time</label>
                        <input type="time" class="form-control" id="check_out_time" name="check_out_time">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <label for="reason" class="form-label">Reason <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reason" name="reason" placeholder="Reason for attendance status" required>
                        <small class="text-muted">Required for Absent or Late status</small>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="remarks" class="form-label">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="1" placeholder="Additional remarks (optional)"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Attendance
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="resetForm()">
                            <i class="fas fa-redo me-2"></i>Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Entries -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Recent Manual Entries</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Teacher</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Reason</th>
                            <th>Marked By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="recentEntriesTable">
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No recent entries found.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
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

.form-label {
    font-weight: 600;
    color: #495057;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn {
    border-radius: 6px;
    padding: 8px 16px;
    font-weight: 500;
}

.card-header {
    border-bottom: 2px solid #007bff;
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

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.75rem;
    }
    
    .btn {
        padding: 6px 12px;
        font-size: 0.875rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('manualAttendanceForm');
    const statusSelect = document.getElementById('status');
    const checkInInput = document.getElementById('check_in_time');
    const checkOutInput = document.getElementById('check_out_time');
    const reasonInput = document.getElementById('reason');
    
    // Set today's date as default
    document.getElementById('date').value = new Date().toISOString().split('T')[0];
    
    // Handle status change to show/hide time inputs
    statusSelect.addEventListener('change', function() {
        const status = this.value;
        
        if (status === 'present' || status === 'late' || status === 'half_day') {
            checkInInput.required = true;
            checkInInput.disabled = false;
            
            if (status === 'present' || status === 'half_day') {
                checkOutInput.required = true;
                checkOutInput.disabled = false;
            } else {
                checkOutInput.required = false;
                checkOutInput.disabled = true;
                checkOutInput.value = '';
            }
        } else {
            checkInInput.required = false;
            checkInInput.disabled = true;
            checkInInput.value = '';
            checkOutInput.required = false;
            checkOutInput.disabled = true;
            checkOutInput.value = '';
        }
        
        // Update reason requirement
        if (status === 'absent' || status === 'late') {
            reasonInput.required = true;
        } else {
            reasonInput.required = false;
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm()) {
            return;
        }
        
        submitAttendance();
    });
    
    // Load recent entries
    loadRecentEntries();
});

function validateForm() {
    const form = document.getElementById('manualAttendanceForm');
    const formData = new FormData(form);
    
    // Clear previous errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
    let isValid = true;
    
    // Validate teacher selection
    if (!formData.get('teacher_id')) {
        const field = document.getElementById('teacher_id');
        field.classList.add('is-invalid');
        field.nextElementSibling.textContent = 'Please select a teacher';
        isValid = false;
    }
    
    // Validate date
    if (!formData.get('date')) {
        const field = document.getElementById('date');
        field.classList.add('is-invalid');
        field.nextElementSibling.textContent = 'Please select a date';
        isValid = false;
    }
    
    // Validate status
    const status = formData.get('status');
    if (!status) {
        const field = document.getElementById('status');
        field.classList.add('is-invalid');
        field.nextElementSibling.textContent = 'Please select attendance status';
        isValid = false;
    }
    
    // Validate check-in time for present/late/half-day
    if (['present', 'late', 'half_day'].includes(status) && !formData.get('check_in_time')) {
        const field = document.getElementById('check_in_time');
        field.classList.add('is-invalid');
        field.nextElementSibling.textContent = 'Check-in time is required for this status';
        isValid = false;
    }
    
    // Validate check-out time for present/half-day
    if (['present', 'half_day'].includes(status) && !formData.get('check_out_time')) {
        const field = document.getElementById('check_out_time');
        field.classList.add('is-invalid');
        field.nextElementSibling.textContent = 'Check-out time is required for this status';
        isValid = false;
    }
    
    // Validate time logic
    const checkIn = formData.get('check_in_time');
    const checkOut = formData.get('check_out_time');
    
    if (checkIn && checkOut && checkOut <= checkIn) {
        const field = document.getElementById('check_out_time');
        field.classList.add('is-invalid');
        field.nextElementSibling.textContent = 'Check-out time must be after check-in time';
        isValid = false;
    }
    
    // Validate reason for absent/late
    if (['absent', 'late'].includes(status) && !formData.get('reason')) {
        const field = document.getElementById('reason');
        field.classList.add('is-invalid');
        field.nextElementSibling.textContent = 'Reason is required for ' + status + ' status';
        isValid = false;
    }
    
    return isValid;
}

function submitAttendance() {
    showLoading();
    
    const form = document.getElementById('manualAttendanceForm');
    const formData = new FormData(form);
    
    fetch('/hr/teacher-attendance/manual/store', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(Object.fromEntries(formData))
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showToast('Success', data.message, 'success');
            resetForm();
            loadRecentEntries();
        } else {
            if (data.existing) {
                showToast('Error', data.message, 'warning');
            } else {
                showToast('Error', data.message || 'Failed to save attendance', 'error');
            }
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error submitting attendance:', error);
        showToast('Error', 'Failed to save attendance', 'error');
    });
}

function resetForm() {
    const form = document.getElementById('manualAttendanceForm');
    form.reset();
    
    // Reset to today's date
    document.getElementById('date').value = new Date().toISOString().split('T')[0];
    
    // Clear validation errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    
    // Reset field states
    document.getElementById('check_in_time').disabled = true;
    document.getElementById('check_out_time').disabled = true;
    document.getElementById('reason').required = false;
}

function loadRecentEntries() {
    fetch('/hr/teacher-attendance/recent-entries')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderRecentEntries(data.entries);
            }
        })
        .catch(error => console.error('Error loading recent entries:', error));
}

function renderRecentEntries(entries) {
    const tbody = document.getElementById('recentEntriesTable');
    
    if (entries.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No recent entries found.</p>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    entries.forEach(entry => {
        const statusColor = getStatusColor(entry.status);
        const statusText = getStatusText(entry.status);
        
        html += `
            <tr>
                <td>${entry.date}</td>
                <td>${entry.teacher_name}</td>
                <td><span class="badge bg-${statusColor}">${statusText}</span></td>
                <td>${entry.check_in_time || '-'}</td>
                <td>${entry.check_out_time || '-'}</td>
                <td>${entry.reason || '-'}</td>
                <td>${entry.marked_by_name}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-info" onclick="viewEntry(${entry.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="editEntry(${entry.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

function getStatusColor(status) {
    const colors = {
        'present': 'success',
        'late': 'warning',
        'absent': 'danger',
        'half_day': 'info'
    };
    return colors[status] || 'secondary';
}

function getStatusText(status) {
    const texts = {
        'present': 'Present',
        'late': 'Late',
        'absent': 'Absent',
        'half_day': 'Half Day'
    };
    return texts[status] || 'Unknown';
}

function viewEntry(id) {
    // Implement view functionality
    window.location.href = `/hr/teacher-attendance/${id}`;
}

function editEntry(id) {
    // Implement edit functionality
    window.location.href = `/hr/teacher-attendance/${id}/edit`;
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
