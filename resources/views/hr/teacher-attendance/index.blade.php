@extends('layouts.app')

@section('title', 'Teacher Attendance Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Teacher Attendance Dashboard</h1>
            <p class="text-muted mb-0">Monitor and manage teacher attendance</p>
        </div>
        <div class="text-end">
            <!-- Option 1: Use manual.create route -->
            <!-- <a href="{{ route('hr.teacher-attendance.manual.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Manual Entry
            </a> -->
            
            <!-- OR Option 2: Use direct URL -->
            <!-- <a href="{{ url('/hr/teacher-attendance/manual/create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Manual Entry
            </a> -->
            
            <!-- OR Option 3: Use resource create route -->
            <a href="{{ route('hr.teacher-attendance.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Manual Entry
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('hr.teacher-attendance.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ $date }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select class="form-select" id="department_id" name="department_id">
                            <option value="">All Departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ $departmentId == $department->id ? 'selected' : '' }}>
                                    {{ $department->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="present" {{ $status == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="late" {{ $status == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="absent" {{ $status == 'absent' ? 'selected' : '' }}>Absent</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search me-2"></i>Filter
                            </button>
                            <a href="{{ route('hr.teacher-attendance.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mt-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">{{ $totalTeachers }}</h5>
                    <p class="card-text">Total Teachers</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">{{ $presentCount }}</h5>
                    <p class="card-text">Present</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">{{ $lateCount }}</h5>
                    <p class="card-text">Late</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">{{ $absentCount }}</h5>
                    <p class="card-text">Absent</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">{{ number_format($attendancePercentage, 1) }}%</h5>
                    <p class="card-text">Attendance Rate</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">{{ $date }}</h5>
                    <p class="card-text">Selected Date</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table -->
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Teacher Attendance Records</h5>
                <div>
                    <span class="badge bg-light text-dark">{{ $attendances->total() }} Records</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">Photo</th>
                            <th>Teacher Name</th>
                            <th width="100">Employee Code</th>
                            <th width="120">Department</th>
                            <th width="100">Check In</th>
                            <th width="100">Check Out</th>
                            <th width="100">Working Hours</th>
                            <th width="80">Status</th>
                            <th width="120">Late Minutes</th>
                            <th width="80">Method</th>
                            <th width="100">Marked By</th>
                            <th width="80">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                        <tr>
                            <td>
                                @if($attendance->teacher->profile_photo)
                                    <img src="{{ asset('storage/' . $attendance->teacher->profile_photo) }}" 
                                         alt="{{ $attendance->teacher->name }}" 
                                         class="avatar-sm rounded-circle">
                                @else
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="fas fa-user-tie text-primary"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="fw-medium">{{ $attendance->teacher->name }}</div>
                                        <small class="text-muted">{{ $attendance->teacher->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $attendance->teacher->employee_code ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <small>{{ $attendance->teacher->department->name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($attendance->check_in_time)
                                    <span class="text-success fw-medium">{{ $attendance->check_in_time }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($attendance->check_out_time)
                                    <span class="text-info fw-medium">{{ $attendance->check_out_time }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $attendance->working_hours > 0 ? 'success' : 'secondary' }}">
                                    {{ $attendance->formatted_working_hours }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $attendance->getStatusBadgeColor() }}">
                                    {{ $attendance->getStatusDisplay() }}
                                </span>
                            </td>
                            <td>
                                @if($attendance->late_minutes > 0)
                                    <span class="badge bg-warning">{{ $attendance->late_minutes }} min</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $attendance->attendance_method === 'system' ? 'info' : 'primary' }}">
                                    {{ ucfirst($attendance->attendance_method) }}
                                </span>
                            </td>
                            <td>
                                @if($attendance->marker)
                                    <small>{{ $attendance->marker->name }}</small>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-info" onclick="viewDetails({{ $attendance->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-warning" onclick="editAttendance({{ $attendance->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No attendance records found for the selected criteria.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }} 
                        of {{ $attendances->total() }} entries
                    </small>
                </div>
                <div>
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Details Modal -->
<div class="modal fade" id="attendanceDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Attendance Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="attendanceDetailsContent">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    object-fit: cover;
}

.avatar-sm i {
    font-size: 1rem;
}

.badge {
    font-size: 0.75rem;
}

.card-body h5 {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.card-body p {
    font-size: 0.875rem;
    margin-bottom: 0;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
}

.table td {
    vertical-align: middle;
    font-size: 0.875rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.75rem;
    }
    
    .btn-group-sm .btn {
        padding: 0.125rem 0.25rem;
        font-size: 0.75rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
function viewDetails(attendanceId) {
    fetch(`/hr/teacher-attendance/${attendanceId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const remarksHtml = data.attendance.remarks ? `
                    <div class="mt-3">
                        <h6>Remarks</h6>
                        <p class="text-muted">${data.attendance.remarks}</p>
                    </div>
                ` : '';
                
                const content = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Teacher Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>${data.attendance.teacher.name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Employee Code:</strong></td>
                                    <td>${data.attendance.teacher.employee_code || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Department:</strong></td>
                                    <td>${data.attendance.teacher.department?.name || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>${data.attendance.teacher.email}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Attendance Details</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td><strong>Date:</strong></td>
                                    <td>${data.attendance.date}</td>
                                </tr>
                                <tr>
                                    <td><strong>Check In:</strong></td>
                                    <td>${data.attendance.check_in_time || 'Not checked in'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Check Out:</strong></td>
                                    <td>${data.attendance.check_out_time || 'Not checked out'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Working Hours:</strong></td>
                                    <td>${data.attendance.formatted_working_hours}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge bg-${data.attendance.status_color}">${data.attendance.status_display}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Late Minutes:</strong></td>
                                    <td>${data.attendance.late_minutes || 0}</td>
                                </tr>
                                <tr>
                                    <td><strong>Method:</strong></td>
                                    <td><span class="badge bg-${data.attendance.attendance_method === 'system' ? 'info' : 'primary'}">${data.attendance.attendance_method}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Marked By:</strong></td>
                                    <td>${data.attendance.marker?.name || 'System'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    ${remarksHtml}
                `;
                
                document.getElementById('attendanceDetailsContent').innerHTML = content;
                new bootstrap.Modal(document.getElementById('attendanceDetailsModal')).show();
            } else {
                alert('Failed to load attendance details');
            }
        })
        .catch(error => {
            console.error('Error loading attendance details:', error);
            alert('Failed to load attendance details. Please try again.');
        });
}

function editAttendance(attendanceId) {
    window.location.href = `/hr/teacher-attendance/${attendanceId}/edit`;
}

// Optional: Auto-refresh only the table data (not the whole page)
// This is a better approach than reloading the entire page
let autoRefreshInterval = null;

function startAutoRefresh() {
    // Clear existing interval if any
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    
    // Set new interval to refresh data every 30 seconds
    autoRefreshInterval = setInterval(() => {
        refreshAttendanceData();
    }, 30000);
}

function refreshAttendanceData() {
    // Get current filter values
    const date = document.getElementById('date')?.value || '';
    const departmentId = document.getElementById('department_id')?.value || '';
    const status = document.getElementById('status')?.value || 'all';
    
    // Build URL with current filters
    let url = window.location.pathname + '?';
    if (date) url += `date=${date}&`;
    if (departmentId) url += `department_id=${departmentId}&`;
    if (status) url += `status=${status}&`;
    
    // Remove trailing & if exists
    url = url.replace(/&$/, '');
    
    // Fetch updated data
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Extract just the table and KPI cards from the response
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Update KPI cards
        const kpiCards = doc.querySelectorAll('.row.mt-4 .card');
        const currentKPICards = document.querySelectorAll('.row.mt-4 .card');
        if (kpiCards.length === currentKPICards.length) {
            kpiCards.forEach((card, index) => {
                currentKPICards[index].innerHTML = card.innerHTML;
            });
        }
        
        // Update table body
        const newTableBody = doc.querySelector('.table tbody');
        const currentTableBody = document.querySelector('.table tbody');
        if (newTableBody && currentTableBody) {
            currentTableBody.innerHTML = newTableBody.innerHTML;
        }
        
        // Update pagination
        const newPagination = doc.querySelector('.card-footer .d-flex');
        const currentPagination = document.querySelector('.card-footer .d-flex');
        if (newPagination && currentPagination) {
            currentPagination.innerHTML = newPagination.innerHTML;
        }
        
        // Show notification
        showNotification('Data refreshed automatically', 'info');
    })
    .catch(error => {
        console.error('Error refreshing data:', error);
    });
}

function showNotification(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.style.animation = 'slideIn 0.3s ease-out';
    toast.innerHTML = `
        <i class="fas fa-${type === 'info' ? 'sync-alt fa-spin' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add CSS for notification animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);

// Add refresh button to page header
document.addEventListener('DOMContentLoaded', function() {
    // Add refresh button next to the Manual Entry button
    const pageHeader = document.querySelector('.d-flex.justify-content-between.align-items-center.mb-4 .text-end');
    if (pageHeader && !document.querySelector('#refreshDataBtn')) {
        const refreshBtn = document.createElement('button');
        refreshBtn.id = 'refreshDataBtn';
        refreshBtn.className = 'btn btn-outline-secondary me-2';
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Refresh';
        refreshBtn.onclick = () => refreshAttendanceData();
        pageHeader.insertBefore(refreshBtn, pageHeader.firstChild);
    }
    
    // Start auto-refresh (optional - comment out if not wanted)
    // startAutoRefresh();
    
    // Stop auto-refresh when page is hidden (good practice)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        } else {
            // startAutoRefresh(); // Uncomment if you want to restart when page becomes visible
        }
    });
});

// Clean up interval on page unload
window.addEventListener('beforeunload', function() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
});
</script>
@endpush
