<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Plant Guardian — Konservasi & Kebun Fantasi')</title>

    <!-- Google Fonts: Baloo 2 (Headings) & Nunito (Body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        html, body {
            background-color: #F5F4DA !important;
            color: #2A2A22 !important;
            font-family: 'Nunito', sans-serif;
        }
    </style>

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-plantGuardian.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col antialiased" style="background-color: #F5F4DA !important; color: #2A2A22 !important;">    <!-- Top Header Bar (Plant Guardian Premium Design System) -->
    <header class="sticky top-0 z-50 border-b border-[#1F3D20]/15 shadow-sm" style="background-color: rgba(245, 244, 218, 0.96) !important; backdrop-filter: blur(12px);">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 py-2 sm:py-2.5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
                
                <!-- Left: Logo, Title, User Name & Level Badge (Never Covering Logo) -->
                <div class="flex items-center gap-2.5 sm:gap-3.5">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3.5 group">
                        <!-- Clean Unobstructed Official Plant Guardian Logo -->
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl border-2 border-[#1F3D20] bg-white p-0.5 shadow-sm flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform overflow-hidden">
                            <img src="{{ asset('images/logo-plantGuardian.jpeg') }}" alt="PlantGuardian Logo" class="w-full h-full object-cover rounded-xl">
                        </div>

                        <!-- Brand & User Identity -->
                        <div class="flex flex-col justify-center min-w-0">
                            <span class="font-baloo font-extrabold text-lg sm:text-2xl leading-none text-[#1F3D20] tracking-tight group-hover:text-[#2D5A2F] transition-colors">
                                Plant Guardian
                            </span>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <span class="text-[11px] font-bold text-[#6B6B55] leading-none truncate max-w-[140px] sm:max-w-none">
                                    {{ auth()->user()->name ?? 'Penjelajah Flora' }}
                                </span>
                                @if(auth()->check())
                                    @if(auth()->user()->role === 'ranger')
                                        <span class="bg-[#8B6A4C] text-[#F5F4DA] text-[9px] font-baloo font-extrabold px-2 py-0.5 rounded-full uppercase leading-none shadow-2xs">
                                            RANGER
                                        </span>
                                    @elseif(auth()->user()->role === 'admin')
                                        <span class="bg-[#FFD700] text-[#1F3D20] text-[9px] font-baloo font-extrabold px-2 py-0.5 rounded-full uppercase leading-none shadow-2xs">
                                            ADMIN
                                        </span>
                                    @else
                                        <!-- Level Badge Cleanly Beside Name (Never Covering Logo) -->
                                        <span class="bg-[#1F3D20] text-[#F5F4DA] text-[9px] font-baloo font-extrabold px-2 py-0.5 rounded-full leading-none shadow-2xs border border-[#F5F4DA]/20">
                                            LVL {{ auth()->user()->level ?? 1 }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Right: NC, EXP, Language Switcher & Logout Controls (Consistent & Premium) -->
                <div class="flex items-center gap-1.5 sm:gap-2.5 self-stretch sm:self-auto justify-between sm:justify-end shrink-0 pt-1 sm:pt-0 border-t sm:border-t-0 border-[#1F3D20]/10">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-1.5 rounded-full bg-[#FFD700] text-[#1F3D20] font-baloo font-extrabold text-xs shadow-xs hover:bg-[#FFD700]/90 transition-colors">
                                👑 MODE ADMIN
                            </a>
                        @elseif(auth()->user()->role === 'ranger')
                            <a href="{{ route('ranger.dashboard') }}" class="px-3.5 py-1.5 rounded-full bg-[#8B6A4C] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#8B6A4C]/90 transition-colors shadow-xs">
                                🌿 MODE RANGER
                            </a>
                        @else
                            <!-- NC (Nature Coin) Pill -->
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs sm:text-sm shadow-xs border border-[#F5F4DA]/10 shrink-0">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" fill="#F4C430" stroke="#B8860B" stroke-width="1.5"/>
                                    <circle cx="12" cy="12" r="7.5" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                                    <path d="M12 6.5c-3 3.5-3.5 7.5-1.2 10.5 3-3.5 3.5-7.5 1.2-10.5z" fill="#1F3D20"/>
                                    <path d="M12 6.5c3 3.5 3.5 7.5 1.2 10.5-3-3.5-3.5-7.5-1.2-10.5z" fill="#27AE60"/>
                                </svg>
                                <span id="user-coin" class="leading-none">{{ auth()->user()->coin ?? 0 }}</span>
                                <span class="text-[10px] text-[#F5F4DA]/80 leading-none">NC</span>
                            </div>

                            <!-- EXP Pill -->
                            <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs sm:text-sm shadow-xs border border-[#1F3D20]/10 shrink-0">
                                <span class="text-[10px] font-extrabold text-[#1F3D20]/70 leading-none">EXP</span>
                                <span id="user-exp" class="leading-none">{{ auth()->user()->exp ?? 0 }}</span>
                            </div>
                        @endif

                        <!-- Controls Group: Language Switcher & Logout -->
                        <div class="flex items-center gap-1.5 ml-auto sm:ml-0">
                            <!-- Language Switcher (EN / ID) -->
                            <div class="flex items-center bg-[#E2E1C4] p-0.5 rounded-full border border-[#1F3D20]/15 font-baloo font-extrabold text-[11px] text-[#1F3D20] shadow-2xs">
                                <form method="POST" action="{{ route('locale.switch') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="locale" value="en">
                                    <button type="submit" class="px-2.5 py-0.5 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-[#1F3D20] text-[#F5F4DA] shadow-2xs' : 'text-[#6B6B55] hover:text-[#1F3D20]' }}">
                                        EN
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('locale.switch') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="locale" value="id">
                                    <button type="submit" class="px-2.5 py-0.5 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'id' ? 'bg-[#1F3D20] text-[#F5F4DA] shadow-2xs' : 'text-[#6B6B55] hover:text-[#1F3D20]' }}">
                                        ID
                                    </button>
                                </form>
                            </div>

                            <!-- Logout Button -->
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="w-8 h-8 sm:w-8.5 sm:h-8.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center hover:bg-[#C0392B] hover:text-white transition-all cursor-pointer shadow-2xs border border-[#1F3D20]/10 active:scale-95" title="Keluar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>

            </div>
        </div>
    </header>

    <!-- Main Content Slot Container -->
    <main class="max-w-6xl mx-auto w-full px-3 sm:px-6 lg:px-8 py-4 sm:py-6 pb-24 sm:pb-28">
        @yield('content')
    </main>

    <!-- Bottom Navigation Bar (Mobile & Floating Signature Component) -->
    @auth
        <div class="fixed bottom-3 inset-x-0 z-50 pointer-events-none flex justify-center px-3 sm:px-4">
            <nav class="pointer-events-auto p-1.5 sm:p-2 flex items-center justify-between gap-1 sm:gap-2 bg-[#1F3D20] text-[#F5F4DA] border-2 border-[#FBFAF0]/20 shadow-2xl rounded-full max-w-lg w-full">
                @if(auth()->user()->isAdmin())
                    <!-- ADMIN NAVBAR -->
                    <a href="{{ route('admin.dashboard') }}" class="flex-1 flex flex-col items-center justify-center py-1 sm:py-1.5 px-2 rounded-full transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#FBFAF0] text-[#1F3D20] font-extrabold shadow-sm' : 'text-[#F5F4DA]/75 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-[10px] sm:text-xs font-baloo leading-none mt-1">{{ __('app.nav.admin') }}</span>
                    </a>
                    <a href="{{ route('home') }}" class="flex-1 flex flex-col items-center justify-center py-1 sm:py-1.5 px-2 rounded-full transition-all {{ request()->routeIs('home') ? 'bg-[#FBFAF0] text-[#1F3D20] font-extrabold shadow-sm' : 'text-[#F5F4DA]/75 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span class="text-[10px] sm:text-xs font-baloo leading-none mt-1">{{ __('app.nav.home') }}</span>
                    </a>
                    <a href="{{ route('peta') }}" class="flex-1 flex flex-col items-center justify-center py-1 sm:py-1.5 px-2 rounded-full transition-all {{ request()->routeIs('peta') ? 'bg-[#FBFAF0] text-[#1F3D20] font-extrabold shadow-sm' : 'text-[#F5F4DA]/75 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <span class="text-[10px] sm:text-xs font-baloo leading-none mt-1">{{ __('app.nav.map') }}</span>
                    </a>
                @else
                    <!-- UNIFIED NAVBAR (VIEWER & RANGER) -->
                    <a href="{{ route('peta') }}" class="flex-1 flex flex-col items-center justify-center py-1 sm:py-1.5 px-1 sm:px-2 rounded-full transition-all {{ request()->routeIs('peta') ? 'bg-[#FBFAF0] text-[#1F3D20] font-extrabold shadow-sm' : 'text-[#F5F4DA]/75 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <span class="text-[10px] sm:text-xs font-baloo leading-none mt-1">{{ __('app.nav.map') }}</span>
                    </a>

                    <a href="{{ route('galeri') }}" class="flex-1 flex flex-col items-center justify-center py-1 sm:py-1.5 px-1 sm:px-2 rounded-full transition-all {{ request()->routeIs('galeri') ? 'bg-[#FBFAF0] text-[#1F3D20] font-extrabold shadow-sm' : 'text-[#F5F4DA]/75 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.5 6 4 10.5 4 15a8 8 0 0016 0c0-4.5-2.5-9-8-13z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-2 2-3 4.5-3 7M12 11c2 1.5 3 3.5 3 6"/>
                        </svg>
                        <span class="text-[10px] sm:text-xs font-baloo leading-none mt-1">{{ __('app.nav.plants') }}</span>
                    </a>

                    <a href="{{ route('minigame') }}" class="flex-1 flex flex-col items-center justify-center py-1 sm:py-1.5 px-1 sm:px-2 rounded-full transition-all {{ request()->routeIs('minigame') ? 'bg-[#FBFAF0] text-[#1F3D20] font-extrabold shadow-sm' : 'text-[#F5F4DA]/75 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-[10px] sm:text-xs font-baloo leading-none mt-1">{{ __('app.nav.minigame') }}</span>
                    </a>

                    <a href="{{ route('achievement') }}" class="flex-1 flex flex-col items-center justify-center py-1 sm:py-1.5 px-1 sm:px-2 rounded-full transition-all {{ request()->routeIs('achievement') ? 'bg-[#FBFAF0] text-[#1F3D20] font-extrabold shadow-sm' : 'text-[#F5F4DA]/75 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        <span class="text-[10px] sm:text-xs font-baloo leading-none mt-1">{{ __('app.nav.achievement') }}</span>
                    </a>

                    <a href="{{ route('profile') }}" class="flex-1 flex flex-col items-center justify-center py-1 sm:py-1.5 px-1 sm:px-2 rounded-full transition-all {{ request()->routeIs('profile') ? 'bg-[#FBFAF0] text-[#1F3D20] font-extrabold shadow-sm' : 'text-[#F5F4DA]/75 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-[10px] sm:text-xs font-baloo leading-none mt-1">{{ __('app.nav.profile') }}</span>
                    </a>
                @endif
            </nav>
        </div>
    @endauth

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.HomeModule) {
                const home = new window.HomeModule();
                home.loadWalletBalance();
                home.loadDailyMission();
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
