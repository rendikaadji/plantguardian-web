<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DailyMissionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyMissionController extends Controller
{
    public function __construct(
        protected DailyMissionService $dailyMissionService
    ) {}

    /**
     * Get status of current user's daily mission.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $this->dailyMissionService->getDailyMissionStatus($request->user());

        return response()->json([
            'success' => true,
            'data' => $status,
        ]);
    }

    /**
     * Claim daily mission rewards.
     */
    public function claim(Request $request): JsonResponse
    {
        try {
            $result = $this->dailyMissionService->claimReward($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Hadiah misi harian berhasil diklaim!',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
