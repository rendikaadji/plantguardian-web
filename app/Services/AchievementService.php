<?php

namespace App\Services;

use App\Models\Friendship;
use App\Models\InventoryItem;
use App\Models\ItemRequest;
use App\Models\PlantDiscovery;
use App\Models\Planting;
use App\Models\PlantSighting;
use App\Models\User;
use App\Models\UserAchievement;
use Exception;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function __construct(
        protected RewardService $rewardService
    ) {}

    /**
     * Get all 12 achievements for user with real progress & claimed statuses.
     */
    public function getAchievementsForUser(User $user): array
    {
        $claimedCodes = UserAchievement::where('user_id', $user->id)
            ->pluck('achievement_code')
            ->toArray();

        // User stats calculations
        $sightingCount = PlantSighting::where('ranger_id', $user->id)->count();
        $discoveryCount = PlantDiscovery::where('user_id', $user->id)->count();
        $totalFloraExplored = max($discoveryCount, $sightingCount);

        $harvestedCount = Planting::whereHas('gardenPlot', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'harvested')
            ->count();

        $totalPlantings = Planting::whereHas('gardenPlot', fn($q) => $q->where('user_id', $user->id))->count();

        $inventoryCount = InventoryItem::where('user_id', $user->id)->count();
        $rareSeedCount = InventoryItem::where('user_id', $user->id)->where('item_type', 'seed')->count();

        $friendCount = Friendship::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('friend_id', $user->id);
        })->where('status', 'accepted')->count();

        $itemRequestCount = ItemRequest::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
        })->count();

        $userLevel = $user->level ?? 1;

        $definitions = [
            [
                'code' => 'flora_explorer',
                'category' => 'exploration',
                'icon' => '🌱',
                'exp_reward' => 100,
                'coin_reward' => 50,
                'target' => 1,
                'current' => $totalFloraExplored,
            ],
            [
                'code' => 'region_mapper',
                'category' => 'exploration',
                'icon' => '🗺️',
                'exp_reward' => 150,
                'coin_reward' => 75,
                'target' => 3,
                'current' => $totalFloraExplored,
            ],
            [
                'code' => 'seedex_expert',
                'category' => 'exploration',
                'icon' => '📚',
                'exp_reward' => 250,
                'coin_reward' => 120,
                'target' => 5,
                'current' => $discoveryCount,
            ],
            [
                'code' => 'digital_farmer',
                'category' => 'garden',
                'icon' => '🌻',
                'exp_reward' => 150,
                'coin_reward' => 75,
                'target' => 1,
                'current' => $harvestedCount,
            ],
            [
                'code' => 'hydrator_master',
                'category' => 'garden',
                'icon' => '🚿',
                'exp_reward' => 120,
                'coin_reward' => 60,
                'target' => 5,
                'current' => $totalPlantings,
            ],
            [
                'code' => 'super_fertilizer',
                'category' => 'garden',
                'icon' => '🧪',
                'exp_reward' => 180,
                'coin_reward' => 90,
                'target' => 3,
                'current' => $inventoryCount,
            ],
            [
                'code' => 'loyal_shopper',
                'category' => 'shop',
                'icon' => '🛒',
                'exp_reward' => 200,
                'coin_reward' => 100,
                'target' => 1,
                'current' => $inventoryCount,
            ],
            [
                'code' => 'rare_seed_collector',
                'category' => 'shop',
                'icon' => '🌸',
                'exp_reward' => 220,
                'coin_reward' => 110,
                'target' => 1,
                'current' => $rareSeedCount,
            ],
            [
                'code' => 'alliance_guardian',
                'category' => 'social',
                'icon' => '🤝',
                'exp_reward' => 200,
                'coin_reward' => 100,
                'target' => 1,
                'current' => $friendCount,
            ],
            [
                'code' => 'alliance_courier',
                'category' => 'social',
                'icon' => '🎁',
                'exp_reward' => 150,
                'coin_reward' => 80,
                'target' => 1,
                'current' => $itemRequestCount,
            ],
            [
                'code' => 'ecosystem_master',
                'category' => 'social',
                'icon' => '👑',
                'exp_reward' => 500,
                'coin_reward' => 250,
                'target' => 5,
                'current' => $userLevel,
            ],
            [
                'code' => 'ancient_legend',
                'category' => 'social',
                'icon' => '🏆',
                'exp_reward' => 1000,
                'coin_reward' => 500,
                'target' => 10,
                'current' => $userLevel,
            ],
        ];

        return array_map(function ($def) use ($claimedCodes) {
            $isClaimed = in_array($def['code'], $claimedCodes);
            $isCompleted = $def['current'] >= $def['target'];
            $canClaim = $isCompleted && ! $isClaimed;

            return array_merge($def, [
                'is_claimed' => $isClaimed,
                'is_completed' => $isCompleted,
                'can_claim' => $canClaim,
            ]);
        }, $definitions);
    }

    /**
     * Claim reward for an achievement.
     */
    public function claimAchievement(User $user, string $code): array
    {
        $achievements = $this->getAchievementsForUser($user);
        $targetAch = null;

        foreach ($achievements as $ach) {
            if ($ach['code'] === $code) {
                $targetAch = $ach;
                break;
            }
        }

        if (! $targetAch) {
            throw new Exception('Achievement tidak ditemukan.');
        }

        if ($targetAch['is_claimed']) {
            throw new Exception('Hadiah achievement ini sudah pernah diklaim.');
        }

        if (! $targetAch['is_completed']) {
            throw new Exception('Misi achievement belum terselesaikan ('.$targetAch['current'].'/'.$targetAch['target'].').');
        }

        return DB::transaction(function () use ($user, $targetAch) {
            // Grant rewards
            $this->rewardService->grantExp($user, $targetAch['exp_reward'], 'claim_achievement_'.$targetAch['code']);
            $this->rewardService->addCoin($user, $targetAch['coin_reward'], 'claim_achievement_'.$targetAch['code']);

            // Save claim record in DB
            UserAchievement::create([
                'user_id' => $user->id,
                'achievement_code' => $targetAch['code'],
                'status' => 'claimed',
                'claimed_at' => now(),
            ]);

            return [
                'achievement_code' => $targetAch['code'],
                'exp_gained' => $targetAch['exp_reward'],
                'coin_gained' => $targetAch['coin_reward'],
                'user_exp' => $user->fresh()->exp,
                'user_coin' => $user->fresh()->coin,
            ];
        });
    }
}
