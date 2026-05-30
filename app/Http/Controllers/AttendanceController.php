<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->middleware('role:Admin,Teacher,Principal');
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $classes = SchoolClass::all();
        $selectedClass = $request->class_id ? SchoolClass::find($request->class_id) : null;
        $date = $request->date ?? now()->toDateString();

        $attendances = collect();
        if ($selectedClass) {
            $students = Student::where('class_id', $selectedClass->id)->with('user')->get();
            $attendances = Attendance::where('class_id', $selectedClass->id)
                ->where('date', $date)
                ->get()
                ->keyBy('user_id');
        } else {
            $students = collect();
        }

        return view('admin.attendance.index', compact('classes', 'selectedClass', 'date', 'students', 'attendances'));
    }

    public function mark(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'in:present,absent,late,holiday',
        ]);

        $classId = $request->class_id;
        $date = $request->date;

        // Check if attendance already marked for this class & date (prevent duplicates)
        $existing = Attendance::where('class_id', $classId)
            ->where('date', $date)
            ->exists();

        if ($existing) {
            return back()->withErrors(['date' => 'Attendance already marked for this class on this date.']);
        }

        $students = Student::where('class_id', $classId)->get();
        $attendances = $request->attendance;

        foreach ($students as $student) {
            $status = $attendances[$student->user_id] ?? 'absent';
            Attendance::create([
                'user_id' => $student->user_id,
                'class_id' => $classId,
                'date' => $date,
                'status' => $status,
                'marked_by' => Auth::id(),
            ]);
        }

        return redirect()->route('admin.attendance.index', ['class_id' => $classId, 'date' => $date])
            ->with('success', 'Attendance marked successfully.');
    }

    public function report(Request $request)
    {
        $data = $this->attendanceService->getAttendanceReport(
            $request->class_id,
            $request->start_date,
            $request->end_date
        );

        $classes = SchoolClass::all();
        return view('admin.attendance.report', compact('data', 'classes'));
    }
}
