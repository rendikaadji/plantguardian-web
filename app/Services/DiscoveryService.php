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

        if (isset($validatedData['latitude'], $validatedData['longitude']) && $sighting->latitude !== null && $sighting->longitude !== null) {
            $distanceMeters = $this->calculateDistanceInMeters(
                (float) $validatedData['latitude'],
                (float) $validatedData['longitude'],
                (float) $sighting->latitude,
                (float) $sighting->longitude
            );

            if ($distanceMeters > 50) {
                throw new Exception('Anda berada di luar jangkauan (lebih dari 50 meter) dari lokasi tumbuhan untuk mengklaim temuan ini.');
            }
        }

        return DB::transaction(function () use ($user, $sighting, $validatedData) {
            $discovery = PlantDiscovery::create([
                'user_id' => $user->id,
                'plant_sighting_id' => $sighting->id,
                'discovered_at' => Carbon::now(),
                'latitude' => $validatedData['latitude'] ?? null,
                'longitude' => $validatedData['longitude'] ?? null,
            ]);

            // Reward viewer for discovering the plant sighting (scaled by plant rarity)
            $status = $sighting->plantSpecies ? $sighting->plantSpecies->conservation_status : 'Common';
            $reward = $this->rewardService->grantScanReward($user, $discovery, $status);

            $discovery->reward_summary = $reward;

            return $discovery->load(['plantSighting.plantSpecies', 'user']);
        });
    }

    /**
     * Calculate Haversine distance in meters between two lat/lng coordinates.
     */
    protected function calculateDistanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
