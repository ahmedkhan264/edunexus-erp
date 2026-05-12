<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $selectedClassId = $request->get('class_id');
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        
        $subjectsQuery = Subject::with(['class', 'teacher']);
            
        if ($selectedClassId) {
            $subjectsQuery->where('class_id', $selectedClassId);
        }
        
        $subjects = $subjectsQuery->orderBy('name')->paginate(20);

        return view('admin.subjects.index', compact('subjects', 'classes', 'selectedClassId'));
    }

    public function store(SubjectRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $subject = Subject::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subject created successfully!',
            'subject' => $subject->load(['class', 'teacher'])
        ]);
    }

    public function update(SubjectRequest $request, Subject $subject): JsonResponse
    {
        $validated = $request->validated();
        
        $subject->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subject updated successfully!',
            'subject' => $subject->load(['class', 'teacher'])
        ]);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        // Check if subject is assigned to any classes or teachers
        if ($subject->class_id || $subject->teacher_id) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete subject. It is assigned to a class or teacher. Please remove assignments first.'
            ], 422);
        }

        $subject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully!'
        ]);
    }

    public function show(Subject $subject): JsonResponse
    {
        return response()->json([
            'success' => true,
            'subject' => $subject->load(['class', 'teacher'])
        ]);
    }

    public function getByClass($classId): JsonResponse
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

    public function assignToClass(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'nullable|exists:teachers,id'
        ]);

        $subject = Subject::find($validated['subject_id']);
        $subject->update([
            'class_id' => $validated['class_id'],
            'teacher_id' => $validated['teacher_id'] ?? null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Subject assigned successfully!',
            'subject' => $subject->load(['class', 'teacher'])
        ]);
    }
}
