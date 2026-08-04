<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AchievementService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function __construct(
        protected AchievementService $achievementService
    ) {}

    /**
     * Display achievement page with dynamic real-time progress.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $achievementsList = $this->achievementService->getAchievementsForUser($user);

        // Map list into associative array keyed by code
        $achievements = [];
        $unlockedCount = 0;
        $totalCount = count($achievementsList);

        foreach ($achievementsList as $ach) {
            $achievements[$ach['code']] = $ach;
            if ($ach['is_claimed'] || $ach['is_completed']) {
                $unlockedCount++;
            }
        }

        $completionPercentage = (int) round(($unlockedCount / max(1, $totalCount)) * 100);

        return view('achievement', compact('achievements', 'unlockedCount', 'totalCount', 'completionPercentage'));
    }

    /**
     * Claim achievement reward securely.
     */
    public function claim(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'achievement_code' => 'required|string',
        ]);

        $user = $request->user();
        $code = $validated['achievement_code'];

        try {
            $res = $this->achievementService->claimAchievement($user, $code);

            return response()->json([
                'success' => true,
                'message' => __('achievement.claim_success', ['exp' => $res['exp_gained'], 'coin' => $res['coin_gained']]),
                'user_exp' => $res['user_exp'],
                'user_coin' => $res['user_coin'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
