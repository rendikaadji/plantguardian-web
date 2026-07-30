<?php

namespace App\Http\Controllers\Ranger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ranger\CompostMaterialRequest;
use App\Services\CompostCatalogService;
use Illuminate\Http\JsonResponse;

class CompostCatalogController extends Controller
{
    public function __construct(
        protected CompostCatalogService $compostCatalogService
    ) {}

    public function index(): JsonResponse
    {
        $materials = $this->compostCatalogService->getAllMaterials();

        return response()->json([
            'data' => $materials,
        ]);
    }

    public function store(CompostMaterialRequest $request): JsonResponse
    {
        $material = $this->compostCatalogService->createMaterial(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Bahan kompos berhasil ditambahkan ke katalog.',
            'data' => $material,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $material = $this->compostCatalogService->getMaterialById($id);

        return response()->json([
            'data' => $material,
        ]);
    }

    public function update(CompostMaterialRequest $request, int $id): JsonResponse
    {
        $material = $this->compostCatalogService->updateMaterial($id, $request->validated());

        return response()->json([
            'message' => 'Bahan kompos berhasil diperbarui.',
            'data' => $material,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->compostCatalogService->deleteMaterial($id);

        return response()->json([
            'message' => 'Bahan kompos berhasil dihapus dari katalog.',
        ]);
    }
}
