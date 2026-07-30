<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated User Routes (Common / Onboarding)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Onboarding Flow
    Route::get('/onboarding/pilih-role', [OnboardingController::class, 'showPilihRole'])->name('onboarding.pilih-role');
    Route::post('/onboarding/pilih-role', [OnboardingController::class, 'storeRole'])->name('onboarding.store-role');
    Route::get('/onboarding/tutorial-viewer', [OnboardingController::class, 'showTutorialViewer'])->name('onboarding.tutorial-viewer');
    Route::get('/onboarding/ranger-placeholder', [OnboardingController::class, 'showRangerPlaceholder'])->name('onboarding.ranger-placeholder');
});

// Viewer Web Views (Protected by 'viewer' middleware)
Route::middleware(['auth', 'viewer'])->group(function () {
    Route::get('/', function () {
        return view('home');
    })->name('home');

    Route::get('/peta', function () {
        return view('peta');
    })->name('peta');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

    Route::get('/galeri', function () {
        return view('galeri');
    })->name('galeri');

    Route::get('/minigame', function () {
        return view('minigame');
    })->name('minigame');

    Route::get('/shop', function () {
        return view('shop');
    })->name('shop');
});

// Ranger Web Views (Protected by 'ranger' middleware)
Route::middleware(['auth', 'ranger'])->prefix('ranger')->name('ranger.')->group(function () {
    Route::get('/dashboard', function () {
        return view('ranger.dashboard');
    })->name('dashboard');

    Route::get('/peta', function () {
        return view('peta');
    })->name('peta');

    // Species Catalog Web Views
    Route::get('/species', function () {
        return view('ranger.species.index');
    })->name('species.index');

    Route::get('/species/create', function () {
        return view('ranger.species.form', ['speciesId' => null]);
    })->name('species.create');

    Route::get('/species/{id}/edit', function ($id) {
        return view('ranger.species.form', ['speciesId' => $id]);
    })->name('species.edit');

    // Compost Materials Catalog Web Views
    Route::get('/compost-materials', function () {
        return view('ranger.compost-materials.index');
    })->name('compost-materials.index');

    Route::get('/compost-materials/create', function () {
        return view('ranger.compost-materials.form', ['materialId' => null]);
    })->name('compost-materials.create');

    Route::get('/compost-materials/{id}/edit', function ($id) {
        return view('ranger.compost-materials.form', ['materialId' => $id]);
    })->name('compost-materials.edit');

    // Plant Sightings Edit Web Views
    Route::get('/sightings', function () {
        return view('ranger.sightings.index');
    })->name('sightings.index');

    Route::get('/sightings/{id}/edit', function ($id) {
        return view('ranger.sightings.form', ['sightingId' => $id]);
    })->name('sightings.edit');

    // Verification Queue Web View
    Route::get('/verifications', function () {
        return view('ranger.verifications.index');
    })->name('verifications.index');
});
