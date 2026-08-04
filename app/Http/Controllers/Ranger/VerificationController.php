<?php

namespace App\Http\Controllers\Ranger;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ranger\VerificationDecisionRequest;
use App\Services\VerificationService;
use Illuminate\Http\JsonResponse;

class VerificationController extends Controller
{
    public function __construct(
        protected VerificationService $verificationService
    ) {}

    /**
     * Get pending verification queue for sightings.
     */
    public function pending(): JsonResponse
    {
        $queue = $this->verificationService->getPendingQueue();

        return response()->json([
            'message' => 'Antrean verifikasi temuan berhasil dimuat.',
            'data' => $queue,
        ]);
    }

    /**
     * Verify or reject a plant sighting item.
     */
    public function verifySighting(VerificationDecisionRequest $request, int $id): JsonResponse
    {
        $decision = $request->input('status', $request->input('decision'));
        $sighting = $this->verificationService->verifySighting($request->user(), $id, $decision);

        return response()->json([
            'message' => "Hasil scan tumbuhan berhasil ditandai {$decision}.",
            'data' => $sighting,
        ]);
    }
}
