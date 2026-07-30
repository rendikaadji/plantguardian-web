<?php

namespace App\Services;

use App\Models\PlantSighting;
use App\Models\RealPlanting;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VerificationService
{
    public function __construct(
        protected RewardService $rewardService
    ) {}

    /**
     * Get pending verification items (plant_sightings pending & real_plantings self_reported).
     * Shared queue for all Rangers (architecture.md §4.7).
     */
    public function getPendingQueue(): array
    {
        $pendingSightings = PlantSighting::with(['ranger:id,name,email', 'plantSpecies'])
            ->where('verification_status', 'pending')
            ->latest()
            ->get();

        $pendingPlantings = RealPlanting::with(['user:id,name,email', 'plantSpecies', 'compostProcess'])
            ->where('verification_status', 'self_reported')
            ->latest()
            ->get();

        return [
            'pending_sightings' => $pendingSightings,
            'pending_real_plantings' => $pendingPlantings,
        ];
    }

    /**
     * Verify or reject a plant sighting scan item.
     */
    public function verifySighting(User $ranger, int $sightingId, string $decision): PlantSighting
    {
        if (! in_array($decision, ['verified', 'rejected'])) {
            throw new Exception('Keputusan verifikasi tidak valid.');
        }

        return DB::transaction(function () use ($ranger, $sightingId, $decision) {
            $sighting = PlantSighting::findOrFail($sightingId);

            $sighting->update([
                'verification_status' => $decision,
                'verified_by' => $ranger->id,
                'verified_at' => Carbon::now(),
            ]);

            // If verified, grant pending scan reward (EXP/Coin) to the Ranger who scanned it
            if ($decision === 'verified' && $sighting->ranger_id) {
                $rangerOwner = User::find($sighting->ranger_id);
                if ($rangerOwner) {
                    $this->rewardService->grantScanReward($rangerOwner, $sighting);
                }
            }

            return $sighting->fresh(['ranger', 'plantSpecies', 'verifier']);
        });
    }

    /**
     * Verify or reject a real tree planting proof item.
     */
    public function verifyRealPlanting(User $ranger, int $realPlantingId, string $decision): RealPlanting
    {
        if (! in_array($decision, ['verified', 'rejected'])) {
            throw new Exception('Keputusan verifikasi tidak valid.');
        }

        return DB::transaction(function () use ($ranger, $realPlantingId, $decision) {
            $planting = RealPlanting::findOrFail($realPlantingId);

            $planting->update([
                'verification_status' => $decision,
                'verified_by' => $ranger->id,
                'verified_at' => Carbon::now(),
            ]);

            return $planting->fresh(['user', 'plantSpecies', 'verifier']);
        });
    }
}
