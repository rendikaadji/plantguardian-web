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
        $user = $request->user();

        $plotsCount = GardenPlot::where('user_id', $user->id)->count();
        if ($plotsCount === 0) {
            // Auto initialize 4 initial plots for user
            GardenPlot::create(['user_id' => $user->id, 'slot_number' => 1, 'unlocked' => true, 'purchase_cost' => 0]);
            GardenPlot::create(['user_id' => $user->id, 'slot_number' => 2, 'unlocked' => false, 'purchase_cost' => 50]);
            GardenPlot::create(['user_id' => $user->id, 'slot_number' => 3, 'unlocked' => false, 'purchase_cost' => 100]);
            GardenPlot::create(['user_id' => $user->id, 'slot_number' => 4, 'unlocked' => false, 'purchase_cost' => 150]);
        }

        $plots = GardenPlot::with(['currentPlanting.plantSpecies'])
            ->where('user_id', $user->id)
            ->orderBy('slot_number')
            ->get();

        $seeds = \App\Models\InventoryItem::where('user_id', $user->id)
            ->where('item_type', 'seed')
            ->where('quantity', '>', 0)
            ->get();

        $tools = \App\Models\InventoryItem::where('user_id', $user->id)
            ->whereIn('item_type', ['tool', 'material'])
            ->where('quantity', '>', 0)
            ->get();

        return response()->json([
            'data' => $plots,
            'seeds' => $seeds,
            'tools' => $tools,
            'user_coin' => $user->coin,
            'user_exp' => $user->exp,
        ]);
    }

    /**
     * Unlock a garden plot for the user.
     */
    public function unlockPlot(Request $request, int $plotId): JsonResponse
    {
        try {
            $plot = $this->gardenService->unlockPlot($request->user(), $plotId);

            return response()->json([
                'message' => 'Lahan tanam berhasil dibuka.',
                'data' => $plot,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Gagal membuka lahan tanam.',
            ], 400);
        }
    }

    /**
     * Plant a seed on a plot.
     */
    public function plant(PlantingRequest $request): JsonResponse
    {
        try {
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
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Gagal menanam benih.',
            ], 400);
        }
    }

    /**
     * Water/care for a growing plant.
     */
    public function water(PlantingRequest $request): JsonResponse
    {
        try {
            $planting = $this->gardenService->waterPlant(
                $request->user(),
                (int) $request->validated('planting_id')
            );

            return response()->json([
                'message' => 'Tanaman berhasil disiram.',
                'data' => new PlantingResource($planting),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Gagal menyiram tanaman.',
            ], 400);
        }
    }

    /**
     * Apply fertilizer (Pupuk Organik Super) to speed up growth.
     */
    public function fertilize(PlantingRequest $request): JsonResponse
    {
        try {
            $planting = $this->gardenService->applyFertilizer(
                $request->user(),
                (int) $request->validated('planting_id')
            );

            return response()->json([
                'message' => 'Pupuk Organik Super berhasil digunakan! Pertumbuhan dipercepat 5 menit 🧪',
                'data' => new PlantingResource($planting),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Gagal menggunakan pupuk.',
            ], 400);
        }
    }

    /**
     * Harvest a ready plant and claim rewards.
     */
    public function harvest(PlantingRequest $request): JsonResponse
    {
        try {
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
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Gagal memanen tanaman.',
            ], 400);
        }
    }
}
