<?php

namespace App\Http\Controllers;

use App\Services\HrDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class HrDashboardController extends Controller
{
    /**
     * Display the HR dashboard.
     */
    public function index(): View
    {
        $dashboardData = HrDashboardService::getDashboardData();
        
        return view('hr.dashboard', compact('dashboardData'));
    }
    
    /**
     * Refresh the HR dashboard data.
     */
    public function refresh(): JsonResponse
    {
        HrDashboardService::clearCache();
        $dashboardData = HrDashboardService::getDashboardData();
        
        return response()->json([
            'success' => true,
            'message' => 'HR dashboard data refreshed successfully',
            'data' => $dashboardData
        ]);
    }
}
