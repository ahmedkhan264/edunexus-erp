<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Role;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    // ❌ The constructor has been removed – middleware is now applied in routes/web.php

    public function index(Request $request)
    {
        $query = Teacher::with(['user', 'subjects', 'classes']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                })->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $teachers = $query->paginate(15)->withQueryString();
        $departments = Teacher::distinct('department')->pluck('department');

        return view('admin.teachers.index', compact('teachers', 'departments'));
    }

    public function create()
    {
        $subjects = Subject::all();
        $classes = SchoolClass::all();
        return view('admin.teachers.create', compact('subjects', 'classes'));
    }

    public function store(StoreTeacherRequest $request)
    {
        $user = User::create([
            'name' => $request->full_name,
            'email' => $request->email,
            'password' => Hash::make($request->password ?? 'password'),
            'role_id' => Role::where('name', 'Teacher')->first()->id,
        ]);

        $photoPath = null;
        if ($request->hasFile('profile_photo')) {
            $photoPath = $request->file('profile_photo')->store('teachers', 'public');
        }

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'employee_code' => $request->employee_code,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'phone' => $request->phone,
            'address' => $request->address,
            'profile_photo' => $photoPath,
            'qualification' => $request->qualification,
            'experience_years' => $request->experience_years,
            'department' => $request->department,
            'employment_type' => $request->employment_type,
            'basic_salary' => $request->basic_salary,
            'joining_date' => $request->joining_date,
        ]);

        if ($request->has('subjects')) {
            $teacher->subjects()->sync($request->subjects);
        }
        if ($request->has('classes')) {
            $teacher->classes()->sync($request->classes);
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher created successfully.');
    }

    public function show(Teacher $teacher)
    {
        $teacher->load(['user', 'subjects', 'classes', 'attendances', 'payrolls']);
        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(Teacher $teacher)
    {
        $teacher->load('subjects', 'classes');
        $subjects = Subject::all();
        $classes = SchoolClass::all();
        return view('admin.teachers.edit', compact('teacher', 'subjects', 'classes'));
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        if ($request->email != $teacher->user->email) {
            $teacher->user->update(['email' => $request->email]);
        }
        if ($request->full_name != $teacher->user->name) {
            $teacher->user->update(['name' => $request->full_name]);
        }

        if ($request->hasFile('profile_photo')) {
            if ($teacher->profile_photo && Storage::disk('public')->exists($teacher->profile_photo)) {
                Storage::disk('public')->delete($teacher->profile_photo);
            }
            $photoPath = $request->file('profile_photo')->store('teachers', 'public');
            $teacher->profile_photo = $photoPath;
        }

        $teacher->fill([
            'employee_code' => $request->employee_code,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'phone' => $request->phone,
            'address' => $request->address,
            'qualification' => $request->qualification,
            'experience_years' => $request->experience_years,
            'department' => $request->department,
            'employment_type' => $request->employment_type,
            'basic_salary' => $request->basic_salary,
            'joining_date' => $request->joining_date,
        ]);
        $teacher->save();

        if ($request->has('subjects')) {
            $teacher->subjects()->sync($request->subjects);
        } else {
            $teacher->subjects()->detach();
        }
        if ($request->has('classes')) {
            $teacher->classes()->sync($request->classes);
        } else {
            $teacher->classes()->detach();
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(Teacher $teacher)
    {
        if ($teacher->profile_photo && Storage::disk('public')->exists($teacher->profile_photo)) {
            Storage::disk('public')->delete($teacher->profile_photo);
        }
        $teacher->subjects()->detach();
        $teacher->classes()->detach();
        $teacher->user->delete();
        $teacher->delete();

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher deleted successfully.');
    }
}