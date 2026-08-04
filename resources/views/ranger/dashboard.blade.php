@extends('layouts.app')

@section('title', 'Meja Arsip & Kurasi — Ranger PlantGuardian')

@section('content')
<div class="space-y-8">
    <!-- Header Meja Arsip Ranger -->
    <div class="border-b border-[#5C574C]/30 pb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="font-mono-code text-xs text-[#9C6644] font-bold" style="font-family: 'IBM Plex Mono', monospace;">ARKIV RANGER SYSTEM v2.4</span>
                <span class="text-xs text-[#5C574C]">•</span>
                <span class="font-mono-code text-xs text-[#2F4A3C] font-bold" style="font-family: 'IBM Plex Mono', monospace;">DESK KURASI LAPANGAN</span>
            </div>
            <h1 class="font-serif-headline text-3xl font-bold" style="font-family: 'Fraunces', Georgia, serif; color: #2F4A3C !important;">
                Meja Arsip Ranger & Manajemen Spesies
            </h1>
            <p class="text-xs text-[#5C574C] max-w-2xl leading-relaxed mt-1">
                Pusat data terpusat untuk memperbarui katalog spesies tumbuhan, meninjau hasil temuan foto Viewer, serta mengelola antrean verifikasi.
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('ranger.species.create') }}" class="px-4 py-2 bg-[#2F4A3C] text-[#EDE6D3] font-mono-code text-xs font-bold uppercase tracking-wider rounded-xs hover:bg-[#2A2823] transition-colors flex items-center gap-2 shadow-xs" style="font-family: 'IBM Plex Mono', monospace;">
                <span>📷</span>
                <span>INPUT SPESIES BARU</span>
            </a>
        </div>
    </div>

    <!-- Section: 3 Laci Arsip Utama Navigasi Ranger -->
    <div class="space-y-4">
        <div class="flex items-center justify-between border-b border-[#5C574C]/30 pb-3">
            <h2 class="font-serif-headline text-xl font-bold flex items-center gap-3" style="font-family: 'Fraunces', Georgia, serif; color: #2F4A3C !important;">
                <span class="font-mono-code text-sm text-[#5C574C]" style="font-family: 'IBM Plex Mono', monospace;">§ 01</span>
                Laci Arsip Navigasi Ranger
            </h2>
            <span class="font-mono-code text-xs text-[#5C574C]" style="font-family: 'IBM Plex Mono', monospace;">3 MODUL TERSEDIA</span>
        </div>

        <!-- Grid 3 Kartu Navigasi Bergaya Laci Arsip -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Kartu Laci 1: Katalog Spesies Tumbuhan -->
            <div class="group border-2 border-[#5C574C]/40 hover:border-[#2F4A3C] transition-all p-6 rounded-xs relative flex flex-col justify-between shadow-xs" style="background-color: #EDE6D3 !important;">
                <!-- Corner Specimen Monospace Tag -->
                <div class="flex justify-between items-start mb-4">
                    <span class="font-mono-code text-xs font-bold px-2 py-0.5 border border-[#5C574C]/30" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important; color: #2F4A3C !important;">
                        DRAWER-01
                    </span>
                    <span id="count-species" class="font-mono-code text-xs font-semibold px-2 py-0.5 border border-[#9C6644]/40 text-[#9C6644]" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important;">
                        Memuat...
                    </span>
                </div>

                <div class="space-y-3 mb-6">
                    <h3 class="font-serif-headline text-lg font-bold group-hover:text-[#2F4A3C] transition-colors" style="font-family: 'Fraunces', Georgia, serif; color: #2A2823 !important;">
                        Katalog Spesies Tumbuhan
                    </h3>
                    <p class="text-xs text-[#5C574C] leading-relaxed">
                        Kelola data induk spesies tumbuhan, nama ilmiah, status konservasi, deskripsi edukasi, dan petunjuk perawatan.
                    </p>
                </div>

                <a href="{{ route('ranger.species.index') }}" class="w-full py-2.5 px-4 font-mono-code text-xs font-bold text-center uppercase tracking-wider border border-[#2F4A3C] transition-all flex items-center justify-center gap-2 group-hover:bg-[#2F4A3C] group-hover:text-[#EDE6D3]" style="font-family: 'IBM Plex Mono', monospace; color: #2F4A3C !important;">
                    <span>BUKA KATALOG SPESIES</span>
                    <span>→</span>
                </a>
            </div>

            <!-- Kartu Laci 2: Verifikasi & Moderasi Temuan -->
            <div class="group border-2 border-[#5C574C]/40 hover:border-[#2F4A3C] transition-all p-6 rounded-xs relative flex flex-col justify-between shadow-xs" style="background-color: #EDE6D3 !important;">
                <!-- Corner Specimen Monospace Tag -->
                <div class="flex justify-between items-start mb-4">
                    <span class="font-mono-code text-xs font-bold px-2 py-0.5 border border-[#5C574C]/30" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important; color: #2F4A3C !important;">
                        DRAWER-02
                    </span>
                    <span id="count-verifications" class="font-mono-code text-xs font-semibold px-2 py-0.5 border border-[#9C6644]/40 text-[#9C6644]" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important;">
                        Memuat...
                    </span>
                </div>

                <div class="space-y-3 mb-6">
                    <h3 class="font-serif-headline text-lg font-bold group-hover:text-[#2F4A3C] transition-colors" style="font-family: 'Fraunces', Georgia, serif; color: #2A2823 !important;">
                        Moderasi & Verifikasi Temuan
                    </h3>
                    <p class="text-xs text-[#5C574C] leading-relaxed">
                        Tinjau dan verifikasi foto temuan pemindaian tumbuhan yang diunggah oleh pengguna dan Ranger di lapangan.
                    </p>
                </div>

                <a href="{{ route('ranger.verifications.index') }}" class="w-full py-2.5 px-4 font-mono-code text-xs font-bold text-center uppercase tracking-wider border border-[#2F4A3C] transition-all flex items-center justify-center gap-2 group-hover:bg-[#2F4A3C] group-hover:text-[#EDE6D3]" style="font-family: 'IBM Plex Mono', monospace; color: #2F4A3C !important;">
                    <span>BUKA ANTREAN VERIFIKASI</span>
                    <span>→</span>
                </a>
            </div>

            <!-- Kartu Laci 3: Edit & Kelola Temuan Lapangan -->
            <div class="group border-2 border-[#5C574C]/40 hover:border-[#2F4A3C] transition-all p-6 rounded-xs relative flex flex-col justify-between shadow-xs" style="background-color: #EDE6D3 !important;">
                <!-- Corner Specimen Monospace Tag -->
                <div class="flex justify-between items-start mb-4">
                    <span class="font-mono-code text-xs font-bold px-2 py-0.5 border border-[#5C574C]/30" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important; color: #2F4A3C !important;">
                        DRAWER-03
                    </span>
                    <span id="count-sightings" class="font-mono-code text-xs font-semibold px-2 py-0.5 border border-[#9C6644]/40 text-[#9C6644]" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important;">
                        Memuat...
                    </span>
                </div>

                <div class="space-y-3 mb-6">
                    <h3 class="font-serif-headline text-lg font-bold group-hover:text-[#2F4A3C] transition-colors" style="font-family: 'Fraunces', Georgia, serif; color: #2A2823 !important;">
                        Edit Temuan Tumbuhan Lapangan
                    </h3>
                    <p class="text-xs text-[#5C574C] leading-relaxed">
                        Lihat, perbaiki spesies, atau hapus data temuan foto live tumbuhan jika terjadi kesalahan identifikasi di lapangan.
                    </p>
                </div>

                <a href="{{ route('ranger.sightings.index') }}" class="w-full py-2.5 px-4 font-mono-code text-xs font-bold text-center uppercase tracking-wider border border-[#2F4A3C] transition-all flex items-center justify-center gap-2 group-hover:bg-[#2F4A3C] group-hover:text-[#EDE6D3]" style="font-family: 'IBM Plex Mono', monospace; color: #2F4A3C !important;">
                    <span>KELOLA TEMUAN LAPANGAN</span>
                    <span>→</span>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    import RangerHome from '/resources/js/modules/ranger-home.js';
    document.addEventListener('DOMContentLoaded', () => {
        const rangerHome = new RangerHome();
        rangerHome.loadDashboardStats();
    });
</script>
@endpush
