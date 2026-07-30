<?php

namespace App\Http\Controllers\Ranger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ranger\SpeciesRequest;
use App\Http\Resources\Ranger\SpeciesResource;
use App\Services\SpeciesCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SpeciesCatalogController extends Controller
{
    public function __construct(
        protected SpeciesCatalogService $speciesCatalogService
    ) {}

    /**
     * Display listing of plant species catalog.
     */
    public function index(): AnonymousResourceCollection
    {
        $species = $this->speciesCatalogService->getAllSpecies();
        return SpeciesResource::collection($species);
    }

    /**
     * Store a newly created plant species in catalog.
     */
    public function store(SpeciesRequest $request): JsonResponse
    {
        $species = $this->speciesCatalogService->createSpecies(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Spesies tumbuhan berhasil ditambahkan ke katalog.',
            'data' => new SpeciesResource($species),
        ], 201);
    }

    /**
     * Display the specified plant species catalog item.
     */
    public function show(int $id): JsonResponse
    {
        $species = $this->speciesCatalogService->getSpeciesById($id);

        return response()->json([
            'data' => new SpeciesResource($species),
        ]);
    }

    /**
     * Update the specified plant species catalog item.
     */
    public function update(SpeciesRequest $request, int $id): JsonResponse
    {
        $species = $this->speciesCatalogService->updateSpecies($id, $request->validated());

        return response()->json([
            'message' => 'Data spesies tumbuhan berhasil diperbarui.',
            'data' => new SpeciesResource($species),
        ]);
    }

    /**
     * Remove the specified plant species from catalog.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->speciesCatalogService->deleteSpecies($id);

        return response()->json([
            'message' => 'Spesies tumbuhan berhasil dihapus dari katalog.',
        ]);
    }
}
