<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlantSightingResource;
use App\Models\PlantDiscovery;
use App\Models\PlantSighting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    /**
     * Get nearby plant sightings for map markers (Viewer view: verified only + sudah_ditemukan flag).
     */
    public function nearby(Request $request): JsonResponse
    {
        $user = $request->user();
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $radius = (float) $request->query('radius', 25); // radius in kilometers

        $query = PlantSighting::with(['plantSpecies', 'ranger:id,name'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Filter status: Viewers only see 'verified', Rangers can see all statuses
        if ($user && $user->role === 'viewer') {
            $query->where('verification_status', 'verified');
        }

        if ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
            $lat = (float) $lat;
            $lng = (float) $lng;

            if (DB::connection()->getDriverName() === 'sqlite') {
                $sightings = $query->get()->filter(function ($sighting) use ($lat, $lng, $radius) {
                    $earthRadius = 6371;
                    $dLat = deg2rad($sighting->latitude - $lat);
                    $dLng = deg2rad($sighting->longitude - $lng);
                    $a = sin($dLat / 2) * sin($dLat / 2) +
                         cos(deg2rad($lat)) * cos(deg2rad((float) $sighting->latitude)) *
                         sin($dLng / 2) * sin($dLng / 2);
                    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                    $distance = $earthRadius * $c;
                    $sighting->distance = $distance;
                    return $distance <= $radius;
                })->sortBy('distance')->take(50)->values();
            } else {
                $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))";
                $sightings = $query->select('*')
                    ->selectRaw("{$haversine} AS distance", [$lat, $lng, $lat])
                    ->having('distance', '<=', $radius)
                    ->orderBy('distance')
                    ->limit(50)
                    ->get();
            }
        } else {
            $sightings = $query->latest()->limit(50)->get();
        }

        $discoveredSightingIds = [];
        if ($user) {
            $discoveredSightingIds = PlantDiscovery::where('user_id', $user->id)
                ->pluck('plant_sighting_id')
                ->flip()
                ->toArray();
        }

        $sightings->each(function ($sighting) use ($discoveredSightingIds) {
            $sighting->sudah_ditemukan = isset($discoveredSightingIds[$sighting->id]);
        });

        return response()->json([
            'message' => 'Temuan tumbuhan di sekitar berhasil dimuat.',
            'data' => PlantSightingResource::collection($sightings),
        ]);
    }

    /**
     * Get detail of a specific plant sighting for map view.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $sighting = PlantSighting::with(['plantSpecies', 'ranger:id,name'])->findOrFail($id);

        if ($user) {
            $isDiscovered = PlantDiscovery::where('user_id', $user->id)
                ->where('plant_sighting_id', $sighting->id)
                ->exists();
            $sighting->sudah_ditemukan = $isDiscovered;
        }

        return response()->json([
            'message' => 'Detail temuan berhasil dimuat.',
            'data' => new PlantSightingResource($sighting),
        ]);
    }
}
