@extends('layouts.app')

@section('title', 'Attendance - ' . $student->user->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Attendance - {{ $student->user->name }}</h1>
            <p class="text-muted mb-0">Grade {{ $student->schoolClass->grade_level }} - {{ $student->schoolClass->section }}</p>
        </div>
        <div class="text-end">
            <a href="{{ route('parent.dashboard', ['child_id' => $student->id]) }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <button class="btn btn-outline-success" onclick="printAttendance()">
                <i class="fas fa-print me-2"></i>Print
            </button>
        </div>
    </div>

    <!-- Month/Year Selector -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label for="monthSelector" class="form-label">Select Month:</label>
                    <select class="form-select" id="monthSelector" onchange="changeMonth()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $currentDate->month == $m ? 'selected' : '' }}>
                                {{ Carbon::createFromDate($currentDate->year, $m, 1)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="yearSelector" class="form-label">Select Year:</label>
                    <select class="form-select" id="yearSelector" onchange="changeYear()">
                        @for($y = Carbon::now()->year - 2; $y <= Carbon::now()->year + 1; $y++)
                            <option value="{{ $y }}" {{ $currentDate->year == $y ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div class="text-start">
                            <a href="{{ route('parent.attendance', [$student->id, 'month' => $navigationData['previous_month']['month'], 'year' => $navigationData['previous_month']['year']]) }}" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-chevron-left me-2"></i>{{ $navigationData['previous_month']['name'] }}
                            </a>
                        </div>
                        <div class="text-center">
                            <h5 class="mb-0">{{ $navigationData['current_month_name'] }} {{ $navigationData['current_year'] }}</h5>
                        </div>
                        <div class="text-end">
                            @if($navigationData['next_month']['allowed'])
                                <a href="{{ route('parent.attendance', [$student->id, 'month' => $navigationData['next_month']['month'], 'year' => $navigationData['next_month']['year']]) }}" 
                                   class="btn btn-outline-primary">
                                    {{ $navigationData['next_month']['name'] }}<i class="fas fa-chevron-right ms-2"></i>
                                </a>
                            @else
                                <button class="btn btn-outline-secondary" disabled>
                                    {{ $navigationData['next_month']['name'] }}<i class="fas fa-chevron-right ms-2"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Summary Card -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0">
                <i class="fas fa-chart-pie me-2"></i>Monthly Summary
            </h6>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-2 col-4 mb-3">
                    <div class="stat-box">
                        <div class="h4 mb-0 text-primary">{{ $monthlyStats['working_days'] }}</div>
                        <small class="text-muted">Working Days</small>
                    </div>
                </div>
                <div class="col-md-2 col-4 mb-3">
                    <div class="stat-box">
                        <div class="h4 mb-0 text-success">{{ $monthlyStats['present_days'] }}</div>
                        <small class="text-muted">Present</small>
                    </div>
                </div>
                <div class="col-md-2 col-4 mb-3">
                    <div class="stat-box">
                        <div class="h4 mb-0 text-danger">{{ $monthlyStats['absent_days'] }}</div>
                        <small class="text-muted">Absent</small>
                    </div>
                </div>
                <div class="col-md-2 col-4 mb-3">
                    <div class="stat-box">
                        <div class="h4 mb-0 text-warning">{{ $monthlyStats['late_days'] }}</div>
                        <small class="text-muted">Late</small>
                    </div>
                </div>
                <div class="col-md-2 col-4 mb-3">
                    <div class="stat-box">
                        <div class="h4 mb-0 text-secondary">{{ $monthlyStats['holiday_days'] }}</div>
                        <small class="text-muted">Holidays</small>
                    </div>
                </div>
                <div class="col-md-2 col-4 mb-3">
                    <div class="stat-box">
                        <div class="h4 mb-0 text-info">{{ $monthlyStats['attendance_percentage'] }}%</div>
                        <small class="text-muted">Attendance</small>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Progress Bar -->
            <div class="mt-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted">Attendance Rate</small>
                    <small class="text-muted">{{ $monthlyStats['attendance_percentage'] }}%</small>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" style="width: {{ $monthlyStats['attendance_percentage'] }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar View -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-calendar-alt me-2"></i>Attendance Calendar
            </h6>
        </div>
        <div class="card-body">
            <!-- Calendar Grid -->
            <div class="attendance-calendar">
                <!-- Weekday Headers -->
                <div class="calendar-weekdays">
                    <div class="weekday-header">Sun</div>
                    <div class="weekday-header">Mon</div>
                    <div class="weekday-header">Tue</div>
                    <div class="weekday-header">Wed</div>
                    <div class="weekday-header">Thu</div>
                    <div class="weekday-header">Fri</div>
                    <div class="weekday-header">Sat</div>
                </div>
                
                <!-- Calendar Days -->
                <div class="calendar-days">
                    @foreach($calendarData as $dayData)
                        @if($dayData['day'])
                            <div class="calendar-day 
                                {{ $dayData['status'] === 'present' ? 'present' : '' }}
                                {{ $dayData['status'] === 'absent' ? 'absent' : '' }}
                                {{ $dayData['status'] === 'late' ? 'late' : '' }}
                                {{ $dayData['status'] === 'holiday' ? 'holiday' : '' }}
                                {{ $dayData['status'] === 'not_marked' ? 'not-marked' : '' }}
                                {{ $dayData['is_today'] ? 'today' : '' }}
                                {{ $dayData['is_weekend'] ? 'weekend' : '' }}"
                                 onclick="showDayDetails({{ $dayData['day'] }}, '{{ $dayData['status'] }}')"
                                 title="{{ $dayData['date'] ? $dayData['date']->format('M j, Y') : '' }}">
                                <div class="day-number">{{ $dayData['day'] }}</div>
                                <div class="day-status">
                                    @if($dayData['status'] === 'present')
                                        <i class="fas fa-check text-success"></i>
                                    @elseif($dayData['status'] === 'absent')
                                        <i class="fas fa-times text-danger"></i>
                                    @elseif($dayData['status'] === 'late')
                                        <i class="fas fa-clock text-warning"></i>
                                    @elseif($dayData['status'] === 'holiday')
                                        <i class="fas fa-home text-secondary"></i>
                                    @elseif($dayData['status'] === 'not_marked')
                                        <i class="fas fa-question text-info"></i>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="calendar-day empty"></div>
                        @endif
                    @endforeach
                </div>
            </div>
            
            <!-- Legend -->
            <div class="calendar-legend mt-4">
                <h6 class="text-muted mb-3">Legend:</h6>
                <div class="row">
                    <div class="col-md-2 col-4 mb-2">
                        <div class="legend-item">
                            <div class="legend-color present"></div>
                            <small>Present</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-2">
                        <div class="legend-item">
                            <div class="legend-color absent"></div>
                            <small>Absent</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-2">
                        <div class="legend-item">
                            <div class="legend-color late"></div>
                            <small>Late</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-2">
                        <div class="legend-item">
                            <div class="legend-color holiday"></div>
                            <small>Holiday</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-2">
                        <div class="legend-item">
                            <div class="legend-color not-marked"></div>
                            <small>Not Marked</small>
                        </div>
                    </div>
                    <div class="col-md-2 col-4 mb-2">
                        <div class="legend-item">
                            <div class="legend-color today"></div>
                            <small>Today</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Day Details Modal -->
<div class="modal fade" id="dayDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Attendance Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dayDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style media="print">
    .no-print {
        display: none !important;
    }
    
    .attendance-calendar {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    
    .card {
        break-inside: avoid;
        page-break-inside: avoid;
        margin-bottom: 1rem;
    }
    
    .calendar-day:hover {
        transform: none !important;
        box-shadow: none !important;
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
.attendance-calendar {
    margin-top: 1rem;
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background-color: #dee2e6;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    overflow: hidden;
}

.weekday-header {
    background-color: #f8f9fa;
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background-color: #dee2e6;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    overflow: hidden;
    margin-top: 1px;
}

.calendar-day {
    background-color: white;
    padding: 0.5rem;
    text-align: center;
    min-height: 60px;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.calendar-day:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.calendar-day.empty {
    background-color: #f8f9fa;
    cursor: default;
}

.calendar-day.empty:hover {
    transform: none;
    box-shadow: none;
}

.calendar-day.present {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
}

.calendar-day.absent {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
}

.calendar-day.late {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
}

.calendar-day.holiday {
    background-color: #e2e3e5;
    border: 1px solid #d6d8db;
}

.calendar-day.not-marked {
    background-color: #d1ecf1;
    border: 1px solid #bee5eb;
}

.calendar-day.today {
    border: 2px solid #007bff;
    font-weight: bold;
}

.calendar-day.weekend {
    background-color: #f8f9fa;
    color: #6c757d;
}

.day-number {
    font-weight: 600;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.day-status {
    font-size: 0.75rem;
}

.calendar-legend {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 0.25rem;
    border: 1px solid #dee2e6;
}

.legend-color.present {
    background-color: #d4edda;
}

.legend-color.absent {
    background-color: #f8d7da;
}

.legend-color.late {
    background-color: #fff3cd;
}

.legend-color.holiday {
    background-color: #e2e3e5;
}

.legend-color.not-marked {
    background-color: #d1ecf1;
}

.legend-color.today {
    background-color: white;
    border: 2px solid #007bff;
}

.stat-box {
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: transform 0.2s ease;
}

.stat-box:hover {
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .calendar-weekdays,
    .calendar-days {
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5px;
    }
    
    .weekday-header {
        font-size: 0.75rem;
        padding: 0.5rem;
    }
    
    .calendar-day {
        min-height: 50px;
        padding: 0.25rem;
    }
    
    .day-number {
        font-size: 0.75rem;
    }
    
    .day-status {
        font-size: 0.625rem;
    }
    
    .legend-item {
        margin-bottom: 0.25rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
});

function initializeCalendar() {
    // Auto-refresh every 5 minutes
    setInterval(refreshCalendar, 300000);
}

function changeMonth() {
    const month = document.getElementById('monthSelector').value;
    const year = document.getElementById('yearSelector').value;
    const studentId = {{ $student->id }};
    
    window.location.href = `/parent/children/${studentId}/attendance?month=${month}&year=${year}`;
}

function changeYear() {
    const month = document.getElementById('monthSelector').value;
    const year = document.getElementById('yearSelector').value;
    const studentId = {{ $student->id }};
    
    window.location.href = `/parent/children/${studentId}/attendance?month=${month}&year=${year}`;
}

function showDayDetails(day, status) {
    const modal = new bootstrap.Modal(document.getElementById('dayDetailsModal'));
    const content = document.getElementById('dayDetailsContent');
    
    // Show loading state
    content.innerHTML = '<div class="text-center py-4"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    // Generate day details based on status
    setTimeout(() => {
        const statusInfo = getStatusInfo(status);
        content.innerHTML = `
            <div class="text-center">
                <div class="mb-3">
                    <div class="display-4 text-${statusInfo.color}">
                        <i class="${statusInfo.icon}"></i>
                    </div>
                </div>
                <h5>Day ${day} - {{ $currentDate->format('F') }} {{ $currentDate->year }}</h5>
                <p class="text-muted mb-3">${statusInfo.description}</p>
                <div class="alert alert-${statusInfo.color}">
                    <strong>Status:</strong> ${statusInfo.title}
                </div>
                ${statusInfo.additional_info ? `<div class="alert alert-info">${statusInfo.additional_info}</div>` : ''}
            </div>
        `;
    }, 300);
    
    modal.show();
}

function getStatusInfo(status) {
    const statusMap = {
        'present': {
            title: 'Present',
            color: 'success',
            icon: 'fas fa-check-circle',
            description: 'Student was present on this day.',
            additional_info: 'Great job! Keep up the good attendance record.'
        },
        'absent': {
            title: 'Absent',
            color: 'danger',
            icon: 'fas fa-times-circle',
            description: 'Student was absent on this day.',
            additional_info: 'Please ensure your child attends school regularly.'
        },
        'late': {
            title: 'Late',
            color: 'warning',
            icon: 'fas fa-clock',
            description: 'Student was late on this day.',
            additional_info: 'Try to arrive on time for better learning opportunities.'
        },
        'holiday': {
            title: 'Holiday',
            color: 'secondary',
            icon: 'fas fa-home',
            description: 'This was a holiday or weekend.',
            additional_info: 'No classes scheduled for this day.'
        },
        'not_marked': {
            title: 'Not Marked',
            color: 'info',
            icon: 'fas fa-question-circle',
            description: 'Attendance not yet marked for this day.',
            additional_info: 'Attendance will be updated by the teacher.'
        }
    };
    
    return statusMap[status] || statusMap['not_marked'];
}

function refreshCalendar() {
    // Refresh the current page to get updated data
    window.location.reload();
}

function printAttendance() {
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
