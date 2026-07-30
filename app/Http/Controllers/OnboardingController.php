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

        if ($user && $user->role === 'ranger') {
            return redirect()->route('ranger.dashboard');
        }

        if ($user && $user->role === 'viewer') {
            return redirect()->route('home');
        }

        return view('onboarding.pilih-role');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:viewer,ranger'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['role' => $validated['role']]);

        if ($validated['role'] === 'viewer') {
            return redirect()->route('onboarding.tutorial-viewer');
        }

        return redirect()->route('ranger.dashboard');
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
