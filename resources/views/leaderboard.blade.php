@extends('layouts.app')

@yield('title', 'Leaderboard Mingguan — Plant Guardian')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Header Banner Garden Guardian RPG -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#1F3D20] via-[#2D4A2E] to-[#152B16] text-[#F5F4DA] p-6 sm:p-8 shadow-xl border border-[#E2E1C4]/20">
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FBFAF0]/15 text-[#F5F4DA] text-xs font-baloo font-bold backdrop-blur-md">
                <span>🏆</span>
                <span>Papan Peringkat Global</span>
            </div>
            <h1 class="font-baloo font-extrabold text-2xl sm:text-4xl text-[#F5F4DA] tracking-tight">
                Leaderboard Mingguan
            </h1>
            <p class="text-xs sm:text-sm text-[#F5F4DA]/80 font-nunito max-w-xl leading-relaxed">
                Pantau perolehan EXP minggu ini dan bersainglah bersama para Guardian lain untuk menempati tahta peringkat puncak!
            </p>
        </div>

        <!-- Decorative background elements -->
        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-[#E2E1C4]/10 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- Logged-in User Current Status Card -->
    <div id="leaderboard-user-status">
        <!-- Rendered dynamically by leaderboard.js -->
    </div>

    <!-- Tab Switcher (Minggu Ini vs Riwayat Juara) -->
    <div class="flex items-center gap-2 p-1.5 rounded-2xl bg-[#E2E1C4]/40 border border-[#1F3D20]/10 max-w-sm mx-auto">
        <button id="tab-btn-current" class="flex-1 py-2 px-4 rounded-xl font-baloo font-extrabold text-xs sm:text-sm transition-all duration-200 bg-[#1F3D20] text-[#F5F4DA] shadow-xs cursor-pointer">
            ✨ Minggu Ini
        </button>
        <button id="tab-btn-history" class="flex-1 py-2 px-4 rounded-xl font-baloo font-bold text-xs sm:text-sm transition-all duration-200 bg-transparent text-[#6B6B55] hover:text-[#1F3D20] cursor-pointer">
            📜 Riwayat Juara
        </button>
    </div>

    <!-- Tab Content 1: Current Week -->
    <div id="tab-content-current" class="space-y-6">

        <!-- Top 3 Podium -->
        <div id="leaderboard-podium">
            <!-- Rendered dynamically by leaderboard.js -->
        </div>

        <!-- Full Ranking List -->
        <div class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <h3 class="font-baloo font-bold text-base text-[#2D4A2E]">
                    Daftar Ranking Guardian
                </h3>
                <span class="text-[11px] font-baloo font-bold text-[#6B6B55]">
                    Diperbarui Real-time
                </span>
            </div>

            <div id="leaderboard-current-list" class="space-y-2">
                <!-- Rendered dynamically by leaderboard.js -->
            </div>
        </div>

    </div>

    <!-- Tab Content 2: History (Hidden by default) -->
    <div id="tab-content-history" class="hidden space-y-4">
        <div class="flex items-center justify-between px-1">
            <h3 class="font-baloo font-bold text-base text-[#2D4A2E]">
                Snapshot Juara Mingguan Lalu
            </h3>
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55]">
                Reset Otomatis Setiap Senin 00:00
            </span>
        </div>

        <div id="leaderboard-history-list">
            <!-- Rendered dynamically by leaderboard.js -->
        </div>
    </div>

    <!-- EXP Guide Card -->
    <div class="card-gg p-5 bg-[#FBFAF0] space-y-3">
        <h4 class="font-baloo font-bold text-sm text-[#2D4A2E] flex items-center gap-2">
            <span>💡</span> Cara Mengumpulkan EXP & Naik Peringkat
        </h4>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-nunito text-[#6B6B55]">
            <div class="p-3 rounded-xl bg-[#E2E1C4]/20 border border-[#1F3D20]/5 space-y-1">
                <span class="font-baloo font-bold text-[#1F3D20] block">🔍 Temukan di Peta</span>
                <p>Temukan marker tumbuhan yang diverifikasi Ranger di Peta untuk meraih <strong>+100 EXP</strong>.</p>
            </div>
            <div class="p-3 rounded-xl bg-[#E2E1C4]/20 border border-[#1F3D20]/5 space-y-1">
                <span class="font-baloo font-bold text-[#1F3D20] block">📜 Misi Harian</span>
                <p>Selesaikan 5 penemuan harian untuk mengklaim reward bonus <strong>+150 EXP</strong>.</p>
            </div>
            <div class="p-3 rounded-xl bg-[#E2E1C4]/20 border border-[#1F3D20]/5 space-y-1">
                <span class="font-baloo font-bold text-[#1F3D20] block">🌿 Kebun Virtual</span>
                <p>Tanam benih di Kebun Virtual dan siram berkala hingga siap panen <strong>+50 EXP</strong>.</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.LeaderboardManager) {
            window.LeaderboardManager.init('{{ auth()->id() }}');
        }
    });
</script>
@endpush
