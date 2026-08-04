<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function showPilihRole(): View|RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if ($user && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user && $user->role === 'ranger') {
            return redirect()->route('ranger.dashboard');
        }

        if ($user && (! $user->role || $user->role === 'viewer')) {
            if (! $user->role) {
                $user->update(['role' => 'viewer']);
            }
            return redirect()->route('home');
        }

        return redirect()->route('home');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (! $user->role) {
            $user->update(['role' => 'viewer']);
        }

        return redirect()->route('onboarding.tutorial-viewer');
    }

    public function showTutorialViewer(): View
    {
        return view('onboarding.tutorial-viewer');
    }

    public function showRangerPlaceholder(): View
    {
        return view('onboarding.ranger-placeholder');
    }
}
