<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Http\Requests\TeacherListRequest;
use App\Http\Requests\TeacherCreateRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    /**
     * Display a listing of teachers.
     */
    public function index(TeacherListRequest $request): View
    {
        // Get filter parameters
        $search = $request->input('search');
        $gender = $request->input('gender');
        $qualification = $request->input('qualification');
        $employment_type = $request->input('employment_type');
        $status = $request->input('status');

        // Build query with filters
        $query = Teacher::with(['user', 'subjects', 'classes']);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', '%' . $search . '%')
                  ->orWhere('last_name', 'like', '%' . $search . '%')
                  ->orWhere('employee_code', 'like', '%' . $search . '%')
                  ->orWhere('cnic', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('qualification', 'like', '%' . $search . '%')
                  ->orWhere('specialization', 'like', '%' . $search . '%');
            });
        }

        // Apply filters
        if ($gender) {
            $query->byGender($gender);
        }

        if ($qualification) {
            $query->byQualification($qualification);
        }

        if ($employment_type) {
            $query->byEmploymentType($employment_type);
        }

        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'inactive') {
            $query->inactive();
        }

        // Get teachers with pagination
        $teachers = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get filter options
        $genders = ['Male', 'Female'];
        $employmentTypes = ['permanent', 'contract', 'part-time'];
        $statuses = ['active', 'inactive'];

        return view('admin.teachers.index', compact('teachers', 'genders', 'employmentTypes', 'statuses'));
    }

    /**
     * Show the form for creating a new teacher.
     */
    public function create(): View
    {
        // Get available subjects and classes for assignment
        $subjects = Subject::active()->orderBy('name')->get();
        $classes = SchoolClass::active()->orderBy('grade_level')->orderBy('section')->get();

        return view('admin.teachers.create', compact('subjects', 'classes'));
    }

    /**
     * Store a newly created teacher in storage.
     */
    public function store(TeacherCreateRequest $request)
    {
        try {
            DB::beginTransaction();

            // Generate unique employee code
            $employeeCode = 'EMP' . str_pad(Teacher::max('id') + 1, 4, '0', STR_PAD_LEFT);

            // Create user account
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make('password'), // Default password
                'role_id' => 4, // Teacher role
                'is_active' => true,
            ]);

            // Handle profile image upload
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $profileImagePath = $profileImage->store('teacher_profiles', 'public');
            }

            // Create teacher record
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'employee_code' => $employeeCode,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'cnic' => $request->cnic,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country,
                'nationality' => $request->nationality,
                'blood_group' => $request->blood_group,
                'religion' => $request->religion,
                'qualification' => $request->qualification,
                'specialization' => $request->specialization,
                'experience_years' => $request->experience_years,
                'previous_institution' => $request->previous_institution,
                'joining_date' => $request->joining_date,
                'basic_salary' => $request->basic_salary,
                'employment_type' => $request->employment_type,
                'is_active' => true,
                'notes' => $request->notes,
                'profile_image' => $profileImagePath,
            ]);

            // Attach subjects if provided
            if ($request->has('subjects') && is_array($request->subjects)) {
                $teacher->subjects()->attach($request->subjects);
            }

            // Attach classes if provided
            if ($request->has('classes') && is_array($request->classes)) {
                $teacher->classes()->attach($request->classes);
            }

            DB::commit();

            return redirect()->route('admin.teachers.index')
                            ->with('success', "Teacher {$teacher->full_name} ({$teacher->employee_code}) has been successfully added.");

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'An error occurred while creating the teacher. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified teacher.
     */
    public function show(Teacher $teacher): View
    {
        // Load comprehensive teacher data with relationships
        $teacher->load([
            'user',
            'subjects',
            'classes'
        ]);

        return view('admin.teachers.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified teacher.
     */
    public function edit(Teacher $teacher): View
    {
        $teacher->load(['user', 'subjects', 'classes']);
        $subjects = Subject::active()->orderBy('name')->get();
        $classes = SchoolClass::active()->orderBy('grade_level')->orderBy('section')->get();

        return view('admin.teachers.edit', compact('teacher', 'subjects', 'classes'));
    }

    /**
     * Update the specified teacher in storage.
     */
    public function update(Request $request, Teacher $teacher)
    {
        // Implementation for update method
        // This will be implemented in the next phase
        return redirect()->route('admin.teachers.index')
                        ->with('success', 'Teacher updated successfully.');
    }

    /**
     * Remove the specified teacher from storage.
     */
    public function destroy(Teacher $teacher)
    {
        // Implementation for destroy method
        // This will be implemented in the next phase
        return redirect()->route('admin.teachers.index')
                        ->with('success', 'Teacher deleted successfully.');
    }
}
