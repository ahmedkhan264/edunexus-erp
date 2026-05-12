@extends('layouts.app')

@section('title', 'Teacher Profile - ' . $teacher->full_name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Teacher Profile</h4>
            <p class="text-muted mb-0">Detailed information for {{ $teacher->full_name }}</p>
        </div>
        <div>
            <a href="{{ route('admin.teachers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Teachers
            </a>
            <a href="{{ route('admin.teachers.edit', $teacher) }}" class="btn btn-primary ms-2">
                <i class="fas fa-edit me-2"></i>Edit Teacher
            </a>
        </div>
    </div>

    <!-- Teacher Profile Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    {!! $teacher->profile_avatar !!}
                </div>
                <div class="col">
                    <h4 class="mb-1">{{ $teacher->full_name }}</h4>
                    <p class="text-muted mb-2">
                        <span class="badge bg-primary me-2">{{ $teacher->employee_code }}</span>
                        {!! $teacher->employment_type_badge !!}
                        {!! $teacher->status_badge !!}
                    </p>
                    <div class="row small text-muted">
                        <div class="col-md-3">
                            <i class="fas fa-graduation-cap me-1"></i>
                            {{ $teacher->qualification }}
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $teacher->formatted_joining_date }}
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-envelope me-1"></i>
                            {{ $teacher->email }}
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-phone me-1"></i>
                            {{ $teacher->formatted_phone }}
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-print me-1"></i>Print Profile
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-envelope me-1"></i>Send Email
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Cards Grid -->
    <div class="row">
        <!-- Personal Information Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>Personal Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Full Name:</strong><br>
                            {{ $teacher->full_name }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Employee Code:</strong><br>
                            {{ $teacher->employee_code }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date of Birth:</strong><br>
                            {{ $teacher->formatted_date_of_birth }} ({{ $teacher->age }} years)
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Gender:</strong><br>
                            {!! $teacher->gender_badge !!}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>CNIC:</strong><br>
                            {{ $teacher->cnic }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Blood Group:</strong><br>
                            {{ $teacher->blood_group ?: 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Religion:</strong><br>
                            {{ $teacher->religion ?: 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Nationality:</strong><br>
                            {{ $teacher->nationality }}
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Address:</strong><br>
                            {{ $teacher->full_address ?: 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Information Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Professional Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Qualification:</strong><br>
                            {{ $teacher->qualification }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Specialization:</strong><br>
                            {{ $teacher->specialization ?: 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Experience:</strong><br>
                            {{ $teacher->experience }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Previous Institution:</strong><br>
                            {{ $teacher->previous_institution ?: 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Joining Date:</strong><br>
                            {{ $teacher->formatted_joining_date }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Years of Service:</strong><br>
                            {{ $teacher->years_of_service }} years
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Employment Type:</strong><br>
                            {!! $teacher->employment_type_badge !!}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong><br>
                            {!! $teacher->status_badge !!}
                        </div>
                        @if($teacher->hasResigned())
                            <div class="col-12 mb-3">
                                <strong>Resignation Date:</strong><br>
                                {{ $teacher->resignation_date->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Subject Assignments Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-book me-2"></i>Subject Assignments
                    </h6>
                </div>
                <div class="card-body">
                    @if($teacher->subjects->count() > 0)
                        <div class="row">
                            @foreach($teacher->subjects as $subject)
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-2">
                                        <div class="fw-semibold">{{ $subject->name }}</div>
                                        <small class="text-muted">{{ $subject->code }}</small>
                                        <br>{!! $subject->department_badge !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-muted">Total: {{ $teacher->subjects->count() }} subjects assigned</small>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-book fa-3x mb-3"></i>
                            <p>No subjects assigned to this teacher</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Class Assignments Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-chalkboard me-2"></i>Class Assignments
                    </h6>
                </div>
                <div class="card-body">
                    @if($teacher->classes->count() > 0)
                        <div class="row">
                            @foreach($teacher->classes as $class)
                                <div class="col-md-6 mb-3">
                                    <div class="border rounded p-2">
                                        <div class="fw-semibold">{{ $class->name }}</div>
                                        <small class="text-muted">Grade {{ $class->grade_level }} - Section {{ $class->section }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-muted">Total: {{ $teacher->classes->count() }} classes assigned</small>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-chalkboard fa-3x mb-3"></i>
                            <p>No classes assigned to this teacher</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Salary Information Card (Role-based visibility) -->
        @if(auth()->user()->role->slug === 'super_admin' || auth()->user()->role->slug === 'principal' || auth()->user()->role->slug === 'hr_manager')
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-money-bill-wave me-2"></i>Salary Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Basic Salary:</strong><br>
                                <span class="text-success fw-bold">{{ $teacher->formatted_salary }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Employment Type:</strong><br>
                                {!! $teacher->employment_type_badge !!}
                            </div>
                            <div class="col-12 mb-3">
                                <strong>Monthly Gross:</strong><br>
                                <span class="text-primary fw-bold">PKR {{ number_format($teacher->basic_salary, 2) }}</span>
                                <small class="text-muted">(Basic only - allowances not calculated)</small>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Complete salary breakdown including allowances, deductions, and net salary will be available in the HR & Salary module.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Attendance Summary Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>Attendance Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-check fa-3x mb-3"></i>
                        <h6>Attendance Module</h6>
                        <p class="small">Teacher attendance tracking and summary will be available in the next module update.</p>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="border rounded p-2 text-center">
                                    <h6 class="text-success mb-0">0</h6>
                                    <small>Present</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2 text-center">
                                    <h6 class="text-danger mb-0">0</h6>
                                    <small>Absent</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2 text-center">
                                    <h6 class="text-info mb-0">0%</h6>
                                    <small>Attendance</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timetable Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Weekly Timetable
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-clock fa-3x mb-3"></i>
                        <h6>Timetable Module</h6>
                        <p class="small">Weekly timetable and schedule management will be available in the next module update.</p>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center">
                                    <h6 class="text-primary mb-0">Mon</h6>
                                    <small>0 Classes</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center">
                                    <h6 class="text-success mb-0">Tue</h6>
                                    <small>0 Classes</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center">
                                    <h6 class="text-warning mb-0">Wed</h6>
                                    <small>0 Classes</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center">
                                    <h6 class="text-info mb-0">Thu</h6>
                                    <small>0 Classes</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Summary Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>Task Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-tasks fa-3x mb-3"></i>
                        <h6>Task Management Module</h6>
                        <p class="small">Teacher task assignment and tracking will be available in the next module update.</p>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="border rounded p-2 text-center">
                                    <h6 class="text-success mb-0">0</h6>
                                    <small>Completed</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2 text-center">
                                    <h6 class="text-warning mb-0">0</h6>
                                    <small>In Progress</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2 text-center">
                                    <h6 class="text-danger mb-0">0</h6>
                                    <small>Pending</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes Card -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-sticky-note me-2"></i>Notes & Remarks
                    </h6>
                </div>
                <div class="card-body">
                    @if($teacher->notes)
                        <p>{{ $teacher->notes }}</p>
                    @else
                        <p class="text-muted">No notes available for this teacher.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Print profile functionality
    document.querySelector('.btn-outline-primary').addEventListener('click', function() {
        window.print();
    });

    // Send email functionality (placeholder)
    document.querySelector('.btn-outline-success').addEventListener('click', function() {
        alert('Email functionality will be implemented in the communication module.');
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endsection
