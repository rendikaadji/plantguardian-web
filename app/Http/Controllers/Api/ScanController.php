<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanRequest;
use App\Http\Resources\PlantSightingResource;
use App\Services\PlantScanService;
use Illuminate\Http\JsonResponse;

class ScanController extends Controller
{
    public function __construct(
        protected PlantScanService $plantScanService
    ) {}

    /**
     * Process plant scan upload and return sighting resource.
     */
    public function scan(ScanRequest $request): JsonResponse
    {
        $sighting = $this->plantScanService->scan(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Pemindaian tumbuhan berhasil.',
            'data' => new PlantSightingResource($sighting),
        ], 201);
    }
}
