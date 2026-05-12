<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoLecture extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'class_id',
        'subject_id',
        'teacher_id',
        'video_url',
        'video_path',
        'thumbnail_path',
        'duration',
        'status',
        'views_count'
    ];

    protected $casts = [
        'duration' => 'integer',
        'views_count' => 'integer'
    ];

    /**
     * Get the class that owns the video lecture.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the subject that owns the video lecture.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the teacher that owns the video lecture.
     */
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the video views for the lecture.
     */
    public function videoViews()
    {
        return $this->hasMany(VideoView::class);
    }

    /**
     * Scope a query to only include published lectures.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include draft lectures.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to filter by class.
     */
    public function scopeForClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope a query to filter by subject.
     */
    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Check if lecture is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if lecture is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Get the video URL or path.
     */
    public function getVideoSource(): string
    {
        return $this->video_url ?? $this->video_path;
    }

    /**
     * Check if video is external (YouTube, Vimeo, etc.).
     */
    public function isExternalVideo(): bool
    {
        return !empty($this->video_url);
    }

    /**
     * Check if video is uploaded.
     */
    public function isUploadedVideo(): bool
    {
        return !empty($this->video_path);
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDuration(): string
    {
        if (!$this->duration) {
            return 'Unknown';
        }

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        } else {
            return sprintf('%02d:%02d', $minutes, $seconds);
        }
    }

    /**
     * Get related videos (same subject, exclude current).
     */
    public function getRelatedVideos($limit = 4)
    {
        return self::where('subject_id', $this->subject_id)
            ->where('id', '!=', $this->id)
            ->where('status', 'published')
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if user has viewed this video.
     */
    public function isViewedByUser($userId): bool
    {
        return $this->videoViews()->where('user_id', $userId)->exists();
    }

    /**
     * Get thumbnail URL or placeholder.
     */
    public function getThumbnailUrl(): string
    {
        if ($this->thumbnail_path) {
            return asset('storage/' . $this->thumbnail_path);
        }

        // Generate YouTube thumbnail if external video
        if ($this->isExternalVideo() && str_contains($this->video_url, 'youtube.com')) {
            $videoId = $this->extractYouTubeId($this->video_url);
            if ($videoId) {
                return "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg";
            }
        }

        // Return placeholder
        return 'https://via.placeholder.com/640x360/4e73df/ffffff?text=Video';
    }

    /**
     * Extract YouTube video ID from URL.
     */
    private function extractYouTubeId($url): ?string
    {
        $pattern = '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/';
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
