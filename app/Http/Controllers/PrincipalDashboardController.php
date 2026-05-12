<?php

namespace App\Http\Controllers;

use App\Services\DashboardDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class PrincipalDashboardController extends Controller
{
    /**
     * Display the executive dashboard.
     */
    public function index(): View
    {
        $dashboardData = DashboardDataService::getDashboardData();
        
        return view('principal.dashboard', compact('dashboardData'));
    }
    
    /**
     * Refresh dashboard data and clear cache.
     */
    public function refresh(): JsonResponse
    {
        // Clear the dashboard cache
        DashboardDataService::clearCache();
        
        // Get fresh data
        $dashboardData = DashboardDataService::getDashboardData();
        
        return response()->json([
            'success' => true,
            'message' => 'Dashboard data refreshed successfully',
            'data' => $dashboardData
        ]);
    }
}
