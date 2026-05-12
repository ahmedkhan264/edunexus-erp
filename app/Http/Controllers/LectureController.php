<?php

namespace App\Http\Controllers;

use App\Models\VideoLecture;
use App\Models\VideoView;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class LectureController extends Controller
{
    /**
     * Display the video lecture player.
     */
    public function show(VideoLecture $lecture): View
    {
        // Authorization check
        $this->authorizeView($lecture);

        // Load relationships
        $lecture->load(['schoolClass', 'subject', 'teacher']);

        // Get related videos
        $relatedVideos = $lecture->getRelatedVideos(4);

        // Check if user has viewed this video
        $hasViewed = false;
        if (auth()->check() && auth()->user()->hasRole(['student'])) {
            $hasViewed = $lecture->isViewedByUser(auth()->id());
        }

        return view('shared.videos.player', compact('lecture', 'relatedVideos', 'hasViewed'));
    }

    /**
     * Mark video as viewed by student (AJAX endpoint).
     */
    public function markViewed(Request $request, VideoLecture $lecture): JsonResponse
    {
        // Authorization check
        $this->authorizeView($lecture);

        if (!auth()->user()->hasRole(['student'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only students can mark videos as viewed.'
            ], 403);
        }

        try {
            // Record view (prevents duplicates)
            $view = VideoView::recordView($lecture->id, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Video marked as viewed successfully.',
                'view_count' => $lecture->fresh()->views_count,
                'viewed_at' => $view->viewed_at
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark video as viewed.'
            ], 500);
        }
    }

    /**
     * Authorize user to view the lecture.
     */
    private function authorizeView(VideoLecture $lecture): void
    {
        $user = auth()->user();

        // Admin and Super Admin can view all
        if ($user->hasRole(['admin', 'super_admin'])) {
            return;
        }

        // Teachers can view their own videos or all published videos
        if ($user->hasRole(['teacher'])) {
            if ($lecture->teacher_id === $user->id) {
                return; // Can view own videos (draft or published)
            }
            if ($lecture->isPublished()) {
                return; // Can view other teachers' published videos
            }
            abort(403, 'You are not authorized to view this video.');
        }

        // Students can only view published videos from their class
        if ($user->hasRole(['student'])) {
            if (!$lecture->isPublished()) {
                abort(403, 'This video is not published yet.');
            }

            $student = $user->student;
            if (!$student || $student->class_id !== $lecture->class_id) {
                abort(403, 'You are not authorized to view this video.');
            }

            return;
        }

        // Other roles cannot view
        abort(403, 'You are not authorized to view this video.');
    }

    /**
     * Get video statistics (AJAX endpoint).
     */
    public function getStats(VideoLecture $lecture): JsonResponse
    {
        $this->authorizeView($lecture);

        $stats = [
            'views_count' => $lecture->views_count,
            'unique_views' => $lecture->videoViews()->count(),
            'recent_views' => $lecture->videoViews()->recent()->count(),
            'duration' => $lecture->getFormattedDuration(),
            'has_viewed' => false
        ];

        if (auth()->check() && auth()->user()->hasRole(['student'])) {
            $stats['has_viewed'] = $lecture->isViewedByUser(auth()->id());
        }

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Get signed URL for uploaded video (protects direct access).
     */
    public function getVideoUrl(VideoLecture $lecture): JsonResponse
    {
        $this->authorizeView($lecture);

        if (!$lecture->isUploadedVideo()) {
            return response()->json([
                'success' => false,
                'message' => 'This is not an uploaded video.'
            ], 400);
        }

        // Generate temporary signed URL
        $url = \Storage::disk('private')->temporaryUrl(
            $lecture->video_path,
            now()->addMinutes(30) // URL expires in 30 minutes
        );

        return response()->json([
            'success' => true,
            'url' => $url
        ]);
    }
}
