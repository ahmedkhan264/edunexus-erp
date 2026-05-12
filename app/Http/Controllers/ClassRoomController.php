<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ClassRoomController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::withCount(['students', 'sections'])
            ->orderBy('grade_level')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.classes.index', compact('classes'));
    }

    public function store(ClassRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $class = SchoolClass::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Class created successfully!',
            'class' => $class->loadCount(['students', 'sections'])
        ]);
    }

    public function update(ClassRequest $request, SchoolClass $class): JsonResponse
    {
        $validated = $request->validated();
        
        $class->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Class updated successfully!',
            'class' => $class->loadCount(['students', 'sections'])
        ]);
    }

    public function destroy(SchoolClass $class): JsonResponse
    {
        // Check if class has active students
        $studentCount = $class->students()->count();
        
        if ($studentCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete class. It has {$studentCount} active student(s). Please reassign or delete students first."
            ], 422);
        }

        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully!'
        ]);
    }

    public function show(SchoolClass $class): JsonResponse
    {
        return response()->json([
            'success' => true,
            'class' => $class->loadCount(['students', 'sections'])
        ]);
    }
}
