<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\ParentProfile;
use App\Models\User;
use App\Http\Requests\StudentListRequest;
use App\Http\Requests\StudentCreateRequest;
use App\Http\Requests\StudentUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(StudentListRequest $request): View
    {
        // Get filter parameters
        $search = $request->input('search');
        $classId = $request->input('class_id');
        $status = $request->input('status');
        $gender = $request->input('gender');
        $perPage = $request->input('per_page', 15);

        // Start query with relationships
        $query = Student::with(['user:id,name,email', 'class:id,name,grade_level,section']);

        // Apply search filter
        if ($search) {
            $query->search($search);
        }

        // Apply class filter
        if ($classId) {
            $query->byClass($classId);
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply gender filter
        if ($gender) {
            $query->byGender($gender);
        }

        // Apply default filters for admin view
        $query->active();

        // Get paginated results
        $students = $query->orderBy('created_at', 'desc')
                         ->paginate($perPage)
                         ->withQueryString();

        // Get all classes for filter dropdown
        $classes = SchoolClass::active()
                             ->orderBy('grade_level')
                             ->orderBy('section')
                             ->get(['id', 'name', 'grade_level', 'section']);

        // Get status options for filter
        $statusOptions = [
            'enrolled' => 'Enrolled',
            'graduated' => 'Graduated',
            'suspended' => 'Suspended',
            'withdrawn' => 'Withdrawn',
        ];

        // Get gender options for filter
        $genderOptions = [
            'Male' => 'Male',
            'Female' => 'Female',
        ];

        // Get per page options
        $perPageOptions = [15, 25, 50, 100];

        // Statistics for dashboard
        $stats = [
            'total_students' => Student::count(),
            'enrolled_students' => Student::enrolled()->count(),
            'graduated_students' => Student::graduated()->count(),
            'active_students' => Student::active()->count(),
        ];

        return view('admin.students.index', compact(
            'students',
            'classes',
            'statusOptions',
            'genderOptions',
            'perPageOptions',
            'stats',
            'search',
            'classId',
            'status',
            'gender',
            'perPage'
        ));
    }

    /**
     * Show the form for creating a new student.
     */
    public function create(): View
    {
        $classes = SchoolClass::active()
                             ->orderBy('grade_level')
                             ->orderBy('section')
                             ->get();

        return view('admin.students.create', compact('classes'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(StudentCreateRequest $request)
    {
        try {
            DB::beginTransaction();

            // Generate unique student ID and admission number
            $studentId = 'STU' . str_pad(Student::max('id') + 1, 4, '0', STR_PAD_LEFT);
            $admissionNumber = 'ADM' . date('Y') . str_pad(Student::max('id') + 1, 4, '0', STR_PAD_LEFT);

            // Create user account
            $user = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make('password'), // Default password
                'role_id' => 5, // Student role
                'is_active' => true,
            ]);

            // Handle profile image upload
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImage = $request->file('profile_image');
                $profileImagePath = $profileImage->store('profile_images', 'public');
            }

            // Create student record
            $student = Student::create([
                'user_id' => $user->id,
                'class_id' => $request->class_id,
                'student_id' => $studentId,
                'admission_number' => $admissionNumber,
                'admission_date' => $request->admission_date,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
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
                'religion' => $request->religion,
                'blood_group' => $request->blood_group,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relation' => $request->emergency_contact_relation,
                'previous_school_gpa' => $request->previous_school_gpa,
                'previous_school_name' => $request->previous_school_name,
                'is_active' => true,
                'status' => 'enrolled',
                'notes' => $request->notes,
                'profile_image' => $profileImagePath,
            ]);

            // Create parent profile
            ParentProfile::create([
                'student_id' => $student->id,
                'father_name' => $request->father_name,
                'father_cnic' => $request->father_cnic,
                'father_phone' => $request->father_phone,
                'father_occupation' => $request->father_occupation,
                'father_email' => $request->father_email,
                'mother_name' => $request->mother_name,
                'mother_cnic' => $request->mother_cnic,
                'mother_phone' => $request->mother_phone,
                'mother_occupation' => $request->mother_occupation,
                'mother_email' => $request->mother_email,
                'guardian_name' => $request->guardian_name,
                'guardian_cnic' => $request->guardian_cnic,
                'guardian_phone' => $request->guardian_phone,
                'guardian_occupation' => $request->guardian_occupation,
                'guardian_email' => $request->guardian_email,
                'guardian_relation' => $request->guardian_relation,
                'guardian_address' => $request->guardian_address,
                'is_primary_guardian' => $request->boolean('is_primary_guardian', false),
                'notes' => null,
            ]);

            DB::commit();

            return redirect()->route('admin.students.index')
                            ->with('success', "Student {$student->full_name} ({$student->student_id}) has been successfully admitted.");

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'An error occurred while creating the student. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student): View
    {
        // Load comprehensive student data with relationships
        $student->load([
            'user',
            'class',
            'parentProfile',
            'attendance' => function ($query) {
                $query->latest()->take(30); // Last 30 attendance records
            }
        ]);

        // Calculate attendance statistics
        $attendanceStats = $student->attendance_stats;

        // Get recent attendance for display
        $recentAttendance = $student->attendance()
            ->orderBy('date', 'desc')
            ->take(10)
            ->get();

        // For now, we'll create placeholder data for fee challans, results, and documents
        // These will be implemented when we create those modules
        $feeChallans = collect(); // Placeholder for FeeChallan::where('student_id', $student->id)->get()
        $results = collect(); // Placeholder for Result::where('student_id', $student->id)->get()
        $documents = collect(); // Placeholder for Document::where('student_id', $student->id)->get()

        return view('admin.students.show', compact('student', 'attendanceStats', 'recentAttendance', 'feeChallans', 'results', 'documents'));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student): View
    {
        $student->load(['user', 'class']);
        $classes = SchoolClass::active()
                             ->orderBy('grade_level')
                             ->orderBy('section')
                             ->get();

        return view('admin.students.edit', compact('student', 'classes'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(StudentUpdateRequest $request, Student $student)
    {
        try {
            DB::beginTransaction();

            // Update user account
            $student->user->update([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
            ]);

            // Handle profile image upload
            $profileImagePath = $student->profile_image;
            if ($request->hasFile('profile_image')) {
                // Delete old profile image if exists
                if ($profileImagePath && storage::disk('public')->exists($profileImagePath)) {
                    storage::disk('public')->delete($profileImagePath);
                }
                
                $profileImage = $request->file('profile_image');
                $profileImagePath = $profileImage->store('profile_images', 'public');
            }

            // Update student record
            $student->update([
                'class_id' => $request->class_id,
                'admission_date' => $request->admission_date,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
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
                'religion' => $request->religion,
                'blood_group' => $request->blood_group,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relation' => $request->emergency_contact_relation,
                'previous_school_gpa' => $request->previous_school_gpa,
                'previous_school_name' => $request->previous_school_name,
                'is_active' => $request->boolean('is_active'),
                'status' => $request->status,
                'graduation_date' => $request->graduation_date,
                'notes' => $request->notes,
                'profile_image' => $profileImagePath,
            ]);

            // Update or create parent profile
            $parentProfile = $student->parentProfile ?? new ParentProfile(['student_id' => $student->id]);
            
            $parentProfile->fill([
                'father_name' => $request->father_name,
                'father_cnic' => $request->father_cnic,
                'father_phone' => $request->father_phone,
                'father_occupation' => $request->father_occupation,
                'father_email' => $request->father_email,
                'mother_name' => $request->mother_name,
                'mother_cnic' => $request->mother_cnic,
                'mother_phone' => $request->mother_phone,
                'mother_occupation' => $request->mother_occupation,
                'mother_email' => $request->mother_email,
                'guardian_name' => $request->guardian_name,
                'guardian_cnic' => $request->guardian_cnic,
                'guardian_phone' => $request->guardian_phone,
                'guardian_occupation' => $request->guardian_occupation,
                'guardian_email' => $request->guardian_email,
                'guardian_relation' => $request->guardian_relation,
                'guardian_address' => $request->guardian_address,
                'is_primary_guardian' => $request->boolean('is_primary_guardian', false),
            ]);
            
            $parentProfile->save();

            DB::commit();

            return redirect()->route('admin.students.index')
                            ->with('success', "Student {$student->full_name} ({$student->student_id}) has been successfully updated.");

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'An error occurred while updating the student. Please try again. Error: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        // Implementation for destroy method
        // This will be implemented in the next phase
        return redirect()->route('admin.students.index')
                        ->with('success', 'Student deleted successfully.');
    }

    /**
     * Print student list.
     */
    public function print(StudentListRequest $request): View
    {
        // Get filter parameters (same as index method)
        $search = $request->input('search');
        $classId = $request->input('class_id');
        $status = $request->input('status');
        $gender = $request->input('gender');

        // Start query with relationships
        $query = Student::with(['user:id,name,email', 'class:id,name,grade_level,section']);

        // Apply filters (same as index method)
        if ($search) {
            $query->search($search);
        }
        if ($classId) {
            $query->byClass($classId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($gender) {
            $query->byGender($gender);
        }
        $query->active();

        // Get all results (no pagination for print)
        $students = $query->orderBy('last_name')->orderBy('first_name')->get();

        // Get filter labels for print header
        $filterLabels = [];
        if ($search) $filterLabels[] = "Search: {$search}";
        if ($classId) {
            $class = SchoolClass::find($classId);
            $filterLabels[] = "Class: " . ($class ? $class->name : 'N/A');
        }
        if ($status) $filterLabels[] = "Status: " . ucfirst($status);
        if ($gender) $filterLabels[] = "Gender: {$gender}";

        return view('admin.students.print', compact('students', 'filterLabels'));
    }
}
