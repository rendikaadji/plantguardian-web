<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlantSightingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ranger_id' => $this->ranger_id,
            'plant_species_id' => $this->plant_species_id,
            'species' => $this->whenLoaded('plantSpecies', function () {
                $refPath = $this->plantSpecies->reference_image_path;
                $refUrl = $refPath
                    ? (str_starts_with($refPath, 'http') ? $refPath : asset('storage/' . ltrim($refPath, '/')))
                    : null;

                return [
                    'id' => $this->plantSpecies->id,
                    'species_code' => $this->plantSpecies->species_code,
                    'common_name' => $this->plantSpecies->common_name,
                    'scientific_name' => $this->plantSpecies->scientific_name,
                    'description' => $this->plantSpecies->description,
                    'care_instructions' => $this->plantSpecies->care_instructions,
                    'conservation_status' => $this->plantSpecies->conservation_status,
                    'reference_image_path' => $refPath,
                    'reference_image_url' => $refUrl,
                ];
            }),
            'ranger_name' => $this->ranger?->name ?? ($this->relationLoaded('ranger') ? $this->ranger?->name : null),
            'ranger' => $this->whenLoaded('ranger', function () {
                return [
                    'id' => $this->ranger->id,
                    'name' => $this->ranger->name,
                ];
            }),
            'photo_url' => $this->resolvePhotoUrl(),
            'photo_path' => $this->photo_path,
            'confidence_score' => $this->confidence_score,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'saved_to_gallery' => $this->saved_to_gallery,
            'verification_status' => $this->verification_status,
            'sudah_ditemukan' => (bool) ($this->sudah_ditemukan ?? false),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function resolvePhotoUrl(): string
    {
        if ($this->photo_path) {
            if (str_starts_with($this->photo_path, 'http')) {
                return $this->photo_path;
            }
            return asset('storage/' . ltrim($this->photo_path, '/'));
        }

        if ($this->relationLoaded('plantSpecies') && $this->plantSpecies?->reference_image_path) {
            $refPath = $this->plantSpecies->reference_image_path;
            if (str_starts_with($refPath, 'http')) {
                return $refPath;
            }
            return asset('storage/' . ltrim($refPath, '/'));
        }

        return asset('images/logo-plantGuardian.jpeg');
    }
}
