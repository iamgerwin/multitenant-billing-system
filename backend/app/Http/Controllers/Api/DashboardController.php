<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private StatsService $statsService
    ) {}

    /**
     * Get dashboard statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $organizationId = $request->user()->organization_id;
        $stats = $this->statsService->getDashboardStats($organizationId);

        return response()->json([
            'data' => $stats,
        ]);
    }
}
