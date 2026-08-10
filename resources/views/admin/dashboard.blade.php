@extends('layouts.app')

@section('title', __('admin.dashboard_title') . ' — Plant Guardian')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto py-2">

    <!-- Top Header Banner (Admin Dedicated Control Panel) -->
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
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 sm:gap-4">

        <!-- Metric 1: Total Users -->
        <div class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#1F3D20]">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.total_users') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#1F3D20]">{{ number_format($stats['total_users']) }}</span>
                <span class="text-xs text-[#1F3D20] font-bold">👤</span>
            </div>
        </div>

        <!-- Metric 2: Total Viewers -->
        <div class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#4C8C4A]">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.viewers') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#4C8C4A]">{{ number_format($stats['total_viewers']) }}</span>
                <span class="text-xs text-[#4C8C4A] font-bold">🎒</span>
            </div>
        </div>

        <!-- Metric 3: Total Rangers -->
        <div class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#8B6A4C]">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.rangers') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#8B6A4C]">{{ number_format($stats['total_rangers']) }}</span>
                <span class="text-xs text-[#8B6A4C] font-bold">🌿</span>
            </div>
        </div>

        <!-- Metric 4: Sightings & Verifications -->
        <div class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#D96C63]">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.sightings') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#D96C63]">{{ number_format($stats['total_sightings']) }}</span>
                <span class="text-[10px] font-baloo font-extrabold px-1.5 py-0.5 rounded-full bg-[#D96C63]/15 text-[#D96C63]">
                    {{ $stats['pending_verifications'] }} {{ __('admin.verifications') }}
                </span>
            </div>
        </div>

        <!-- Metric 5: Pending Sighting Reports -->
        <div class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#C0392B]">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.sighting_reports') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#C0392B]">{{ number_format($stats['pending_reports']) }}</span>
                <span class="text-[10px] font-baloo font-extrabold px-1.5 py-0.5 rounded-full bg-[#C0392B]/15 text-[#C0392B]">
                    {{ __('admin.pending') }}
                </span>
            </div>
        </div>

        <!-- Metric 6: Total Species Catalog -->
        <div class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#27AE60]">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.species_catalog') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-2xl text-[#27AE60]">{{ number_format($stats['total_species_catalog']) }}</span>
                <span class="text-xs text-[#27AE60] font-bold">📚</span>
            </div>
        </div>

        <!-- Metric 7: Total EXP Issued -->
        <div class="card-gg p-4 flex flex-col justify-between space-y-2 bg-[#FBFAF0] border-l-4 border-l-[#7D5BA6]">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('admin.total_exp') }}</span>
            <div class="flex items-baseline justify-between">
                <span class="font-baloo font-extrabold text-xl text-[#7D5BA6]">{{ number_format($stats['total_exp_issued']) }}</span>
                <span class="text-xs text-[#7D5BA6] font-bold">⭐</span>
            </div>
        </div>

    </div>

    <!-- Section: User Control & Role Management Table -->
    <div class="card-gg p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1F3D20]/10 pb-4">
            <div>
                <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20] flex items-center gap-2">
                    <span>👥</span>
                    <span>{{ __('admin.user_management_title') }}</span>
                </h2>
                <p class="font-nunito text-xs text-[#6B6B55]">{{ __('admin.user_management_subtitle') }}</p>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="{{ __('admin.search_placeholder') }}" class="px-4 py-2 rounded-xl border border-[#1F3D20]/20 bg-white font-nunito text-xs text-[#1F3D20] focus:outline-none focus:border-[#1F3D20] w-64 shadow-xs" />
                <button type="submit" class="px-4 py-2 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer">
                    {{ __('admin.search_btn') }}
                </button>
                @if($search)
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-xl bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs hover:bg-[#1F3D20]/10">{{ __('admin.reset_btn') }}</a>
                @endif
            </form>
        </div>

        <!-- Users Table -->
        <div class="overflow-x-auto rounded-2xl border border-[#1F3D20]/10 shadow-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs tracking-wider">
                        <th class="py-3.5 px-4">{{ __('admin.col_user') }}</th>
                        <th class="py-3.5 px-4">{{ __('admin.col_current_role') }}</th>
                        <th class="py-3.5 px-4 text-center">{{ __('admin.col_level_exp') }}</th>
                        <th class="py-3.5 px-4 text-center">{{ __('admin.col_coin') }}</th>
                        <th class="py-3.5 px-4">{{ __('admin.col_joined') }}</th>
                        <th class="py-3.5 px-4 text-right">{{ __('admin.col_role_action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#1F3D20]/10 font-nunito text-xs bg-white">
                    @forelse($users as $userItem)
                        <tr class="hover:bg-[#FBFAF0] transition-colors">
                            <!-- User Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#E2E1C4] font-baloo font-extrabold text-sm text-[#1F3D20] flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($userItem->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-baloo font-bold text-sm text-[#1F3D20] block leading-snug">{{ $userItem->name }}</span>
                                        <span class="text-[11px] text-[#6B6B55] block">{{ $userItem->email }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Role Badge -->
                            <td class="py-3.5 px-4">
                                @if($userItem->role === 'admin')
                                    <span class="px-2.5 py-1 rounded-full bg-[#FFD700]/30 text-[#8B6A4C] font-baloo font-extrabold text-[11px] border border-[#FFD700] inline-flex items-center gap-1">
                                        👑 ADMIN
                                    </span>
                                @elseif($userItem->role === 'ranger')
                                    <span class="px-2.5 py-1 rounded-full bg-[#8B6A4C]/15 text-[#8B6A4C] font-baloo font-extrabold text-[11px] border border-[#8B6A4C]/30 inline-flex items-center gap-1">
                                        🌿 RANGER
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-[#4C8C4A]/15 text-[#4C8C4A] font-baloo font-extrabold text-[11px] border border-[#4C8C4A]/30 inline-flex items-center gap-1">
                                        🎒 VIEWER
                                    </span>
                                @endif
                            </td>

                            <!-- Level & EXP -->
                            <td class="py-3.5 px-4 text-center font-baloo font-bold text-[#1F3D20]">
                                <div>Lvl {{ $userItem->level }}</div>
                                <span class="text-[10px] text-[#6B6B55] font-extrabold block">{{ number_format($userItem->exp) }} EXP</span>
                            </td>

                            <!-- Coin -->
                            <td class="py-3.5 px-4 text-center font-baloo font-extrabold text-[#1F3D20]">
                                🪙 {{ number_format($userItem->coin) }} NC
                            </td>

                            <!-- Join Date -->
                            <td class="py-3.5 px-4 text-[#6B6B55] font-mono-code text-[11px]">
                                {{ $userItem->created_at ? $userItem->created_at->format('d M Y H:i') : '-' }}
                            </td>

                            <!-- Role Change Form & Detail Action -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <button type="button" onclick='openUserDetailModal(@json($userItem))' class="px-2.5 py-1 rounded-lg bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors cursor-pointer shadow-xs">
                                        {{ __('admin.detail_btn') }}
                                    </button>
                                    <form method="POST" action="{{ route('admin.users.update-role', $userItem->id) }}" class="inline-flex items-center gap-1.5">
                                        @csrf
                                        <select name="role" class="px-2 py-1 rounded-lg border border-[#1F3D20]/20 bg-[#FBFAF0] font-baloo font-bold text-xs text-[#1F3D20] focus:outline-none">
                                            <option value="viewer" {{ $userItem->role === 'viewer' ? 'selected' : '' }}>Viewer</option>
                                            <option value="ranger" {{ $userItem->role === 'ranger' ? 'selected' : '' }}>Ranger</option>
                                            <option value="admin" {{ $userItem->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-[#1F3D20] hover:bg-[#2D4A2E] text-[#F5F4DA] font-baloo font-bold text-xs transition-colors cursor-pointer shadow-xs">
                                            {{ __('admin.save_btn') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-[#6B6B55] font-baloo font-bold">
                                {{ __('admin.no_users_found') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="pt-2">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Section: Sighting Reports Moderation Queue -->
    <div class="card-gg p-6 space-y-5 border-l-4 border-l-[#C0392B]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1F3D20]/10 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20] flex items-center gap-2">
                        <span>🚩</span>
                        <span>{{ __('admin.reports_title') }}</span>
                    </h2>
                    @if(count($reports) > 0)
                        <span class="px-2.5 py-0.5 rounded-full bg-[#C0392B] text-white font-baloo font-extrabold text-xs shadow-xs animate-pulse">
                            {{ __('admin.pending_reports_badge', ['count' => count($reports)]) }}
                        </span>
                    @endif
                </div>
                <p class="font-nunito text-xs text-[#6B6B55]">{{ __('admin.reports_subtitle') }}</p>
            </div>
        </div>

        @if(count($reports) > 0)
            <div class="overflow-x-auto rounded-2xl border border-[#1F3D20]/10 shadow-xs">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs tracking-wider">
                            <th class="py-3.5 px-4">{{ __('admin.col_reported_plant') }}</th>
                            <th class="py-3.5 px-4">{{ __('admin.col_reporter') }}</th>
                            <th class="py-3.5 px-4">{{ __('admin.col_report_reason') }}</th>
                            <th class="py-3.5 px-4">{{ __('admin.col_report_notes') }}</th>
                            <th class="py-3.5 px-4">{{ __('admin.col_report_time') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('admin.col_admin_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1F3D20]/10 font-nunito text-xs bg-white">
                        @foreach($reports as $report)
                            <tr class="hover:bg-[#FBFAF0] transition-colors">
                                <!-- Reported Sighting -->
                                <td class="py-3.5 px-4">
                                    @if($report->sighting)
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $report->sighting->photo_url }}" class="w-11 h-11 object-cover rounded-xl border border-[#1F3D20]/20 shrink-0" onerror="this.onerror=null; this.src='/images/logo-plantGuardian.jpeg';" />
                                            <div>
                                                <div class="font-baloo font-bold text-sm text-[#1F3D20]">
                                                    {{ $report->sighting->species ? $report->sighting->species->common_name : 'Spesies Tumbuhan' }}
                                                </div>
                                                <div class="text-[10px] text-[#6B6B55] italic">
                                                    {{ $report->sighting->species ? $report->sighting->species->scientific_name : '-' }}
                                                </div>
                                                <div class="text-[10px] text-[#8B6A4C] font-semibold mt-0.5">
                                                    👤 Ranger: {{ $report->sighting->ranger ? $report->sighting->ranger->name : 'System' }}
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic">{{ __('admin.deleted_marker_label') }}</span>
                                    @endif
                                </td>

                                <!-- Reporter User -->
                                <td class="py-3.5 px-4">
                                    <div class="font-baloo font-bold text-xs text-[#1F3D20]">
                                        {{ $report->user ? $report->user->name : 'User' }}
                                    </div>
                                    <div class="text-[10px] text-[#6B6B55]">
                                        {{ $report->user ? $report->user->email : '-' }}
                                    </div>
                                </td>

                                <!-- Reason -->
                                <td class="py-3.5 px-4">
                                    @php
                                        $reasonBadge = match($report->reason) {
                                            'fake_specimen' => ['bg-red-100 text-red-700 border-red-300', __('map.reason_fake_specimen')],
                                            'plant_missing_or_dead' => ['bg-amber-100 text-amber-800 border-amber-300', __('map.reason_plant_missing_or_dead')],
                                            'species_mismatch_or_replaced' => ['bg-purple-100 text-purple-700 border-purple-300', __('map.reason_species_mismatch_or_replaced')],
                                            default => ['bg-gray-100 text-gray-700 border-gray-300', __('map.reason_other')],
                                        };
                                    @endphp
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10.5px] font-baloo font-bold border {{ $reasonBadge[0] }}">
                                        {{ $reasonBadge[1] }}
                                    </span>
                                </td>

                                <!-- Notes -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="text-xs text-[#2A2A22] italic leading-relaxed">
                                        {{ $report->notes ? '"' . $report->notes . '"' : '-' }}
                                    </p>
                                </td>

                                <!-- Date -->
                                <td class="py-3.5 px-4 font-mono-code text-[10.5px] text-[#6B6B55]">
                                    {{ $report->created_at ? $report->created_at->diffForHumans() : '-' }}
                                </td>

                                <!-- Admin Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Action 1: Delete Sighting Marker -->
                                        <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}" onsubmit="return confirm('{{ __('admin.delete_confirm') }}');">
                                            @csrf
                                            <input type="hidden" name="action" value="delete_sighting">
                                            <button type="submit" class="px-3 py-1.5 rounded-full bg-[#C0392B] text-white font-baloo font-bold text-xs hover:bg-red-700 transition-colors shadow-2xs cursor-pointer">
                                                {{ __('admin.action_delete_sighting') }}
                                            </button>
                                        </form>

                                        <!-- Action 2: Dismiss Report -->
                                        <form method="POST" action="{{ route('admin.reports.resolve', $report->id) }}">
                                            @csrf
                                            <input type="hidden" name="action" value="dismiss">
                                            <button type="submit" class="px-3 py-1.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs hover:bg-[#1F3D20]/15 transition-colors cursor-pointer">
                                                {{ __('admin.action_dismiss_report') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-6 text-[#6B6B55] font-baloo font-bold text-xs bg-white rounded-2xl border border-[#1F3D20]/10 p-4">
                {{ __('admin.no_pending_reports') }}
            </div>
        @endif
    </div>

    <!-- Section: Monitoring Log Activity (Recent Plant Sightings) -->
    <div class="card-gg p-6 space-y-5">
        <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-4">
            <div>
                <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20] flex items-center gap-2">
                    <span>📍</span>
                    <span>{{ __('admin.monitoring_title') }}</span>
                </h2>
                <p class="font-nunito text-xs text-[#6B6B55]">{{ __('admin.monitoring_subtitle') }}</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold text-xs">
                {{ __('admin.realtime_streaming') }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($recentSightings as $sighting)
                <div class="card-gg p-4 space-y-3 bg-[#FBFAF0] border border-[#1F3D20]/10">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-baloo font-extrabold px-2 py-0.5 rounded-full bg-[#1F3D20] text-[#F5F4DA]">
                            {{ $sighting->species ? $sighting->species->species_code : 'SPESIES' }}
                        </span>
                        @php
                            $statusLabel = match($sighting->verification_status) {
                                'verified' => __('admin.status_verified'),
                                'rejected' => __('admin.status_rejected'),
                                default => __('admin.status_pending'),
                            };
                        @endphp
                        <span class="text-[10px] font-baloo font-extrabold px-2 py-0.5 rounded-full {{ $sighting->verification_status === 'verified' ? 'bg-[#27AE60]/20 text-[#27AE60]' : ($sighting->verification_status === 'rejected' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-700') }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        @if($sighting->photo_url)
                            <img src="{{ $sighting->photo_url }}" class="w-14 h-14 object-cover rounded-xl border border-[#1F3D20]/20 shrink-0" />
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
                            <p class="text-[10px] text-[#6B6B55] mt-0.5">
                                {{ __('admin.scanned_by') }}: {{ $sighting->ranger ? $sighting->ranger->name : 'System' }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[#1F3D20]/10 flex items-center justify-between text-[10px] font-mono-code text-[#6B6B55]">
                        <span>GPS: {{ $sighting->latitude ? number_format($sighting->latitude, 4) . ', ' . number_format($sighting->longitude, 4) : 'Tanpa Lokasi' }}</span>
                        <span>{{ $sighting->created_at ? $sighting->created_at->diffForHumans() : '' }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-8 text-[#6B6B55] font-baloo font-bold text-sm">
                    {{ __('admin.no_sightings_log') }}
                </div>
            @endforelse
        </div>
    </div>

    <!-- User Detail Modal Component -->
    <div id="user-detail-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs hidden">
        <div class="card-gg p-6 w-full max-w-lg space-y-5 bg-[#FBFAF0] border-2 border-[#1F3D20]/20 shadow-2xl relative">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-3">
                <div class="flex items-center gap-3">
                    <div id="modal-avatar" class="w-12 h-12 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-extrabold text-lg flex items-center justify-center shrink-0 shadow-xs">
                        US
                    </div>
                    <div>
                        <h3 id="modal-user-name" class="font-baloo font-extrabold text-lg text-[#1F3D20] leading-none">Nama Pengguna</h3>
                        <span id="modal-user-email" class="text-xs text-[#6B6B55] font-nunito">user@example.com</span>
                    </div>
                </div>
                <button type="button" onclick="closeUserDetailModal()" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold flex items-center justify-center hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors cursor-pointer">
                    ✕
                </button>
            </div>

            <!-- Modal Content Body -->
            <div class="space-y-4 font-nunito text-xs text-[#2A2A22]">
                <!-- Role Tag -->
                <div class="flex items-center justify-between p-3 rounded-xl bg-[#E2E1C4]/40 border border-[#1F3D20]/10">
                    <span class="font-baloo font-bold text-[#6B6B55]">Role Pengguna:</span>
                    <span id="modal-user-role-badge" class="px-3 py-1 rounded-full font-baloo font-extrabold text-xs">
                        ROLE
                    </span>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl bg-white border border-[#1F3D20]/10 space-y-1">
                        <span class="text-[10px] font-baloo font-bold text-[#6B6B55] uppercase block">Level & EXP</span>
                        <div id="modal-user-level" class="font-baloo font-extrabold text-base text-[#1F3D20]">Lvl 1</div>
                        <div id="modal-user-exp" class="text-[11px] text-[#27AE60] font-bold">0 EXP</div>
                    </div>

                    <div class="p-3 rounded-xl bg-white border border-[#1F3D20]/10 space-y-1">
                        <span class="text-[10px] font-baloo font-bold text-[#6B6B55] uppercase block">Saldo Digital Coin</span>
                        <div id="modal-user-coin" class="font-baloo font-extrabold text-base text-[#8B6A4C]">🪙 0 NC</div>
                    </div>
                </div>

                <!-- Detail List -->
                <div class="space-y-2 p-3 rounded-xl bg-white border border-[#1F3D20]/10">
                    <div class="flex justify-between py-1 border-b border-[#1F3D20]/5">
                        <span class="text-[#6B6B55]">ID Pengguna:</span>
                        <span id="modal-user-id" class="font-mono-code font-bold text-[#1F3D20]">#1</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[#1F3D20]/5">
                        <span class="text-[#6B6B55]">Terdaftar Pada:</span>
                        <span id="modal-user-created" class="font-bold text-[#1F3D20]">-</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-[#6B6B55]">Preferensi Bahasa:</span>
                        <span id="modal-user-locale" class="font-bold uppercase text-[#1F3D20]">ID</span>
                    </div>
                </div>

                <!-- Plant Activity History Section (Viewer vs Ranger) -->
                <div class="space-y-2 p-3 rounded-xl bg-white border border-[#1F3D20]/10">
                    <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-2">
                        <h4 id="modal-activity-title" class="font-baloo font-bold text-xs sm:text-sm text-[#1F3D20]">
                            Daftar Tanaman Terkait
                        </h4>
                        <span id="modal-activity-count" class="px-2 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold text-[10px]">
                            0 Data
                        </span>
                    </div>

                    <!-- Scrollable Plant List Container -->
                    <div id="modal-activity-list" class="max-h-52 overflow-y-auto space-y-2 pr-1">
                        <div class="text-center py-4 text-[#6B6B55] font-nunito italic">
                            Memuat data tanaman...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer Action -->
            <div class="pt-2 border-t border-[#1F3D20]/10 flex justify-end">
                <button type="button" onclick="closeUserDetailModal()" class="px-5 py-2 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function openUserDetailModal(user) {
            document.getElementById('modal-avatar').textContent = user.name ? user.name.substring(0, 2).toUpperCase() : 'US';
            document.getElementById('modal-user-name').textContent = user.name || '-';
            document.getElementById('modal-user-email').textContent = user.email || '-';
            document.getElementById('modal-user-id').textContent = '#' + user.id;
            document.getElementById('modal-user-level').textContent = 'Lvl ' + (user.level || 1);
            document.getElementById('modal-user-exp').textContent = (user.exp || 0).toLocaleString() + ' EXP';
            document.getElementById('modal-user-coin').textContent = '🪙 ' + (user.coin || 0).toLocaleString() + ' NC';
            document.getElementById('modal-user-created').textContent = user.created_at ? new Date(user.created_at).toLocaleString('id-ID') : '-';
            document.getElementById('modal-user-locale').textContent = (user.locale || 'id').toUpperCase();

            const badge = document.getElementById('modal-user-role-badge');
            if (user.role === 'admin') {
                badge.className = 'px-3 py-1 rounded-full bg-[#FFD700]/30 text-[#8B6A4C] font-baloo font-extrabold text-xs border border-[#FFD700]';
                badge.textContent = '👑 ADMIN';
            } else if (user.role === 'ranger') {
                badge.className = 'px-3 py-1 rounded-full bg-[#8B6A4C]/15 text-[#8B6A4C] font-baloo font-extrabold text-xs border border-[#8B6A4C]/30';
                badge.textContent = '🌿 RANGER';
            } else {
                badge.className = 'px-3 py-1 rounded-full bg-[#4C8C4A]/15 text-[#4C8C4A] font-baloo font-extrabold text-xs border border-[#4C8C4A]/30';
                badge.textContent = '🎒 VIEWER';
            }

            document.getElementById('user-detail-modal').classList.remove('hidden');

            // Reset & fetch activity list
            const activityTitle = document.getElementById('modal-activity-title');
            const activityCount = document.getElementById('modal-activity-count');
            const activityList = document.getElementById('modal-activity-list');

            activityList.innerHTML = '<div class="text-center py-4 text-[#6B6B55] font-nunito italic">Memuat data tanaman...</div>';

            fetch(`/admin/users/${user.id}/details`)
                .then(res => res.json())
                .then(data => {
                    if (!data.success || !data.activity) return;

                    const act = data.activity;
                    activityCount.textContent = act.total_count + ' Data';

                    if (act.type === 'ranger') {
                        activityTitle.textContent = '📸 Tanaman Difoto & Dipindai Ranger';
                    } else {
                        activityTitle.textContent = '🌿 Tanaman Ditemukan oleh Viewer';
                    }

                    if (!act.items || act.items.length === 0) {
                        activityList.innerHTML = `<div class="text-center py-4 text-[#6B6B55] font-nunito italic">${act.type === 'ranger' ? 'Belum ada tanaman yang difoto oleh Ranger ini.' : 'Belum ada tanaman yang ditemukan oleh Viewer ini.'}</div>`;
                        return;
                    }

                    let html = '';
                    act.items.forEach(item => {
                        html += `
                            <div class="p-2.5 rounded-lg bg-[#FBFAF0] border border-[#1F3D20]/10 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 overflow-hidden">
                                    ${item.photo_url ? `<img src="${item.photo_url}" class="w-10 h-10 object-cover rounded-lg border border-[#1F3D20]/20 shrink-0">` : `<div class="w-10 h-10 rounded-lg bg-[#E2E1C4] flex items-center justify-center text-sm shrink-0">🌿</div>`}
                                    <div class="overflow-hidden">
                                        <div class="font-baloo font-bold text-xs text-[#1F3D20] truncate">${item.species_name}</div>
                                        ${item.scientific_name ? `<div class="text-[10px] text-[#6B6B55] italic truncate">${item.scientific_name}</div>` : ''}
                                        <div class="text-[10px] text-[#27AE60] font-mono-code font-bold truncate">📍 ${item.location_text}</div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    ${item.status ? `<span class="text-[9px] font-baloo font-extrabold px-1.5 py-0.5 rounded-full ${item.status === 'verified' ? 'bg-[#27AE60]/20 text-[#27AE60]' : 'bg-amber-100 text-amber-700'} block uppercase mb-1">${item.status}</span>` : ''}
                                    <span class="text-[9px] text-[#6B6B55] block font-mono-code">${item.created_at || item.discovered_at}</span>
                                </div>
                            </div>
                        `;
                    });
                    activityList.innerHTML = html;
                })
                .catch(err => {
                    activityList.innerHTML = '<div class="text-center py-4 text-red-500 font-nunito">Gagal memuat data tanaman.</div>';
                });
        }

        function closeUserDetailModal() {
            document.getElementById('user-detail-modal').classList.add('hidden');
        }
    </script>
</div>
@endsection
