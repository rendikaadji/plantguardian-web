<?php

namespace App\Services;

use App\Models\PlantDiscovery;
use App\Models\PlantSighting;
use App\Models\PlantSpecies;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminService
{
    /**
     * Get system aggregation statistics for Admin Dashboard.
     */
    public function getDashboardStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_viewers' => User::where('role', 'viewer')->count(),
            'total_rangers' => User::where('role', 'ranger')->count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_sightings' => PlantSighting::count(),
            'pending_verifications' => PlantSighting::where('verification_status', 'pending')->count(),
            'total_species_catalog' => PlantSpecies::count(),
            'total_exp_issued' => User::sum('exp'),
            'total_coin_issued' => User::sum('coin'),
        ];
    }

    /**
     * Get paginated users list with optional search query.
     */
    public function getUsersList(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get recent plant sightings for monitoring.
     */
    public function getRecentSightings(int $limit = 10): Collection
    {
        return PlantSighting::with(['plantSpecies', 'ranger'])
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Update a user's role.
     */
    public function updateUserRole(User $targetUser, string $newRole): User
    {
        if (! in_array($newRole, ['viewer', 'ranger', 'admin'])) {
            throw new Exception('Role pengguna tidak valid.');
        }

        $targetUser->update(['role' => $newRole]);

        return $targetUser->fresh();
    }

    /**
     * Get detailed activity and plant records for a specific user (Viewer or Ranger).
     */
    public function getUserActivityDetails(User $targetUser): array
    {
        if ($targetUser->role === 'ranger') {
            // Ranger: Get plant sightings scanned/photographed by this ranger with location
            $sightings = PlantSighting::with('plantSpecies')
                ->where('ranger_id', $targetUser->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($sighting) {
                    return [
                        'id' => $sighting->id,
                        'species_name' => $sighting->plantSpecies ? $sighting->plantSpecies->common_name : 'Spesies Tumbuhan',
                        'scientific_name' => $sighting->plantSpecies ? $sighting->plantSpecies->scientific_name : null,
                        'photo_url' => $sighting->photo_path ? asset('storage/' . $sighting->photo_path) : null,
                        'status' => $sighting->verification_status ?? 'pending',
                        'latitude' => $sighting->latitude,
                        'longitude' => $sighting->longitude,
                        'location_text' => $sighting->latitude ? "GPS: {$sighting->latitude}, {$sighting->longitude}" : 'Tanpa Lokasi',
                        'created_at' => $sighting->created_at ? $sighting->created_at->format('d M Y H:i') : '-',
                    ];
                });

            return [
                'type' => 'ranger',
                'items' => $sightings,
                'total_count' => $sightings->count(),
            ];
        } else {
            // Viewer: Get plants discovered/caught by this viewer
            $discoveries = PlantDiscovery::with(['plantSighting.plantSpecies'])
                ->where('user_id', $targetUser->id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($discovery) {
                    $species = $discovery->plantSighting ? $discovery->plantSighting->plantSpecies : null;
                    return [
                        'id' => $discovery->id,
                        'species_name' => $species ? $species->common_name : 'Spesies Tumbuhan',
                        'scientific_name' => $species ? $species->scientific_name : null,
                        'photo_url' => $species ? $species->photo_url : null,
                        'discovered_at' => $discovery->created_at ? $discovery->created_at->format('d M Y H:i') : '-',
                        'latitude' => $discovery->latitude ?? ($discovery->plantSighting ? $discovery->plantSighting->latitude : null),
                        'longitude' => $discovery->longitude ?? ($discovery->plantSighting ? $discovery->plantSighting->longitude : null),
                        'location_text' => $discovery->latitude ? "GPS: {$discovery->latitude}, {$discovery->longitude}" : ($discovery->plantSighting && $discovery->plantSighting->latitude ? "GPS: {$discovery->plantSighting->latitude}, {$discovery->plantSighting->longitude}" : 'Tanpa Lokasi'),
                    ];
                });

            return [
                'type' => 'viewer',
                'items' => $discoveries,
                'total_count' => $discoveries->count(),
            ];
        }
    }
}
