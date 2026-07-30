<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompostMaterial;
use App\Models\CompostProcess;
use App\Services\CompostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompostController extends Controller
{
    public function __construct(
        protected CompostService $compostService
    ) {}

    public function materials(): JsonResponse
    {
        $materials = CompostMaterial::latest()->get();

        return response()->json([
            'data' => $materials,
        ]);
    }

    public function processes(Request $request): JsonResponse
    {
        $processes = CompostProcess::with(['compostMaterial', 'progressLogs'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => $processes,
        ]);
    }

    public function showProcess(Request $request, int $id): JsonResponse
    {
        $process = CompostProcess::with(['compostMaterial', 'progressLogs'])
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'data' => $process,
        ]);
    }

    public function startProcess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'compost_material_id' => ['nullable', 'integer', 'exists:compost_materials,id'],
        ]);

        $process = $this->compostService->startProcess($request->user(), $validated['compost_material_id'] ?? null);

        return response()->json([
            'message' => 'Tantangan proses kompos berhasil dimulai! (+50 EXP)',
            'data' => $process,
        ], 201);
    }

    public function checkin(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'stage_label' => ['required', 'string', 'max:255'],
            'photo_path' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'note' => ['nullable', 'string'],
        ]);

        $log = $this->compostService->recordCheckin($request->user(), $id, $validated);

        return response()->json([
            'message' => 'Check-in progress kompos berhasil dicatat! (+20 EXP)',
            'data' => $log,
        ], 201);
    }

    public function mature(Request $request, int $id): JsonResponse
    {
        $process = $this->compostService->markMatured($request->user(), $id);

        return response()->json([
            'message' => 'Kompos berhasil ditandai matang! (+100 EXP)',
            'data' => $process,
        ]);
    }

    public function storeRealPlanting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'compost_process_id' => ['nullable', 'integer', 'exists:compost_processes,id'],
            'plant_species_id' => ['nullable', 'integer', 'exists:plant_species,id'],
            'photo_path' => ['required', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $realPlanting = $this->compostService->recordRealPlanting($request->user(), $validated);

        return response()->json([
            'message' => 'Bukti penanaman pohon nyata berhasil diunggah! (+300 EXP)',
            'data' => $realPlanting,
        ], 201);
    }
}
