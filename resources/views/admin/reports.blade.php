@extends('layouts.admin')

@section('title', __('admin.reports_title') . ' — Plant Guardian')
@section('header_title', __('admin.reports_title'))

@section('content')
<div class="space-y-6 max-w-7xl mx-auto py-2">

    <!-- Alert Success Notification -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-[#27AE60]/15 border border-[#27AE60]/30 text-[#1F3D20] font-baloo font-bold text-sm flex items-center gap-3 shadow-xs">
            <span class="text-xl">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Top Summary Metrics for Reports Page -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-[#C0392B] space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">Laporan Pending</span>
            <div class="font-baloo font-extrabold text-2xl text-[#C0392B]">{{ number_format($stats['pending_reports']) }}</div>
        </div>
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-red-500 space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">Tumbuhan Palsu / Hoaks</span>
            <div class="font-baloo font-extrabold text-2xl text-red-600">{{ number_format($stats['reports_fake']) }}</div>
        </div>
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-amber-500 space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">Mati / Ditebang</span>
            <div class="font-baloo font-extrabold text-2xl text-amber-700">{{ number_format($stats['reports_missing']) }}</div>
        </div>
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-purple-500 space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">Diganti / Berbeda</span>
            <div class="font-baloo font-extrabold text-2xl text-purple-700">{{ number_format($stats['reports_replaced']) }}</div>
        </div>
    </div>

    <!-- Main Reports Moderation Queue Table Card -->
    <div class="card-gg p-6 space-y-5 bg-[#FBFAF0] border border-[#1F3D20]/15 shadow-sm border-l-4 border-l-[#C0392B]">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#1F3D20]/10 pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20] flex items-center gap-2">
                        <span>🚩</span>
                        <span>{{ __('admin.reports_title') }}</span>
                    </h2>
                    @if($stats['pending_reports'] > 0)
                        <span class="px-2.5 py-0.5 rounded-full bg-[#C0392B] text-white font-baloo font-extrabold text-xs shadow-xs animate-pulse">
                            {{ __('admin.pending_reports_badge', ['count' => $stats['pending_reports']]) }}
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
                            <th class="py-3.5 px-4">Status Laporan</th>
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

                                <!-- Status Report -->
                                <td class="py-3.5 px-4">
                                    @if($report->status === 'pending')
                                        <span class="px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 font-baloo font-bold text-[10px] uppercase">
                                            Pending Review
                                        </span>
                                    @elseif($report->status === 'resolved_deleted')
                                        <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 font-baloo font-bold text-[10px] uppercase">
                                            Marker Dihapus
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 font-baloo font-bold text-[10px] uppercase">
                                            Diabaikan
                                        </span>
                                    @endif
                                </td>

                                <!-- Date -->
                                <td class="py-3.5 px-4 font-mono-code text-[10.5px] text-[#6B6B55]">
                                    {{ $report->created_at ? $report->created_at->diffForHumans() : '-' }}
                                </td>

                                <!-- Admin Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    @if($report->status === 'pending')
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
                                    @else
                                        <span class="text-xs text-gray-400 italic">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="pt-2">
                {{ $reports->links() }}
            </div>
        @else
            <div class="text-center py-8 text-[#6B6B55] font-baloo font-bold text-xs bg-white rounded-2xl border border-[#1F3D20]/10 p-4">
                {{ __('admin.no_pending_reports') }}
            </div>
        @endif
    </div>
</div>
@endsection
