<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Cache dashboard data for better performance
        $cacheKey = 'admin_dashboard_' . auth()->id();
        
        try {
            $dashboardData = Cache::remember($cacheKey, 300, function () {
                return [
                    'totalStudents' => $this->getTotalStudents(),
                    'totalTeachers' => $this->getTotalTeachers(),
                    'totalClasses' => $this->getTotalClasses(),
                    'todayAttendance' => $this->getTodayAttendance(),
                    'monthlyFeeCollection' => $this->getMonthlyFeeCollection(),
                    'pendingFees' => $this->getPendingFees(),
                    'recentAdmissions' => $this->getRecentAdmissions(),
                    'upcomingEvents' => $this->getUpcomingEvents(),
                    'feeCollectionChart' => $this->getFeeCollectionChart(),
                    'attendanceTrend' => $this->getAttendanceTrend(),
                ];
            });
        } catch (\Exception $e) {
            // Clear corrupted cache and retry
            Cache::forget($cacheKey);
            $dashboardData = Cache::remember($cacheKey, 300, function () {
                return [
                    'totalStudents' => $this->getTotalStudents(),
                    'totalTeachers' => $this->getTotalTeachers(),
                    'totalClasses' => $this->getTotalClasses(),
                    'todayAttendance' => $this->getTodayAttendance(),
                    'monthlyFeeCollection' => $this->getMonthlyFeeCollection(),
                    'pendingFees' => $this->getPendingFees(),
                    'recentAdmissions' => $this->getRecentAdmissions(),
                    'upcomingEvents' => $this->getUpcomingEvents(),
                    'feeCollectionChart' => $this->getFeeCollectionChart(),
                    'attendanceTrend' => $this->getAttendanceTrend(),
                ];
            });
        }
        
        return view('admin.dashboard', $dashboardData);
    }
    
    private function getTotalStudents()
    {
        return Cache::remember('total_students', 3600, function () {
            return User::whereHas('role', function($query) {
                $query->where('slug', 'student');
            })->where('is_active', true)->count();
        });
    }
    
    private function getTotalTeachers()
    {
        return Cache::remember('total_teachers', 3600, function () {
            return User::whereHas('role', function($query) {
                $query->where('slug', 'teacher');
            })->where('is_active', true)->count();
        });
    }
    
    private function getTotalClasses()
    {
        return Cache::remember('total_classes', 3600, function () {
            return SchoolClass::count();
        });
    }
    
    private function getTodayAttendance()
    {
        return DB::table('attendance')
            ->whereDate('date', today())
            ->where('status', 'present')
            ->count();
    }
    
    private function getMonthlyFeeCollection()
    {
        return DB::table('fee_payments')
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount_paid');
    }
    
    private function getPendingFees()
    {
        return DB::table('fee_challans')
            ->where('status', 'unpaid')
            ->where('due_date', '<', now())
            ->count();
    }
    
    private function getRecentAdmissions()
    {
        return User::whereHas('role', function($query) {
                $query->where('slug', 'student');
            })
            ->where('is_active', true)
            ->latest()
            ->take(5)
            ->get(['id', 'name', 'email', 'created_at'])
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->toDateTimeString(),
                ];
            })
            ->toArray();
    }
    
    private function getUpcomingEvents()
    {
        return [
            [
                'title' => 'Monthly Test Schedule',
                'date' => now()->addDays(5)->format('M d, Y'),
                'type' => 'academic'
            ],
            [
                'title' => 'Fee Due Reminder',
                'date' => now()->addDays(3)->format('M d, Y'),
                'type' => 'fee'
            ],
            [
                'title' => 'Teacher Meeting',
                'date' => now()->addDays(7)->format('M d, Y'),
                'type' => 'meeting'
            ]
        ];
    }
    
    private function getFeeCollectionChart()
    {
        $data = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        
        foreach ($months as $index => $month) {
            $data[] = [
                'month' => $month,
                'collected' => rand(50000, 80000),
                'target' => 75000
            ];
        }
        
        return $data;
    }
    
    private function getAttendanceTrend()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'date' => $date->format('M d'),
                'present' => rand(180, 220),
                'absent' => rand(20, 40)
            ];
        }
        
        return $data;
    }
}
