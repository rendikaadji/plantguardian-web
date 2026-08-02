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
<body class="min-h-screen flex flex-col antialiased" style="background-color: #F5F4DA !important; color: #2A2A22 !important;">

    <!-- Top Header Bar (Plant Guardian Design System) -->
    <header class="sticky top-0 z-50 border-b border-[#1F3D20]/10 shadow-xs" style="background-color: rgba(245, 244, 218, 0.95) !important; backdrop-filter: blur(8px);">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3">
            <div class="flex items-center justify-between gap-4">
                
                <!-- User Avatar & Title -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                        <div class="relative">
                            <div class="w-11 h-11 rounded-full border-2 border-[#1F3D20] bg-[#FBFAF0] flex items-center justify-center overflow-hidden shadow-xs">
                                <img src="{{ asset('images/logo-plantGuardian.jpeg') }}" alt="PlantGuardian Logo" class="w-full h-full object-cover">
                            </div>
                            @if(auth()->check() && auth()->user()->role !== 'ranger')
                                <span class="absolute -bottom-1 -right-1 bg-[#1F3D20] text-[#F5F4DA] text-[9px] font-extrabold px-1.5 py-0.2 rounded-full font-baloo border border-[#F5F4DA]">
                                    LVL {{ auth()->user()->level }}
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-col">
                            <span class="font-baloo font-extrabold text-xl sm:text-2xl leading-none text-[#1F3D20] tracking-tight">
                                Plant Guardian
                            </span>
                            <span class="text-[11px] font-bold text-[#6B6B55] tracking-wide flex items-center gap-1">
                                {{ auth()->user()->name ?? 'Penjelajah Flora' }}
                                @if(auth()->check() && auth()->user()->role === 'ranger')
                                    <span class="bg-[#8B6A4C] text-[#F5F4DA] text-[9px] font-extrabold px-1.5 rounded-full uppercase">RANGER</span>
                                @endif
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Currency Pills & Action Buttons -->
                <div class="flex items-center gap-2 sm:gap-3">
                    @auth
                        @if(auth()->user()->role !== 'ranger')
                            <!-- Coin Pill (Viewer Only) -->
                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs sm:text-sm shadow-xs">
                                <svg class="w-4.5 h-4.5 shrink-0" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" fill="#F4C430" stroke="#B8860B" stroke-width="1.5"/>
                                    <circle cx="12" cy="12" r="7.5" fill="#FFD700" stroke="#DAA520" stroke-width="1"/>
                                    <path d="M12 6.5c-3 3.5-3.5 7.5-1.2 10.5 3-3.5 3.5-7.5 1.2-10.5z" fill="#1F3D20"/>
                                    <path d="M12 6.5c3 3.5 3.5 7.5 1.2 10.5-3-3.5-3.5-7.5-1.2-10.5z" fill="#27AE60"/>
                                </svg>
                                <span id="user-coin">0</span>
                                <span class="text-[10px] opacity-80">NC</span>
                            </div>

                            <!-- EXP Pill (Viewer Only) -->
                            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#E7E6BE] text-[#1F3D20] font-baloo font-bold text-xs sm:text-sm shadow-xs">
                                <span class="text-[10px] text-[#1F3D20] font-extrabold">EXP</span>
                                <span id="user-exp">0</span>
                            </div>
                        @else
                            <span class="px-3 py-1 rounded-full bg-[#8B6A4C] text-[#F5F4DA] font-baloo font-bold text-xs">
                                🌿 MODE RANGER
                            </span>
                        @endif

                        <!-- Logout Form -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="w-8 h-8 rounded-full bg-[#E7E6BE] text-[#1F3D20] flex items-center justify-center hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors cursor-pointer" title="Keluar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                            </button>
                        </form>
                    @endauth
                </div>

            </div>
        </div>
    </header>

    <!-- Main View Content -->
    <main class="flex-1 max-w-5xl w-full mx-auto px-4 sm:px-6 py-6 pb-36 md:pb-40">
        @yield('content')
    </main>

    <!-- Bottom Tab Bar (Tailored for Viewer vs Ranger) -->
    @auth
        <div class="fixed bottom-0 left-0 right-0 z-50 border-t border-[#1F3D20]/10 px-4 py-2 bg-[#F5F4DA]/95 backdrop-blur-md shadow-lg">
            <nav class="flex justify-around items-center max-w-md mx-auto">
                @if(auth()->user()->role === 'ranger')
                    <!-- RANGER NAVBAR (Peta Scan AR & Profil Only) -->
                    <!-- 1. Map / AR Scan -->
                    <a href="{{ route('peta') }}" class="flex flex-col items-center px-6 py-1.5 rounded-full transition-colors {{ request()->routeIs('peta') ? 'bg-[#1F3D20] text-[#F5F4DA]' : 'text-[#6B6B55] hover:text-[#1F3D20]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <span class="text-[10px] font-baloo font-bold">Peta Scan AR</span>
                    </a>

                    <!-- 2. Profile -->
                    <a href="{{ route('profile') }}" class="flex flex-col items-center px-6 py-1.5 rounded-full transition-colors {{ request()->routeIs('profile') ? 'bg-[#1F3D20] text-[#F5F4DA]' : 'text-[#6B6B55] hover:text-[#1F3D20]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-[10px] font-baloo font-bold">Profil Ranger</span>
                    </a>
                @else
                    <!-- VIEWER NAVBAR (5 Items Signature) -->
                    <!-- 1. Map (Peta) -->
                    <a href="{{ route('peta') }}" class="flex flex-col items-center px-3 py-1.5 rounded-full transition-colors {{ request()->routeIs('peta') ? 'bg-[#1F3D20] text-[#F5F4DA]' : 'text-[#6B6B55] hover:text-[#1F3D20]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                        </svg>
                        <span class="text-[10px] font-baloo font-bold">Peta</span>
                    </a>

                    <!-- 2. Plants / Seedex (Galeri) -->
                    <a href="{{ route('galeri') }}" class="flex flex-col items-center px-3 py-1.5 rounded-full transition-colors {{ request()->routeIs('galeri') ? 'bg-[#1F3D20] text-[#F5F4DA]' : 'text-[#6B6B55] hover:text-[#1F3D20]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.5 6 4 10.5 4 15a8 8 0 0016 0c0-4.5-2.5-9-8-13z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-2 2-3 4.5-3 7M12 11c2 1.5 3 3.5 3 6"/>
                        </svg>
                        <span class="text-[10px] font-baloo font-bold">Plants</span>
                    </a>

                    <!-- 3. Mini Game -->
                    <a href="{{ route('minigame') }}" class="flex flex-col items-center px-3 py-1.5 rounded-full transition-colors {{ request()->routeIs('minigame') ? 'bg-[#1F3D20] text-[#F5F4DA]' : 'text-[#6B6B55] hover:text-[#1F3D20]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <span class="text-[10px] font-baloo font-bold">Mini Game</span>
                    </a>

                    <!-- 4. Shop -->
                    <a href="{{ route('shop') }}" class="flex flex-col items-center px-3 py-1.5 rounded-full transition-colors {{ request()->routeIs('shop') ? 'bg-[#1F3D20] text-[#F5F4DA]' : 'text-[#6B6B55] hover:text-[#1F3D20]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span class="text-[10px] font-baloo font-bold">Shop</span>
                    </a>

                    <!-- 5. Profile -->
                    <a href="{{ route('profile') }}" class="flex flex-col items-center px-3 py-1.5 rounded-full transition-colors {{ request()->routeIs('profile') ? 'bg-[#1F3D20] text-[#F5F4DA]' : 'text-[#6B6B55] hover:text-[#1F3D20]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-[10px] font-baloo font-bold">Profile</span>
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
