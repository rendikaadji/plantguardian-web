<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google-site-verification" content="czz5LCT5nx585AfXccHZgb0RoTsg2ZBifc6Y_nfQfu4">
    <title>{{ __('auth.login_title') }}</title>

    <!-- Google Fonts: Fraunces, Public Sans, IBM Plex Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..800&family=IBM+Plex+Mono:wght@400;500;600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Favicon & Brand Icons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- Meta & Open Graph Tags -->
    <meta property="og:title" content="{{ __('auth.login_title') }}">
    <meta property="og:description" content="Selamat datang di Plant Guardian — Konservasi & Kebun Fantasi.">
    <meta property="og:image" content="{{ asset('images/logo-plantGuardian.jpeg') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="{{ asset('images/logo-plantGuardian.jpeg') }}">

    <!-- Schema.org Organization Logo for Google Search -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Organization",
      "name": "Plant Guardian",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('images/logo-plantGuardian.jpeg') }}"
    }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 antialiased" style="background-color: #EDE6D3 !important; color: #2A2823 !important; font-family: 'Public Sans', sans-serif;">

    <!-- Outer Frame Container -->
    <div class="w-full max-w-lg p-3 rounded-sm border" style="background-color: #EDE6D3 !important; border-color: #9C6644 !important;">
        <!-- Inner Specimen Card Container -->
        <div class="p-8 sm:p-10 rounded-sm relative" style="background-color: #EDE6D3 !important; border: 2px dashed rgba(156, 102, 68, 0.4) !important;">
            
            <!-- Brand Logo & Sub-header -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-plantGuardian.jpeg') }}" alt="PlantGuardian Logo" class="w-10 h-10 rounded-xs object-cover border border-[#2A2823] shadow-xs">
                    <div class="flex flex-col">
                        <span class="font-serif-headline font-bold text-xl leading-none" style="font-family: 'Fraunces', Georgia, serif !important; color: #2F4A3C !important;">
                            PlantGuardian
                        </span>
                        <span class="font-mono-code text-[10px] uppercase tracking-widest pt-0.5" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                            HERBARIUM & FIELD JOURNAL
                        </span>
                    </div>
                </div>

                <!-- Language Switcher Pills -->
                <div class="flex items-center bg-[#E3DABF] p-0.5 rounded-full border border-[#2F4A3C]/20 font-mono-code text-[10px] font-bold">
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
            </div>

            <!-- Page Title & Subtitle -->
            <div class="space-y-2 mb-8">
                <h1 class="font-serif-headline text-3xl sm:text-4xl font-bold tracking-tight" style="font-family: 'Fraunces', Georgia, serif !important; color: #2F4A3C !important;">
                    {{ __('auth.welcome_back') }}
                </h1>
                <p class="text-sm leading-relaxed" style="color: #5C574C !important;">
                    {{ __('auth.login_subtitle') }}
                </p>
            </div>

            <!-- Validation Error Alert -->
            @if ($errors->any())
                <div class="p-3 mb-6 rounded-xs border text-xs font-mono-code" style="background-color: rgba(139, 58, 58, 0.1) !important; border-color: #8B3A3A !important; color: #8B3A3A !important; font-family: 'IBM Plex Mono', monospace !important;">
                    @foreach ($errors->all() as $error)
                        <p>⚠️ {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="block font-mono-code text-xs font-bold uppercase tracking-wider" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                        {{ __('auth.email_label') }}
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="{{ __('auth.email_placeholder') }}" class="w-full px-4 py-3 rounded-xs border font-sans text-sm transition-colors focus:outline-none" style="background-color: #E3DABF !important; border-color: #2F4A3C !important; color: #2A2823 !important;">
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="block font-mono-code text-xs font-bold uppercase tracking-wider" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                        {{ __('auth.password_label') }}
                    </label>
                    <div class="flex items-center rounded-xs border overflow-hidden transition-colors focus-within:ring-1 focus-within:ring-[#2F4A3C]/40" style="background-color: #E3DABF !important; border-color: #2F4A3C !important;">
                        <input type="password" id="password" name="password" required placeholder="{{ __('auth.password_placeholder') }}" class="flex-1 min-w-0 px-4 py-3 bg-transparent border-none font-sans text-sm focus:outline-none" style="color: #2A2823 !important;">
                        <button type="button" onclick="togglePasswordVisibility('password', this)" class="flex-shrink-0 flex items-center justify-center w-11 h-full text-[#5C574C] hover:text-[#2F4A3C] transition-colors cursor-pointer focus:outline-none" title="Tampilkan/Sembunyikan Kata Sandi">
                            <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858-5.908a10.05 10.05 0 014.122-.963c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21M3 3l18 18"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 font-semibold text-sm rounded-xs transition-colors shadow-xs cursor-pointer" style="background-color: #2F4A3C !important; color: #EDE6D3 !important;">
                        {{ __('auth.login_button') }}
                    </button>
                </div>
            </form>

            <!-- Register Redirect Link -->
            <div class="mt-8 text-center text-sm" style="color: #5C574C !important;">
                <span>{{ __('auth.no_account') }} </span>
                <a href="{{ route('register') }}" class="font-bold underline" style="color: #2F4A3C !important;">{{ __('auth.register_now') }}</a>
            </div>

            <!-- Footer Metadata -->
            <div class="mt-8 pt-4 border-t flex justify-between items-center font-mono-code text-[11px]" style="border-color: rgba(156, 102, 68, 0.3) !important; color: #5C574C !important; font-family: 'IBM Plex Mono', monospace !important;">
                <span>VOL. 01 — JURNAL FLORA</span>
                <span>ED. 2026</span>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                if (eyeOpen) eyeOpen.classList.add('hidden');
                if (eyeClosed) eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                if (eyeOpen) eyeOpen.classList.remove('hidden');
                if (eyeClosed) eyeClosed.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
