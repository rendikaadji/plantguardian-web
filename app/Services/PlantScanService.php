<?php

namespace App\Services;

use App\Models\PlantSighting;
use App\Models\PlantSpecies;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PlantScanService
{
    /**
     * Handle plant scan request by Ranger: sends image to Python AI service, matches species, and saves pending sighting.
     */
    public function scan(User $user, array $validatedData): PlantSighting
    {
        $photoPath = 'sightings/default.jpg';
        $imageBase64 = null;

        // Process uploaded image file or base64 string
        if (isset($validatedData['image']) && is_object($validatedData['image'])) {
            $file = $validatedData['image'];
            $photoPath = $file->store('sightings', 'public');
            $imageBase64 = base64_encode(file_get_contents($file->getRealPath()));
        } elseif (! empty($validatedData['image_base64'])) {
            $imageBase64 = $validatedData['image_base64'];
        }

        $aiUrl = config('services.ai_service.url', env('AI_SERVICE_URL', 'http://127.0.0.1:8000'));
        $timeout = (int) config('services.ai_service.timeout', env('AI_SERVICE_TIMEOUT', 10));

        $matchedSpeciesId = null;
        $confidenceScore = null;

        // Call Python AI microservice POST /classify with timeout & fallback
        if ($imageBase64) {
            try {
                $response = Http::timeout($timeout)->post("{$aiUrl}/classify", [
                    'image_base64' => $imageBase64,
                    'request_id' => (string) Str::uuid(),
                ]);

                if ($response->successful()) {
                    $aiResult = $response->json();
                    if (! empty($aiResult['success']) && ! empty($aiResult['predicted_species_code'])) {
                        $confidenceScore = $aiResult['confidence'] ?? null;
                        $species = PlantSpecies::where('species_code', $aiResult['predicted_species_code'])->first();
                        if ($species) {
                            $matchedSpeciesId = $species->id;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal menghubungi AI service klasifikasi: ' . $e->getMessage());
            }
        }

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
                    'care_instructions' => $validatedData['care_instructions'] ?? 'Penyiraman teratur dan pemupukan kompos berkala.',
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

        // Fallback: If species specified directly in data
        if (! $matchedSpeciesId && ! empty($validatedData['plant_species_id'])) {
            $matchedSpeciesId = $validatedData['plant_species_id'];
        }

        // Save sighting created by Ranger with status 'verified' (directly active for Viewers on the map)
        $sighting = PlantSighting::create([
            'ranger_id' => $user->id,
            'plant_species_id' => $matchedSpeciesId,
            'photo_path' => $photoPath,
            'confidence_score' => $confidenceScore,
            'latitude' => $validatedData['latitude'] ?? null,
            'longitude' => $validatedData['longitude'] ?? null,
            'saved_to_gallery' => true,
            'verification_status' => 'verified',
        ]);

        return $sighting->load('plantSpecies');
    }
}
