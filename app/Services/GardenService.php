<?php

namespace App\Services;

use App\Models\GardenPlot;
use App\Models\InventoryItem;
use App\Models\Planting;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GardenService
{
    public function __construct(
        protected RewardService $rewardService
    ) {}

    /**
     * Unlock / Purchase a new garden plot for user.
     */
    public function unlockPlot(User $user, int $plotId, int $cost = 100): GardenPlot
    {
        return DB::transaction(function () use ($user, $plotId, $cost) {
            $plot = GardenPlot::where('user_id', $user->id)
                ->where('id', $plotId)
                ->firstOrFail();

            if ($plot->unlocked) {
                throw new Exception('Lahan tanam sudah terbuka.');
            }

            if ($user->coin < $cost) {
                throw new Exception('Coin tidak mencukupi untuk membuka lahan ini.');
            }

            // Deduct cost and record coin transaction
            $this->rewardService->deductCoin($user, $cost, 'buy_plot', $plot);

            $plot->update([
                'unlocked' => true,
                'purchase_cost' => $cost,
            ]);

            return $plot->fresh();
        });
    }

    /**
     * Get seed configuration (price, growth duration, EXP reward, NC coin reward).
     */
    public static function getSeedConfig(string $seedCode): array
    {
        $seeds = [
            'seed_sunflower' => [
                'name' => 'Benih Bunga Matahari',
                'price' => 50,
                'growth_duration_minutes' => 6,
                'exp_reward' => 50,
                'coin_reward' => 70,
                'icon' => '🌻',
                'description' => 'Benih bunga matahari hias berkualitas tinggi.',
            ],
            'seed_tomato' => [
                'name' => 'Benih Tomat Organik',
                'price' => 75,
                'growth_duration_minutes' => 12,
                'exp_reward' => 90,
                'coin_reward' => 110,
                'icon' => '🍅',
                'description' => 'Benih buah tomat cepat tumbuh dan manis.',
            ],
            'seed_monstera' => [
                'name' => 'Benih Monstera Deliciosa',
                'price' => 120,
                'growth_duration_minutes' => 21,
                'exp_reward' => 160,
                'coin_reward' => 180,
                'icon' => '🌿',
                'description' => 'Benih tanaman hias indoor eksotis favorit.',
            ],
            'seed_orchid' => [
                'name' => 'Benih Anggrek Hitam',
                'price' => 200,
                'growth_duration_minutes' => 36,
                'exp_reward' => 300,
                'coin_reward' => 310,
                'icon' => '🪻',
                'description' => 'Benih anggrek langka bernilai tinggi.',
            ],
        ];

        return $seeds[$seedCode] ?? [
            'name' => 'Benih Spesies',
            'price' => 50,
            'growth_duration_minutes' => 6,
            'exp_reward' => 50,
            'coin_reward' => 70,
            'icon' => '🌱',
            'description' => 'Benih standar.',
        ];
    }

    /**
     * Plant a seed on an unlocked garden plot.
     */
    public function plantSeed(
        User $user,
        int $gardenPlotId,
        string $seedCode,
        ?int $plantSpeciesId = null,
        ?int $growthDurationMinutes = null
    ): Planting {
        return DB::transaction(function () use ($user, $gardenPlotId, $seedCode, $plantSpeciesId, $growthDurationMinutes) {
            // Verify garden plot belongs to user and is unlocked
            $plot = GardenPlot::where('user_id', $user->id)
                ->where('id', $gardenPlotId)
                ->firstOrFail();

            if (! $plot->unlocked) {
                throw new Exception('Lahan tanam belum terbuka.');
            }

            // Check if plot currently has an active planting (growing or ready)
            $activePlanting = Planting::where('garden_plot_id', $plot->id)
                ->whereIn('status', ['growing', 'ready'])
                ->first();

            if ($activePlanting) {
                throw new Exception('Lahan tanam sedang digunakan.');
            }

            // Check inventory item for seed
            $seedItem = InventoryItem::where('user_id', $user->id)
                ->where('item_type', 'seed')
                ->where('item_code', $seedCode)
                ->where('quantity', '>', 0)
                ->first();

            if (! $seedItem) {
                throw new Exception('Benih tidak ditemukan di inventaris Anda atau stok habis.');
            }

            // Determine growth duration
            if ($growthDurationMinutes === null) {
                $seedConfig = self::getSeedConfig($seedCode);
                $growthDurationMinutes = $seedConfig['growth_duration_minutes'];
            }

            // Deduct seed quantity
            $seedItem->decrement('quantity');

            $plantedAt = Carbon::now();
            $readyAt = (clone $plantedAt)->addMinutes($growthDurationMinutes);

            // Create planting record
            return Planting::create([
                'garden_plot_id' => $plot->id,
                'plant_species_id' => $plantSpeciesId,
                'seed_code' => $seedCode,
                'planted_at' => $plantedAt,
                'ready_at' => $readyAt,
                'status' => 'growing',
            ]);
        });
    }

    /**
     * Water/care for a growing plant to reduce remaining growth time using Penyiram Otomatis (Consumable item).
     */
    public function waterPlant(User $user, int $plantingId, int $timeBonusMinutes = 10): Planting
    {
        return DB::transaction(function () use ($user, $plantingId, $timeBonusMinutes) {
            $planting = $this->getScopedPlanting($user, $plantingId);

            $this->syncPlantingStatus($planting);

            if ($planting->status !== 'growing') {
                throw new Exception('Hanya tanaman yang sedang tumbuh yang dapat disiram.');
            }

            // Check and deduct Penyiram Otomatis (tool_watering_can) from inventory
            $wateringCanItem = InventoryItem::where('user_id', $user->id)
                ->where('item_code', 'tool_watering_can')
                ->where('quantity', '>', 0)
                ->first();

            if (! $wateringCanItem) {
                throw new Exception('Stok Penyiram Otomatis Anda habis. Silakan beli di Shop.');
            }

            // Deduct 1 watering can item
            $wateringCanItem->decrement('quantity');

            // Update last watered and apply 10-minute time bonus
            $newReadyAt = Carbon::parse($planting->ready_at)->subMinutes($timeBonusMinutes);
            if ($newReadyAt->isPast()) {
                $newReadyAt = Carbon::now();
            }

            $planting->update([
                'last_watered_at' => Carbon::now(),
                'ready_at' => $newReadyAt,
            ]);

            return $this->syncPlantingStatus($planting);
        });
    }

    /**
     * Apply Pupuk Organik Super (tool_fertilizer) to speed up plant growth (5 mins).
     */
    public function applyFertilizer(User $user, int $plantingId): Planting
    {
        return DB::transaction(function () use ($user, $plantingId) {
            $planting = $this->getScopedPlanting($user, $plantingId);

            $this->syncPlantingStatus($planting);

            if ($planting->status !== 'growing') {
                throw new Exception('Hanya tanaman yang sedang tumbuh yang dapat dipupuk.');
            }

            $fertilizerItem = InventoryItem::where('user_id', $user->id)
                ->where('item_code', 'tool_fertilizer')
                ->where('quantity', '>', 0)
                ->first();

            if (! $fertilizerItem) {
                throw new Exception('Anda tidak memiliki Pupuk Organik Super di inventaris.');
            }

            $fertilizerItem->decrement('quantity');

            // Apply 5 minutes speedup
            $newReadyAt = Carbon::parse($planting->ready_at)->subMinutes(5);
            if ($newReadyAt->isPast()) {
                $newReadyAt = Carbon::now();
            }

            $planting->update([
                'ready_at' => $newReadyAt,
            ]);

            return $this->syncPlantingStatus($planting);
        });
    }

    /**
     * Check and sync status of a planting (growing -> ready if time arrived).
     */
    public function syncPlantingStatus(Planting $planting): Planting
    {
        if ($planting->status === 'growing' && Carbon::now()->gte($planting->ready_at)) {
            $planting->update(['status' => 'ready']);
        }

        return $planting;
    }

    /**
     * Harvest a ready plant and grant EXP/Coin rewards.
     */
    public function harvestPlant(User $user, int $plantingId, ?int $expReward = null, ?int $coinReward = null): array
    {
        return DB::transaction(function () use ($user, $plantingId, $expReward, $coinReward) {
            $planting = $this->getScopedPlanting($user, $plantingId);

            $this->syncPlantingStatus($planting);

            if ($planting->status !== 'ready') {
                throw new Exception('Tanaman belum siap dipanen.');
            }

            if ($expReward === null || $coinReward === null) {
                $seedConfig = self::getSeedConfig($planting->seed_code ?? '');
                $expReward = $expReward ?? $seedConfig['exp_reward'];
                $coinReward = $coinReward ?? $seedConfig['coin_reward'];
            }

            $planting->update([
                'status' => 'harvested',
                'harvested_at' => Carbon::now(),
            ]);

            // Grant rewards via RewardService
            $this->rewardService->grantHarvestReward($user, $planting, $expReward, $coinReward);

            return [
                'planting' => $planting->fresh(),
                'exp_reward' => $expReward,
                'coin_reward' => $coinReward,
            ];
        });
    }

    /**
     * Helper to retrieve a planting scoped to the authenticated user.
     */
    protected function getScopedPlanting(User $user, int $plantingId): Planting
    {
        return Planting::whereHas('gardenPlot', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('id', $plantingId)->firstOrFail();
    }
}
