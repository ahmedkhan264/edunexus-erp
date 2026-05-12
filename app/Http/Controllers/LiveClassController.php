<?php

namespace App\Http\Controllers;

use App\Models\LiveClass;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class LiveClassController extends Controller
{
    /**
     * Show the form for creating a new live class.
     */
    public function create(): View
    {
        $classes = SchoolClass::orderBy('grade_level')->orderBy('section')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        
        return view('teacher.live-classes.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created live class in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'class_id' => 'required|exists:classes,id',
            'section' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:1|max:480',
            'meeting_link' => 'required|url',
            'notify_students' => 'boolean'
        ]);

        try {
            // Calculate start and end times
            $startDateTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
            $endDateTime = $startDateTime->copy()->addMinutes($validated['duration']);

            // Create temporary live class instance for clash detection
            $tempLiveClass = new LiveClass([
                'class_id' => $validated['class_id'],
                'section' => $validated['section'],
                'start_time' => $startDateTime,
                'end_time' => $endDateTime
            ]);

            // Check for time clashes
            $clashingClasses = $tempLiveClass->findClashingClasses();
            
            if ($clashingClasses->count() > 0) {
                $clashDetails = $clashingClasses->map(function ($class) {
                    return [
                        'title' => $class->title,
                        'subject' => $class->subject->name,
                        'start_time' => $class->start_time->format('g:i A'),
                        'end_time' => $class->end_time->format('g:i A')
                    ];
                });

                return response()->json([
                    'success' => false,
                    'message' => 'Time clash detected with existing classes.',
                    'clashes' => $clashDetails
                ], 422);
            }

            // Create the live class
            $liveClass = LiveClass::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'class_id' => $validated['class_id'],
                'section' => $validated['section'],
                'subject_id' => $validated['subject_id'],
                'teacher_id' => auth()->id(),
                'meeting_link' => $validated['meeting_link'],
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'duration' => $validated['duration']
            ]);

            // Notify students if requested
            if ($validated['notify_students'] ?? false) {
                $this->notifyStudents($liveClass);
            }

            return response()->json([
                'success' => true,
                'message' => 'Live class scheduled successfully!',
                'live_class' => $liveClass->load(['schoolClass', 'subject', 'teacher'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule live class: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display live classes for students.
     */
    public function studentIndex(): View
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $upcomingClasses = LiveClass::forClass($student->class_id, $student->section)
            ->upcoming()
            ->with(['subject', 'teacher'])
            ->orderBy('start_time')
            ->get();

        $completedClasses = LiveClass::forClass($student->class_id, $student->section)
            ->past()
            ->with(['subject', 'teacher'])
            ->orderBy('start_time', 'desc')
            ->get();

        return view('student.live-classes.index', compact('upcomingClasses', 'completedClasses'));
    }

    /**
     * Get sections for a specific class (AJAX).
     */
    public function getSections($classId): JsonResponse
    {
        $class = SchoolClass::findOrFail($classId);
        $sections = explode(',', $class->sections);
        
        return response()->json([
            'success' => true,
            'sections' => array_map('trim', $sections)
        ]);
    }

    /**
     * Get subjects for a specific class (AJAX).
     */
    public function getSubjects($classId): JsonResponse
    {
        $subjects = Subject::where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json([
            'success' => true,
            'subjects' => $subjects
        ]);
    }

    /**
     * Check for time clashes (AJAX).
     */
    public function checkClash(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:1',
            'exclude_id' => 'nullable|integer'
        ]);

        $startDateTime = \Carbon\Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $endDateTime = $startDateTime->copy()->addMinutes($validated['duration']);

        $query = LiveClass::where('class_id', $validated['class_id'])
            ->where('section', $validated['section'])
            ->whereDate('start_time', $validated['date'])
            ->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->where(function ($q) use ($startDateTime, $endDateTime) {
                    $q->where('start_time', '<', $endDateTime)
                      ->where('end_time', '>', $startDateTime);
                });
            });

        // Exclude current class if editing
        if (!empty($validated['exclude_id'])) {
            $query->where('id', '!=', $validated['exclude_id']);
        }

        $clashingClasses = $query->with(['subject', 'teacher'])->get();

        if ($clashingClasses->count() > 0) {
            $clashDetails = $clashingClasses->map(function ($class) {
                return [
                    'id' => $class->id,
                    'title' => $class->title,
                    'subject' => $class->subject->name,
                    'teacher' => $class->teacher->name,
                    'start_time' => $class->start_time->format('g:i A'),
                    'end_time' => $class->end_time->format('g:i A')
                ];
            });

            return response()->json([
                'has_clash' => true,
                'clashes' => $clashDetails
            ]);
        }

        return response()->json([
            'has_clash' => false
        ]);
    }

    /**
     * Notify students about the live class.
     */
    private function notifyStudents(LiveClass $liveClass): void
    {
        $students = $liveClass->students();
        
        foreach ($students as $student) {
            // Create database notification
            $student->user->notify(new \App\Notifications\LiveClassScheduled($liveClass));
        }

        // Mark notification as sent
        $liveClass->update(['notification_sent' => true]);
    }
}
