<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StaffPerformanceService
{
    /**
     * Get staff performance data with caching.
     */
    public static function getStaffPerformanceData(int $month, int $year, ?string $department = null): array
    {
        $cacheKey = "staff_performance_{$month}_{$year}_" . ($department ?? 'all');
        
        return Cache::remember($cacheKey, 86400, function () use ($month, $year, $department) {
            return [
                'kpi_cards' => self::getKpiCards($month, $year, $department),
                'teacher_performance' => self::getTeacherPerformance($month, $year, $department),
            ];
        });
    }
    
    /**
     * Get KPI cards data.
     */
    private static function getKpiCards(int $month, int $year, ?string $department = null): array
    {
        $teachers = self::getTeachersQuery($department)->get();
        
        if ($teachers->isEmpty()) {
            return [
                'avg_teacher_attendance' => 0,
                'avg_tasks_completed' => 0,
                'avg_late_days' => 0,
                'pending_grading_count' => 0,
            ];
        }
        
        $totalAttendance = 0;
        $totalTasksCompleted = 0;
        $totalLateDays = 0;
        $pendingGradingCount = 0;
        
        foreach ($teachers as $teacher) {
            $attendanceData = self::getTeacherAttendance($teacher->id, $month, $year);
            $taskData = self::getTeacherTasks($teacher->id, $month, $year);
            
            $totalAttendance += $attendanceData['percentage'];
            $totalTasksCompleted += $taskData['completion_percentage'];
            $totalLateDays += $attendanceData['late_days'];
            $pendingGradingCount += $taskData['pending_grading'];
        }
        
        $teacherCount = $teachers->count();
        
        return [
            'avg_teacher_attendance' => round($totalAttendance / $teacherCount, 1),
            'avg_tasks_completed' => round($totalTasksCompleted / $teacherCount, 1),
            'avg_late_days' => round($totalLateDays / $teacherCount, 1),
            'pending_grading_count' => $pendingGradingCount,
        ];
    }
    
    /**
     * Get teacher performance data.
     */
    private static function getTeacherPerformance(int $month, int $year, ?string $department = null): array
    {
        $teachers = self::getTeachersQuery($department)->get();
        
        $performanceData = [];
        
        foreach ($teachers as $teacher) {
            $attendanceData = self::getTeacherAttendance($teacher->id, $month, $year);
            $taskData = self::getTeacherTasks($teacher->id, $month, $year);
            $classData = self::getTeacherClasses($teacher->id, $month, $year);
            
            // Calculate performance score
            $attendanceWeight = 0.4;
            $taskWeight = 0.3;
            $gradingWeight = 0.3;
            
            $attendanceScore = $attendanceData['percentage'];
            $taskScore = $taskData['completion_percentage'];
            $gradingScore = $taskData['grading_completion_percentage'];
            
            $performanceScore = ($attendanceScore * $attendanceWeight) + 
                               ($taskScore * $taskWeight) + 
                               ($gradingScore * $gradingWeight);
            
            $performanceData[] = [
                'teacher_id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'department' => $teacher->department ?? 'Teaching',
                'attendance_percentage' => $attendanceData['percentage'],
                'late_count' => $attendanceData['late_days'],
                'classes_taken' => $classData['classes_taken'],
                'tasks_completed' => $taskData['completed'],
                'tasks_total' => $taskData['total'],
                'tasks_completion_percentage' => $taskData['completion_percentage'],
                'pending_grading' => $taskData['pending_grading'],
                'grading_completion_percentage' => $taskData['grading_completion_percentage'],
                'performance_score' => round($performanceScore, 1),
                'performance_level' => self::getPerformanceLevel($performanceScore),
                'performance_color' => self::getPerformanceColor($performanceScore),
            ];
        }
        
        // Sort by performance score (highest first)
        usort($performanceData, function ($a, $b) {
            return $b['performance_score'] - $a['performance_score'];
        });
        
        return $performanceData;
    }
    
    /**
     * Get teachers query with optional department filter.
     */
    private static function getTeachersQuery(?string $department = null)
    {
        $query = User::where('role_id', 3) // Assuming role_id 3 is teacher
                    ->where('status', 'active');
        
        if ($department) {
            $query->where('department', $department);
        }
        
        return $query;
    }
    
    /**
     * Get teacher attendance data for a specific month.
     */
    private static function getTeacherAttendance(int $teacherId, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        // Get working days (excluding weekends)
        $workingDays = 0;
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if (!$date->isWeekend()) {
                $workingDays++;
            }
        }
        
        if ($workingDays === 0) {
            return [
                'percentage' => 0,
                'late_days' => 0,
                'present_days' => 0,
            ];
        }
        
        // Get attendance records
        $attendanceRecords = Attendance::where('user_id', $teacherId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();
        
        $presentDays = $attendanceRecords->where('status', 'present')->count();
        $lateDays = $attendanceRecords->where('status', 'late')->count();
        
        // For calculation, late days count as half present
        $effectivePresentDays = $presentDays + ($lateDays * 0.5);
        $attendancePercentage = ($effectivePresentDays / $workingDays) * 100;
        
        return [
            'percentage' => round($attendancePercentage, 1),
            'late_days' => $lateDays,
            'present_days' => $presentDays,
            'working_days' => $workingDays,
        ];
    }
    
    /**
     * Get teacher task data for a specific month.
     */
    private static function getTeacherTasks(int $teacherId, int $month, int $year): array
    {
        $totalTasks = Task::where('assigned_to', $teacherId)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();
        
        $completedTasks = Task::where('assigned_to', $teacherId)
            ->where('status', 'completed')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();
        
        $completionPercentage = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        
        // Mock pending grading count (would need Assignment model in real implementation)
        $pendingGrading = rand(0, 5); // Placeholder
        $totalGrading = $pendingGrading + rand(5, 15); // Placeholder
        $gradingCompletionPercentage = $totalGrading > 0 ? (($totalGrading - $pendingGrading) / $totalGrading) * 100 : 100;
        
        return [
            'total' => $totalTasks,
            'completed' => $completedTasks,
            'completion_percentage' => round($completionPercentage, 1),
            'pending_grading' => $pendingGrading,
            'grading_completion_percentage' => round($gradingCompletionPercentage, 1),
        ];
    }
    
    /**
     * Get teacher classes data for a specific month.
     */
    private static function getTeacherClasses(int $teacherId, int $month, int $year): array
    {
        // Mock data - would need Timetable model in real implementation
        $classesTaken = rand(15, 25); // Placeholder
        
        return [
            'classes_taken' => $classesTaken,
        ];
    }
    
    /**
     * Get performance level based on score.
     */
    private static function getPerformanceLevel(float $score): string
    {
        if ($score >= 90) {
            return 'Excellent';
        } elseif ($score >= 75) {
            return 'Good';
        } elseif ($score >= 60) {
            return 'Satisfactory';
        } else {
            return 'Needs Improvement';
        }
    }
    
    /**
     * Get performance color based on score.
     */
    private static function getPerformanceColor(float $score): string
    {
        if ($score >= 90) {
            return 'success';
        } elseif ($score >= 75) {
            return 'info';
        } elseif ($score >= 60) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
    
    /**
     * Clear staff performance cache.
     */
    public static function clearCache(): void
    {
        // Clear all staff performance caches
        $cacheKeys = Cache::getRedis()->keys('staff_performance_*');
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
