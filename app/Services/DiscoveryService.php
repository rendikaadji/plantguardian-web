<?php

namespace App\Services;

use App\Models\PlantDiscovery;
use App\Models\PlantSighting;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DiscoveryService
{
    public function __construct(
        protected RewardService $rewardService
    ) {}

    /**
     * Handle viewer discovery ("catch") of a verified plant sighting.
     */
    public function discover(User $user, array $validatedData): PlantDiscovery
    {
        $sighting = PlantSighting::findOrFail($validatedData['plant_sighting_id']);

        if ($sighting->verification_status !== 'verified') {
            throw new Exception('Hanya temuan tumbuhan yang sudah diverifikasi yang dapat ditemukan.');
        }

        $alreadyDiscovered = PlantDiscovery::where('user_id', $user->id)
            ->where('plant_sighting_id', $sighting->id)
            ->exists();

        if ($alreadyDiscovered) {
            throw new Exception('Anda sudah pernah menemukan tumbuhan ini.');
        }

        return DB::transaction(function () use ($user, $sighting, $validatedData) {
            $discovery = PlantDiscovery::create([
                'user_id' => $user->id,
                'plant_sighting_id' => $sighting->id,
                'discovered_at' => Carbon::now(),
                'latitude' => $validatedData['latitude'] ?? null,
                'longitude' => $validatedData['longitude'] ?? null,
            ]);

            // Reward viewer for discovering the plant sighting
            $this->rewardService->grantScanReward($user, $discovery);

            return $discovery->load(['plantSighting.plantSpecies', 'user']);
        });
    }
}
