<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Generate PDF report for student attendance
     */
    public function studentAttendancePdf(Request $request)
    {
        // Validate request parameters
        $validated = $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'class_id' => 'nullable|exists:school_classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'student_id' => 'nullable|exists:students,id',
        ]);

        // Get filter parameters
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');
        $classId = $request->get('class_id');
        $sectionId = $request->get('section_id');
        $studentId = $request->get('student_id');

        // Get students based on filters
        $studentsQuery = Student::with(['user', 'class']);
        
        if ($classId) {
            $studentsQuery->where('class_id', $classId);
        }
        
        if ($sectionId) {
            $studentsQuery->where('section_id', $sectionId);
        }
        
        if ($studentId) {
            $studentsQuery->where('id', $studentId);
        }
        
        $students = $studentsQuery->get();
        
        // Get attendance records for date range
        $attendanceQuery = Attendance::with(['user', 'class'])
            ->whereBetween('date', [$fromDate, $toDate]);
        
        if ($classId) {
            $attendanceQuery->where('class_id', $classId);
        }
        
        if ($studentId) {
            $attendanceQuery->where('student_id', $studentId);
        }
        
        $attendances = $attendanceQuery->get();
        
        // Calculate summary statistics
        $totalPresent = $attendances->where('status', 'present')->count();
        $totalAbsent = $attendances->where('status', 'absent')->count();
        $totalLate = $attendances->where('status', 'late')->count();
        
        $totalDays = Carbon::parse($fromDate)->diffInDays(Carbon::parse($toDate)) + 1;
        $totalStudents = $students->count();
        $averageAttendance = $totalStudents > 0 && $totalDays > 0 
            ? ($totalPresent / ($totalStudents * $totalDays)) * 100 
            : 0;
        
        // Prepare student-wise attendance data
        $studentAttendanceData = [];
        $perfectAttendance = 0;
        $lowAttendance = 0;
        
        foreach ($students as $student) {
            $studentAttendances = $attendances->where('student_id', $student->id);
            $presentDays = $studentAttendances->where('status', 'present')->count();
            $absentDays = $studentAttendances->where('status', 'absent')->count();
            $lateDays = $studentAttendances->where('status', 'late')->count();
            $totalStudentDays = $studentAttendances->count();
            
            $attendancePercentage = $totalStudentDays > 0 
                ? ($presentDays / $totalStudentDays) * 100 
                : 0;
            
            if ($attendancePercentage >= 99.9) $perfectAttendance++;
            if ($attendancePercentage < 75) $lowAttendance++;
            
            // Determine status color
            $statusColor = $attendancePercentage >= 95 ? 'success' : 
                          ($attendancePercentage >= 85 ? 'info' : 
                          ($attendancePercentage >= 75 ? 'warning' : 'danger'));
            
            $studentAttendanceData[] = [
                'roll_number' => $student->roll_number ?? '-',
                'name' => $student->user->name ?? $student->name ?? 'N/A',
                'class_name' => $student->class->name ?? 'N/A',
                'total_days' => $totalStudentDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'attendance_percentage' => $attendancePercentage,
                'status_color' => $statusColor,
            ];
        }
        
        // Sort by attendance percentage (highest first)
        usort($studentAttendanceData, function($a, $b) {
            return $b['attendance_percentage'] <=> $a['attendance_percentage'];
        });
        
        // Prepare complete report data
        $reportData = [
            'summary' => [
                'total_students' => $totalStudents,
                'total_present' => $totalPresent,
                'total_absent' => $totalAbsent,
                'total_late' => $totalLate,
                'average_attendance' => $averageAttendance,
                'perfect_attendance' => $perfectAttendance,
                'low_attendance' => $lowAttendance,
            ],
            'students' => $studentAttendanceData,
            'date_range' => [
                'from' => Carbon::parse($fromDate)->format('Y-m-d'),
                'to' => Carbon::parse($toDate)->format('Y-m-d'),
                'days' => $totalDays,
            ],
            'filters' => [
                'class' => $classId ? (SchoolClass::find($classId)->name ?? 'N/A') : 'All Classes',
                'section' => $sectionId ? 'Section ' . $sectionId : 'All Sections',
                'student' => $studentId ? ($students->first()->user->name ?? 'Selected Student') : 'All Students',
            ]
        ];
        
        // Get classes for any additional display needs
        $classes = SchoolClass::all();
        $selectedClass = $classId ? SchoolClass::find($classId) : null;
        
        // Generate PDF
        $pdf = Pdf::loadView('reports.student-attendance-pdf', compact('reportData', 'validated', 'attendances', 'classes', 'selectedClass'));
        $pdf->setPaper('A4', 'landscape');
        
        // Return PDF for download
        return $pdf->download('student-attendance-report-' . date('Y-m-d') . '.pdf');
    }
    
    // Add other report methods as needed
}