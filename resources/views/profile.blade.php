@extends('layouts.app')

@section('title', 'Profil — Garden Guardian')

@section('content')
<div class="space-y-6 max-w-md mx-auto py-2">

    @if(auth()->check() && auth()->user()->role === 'ranger')
        <!-- RANGER DEDICATED PROFILE CARD -->
        <div class="flex flex-col items-center text-center space-y-4 pt-2">
            <div class="relative">
                <div class="w-32 h-32 rounded-full border-4 border-[#8B6A4C] p-1 bg-[#FBFAF0] shadow-md overflow-hidden">
                    <img src="{{ asset('images/guardian_avatar.png') }}" alt="Avatar" class="w-full h-full object-cover rounded-full" />
                </div>
            </div>

            <div class="space-y-1">
                <span class="font-mono-code text-xs font-bold px-3 py-1 rounded-xs uppercase tracking-widest border border-[#8B6A4C]" style="font-family: 'IBM Plex Mono', monospace; background-color: #8B6A4C !important; color: #EDE6D3 !important;">
                    [ RANGER - DATA STEWARD ]
                </span>
                <h1 class="font-serif-headline font-extrabold text-2xl text-[#1F3D20] pt-2" style="font-family: 'Fraunces', Georgia, serif;">
                    {{ auth()->user()->name }}
                </h1>
                <p class="font-mono-code text-xs text-[#5C574C]" style="font-family: 'IBM Plex Mono', monospace;">
                    ID: {{ sprintf('RNG-%04d', auth()->id()) }} | {{ auth()->user()->email }}
                </p>
            </div>
        </div>

        <!-- Ranger Responsibilities & Quick Actions -->
        <div class="card-gg p-5 space-y-4 bg-[#EDE6D3] border-2 border-[#5C574C]/30 shadow-xs">
            <h3 class="font-serif-headline text-base font-bold text-[#2F4A3C]" style="font-family: 'Fraunces', Georgia, serif;">
                Peran & Responsibilitas Ranger
            </h3>
            <p class="text-xs text-[#5C574C] leading-relaxed">
                Anda terdaftar sebagai Ranger (Data Steward). Tugas utama Anda adalah mengambil foto tumbuhan nyata di lapangan dan langsung menginput Nama Umum, Nama Ilmiah, Risiko Konservasi, Deskripsi, serta Cara Merawat Pohon pada saat scan.
            </p>

            <div class="pt-2">
                <a href="{{ route('peta') }}" class="w-full py-3 px-4 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-extrabold text-sm hover:bg-[#8B6A4C] transition-colors flex items-center justify-center gap-2 shadow-xs">
                    <span>🗺️ Buka Peta Lapangan & Scan AR Tumbuhan</span>
                </a>
            </div>
        </div>
    @else
        <!-- VIEWER PROFILE -->
        <!-- 1. Profile Avatar & User Header -->
        <div class="flex flex-col items-center text-center space-y-3 pt-2">
            <div class="relative">
                <div class="w-32 h-32 rounded-full border-4 border-[#1F3D20] p-1 bg-[#FBFAF0] shadow-md overflow-hidden">
                    <img src="{{ asset('images/guardian_avatar.png') }}" alt="Avatar" class="w-full h-full object-cover rounded-full" />
                </div>
                <span class="absolute bottom-1 right-2 bg-[#1F3D20] text-[#F5F4DA] text-xs font-baloo font-extrabold px-2.5 py-0.5 rounded-full border-2 border-[#F5F4DA] shadow-xs">
                    LVL {{ floor((auth()->user()->exp ?? 8420) / 200) + 1 }}
                </span>
            </div>

            <div>
                <h1 class="font-baloo font-extrabold text-2xl text-[#1F3D20] leading-tight">
                    {{ auth()->user()->name ?? 'Caelum Thorne' }}
                </h1>
                <p class="font-baloo font-bold text-xs text-[#6B6B55] flex items-center justify-center gap-1.5 uppercase tracking-wider mt-0.5">
                    <svg class="w-4 h-4 text-[#1F3D20]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                    </svg>
                    <span>GRAND ARBITER RANK</span>
                </p>
            </div>
        </div>

        <!-- 2. Category Selector Tabs (Flora, Fauna, Terra) -->
        <div class="grid grid-cols-3 gap-3 pt-2">
            <button class="card-gg p-3.5 flex flex-col items-center justify-center gap-1.5 cursor-pointer hover:bg-[#FBFAF0] transition-colors">
                <div class="w-8 h-8 rounded-full bg-[#1F3D20]/10 text-[#1F3D20] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <span class="font-baloo font-bold text-xs text-[#6B6B55]">Flora</span>
            </button>

            <button class="bg-[#1F3D20] text-[#F5F4DA] p-3.5 rounded-2xl flex flex-col items-center justify-center gap-1.5 shadow-sm cursor-pointer">
                <div class="w-8 h-8 rounded-full bg-[#FBFAF0]/20 text-[#F5F4DA] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14.5v-5l4.5 2.5-4.5 2.5z"/>
                    </svg>
                </div>
                <span class="font-baloo font-extrabold text-xs text-[#F5F4DA]">Fauna</span>
            </button>

            <button class="card-gg p-3.5 flex flex-col items-center justify-center gap-1.5 cursor-pointer hover:bg-[#FBFAF0] transition-colors">
                <div class="w-8 h-8 rounded-full bg-[#1F3D20]/10 text-[#1F3D20] flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a2.5 2.5 0 002.5-2.5V8.5dM12 12.5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                    </svg>
                </div>
                <span class="font-baloo font-bold text-xs text-[#6B6B55]">Terra</span>
            </button>
        </div>

        <!-- 3. Growth Synergy Progress Card -->
        <div class="card-gg p-5 space-y-4">
            <div class="flex items-center justify-between font-baloo font-extrabold text-sm text-[#1F3D20]">
                <span>Growth Synergy</span>
                <span class="text-xs text-[#1F3D20]">{{ auth()->user()->exp ?? 8420 }} / 10,000 XP</span>
            </div>

            <div class="progress-bar-gg">
                <div class="progress-fill-gg" style="width: {{ min(round(((auth()->user()->exp ?? 8420) / 10000) * 100), 100) }}%;"></div>
            </div>

            <div class="grid grid-cols-2 gap-3 pt-1">
                <div class="bg-[#FBFAF0] p-3.5 rounded-2xl border border-[#D96C63]/20 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#D96C63]/15 text-[#D96C63] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.605 15.12a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-baloo font-bold text-[#6B6B55] uppercase block leading-none">HYDRATION</span>
                        <span class="font-baloo font-extrabold text-xl text-[#1F3D20]">92%</span>
                    </div>
                </div>

                <div class="bg-[#FBFAF0] p-3.5 rounded-2xl border border-[#2E6DA4]/20 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#2E6DA4]/15 text-[#2E6DA4] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-baloo font-bold text-[#6B6B55] uppercase block leading-none">VITALITY</span>
                        <span class="font-baloo font-extrabold text-xl text-[#1F3D20]">74%</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

    <!-- 4. Green Contribution Badges -->
    <div class="space-y-3">
        <div class="flex items-center justify-between font-baloo font-extrabold text-lg text-[#1F3D20]">
            <span>Green Contribution Badges</span>
            <a href="#" class="text-xs text-[#6B6B55] hover:text-[#1F3D20] font-bold">View All</a>
        </div>

        <div class="flex items-center justify-between gap-2 overflow-x-auto pb-2 scrollbar-none">
            <!-- Badge 1: Forest Guard -->
            <div class="flex flex-col items-center space-y-1.5 shrink-0 w-20">
                <div class="relative w-14 h-14 rounded-full border-2 border-[#1F3D20] bg-[#1F3D20] text-[#F5F4DA] flex items-center justify-center shadow-xs">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                    </svg>
                    <span class="absolute -top-1 -right-1 bg-[#8B6A4C] text-[#F5F4DA] text-[9px] font-baloo font-extrabold px-1.5 py-0.2 rounded-full border border-[#F5F4DA]">
                        x5
                    </span>
                </div>
                <span class="font-baloo font-bold text-[11px] text-[#1F3D20] text-center leading-tight">Forest Guard</span>
            </div>

            <!-- Badge 2: Soil Master -->
            <div class="flex flex-col items-center space-y-1.5 shrink-0 w-20">
                <div class="w-14 h-14 rounded-full border-2 border-[#8B6A4C] bg-[#8B6A4C]/20 text-[#8B6A4C] flex items-center justify-center shadow-xs">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <span class="font-baloo font-bold text-[11px] text-[#1F3D20] text-center leading-tight">Soil Master</span>
            </div>

            <!-- Badge 3: Storm Bringer -->
            <div class="flex flex-col items-center space-y-1.5 shrink-0 w-20">
                <div class="w-14 h-14 rounded-full border-2 border-[#2E6DA4] bg-[#2E6DA4]/20 text-[#2E6DA4] flex items-center justify-center shadow-xs">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z"/>
                    </svg>
                </div>
                <span class="font-baloo font-bold text-[11px] text-[#1F3D20] text-center leading-tight">Storm Bringer</span>
            </div>

            <!-- Badge 4: Blossom Sentinel -->
            <div class="flex flex-col items-center space-y-1.5 shrink-0 w-20 opacity-40">
                <div class="w-14 h-14 rounded-full border-2 border-[#7D5BA6] bg-[#7D5BA6]/20 text-[#7D5BA6] flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="font-baloo font-bold text-[11px] text-[#6B6B55] text-center leading-tight">Blossom</span>
            </div>
        </div>
    </div>

    <!-- 5. Alliance Friends Section -->
    <div class="space-y-3 pt-2">
        <h2 class="font-baloo font-extrabold text-xl text-[#1F3D20]">
            Alliance Friends
        </h2>

        <div class="space-y-2.5">
            <!-- Friend 1 -->
            <div class="card-gg p-3.5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-[#1F3D20] text-[#F5F4DA] flex items-center justify-center font-baloo font-bold text-sm shrink-0 border border-[#1F3D20]">
                        LM
                    </div>
                    <div>
                        <h4 class="font-baloo font-bold text-sm text-[#1F3D20] leading-tight">Lyra Meadow</h4>
                        <p class="font-nunito text-[11px] text-[#6B6B55]">LVL 38 • Terra Faction</p>
                    </div>
                </div>
                <span class="w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20" title="Online"></span>
            </div>

            <!-- Friend 2 -->
            <div class="card-gg p-3.5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-[#8B6A4C] text-[#F5F4DA] flex items-center justify-center font-baloo font-bold text-sm shrink-0 border border-[#8B6A4C]">
                        OB
                    </div>
                    <div>
                        <h4 class="font-baloo font-bold text-sm text-[#1F3D20] leading-tight">Oaken Bark</h4>
                        <p class="font-nunito text-[11px] text-[#6B6B55]">LVL 51 • Flora Faction</p>
                    </div>
                </div>
                <span class="w-3 h-3 rounded-full bg-slate-300" title="Offline"></span>
            </div>

            <!-- Friend 3 -->
            <div class="card-gg p-3.5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-[#D96C63] text-[#F5F4DA] flex items-center justify-center font-baloo font-bold text-sm shrink-0 border border-[#D96C63]">
                        SE
                    </div>
                    <div>
                        <h4 class="font-baloo font-bold text-sm text-[#1F3D20] leading-tight">Sienna Ember</h4>
                        <p class="font-nunito text-[11px] text-[#6B6B55]">LVL 29 • Fauna Faction</p>
                    </div>
                </div>
                <span class="w-3 h-3 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20" title="Online"></span>
            </div>
        </div>

        <!-- Expand Alliance Button -->
        <button class="w-full py-3.5 rounded-2xl border-2 border-dashed border-[#1F3D20]/30 text-[#1F3D20] font-baloo font-extrabold text-sm flex items-center justify-center gap-2 hover:bg-[#1F3D20]/5 transition-colors cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            <span>Expand Alliance</span>
        </button>
    </div>

</div>
@endsection
