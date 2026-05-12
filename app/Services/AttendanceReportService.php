<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AttendanceReportService
{
    /**
     * Generate attendance report data.
     */
    public function generateReport(array $filters): array
    {
        $cacheKey = $this->getCacheKey($filters);
        
        return Cache::remember($cacheKey, 3600, function () use ($filters) {
            $fromDate = Carbon::parse($filters['from_date']);
            $toDate = Carbon::parse($filters['to_date']);
            
            // Build base query
            $query = Student::with(['user', 'class'])
                ->where('is_active', true);
            
            // Apply filters
            if (!empty($filters['class_id'])) {
                $query->where('class_id', $filters['class_id']);
            }
            
            if (!empty($filters['section_id'])) {
                $query->where('section_id', $filters['section_id']);
            }
            
            if (!empty($filters['student_id'])) {
                $query->where('id', $filters['student_id']);
            }
            
            $students = $query->orderBy('class_id')
                ->orderBy('roll_number')
                ->orderBy('user.name')
                ->get();
            
            // Get attendance data for all students
            $attendanceData = $this->getAttendanceData($students, $fromDate, $toDate);
            
            // Process student statistics
            $studentStats = [];
            foreach ($students as $student) {
                $studentAttendance = $attendanceData[$student->user_id] ?? [];
                
                $totalDays = count($studentAttendance);
                $presentDays = collect($studentAttendance)->where('status', 'present')->count();
                $absentDays = collect($studentAttendance)->where('status', 'absent')->count();
                $lateDays = collect($studentAttendance)->where('status', 'late')->count();
                
                $attendancePercentage = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;
                
                $studentStats[] = [
                    'id' => $student->id,
                    'user_id' => $student->user_id,
                    'roll_number' => $student->roll_number,
                    'name' => $student->user->name,
                    'class_name' => $student->class->name,
                    'total_days' => $totalDays,
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'late_days' => $lateDays,
                    'attendance_percentage' => round($attendancePercentage, 2),
                    'status_color' => $this->getAttendanceColor($attendancePercentage)
                ];
            }
            
            // Calculate summary statistics
            $summary = $this->calculateSummary($studentStats);
            
            return [
                'students' => $studentStats,
                'summary' => $summary,
                'filters' => $filters,
                'date_range' => [
                    'from' => $fromDate->format('M d, Y'),
                    'to' => $toDate->format('M d, Y'),
                    'days' => $fromDate->diffInDays($toDate) + 1
                ]
            ];
        });
    }
    
    /**
     * Generate chart data for attendance trends.
     */
    public function generateChartData(array $filters): array
    {
        $fromDate = Carbon::parse($filters['from_date']);
        $toDate = Carbon::parse($filters['to_date']);
        
        // Get daily attendance data
        $dailyData = Attendance::whereBetween('date', [$fromDate, $toDate])
            ->selectRaw('date, status, COUNT(*) as count')
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date');
        
        // Build date range
        $dates = [];
        $presentData = [];
        $absentData = [];
        $lateData = [];
        
        for ($date = $fromDate->copy(); $date <= $toDate; $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dates[] = $date->format('M d');
            
            $dayData = $dailyData[$dateStr] ?? collect();
            $presentData[] = $dayData->where('status', 'present')->sum('count');
            $absentData[] = $dayData->where('status', 'absent')->sum('count');
            $lateData[] = $dayData->where('status', 'late')->sum('count');
        }
        
        return [
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $presentData,
                    'backgroundColor' => 'rgba(40, 167, 69, 0.8)',
                    'borderColor' => 'rgba(40, 167, 69, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Absent',
                    'data' => $absentData,
                    'backgroundColor' => 'rgba(220, 53, 69, 0.8)',
                    'borderColor' => 'rgba(220, 53, 69, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Late',
                    'data' => $lateData,
                    'backgroundColor' => 'rgba(255, 193, 7, 0.8)',
                    'borderColor' => 'rgba(255, 193, 7, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }
    
    /**
     * Get attendance data for students.
     */
    private function getAttendanceData($students, $fromDate, $toDate): array
    {
        $userIds = $students->pluck('user_id');
        
        $attendance = Attendance::whereBetween('date', [$fromDate, $toDate])
            ->whereIn('user_id', $userIds)
            ->select('user_id', 'date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('user_id');
        
        return $attendance->map(function ($records) {
            return $records->keyBy('date')->toArray();
        })->toArray();
    }
    
    /**
     * Calculate summary statistics.
     */
    private function calculateSummary(array $studentStats): array
    {
        if (empty($studentStats)) {
            return [
                'total_students' => 0,
                'average_attendance' => 0,
                'total_present' => 0,
                'total_absent' => 0,
                'total_late' => 0,
                'perfect_attendance' => 0,
                'low_attendance' => 0
            ];
        }
        
        $totalStudents = count($studentStats);
        $totalPresent = collect($studentStats)->sum('present_days');
        $totalAbsent = collect($studentStats)->sum('absent_days');
        $totalLate = collect($studentStats)->sum('late_days');
        $totalDays = collect($studentStats)->sum('total_days');
        
        $averageAttendance = $totalDays > 0 ? ($totalPresent / $totalDays) * 100 : 0;
        $perfectAttendance = collect($studentStats)->where('attendance_percentage', 100)->count();
        $lowAttendance = collect($studentStats)->where('attendance_percentage', '<', 75)->count();
        
        return [
            'total_students' => $totalStudents,
            'average_attendance' => round($averageAttendance, 2),
            'total_present' => $totalPresent,
            'total_absent' => $totalAbsent,
            'total_late' => $totalLate,
            'perfect_attendance' => $perfectAttendance,
            'low_attendance' => $lowAttendance
        ];
    }
    
    /**
     * Get color based on attendance percentage.
     */
    private function getAttendanceColor(float $percentage): string
    {
        if ($percentage >= 95) {
            return 'success';
        } elseif ($percentage >= 85) {
            return 'info';
        } elseif ($percentage >= 75) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
    
    /**
     * Generate cache key for filters.
     */
    private function getCacheKey(array $filters): string
    {
        $key = 'attendance_report_';
        $key .= md5(serialize($filters));
        
        return $key;
    }
    
    /**
     * Clear cache for attendance reports.
     */
    public function clearCache(): void
    {
        // Clear all attendance report cache
        Cache::flush();
    }
}
