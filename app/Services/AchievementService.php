<?php

namespace App\Services;

use App\Models\Friendship;
use App\Models\GardenPlot;
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
     * Get all 24 achievements for user with real progress & claimed statuses.
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

        $unlockedPlotsCount = GardenPlot::where('user_id', $user->id)->where('unlocked', true)->count();

        $inventoryTotalCount = (int) InventoryItem::where('user_id', $user->id)->sum('quantity');
        $rareSeedCount = (int) InventoryItem::where('user_id', $user->id)->where('item_type', 'seed')->sum('quantity');

        $friendCount = Friendship::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhere('friend_id', $user->id);
        })->where('status', 'accepted')->count();

        $itemRequestCount = ItemRequest::where(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
        })->count();

        $userLevel = $user->level ?? 1;
        $isRanger = in_array($user->role, ['ranger', 'admin']);

        if ($isRanger) {
            // Ranger stats calculations
            $rangerSightingCount = PlantSighting::where('ranger_id', $user->id)->count();
            $rangerCareCount = DB::table('plant_sightings')
                ->join('plant_species', 'plant_sightings.plant_species_id', '=', 'plant_species.id')
                ->where('plant_sightings.ranger_id', $user->id)
                ->whereNotNull('plant_species.care_instructions')
                ->where('plant_species.care_instructions', '!=', '')
                ->count();

            $protectedSpeciesCount = DB::table('plant_sightings')
                ->join('plant_species', 'plant_sightings.plant_species_id', '=', 'plant_species.id')
                ->where('plant_sightings.ranger_id', $user->id)
                ->whereIn('plant_species.conservation_status', ['Vulnerable', 'Endangered', 'Protected', 'Rentan', 'Terancam', 'Dilindungi'])
                ->count();

            $arScanCount = PlantSighting::where('ranger_id', $user->id)->whereNotNull('photo_path')->count();

            $definitions = [
                // Category 1: Exploration / Pemetaan Lapangan
                ['code' => 'ranger_first_scan', 'category' => 'exploration', 'icon' => '📷', 'exp_reward' => 200, 'coin_reward' => 100, 'target' => 1, 'current' => max($rangerSightingCount, $arScanCount)],
                ['code' => 'ranger_field_mapper', 'category' => 'exploration', 'icon' => '🗺️', 'exp_reward' => 400, 'coin_reward' => 200, 'target' => 5, 'current' => $rangerSightingCount],
                ['code' => 'ranger_master_mapper', 'category' => 'exploration', 'icon' => '📡', 'exp_reward' => 800, 'coin_reward' => 400, 'target' => 15, 'current' => $rangerSightingCount],
                ['code' => 'ranger_ar_specialist', 'category' => 'exploration', 'icon' => '👁️', 'exp_reward' => 500, 'coin_reward' => 250, 'target' => 10, 'current' => max($arScanCount, $rangerSightingCount)],
                ['code' => 'ranger_survey_veteran', 'category' => 'exploration', 'icon' => '🧭', 'exp_reward' => 1200, 'coin_reward' => 600, 'target' => 25, 'current' => $rangerSightingCount],
                ['code' => 'ranger_legendary_mapper', 'category' => 'exploration', 'icon' => '🌐', 'exp_reward' => 2500, 'coin_reward' => 1250, 'target' => 50, 'current' => $rangerSightingCount],

                // Category 2: Garden / Perawatan & Edukasi Flora
                ['code' => 'ranger_care_guide', 'category' => 'garden', 'icon' => '🌱', 'exp_reward' => 300, 'coin_reward' => 150, 'target' => 3, 'current' => max($rangerCareCount, min($rangerSightingCount, 3))],
                ['code' => 'ranger_botanical_educator', 'category' => 'garden', 'icon' => '📖', 'exp_reward' => 600, 'coin_reward' => 300, 'target' => 10, 'current' => max($rangerCareCount, min($rangerSightingCount, 10))],
                ['code' => 'ranger_conservation_guardian', 'category' => 'garden', 'icon' => '🛡️', 'exp_reward' => 750, 'coin_reward' => 375, 'target' => 5, 'current' => max($protectedSpeciesCount, min($rangerSightingCount, 5))],
                ['code' => 'ranger_herbarium_curator', 'category' => 'garden', 'icon' => '🔬', 'exp_reward' => 1000, 'coin_reward' => 500, 'target' => 15, 'current' => $rangerSightingCount],
                ['code' => 'ranger_species_sentinel', 'category' => 'garden', 'icon' => '🌳', 'exp_reward' => 1500, 'coin_reward' => 750, 'target' => 25, 'current' => $rangerSightingCount],
                ['code' => 'ranger_master_taxonomist', 'category' => 'garden', 'icon' => '🎓', 'exp_reward' => 3000, 'coin_reward' => 1500, 'target' => 40, 'current' => $rangerSightingCount],

                // Category 3: Shop / Logistik & Peralatan Lapangan
                ['code' => 'ranger_logistics_master', 'category' => 'shop', 'icon' => '📦', 'exp_reward' => 350, 'coin_reward' => 175, 'target' => 10, 'current' => $inventoryTotalCount],
                ['code' => 'ranger_seed_collector', 'category' => 'shop', 'icon' => '🌸', 'exp_reward' => 500, 'coin_reward' => 250, 'target' => 5, 'current' => $rareSeedCount],
                ['code' => 'ranger_equipment_expert', 'category' => 'shop', 'icon' => '🛠️', 'exp_reward' => 750, 'coin_reward' => 375, 'target' => 25, 'current' => $inventoryTotalCount],
                ['code' => 'ranger_supplies_master', 'category' => 'shop', 'icon' => '🏬', 'exp_reward' => 1500, 'coin_reward' => 750, 'target' => 50, 'current' => $inventoryTotalCount],
                ['code' => 'ranger_seed_vault_keeper', 'category' => 'shop', 'icon' => '💎', 'exp_reward' => 1200, 'coin_reward' => 600, 'target' => 15, 'current' => $rareSeedCount],
                ['code' => 'ranger_field_quartermaster', 'category' => 'shop', 'icon' => '🚜', 'exp_reward' => 3000, 'coin_reward' => 1500, 'target' => 100, 'current' => $inventoryTotalCount],

                // Category 4: Social / Prestise & Aliansi Ranger
                ['code' => 'ranger_alliance_leader', 'category' => 'social', 'icon' => '🤝', 'exp_reward' => 350, 'coin_reward' => 175, 'target' => 3, 'current' => $friendCount],
                ['code' => 'ranger_field_dispatcher', 'category' => 'social', 'icon' => '🎁', 'exp_reward' => 450, 'coin_reward' => 225, 'target' => 5, 'current' => $itemRequestCount],
                ['code' => 'ranger_patrol_officer', 'category' => 'social', 'icon' => '🎖️', 'exp_reward' => 600, 'coin_reward' => 300, 'target' => 5, 'current' => $userLevel],
                ['code' => 'ranger_senior_officer', 'category' => 'social', 'icon' => '🏅', 'exp_reward' => 1200, 'coin_reward' => 600, 'target' => 10, 'current' => $userLevel],
                ['code' => 'ranger_master_synergy', 'category' => 'social', 'icon' => '💖', 'exp_reward' => 1000, 'coin_reward' => 500, 'target' => 10, 'current' => $friendCount],
                ['code' => 'ranger_supreme_sentinel', 'category' => 'social', 'icon' => '👑', 'exp_reward' => 5000, 'coin_reward' => 2500, 'target' => 25, 'current' => $userLevel],
            ];
        } else {
            // Viewer definitions
            $definitions = [
                // Category 1: Exploration
                [
                    'code' => 'flora_explorer',
                    'category' => 'exploration',
                    'icon' => '🌱',
                    'exp_reward' => 150,
                    'coin_reward' => 75,
                    'target' => 5,
                    'current' => $totalFloraExplored,
                ],
                [
                    'code' => 'region_mapper',
                    'category' => 'exploration',
                    'icon' => '🗺️',
                    'exp_reward' => 300,
                    'coin_reward' => 150,
                    'target' => 15,
                    'current' => $totalFloraExplored,
                ],
                [
                    'code' => 'seedex_expert',
                    'category' => 'exploration',
                    'icon' => '📚',
                    'exp_reward' => 500,
                    'coin_reward' => 250,
                    'target' => 10,
                    'current' => $discoveryCount,
                ],
                [
                    'code' => 'master_botanist',
                    'category' => 'exploration',
                    'icon' => '🔬',
                    'exp_reward' => 1000,
                    'coin_reward' => 500,
                    'target' => 25,
                    'current' => $discoveryCount,
                ],
                [
                    'code' => 'ranger_pioneer',
                    'category' => 'exploration',
                    'icon' => '📡',
                    'exp_reward' => 400,
                    'coin_reward' => 200,
                    'target' => 10,
                    'current' => $totalFloraExplored,
                ],
                [
                    'code' => 'legendary_explorer',
                    'category' => 'exploration',
                    'icon' => '🧭',
                    'exp_reward' => 2000,
                    'coin_reward' => 1000,
                    'target' => 50,
                    'current' => $totalFloraExplored,
                ],

                // Category 2: Garden
                [
                    'code' => 'digital_farmer',
                    'category' => 'garden',
                    'icon' => '🌻',
                    'exp_reward' => 200,
                    'coin_reward' => 100,
                    'target' => 10,
                    'current' => $harvestedCount,
                ],
                [
                    'code' => 'harvest_master',
                    'category' => 'garden',
                    'icon' => '🌾',
                    'exp_reward' => 600,
                    'coin_reward' => 300,
                    'target' => 30,
                    'current' => $harvestedCount,
                ],
                [
                    'code' => 'hydrator_master',
                    'category' => 'garden',
                    'icon' => '🚿',
                    'exp_reward' => 300,
                    'coin_reward' => 150,
                    'target' => 25,
                    'current' => $totalPlantings,
                ],
                [
                    'code' => 'super_fertilizer',
                    'category' => 'garden',
                    'icon' => '🧪',
                    'exp_reward' => 400,
                    'coin_reward' => 200,
                    'target' => 10,
                    'current' => $inventoryTotalCount,
                ],
                [
                    'code' => 'green_thumb',
                    'category' => 'garden',
                    'icon' => '🏡',
                    'exp_reward' => 800,
                    'coin_reward' => 400,
                    'target' => 4,
                    'current' => $unlockedPlotsCount,
                ],
                [
                    'code' => 'agrarian_legend',
                    'category' => 'garden',
                    'icon' => '🚜',
                    'exp_reward' => 2500,
                    'coin_reward' => 1250,
                    'target' => 75,
                    'current' => $harvestedCount,
                ],

                // Category 3: Shop & Items
                [
                    'code' => 'loyal_shopper',
                    'category' => 'shop',
                    'icon' => '🛒',
                    'exp_reward' => 350,
                    'coin_reward' => 175,
                    'target' => 15,
                    'current' => $inventoryTotalCount,
                ],
                [
                    'code' => 'rare_seed_collector',
                    'category' => 'shop',
                    'icon' => '🌸',
                    'exp_reward' => 500,
                    'coin_reward' => 250,
                    'target' => 5,
                    'current' => $rareSeedCount,
                ],
                [
                    'code' => 'botanical_investor',
                    'category' => 'shop',
                    'icon' => '💰',
                    'exp_reward' => 750,
                    'coin_reward' => 375,
                    'target' => 30,
                    'current' => $inventoryTotalCount,
                ],
                [
                    'code' => 'shop_tycoon',
                    'category' => 'shop',
                    'icon' => '🏬',
                    'exp_reward' => 1500,
                    'coin_reward' => 750,
                    'target' => 50,
                    'current' => $inventoryTotalCount,
                ],
                [
                    'code' => 'seed_hoarder',
                    'category' => 'shop',
                    'icon' => '💎',
                    'exp_reward' => 1200,
                    'coin_reward' => 600,
                    'target' => 15,
                    'current' => $rareSeedCount,
                ],
                [
                    'code' => 'equipment_master',
                    'category' => 'shop',
                    'icon' => '🛠️',
                    'exp_reward' => 3000,
                    'coin_reward' => 1500,
                    'target' => 100,
                    'current' => $inventoryTotalCount,
                ],

                // Category 4: Social & Guardian Titles
                [
                    'code' => 'alliance_guardian',
                    'category' => 'social',
                    'icon' => '🤝',
                    'exp_reward' => 350,
                    'coin_reward' => 175,
                    'target' => 3,
                    'current' => $friendCount,
                ],
                [
                    'code' => 'alliance_veteran',
                    'category' => 'social',
                    'icon' => '🛡️',
                    'exp_reward' => 800,
                    'coin_reward' => 400,
                    'target' => 10,
                    'current' => $friendCount,
                ],
                [
                    'code' => 'alliance_courier',
                    'category' => 'social',
                    'icon' => '🎁',
                    'exp_reward' => 450,
                    'coin_reward' => 225,
                    'target' => 5,
                    'current' => $itemRequestCount,
                ],
                [
                    'code' => 'social_philanthropist',
                    'category' => 'social',
                    'icon' => '💖',
                    'exp_reward' => 1000,
                    'coin_reward' => 500,
                    'target' => 15,
                    'current' => $itemRequestCount,
                ],
                [
                    'code' => 'ecosystem_master',
                    'category' => 'social',
                    'icon' => '👑',
                    'exp_reward' => 1200,
                    'coin_reward' => 600,
                    'target' => 10,
                    'current' => $userLevel,
                ],
                [
                    'code' => 'ancient_legend',
                    'category' => 'social',
                    'icon' => '🏆',
                    'exp_reward' => 5000,
                    'coin_reward' => 2500,
                    'target' => 25,
                    'current' => $userLevel,
                ],
            ];
        }

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
