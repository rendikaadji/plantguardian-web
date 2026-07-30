@extends('layouts.app')

@section('title', 'Dashboard Ranger — Meja Arsip PlantGuardian')

@section('content')
<div class="space-y-8">
    <!-- Ranger Header / Field Journal Archive Desk Header -->
    <div class="p-6 md:p-8 rounded-xs border-2 border-[#5C574C]/30 relative overflow-hidden shadow-xs" style="background-color: #E3DABF !important;">
        <!-- Background Perforated Border Accent -->
        <div class="absolute inset-2 border border-dashed border-[#5C574C]/20 pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <!-- Badge Monospace Ranger Stamp -->
                    <span class="font-mono-code text-xs font-bold px-3 py-1 rounded-xs uppercase tracking-widest border border-[#9C6644]" style="font-family: 'IBM Plex Mono', monospace; background-color: #9C6644 !important; color: #EDE6D3 !important;">
                        [ RANGER - DATA STEWARD ]
                    </span>
                    <span class="font-mono-code text-xs text-[#5C574C]" style="font-family: 'IBM Plex Mono', monospace;">
                        ID: {{ sprintf('RNG-%04d', auth()->id()) }}
                    </span>
                </div>
                <h1 class="font-serif-headline text-2xl md:text-3xl font-bold tracking-tight" style="font-family: 'Fraunces', Georgia, serif; color: #2F4A3C !important;">
                    Meja Arsip & Kurasi Spesimen
                </h1>
                <p class="text-sm text-[#5C574C] max-w-2xl">
                    Selamat datang di panel administrasi Ranger. Kelola data master katalog spesies tumbuhan, bahan pembuatan kompos, serta moderasi bukti temuan dari pengguna umum.
                </p>
            </div>

            <!-- Quick Action Timestamp Stamp -->
            <div class="flex flex-col items-end justify-center p-3 border border-[#5C574C]/30 rounded-xs" style="background-color: #EDE6D3 !important;">
                <span class="font-mono-code text-[10px] uppercase text-[#5C574C]" style="font-family: 'IBM Plex Mono', monospace;">SISTEM LOGGED IN</span>
                <span class="font-mono-code text-xs font-bold text-[#2A2823]" style="font-family: 'IBM Plex Mono', monospace;">{{ auth()->user()->name }}</span>
            </div>
        </div>
    </div>

    <!-- Section Title: Laci Arsip Administrasi -->
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
                    Kelola data induk spesies tumbuhan, nama ilmiah, status konservasi, deskripsi edukasi, dan foto referensi AI.
                </p>
            </div>

            <a href="{{ route('ranger.species.index') }}" class="w-full py-2.5 px-4 font-mono-code text-xs font-bold text-center uppercase tracking-wider border border-[#2F4A3C] transition-all flex items-center justify-center gap-2 group-hover:bg-[#2F4A3C] group-hover:text-[#EDE6D3]" style="font-family: 'IBM Plex Mono', monospace; color: #2F4A3C !important;">
                <span>BUKA KATALOG SPESIES</span>
                <span>→</span>
            </a>
        </div>

        <!-- Kartu Laci 2: Katalog Bahan Kompos -->
        <div class="group border-2 border-[#5C574C]/40 hover:border-[#2F4A3C] transition-all p-6 rounded-xs relative flex flex-col justify-between shadow-xs" style="background-color: #EDE6D3 !important;">
            <!-- Corner Specimen Monospace Tag -->
            <div class="flex justify-between items-start mb-4">
                <span class="font-mono-code text-xs font-bold px-2 py-0.5 border border-[#5C574C]/30" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important; color: #2F4A3C !important;">
                    DRAWER-02
                </span>
                <span id="count-compost" class="font-mono-code text-xs font-semibold px-2 py-0.5 border border-[#9C6644]/40 text-[#9C6644]" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important;">
                    Memuat...
                </span>
            </div>

            <div class="space-y-3 mb-6">
                <h3 class="font-serif-headline text-lg font-bold group-hover:text-[#2F4A3C] transition-colors" style="font-family: 'Fraunces', Georgia, serif; color: #2A2823 !important;">
                    Katalog Bahan Kompos
                </h3>
                <p class="text-xs text-[#5C574C] leading-relaxed">
                    Tambah dan perbarui daftar bahan organik yang cocok untuk pembuatan kompos beserta panduan instruksi langkahnya.
                </p>
            </div>

            <a href="{{ route('ranger.compost-materials.index') }}" class="w-full py-2.5 px-4 font-mono-code text-xs font-bold text-center uppercase tracking-wider border border-[#2F4A3C] transition-all flex items-center justify-center gap-2 group-hover:bg-[#2F4A3C] group-hover:text-[#EDE6D3]" style="font-family: 'IBM Plex Mono', monospace; color: #2F4A3C !important;">
                <span>BUKA KATALOG BAHAN</span>
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
