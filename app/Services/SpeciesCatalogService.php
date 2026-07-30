<?php

namespace App\Services;

use App\Models\PlantSpecies;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class SpeciesCatalogService
{
    /**
     * Get all plant species catalog items.
     * EXCEPTION TO SCOPING (rules.md §1.5): plant_species is shared catalog data
     * read across all roles, not personal user data - read query is NOT scoped by created_by.
     */
    public function getAllSpecies(): Collection
    {
        return PlantSpecies::with('creator')->latest()->get();
    }

    /**
     * Get single plant species by ID.
     */
    public function getSpeciesById(int $id): PlantSpecies
    {
        return PlantSpecies::with('creator')->findOrFail($id);
    }

    /**
     * Create a new plant species catalog entry.
     */
    public function createSpecies(User $ranger, array $data): PlantSpecies
    {
        if (isset($data['reference_image']) && is_object($data['reference_image'])) {
            $data['reference_image_path'] = $data['reference_image']->store('species_references', 'public');
            unset($data['reference_image']);
        }

        $data['created_by'] = $ranger->id;

        return PlantSpecies::create($data);
    }

    /**
     * Update an existing plant species catalog entry.
     */
    public function updateSpecies(int $id, array $data): PlantSpecies
    {
        $species = PlantSpecies::findOrFail($id);

        if (isset($data['reference_image']) && is_object($data['reference_image'])) {
            if ($species->reference_image_path) {
                Storage::disk('public')->delete($species->reference_image_path);
            }
            $data['reference_image_path'] = $data['reference_image']->store('species_references', 'public');
            unset($data['reference_image']);
        }

        $species->update($data);

        return $species->fresh(['creator']);
    }

    /**
     * Delete a plant species catalog entry.
     */
    public function deleteSpecies(int $id): bool
    {
        $species = PlantSpecies::findOrFail($id);

        if ($species->reference_image_path) {
            Storage::disk('public')->delete($species->reference_image_path);
        }

        return $species->delete();
    }
}
