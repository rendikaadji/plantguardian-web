<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.role_title') }}</title>

    <!-- Google Fonts: Fraunces, Public Sans, IBM Plex Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..800&family=IBM+Plex+Mono:wght@400;500;600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-8 antialiased" style="background-color: #EDE6D3 !important; color: #2A2823 !important; font-family: 'Public Sans', sans-serif;">

    <div class="w-full max-w-4xl space-y-8 py-8">
        <!-- Top Header Step Indicator -->
        <div class="text-center space-y-2">
            <!-- Language Switcher Pills -->
            <div class="inline-flex items-center bg-[#E3DABF] p-0.5 rounded-full border border-[#2F4A3C]/20 font-mono-code text-[10px] font-bold mb-2">
                <form method="POST" action="{{ route('locale.switch') }}" class="inline">
                    @csrf
                    <input type="hidden" name="locale" value="en">
                    <button type="submit" class="px-2 py-0.5 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'en' ? 'bg-[#2F4A3C] text-[#EDE6D3]' : 'text-[#5C574C]' }}">EN</button>
                </form>
                <form method="POST" action="{{ route('locale.switch') }}" class="inline">
                    @csrf
                    <input type="hidden" name="locale" value="id">
                    <button type="submit" class="px-2 py-0.5 rounded-full transition-all cursor-pointer {{ app()->getLocale() === 'id' ? 'bg-[#2F4A3C] text-[#EDE6D3]' : 'text-[#5C574C]' }}">ID</button>
                </form>
            </div>

            <h1 class="font-serif-headline text-4xl sm:text-5xl font-bold tracking-tight" style="font-family: 'Fraunces', Georgia, serif !important; color: #2F4A3C !important;">
                {{ __('auth.role_heading') }}
            </h1>
            <p class="text-base max-w-xl mx-auto" style="color: #5C574C !important;">
                {{ __('auth.role_subtitle') }}
            </p>
        </div>

        <!-- Role Selection Form -->
        <form method="POST" action="{{ route('onboarding.store-role') }}" id="role-form">
            @csrf
            <input type="hidden" name="role" id="selected-role" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Viewer Role Card -->
                <div onclick="submitRole('viewer')" class="p-8 rounded-sm cursor-pointer transition-all hover:-translate-y-1 relative flex flex-col justify-between" style="background-color: #EDE6D3 !important; border: 2px dashed rgba(156, 102, 68, 0.5) !important;">
                    <div class="space-y-6">
                        <!-- Role Badge Monospace Tag -->
                        <div class="flex items-center justify-between border-b pb-4" style="border-color: rgba(156, 102, 68, 0.3) !important;">
                            <span class="font-mono-code text-xs font-bold px-2.5 py-1 rounded-xs" style="font-family: 'IBM Plex Mono', monospace !important; background-color: #9C6644 !important; color: #EDE6D3 !important;">
                                ROLE-VWR
                            </span>
                        </div>

                        <!-- Icon Box -->
                        <div class="w-14 h-14 rounded-xs border p-3 flex items-center justify-center" style="background-color: #EDE6D3 !important; border-color: #2F4A3C !important;">
                            <svg class="w-8 h-8" style="color: #2F4A3C !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Title & Description -->
                        <div class="space-y-2">
                            <h3 class="font-serif-headline text-2xl sm:text-3xl font-bold" style="font-family: 'Fraunces', Georgia, serif !important; color: #2F4A3C !important;">
                                {{ __('auth.viewer_role') }}
                            </h3>
                            <p class="text-sm leading-relaxed" style="color: #5C574C !important;">
                                {{ __('auth.viewer_desc') }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-8 mt-6 border-t flex items-center justify-between font-mono-code text-sm font-bold" style="border-color: rgba(156, 102, 68, 0.3) !important; color: #2F4A3C !important; font-family: 'IBM Plex Mono', monospace !important;">
                        <span>{{ __('auth.save_role') }} (Viewer)</span>
                        <span>&rarr;</span>
                    </div>
                </div>

                <!-- Ranger Role Card -->
                <div onclick="submitRole('ranger')" class="p-8 rounded-sm cursor-pointer transition-all hover:-translate-y-1 relative flex flex-col justify-between" style="background-color: #EDE6D3 !important; border: 2px dashed rgba(156, 102, 68, 0.5) !important;">
                    <div class="space-y-6">
                        <!-- Role Badge Monospace Tag -->
                        <div class="flex items-center justify-between border-b pb-4" style="border-color: rgba(156, 102, 68, 0.3) !important;">
                            <span class="font-mono-code text-xs font-bold px-2.5 py-1 rounded-xs" style="font-family: 'IBM Plex Mono', monospace !important; background-color: #9C6644 !important; color: #EDE6D3 !important;">
                                ROLE-RGR
                            </span>
                        </div>

                        <!-- Icon Box -->
                        <div class="w-14 h-14 rounded-xs border p-3 flex items-center justify-center" style="background-color: #EDE6D3 !important; border-color: #2F4A3C !important;">
                            <svg class="w-8 h-8" style="color: #2F4A3C !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>

                        <!-- Title & Description -->
                        <div class="space-y-2">
                            <h3 class="font-serif-headline text-2xl sm:text-3xl font-bold" style="font-family: 'Fraunces', Georgia, serif !important; color: #2F4A3C !important;">
                                {{ __('auth.ranger_role') }}
                            </h3>
                            <p class="text-sm leading-relaxed" style="color: #5C574C !important;">
                                {{ __('auth.ranger_desc') }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-8 mt-6 border-t flex items-center justify-between font-mono-code text-sm font-bold" style="border-color: rgba(156, 102, 68, 0.3) !important; color: #2F4A3C !important; font-family: 'IBM Plex Mono', monospace !important;">
                        <span>{{ __('auth.save_role') }} (Ranger)</span>
                        <span>&rarr;</span>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function submitRole(roleName) {
            document.getElementById('selected-role').value = roleName;
            document.getElementById('role-form').submit();
        }
    </script>
</body>
</html>
