<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlantDiscoveryRequest;
use App\Http\Resources\PlantDiscoveryResource;
use App\Services\DiscoveryService;
use Exception;
use Illuminate\Http\JsonResponse;

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
}
