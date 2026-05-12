<?php

namespace App\Http\Controllers;

use App\Http\Requests\SectionRequest;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(Request $request): View
    {
        $selectedClassId = $request->get('class_id');
        $classes = SchoolClass::where('is_active', true)->orderBy('grade_level')->orderBy('name')->get();
        
        $sectionsQuery = Section::with(['class', 'teacher', 'students'])
            ->withCount(['students']);
            
        if ($selectedClassId) {
            $sectionsQuery->where('class_id', $selectedClassId);
        }
        
        $sections = $sectionsQuery->orderBy('name')->paginate(20);

        return view('admin.sections.index', compact('sections', 'classes', 'selectedClassId'));
    }

    public function store(SectionRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $section = Section::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Section created successfully!',
            'section' => $section->load(['class', 'teacher', 'students_count'])
        ]);
    }

    public function update(SectionRequest $request, Section $section): JsonResponse
    {
        $validated = $request->validated();
        
        $section->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Section updated successfully!',
            'section' => $section->load(['class', 'teacher', 'students_count'])
        ]);
    }

    public function destroy(Section $section): JsonResponse
    {
        // Check if section has active students
        $studentCount = $section->students()->count();
        
        if ($studentCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete section. It has {$studentCount} active student(s). Please reassign or delete students first."
            ], 422);
        }

        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section deleted successfully!'
        ]);
    }

    public function show(Section $section): JsonResponse
    {
        return response()->json([
            'success' => true,
            'section' => $section->load(['class', 'teacher', 'students'])
        ]);
    }

    public function getByClass($classId): JsonResponse
    {
        $sections = Section::where('class_id', $classId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'sections' => $sections
        ]);
    }
}
