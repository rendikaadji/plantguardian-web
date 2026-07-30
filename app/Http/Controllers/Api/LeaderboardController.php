<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LeaderboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function __construct(
        protected LeaderboardService $leaderboardService
    ) {}

    /**
     * Get real-time leaderboard for the current ongoing week.
     */
    public function current(): JsonResponse
    {
        $leaderboard = $this->leaderboardService->getCurrentLeaderboard();

        return response()->json([
            'message' => 'Papan peringkat minggu ini berhasil dimuat.',
            'data' => $leaderboard,
        ]);
    }

    /**
     * Get historical weekly leaderboard snapshots.
     */
    public function history(Request $request): JsonResponse
    {
        $history = $this->leaderboardService->getLeaderboardHistory($request->user()->id);

        return response()->json([
            'message' => 'Riwayat peringkat mingguan berhasil dimuat.',
            'data' => $history,
        ]);
    }
}
