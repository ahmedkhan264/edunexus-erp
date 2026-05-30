@extends('layouts.app')

@section('title', 'Student Attendance Report')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Student Attendance Report</h1>
            <p class="text-muted mb-0">Comprehensive attendance analysis and reporting</p>
        </div>
        <div class="text-end">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" onclick="exportToExcel()">
                    <i class="fas fa-file-excel me-2"></i>Export Excel
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="exportToPdf()">
                    <i class="fas fa-file-pdf me-2"></i>Export PDF
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Report Filters</h5>
        </div>
        <div class="card-body">
            <form id="filterForm">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="from_date" class="form-label">From Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="from_date" name="from_date" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="to_date" class="form-label">To Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="to_date" name="to_date" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="class_id" class="form-label">Class</label>
                        <select class="form-select" id="class_id" name="class_id">
                            <option value="">All Classes</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} (Grade {{ $class->grade_level }})
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="section_id" class="form-label">Section</label>
                        <select class="form-select" id="section_id" name="section_id" disabled>
                            <option value="">All Sections</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="student_id" class="form-label">Student</label>
                        <select class="form-select" id="student_id" name="student_id" disabled>
                            <option value="">All Students</option>
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Generate Report
                        </button>
                        <button type="button" class="btn btn-outline-secondary ms-2" onclick="resetFilters()">
                            <i class="fas fa-redo me-2"></i>Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    @if(isset($reportData))
    <div class="row mt-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">{{ $reportData['summary']['total_students'] ?? 0 }}</h5>
                    <p class="card-text">Total Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">{{ $reportData['summary']['total_present'] ?? 0 }}</h5>
                    <p class="card-text">Present Days</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">{{ $reportData['summary']['total_absent'] ?? 0 }}</h5>
                    <p class="card-text">Absent Days</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">{{ $reportData['summary']['total_late'] ?? 0 }}</h5>
                    <p class="card-text">Late Days</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">{{ number_format($reportData['summary']['average_attendance'] ?? 0, 1) }}%</h5>
                    <p class="card-text">Avg Attendance</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5 class="card-title">{{ $reportData['date_range']['days'] ?? 0 }}</h5>
                    <p class="card-text">Total Days</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="card mt-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Attendance Trend</h5>
        </div>
        <div class="card-body">
            <canvas id="attendanceChart" height="80"></canvas>
        </div>
    </div>

    <!-- Student Table -->
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Student-wise Attendance</h5>
                <div>
                    <span class="badge bg-light text-dark">{{ count($reportData['students'] ?? []) }} Students</span>
                    <span class="badge bg-warning ms-1">{{ $reportData['summary']['perfect_attendance'] ?? 0 }} Perfect</span>
                    <span class="badge bg-danger ms-1">{{ $reportData['summary']['low_attendance'] ?? 0 }} Low</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Roll No</th>
                            <th>Student Name</th>
                            <th width="100">Class</th>
                            <th width="80">Total</th>
                            <th width="80">Present</th>
                            <th width="80">Absent</th>
                            <th width="80">Late</th>
                            <th width="120">Attendance %</th>
                            <th width="100">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData['students'] ?? [] as $student)
                        <tr>
                            <td>{{ $student['roll_number'] ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                        <i class="fas fa-user text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $student['name'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $student['class_name'] }}</td>
                            <td>{{ $student['total_days'] }}</td>
                            <td>
                                <span class="badge bg-success">{{ $student['present_days'] }}</span>
                            </td>
                            <td>
                                <span class="badge bg-danger">{{ $student['absent_days'] }}</span>
                            </td>
                            <td>
                                <span class="badge bg-warning">{{ $student['late_days'] }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                        <div class="progress-bar bg-{{ $student['status_color'] ?? 'secondary' }}" 
                                             style="width: {{ $student['attendance_percentage'] ?? 0 }}%"></div>
                                    </div>
                                    <small>{{ number_format($student['attendance_percentage'] ?? 0, 1) }}%</small>
                                </div>
                            </td>
                            <td>
                                @php
                                    $percentage = $student['attendance_percentage'] ?? 0;
                                    $statusText = $percentage >= 95 ? 'Excellent' : ($percentage >= 85 ? 'Good' : ($percentage >= 75 ? 'Average' : 'Poor'));
                                    $statusColor = $student['status_color'] ?? ($percentage >= 95 ? 'success' : ($percentage >= 85 ? 'info' : ($percentage >= 75 ? 'warning' : 'danger')));
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">
                                    {{ $statusText }}
                                </span>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-chart-line me-2"></i>No attendance data found for the selected criteria
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @else
    <div class="alert alert-info mt-4">
        <i class="fas fa-info-circle me-2"></i>Please select date range and generate report to view attendance data.
    </div>
    @endif
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <div class="mt-2">Generating report...</div>
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

.progress {
    background-color: #e9ecef;
}

.avatar-sm {
    width: 32px;
    height: 32px;
}

@media print {
    .no-print {
        display: none !important;
    }
    
    .card {
        page-break-inside: avoid;
    }
}
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const loadingOverlay = document.getElementById('loadingOverlay');
    
    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);
    
    document.getElementById('from_date').value = thirtyDaysAgo.toISOString().split('T')[0];
    document.getElementById('to_date').value = today.toISOString().split('T')[0];
    
    // Load sections when class changes
    document.getElementById('class_id').addEventListener('change', function() {
        const classId = this.value;
        const sectionSelect = document.getElementById('section_id');
        const studentSelect = document.getElementById('student_id');
        
        if (classId) {
            loadSections(classId);
            loadStudents(classId);
        } else {
            sectionSelect.innerHTML = '<option value="">All Sections</option>';
            sectionSelect.disabled = true;
            studentSelect.innerHTML = '<option value="">All Students</option>';
            studentSelect.disabled = true;
        }
    });
    
    // Handle form submission
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        generateReport();
    });
    
    // Initialize chart if data exists
    @if(isset($reportData))
    initializeChart();
    @endif
});

