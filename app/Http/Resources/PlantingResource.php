<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlantingResource extends JsonResource
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
            'garden_plot_id' => $this->garden_plot_id,
            'plant_species_id' => $this->plant_species_id,
            'species' => $this->whenLoaded('plantSpecies', function () {
                return [
                    'id' => $this->plantSpecies->id,
                    'species_code' => $this->plantSpecies->species_code,
                    'common_name' => $this->plantSpecies->common_name,
                    'scientific_name' => $this->plantSpecies->scientific_name,
                ];
            }),
            'planted_at' => $this->planted_at?->toIso8601String(),
            'ready_at' => $this->ready_at?->toIso8601String(),
            'last_watered_at' => $this->last_watered_at?->toIso8601String(),
            'status' => $this->status,
            'is_ready' => $this->status === 'ready' || ($this->status === 'growing' && now()->gte($this->ready_at)),
            'harvested_at' => $this->harvested_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
