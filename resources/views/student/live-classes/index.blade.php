@extends('layouts.app')

@section('title', 'Live Classes')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Live Classes</h1>
            <p class="text-muted mb-0">View and join your scheduled live classes</p>
        </div>
        <div class="text-end">
            @if($upcomingClasses->count() > 0)
                <div class="text-center">
                    <div class="badge bg-primary mb-1">Next Class In</div>
                    <div id="countdown" class="h5 text-primary mb-0">--:--:--</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label for="subjectFilter" class="form-label">Filter by Subject</label>
                    <select class="form-select" id="subjectFilter">
                        <option value="">All Subjects</option>
                        @foreach($upcomingClasses->pluck('subject.name')->unique() as $subject)
                            <option value="{{ $subject }}">{{ $subject }}</option>
                        @endforeach
                        @foreach($completedClasses->pluck('subject.name')->unique() as $subject)
                            @if(!$upcomingClasses->pluck('subject.name')->contains($subject))
                                <option value="{{ $subject }}">{{ $subject }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-center h-100">
                        <div class="text-muted">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="classCount">Showing {{ $upcomingClasses->count() + $completedClasses->count() }} classes</span>
                        </div>
                        <div>
                            <button class="btn btn-outline-primary btn-sm" onclick="refreshClasses()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="liveClassTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">
                <i class="fas fa-calendar-alt me-2"></i>Upcoming
                <span class="badge bg-primary ms-2">{{ $upcomingClasses->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                <i class="fas fa-history me-2"></i>Completed
                <span class="badge bg-secondary ms-2">{{ $completedClasses->count() }}</span>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="liveClassTabContent">
        <!-- Upcoming Classes -->
        <div class="tab-pane fade show active" id="upcoming" role="tabpanel">
            @if($upcomingClasses->count() > 0)
                <div class="row" id="upcomingClassesContainer">
                    @foreach($upcomingClasses as $class)
                        @include('student.live-classes.partials.class-card', ['class' => $class, 'type' => 'upcoming'])
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Upcoming Classes</h5>
                    <p class="text-muted">You don't have any live classes scheduled at the moment.</p>
                </div>
            @endif
        </div>

        <!-- Completed Classes -->
        <div class="tab-pane fade" id="completed" role="tabpanel">
            @if($completedClasses->count() > 0)
                <div class="row" id="completedClassesContainer">
                    @foreach($completedClasses as $class)
                        @include('student.live-classes.partials.class-card', ['class' => $class, 'type' => 'completed'])
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Completed Classes</h5>
                    <p class="text-muted">You haven't attended any live classes yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.class-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    cursor: pointer;
}

.class-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.platform-badge {
    font-size: 0.875rem;
}

.countdown-timer {
    font-family: 'Courier New', monospace;
    font-weight: bold;
}

.join-button {
    transition: all 0.3s ease;
}

.join-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.join-button:not(:disabled):hover {
    transform: scale(1.05);
}

.class-time {
    font-size: 0.875rem;
    color: #6c757d;
}

.class-subject {
    font-size: 0.875rem;
    font-weight: 600;
}

.class-teacher {
    font-size: 0.875rem;
    color: #6c757d;
}

.status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
}

@media (max-width: 768px) {
    .class-card {
        margin-bottom: 1rem;
    }
    
    .countdown-timer {
        font-size: 0.875rem;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCountdown();
    initializeFilters();
    initializeTabSwitching();
});

function initializeCountdown() {
    const upcomingClasses = @json($upcomingClasses->toArray());
    
    if (upcomingClasses.length === 0) {
        document.getElementById('countdown').textContent = 'No upcoming classes';
        return;
    }

    // Find the next upcoming class
    const nextClass = upcomingClasses.find(cls => new Date(cls.start_time) > new Date());
    
    if (!nextClass) {
        document.getElementById('countdown').textContent = 'No upcoming classes';
        return;
    }

    updateCountdown(nextClass.start_time);
    
    // Update countdown every second
    setInterval(() => {
        updateCountdown(nextClass.start_time);
    }, 1000);
}

function updateCountdown(startTime) {
    const now = new Date();
    const target = new Date(startTime);
    const diff = target - now;

    if (diff <= 0) {
        document.getElementById('countdown').textContent = 'Started';
        return;
    }

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    const formattedTime = [
        hours.toString().padStart(2, '0'),
        minutes.toString().padStart(2, '0'),
        seconds.toString().padStart(2, '0')
    ].join(':');

    document.getElementById('countdown').textContent = formattedTime;
}

function initializeFilters() {
    const subjectFilter = document.getElementById('subjectFilter');
    
    subjectFilter.addEventListener('change', function() {
        filterClasses(this.value);
    });
}

function filterClasses(subject) {
    const upcomingCards = document.querySelectorAll('#upcomingClassesContainer .class-card');
    const completedCards = document.querySelectorAll('#completedClassesContainer .class-card');
    
    let visibleCount = 0;
    
    // Filter upcoming classes
    upcomingCards.forEach(card => {
        const cardSubject = card.dataset.subject;
        if (!subject || cardSubject === subject) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Filter completed classes
    completedCards.forEach(card => {
        const cardSubject = card.dataset.subject;
        if (!subject || cardSubject === subject) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Update count
    document.getElementById('classCount').textContent = `Showing ${visibleCount} classes`;
}

function initializeTabSwitching() {
    const tabs = document.querySelectorAll('#liveClassTabs button');
    
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function() {
            // Refresh countdown when switching to upcoming tab
            if (tab.id === 'upcoming-tab') {
                initializeCountdown();
            }
        });
    });
}

function joinClass(classId, meetingLink) {
    // Open meeting link in new tab
    window.open(meetingLink, '_blank');
    
    // Optionally, mark class as joined (you could implement this with AJAX)
    markClassAsJoined(classId);
}

function markClassAsJoined(classId) {
    // This would be an AJAX call to mark the student as having joined the class
    fetch(`/student/live-classes/${classId}/join`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Class marked as joined');
        }
    })
    .catch(error => {
        console.error('Error marking class as joined:', error);
    });
}

function refreshClasses() {
    // Show loading state
    const refreshBtn = event.target;
    const originalHtml = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
    refreshBtn.disabled = true;
    
    // Reload the page (you could implement AJAX refresh here)
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

// Update join buttons based on time
function updateJoinButtons() {
    const joinButtons = document.querySelectorAll('.join-button');
    
    joinButtons.forEach(button => {
        const startTime = new Date(button.dataset.startTime);
        const now = new Date();
        const timeDiff = startTime - now;
        const minutesDiff = timeDiff / (1000 * 60);
        
        // Enable button only if class starts within 15 minutes or is in progress
        if (minutesDiff <= 15 && minutesDiff > -60) { // Allow joining up to 1 hour after start
            button.disabled = false;
            if (minutesDiff <= 0 && minutesDiff > -60) {
                button.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Join Now';
                button.classList.remove('btn-success');
                button.classList.add('btn-warning');
            }
        } else {
            button.disabled = true;
            if (minutesDiff > 15) {
                const minutesUntil = Math.floor(minutesDiff);
                button.innerHTML = `<i class="fas fa-clock me-2"></i>Available in ${minutesUntil} min`;
            } else {
                button.innerHTML = '<i class="fas fa-times me-2"></i>Class Ended';
                button.classList.remove('btn-success');
                button.classList.add('btn-secondary');
            }
        }
    });
}

// Update join buttons every minute
setInterval(updateJoinButtons, 60000);
updateJoinButtons(); // Initial update
</script>
@endpush
