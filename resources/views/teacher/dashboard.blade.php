@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Teacher Dashboard</h1>
            <p class="text-muted mb-0">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary">{{ now()->format('l, F j, Y') }}</span>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Today's Classes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">4</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Students
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">156</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Lessons Created
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">23</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Attendance Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">92%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if(Route::has('teacher.lms.lessons.create'))
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('teacher.lms.lessons.create') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-plus me-2"></i>Upload Lesson
                        </a>
                    </div>
                    @else
                    <div class="col-lg-3 col-md-6 mb-3">
                        <button class="btn btn-secondary btn-block" disabled>
                            <i class="fas fa-plus me-2"></i>Upload Lesson (Soon)
                        </button>
                    </div>
                    @endif

                    @if(Route::has('teacher.attendance.students'))
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('teacher.attendance.students') }}" class="btn btn-success btn-block">
                            <i class="fas fa-user-check me-2"></i>Mark Attendance
                        </a>
                    </div>
                    @else
                    <div class="col-lg-3 col-md-6 mb-3">
                        <button class="btn btn-secondary btn-block" disabled>
                            <i class="fas fa-user-check me-2"></i>Mark Attendance (Soon)
                        </button>
                    </div>
                    @endif

                    @if(Route::has('teacher.attendance.checkin'))
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('teacher.attendance.checkin') }}" class="btn btn-info btn-block">
                            <i class="fas fa-clock me-2"></i>Check In/Out
                        </a>
                    </div>
                    @else
                    <div class="col-lg-3 col-md-6 mb-3">
                        <button class="btn btn-secondary btn-block" disabled>
                            <i class="fas fa-clock me-2"></i>Check In/Out (Soon)
                        </button>
                    </div>
                    @endif

                    @if(Route::has('teacher.attendance.corrections'))
                    <div class="col-lg-3 col-md-6 mb-3">
                        <a href="{{ route('teacher.attendance.corrections') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-edit me-2"></i>Request Correction
                        </a>
                    </div>
                    @else
                    <div class="col-lg-3 col-md-6 mb-3">
                        <button class="btn btn-secondary btn-block" disabled>
                            <i class="fas fa-edit me-2"></i>Request Correction (Soon)
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Today's Schedule -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Today's Schedule</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Room</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>8:00 - 9:00 AM</td>
                                    <td>Grade 10 - A</td>
                                    <td>Mathematics</td>
                                    <td>Room 201</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Mark Attendance</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>9:30 - 10:30 AM</td>
                                    <td>Grade 10 - B</td>
                                    <td>Mathematics</td>
                                    <td>Room 202</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Mark Attendance</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>11:00 AM - 12:00 PM</td>
                                    <td>Grade 9 - A</td>
                                    <td>Physics</td>
                                    <td>Room 301</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Mark Attendance</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>1:00 - 2:00 PM</td>
                                    <td>Grade 9 - B</td>
                                    <td>Physics</td>
                                    <td>Room 302</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary">Mark Attendance</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-upload text-primary me-2"></i>
                                <span>Uploaded lesson: "Algebra Basics"</span>
                            </div>
                            <small class="text-muted">2h ago</small>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-user-check text-success me-2"></i>
                                <span>Marked attendance for Grade 10-A</span>
                            </div>
                            <small class="text-muted">3h ago</small>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-clock text-info me-2"></i>
                                <span>Checked in at 8:45 AM</span>
                            </div>
                            <small class="text-muted">Today</small>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-edit text-warning me-2"></i>
                                <span>Requested attendance correction</span>
                            </div>
                            <small class="text-muted">Yesterday</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcements -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Announcements</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" role="alert">
                        <h6 class="alert-heading"><i class="fas fa-bullhorn me-2"></i>Staff Meeting Tomorrow</h6>
                        <p class="mb-0">There will be a staff meeting tomorrow at 3:00 PM in the conference room. Please be on time.</p>
                        <hr>
                        <small class="text-muted">Posted 2 days ago by Principal</small>
                    </div>
                    <div class="alert alert-warning" role="alert">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Schedule Change</h6>
                        <p class="mb-0">Grade 10 Mathematics schedule has been updated. Please check your new timetable.</p>
                        <hr>
                        <small class="text-muted">Posted 3 days ago by Admin</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-gray-300 {
    color: #dddfeb !important;
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.text-xs {
    font-size: 0.7rem;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.text-uppercase {
    text-transform: uppercase !important;
}

.card-body {
    padding: 1.25rem;
}

.shadow {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.h-100 {
    height: 100% !important;
}

.py-2 {
    padding-top: 0.5rem !important;
    padding-bottom: 0.5rem !important;
}

.mr-2 {
    margin-right: 0.5rem !important;
}

.col-auto {
    flex: 0 0 auto;
    width: auto;
    max-width: 100%;
}

.no-gutters {
    margin-right: 0;
    margin-left: 0;
}

.no-gutters > .col,
.no-gutters > [class*="col-"] {
    padding-right: 0;
    padding-left: 0;
}

.align-items-center {
    align-items: center !important;
}

.text-primary {
    color: #4e73df !important;
}

.text-success {
    color: #1cc88a !important;
}

.text-info {
    color: #36b9cc !important;
}

.text-warning {
    color: #f6c23e !important;
}

.text-muted {
    color: #858796 !important;
}

.btn-block {
    display: block;
    width: 100%;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.btn-outline-primary {
    color: #4e73df;
    border-color: #4e73df;
}

.btn-outline-primary:hover {
    color: #fff;
    background-color: #4e73df;
    border-color: #4e73df;
}

.list-group-flush .list-group-item {
    border-right: 0;
    border-left: 0;
    border-radius: 0;
}

.list-group-item {
    padding: 0.75rem 1.25rem;
    margin-bottom: 0;
    background-color: #fff;
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.d-flex {
    display: flex !important;
}

.justify-content-between {
    justify-content: space-between !important;
}

.align-items-center {
    align-items: center !important;
}

.me-2 {
    margin-right: 0.5rem !important;
}

.alert {
    position: relative;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.35rem;
}

.alert-info {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}

.alert-warning {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

.alert-heading {
    color: inherit;
}

hr {
    margin-top: 1rem;
    margin-bottom: 1rem;
    border: 0;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}
</style>
@endsection
