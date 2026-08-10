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

// Locale Switch Route (Accessible to all)
Route::post('/locale/switch', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'locale' => 'required|in:en,id',
    ]);

    $locale = $validated['locale'];
    session(['locale' => $locale]);

    if (auth()->check()) {
        auth()->user()->update(['locale' => $locale]);
    }

    return redirect()->back();
})->name('locale.switch');

// Authenticated User Routes (Common / Onboarding)
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    // Onboarding Flow
    Route::get('/onboarding/pilih-role', [OnboardingController::class, 'showPilihRole'])->name('onboarding.pilih-role');
    Route::post('/onboarding/pilih-role', [OnboardingController::class, 'storeRole'])->name('onboarding.store-role');
    Route::get('/onboarding/tutorial-viewer', [OnboardingController::class, 'showTutorialViewer'])->name('onboarding.tutorial-viewer');
    Route::get('/onboarding/ranger-placeholder', [OnboardingController::class, 'showRangerPlaceholder'])->name('onboarding.ranger-placeholder');

    // Profile Password Update Route
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

// Admin Control Routes (Protected by 'admin' middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}/details', [\App\Http\Controllers\AdminController::class, 'userDetails'])->name('users.details');
    Route::post('/users/{user}/role', [\App\Http\Controllers\AdminController::class, 'updateRole'])->name('users.update-role');
    Route::get('/reports', [\App\Http\Controllers\AdminController::class, 'reports'])->name('reports');
    Route::post('/reports/{report}/resolve', [\App\Http\Controllers\AdminController::class, 'resolveReport'])->name('reports.resolve');
    Route::get('/monitoring', [\App\Http\Controllers\AdminController::class, 'monitoring'])->name('monitoring');
});

// Viewer Web Views (Protected by 'viewer' middleware)
Route::middleware(['auth', 'viewer'])->group(function () {
    Route::get('/', function () {
        return view('home');
    })->name('home');

    // Friend & Shop Item Request/Gift Routes
    Route::get('/api/friends', [\App\Http\Controllers\FriendController::class, 'index'])->name('friends.index');
    Route::get('/api/friends/search', [\App\Http\Controllers\FriendController::class, 'search'])->name('friends.search');
    Route::post('/api/friends/add', [\App\Http\Controllers\FriendController::class, 'addFriend'])->name('friends.add');
    Route::post('/api/friends/accept', [\App\Http\Controllers\FriendController::class, 'acceptFriend'])->name('friends.accept');
    Route::post('/api/friends/remove', [\App\Http\Controllers\FriendController::class, 'removeFriend'])->name('friends.remove');
    Route::post('/api/friends/request-item', [\App\Http\Controllers\FriendController::class, 'requestItem'])->name('friends.request-item');
    Route::post('/api/friends/gift-item', [\App\Http\Controllers\FriendController::class, 'giftItem'])->name('friends.gift-item');

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

    Route::get('/achievement', [\App\Http\Controllers\Api\AchievementController::class, 'index'])->name('achievement');
    Route::post('/api/achievements/claim', [\App\Http\Controllers\Api\AchievementController::class, 'claim'])->name('achievements.claim');

    Route::get('/shop', function () {
        return view('shop');
    })->name('shop');

    Route::get('/leaderboard', function () {
        return view('leaderboard');
    })->name('leaderboard');
});

// Ranger Web Views (Protected by 'ranger' middleware)
Route::middleware(['auth', 'ranger'])->prefix('ranger')->name('ranger.')->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('peta');
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
