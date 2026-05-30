<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'class']);

        // Search
        $search = $request->search;

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        // Class filter
        $classId = $request->class_id;

        if ($request->filled('class_id')) {
            $query->where('class_id', $classId);
        }

        // Status filter
        $status = $request->status;

        if ($request->filled('status')) {
            $query->where('status', $status);
        }

        // Gender filter
        $gender = $request->gender;

        if ($request->filled('gender')) {
            $query->where('gender', $gender);
        }

        // Pagination
        $perPage = (int) $request->get('per_page', 15);
        $perPageOptions = [10, 15, 25, 50, 100];

        // Students
        $students = $query->paginate($perPage)->withQueryString();

        // Classes
        $classes = SchoolClass::all();

        // Statistics
        $stats = [
            'total'     => Student::count(),
            'active'    => Student::where('status', 'Enrolled')->count(),
            'graduated' => Student::where('status', 'Graduated')->count(),
            'suspended' => Student::where('status', 'Suspended')->count(),
            'withdrawn' => Student::where('status', 'Withdrawn')->count(),
            'male'      => Student::where('gender', 'Male')->count(),
            'female'    => Student::where('gender', 'Female')->count(),
        ];

        // Status dropdown
        $statusOptions = [
            'Enrolled'  => 'Enrolled',
            'Graduated' => 'Graduated',
            'Suspended' => 'Suspended',
            'Withdrawn' => 'Withdrawn',
        ];

        // Gender dropdown
        $genderOptions = [
            'Male'   => 'Male',
            'Female' => 'Female',
        ];

        return view('admin.students.index', compact(
            'students',
            'classes',
            'stats',
            'search',
            'classId',
            'status',
            'gender',
            'statusOptions',
            'genderOptions',
            'perPage',
            'perPageOptions'
        ));
    }

    public function create()
    {
        $classes = SchoolClass::all();

        return view('admin.students.create', compact('classes'));
    }

    public function store(StoreStudentRequest $request)
    {
        $user = User::create([
            'name'     => $request->full_name,
            'email'    => $request->email,
            'password' => Hash::make($request->password ?? 'password'),
            'role_id'  => Role::where('name', 'Student')->first()->id,
        ]);

        $photoPath = null;

        if ($request->hasFile('profile_photo')) {
            $photoPath = $request->file('profile_photo')
                ->store('students', 'public');
        }

        $student = Student::create([
            'user_id'           => $user->id,
            'admission_number'  => $request->admission_number,
            'class_id'          => $request->class_id,
            'roll_number'       => $request->roll_number,
            'gender'            => $request->gender,
            'date_of_birth'     => $request->date_of_birth,
            'blood_group'       => $request->blood_group,
            'phone'             => $request->phone,
            'address'           => $request->address,
            'profile_photo'     => $photoPath,
            'status'            => $request->status,
            'previous_school'   => $request->previous_school,
            'previous_gpa'      => $request->previous_gpa,
        ]);

        // Parent profile
        if ($request->filled('father_name')) {
            $student->parentProfile()->create([
                'father_name'        => $request->father_name,
                'father_phone'       => $request->father_phone,
                'father_occupation'  => $request->father_occupation,
                'mother_name'        => $request->mother_name,
                'mother_phone'       => $request->mother_phone,
                'mother_occupation'  => $request->mother_occupation,
                'guardian_name'      => $request->guardian_name,
                'guardian_phone'     => $request->guardian_phone,
                'guardian_relation'  => $request->guardian_relation,
                'address'            => $request->parent_address,
            ]);
        }

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Student created successfully.');
    }

    public function show(Student $student)
    {
        $student->load([
            'user',
            'class',
            'parentProfile',
            'attendances'
        ]);

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student)
    {
        $student->load('parentProfile');

        $classes = SchoolClass::all();

        return view('admin.students.edit', compact(
            'student',
            'classes'
        ));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        // Update user
        if ($request->email != $student->user->email) {
            $student->user->update([
                'email' => $request->email
            ]);
        }

        if ($request->full_name != $student->user->name) {
            $student->user->update([
                'name' => $request->full_name
            ]);
        }

        // Update photo
        if ($request->hasFile('profile_photo')) {

            if (
                $student->profile_photo &&
                Storage::disk('public')->exists($student->profile_photo)
            ) {
                Storage::disk('public')->delete($student->profile_photo);
            }

            $photoPath = $request->file('profile_photo')
                ->store('students', 'public');

            $student->profile_photo = $photoPath;
        }

        // Update student
        $student->fill([
            'admission_number' => $request->admission_number,
            'class_id'         => $request->class_id,
            'roll_number'      => $request->roll_number,
            'gender'           => $request->gender,
            'date_of_birth'    => $request->date_of_birth,
            'blood_group'      => $request->blood_group,
            'phone'            => $request->phone,
            'address'          => $request->address,
            'status'           => $request->status,
            'previous_school'  => $request->previous_school,
            'previous_gpa'     => $request->previous_gpa,
        ]);

        $student->save();

        // Parent profile
        $parentData = [
            'father_name'        => $request->father_name,
            'father_phone'       => $request->father_phone,
            'father_occupation'  => $request->father_occupation,
            'mother_name'        => $request->mother_name,
            'mother_phone'       => $request->mother_phone,
            'mother_occupation'  => $request->mother_occupation,
            'guardian_name'      => $request->guardian_name,
            'guardian_phone'     => $request->guardian_phone,
            'guardian_relation'  => $request->guardian_relation,
            'address'            => $request->parent_address,
        ];

        if ($student->parentProfile) {
            $student->parentProfile->update($parentData);
        } else {
            $student->parentProfile()->create($parentData);
        }

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        // Delete photo
        if (
            $student->profile_photo &&
            Storage::disk('public')->exists($student->profile_photo)
        ) {
            Storage::disk('public')->delete($student->profile_photo);
        }

        // Delete parent profile
        if ($student->parentProfile) {
            $student->parentProfile->delete();
        }

        // Delete user
        if ($student->user) {
            $student->user->delete();
        }

        // Delete student
        $student->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }

    /**
     * Print-friendly student list
     */
    public function printIndex(Request $request)
    {
        $query = Student::with(['user', 'class']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                })
                ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        // Class filter
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        $students = $query->get();

        $classes = SchoolClass::all();

        $stats = [
            'total'     => Student::count(),
            'active'    => Student::where('status', 'Enrolled')->count(),
            'graduated' => Student::where('status', 'Graduated')->count(),
            'suspended' => Student::where('status', 'Suspended')->count(),
            'withdrawn' => Student::where('status', 'Withdrawn')->count(),
            'male'      => Student::where('gender', 'Male')->count(),
            'female'    => Student::where('gender', 'Female')->count(),
        ];

        return view('admin.students.print', compact(
            'students',
            'classes',
            'stats'
        ));
    }
}