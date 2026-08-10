<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('admin.dashboard_title') . ' — Plant Guardian')</title>

    <!-- Google Fonts: Baloo 2 & Nunito -->
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
<body class="min-h-screen flex flex-col antialiased bg-[#F5F4DA] text-[#2A2A22]">

    <!-- Sidebar Layout Container -->
    <div class="min-h-screen flex flex-col">

        <!-- Desktop Sidebar Component (Fixed 260px width) -->
        <aside class="hidden lg:flex flex-col fixed inset-y-0 left-0 w-64 z-40 bg-[#152B16] text-[#F5F4DA] border-r border-[#E2E1C4]/20 shadow-2xl">
            <!-- Sidebar Header / Logo -->
            <div class="p-5 border-b border-[#F5F4DA]/10 flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-2xl border-2 border-[#FFD700]/50 bg-white p-0.5 shadow-md flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform overflow-hidden">
                        <img src="{{ asset('images/logo-plantGuardian.jpeg') }}" alt="PlantGuardian Logo" class="w-full h-full object-cover rounded-xl">
                    </div>
                    <div>
                        <h1 class="font-baloo font-extrabold text-lg leading-none text-[#F5F4DA]">Plant Guardian</h1>
                        <span class="inline-block mt-1 text-[9px] font-baloo font-extrabold px-2 py-0.5 rounded-full bg-[#FFD700]/20 text-[#FFD700] border border-[#FFD700]/30 tracking-wider uppercase">
                            ADMIN PANEL
                        </span>
                    </div>
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 py-4 px-3 space-y-1.5 overflow-y-auto font-baloo text-xs font-bold">
                <div class="px-3 py-1 text-[10px] font-extrabold text-[#F5F4DA]/50 uppercase tracking-widest">
                    NAVIGASI KONTROL
                </div>

                <a href="#overview" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-[#FBFAF0]/10 text-[#FFD700] border border-[#FFD700]/30 transition-all shadow-2xs">
                    <span class="text-base">📊</span>
                    <span>{{ __('admin.sidebar.overview') }}</span>
                </a>

                <a href="#section-users" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#F5F4DA]/80 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10 transition-all">
                    <span class="text-base">👥</span>
                    <span>{{ __('admin.sidebar.users') }}</span>
                </a>

                <a href="#section-reports" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[#F5F4DA]/80 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10 transition-all">
                    <div class="flex items-center gap-3">
                        <span class="text-base">🚩</span>
                        <span>{{ __('admin.sidebar.reports') }}</span>
                    </div>
                    @if(isset($reports) && count($reports) > 0)
                        <span class="px-2 py-0.5 rounded-full bg-[#C0392B] text-white text-[10px] font-extrabold">
                            {{ count($reports) }}
                        </span>
                    @endif
                </a>

                <a href="#section-monitoring" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#F5F4DA]/80 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10 transition-all">
                    <span class="text-base">📍</span>
                    <span>{{ __('admin.sidebar.monitoring') }}</span>
                </a>

                <div class="pt-4 px-3 py-1 text-[10px] font-extrabold text-[#F5F4DA]/50 uppercase tracking-widest border-t border-[#F5F4DA]/10 mt-3">
                    {{ __('admin.sidebar.mode_explorer') }}
                </div>

                <a href="{{ route('peta') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#F5F4DA]/80 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10 transition-all">
                    <span class="text-base">🗺️</span>
                    <span>{{ __('admin.sidebar.peta') }}</span>
                </a>

                <a href="{{ route('home') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#F5F4DA]/80 hover:text-[#F5F4DA] hover:bg-[#FBFAF0]/10 transition-all">
                    <span class="text-base">🏠</span>
                    <span>{{ __('admin.sidebar.home') }}</span>
                </a>
            </div>

            <!-- Sidebar User Profile Footer -->
            <div class="p-4 border-t border-[#F5F4DA]/10 bg-[#102211]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-9 h-9 rounded-full bg-[#FFD700]/20 text-[#FFD700] font-baloo font-extrabold text-xs flex items-center justify-center shrink-0 border border-[#FFD700]/30 shadow-xs">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                        <div class="overflow-hidden">
                            <span class="font-baloo font-bold text-xs text-[#F5F4DA] block truncate leading-tight">{{ auth()->user()->name }}</span>
                            <span class="text-[10px] text-[#FFD700] font-baloo font-extrabold block uppercase tracking-wider">👑 ADMIN</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-8 h-8 rounded-full bg-[#C0392B]/20 hover:bg-[#C0392B] text-[#F5F4DA] flex items-center justify-center transition-colors cursor-pointer" title="{{ __('app.logout') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Mobile Drawer Sidebar (Toggleable overlay) -->
        <div id="mobile-admin-drawer" class="fixed inset-0 z-50 bg-[#152B16]/80 backdrop-blur-sm hidden lg:hidden">
            <div class="w-72 max-w-[80vw] h-full bg-[#152B16] text-[#F5F4DA] flex flex-col shadow-2xl">
                <div class="p-5 border-b border-[#F5F4DA]/10 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('images/logo-plantGuardian.jpeg') }}" class="w-9 h-9 rounded-xl border border-[#FFD700]/40 object-cover" />
                        <span class="font-baloo font-extrabold text-base text-[#F5F4DA]">Admin Panel</span>
                    </div>
                    <button id="close-admin-drawer" class="w-8 h-8 rounded-full bg-[#F5F4DA]/10 text-white font-bold flex items-center justify-center">&times;</button>
                </div>

                <div class="flex-1 py-4 px-3 space-y-2 overflow-y-auto font-baloo text-xs font-bold">
                    <a href="#overview" onclick="toggleAdminDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-[#FBFAF0]/10 text-[#FFD700]">
                        <span>📊</span> <span>{{ __('admin.sidebar.overview') }}</span>
                    </a>
                    <a href="#section-users" onclick="toggleAdminDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#F5F4DA]/80">
                        <span>👥</span> <span>{{ __('admin.sidebar.users') }}</span>
                    </a>
                    <a href="#section-reports" onclick="toggleAdminDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#F5F4DA]/80">
                        <span>🚩</span> <span>{{ __('admin.sidebar.reports') }}</span>
                    </a>
                    <a href="#section-monitoring" onclick="toggleAdminDrawer()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#F5F4DA]/80">
                        <span>📍</span> <span>{{ __('admin.sidebar.monitoring') }}</span>
                    </a>
                    <a href="{{ route('peta') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[#F5F4DA]/80">
                        <span>🗺️</span> <span>{{ __('admin.sidebar.peta') }}</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Workspace Area (Offset by lg:pl-64) -->
        <div class="lg:pl-64 flex flex-col flex-1">

            <!-- Top Header Navbar -->
            <header class="sticky top-0 z-30 border-b border-[#1F3D20]/15 bg-[#F5F4DA]/95 backdrop-blur-md shadow-xs">
                <div class="px-4 sm:px-8 py-3 flex items-center justify-between">
                    
                    <!-- Left: Mobile Menu Toggle & Title -->
                    <div class="flex items-center gap-3">
                        <button id="open-admin-drawer" class="lg:hidden p-2 rounded-xl bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs flex items-center gap-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>

                        <div class="flex items-center gap-2">
                            <span class="text-xs font-baloo font-bold text-[#6B6B55] hidden sm:inline">Pusat Kontrol /</span>
                            <span class="font-baloo font-extrabold text-base sm:text-lg text-[#1F3D20]">
                                {{ __('admin.dashboard_title') }}
                            </span>
                        </div>
                    </div>

                    <!-- Right Controls: Status & Language Switcher -->
                    <div class="flex items-center gap-2.5">
                        <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#27AE60]/15 text-[#27AE60] font-baloo font-extrabold text-xs border border-[#27AE60]/30 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-[#27AE60] animate-pulse"></span>
                            <span>{{ __('admin.sidebar.system_active') }}</span>
                        </span>

                        <a href="{{ route('peta') }}" class="px-3.5 py-1.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs hover:bg-[#2D4A2E] transition-colors shadow-xs">
                            🌿 {{ __('admin.sidebar.peta') }}
                        </a>

                        <!-- Language Switcher -->
                        <div class="flex items-center bg-[#E2E1C4] p-0.5 rounded-full border border-[#1F3D20]/15 font-baloo font-extrabold text-[11px] text-[#1F3D20]">
                            <form method="POST" action="{{ route('locale.switch') }}" class="inline">
                                @csrf
                                <input type="hidden" name="locale" value="en">
                                <button type="submit" class="px-2.5 py-0.5 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-[#1F3D20] text-[#F5F4DA]' : 'text-[#6B6B55]' }}">
                                    EN
                                </button>
                            </form>
                            <form method="POST" action="{{ route('locale.switch') }}" class="inline">
                                @csrf
                                <input type="hidden" name="locale" value="id">
                                <button type="submit" class="px-2.5 py-0.5 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'id' ? 'bg-[#1F3D20] text-[#F5F4DA]' : 'text-[#6B6B55]' }}">
                                    ID
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Dashboard View Container -->
            <main class="flex-1 px-4 sm:px-8 py-6 pb-16">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const drawer = document.getElementById('mobile-admin-drawer');
        const openBtn = document.getElementById('open-admin-drawer');
        const closeBtn = document.getElementById('close-admin-drawer');

        function toggleAdminDrawer() {
            if (drawer) drawer.classList.toggle('hidden');
        }

        if (openBtn) openBtn.addEventListener('click', toggleAdminDrawer);
        if (closeBtn) closeBtn.addEventListener('click', toggleAdminDrawer);
    </script>
</body>
</html>
