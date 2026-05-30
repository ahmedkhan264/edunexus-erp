<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role->name ?? '';

        // Redirect non-admin roles to their specific dashboards
        if ($role === 'Teacher') {
            return redirect()->route('teacher.dashboard');
        }
        if ($role === 'Student') {
            return redirect()->route('student.dashboard');
        }

        // ----- Basic Counts -----
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();
        $totalClasses = SchoolClass::count();

        // ----- Today's Attendance -----
        $today = Carbon::today()->toDateString();
        $todayAttendance = Attendance::whereDate('date', $today)->count();

        // ----- Fee Related (dummy data) -----
        $monthlyFeeCollection = 125000;
        $pendingFees = 25000;

        // ----- Recent Admissions (latest 5 students) -----
        $recentAdmissions = Student::latest()->take(5)->get()->map(function ($student) {
            return [
                'name' => $student->full_name ?? $student->name,
                'email' => $student->email ?? 'No email',
                'created_at' => $student->created_at,
            ];
        });

        // ----- Upcoming Events (dummy) -----
        $upcomingEvents = [
            ['title' => 'Mid-Term Exams', 'date' => 'Dec 15-20, 2024', 'type' => 'academic'],
            ['title' => 'Fee Submission Deadline', 'date' => 'Dec 10, 2024', 'type' => 'fee'],
            ['title' => 'Parent-Teacher Meeting', 'date' => 'Dec 22, 2024', 'type' => 'meeting'],
        ];

        // ----- Fee Collection Chart Data (last 6 months) -----
        $feeCollectionChart = $this->getFeeCollectionChartData();

        // ----- Attendance Trend Data (last 7 days) -----
        $attendanceTrend = $this->getAttendanceTrendData();

        return view('admin.dashboard', compact(
            'totalStudents',
            'totalTeachers',
            'todayAttendance',
            'monthlyFeeCollection',
            'totalClasses',
            'pendingFees',
            'recentAdmissions',
            'upcomingEvents',
            'feeCollectionChart',
            'attendanceTrend'
        ));
    }

    /**
     * Generate fee collection chart data (last 6 months)
     */
    private function getFeeCollectionChartData()
    {
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $months->push([
                'month' => Carbon::now()->subMonths($i)->format('M'),
                'collected' => rand(80000, 150000), // Replace with actual sum
                'target' => 120000,
            ]);
        }
        return $months;
    }

    /**
     * Generate attendance trend data (last 7 days)
     */
    private function getAttendanceTrendData()
    {
        $trend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $present = Attendance::whereDate('date', $date)->where('status', 'Present')->count();
            $absent = Attendance::whereDate('date', $date)->where('status', 'Absent')->count();
            $trend->push([
                'date' => $date->format('D'),
                'present' => $present,
                'absent' => $absent,
            ]);
        }
        return $trend;
    }

    /**
     * Teacher Dashboard
     */
    public function teacherDashboard()
    {
        // Example data – replace with actual queries later
        $totalStudents = Student::count();
        $todayClasses = 4; // placeholder
        $lessonsCreated = 23; // placeholder
        $attendanceRate = 92; // placeholder

        $todaysSchedule = [
            ['time' => '8:00 - 9:00', 'class' => 'Grade 10 - A', 'subject' => 'Mathematics', 'room' => '201'],
            ['time' => '9:30 - 10:30', 'class' => 'Grade 10 - B', 'subject' => 'Mathematics', 'room' => '202'],
            ['time' => '11:00 - 12:00', 'class' => 'Grade 9 - A', 'subject' => 'Physics', 'room' => '301'],
            ['time' => '13:00 - 14:00', 'class' => 'Grade 9 - B', 'subject' => 'Physics', 'room' => '302'],
        ];

        $recentActivities = [
            ['icon' => 'upload', 'text' => 'Uploaded lesson: "Algebra Basics"', 'time' => '2h ago', 'color' => 'primary'],
            ['icon' => 'user-check', 'text' => 'Marked attendance for Grade 10-A', 'time' => '3h ago', 'color' => 'success'],
            ['icon' => 'clock', 'text' => 'Checked in at 8:45 AM', 'time' => 'Today', 'color' => 'info'],
            ['icon' => 'edit', 'text' => 'Requested attendance correction', 'time' => 'Yesterday', 'color' => 'warning'],
        ];

        $announcements = [
            ['title' => 'Staff Meeting Tomorrow', 'message' => 'Staff meeting tomorrow at 3:00 PM in conference room.', 'posted_by' => 'Principal', 'date' => '2 days ago', 'type' => 'info'],
            ['title' => 'Schedule Change', 'message' => 'Grade 10 Mathematics schedule updated. Check new timetable.', 'posted_by' => 'Admin', 'date' => '3 days ago', 'type' => 'warning'],
        ];

        return view('teacher.dashboard', compact(
            'totalStudents',
            'todayClasses',
            'lessonsCreated',
            'attendanceRate',
            'todaysSchedule',
            'recentActivities',
            'announcements'
        ));
    }

    /**
     * Student Dashboard
     */
    public function studentDashboard()
    {
        $user = Auth::user();

        // Get student record (assuming one-to-one relationship)
        $student = Student::where('user_id', $user->id)->first();

        // Attendance rate for current month
        $attendanceRate = 0;
        $totalDays = Attendance::where('user_id', $user->id)->whereMonth('date', now()->month)->count();
        $presentDays = Attendance::where('user_id', $user->id)->whereMonth('date', now()->month)->whereIn('status', ['Present', 'Late'])->count();
        if ($totalDays > 0) {
            $attendanceRate = round(($presentDays / $totalDays) * 100);
        } else {
            $attendanceRate = 92; // fallback placeholder
        }

        // Placeholder data – replace with actual counts from your models
        $pendingAssignments = 3;
        $upcomingExams = 2;
        $overdueBooks = 0;

        // Today's classes (example)
        $todaysClasses = [
            ['time' => '8:00 - 9:00', 'subject' => 'Mathematics', 'teacher' => 'Mr. Smith', 'room' => '201'],
            ['time' => '9:30 - 10:30', 'subject' => 'Physics', 'teacher' => 'Dr. Jones', 'room' => '301'],
            ['time' => '11:00 - 12:00', 'subject' => 'English', 'teacher' => 'Ms. Brown', 'room' => '105'],
        ];

        // Recent activities (example)
        $recentActivities = [
            ['icon' => 'check-circle', 'text' => 'Attendance marked for today', 'time' => 'Today', 'color' => 'success'],
            ['icon' => 'file-alt', 'text' => 'Assignment submitted: Math Homework', 'time' => 'Yesterday', 'color' => 'primary'],
            ['icon' => 'book', 'text' => 'Borrowed "Physics Fundamentals"', 'time' => '3 days ago', 'color' => 'info'],
        ];

        // Announcements
        $announcements = [
            ['title' => 'Holiday Notice', 'message' => 'School closed on Monday for public holiday.', 'posted_by' => 'Principal', 'type' => 'info'],
            ['title' => 'Exam Schedule', 'message' => 'Mid-term exams start next week. Check timetable.', 'posted_by' => 'Exam Cell', 'type' => 'warning'],
        ];

        return view('student.dashboard', compact(
            'attendanceRate',
            'pendingAssignments',
            'upcomingExams',
            'overdueBooks',
            'todaysClasses',
            'recentActivities',
            'announcements'
        ));
    }
}