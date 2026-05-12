@extends('layouts.app')

@section('title', 'Teacher Attendance')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Teacher Attendance</h1>
            <p class="text-muted mb-0">Mark your daily attendance</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6" id="currentDate"></span>
        </div>
    </div>

    <!-- Teacher Card -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">My Profile</h5>
                <div id="liveClock" class="fs-5 fw-bold"></div>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="avatar-lg bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                        <i class="fas fa-user-tie fa-3x text-primary"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4 class="mb-1">{{ $teacher->name }}</h4>
                    <p class="text-muted mb-2">Teacher</p>
                    <div class="row">
                        <div class="col-sm-6">
                            <small class="text-muted">Employee Code:</small>
                            <p class="mb-1 fw-medium">{{ $teacher->employee_code ?? 'N/A' }}</p>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">Department:</small>
                            <p class="mb-1 fw-medium">{{ $teacher->department->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div id="statusBadge" class="badge bg-secondary fs-6 p-3 mb-3">
                            <i class="fas fa-clock me-2"></i>
                            <span id="statusText">Not Checked In</span>
                        </div>
                        <div id="workingHours" class="text-muted" style="display: none;">
                            <small>Working Hours: <span id="hoursDisplay" class="fw-bold">0:00</span></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Actions -->
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Attendance Actions</h5>
        </div>
        <div class="card-body text-center">
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-success btn-lg px-5 py-3" id="checkInBtn" onclick="checkIn()">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Check In
                    </button>
                    <div class="mt-2">
                        <small class="text-muted" id="checkInTime"></small>
                    </div>
                </div>
                <div class="col-md-6">
                    <button type="button" class="btn btn-danger btn-lg px-5 py-3" id="checkOutBtn" onclick="checkOut()" disabled>
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Check Out
                    </button>
                    <div class="mt-2">
                        <small class="text-muted" id="checkOutTime"></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Today's Activity</h5>
        </div>
        <div class="card-body">
            <div id="timeline">
                <div class="text-center text-muted py-4">
                    <i class="fas fa-history fa-2x mb-2"></i>
                    <p>No activity recorded yet today.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance History -->
    <div class="card mt-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Recent Attendance History (Last 7 Days)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Working Hours</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($recentAttendance->count() > 0)
                            @foreach($recentAttendance as $attendance)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</td>
                                <td>{{ $attendance->check_in_time ?? '-' }}</td>
                                <td>{{ $attendance->check_out_time ?? '-' }}</td>
                                <td>{{ $attendance->formatted_working_hours }}</td>
                                <td>
                                    <span class="badge bg-{{ $attendance->getStatusBadgeColor() }}">
                                        {{ $attendance->getStatusDisplay() }}
                                    </span>
                                </td>
                                <td>{{ $attendance->remarks ?? '-' }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No attendance records found.
                                </td>
                            </tr>
                        @endif
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

.avatar-lg {
    width: 80px;
    height: 80px;
}

.timeline-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.timeline-item:last-child {
    border-bottom: none;
}

.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    color: white;
}

.timeline-content {
    flex: 1;
}

.timeline-time {
    font-weight: bold;
    color: #333;
}

.timeline-action {
    color: #666;
}

.timeline-description {
    font-size: 0.9em;
    color: #999;
    margin-top: 2px;
}

.btn-lg {
    font-size: 1.2rem;
    padding: 15px 30px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn:disabled {
    transform: none;
    box-shadow: none;
}

.badge.fs-6 {
    font-size: 1rem !important;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize clock and date
    updateClock();
    setInterval(updateClock, 1000);
    
    // Set current date
    const today = new Date();
    document.getElementById('currentDate').textContent = today.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    // Load current status
    loadCurrentStatus();
    loadTimeline();
    
    // Auto-refresh status every 30 seconds
    setInterval(loadCurrentStatus, 30000);
});

function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        hour12: false 
    });
    document.getElementById('liveClock').textContent = timeString;
}

function loadCurrentStatus() {
    fetch('/teacher/attendance/checkin/status')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateStatusDisplay(data.data);
            }
        })
        .catch(error => console.error('Error loading status:', error));
}

function updateStatusDisplay(statusData) {
    const statusBadge = document.getElementById('statusBadge');
    const statusText = document.getElementById('statusText');
    const checkInBtn = document.getElementById('checkInBtn');
    const checkOutBtn = document.getElementById('checkOutBtn');
    const checkInTime = document.getElementById('checkInTime');
    const checkOutTime = document.getElementById('checkOutTime');
    const workingHours = document.getElementById('workingHours');
    const hoursDisplay = document.getElementById('hoursDisplay');
    
    // Update status badge
    statusBadge.className = `badge bg-${statusData.status_color} fs-6 p-3 mb-3`;
    statusText.textContent = statusData.status_display;
    
    // Update buttons
    if (statusData.has_checked_in) {
        checkInBtn.disabled = true;
        checkInBtn.innerHTML = '<i class="fas fa-check me-2"></i>Checked In';
        checkInTime.textContent = `Checked in at: ${statusData.check_in_time}`;
        
        if (statusData.has_checked_out) {
            checkOutBtn.disabled = true;
            checkOutBtn.innerHTML = '<i class="fas fa-check me-2"></i>Checked Out';
            checkOutTime.textContent = `Checked out at: ${statusData.check_out_time}`;
            workingHours.style.display = 'block';
            hoursDisplay.textContent = statusData.working_hours;
        } else {
            checkOutBtn.disabled = false;
            checkOutTime.textContent = '';
            workingHours.style.display = 'none';
        }
    } else {
        checkInBtn.disabled = false;
        checkInBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Check In';
        checkInTime.textContent = '';
        checkOutBtn.disabled = true;
        checkOutTime.textContent = '';
        workingHours.style.display = 'none';
    }
}

function checkIn() {
    showLoading();
    
    fetch('/teacher/attendance/checkin', {
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
            updateStatusDisplay(data.data);
            loadTimeline();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error checking in:', error);
        showToast('Error', 'Failed to check in', 'error');
    });
}

function checkOut() {
    showLoading();
    
    fetch('/teacher/attendance/checkout', {
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
            updateStatusDisplay(data.data);
            loadTimeline();
        } else {
            showToast('Error', data.message, 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error checking out:', error);
        showToast('Error', 'Failed to check out', 'error');
    });
}

function loadTimeline() {
    fetch('/teacher/attendance/checkin/timeline')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.timeline.length > 0) {
                renderTimeline(data.timeline);
            } else {
                renderEmptyTimeline();
            }
        })
        .catch(error => console.error('Error loading timeline:', error));
}

function renderTimeline(timeline) {
    const timelineDiv = document.getElementById('timeline');
    let html = '';
    
    timeline.forEach(item => {
        html += `
            <div class="timeline-item">
                <div class="timeline-icon bg-${item.color}">
                    <i class="fas ${item.icon}"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-time">${item.time}</div>
                    <div class="timeline-action">${item.action}</div>
                    <div class="timeline-description">${item.description}</div>
                </div>
            </div>
        `;
    });
    
    timelineDiv.innerHTML = html;
}

function renderEmptyTimeline() {
    const timelineDiv = document.getElementById('timeline');
    timelineDiv.innerHTML = `
        <div class="text-center text-muted py-4">
            <i class="fas fa-history fa-2x mb-2"></i>
            <p>No activity recorded yet today.</p>
        </div>
    `;
}

function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}

function showToast(title, message, type) {
    // Simple toast notification
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
