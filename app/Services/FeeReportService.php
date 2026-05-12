<?php

namespace App\Services;

use App\Models\FeeChallan;
use App\Models\FeePayment;
use App\Models\SchoolClass;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FeeReportService
{
    /**
     * Get fee recovery report data with caching.
     */
    public static function getFeeRecoveryData(string $academicYear, ?int $month = null): array
    {
        $cacheKey = "fee_recovery_report_{$academicYear}_" . ($month ?? 'all');
        
        return Cache::remember($cacheKey, 900, function () use ($academicYear, $month) {
            return [
                'kpi_cards' => self::getKpiCards($academicYear, $month),
                'class_wise_collection' => self::getClassWiseCollection($academicYear, $month),
                'top_defaulters' => self::getTopDefaulters($academicYear, $month),
                'monthly_collection_trend' => self::getMonthlyCollectionTrend($academicYear),
            ];
        });
    }
    
    /**
     * Get KPI cards data.
     */
    private static function getKpiCards(string $academicYear, ?int $month = null): array
    {
        [$startYear, $endYear] = explode('-', $academicYear);
        
        $query = FeeChallan::query();
        
        // Filter by academic year
        $query->where(function ($q) use ($startYear, $endYear) {
            $q->whereYear('created_at', '>=', $startYear)
              ->whereYear('created_at', '<=', $endYear);
        });
        
        // Filter by month if specified
        if ($month) {
            $query->whereMonth('created_at', $month);
        }
        
        // Calculate totals
        $totalChallaned = $query->sum('amount');
        $totalCollected = $query->sum('paid_amount');
        $totalOutstanding = $totalChallaned - $totalCollected;
        $recoveryPercentage = $totalChallaned > 0 ? ($totalCollected / $totalChallaned) * 100 : 0;
        
        return [
            'total_challaned' => $totalChallaned,
            'total_collected' => $totalCollected,
            'total_outstanding' => $totalOutstanding,
            'recovery_percentage' => round($recoveryPercentage, 1),
        ];
    }
    
    /**
     * Get class-wise collection data.
     */
    private static function getClassWiseCollection(string $academicYear, ?int $month = null): array
    {
        [$startYear, $endYear] = explode('-', $academicYear);
        
        $query = FeeChallan::query()
            ->join('students as s', 'fee_challans.student_id', '=', 's.id')
            ->join('school_classes as sc', 's.class_id', '=', 'sc.id')
            ->select(
                'sc.id as class_id',
                'sc.grade_level',
                'sc.section',
                DB::raw('COUNT(s.id) as total_students'),
                DB::raw('SUM(fee_challans.amount) as total_challaned'),
                DB::raw('SUM(fee_challans.paid_amount) as total_paid'),
                DB::raw('SUM(fee_challans.amount - fee_challans.paid_amount) as outstanding')
            )
            ->where('s.status', 'active')
            ->where(function ($q) use ($startYear, $endYear) {
                $q->whereYear('fee_challans.created_at', '>=', $startYear)
                  ->whereYear('fee_challans.created_at', '<=', $endYear);
            });
        
        // Filter by month if specified
        if ($month) {
            $query->whereMonth('fee_challans.created_at', $month);
        }
        
        $results = $query->groupBy('sc.id', 'sc.grade_level', 'sc.section')
            ->orderBy('sc.grade_level')
            ->orderBy('sc.section')
            ->get();
        
        return $results->map(function ($result) {
            $recoveryPercentage = $result->total_challaned > 0 ? 
                ($result->total_paid / $result->total_challaned) * 100 : 0;
            
            return [
                'class_id' => $result->class_id,
                'class_name' => "Grade {$result->grade_level} - {$result->section}",
                'total_students' => $result->total_students,
                'total_challaned' => $result->total_challaned,
                'total_paid' => $result->total_paid,
                'outstanding' => $result->outstanding,
                'recovery_percentage' => round($recoveryPercentage, 1),
            ];
        })->toArray();
    }
    
    /**
     * Get top defaulters.
     */
    private static function getTopDefaulters(string $academicYear, ?int $month = null): array
    {
        [$startYear, $endYear] = explode('-', $academicYear);
        
        $query = FeeChallan::query()
            ->join('students as s', 'fee_challans.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->join('school_classes as sc', 's.class_id', '=', 'sc.id')
            ->select(
                'u.name',
                's.roll_number',
                'sc.grade_level',
                'sc.section',
                DB::raw('SUM(fee_challans.amount - fee_challans.paid_amount) as total_due'),
                DB::raw('MIN(fee_challans.due_date) as earliest_due_date')
            )
            ->where('fee_challans.status', '!=', 'paid')
            ->where('fee_challans.due_date', '<', Carbon::now())
            ->where('s.status', 'active')
            ->where(function ($q) use ($startYear, $endYear) {
                $q->whereYear('fee_challans.created_at', '>=', $startYear)
                  ->whereYear('fee_challans.created_at', '<=', $endYear);
            });
        
        // Filter by month if specified
        if ($month) {
            $query->whereMonth('fee_challans.created_at', $month);
        }
        
        $defaulters = $query->groupBy('s.id', 'u.name', 's.roll_number', 'sc.grade_level', 'sc.section')
            ->orderBy('total_due', 'desc')
            ->limit(5)
            ->get();
        
        return $defaulters->map(function ($defaulter) {
            return [
                'name' => $defaulter->name,
                'roll_number' => $defaulter->roll_number,
                'class' => "Grade {$defaulter->grade_level} - {$defaulter->section}",
                'total_due' => $defaulter->total_due,
                'days_overdue' => Carbon::parse($defaulter->earliest_due_date)->diffInDays(Carbon::now()),
            ];
        })->toArray();
    }
    
    /**
     * Get monthly collection trend for the academic year.
     */
    private static function getMonthlyCollectionTrend(string $academicYear): array
    {
        [$startYear, $endYear] = explode('-', $academicYear);
        
        $trend = [];
        
        // Get data for each month in the academic year
        for ($month = 1; $month <= 12; $month++) {
            $collected = FeePayment::whereMonth('payment_date', $month)
                ->whereYear('payment_date', '>=', $startYear)
                ->whereYear('payment_date', '<=', $endYear)
                ->sum('amount');
            
            $target = 1000000; // Example target per month
            
            $trend[] = [
                'month' => Carbon::createFromDate($startYear, $month, 1)->format('M Y'),
                'collected' => $collected,
                'target' => $target,
            ];
        }
        
        return $trend;
    }
    
    /**
     * Clear fee report cache.
     */
    public static function clearCache(): void
    {
        // Clear all fee report caches
        $cacheKeys = Cache::getRedis()->keys('fee_recovery_report_*');
        
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
