@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- KPI Cards Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card blue">
                <div class="kpi-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="kpi-value">{{ number_format($totalStudents) }}</div>
                <div class="kpi-label">Total Students</div>
                <div class="kpi-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>12% from last month</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card green">
                <div class="kpi-icon green">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="kpi-value">{{ number_format($totalTeachers) }}</div>
                <div class="kpi-label">Total Teachers</div>
                <div class="kpi-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>5% from last month</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card orange">
                <div class="kpi-icon orange">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="kpi-value">{{ number_format($todayAttendance) }}</div>
                <div class="kpi-label">Today's Attendance</div>
                <div class="kpi-change positive">
                    <i class="fas fa-arrow-up"></i>
                    <span>95% attendance rate</span>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="kpi-card red">
                <div class="kpi-icon red">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="kpi-value">{{ number_format($monthlyFeeCollection, 0) }}</div>
                <div class="kpi-label">Monthly Fee Collection</div>
                <div class="kpi-change negative">
                    <i class="fas fa-arrow-down"></i>
                    <span>8% from target</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts and Tables Row -->
    <div class="row">
        <!-- Fee Collection Chart -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">Fee Collection Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="feeCollectionChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="small text-muted">Total Classes</div>
                            <div class="h5 mb-0">{{ $totalClasses }}</div>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-school fa-2x"></i>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="small text-muted">Pending Fees</div>
                            <div class="h5 mb-0 text-danger">{{ $pendingFees }}</div>
                        </div>
                        <div class="text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-muted">Collection Rate</div>
                            <div class="h5 mb-0 text-success">92%</div>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Attendance Trend and Recent Activities Row -->
    <div class="row">
        <!-- Attendance Trend Chart -->
        <div class="col-xl-8 col-lg-7 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">Weekly Attendance Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="attendanceTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Admissions -->
        <div class="col-xl-4 col-lg-5 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">Recent Admissions</h6>
                </div>
                <div class="card-body">
                    @if(count($recentAdmissions) > 0)
                        @foreach($recentAdmissions as $admission)
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($admission['name'], 0, 1)) }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $admission['name'] }}</div>
                                    <div class="small text-muted">{{ $admission['email'] }}</div>
                                    <div class="small text-primary">{{ \Carbon\Carbon::parse($admission['created_at'])->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-user-plus fa-2x mb-3"></i>
                            <div>No recent admissions</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Events Row -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0">Upcoming Events</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($upcomingEvents as $event)
                            <div class="col-md-4 mb-3">
                                <div class="card border-0 bg-light">
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            @if($event['type'] == 'academic')
                                                <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                                            @elseif($event['type'] == 'fee')
                                                <i class="fas fa-money-bill fa-3x text-warning"></i>
                                            @elseif($event['type'] == 'meeting')
                                                <i class="fas fa-users fa-3x text-info"></i>
                                            @endif
                                        </div>
                                        <h6 class="card-title">{{ $event['title'] }}</h6>
                                        <p class="card-text text-muted">{{ $event['date'] }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Fee Collection Chart
    const feeCtx = document.getElementById('feeCollectionChart').getContext('2d');
    new Chart(feeCtx, {
        type: 'bar',
        data: {
            labels: @json(collect($feeCollectionChart)->pluck('month')),
            datasets: [{
                label: 'Collected',
                data: @json(collect($feeCollectionChart)->pluck('collected')),
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgba(16, 185, 129, 1)',
                borderWidth: 1
            }, {
                label: 'Target',
                data: @json(collect($feeCollectionChart)->pluck('target')),
                backgroundColor: 'rgba(59, 130, 246, 0.3)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 1,
                type: 'line'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Attendance Trend Chart
    const attendanceCtx = document.getElementById('attendanceTrendChart').getContext('2d');
    new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: @json(collect($attendanceTrend)->pluck('date')),
            datasets: [{
                label: 'Present',
                data: @json(collect($attendanceTrend)->pluck('present')),
                borderColor: 'rgba(16, 185, 129, 1)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Absent',
                data: @json(collect($attendanceTrend)->pluck('absent')),
                borderColor: 'rgba(239, 68, 68, 1)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Notification bell click handler
    document.getElementById('notificationBell').addEventListener('click', function(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('show');
    });
    
    // Close notification dropdown when clicking outside
    document.addEventListener('click', function() {
        document.getElementById('notificationDropdown').classList.remove('show');
    });
    
    document.getElementById('notificationDropdown').addEventListener('click', function(e) {
        e.stopPropagation();
    });
</script>
@endsection
