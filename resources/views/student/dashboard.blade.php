@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Student Dashboard</h1>
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
                                Overall Attendance
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $attendanceRate ?? 0 }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
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
                                Assignments Pending
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingAssignments ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
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
                                Upcoming Exams
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $upcomingExams ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pen-alt fa-2x text-gray-300"></i>
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
                                Library Books Due
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overdueBooks ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
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
                    <h6 class="m-0 font-weight-bold text-primary">Today's Timetable</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Subject</th>
                                    <th>Teacher</th>
                                    <th>Room</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todaysClasses ?? [] as $class)
                                <tr>
                                    <td>{{ $class['time'] }}</td>
                                    <td>{{ $class['subject'] }}</td>
                                    <td>{{ $class['teacher'] }}</td>
                                    <td>{{ $class['room'] }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No classes scheduled for today.</td>
                                </tr>
                                @endforelse
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
                        @forelse($recentActivities ?? [] as $activity)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-{{ $activity['icon'] }} text-{{ $activity['color'] }} me-2"></i>
                                <span>{{ $activity['text'] }}</span>
                            </div>
                            <small class="text-muted">{{ $activity['time'] }}</small>
                        </div>
                        @empty
                        <div class="list-group-item text-center">No recent activity</div>
                        @endforelse
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
                    <h6 class="m-0 font-weight-bold text-primary">School Announcements</h6>
                </div>
                <div class="card-body">
                    @forelse($announcements ?? [] as $announcement)
                    <div class="alert alert-{{ $announcement['type'] }}" role="alert">
                        <h6 class="alert-heading"><i class="fas fa-bullhorn me-2"></i>{{ $announcement['title'] }}</h6>
                        <p class="mb-0">{{ $announcement['message'] }}</p>
                        <hr>
                        <small class="text-muted">Posted by {{ $announcement['posted_by'] }}</small>
                    </div>
                    @empty
                    <div class="alert alert-info">No announcements at this time.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-primary { border-left: 0.25rem solid #4e73df !important; }
.border-left-success { border-left: 0.25rem solid #1cc88a !important; }
.border-left-info { border-left: 0.25rem solid #36b9cc !important; }
.border-left-warning { border-left: 0.25rem solid #f6c23e !important; }
.text-gray-300 { color: #dddfeb !important; }
.text-gray-800 { color: #5a5c69 !important; }
.text-xs { font-size: 0.7rem; }
.font-weight-bold { font-weight: 700 !important; }
.text-uppercase { text-transform: uppercase !important; }
.card-body { padding: 1.25rem; }
.shadow { box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important; }
.h-100 { height: 100% !important; }
.py-2 { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; }
.mr-2 { margin-right: 0.5rem !important; }
.col-auto { flex: 0 0 auto; width: auto; max-width: 100%; }
.no-gutters { margin-right: 0; margin-left: 0; }
.no-gutters > .col, .no-gutters > [class*="col-"] { padding-right: 0; padding-left: 0; }
.align-items-center { align-items: center !important; }
.text-primary { color: #4e73df !important; }
.text-success { color: #1cc88a !important; }
.text-info { color: #36b9cc !important; }
.text-warning { color: #f6c23e !important; }
.text-muted { color: #858796 !important; }
.btn-block { display: block; width: 100%; }
.list-group-flush .list-group-item { border-right: 0; border-left: 0; border-radius: 0; }
.list-group-item { padding: 0.75rem 1.25rem; background-color: #fff; border: 1px solid rgba(0, 0, 0, 0.125); }
.d-flex { display: flex !important; }
.justify-content-between { justify-content: space-between !important; }
.me-2 { margin-right: 0.5rem !important; }
.alert { padding: 0.75rem 1.25rem; border-radius: 0.35rem; }
.alert-info { color: #0c5460; background-color: #d1ecf1; border-color: #bee5eb; }
.alert-warning { color: #856404; background-color: #fff3cd; border-color: #ffeaa7; }
.alert-heading { color: inherit; }
hr { margin-top: 1rem; margin-bottom: 1rem; border-top: 1px solid rgba(0,0,0,0.1); }
</style>
@endsection