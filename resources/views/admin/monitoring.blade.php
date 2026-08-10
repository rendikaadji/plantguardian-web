@extends('layouts.admin')

@section('title', __('admin.monitoring_title') . ' — Plant Guardian')
@section('header_title', __('admin.monitoring_title'))

@section('content')
<div class="space-y-6 max-w-7xl mx-auto py-2">

    <!-- Top Summary Metrics for Monitoring Page -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-[#1F3D20] space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">Total Temuan Peta</span>
            <div class="font-baloo font-extrabold text-2xl text-[#1F3D20]">{{ number_format($stats['total_sightings']) }}</div>
        </div>
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-[#27AE60] space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">Katalog Spesies</span>
            <div class="font-baloo font-extrabold text-2xl text-[#27AE60]">{{ number_format($stats['total_species_catalog']) }}</div>
        </div>
        <div class="card-gg p-4 bg-[#FBFAF0] border-l-4 border-l-[#8B6A4C] space-y-1">
            <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">Total Ranger Kontributor</span>
            <div class="font-baloo font-extrabold text-2xl text-[#8B6A4C]">{{ number_format($stats['total_rangers']) }}</div>
        </div>
    </div>

    <!-- Main Monitoring Section Card -->
    <div class="card-gg p-6 space-y-5 bg-[#FBFAF0] border border-[#1F3D20]/15 shadow-sm">
        <div class="flex items-center justify-between border-b border-[#1F3D20]/10 pb-4">
            <div>
                <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20] flex items-center gap-2">
                    <span>📍</span>
                    <span>{{ __('admin.monitoring_title') }}</span>
                </h2>
                <p class="font-nunito text-xs text-[#6B6B55]">{{ __('admin.monitoring_subtitle') }}</p>
            </div>
            <span class="px-3.5 py-1 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold text-xs shadow-2xs">
                {{ __('admin.realtime_streaming') }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($sightings as $sighting)
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
                            <img src="{{ $sighting->photo_url }}" class="w-16 h-16 object-cover rounded-xl border border-[#1F3D20]/20 shrink-0" onerror="this.onerror=null; this.src='/images/logo-plantGuardian.jpeg';" />
                        @else
                            <div class="w-16 h-16 rounded-xl bg-[#E2E1C4] flex items-center justify-center text-2xl shrink-0">🌿</div>
                        @endif
                        <div class="overflow-hidden">
                            <h4 class="font-baloo font-bold text-sm text-[#1F3D20] truncate leading-snug">
                                {{ $sighting->species ? $sighting->species->common_name : 'Tumbuhan Tanpa Nama' }}
                            </h4>
                            <p class="text-[11px] text-[#6B6B55] truncate font-nunito italic">
                                {{ $sighting->species ? $sighting->species->scientific_name : '-' }}
                            </p>
                            <p class="text-[11px] text-[#8B6A4C] font-semibold mt-1">
                                {{ __('admin.scanned_by') }}: {{ $sighting->ranger ? $sighting->ranger->name : 'System' }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-[#1F3D20]/10 flex items-center justify-between text-[10.5px] font-mono-code text-[#6B6B55]">
                        <span>📍 GPS: {{ $sighting->latitude ? number_format($sighting->latitude, 4) . ', ' . number_format($sighting->longitude, 4) : 'Tanpa Lokasi' }}</span>
                        <span>{{ $sighting->created_at ? $sighting->created_at->diffForHumans() : '' }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-[#6B6B55] font-baloo font-bold text-sm bg-white rounded-2xl border border-[#1F3D20]/10 p-6">
                    {{ __('admin.no_sightings_log') }}
                </div>
            @endforelse
        </div>

        <!-- Pagination Links -->
        <div class="pt-4">
            {{ $sightings->links() }}
        </div>
    </div>
</div>
@endsection
