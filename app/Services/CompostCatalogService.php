<?php

namespace App\Services;

use App\Models\CompostMaterial;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CompostCatalogService
{
    /**
     * Get all compost materials catalog items.
     * Shared catalog data across roles (architecture.md §4.7).
     */
    public function getAllMaterials(): Collection
    {
        return CompostMaterial::with('creator')->latest()->get();
    }

    public function getMaterialById(int $id): CompostMaterial
    {
        return CompostMaterial::with('creator')->findOrFail($id);
    }

    public function createMaterial(User $ranger, array $data): CompostMaterial
    {
        $data['created_by'] = $ranger->id;
        return CompostMaterial::create($data);
    }

    public function updateMaterial(int $id, array $data): CompostMaterial
    {
        $material = CompostMaterial::findOrFail($id);
        $material->update($data);
        return $material->fresh(['creator']);
    }

    public function deleteMaterial(int $id): bool
    {
        $material = CompostMaterial::findOrFail($id);
        return $material->delete();
    }
}
