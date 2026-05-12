<?php

namespace App\Http\Controllers;

use App\Services\LibraryDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class LibraryDashboardController extends Controller
{
    /**
     * Display the library dashboard.
     */
    public function index(): View
    {
        $dashboardData = LibraryDashboardService::getDashboardData();
        
        return view('library.dashboard', compact('dashboardData'));
    }
    
    /**
     * Refresh the library dashboard data.
     */
    public function refresh(): JsonResponse
    {
        LibraryDashboardService::clearCache();
        $dashboardData = LibraryDashboardService::getDashboardData();
        
        return response()->json([
            'success' => true,
            'message' => 'Library dashboard data refreshed successfully',
            'data' => $dashboardData
        ]);
    }
}
