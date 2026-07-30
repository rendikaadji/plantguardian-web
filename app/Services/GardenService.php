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
     * Plant a seed on an unlocked garden plot.
     */
    public function plantSeed(
        User $user,
        int $gardenPlotId,
        string $seedCode,
        ?int $plantSpeciesId = null,
        int $growthDurationMinutes = 30
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

            // Deduct seed quantity
            $seedItem->decrement('quantity');

            $plantedAt = Carbon::now();
            $readyAt = (clone $plantedAt)->addMinutes($growthDurationMinutes);

            // Create planting record
            return Planting::create([
                'garden_plot_id' => $plot->id,
                'plant_species_id' => $plantSpeciesId,
                'planted_at' => $plantedAt,
                'ready_at' => $readyAt,
                'status' => 'growing',
            ]);
        });
    }

    /**
     * Water/care for a growing plant to reduce remaining growth time or update care timestamp.
     */
    public function waterPlant(User $user, int $plantingId, int $timeBonusMinutes = 5): Planting
    {
        return DB::transaction(function () use ($user, $plantingId, $timeBonusMinutes) {
            $planting = $this->getScopedPlanting($user, $plantingId);

            $this->syncPlantingStatus($planting);

            if ($planting->status !== 'growing') {
                throw new Exception('Hanya tanaman yang sedang tumbuh yang dapat disiram.');
            }

            // Update last watered and apply time bonus
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
    public function harvestPlant(User $user, int $plantingId, int $expReward = 50, int $coinReward = 20): array
    {
        return DB::transaction(function () use ($user, $plantingId, $expReward, $coinReward) {
            $planting = $this->getScopedPlanting($user, $plantingId);

            $this->syncPlantingStatus($planting);

            if ($planting->status !== 'ready') {
                throw new Exception('Tanaman belum siap dipanen.');
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
