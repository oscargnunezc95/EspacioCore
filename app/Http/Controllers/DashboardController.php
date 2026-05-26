<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(Request $request, DashboardService $dashboardService)
    {
        $period = $request->input('period', now()->format('Y-m'));
        
        $metrics = $dashboardService->getDashboardMetrics($period);

        return view('studios.dashboard', [
            'period'              => $metrics['period'],
            'activeStudentsCount' => $metrics['activeStudentsCount'],
            'newStudentsCount'    => $metrics['newStudentsCount'],
            'occupancyRate'       => $metrics['occupancyData']['occupancyRate'],
            'monthlyRevenue'      => $metrics['financialData']['monthlyRevenue'],
            'revenuePercentages'  => $metrics['financialData']['revenuePercentages'],
            'todayClasses'        => $metrics['todayClasses'],
            'studentsWithDebt'    => $metrics['studentsWithDebt'],
            'historicalData'      => $metrics['historicalData'], // <--- NUEVO
        ]);
    }
}