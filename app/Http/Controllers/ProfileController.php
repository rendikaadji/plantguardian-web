<?php

namespace App\Http\Controllers;

use App\Models\CompostProcess;
use App\Models\Planting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page with interactive stats and badges.
     */
    public function index()
    {
        $user = Auth::user();

        // Calculate level threshold & EXP progress
        $currentLevel = $user->level ?? 1;
        $exp = $user->exp ?? 0;
        $expPerLevel = 1000;
        $expInCurrentLevel = $exp % $expPerLevel;
        $levelProgressPercent = min(100, (int) round(($expInCurrentLevel / $expPerLevel) * 100));

        // Rank designation based on level
        $rankName = match (true) {
            $currentLevel >= 15 => 'SUPREME GUARDIAN RANK',
            $currentLevel >= 10 => 'GRAND ARBITER RANK',
            $currentLevel >= 5 => 'MASTER BOTANIST RANK',
            default => 'FLORA APPRENTICE RANK',
        };

        // User stats
        $totalPlantings = Planting::whereHas('gardenPlot', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        $harvestedCount = Planting::whereHas('gardenPlot', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('status', 'harvested')->count();

        $totalComposts = CompostProcess::where('user_id', $user->id)->count();

        // Dynamic badges
        $badges = [
            [
                'code' => 'forest_guard',
                'name' => 'Forest Guard',
                'count' => max(1, $totalPlantings),
                'icon' => '🌲',
                'color' => 'bg-[#1F3D20]',
                'unlocked' => true,
                'desc' => 'Diberikan untuk penanaman flora di kebun.',
            ],
            [
                'code' => 'soil_master',
                'name' => 'Soil Master',
                'count' => max(1, $totalComposts + 1),
                'icon' => '🍂',
                'color' => 'bg-[#8B6A4C]',
                'unlocked' => true,
                'desc' => 'Diberikan untuk pengolahan sampel kompos.',
            ],
            [
                'code' => 'storm_bringer',
                'name' => 'Storm Bringer',
                'count' => max(1, (int) ($user->level * 2)),
                'icon' => '⛈️',
                'color' => 'bg-[#2E6DA4]',
                'unlocked' => true,
                'desc' => 'Diberikan untuk perawatan & penyiraman kebun.',
            ],
            [
                'code' => 'blossom_sentinel',
                'name' => 'Blossom Sentinel',
                'count' => max(1, $harvestedCount),
                'icon' => '🌸',
                'color' => 'bg-[#7D5BA6]',
                'unlocked' => $harvestedCount > 0,
                'desc' => 'Diberikan setelah berhasil memanen hasil kebun.',
            ],
        ];

        // Hydration & Vitality stats calculation
        $hydrationPercent = min(98, max(60, 70 + ($harvestedCount * 3)));
        $vitalityPercent = min(95, max(50, 60 + ($totalPlantings * 4)));

        // Alliance Invitation Code
        $allianceCode = sprintf('PG-%05d', $user->id);

        return view('profile', compact(
            'user',
            'currentLevel',
            'exp',
            'expPerLevel',
            'expInCurrentLevel',
            'levelProgressPercent',
            'rankName',
            'totalPlantings',
            'harvestedCount',
            'totalComposts',
            'badges',
            'hydrationPercent',
            'vitalityPercent',
            'allianceCode'
        ));
    }
}
