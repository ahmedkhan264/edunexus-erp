@extends('layouts.app')

@section('title', 'Student Profile - ' . $student->full_name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Student Profile</h4>
            <p class="text-muted mb-0">Detailed information for {{ $student->full_name }}</p>
        </div>
        <div>
            <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Students
            </a>
            <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-primary ms-2">
                <i class="fas fa-edit me-2"></i>Edit Student
            </a>
        </div>
    </div>

    <!-- Student Profile Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    @if($student->profile_image)
                        <img src="{{ asset('storage/' . $student->profile_image) }}" 
                             alt="{{ $student->full_name }}" class="rounded-circle" width="80" height="80">
                    @else
                        <div class="avatar-xl bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            {{ strtoupper(substr($student->first_name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="col">
                    <h4 class="mb-1">{{ $student->full_name }}</h4>
                    <p class="text-muted mb-2">
                        <span class="badge bg-primary me-2">{{ $student->student_id }}</span>
                        <span class="badge bg-info me-2">{{ $student->admission_number }}</span>
                        {!! $student->status_badge !!}
                    </p>
                    <div class="row small text-muted">
                        <div class="col-md-3">
                            <i class="fas fa-graduation-cap me-1"></i>
                            {{ $student->class ? $student->class->name : 'Not Assigned' }}
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $student->formatted_admission_date }}
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-envelope me-1"></i>
                            {{ $student->email }}
                        </div>
                        <div class="col-md-3">
                            <i class="fas fa-phone me-1"></i>
                            {{ $student->formatted_phone }}
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
        <!-- Basic Information Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-user me-2"></i>Basic Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Full Name:</strong><br>
                            {{ $student->full_name }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Student ID:</strong><br>
                            {{ $student->student_id }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Admission Number:</strong><br>
                            {{ $student->admission_number }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Date of Birth:</strong><br>
                            {{ $student->formatted_date_of_birth }} ({{ $student->age }} years)
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Gender:</strong><br>
                            {!! $student->gender_badge !!}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Blood Group:</strong><br>
                            {{ $student->blood_group ?: 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Religion:</strong><br>
                            {{ $student->religion ?: 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Nationality:</strong><br>
                            {{ $student->nationality }}
                        </div>
                        <div class="col-12 mb-3">
                            <strong>Address:</strong><br>
                            {{ $student->full_address ?: 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guardian Information Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>Guardian Information
                    </h6>
                </div>
                <div class="card-body">
                    @if($student->parentProfile)
                        <div class="row">
                            <!-- Father Information -->
                            @if($student->parentProfile->father_name)
                                <div class="col-12 mb-4">
                                    <h6 class="text-primary mb-2">Father Information</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <strong>Name:</strong><br>
                                            {{ $student->parentProfile->father_name }}
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong>CNIC:</strong><br>
                                            {{ $student->parentProfile->father_cnic ?: 'N/A' }}
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong>Phone:</strong><br>
                                            {{ $student->parentProfile->father_phone ?: 'N/A' }}
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong>Occupation:</strong><br>
                                            {{ $student->parentProfile->father_occupation ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Mother Information -->
                            @if($student->parentProfile->mother_name)
                                <div class="col-12 mb-4">
                                    <h6 class="text-danger mb-2">Mother Information</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <strong>Name:</strong><br>
                                            {{ $student->parentProfile->mother_name }}
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong>CNIC:</strong><br>
                                            {{ $student->parentProfile->mother_cnic ?: 'N/A' }}
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong>Phone:</strong><br>
                                            {{ $student->parentProfile->mother_phone ?: 'N/A' }}
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong>Occupation:</strong><br>
                                            {{ $student->parentProfile->mother_occupation ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Guardian Information -->
                            @if($student->parentProfile->guardian_name)
                                <div class="col-12">
                                    <h6 class="text-info mb-2">Guardian Information</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <strong>Name:</strong><br>
                                            {{ $student->parentProfile->guardian_name }}
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong>Relation:</strong><br>
                                            {{ $student->parentProfile->guardian_relation ?: 'N/A' }}
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong>CNIC:</strong><br>
                                            {{ $student->parentProfile->guardian_cnic ?: 'N/A' }}
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <strong>Phone:</strong><br>
                                            {{ $student->parentProfile->guardian_phone ?: 'N/A' }}
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <strong>Address:</strong><br>
                                            {{ $student->parentProfile->guardian_address ?: 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Emergency Contact -->
                            <div class="col-12 mt-3">
                                <h6 class="text-warning mb-2">Emergency Contact</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <strong>Name:</strong><br>
                                        {{ $student->emergency_contact_name }}
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <strong>Phone:</strong><br>
                                        {{ $student->emergency_contact_phone }}
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <strong>Relation:</strong><br>
                                        {{ $student->emergency_contact_relation }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <p>No guardian information available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Academic Information Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-graduation-cap me-2"></i>Academic Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Current Class:</strong><br>
                            {{ $student->class ? $student->class->name : 'Not Assigned' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Grade Level:</strong><br>
                            {{ $student->class ? 'Grade ' . $student->class->grade_level : 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Section:</strong><br>
                            {{ $student->class ? $student->class->section : 'N/A' }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Admission Date:</strong><br>
                            {{ $student->formatted_admission_date }}
                        </div>
                        @if($student->graduation_date)
                            <div class="col-md-6 mb-3">
                                <strong>Graduation Date:</strong><br>
                                {{ $student->graduation_date->format('M d, Y') }}
                            </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <strong>Status:</strong><br>
                            {!! $student->status_badge !!}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Active Status:</strong><br>
                            {{ $student->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' }}
                        </div>
                        @if($student->previous_school_name)
                            <div class="col-12 mb-3">
                                <strong>Previous School:</strong><br>
                                {{ $student->previous_school_name }}
                            </div>
                        @endif
                        @if($student->previous_school_gpa)
                            <div class="col-md-6 mb-3">
                                <strong>Previous GPA:</strong><br>
                                {{ $student->previous_school_gpa }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Summary Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar-check me-2"></i>Attendance Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-3">
                            <div class="text-success">
                                <h4 class="mb-0">{{ $attendanceStats['present'] }}</h4>
                                <small>Present</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-danger">
                                <h4 class="mb-0">{{ $attendanceStats['absent'] }}</h4>
                                <small>Absent</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-warning">
                                <h4 class="mb-0">{{ $attendanceStats['late'] }}</h4>
                                <small>Late</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-info">
                                <h4 class="mb-0">{{ $attendanceStats['percentage'] }}%</h4>
                                <small>Attendance</small>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Attendance -->
                    <h6 class="mb-3">Recent Attendance</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Check In</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAttendance as $attendance)
                                    <tr>
                                        <td>{{ $attendance->date->format('M d, Y') }}</td>
                                        <td>{!! $attendance->status_badge !!}</td>
                                        <td>{{ $attendance->check_in_time ? $attendance->check_in_time->format('h:i A') : 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="#" class="btn btn-outline-primary btn-sm">View Full Attendance</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fee Ledger Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-money-bill-wave me-2"></i>Fee Ledger
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-money-bill-wave fa-3x mb-3"></i>
                        <h6>Fee Management Module</h6>
                        <p class="small">Fee tracking and challan generation will be available in the next module update.</p>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="border rounded p-2">
                                    <h6 class="text-primary mb-0">0</h6>
                                    <small>Total Fees</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2">
                                    <h6 class="text-success mb-0">0</h6>
                                    <small>Paid</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2">
                                    <h6 class="text-danger mb-0">0</h6>
                                    <small>Pending</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Card -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Academic Results
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <h6>Result Management Module</h6>
                        <p class="small">Academic results and grade reports will be available in the next module update.</p>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="border rounded p-2">
                                    <h6 class="text-info mb-0">0</h6>
                                    <small>Exams</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2">
                                    <h6 class="text-warning mb-0">0%</h6>
                                    <small>Average</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-2">
                                    <h6 class="text-success mb-0">A+</h6>
                                    <small>Grade</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Card -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-file-alt me-2"></i>Documents & Attachments
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-file-alt fa-3x mb-3"></i>
                        <h6>Document Management Module</h6>
                        <p class="small">Student documents and file management will be available in the next module update.</p>
                        <div class="row mt-3">
                            <div class="col-md-2">
                                <div class="border rounded p-3 text-center">
                                    <i class="fas fa-id-card fa-2x text-primary mb-2"></i>
                                    <div class="small">Birth Certificate</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="border rounded p-3 text-center">
                                    <i class="fas fa-file-alt fa-2x text-success mb-2"></i>
                                    <div class="small">Previous Report</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="border rounded p-3 text-center">
                                    <i class="fas fa-id-badge fa-2x text-info mb-2"></i>
                                    <div class="small">Student ID</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="border rounded p-3 text-center">
                                    <i class="fas fa-file-contract fa-2x text-warning mb-2"></i>
                                    <div class="small">Agreement</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="border rounded p-3 text-center">
                                    <i class="fas fa-medical fa-2x text-danger mb-2"></i>
                                    <div class="small">Medical</div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="border rounded p-3 text-center">
                                    <i class="fas fa-plus fa-2x text-secondary mb-2"></i>
                                    <div class="small">Add More</div>
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
                    @if($student->notes)
                        <p>{{ $student->notes }}</p>
                    @else
                        <p class="text-muted">No notes available for this student.</p>
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
