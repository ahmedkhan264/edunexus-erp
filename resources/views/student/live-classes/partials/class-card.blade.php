<div class="col-lg-4 col-md-6 mb-4">
    <div class="card class-card h-100" data-subject="{{ $class->subject->name }}">
        <div class="position-relative">
            <!-- Status Badge -->
            <span class="status-badge">
                @if($type === 'upcoming')
                    @if($class->startsSoon())
                        <span class="badge bg-warning">Starting Soon</span>
                    @else
                        <span class="badge bg-primary">Upcoming</span>
                    @endif
                @else
                    <span class="badge bg-secondary">Completed</span>
                @endif
            </span>
            
            <!-- Platform Icon -->
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                <div class="text-center">
                    <i class="{{ $class->getMeetingPlatformIcon() }} fa-2x text-{{ $class->getMeetingPlatformColor() }}"></i>
                    <div class="small text-muted mt-2">{{ $class->getMeetingPlatform() }}</div>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <h6 class="card-title text-truncate" title="{{ $class->title }}">
                {{ $class->title }}
            </h6>
            
            <div class="mb-2">
                <span class="badge bg-info class-subject">{{ $class->subject->name }}</span>
            </div>
            
            <div class="class-time mb-2">
                <i class="fas fa-calendar me-1"></i>
                {{ $class->start_time->format('M j, Y') }}
            </div>
            
            <div class="class-time mb-2">
                <i class="fas fa-clock me-1"></i>
                {{ $class->start_time->format('g:i A') }} - {{ $class->end_time->format('g:i A') }}
                <span class="text-muted">({{ $class->getFormattedDuration() }})</span>
            </div>
            
            <div class="class-teacher mb-3">
                <i class="fas fa-user-tie me-1"></i>
                {{ $class->teacher->name }}
            </div>
            
            @if($class->description)
                <p class="card-text small text-muted mb-3">
                    {{ Str::limit($class->description, 80) }}
                </p>
            @endif
            
            <div class="d-flex justify-content-between align-items-center">
                @if($type === 'upcoming')
                    <button class="btn btn-success join-button btn-sm" 
                            onclick="joinClass({{ $class->id }}, '{{ $class->meeting_link }}')"
                            data-start-time="{{ $class->start_time->toISOString() }}"
                            @if(!$class->startsSoon() && $class->start_time->diffInMinutes(now(), false) > 15)
                            disabled
                            @endif>
                        @if($class->startsSoon())
                            <i class="fas fa-sign-in-alt me-2"></i>Join Now
                        @else
                            <i class="fas fa-clock me-2"></i>Join in {{ $class->start_time->diffInMinutes(now(), false) }} min
                        @endif
                    </button>
                @else
                    <span class="badge bg-secondary">
                        <i class="fas fa-check me-1"></i>Completed
                    </span>
                @endif
                
                <button class="btn btn-outline-primary btn-sm" onclick="showClassDetails({{ $class->id }})">
                    <i class="fas fa-info-circle"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Class Details Modal -->
<div class="modal fade" id="classDetailsModal{{ $class->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">{{ $class->title }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Subject:</strong></td>
                                <td>{{ $class->subject->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Class:</strong></td>
                                <td>Grade {{ $class->schoolClass->grade_level }} - {{ $class->section }}</td>
                            </tr>
                            <tr>
                                <td><strong>Teacher:</strong></td>
                                <td>{{ $class->teacher->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Date:</strong></td>
                                <td>{{ $class->start_time->format('l, F j, Y') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Time:</strong></td>
                                <td>{{ $class->start_time->format('g:i A') }} - {{ $class->end_time->format('g:i A') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Duration:</strong></td>
                                <td>{{ $class->getFormattedDuration() }}</td>
                            </tr>
                            <tr>
                                <td><strong>Platform:</strong></td>
                                <td>
                                    <span class="badge bg-{{ $class->getMeetingPlatformColor() }}">
                                        <i class="{{ $class->getMeetingPlatformIcon() }} me-1"></i>
                                        {{ $class->getMeetingPlatform() }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                        
                        @if($class->description)
                            <div class="mt-3">
                                <h6>Description:</h6>
                                <p>{{ $class->description }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="bg-light rounded p-3 mb-3">
                                <i class="{{ $class->getMeetingPlatformIcon() }} fa-3x text-{{ $class->getMeetingPlatformColor() }}"></i>
                                <div class="mt-2">
                                    <span class="badge bg-{{ $class->getMeetingPlatformColor() }}">
                                        {{ $class->getMeetingPlatform() }}
                                    </span>
                                </div>
                            </div>
                            
                            @if($type === 'upcoming')
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">Time until class:</div>
                                    <div class="h5 text-primary" id="modalCountdown{{ $class->id }}">
                                        {{ $class->getTimeUntilStart() }}
                                    </div>
                                </div>
                                
                                <button class="btn btn-success btn-block join-button" 
                                        onclick="joinClass({{ $class->id }}, '{{ $class->meeting_link }}')"
                                        data-start-time="{{ $class->start_time->toISOString() }}"
                                        @if(!$class->startsSoon() && $class->start_time->diffInMinutes(now(), false) > 15)
                                        disabled
                                        @endif>
                                    @if($class->startsSoon())
                                        <i class="fas fa-sign-in-alt me-2"></i>Join Class
                                    @else
                                        <i class="fas fa-clock me-2"></i>Join Later
                                    @endif
                                </button>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-check-circle me-2"></i>
                                    This class was completed on {{ $class->start_time->format('M j, Y') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @if($type === 'upcoming')
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" 
                            onclick="joinClass({{ $class->id }}, '{{ $class->meeting_link }}')"
                            @if(!$class->startsSoon() && $class->start_time->diffInMinutes(now(), false) > 15)
                            disabled
                            @endif>
                        <i class="fas fa-sign-in-alt me-2"></i>Join Class
                    </button>
                @else
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Initialize modal countdown
document.addEventListener('DOMContentLoaded', function() {
    const classStartTime = new Date('{{ $class->start_time->toISOString() }}');
    const countdownElement = document.getElementById('modalCountdown{{ $class->id }}');
    
    if (countdownElement) {
        const updateModalCountdown = function() {
            const now = new Date();
            const diff = classStartTime - now;
            
            if (diff <= 0) {
                countdownElement.textContent = 'Started';
                return;
            }
            
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            
            let timeString = '';
            if (hours > 0) {
                timeString = hours + 'h ' + minutes + 'm';
            } else {
                timeString = minutes + ' minutes';
            }
            
            countdownElement.textContent = timeString;
        };
        
        updateModalCountdown();
        setInterval(updateModalCountdown, 60000); // Update every minute
    }
});

function showClassDetails(classId) {
    const modal = new bootstrap.Modal(document.getElementById('classDetailsModal' + classId));
    modal.show();
}
</script>
