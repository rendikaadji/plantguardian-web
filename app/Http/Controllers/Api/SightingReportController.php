<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlantSighting;
use App\Models\SightingReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SightingReportController extends Controller
{
    /**
     * Store a user report for a specific plant sighting.
     */
    public function store(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|in:fake_specimen,plant_missing_or_dead,species_mismatch_or_replaced,other',
            'notes' => 'nullable|string|max:500',
        ]);

        $sighting = PlantSighting::findOrFail($id);
        $user = $request->user();

        // Check for existing pending report by this user on the same sighting to prevent spamming
        $existingReport = SightingReport::where('user_id', $user->id)
            ->where('plant_sighting_id', $sighting->id)
            ->where('status', 'pending')
            ->first();

        if ($existingReport) {
            return response()->json([
                'message' => 'Kamu sudah pernah melaporkan temuan ini. Laporanmu sedang ditinjau oleh Admin.',
            ], 422);
        }

        SightingReport::create([
            'user_id' => $user->id,
            'plant_sighting_id' => $sighting->id,
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Laporan temuan tumbuhan berhasil dikirim ke Admin. Terima kasih atas kontribusimu!',
        ]);
    }
}
