<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonFile;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    /**
     * Show the form for creating a new lesson.
     */
    public function create(): View
    {
        $classes = SchoolClass::orderBy('grade_level')->orderBy('section')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        
        return view('teacher.lms.lessons.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created lesson in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'section' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'chapter' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,published',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png,gif,mp4,mov,webm|max:10240'
        ]);

        try {
            // Create the lesson
            $lesson = Lesson::create([
                'title' => $validated['title'],
                'class_id' => $validated['class_id'],
                'section' => $validated['section'],
                'subject_id' => $validated['subject_id'],
                'chapter' => $validated['chapter'],
                'description' => $validated['description'],
                'status' => $validated['status'],
                'teacher_id' => auth()->id()
            ]);

            // Handle file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $index => $file) {
                    // Store file in private storage
                    $filePath = $file->store('lessons/' . $lesson->id, 'private');
                    
                    // Create file record
                    LessonFile::create([
                        'lesson_id' => $lesson->id,
                        'file_path' => $filePath,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'order' => $index
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Lesson created successfully!',
                'lesson' => $lesson->load('files')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lesson: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sections for a specific class.
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
     * Get subjects for a specific class.
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
     * Download a lesson file.
     */
    public function downloadFile($fileId)
    {
        $file = LessonFile::findOrFail($fileId);
        $lesson = $file->lesson;
        
        // Check if user has access to this lesson
        if (!auth()->user()->hasRole(['admin', 'super_admin']) && 
            $lesson->teacher_id !== auth()->id() && 
            !auth()->user()->hasRole(['student'])) {
            abort(403);
        }

        // If student, check if lesson is published and belongs to their class
        if (auth()->user()->hasRole(['student'])) {
            if (!$lesson->isPublished() || 
                $lesson->class_id !== auth()->user()->student->class_id || 
                $lesson->section !== auth()->user()->student->section) {
                abort(403);
            }
        }

        $filePath = Storage::disk('private')->path($file->file_path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath, $file->original_name);
    }

    /**
     * Preview an image file.
     */
    public function previewFile($fileId)
    {
        $file = LessonFile::findOrFail($fileId);
        
        if (!$file->isImage()) {
            abort(404);
        }

        $lesson = $file->lesson;
        
        // Check access permissions (same as download)
        if (!auth()->user()->hasRole(['admin', 'super_admin']) && 
            $lesson->teacher_id !== auth()->id() && 
            !auth()->user()->hasRole(['student'])) {
            abort(403);
        }

        if (auth()->user()->hasRole(['student'])) {
            if (!$lesson->isPublished() || 
                $lesson->class_id !== auth()->user()->student->class_id || 
                $lesson->section !== auth()->user()->student->section) {
                abort(403);
            }
        }

        $filePath = Storage::disk('private')->path($file->file_path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    }
}
