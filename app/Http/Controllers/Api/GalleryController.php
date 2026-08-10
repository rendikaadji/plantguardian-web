<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlantDiscoveryResource;
use App\Http\Resources\PlantSightingResource;
use App\Models\PlantDiscovery;
use App\Models\PlantSighting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Display a listing of personal gallery items (discoveries for Viewer, scan sightings for Ranger).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'ranger') {
            $sightings = PlantSighting::with(['plantSpecies', 'ranger:id,name'])
                ->where('ranger_id', $user->id)
                ->latest()
                ->get();

            return response()->json([
                'role' => 'ranger',
                'data' => PlantSightingResource::collection($sightings),
            ]);
        }

        // Default for Viewer: return user's catches from plant_discoveries
        $discoveries = PlantDiscovery::with(['plantSighting.plantSpecies', 'plantSighting.ranger:id,name'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $totalSpecies = \App\Models\PlantSpecies::count();

        return response()->json([
            'role' => 'viewer',
            'data' => PlantDiscoveryResource::collection($discoveries),
            'total_species' => $totalSpecies,
        ]);
    }

    /**
     * Display the specified gallery item detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'ranger') {
            $sighting = PlantSighting::with(['plantSpecies', 'ranger:id,name'])
                ->where('ranger_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            return response()->json([
                'data' => new PlantSightingResource($sighting),
            ]);
        }

        $discovery = PlantDiscovery::with('plantSighting.plantSpecies')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'data' => new PlantDiscoveryResource($discovery),
        ]);
    }

    /**
     * Remove the specified gallery item.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'ranger') {
            $sighting = PlantSighting::where('ranger_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            $speciesId = $sighting->plant_species_id;
            $sighting->delete();

            if ($speciesId) {
                $hasOtherSightings = PlantSighting::where('plant_species_id', $speciesId)->exists();
                $hasPlantings = \App\Models\Planting::where('plant_species_id', $speciesId)->exists();
                if (! $hasOtherSightings && ! $hasPlantings) {
                    \App\Models\PlantSpecies::where('id', $speciesId)->delete();
                }
            }
        } else {
            $discovery = PlantDiscovery::where('user_id', $user->id)
                ->where('id', $id)
                ->firstOrFail();

            $discovery->delete();
        }

        return response()->json([
            'message' => 'Entri galeri berhasil dihapus.',
        ]);
    }
}
