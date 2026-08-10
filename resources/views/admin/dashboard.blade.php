@extends('layouts.admin')

@section('title', __('admin.dashboard_title') . ' — Plant Guardian')
@section('header_title', __('admin.dashboard_title'))

@section('content')
<div class="space-y-8 max-w-7xl mx-auto py-2">

    <!-- Top Executive Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#152B16] via-[#1F3D20] to-[#2D4A2E] text-[#F5F4DA] p-6 sm:p-8 shadow-xl border border-[#E2E1C4]/20">
        <div class="relative z-10 space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FFD700]/20 text-[#FFD700] text-xs font-baloo font-bold backdrop-blur-md border border-[#FFD700]/30">
                <span>👑</span>
                <span>{{ __('admin.system_control_title') }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="font-baloo font-extrabold text-3xl sm:text-4xl text-[#F5F4DA] tracking-tight">
                        {{ __('admin.dashboard_title') }}
                    </h1>
                    <p class="text-xs sm:text-sm text-[#F5F4DA]/80 font-nunito max-w-2xl leading-relaxed mt-1">
                        {{ __('admin.dashboard_subtitle') }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <span class="px-3.5 py-1.5 rounded-full bg-[#FBFAF0]/10 text-[#F5F4DA] font-baloo font-bold text-xs border border-[#F5F4DA]/20">
                        {{ __('admin.admin_label') }}: {{ auth()->user()->name }}
                    </span>
                </div>
            </div>
        </div>

        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-[#FFD700]/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    <!-- Alert Success Notification -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-[#27AE60]/15 border border-[#27AE60]/30 text-[#1F3D20] font-baloo font-bold text-sm flex items-center gap-3 shadow-xs">
            <span class="text-xl">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- System High-Visibility Metric Cards Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">

        <!-- Metric 1: Total Users -->
        <a href="{{ route('admin.users') }}" class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#1F3D20] hover:scale-[1.02] transition-transform">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.total_users') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#1F3D20]">{{ number_format($stats['total_users']) }}</span>
                <span class="text-xs text-[#1F3D20] font-bold">👤</span>
            </div>
        </a>

        <!-- Metric 2: Total Viewers -->
        <a href="{{ route('admin.users') }}" class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#4C8C4A] hover:scale-[1.02] transition-transform">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.viewers') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#4C8C4A]">{{ number_format($stats['total_viewers']) }}</span>
                <span class="text-xs text-[#4C8C4A] font-bold">🎒</span>
            </div>
        </a>

        <!-- Metric 3: Total Rangers -->
        <a href="{{ route('admin.users') }}" class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#8B6A4C] hover:scale-[1.02] transition-transform">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.rangers') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#8B6A4C]">{{ number_format($stats['total_rangers']) }}</span>
                <span class="text-xs text-[#8B6A4C] font-bold">🌿</span>
            </div>
        </a>

        <!-- Metric 4: Map Sightings -->
        <a href="{{ route('admin.monitoring') }}" class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#D96C63] hover:scale-[1.02] transition-transform">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.sightings') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#D96C63]">{{ number_format($stats['total_sightings']) }}</span>
                <span class="text-xs text-[#D96C63] font-bold">📍</span>
            </div>
        </a>

        <!-- Metric 5: Pending Sighting Reports -->
        <a href="{{ route('admin.reports') }}" class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#C0392B] hover:scale-[1.02] transition-transform">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.sighting_reports') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#C0392B]">{{ number_format($stats['pending_reports']) }}</span>
                <span class="text-[10px] font-baloo font-extrabold px-1.5 py-0.5 rounded-full bg-[#C0392B]/15 text-[#C0392B]">
                    {{ __('admin.pending') }}
                </span>
            </div>
        </a>

        <!-- Metric 6: Total Species Catalog -->
        <div class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#27AE60]">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.species_catalog') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#27AE60]">{{ number_format($stats['total_species_catalog']) }}</span>
                <span class="text-xs text-[#27AE60] font-bold">📚</span>
            </div>
        </div>

    </div>

    <!-- Visual Data Analytics & Anomaly Detection Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Chart 1: User Distribution -->
        <div class="card-gg p-5 bg-[#FBFAF0] border border-[#1F3D20]/15 space-y-3 shadow-sm">
            <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-2.5">
                <h3 class="font-baloo font-bold text-sm text-[#1F3D20] flex items-center gap-1.5">
                    <span>📊</span> <span>Distribusi Peran Pengguna</span>
                </h3>
                <span class="text-[10px] font-mono-code font-bold text-[#6B6B55]">Real-time</span>
            </div>
            <div class="h-48 relative">
                <canvas id="userRoleChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Catalog vs Sightings Summary -->
        <div class="card-gg p-5 bg-[#FBFAF0] border border-[#1F3D20]/15 space-y-3 shadow-sm">
            <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-2.5">
                <h3 class="font-baloo font-bold text-sm text-[#1F3D20] flex items-center gap-1.5">
                    <span>📈</span> <span>Ringkasan Katalog & Spesimen</span>
                </h3>
                <span class="text-[10px] font-mono-code font-bold text-[#6B6B55]">Flora Data</span>
            </div>
            <div class="h-48 relative">
                <canvas id="sightingsChart"></canvas>
            </div>
        </div>

        <!-- Card 3: Anomaly & System Health Hub -->
        <div class="card-gg p-5 bg-[#FBFAF0] border border-[#1F3D20]/15 space-y-3 flex flex-col justify-between shadow-sm">
            <div>
                <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-2.5">
                    <h3 class="font-baloo font-bold text-sm text-[#1F3D20] flex items-center gap-1.5">
                        <span>⚠️</span> <span>Deteksi Anomali & Isu Sistem</span>
                    </h3>
                    <span class="px-2 py-0.5 rounded-full bg-[#27AE60]/15 text-[#27AE60] font-baloo font-extrabold text-[10px]">
                        100% Operasional
                    </span>
                </div>

                <div class="space-y-2.5 mt-3">
                    <!-- Issue 1: Pending Reports -->
                    <a href="{{ route('admin.reports') }}" class="p-2.5 rounded-xl border border-red-200 bg-red-50/60 flex items-center justify-between hover:bg-red-100/60 transition-colors group">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">🚩</span>
                            <div>
                                <div class="font-baloo font-bold text-xs text-red-800">Laporan Temuan Peta</div>
                                <div class="text-[10px] text-red-600 font-nunito">Membutuhkan peninjauan admin</div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-red-600 text-white font-baloo font-extrabold text-xs shadow-2xs group-hover:scale-105 transition-transform">
                            {{ $stats['pending_reports'] }} Pending
                        </span>
                    </a>

                    <!-- Metric: Total System EXP -->
                    <div class="p-2.5 rounded-xl border border-[#1F3D20]/10 bg-white flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">⭐</span>
                            <div>
                                <div class="font-baloo font-bold text-xs text-[#1F3D20]">Total EXP Terbit</div>
                                <div class="text-[10px] text-[#6B6B55] font-nunito">Akumulasi seluruh pemain</div>
                            </div>
                        </div>
                        <span class="font-baloo font-extrabold text-sm text-[#7D5BA6]">
                            {{ number_format($stats['total_exp_issued']) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="pt-3 border-t border-[#1F3D20]/10 flex items-center gap-2">
                <a href="{{ route('admin.reports') }}" class="flex-1 py-2 rounded-xl bg-[#C0392B] text-white font-baloo font-bold text-xs text-center hover:bg-red-700 transition-colors shadow-2xs">
                    🚩 Moderasi Laporan
                </a>
                <a href="{{ route('admin.users') }}" class="flex-1 py-2 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs text-center hover:bg-[#2D4A2E] transition-colors shadow-2xs">
                    👥 Manajemen User
                </a>
            </div>
        </div>

    </div>

    <!-- Section Preview: Recent Sighting Activity Feed -->
    <div class="card-gg p-6 space-y-5 bg-[#FBFAF0] border border-[#1F3D20]/15 shadow-sm">
        <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-4">
            <div>
                <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20] flex items-center gap-2">
                    <span>📍</span>
                    <span>Ringkasan Temuan Spesies Terbaru</span>
                </h2>
                <p class="font-nunito text-xs text-[#6B6B55]">Aktivitas pemindaian flora terbaru dari seluruh Ranger di lapangan.</p>
            </div>
            <a href="{{ route('admin.monitoring') }}" class="px-3.5 py-1.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors shadow-2xs">
                Lihat Selengkapnya &rarr;
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($recentSightings as $sighting)
                <div class="card-gg p-4 space-y-3 bg-white border border-[#1F3D20]/10 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-baloo font-extrabold px-2.5 py-0.5 rounded-full bg-[#1F3D20] text-[#F5F4DA]">
                            {{ $sighting->species ? $sighting->species->species_code : 'SPESIES' }}
                        </span>
                        <span class="text-[10px] font-mono-code font-bold text-[#6B6B55]">
                            #{{ $sighting->id }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($sighting->photo_url)
                            <img src="{{ $sighting->photo_url }}" class="w-14 h-14 object-cover rounded-xl border border-[#1F3D20]/20 shrink-0" onerror="this.onerror=null; this.src='/images/logo-plantGuardian.jpeg';" />
                        @else
                            <div class="w-14 h-14 rounded-xl bg-[#E2E1C4] flex items-center justify-center text-xl shrink-0">🌿</div>
                        @endif
                        <div class="overflow-hidden">
                            <h4 class="font-baloo font-bold text-sm text-[#1F3D20] truncate">
                                {{ $sighting->species ? $sighting->species->common_name : 'Tumbuhan Tanpa Nama' }}
                            </h4>
                            <p class="text-[11px] text-[#6B6B55] truncate font-nunito italic">
                                {{ $sighting->species ? $sighting->species->scientific_name : '-' }}
                            </p>
                            <p class="text-[10px] text-[#8B6A4C] font-semibold mt-0.5">
                                Dipindai: {{ $sighting->ranger ? $sighting->ranger->name : 'System' }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[#1F3D20]/10 flex items-center justify-between text-[10px] font-mono-code text-[#6B6B55]">
                        <span>GPS: {{ $sighting->latitude ? number_format($sighting->latitude, 4) . ', ' . number_format($sighting->longitude, 4) : 'Tanpa Lokasi' }}</span>
                        <span>{{ $sighting->created_at ? $sighting->created_at->diffForHumans() : '' }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8 text-[#6B6B55] font-baloo font-bold text-sm bg-white rounded-2xl border border-[#1F3D20]/10 p-4">
                    Belum ada log temuan spesies tercatat.
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Chart === 'undefined') return;

        // Chart 1: User Role Distribution (Doughnut Chart)
        const ctxRole = document.getElementById('userRoleChart');
        if (ctxRole) {
            new Chart(ctxRole, {
                type: 'doughnut',
                data: {
                    labels: ['Viewer', 'Ranger', 'Admin'],
                    datasets: [{
                        data: [{{ $stats['total_viewers'] }}, {{ $stats['total_rangers'] }}, {{ $stats['total_admins'] }}],
                        backgroundColor: ['#4C8C4A', '#8B6A4C', '#FFD700'],
                        borderWidth: 3,
                        borderColor: '#FBFAF0'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { family: 'Baloo 2', size: 11, weight: 'bold' } }
                        }
                    }
                }
            });
        }

        // Chart 2: Sightings vs Species Summary (Bar Chart)
        const ctxSightings = document.getElementById('sightingsChart');
        if (ctxSightings) {
            new Chart(ctxSightings, {
                type: 'bar',
                data: {
                    labels: ['Katalog Spesies', 'Temuan Peta'],
                    datasets: [{
                        label: 'Jumlah Item',
                        data: [{{ $stats['total_species_catalog'] }}, {{ $stats['total_sightings'] }}],
                        backgroundColor: ['#27AE60', '#D96C63'],
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { family: 'Nunito', size: 10 } }
                        },
                        x: {
                            ticks: { font: { family: 'Baloo 2', size: 11, weight: 'bold' } }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
