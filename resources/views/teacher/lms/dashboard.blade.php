@extends('layouts.app')

@section('title', 'LMS Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Learning Management System</h1>
            <p class="text-muted mb-0">Manage your lessons, assignments, and educational content</p>
        </div>
        <div class="text-end">
            <div class="d-flex align-items-center">
                <button class="btn btn-primary me-2" onclick="createNewLesson()">
                    <i class="fas fa-plus me-2"></i>Create Lesson
                </button>
                <a href="{{ route('teacher.assignments.create') }}" class="btn btn-outline-primary">
                    <i class="fas fa-tasks me-2"></i>New Assignment
                </a>
            </div>
        </div>
    </div>

    <!-- LMS Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-book-open fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $lessonCount ?? 0 }}</h4>
                    <small>Total Lessons</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-tasks fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $assignmentCount ?? 0 }}</h4>
                    <small>Assignments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-video fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $videoCount ?? 0 }}</h4>
                    <small>Video Lectures</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i>
                    <h4 class="mb-0">{{ $liveClassCount ?? 0 }}</h4>
                    <small>Live Classes</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('teacher.lms.lessons.create') }}" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-plus-circle fa-2x mb-2 d-block"></i>
                                    Create New Lesson
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('teacher.assignments.create') }}" class="btn btn-outline-success btn-lg">
                                    <i class="fas fa-file-alt fa-2x mb-2 d-block"></i>
                                    Create Assignment
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('teacher.live-classes.create') }}" class="btn btn-outline-info btn-lg">
                                    <i class="fas fa-broadcast-tower fa-2x mb-2 d-block"></i>
                                    Schedule Live Class
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-grid">
                                <a href="{{ route('teacher.assignments.results.index') }}" class="btn btn-outline-warning btn-lg">
                                    <i class="fas fa-chart-bar fa-2x mb-2 d-block"></i>
                                    View Results
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Recent Activity
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($recentActivities) && $recentActivities->count() > 0)
                        <div class="activity-timeline">
                            @foreach($recentActivities as $activity)
                                <div class="activity-item mb-3 pb-3 border-bottom">
                                    <div class="d-flex align-items-start">
                                        <div class="activity-icon me-3">
                                            @if($activity->type === 'lesson')
                                                <i class="fas fa-book-open text-primary"></i>
                                            @elseif($activity->type === 'assignment')
                                                <i class="fas fa-tasks text-success"></i>
                                            @elseif($activity->type === 'video')
                                                <i class="fas fa-video text-info"></i>
                                            @elseif($activity->type === 'live_class')
                                                <i class="fas fa-chalkboard-teacher text-warning"></i>
                                            @else
                                                <i class="fas fa-circle text-secondary"></i>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-medium">{{ $activity->title }}</div>
                                            <div class="text-muted small">{{ $activity->description }}</div>
                                            <div class="text-muted small">{{ $activity->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No Recent Activity</h6>
                            <p class="text-muted">Start creating lessons and assignments to see your activity here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-calendar me-2"></i>Upcoming Schedule
                    </h6>
                </div>
                <div class="card-body">
                    @if(isset($upcomingSchedule) && $upcomingSchedule->count() > 0)
                        <div class="schedule-list">
                            @foreach($upcomingSchedule as $item)
                                <div class="schedule-item mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-medium">{{ $item->title }}</div>
                                            <div class="text-muted small">
                                                <i class="fas fa-clock me-1"></i>{{ $item->date->format('M j, g:i A') }}
                                            </div>
                                            @if(isset($item->class))
                                                <div class="text-muted small">
                                                    <i class="fas fa-users me-1"></i>{{ $item->class }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            @if($item->type === 'live_class')
                                                <span class="badge bg-info">Live</span>
                                            @elseif($item->type === 'assignment_due')
                                                <span class="badge bg-warning">Due</span>
                                            @else
                                                <span class="badge bg-secondary">Scheduled</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-alt fa-2x text-muted mb-2"></i>
                            <h6 class="text-muted">No Upcoming Events</h6>
                            <p class="text-muted small">Your schedule will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Resources -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-folder me-2"></i>Quick Resources
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-question-circle me-2"></i>LMS Help Guide
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-video me-2"></i>Video Tutorial Library
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <i class="fas fa-download me-2"></i>Download Templates
                        </a>
                        <a href="{{ route('teacher.assignments.results.index') }}" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar me-2"></i>Assignment Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.activity-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: #f8f9fa;
    font-size: 1.2rem;
}

.activity-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.schedule-item:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.btn-lg {
    padding: 1.5rem 1rem;
    text-align: center;
}

.btn-lg i {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .btn-lg {
        padding: 1rem 0.75rem;
        font-size: 0.9rem;
    }
    
    .btn-lg i {
        font-size: 1.5rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

function initializeDashboard() {
    // Auto-refresh recent activity every 30 seconds
    setInterval(refreshActivity, 30000);
}

function createNewLesson() {
    // Redirect to lesson creation page
    window.location.href = '{{ route("teacher.lms.lessons.create") }}';
}

function refreshActivity() {
    // You can implement AJAX refresh here if needed
    console.log('Refreshing activity...');
}
</script>
@push
