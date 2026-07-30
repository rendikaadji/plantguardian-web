<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlantingRequest;
use App\Http\Resources\PlantingResource;
use App\Models\GardenPlot;
use App\Services\GardenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MiniGameController extends Controller
{
    public function __construct(
        protected GardenService $gardenService
    ) {}

    /**
     * Display a listing of user garden plots with active planting.
     */
    public function plots(Request $request): JsonResponse
    {
        $plots = GardenPlot::with(['currentPlanting.plantSpecies'])
            ->where('user_id', $request->user()->id)
            ->orderBy('slot_number')
            ->get();

        return response()->json([
            'data' => $plots,
        ]);
    }

    /**
     * Unlock a garden plot for the user.
     */
    public function unlockPlot(Request $request, int $plotId): JsonResponse
    {
        $plot = $this->gardenService->unlockPlot($request->user(), $plotId);

        return response()->json([
            'message' => 'Lahan tanam berhasil dibuka.',
            'data' => $plot,
        ]);
    }

    /**
     * Plant a seed on a plot.
     */
    public function plant(PlantingRequest $request): JsonResponse
    {
        $planting = $this->gardenService->plantSeed(
            $request->user(),
            (int) $request->validated('garden_plot_id'),
            (string) $request->validated('seed_code'),
            $request->validated('plant_species_id') ? (int) $request->validated('plant_species_id') : null
        );

        return response()->json([
            'message' => 'Benih berhasil ditanam.',
            'data' => new PlantingResource($planting),
        ], 201);
    }

    /**
     * Water/care for a growing plant.
     */
    public function water(PlantingRequest $request): JsonResponse
    {
        $planting = $this->gardenService->waterPlant(
            $request->user(),
            (int) $request->validated('planting_id')
        );

        return response()->json([
            'message' => 'Tanaman berhasil disiram.',
            'data' => new PlantingResource($planting),
        ]);
    }

    /**
     * Harvest a ready plant and claim rewards.
     */
    public function harvest(PlantingRequest $request): JsonResponse
    {
        $result = $this->gardenService->harvestPlant(
            $request->user(),
            (int) $request->validated('planting_id')
        );

        return response()->json([
            'message' => 'Tanaman berhasil dipanen.',
            'data' => [
                'planting' => new PlantingResource($result['planting']),
                'exp_earned' => $result['exp_reward'],
                'coin_earned' => $result['coin_reward'],
            ],
        ]);
    }
}
