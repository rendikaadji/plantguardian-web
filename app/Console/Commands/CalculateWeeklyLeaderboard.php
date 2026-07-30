<?php

namespace App\Console\Commands;

use App\Services\LeaderboardService;
use Illuminate\Console\Command;

class CalculateWeeklyLeaderboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leaderboard:calculate-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hitung total EXP mingguan per pengguna dari exp_logs dan simpan snapshot ke weekly_rewards';

    /**
     * Execute the console command.
     */
    public function handle(LeaderboardService $leaderboardService): int
    {
        $this->info('Memulai kalkulasi papan peringkat mingguan...');

        $snapshots = $leaderboardService->calculateAndSnapshotForWeek();

        $this->info("Kalkulasi selesai. Berhasil menyimpan snapshot untuk {$snapshots->count()} pengguna.");

        return Command::SUCCESS;
    }
}
