<?php

namespace App\Http\Resources\Ranger;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SpeciesResource extends JsonResource
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
            'species_code' => $this->species_code,
            'common_name' => $this->common_name,
            'scientific_name' => $this->scientific_name,
            'description' => $this->description,
            'care_instructions' => $this->care_instructions,
            'conservation_status' => $this->conservation_status,
            'reference_image_url' => $this->reference_image_path ? asset('storage/' . $this->reference_image_path) : null,
            'reference_image_path' => $this->reference_image_path,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
