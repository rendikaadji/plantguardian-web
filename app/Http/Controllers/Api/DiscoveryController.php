<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlantDiscoveryRequest;
use App\Http\Resources\PlantDiscoveryResource;
use App\Services\DiscoveryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    public function __construct(
        protected DiscoveryService $discoveryService
    ) {}

    /**
     * Handle Viewer "catch" of a verified plant sighting.
     */
    public function store(PlantDiscoveryRequest $request): JsonResponse
    {
        try {
            $discovery = $this->discoveryService->discover(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'message' => 'Berhasil menemukan tumbuhan!',
                'data' => new PlantDiscoveryResource($discovery),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Handle direct map discovery claim by sighting ID.
     */
    public function claimFromMap(Request $request, int $id): JsonResponse
    {
        try {
            $discovery = $this->discoveryService->discover(
                $request->user(),
                ['plant_sighting_id' => $id]
            );

            $reward = $discovery->reward_summary ?? ['exp_gained' => 100, 'coin_gained' => 50];

            return response()->json([
                'message' => "Selamat! Tumbuhan berhasil kamu temukan (+{$reward['exp_gained']} EXP, +{$reward['coin_gained']} Coin)!",
                'data' => new PlantDiscoveryResource($discovery),
                'exp_gained' => $reward['exp_gained'],
                'coin_gained' => $reward['coin_gained'],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
