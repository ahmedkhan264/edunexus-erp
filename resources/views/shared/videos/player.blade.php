@extends('layouts.app')

@section('title', $lecture->title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $lecture->title }}</h1>
            <p class="text-muted mb-0">
                by {{ $lecture->teacher->name }} • 
                {{ $lecture->schoolClass->grade_level }}-{{ $lecture->schoolClass->section }} • 
                {{ $lecture->subject->name }}
            </p>
        </div>
        <div class="text-end">
            @if(auth()->user()->hasRole(['teacher']) && $lecture->teacher_id === auth()->id())
                <div class="btn-group">
                    <a href="{{ route('teacher.videos.edit', $lecture->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <button class="btn btn-outline-danger btn-sm" onclick="confirmDelete({{ $lecture->id }})">
                        <i class="fas fa-trash me-1"></i>Delete
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Video Player Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body p-0">
                    <!-- Video Player -->
                    <div class="video-container position-relative">
                        @if($lecture->isExternalVideo())
                            <!-- External Video (YouTube/Vimeo) -->
                            @if(str_contains($lecture->video_url, 'youtube.com') || str_contains($lecture->video_url, 'youtu.be'))
                                <div class="embed-responsive embed-responsive-16by9">
                                    <iframe class="embed-responsive-item" 
                                            src="{{ $lecture->video_url }}" 
                                            frameborder="0" 
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                            allowfullscreen>
                                    </iframe>
                                </div>
                            @elseif(str_contains($lecture->video_url, 'vimeo.com'))
                                <div class="embed-responsive embed-responsive-16by9">
                                    <iframe class="embed-responsive-item" 
                                            src="{{ $lecture->video_url }}" 
                                            frameborder="0" 
                                            allow="autoplay; fullscreen; picture-in-picture" 
                                            allowfullscreen>
                                    </iframe>
                                </div>
                            @else
                                <!-- Other external videos -->
                                <video class="w-100" controls>
                                    <source src="{{ $lecture->video_url }}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            @endif
                        @else
                            <!-- Uploaded Video -->
                            <video id="uploadedVideo" class="w-100" controls>
                                <source src="#" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        @endif

                        <!-- Video Overlay Info -->
                        <div class="video-overlay position-absolute top-0 start-0 p-3 text-white">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2">{{ $lecture->subject->name }}</span>
                                <span class="badge bg-info">{{ $lecture->getFormattedDuration() }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Video Info Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>Video Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Teacher</label>
                        <div class="fw-medium">{{ $lecture->teacher->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Class</label>
                        <div class="fw-medium">Grade {{ $lecture->schoolClass->grade_level }} - {{ $lecture->schoolClass->section }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Subject</label>
                        <div class="fw-medium">{{ $lecture->subject->name }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Duration</label>
                        <div class="fw-medium">{{ $lecture->getFormattedDuration() }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Views</label>
                        <div class="fw-medium">
                            <i class="fas fa-eye me-1"></i>{{ number_format($lecture->views_count) }}
                        </div>
                    </div>
                    @if($lecture->description)
                        <div class="mb-3">
                            <label class="text-muted small">Description</label>
                            <div class="small">{{ $lecture->description }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Mark as Viewed (for students) -->
            @if(auth()->user()->hasRole(['student']))
                <div class="card shadow">
                    <div class="card-body text-center">
                        @if($hasViewed)
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x mb-2"></i>
                                <p class="mb-0 fw-medium">You have viewed this video</p>
                                <small class="text-muted">Viewed on {{ $lecture->videoViews()->where('user_id', auth()->id())->first()->viewed_at->format('M j, Y g:i A') }}</small>
                            </div>
                        @else
                            <button class="btn btn-success" id="markViewedBtn" onclick="markAsViewed()">
                                <i class="fas fa-check me-2"></i>Mark as Viewed
                            </button>
                            <p class="text-muted small mt-2">Mark this video as viewed after watching</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Description Section -->
    @if($lecture->description)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-align-left me-2"></i>Description
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ nl2br($lecture->description) }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Related Videos -->
    @if($relatedVideos->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-video me-2"></i>Related Videos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($relatedVideos as $relatedVideo)
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="card h-100 video-card" onclick="window.location.href='{{ route('videos.show', $relatedVideo->id) }}'">
                                        <div class="position-relative">
                                            <img src="{{ $relatedVideo->getThumbnailUrl() }}" 
                                                 class="card-img-top video-thumbnail" 
                                                 alt="{{ $relatedVideo->title }}">
                                            <div class="position-absolute bottom-0 end-0 m-2">
                                                <span class="badge bg-dark">{{ $relatedVideo->getFormattedDuration() }}</span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h6 class="card-title text-truncate">{{ $relatedVideo->title }}</h6>
                                            <p class="card-text text-muted small mb-0">
                                                {{ $relatedVideo->teacher->name }} • 
                                                {{ $relatedVideo->views_count }} views
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Video</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this video lecture?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="{{ route('teacher.videos.destroy', ':id') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.video-container {
    background: #000;
    border-radius: 0.375rem;
    overflow: hidden;
}

.embed-responsive {
    position: relative;
    display: block;
    width: 100%;
    padding: 0;
}

.embed-responsive::before {
    content: "";
    display: block;
    padding-top: 56.25%;
}

.embed-responsive .embed-responsive-item {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

.video-overlay {
    background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, transparent 100%);
    pointer-events: none;
}

.video-card {
    cursor: pointer;
    transition: transform 0.2s ease-in-out;
}

.video-card:hover {
    transform: translateY(-5px);
}

.video-thumbnail {
    height: 180px;
    object-fit: cover;
}

.card-img-top {
    border-top-left-radius: 0.375rem;
    border-top-right-radius: 0.375rem;
}

#deleteForm {
    display: inline;
}

@media (max-width: 768px) {
    .video-thumbnail {
        height: 120px;
    }
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load uploaded video URL if needed
    @if($lecture->isUploadedVideo())
        loadUploadedVideo();
    @endif
    
    // Track video progress for auto-marking as viewed
    trackVideoProgress();
});

function loadUploadedVideo() {
    fetch(`/videos/{{ $lecture->id }}/url`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const video = document.getElementById('uploadedVideo');
                const source = video.querySelector('source');
                source.src = data.url;
                video.load();
            }
        })
        .catch(error => {
            console.error('Error loading video URL:', error);
        });
}

function markAsViewed() {
    const btn = document.getElementById('markViewedBtn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Marking...';
    
    fetch(`/videos/{{ $lecture->id }}/viewed`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const cardBody = btn.closest('.card-body');
            cardBody.innerHTML = `
                <div class="text-success mb-2">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <p class="mb-0 fw-medium">You have viewed this video</p>
                    <small class="text-muted">Viewed just now</small>
                </div>
            `;
            
            // Update view count
            updateViewCount(data.view_count);
            
            showToast('Success', data.message, 'success');
        } else {
            btn.disabled = false;
            btn.innerHTML = originalText;
            showToast('Error', data.message || 'Failed to mark as viewed', 'error');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error('Error marking as viewed:', error);
        showToast('Error', 'Failed to mark as viewed', 'error');
    });
}

function trackVideoProgress() {
    const video = document.querySelector('video');
    if (!video) return;
    
    let hasMarkedViewed = @json($hasViewed);
    let progressThreshold = 0.8; // Mark as viewed after 80% progress
    
    video.addEventListener('timeupdate', function() {
        if (!hasMarkedViewed) {
            const progress = video.currentTime / video.duration;
            
            if (progress >= progressThreshold) {
                hasMarkedViewed = true;
                markAsViewed();
            }
        }
    });
}

function updateViewCount(newCount) {
    const viewCountElement = document.querySelector('.fa-eye').parentElement;
    if (viewCountElement) {
        viewCountElement.innerHTML = `<i class="fas fa-eye me-1"></i>${number_format(newCount)}`;
    }
}

function confirmDelete(lectureId) {
    const form = document.getElementById('deleteForm');
    form.action = form.action.replace(':id', lectureId);
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function showToast(title, message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
    toast.style.zIndex = '9999';
    toast.innerHTML = `
        <strong>${title}:</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Helper function for number formatting
function number_format(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
</script>
@endpush
