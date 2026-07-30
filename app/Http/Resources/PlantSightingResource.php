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
                return [
                    'id' => $this->plantSpecies->id,
                    'species_code' => $this->plantSpecies->species_code,
                    'common_name' => $this->plantSpecies->common_name,
                    'scientific_name' => $this->plantSpecies->scientific_name,
                    'description' => $this->plantSpecies->description,
                    'care_instructions' => $this->plantSpecies->care_instructions,
                    'conservation_status' => $this->plantSpecies->conservation_status,
                    'reference_image_path' => $this->plantSpecies->reference_image_path,
                ];
            }),
            'photo_url' => asset('storage/' . $this->photo_path),
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
}
