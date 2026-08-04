<?php

namespace App\Services;

use App\Models\PlantSighting;
use App\Models\PlantSpecies;
use App\Models\User;
use Illuminate\Support\Str;

class PlantScanService
{
    /**
     * Handle plant scan request by Ranger: matches species locally and saves verified sighting.
     */
    public function scan(User $user, array $validatedData): PlantSighting
    {
        $photoPath = 'sightings/default.jpg';

        // Process uploaded image file
        if (isset($validatedData['image']) && is_object($validatedData['image'])) {
            $file = $validatedData['image'];
            $photoPath = $file->store('sightings', 'public');
        }

        $matchedSpeciesId = null;

        // On-the-spot plant data entry by Ranger
        if (! empty($validatedData['common_name'])) {
            $code = strtoupper(Str::slug($validatedData['common_name'], '_'));
            $species = PlantSpecies::firstOrCreate(
                ['species_code' => $code],
                [
                    'common_name' => $validatedData['common_name'],
                    'scientific_name' => $validatedData['scientific_name'] ?? $validatedData['common_name'],
                    'conservation_status' => $validatedData['conservation_status'] ?? 'Common',
                    'description' => $validatedData['description'] ?? 'Tumbuhan hasil pemindaian lapangan oleh Ranger.',
                    'care_instructions' => $validatedData['care_instructions'] ?? 'Penyiraman teratur dan perawatan berkala.',
                    'created_by' => $user->id,
                ]
            );

            // Update existing species attributes if provided
            $species->update(array_filter([
                'common_name' => $validatedData['common_name'] ?? null,
                'scientific_name' => $validatedData['scientific_name'] ?? null,
                'conservation_status' => $validatedData['conservation_status'] ?? null,
                'description' => $validatedData['description'] ?? null,
                'care_instructions' => $validatedData['care_instructions'] ?? null,
            ]));

            $matchedSpeciesId = $species->id;
        }

        // Fallback: If species specified directly in data or existing catalog
        if (! $matchedSpeciesId && ! empty($validatedData['plant_species_id'])) {
            $matchedSpeciesId = $validatedData['plant_species_id'];
        }

        if (! $matchedSpeciesId) {
            $matchedSpeciesId = PlantSpecies::first()?->id;
        }

        // Save sighting created by Ranger with status 'verified'
        $sighting = PlantSighting::create([
            'ranger_id' => $user->id,
            'plant_species_id' => $matchedSpeciesId,
            'photo_path' => $photoPath,
            'confidence_score' => 1.0,
            'latitude' => $validatedData['latitude'] ?? null,
            'longitude' => $validatedData['longitude'] ?? null,
            'saved_to_gallery' => true,
            'verification_status' => 'verified',
        ]);

        return $sighting->load('plantSpecies');
    }
}