function loadSections(classId) {
    fetch(`/admin/attendance/students/sections/${classId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const sectionSelect = document.getElementById('section_id');
                sectionSelect.innerHTML = '<option value="">All Sections</option>';
                data.sections.forEach(section => {
                    sectionSelect.innerHTML += `<option value="${section.id}">${section.name}</option>`;
                });
                sectionSelect.disabled = false;
            }
        })
        .catch(error => console.error('Error loading sections:', error));
}

function loadStudents(classId) {
    fetch(`/api/students/by-class/${classId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const studentSelect = document.getElementById('student_id');
                studentSelect.innerHTML = '<option value="">All Students</option>';
                data.students.forEach(student => {
                    studentSelect.innerHTML += `<option value="${student.id}">${student.name}</option>`;
                });
                studentSelect.disabled = false;
            }
        })
        .catch(error => console.error('Error loading students:', error));
}

function generateReport() {
    showLoading();

    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);

    window.location.href = window.location.pathname + '?' + params.toString();
}

function resetFilters() {
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);
    
    document.getElementById('from_date').value = thirtyDaysAgo.toISOString().split('T')[0];
    document.getElementById('to_date').value = today.toISOString().split('T')[0];
    document.getElementById('class_id').value = '';
    document.getElementById('section_id').value = '';
    document.getElementById('student_id').value = '';
    
    document.getElementById('section_id').disabled = true;
    document.getElementById('student_id').disabled = true;
}

function exportToExcel() {
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    window.open(`/reports/student-attendance/export/excel?${params.toString()}`);
}

function exportToPdf() {
    // Get values
    const fromDate = document.getElementById('from_date').value;
    const toDate = document.getElementById('to_date').value;
    const classId = document.getElementById('class_id').value;
    const sectionId = document.getElementById('section_id').value;
    const studentId = document.getElementById('student_id').value;
    
    if (!fromDate || !toDate) {
        alert('Please select both From Date and To Date');
        return;
    }
    
    // Use Laravel's named route to generate URL
    let url = '{{ route("reports.student-attendance.pdf") }}?';
    url += `from_date=${fromDate}&to_date=${toDate}`;
    
    if (classId && classId !== '') {
        url += `&class_id=${classId}`;
    }
    if (sectionId && sectionId !== '') {
        url += `&section_id=${sectionId}`;
    }
    if (studentId && studentId !== '') {
        url += `&student_id=${studentId}`;
    }
    
    window.open(url, '_blank');
}
function initializeChart() {
    const canvas = document.getElementById('attendanceChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    
    // Get chart data from server
    const formData = new FormData(document.getElementById('filterForm'));
    const params = new URLSearchParams(formData);
    
    fetch(`/reports/student-attendance/chart-data?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                new Chart(ctx, {
                    type: 'bar',
                    data: data.chartData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Students'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            }
                        }
                    }
                });
            }
        })
        .catch(error => console.error('Error loading chart data:', error));
}

function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}
</script>
@endpush