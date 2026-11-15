<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Mobile\MobileDashboardService;
use Illuminate\Http\Request;

class MobileDashboardApiController extends Controller
{
    protected $dashboardService;

    public function __construct(MobileDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * GET /api/v1/mobile/dashboard
     */
    public function summary(Request $request)
    {
        $data = $this->dashboardService->getDashboardData($request->user());

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
