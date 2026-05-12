<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VideoView extends Model
{
    use HasFactory;

    protected $fillable = [
        'video_lecture_id',
        'user_id',
        'viewed_at'
    ];

    protected $casts = [
        'viewed_at' => 'datetime'
    ];

    /**
     * Get the video lecture that owns the view.
     */
    public function videoLecture()
    {
        return $this->belongsTo(VideoLecture::class);
    }

    /**
     * Get the user (student) who viewed the video.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include views by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include views within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('viewed_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include recent views (last 24 hours).
     */
    public function scopeRecent($query)
    {
        return $query->where('viewed_at', '>=', now()->subHours(24));
    }

    /**
     * Create a new view record for a video by a user.
     */
    public static function recordView($videoLectureId, $userId)
    {
        // Check if view already exists
        $existingView = self::where('video_lecture_id', $videoLectureId)
            ->where('user_id', $userId)
            ->first();

        if ($existingView) {
            return $existingView;
        }

        // Create new view record
        $view = self::create([
            'video_lecture_id' => $videoLectureId,
            'user_id' => $userId,
            'viewed_at' => now()
        ]);

        // Increment video lecture view count
        $videoLecture = VideoLecture::find($videoLectureId);
        if ($videoLecture) {
            $videoLecture->increment('views_count');
        }

        return $view;
    }

    /**
     * Check if a user has viewed a specific video.
     */
    public static function hasUserViewedVideo($videoLectureId, $userId): bool
    {
        return self::where('video_lecture_id', $videoLectureId)
            ->where('user_id', $userId)
            ->exists();
    }
}
