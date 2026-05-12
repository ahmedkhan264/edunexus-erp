<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentFile;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Student;
use App\Notifications\NewAssignmentNotification;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Show the form for creating a new assignment.
     */
    public function create(): View
    {
        $classes = SchoolClass::orderBy('grade_level')->orderBy('section')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();
        
        return view('teacher.assignments.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created assignment in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'class_id' => 'required|exists:classes,id',
            'section' => 'required|string',
            'subject_id' => 'required|exists:subjects,id',
            'due_date' => 'required|date|after:now',
            'total_marks' => 'required|integer|min:0|max:1000',
            'allow_resubmission' => 'boolean',
            'files' => 'nullable|array|max:5',
            'files.*' => [
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip',
                'max:10240' // 10MB per file
            ]
        ]);

        try {
            // Create the assignment
            $assignment = Assignment::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'class_id' => $validated['class_id'],
                'section' => $validated['section'],
                'subject_id' => $validated['subject_id'],
                'teacher_id' => auth()->id(),
                'due_date' => $validated['due_date'],
                'total_marks' => $validated['total_marks'],
                'allow_resubmission' => $validated['allow_resubmission'] ?? false
            ]);

            // Handle file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    // Store file in private storage
                    $filePath = $file->store('assignments/' . $assignment->id, 'private');
                    
                    // Create file record
                    AssignmentFile::create([
                        'assignment_id' => $assignment->id,
                        'file_path' => $filePath,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize()
                    ]);
                }
            }

            // Notify students about the new assignment
            $this->notifyStudents($assignment);

            return response()->json([
                'success' => true,
                'message' => 'Assignment created successfully!',
                'assignment' => $assignment->load(['schoolClass', 'subject', 'files'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of assignments for the teacher.
     */
    public function index(): View
    {
        $assignments = Assignment::where('teacher_id', auth()->id())
            ->with(['schoolClass', 'subject', 'files'])
            ->orderBy('due_date', 'desc')
            ->paginate(10);

        return view('teacher.assignments.index', compact('assignments'));
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
     * Download an assignment file.
     */
    public function downloadFile($fileId)
    {
        $file = AssignmentFile::findOrFail($fileId);
        $assignment = $file->assignment;
        
        // Check if user has access to this assignment
        if (!auth()->user()->hasRole(['admin', 'super_admin']) && 
            $assignment->teacher_id !== auth()->id()) {
            abort(403);
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
        $file = AssignmentFile::findOrFail($fileId);
        
        if (!$file->isImage()) {
            abort(404);
        }

        $assignment = $file->assignment;
        
        // Check access permissions (same as download)
        if (!auth()->user()->hasRole(['admin', 'super_admin']) && 
            $assignment->teacher_id !== auth()->id()) {
            abort(403);
        }

        $filePath = Storage::disk('private')->path($file->file_path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    }

    /**
     * Notify students about the new assignment.
     */
    private function notifyStudents(Assignment $assignment): void
    {
        $students = $assignment->students();
        
        foreach ($students as $student) {
            // Create database notification
            $student->user->notify(new NewAssignmentNotification($assignment));
        }
    }
}
