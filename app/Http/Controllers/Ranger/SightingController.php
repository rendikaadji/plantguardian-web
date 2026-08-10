<?php

namespace App\Http\Controllers\Ranger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ranger\SightingUpdateRequest;
use App\Http\Resources\PlantSightingResource;
use App\Models\PlantSighting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SightingController extends Controller
{
    /**
     * Display a listing of plant sightings uploaded by Rangers.
     */
    public function index(): AnonymousResourceCollection
    {
        $sightings = PlantSighting::with(['plantSpecies', 'ranger:id,name'])
            ->latest()
            ->get();

        return PlantSightingResource::collection($sightings);
    }

    /**
     * Display the specified plant sighting.
     */
    public function show(int $id): JsonResponse
    {
        $sighting = PlantSighting::with(['plantSpecies', 'ranger:id,name'])->findOrFail($id);

        return response()->json([
            'data' => new PlantSightingResource($sighting),
        ]);
    }

    /**
     * Update the specified plant sighting (e.g. correct assigned species or location).
     */
    public function update(SightingUpdateRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $sighting = PlantSighting::with('plantSpecies')->findOrFail($id);

        if ($user && $user->role !== 'admin' && $sighting->ranger_id !== $user->id) {
            return response()->json([
                'message' => 'Anda hanya dapat mengedit temuan tumbuhan yang Anda buat sendiri. Hanya Admin yang dapat mengedit milik Ranger lain.',
            ], 403);
        }

        $data = $request->validated();

        // Update species attributes if provided
        if (! empty($data['common_name'])) {
            if ($sighting->plantSpecies) {
                $sighting->plantSpecies->update(array_filter([
                    'common_name' => $data['common_name'] ?? null,
                    'scientific_name' => $data['scientific_name'] ?? null,
                    'conservation_status' => $data['conservation_status'] ?? null,
                    'description' => $data['description'] ?? null,
                    'care_instructions' => $data['care_instructions'] ?? null,
                ]));
            } else {
                $code = strtoupper(\Illuminate\Support\Str::slug($data['common_name'], '_'));
                $species = \App\Models\PlantSpecies::firstOrCreate(
                    ['species_code' => $code],
                    [
                        'common_name' => $data['common_name'],
                        'scientific_name' => $data['scientific_name'] ?? $data['common_name'],
                        'conservation_status' => $data['conservation_status'] ?? 'Common',
                        'description' => $data['description'] ?? 'Tumbuhan hasil pemindaian Ranger.',
                        'care_instructions' => $data['care_instructions'] ?? 'Penyiraman teratur.',
                        'created_by' => $request->user()->id,
                    ]
                );
                $data['plant_species_id'] = $species->id;
            }
        }

        $sighting->update(array_filter([
            'plant_species_id' => $data['plant_species_id'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]));

        return response()->json([
            'message' => 'Data temuan tumbuhan berhasil diperbarui.',
            'data' => new PlantSightingResource($sighting->fresh(['plantSpecies', 'ranger:id,name'])),
        ]);
    }

    /**
     * Remove the specified plant sighting.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $sighting = PlantSighting::findOrFail($id);

        if ($user && $user->role !== 'admin' && $sighting->ranger_id !== $user->id) {
            return response()->json([
                'message' => 'Anda hanya dapat menghapus temuan tumbuhan yang Anda buat sendiri.',
            ], 403);
        }

        $speciesId = $sighting->plant_species_id;
        $sighting->delete();

        // Automatic orphan species cleanup: delete species catalog entry if no other sightings or plantings use it
        if ($speciesId) {
            $hasOtherSightings = PlantSighting::where('plant_species_id', $speciesId)->exists();
            $hasPlantings = \App\Models\Planting::where('plant_species_id', $speciesId)->exists();
            if (! $hasOtherSightings && ! $hasPlantings) {
                \App\Models\PlantSpecies::where('id', $speciesId)->delete();
            }
        }

        return response()->json([
            'message' => 'Data temuan tumbuhan berhasil dihapus dari peta.',
        ]);
    }
}
