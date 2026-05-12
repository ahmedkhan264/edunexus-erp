<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionFile;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionController extends Controller
{
    /**
     * Display a listing of assignments for the student.
     */
    public function index(): View
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $assignments = Assignment::forClass($student->class_id, $student->section)
            ->with(['subject', 'teacher', 'files', 'submissions' => function($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->orderBy('due_date', 'desc')
            ->get();

        return view('student.assignments.index', compact('assignments'));
    }

    /**
     * Show the form for submitting an assignment.
     */
    public function create(Assignment $assignment): View
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        // Check if assignment belongs to student's class
        if ($assignment->class_id !== $student->class_id || $assignment->section !== $student->section) {
            abort(403, 'You are not authorized to submit this assignment.');
        }

        // Check if assignment is overdue
        if ($assignment->isOverdue()) {
            abort(403, 'This assignment is overdue.');
        }

        // Check if student has already submitted
        $existingSubmission = $assignment->getStudentSubmission($student->id);

        return view('student.assignments.submit', compact('assignment', 'existingSubmission'));
    }

    /**
     * Store a newly created assignment submission in storage.
     */
    public function store(Request $request, Assignment $assignment): JsonResponse
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found.'
            ], 403);
        }

        // Check authorization
        if ($assignment->class_id !== $student->class_id || $assignment->section !== $student->section) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to submit this assignment.'
            ], 403);
        }

        // Check if assignment is overdue
        if ($assignment->isOverdue()) {
            return response()->json([
                'success' => false,
                'message' => 'This assignment is overdue and cannot be submitted.'
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'nullable|string|max:2000',
            'files' => 'nullable|array|max:5',
            'files.*' => [
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip',
                'max:10240' // 10MB per file
            ]
        ]);

        try {
            // Check for existing submission
            $existingSubmission = $assignment->getStudentSubmission($student->id);
            
            if ($existingSubmission) {
                // Check if resubmission is allowed
                if (!$assignment->allow_resubmission) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Resubmission is not allowed for this assignment.'
                    ], 403);
                }

                // Check if resubmission time window is still open (24 hours)
                if (!$existingSubmission->canBeEdited()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Resubmission window has closed (24 hours after initial submission).'
                    ], 403);
                }

                // Update existing submission
                $existingSubmission->content = $validated['content'];
                $existingSubmission->save();

                // Remove old files and add new ones
                $existingSubmission->files()->delete();

                // Handle new file uploads
                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $filePath = $file->store('assignment_submissions/' . $existingSubmission->id, 'private');
                        
                        AssignmentSubmissionFile::create([
                            'assignment_submission_id' => $existingSubmission->id,
                            'file_path' => $filePath,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize()
                        ]);
                    }
                }

                $submission = $existingSubmission;

            } else {
                // Create new submission
                $submission = AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'content' => $validated['content']
                ]);

                // Handle file uploads
                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $file) {
                        $filePath = $file->store('assignment_submissions/' . $submission->id, 'private');
                        
                        AssignmentSubmissionFile::create([
                            'assignment_submission_id' => $submission->id,
                            'file_path' => $filePath,
                            'original_name' => $file->getClientOriginalName(),
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize()
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Assignment submitted successfully!',
                'submission' => $submission->load(['files'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit assignment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified assignment submission.
     */
    public function show(Assignment $assignment): View
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        // Check if assignment belongs to student's class
        if ($assignment->class_id !== $student->class_id || $assignment->section !== $student->section) {
            abort(403, 'You are not authorized to view this assignment.');
        }

        $submission = $assignment->getStudentSubmission($student->id);

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    /**
     * Download a submission file.
     */
    public function downloadFile($fileId)
    {
        $file = AssignmentSubmissionFile::findOrFail($fileId);
        $submission = $file->assignmentSubmission;
        $student = auth()->user()->student;
        
        // Check if user owns this submission
        if (!$student || $submission->student_id !== $student->id) {
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
        $file = AssignmentSubmissionFile::findOrFail($fileId);
        
        if (!$file->isImage()) {
            abort(404);
        }

        $submission = $file->assignmentSubmission;
        $student = auth()->user()->student;
        
        // Check if user owns this submission
        if (!$student || $submission->student_id !== $student->id) {
            abort(403);
        }

        $filePath = Storage::disk('private')->path($file->file_path);
        
        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    }
}
