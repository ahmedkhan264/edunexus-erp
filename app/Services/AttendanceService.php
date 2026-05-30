<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\SchoolClass;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Mark attendance for a class on a given date.
     * Returns true if successful, false if already marked.
     */
    public function markAttendance($classId, $date, array $statuses)
    {
        $existing = Attendance::where('class_id', $classId)
            ->where('date', $date)
            ->exists();

        if ($existing) {
            return false;
        }

        $students = Student::where('class_id', $classId)->get();
        $attendanceRecords = [];

        foreach ($students as $student) {
            $status = $statuses[$student->user_id] ?? 'absent';
            $attendanceRecords[] = [
                'user_id' => $student->user_id,
                'class_id' => $classId,
                'date' => $date,
                'status' => $status,
                'marked_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Attendance::insert($attendanceRecords);
        return true;
    }

    /**
     * Get attendance report for a class between dates.
     */
    public function getAttendanceReport($classId, $startDate, $endDate)
    {
        $class = SchoolClass::find($classId);
        if (!$class) {
            return null;
        }

        $students = Student::where('class_id', $classId)->with('user')->get();

        $attendances = Attendance::where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        $report = [];
        foreach ($students as $student) {
            $studentAttendance = $attendances->get($student->user_id, collect());
            $totalDays = $studentAttendance->count();
            $present = $studentAttendance->where('status', 'present')->count();
            $absent = $studentAttendance->where('status', 'absent')->count();
            $late = $studentAttendance->where('status', 'late')->count();
            $holiday = $studentAttendance->where('status', 'holiday')->count();
            $percentage = $totalDays > 0 ? round(($present / $totalDays) * 100, 2) : 0;

            $report[] = [
                'student_name' => $student->user->name,
                'roll_number' => $student->roll_number,
                'total_days' => $totalDays,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'holiday' => $holiday,
                'percentage' => $percentage,
            ];
        }

        return $report;
    }

    /**
     * Calculate overall class attendance percentage for a given period.
     */
    public function classAttendancePercentage($classId, $startDate, $endDate)
    {
        $totalRecords = Attendance::where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->count();

        $presentRecords = Attendance::where('class_id', $classId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'present')
            ->count();

        return $totalRecords > 0 ? round(($presentRecords / $totalRecords) * 100, 2) : 0;
    }
}
