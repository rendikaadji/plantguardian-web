<?php

namespace App\Services;

use App\Models\ExpLog;
use App\Models\User;
use App\Models\WeeklyReward;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeaderboardService
{
    /**
     * Get real-time leaderboard ranking for the current ongoing week.
     */
    public function getCurrentLeaderboard(): Collection
    {
        $startOfWeek = Carbon::now()->startOfWeek()->toDateTimeString();
        $endOfWeek = Carbon::now()->endOfWeek()->toDateTimeString();

        $rankings = ExpLog::select('user_id', DB::raw('SUM(amount) as exp_earned'))
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->groupBy('user_id')
            ->orderByDesc('exp_earned')
            ->get();

        if ($rankings->isEmpty()) {
            return User::where('role', 'viewer')
                ->orderByDesc('exp')
                ->limit(20)
                ->get()
                ->values()
                ->map(function ($user, $index) {
                    return [
                        'rank' => $index + 1,
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_role' => $user->role,
                        'exp_earned' => (int) $user->exp,
                    ];
                });
        }

        $users = User::whereIn('id', $rankings->pluck('user_id'))
            ->get()
            ->keyBy('id');

        return $rankings->map(function ($row, $index) use ($users) {
            $user = $users->get($row->user_id);
            return [
                'rank' => $index + 1,
                'user_id' => $row->user_id,
                'user_name' => $user ? $user->name : 'Pengguna',
                'user_role' => $user ? $user->role : 'viewer',
                'exp_earned' => (int) $row->exp_earned,
            ];
        });
    }

    /**
     * Calculate EXP ranking for a given week and snapshot results to weekly_rewards.
     */
    public function calculateAndSnapshotForWeek(?Carbon $targetDate = null): Collection
    {
        $date = $targetDate ? $targetDate->copy() : Carbon::now()->subWeek();
        $startOfWeek = $date->copy()->startOfWeek();
        $endOfWeek = $date->copy()->endOfWeek();

        $expSummary = ExpLog::select('user_id', DB::raw('SUM(amount) as exp_earned'))
            ->whereBetween('created_at', [$startOfWeek->toDateTimeString(), $endOfWeek->toDateTimeString()])
            ->groupBy('user_id')
            ->orderByDesc('exp_earned')
            ->get();

        $snapshots = collect();

        DB::transaction(function () use ($expSummary, $startOfWeek, $endOfWeek, &$snapshots) {
            foreach ($expSummary as $index => $row) {
                $rank = $index + 1;
                $rewardDescription = match ($rank) {
                    1 => 'Juara 1 Mingguan — Lencana Emas',
                    2 => 'Juara 2 Mingguan — Lencana Perak',
                    3 => 'Juara 3 Mingguan — Lencana Perunggu',
                    default => null,
                };

                $record = WeeklyReward::updateOrCreate(
                    [
                        'user_id' => $row->user_id,
                        'week_start_date' => $startOfWeek->toDateString(),
                    ],
                    [
                        'week_end_date' => $endOfWeek->toDateString(),
                        'exp_earned' => (int) $row->exp_earned,
                        'rank' => $rank,
                        'reward_description' => $rewardDescription,
                        'created_at' => Carbon::now(),
                    ]
                );

                $snapshots->push($record);
            }
        });

        return $snapshots;
    }

    /**
     * Get historical weekly leaderboard snapshots.
     */
    public function getLeaderboardHistory(?int $userId = null): Collection
    {
        $query = WeeklyReward::with('user:id,name,email,role')
            ->orderByDesc('week_start_date')
            ->orderBy('rank');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }
}
