<?php

use App\Http\Controllers\Api\CompostController;
use App\Http\Controllers\Api\DiscoveryController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\MapController;
use App\Http\Controllers\Api\MiniGameController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Ranger\SpeciesCatalogController;
use App\Http\Controllers\Ranger\VerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

// Viewer Protected API Routes (Protected by 'viewer' middleware)
Route::middleware(['auth:sanctum,web', 'viewer'])->group(function () {
    Route::post('/plant-discoveries', [DiscoveryController::class, 'store']);
    Route::get('/plant-sightings/nearby', [MapController::class, 'nearby']);

    // Gallery Routes
    Route::get('/gallery', [GalleryController::class, 'index']);
    Route::get('/gallery/{id}', [GalleryController::class, 'show']);
    Route::delete('/gallery/{id}', [GalleryController::class, 'destroy']);

    // MiniGame Routes
    Route::get('/minigame/plots', [MiniGameController::class, 'plots']);
    Route::post('/minigame/plots/{id}/unlock', [MiniGameController::class, 'unlockPlot']);
    Route::post('/minigame/plant', [MiniGameController::class, 'plant']);
    Route::post('/minigame/water', [MiniGameController::class, 'water']);
    Route::post('/minigame/harvest', [MiniGameController::class, 'harvest']);

    // Compost Challenge & Real Planting Routes
    Route::get('/compost-materials', [CompostController::class, 'materials']);
    Route::get('/compost-processes', [CompostController::class, 'processes']);
    Route::get('/compost-processes/{id}', [CompostController::class, 'showProcess']);
    Route::post('/compost-processes', [CompostController::class, 'startProcess']);
    Route::post('/compost-processes/{id}/checkin', [CompostController::class, 'checkin']);
    Route::post('/compost-processes/{id}/mature', [CompostController::class, 'mature']);
    Route::post('/real-plantings', [CompostController::class, 'storeRealPlanting']);

    // Wallet Routes
    Route::get('/wallet/balance', [WalletController::class, 'balance']);
    Route::get('/wallet/transactions', [WalletController::class, 'transactions']);
    Route::get('/wallet/exp-logs', [WalletController::class, 'expLogs']);

    // Leaderboard Routes
    Route::get('/leaderboard/current', [LeaderboardController::class, 'current']);
    Route::get('/leaderboard/history', [LeaderboardController::class, 'history']);
});

// Ranger Protected API Routes (Protected by 'ranger' middleware)
Route::middleware(['auth:sanctum,web', 'ranger'])->group(function () {
    Route::post('/scan', [ScanController::class, 'scan']);

    Route::prefix('ranger')->group(function () {
        Route::apiResource('/species', SpeciesCatalogController::class);
        Route::apiResource('/compost-materials', \App\Http\Controllers\Ranger\CompostCatalogController::class);
        Route::apiResource('/sightings', \App\Http\Controllers\Ranger\SightingController::class);
        Route::get('/verifications/pending', [VerificationController::class, 'pending']);
        Route::post('/verifications/sightings/{id}', [VerificationController::class, 'verifySighting']);
        Route::post('/verifications/real-plantings/{id}', [VerificationController::class, 'verifyRealPlanting']);
    });
});
