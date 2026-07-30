@extends('layouts.app')

@section('title', 'Beranda — Plant Guardian')

@section('content')
<div class="space-y-6">

    <!-- Hero Card: Hero Banner (Plant Guardian Signature) -->
    <div class="relative overflow-hidden rounded-3xl bg-[#1F3D20] text-[#F5F4DA] p-6 sm:p-8 shadow-md">
        <!-- Background Pattern Decor -->
        <div class="absolute -right-10 -bottom-10 opacity-15 pointer-events-none">
            <svg class="w-64 h-64 text-[#F5F4DA]" fill="currentColor" viewBox="0 0 200 200">
                <path d="M45,-63C58,-54,67,-40,73,-25C79,-10,82,6,77,20C72,34,59,46,45,56C31,66,16,74,0,74C-16,74,-31,66,-44,56C-57,46,-68,34,-73,19C-78,4,-77,-14,-70,-28C-63,-42,-50,-53,-37,-63C-24,-73,-12,-82,2,-85C16,-88,32,-72,45,-63Z" transform="translate(100 100)" />
            </svg>
        </div>

        <div class="relative z-10 space-y-4 max-w-xl">
            <!-- Event Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FBFAF0]/15 text-[#F5F4DA] text-xs font-baloo font-bold">
                <span class="w-2 h-2 rounded-full bg-[#D96C63] animate-pulse"></span>
                ECO-EVENT AKTIF: SUNNY SKIES
            </div>

            <!-- Main Heading in Baloo 2 -->
            <h1 class="font-baloo font-extrabold text-3xl sm:text-4xl leading-tight text-[#F5F4DA]">
                Jaga & Temukan Keanekaragaman Tumbuhan
            </h1>

            <p class="text-sm font-nunito leading-relaxed text-[#F5F4DA]/90">
                Jelajahi peta temuan spesies dari Ranger, kumpulkan benih di Seedex, serta selesaikan tantangan fisik pemuatan kompos dan penanaman pohon nyata.
            </p>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <a href="{{ route('peta') }}" class="btn-gg-primary bg-[#FBFAF0] !text-[#1F3D20] hover:!bg-[#F5F4DA] inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Buka Peta Temuan</span>
                </a>
                <a href="{{ route('minigame') }}" class="px-5 py-2.5 rounded-full bg-[#FBFAF0]/15 text-[#F5F4DA] hover:bg-[#FBFAF0]/25 font-baloo font-bold text-sm transition-colors inline-flex items-center gap-2">
                    <span>Koleksi Achievement</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Active Daily Quest / Card Misi Signature -->
    <div class="card-gg p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#D96C63]/15 text-[#D96C63] flex items-center justify-center font-baloo font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                    </svg>
                </div>
                <div>
                    <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">MISI HARIAN</span>
                    <h3 class="font-baloo font-bold text-lg text-[#2D4A2E] leading-none">Penjelajah Lapangan</h3>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold text-xs">
                3 / 5
            </span>
        </div>

        <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed">
            Temukan 5 marker spesies yang diverifikasi oleh Ranger di peta sekitar tempat tinggalmu hari ini.
        </p>

        <!-- Thick Progress Bar GG -->
        <div class="space-y-1.5">
            <div class="progress-bar-gg">
                <div class="progress-fill-gg" style="width: 60%;"></div>
            </div>
            <div class="flex justify-between text-[11px] font-baloo font-bold text-[#6B6B55]">
                <span>Progress: 60%</span>
                <span class="text-[#1F3D20]">+150 EXP & 🪙 50 NC</span>
            </div>
        </div>
    </div>

    <!-- Quick Access Modules Grid (5 Core Features) -->
    <div class="space-y-4 pt-2">
        <h2 class="font-baloo font-extrabold text-xl text-[#2D4A2E]">
            Modul Fitur Utama
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            <!-- Card 1: Peta & Catch Spesies -->
            <a href="{{ route('peta') }}" class="card-gg card-gg-hover p-5 flex flex-col justify-between group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-[#1F3D20] text-[#F5F4DA] flex items-center justify-center shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                            </svg>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-extrabold">
                            GPS REAL
                        </span>
                    </div>

                    <h3 class="font-baloo font-bold text-lg text-[#2D4A2E] group-hover:text-[#1F3D20] transition-colors">
                        Peta Temuan & Catch
                    </h3>

                    <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed">
                        Cari marker tumbuhan yang telah dipindai Ranger, tap "Temukan!" untuk menambahkan ke koleksi.
                    </p>
                </div>

                <div class="pt-4 flex items-center justify-between text-xs font-baloo font-bold text-[#1F3D20]">
                    <span>Jelajahi Peta</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 2: Galeri Seedex -->
            <a href="{{ route('galeri') }}" class="card-gg card-gg-hover p-5 flex flex-col justify-between group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-[#4C8C4A] text-[#FBFAF0] flex items-center justify-center shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-extrabold">
                            SEEDEX
                        </span>
                    </div>

                    <h3 class="font-baloo font-bold text-lg text-[#2D4A2E] group-hover:text-[#1F3D20] transition-colors">
                        Koleksi Seedex
                    </h3>

                    <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed">
                        Lihat album koleksi benih dan tumbuhan yang sudah kamu temukan lengkap dengan rarity & deskripsinya.
                    </p>
                </div>

                <div class="pt-4 flex items-center justify-between text-xs font-baloo font-bold text-[#1F3D20]">
                    <span>Buka Seedex</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 3: Kebun Virtual & Mini Game -->
            <a href="{{ route('minigame') }}" class="card-gg card-gg-hover p-5 flex flex-col justify-between group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-[#8B6A4C] text-[#FBFAF0] flex items-center justify-center shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-extrabold">
                            MINIGAME & SIMULASI
                        </span>
                    </div>

                    <h3 class="font-baloo font-bold text-lg text-[#2D4A2E] group-hover:text-[#1F3D20] transition-colors">
                        Kebun Virtual & Mini Game
                    </h3>

                    <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed">
                        Tanam benih di lahan simulasi, siram berkala hingga siap panen, serta kumpulkan Coin & EXP.
                    </p>
                </div>

                <div class="pt-4 flex items-center justify-between text-xs font-baloo font-bold text-[#1F3D20]">
                    <span>Masuk Kebun</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 4: Tantangan Kompos & Pohon Nyata -->
            <a href="{{ url('/compost') }}" class="card-gg card-gg-hover p-5 flex flex-col justify-between group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-[#2E6DA4] text-[#FBFAF0] flex items-center justify-center shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-extrabold">
                            AKSI NYATA
                        </span>
                    </div>

                    <h3 class="font-baloo font-bold text-lg text-[#2D4A2E] group-hover:text-[#1F3D20] transition-colors">
                        Tantangan Kompos & Pohon
                    </h3>

                    <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed">
                        Olah sampah organik jadi kompos matang melalui check-in foto berkala, dan kirim bukti tanam pohon nyata.
                    </p>
                </div>

                <div class="pt-4 flex items-center justify-between text-xs font-baloo font-bold text-[#1F3D20]">
                    <span>Mulai Kompos</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Card 5: Papan Peringkat Leaderboard -->
            <a href="{{ url('/leaderboard') }}" class="card-gg card-gg-hover p-5 flex flex-col justify-between group">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="w-12 h-12 rounded-2xl bg-[#7D5BA6] text-[#FBFAF0] flex items-center justify-center shadow-xs">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] text-[10px] font-baloo font-extrabold">
                            RANKING
                        </span>
                    </div>

                    <h3 class="font-baloo font-bold text-lg text-[#2D4A2E] group-hover:text-[#1F3D20] transition-colors">
                        Leaderboard Mingguan
                    </h3>

                    <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed">
                        Pantau peringkat perolehan EXP mingguanmu dibanding Guardian lainnya di papan peringkat global.
                    </p>
                </div>

                <div class="pt-4 flex items-center justify-between text-xs font-baloo font-bold text-[#1F3D20]">
                    <span>Lihat Ranking</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

        </div>
    </div>

</div>
@endsection
