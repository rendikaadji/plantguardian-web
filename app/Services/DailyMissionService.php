<?php

namespace App\Services;

use App\Models\ExpLog;
use App\Models\PlantDiscovery;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DailyMissionService
{
    public const TARGET_COUNT = 5;
    public const EXP_REWARD = 150;
    public const COIN_REWARD = 50;

    public function __construct(
        protected RewardService $rewardService
    ) {}

    /**
     * Get the current user's daily mission status for today.
     */
    public function getDailyMissionStatus(User $user): array
    {
        $today = Carbon::today();

        $currentCount = PlantDiscovery::where('user_id', $user->id)
            ->whereDate('discovered_at', $today)
            ->count();

        $percentage = (int) min(100, round(($currentCount / self::TARGET_COUNT) * 100));
        $isCompleted = $currentCount >= self::TARGET_COUNT;

        $isClaimed = ExpLog::where('user_id', $user->id)
            ->where('reason', 'daily_mission_reward')
            ->whereDate('created_at', $today)
            ->exists();

        return [
            'id' => 'daily_field_explorer',
            'title' => 'Penjelajah Lapangan',
            'description' => 'Temukan 5 marker spesies yang diverifikasi oleh Ranger di peta sekitar tempat tinggalmu hari ini.',
            'current_count' => $currentCount,
            'target_count' => self::TARGET_COUNT,
            'percentage' => $percentage,
            'is_completed' => $isCompleted,
            'is_claimed' => $isClaimed,
            'reward' => [
                'exp' => self::EXP_REWARD,
                'coin' => self::COIN_REWARD,
            ],
            'reset_info' => 'Progress teriset otomatis setiap hari pukul 00:00',
        ];
    }

    /**
     * Claim daily mission rewards if completed today and not yet claimed.
     */
    public function claimReward(User $user): array
    {
        $status = $this->getDailyMissionStatus($user);

        if (!$status['is_completed']) {
            throw new Exception('Misi harian belum selesai. Selesaikan 5 penemuan spesies hari ini terlebih dahulu.');
        }

        if ($status['is_claimed']) {
            throw new Exception('Hadiah misi harian untuk hari ini sudah pernah diklaim.');
        }

        return DB::transaction(function () use ($user) {
            $expLog = $this->rewardService->grantExp($user, self::EXP_REWARD, 'daily_mission_reward');
            $coinTx = $this->rewardService->addCoin($user, self::COIN_REWARD, 'daily_mission_reward');

            $user->refresh();

            $updatedStatus = $this->getDailyMissionStatus($user);

            return [
                'status' => $updatedStatus,
                'user' => [
                    'exp' => $user->exp,
                    'coin' => $user->coin,
                ],
                'granted' => [
                    'exp' => self::EXP_REWARD,
                    'coin' => self::COIN_REWARD,
                ],
            ];
        });
    }
}
