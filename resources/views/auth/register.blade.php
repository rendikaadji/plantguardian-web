<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.register_title') }}</title>

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
    <meta property="og:title" content="{{ __('auth.register_title') }}">
    <meta property="og:description" content="Daftar akun baru Plant Guardian dan mulai petualangan konservasi flora Anda.">
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
            <div class="flex items-center justify-between mb-6">
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
            <div class="space-y-2 mb-6">
                <h1 class="font-serif-headline text-3xl font-bold tracking-tight" style="font-family: 'Fraunces', Georgia, serif !important; color: #2F4A3C !important;">
                    {{ __('auth.join_title') }}
                </h1>
                <p class="text-sm leading-relaxed" style="color: #5C574C !important;">
                    {{ __('auth.register_subtitle') }}
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

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- Name Input -->
                <div class="space-y-1">
                    <label for="name" class="block font-mono-code text-xs font-bold uppercase tracking-wider" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                        {{ __('auth.name_label') }}
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="{{ __('auth.name_placeholder') }}" class="w-full px-4 py-2.5 rounded-xs border font-sans text-sm transition-colors focus:outline-none" style="background-color: #E3DABF !important; border-color: #2F4A3C !important; color: #2A2823 !important;">
                </div>

                <!-- Email Input -->
                <div class="space-y-1">
                    <label for="email" class="block font-mono-code text-xs font-bold uppercase tracking-wider" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                        {{ __('auth.email_label') }}
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="{{ __('auth.email_placeholder') }}" class="w-full px-4 py-2.5 rounded-xs border font-sans text-sm transition-colors focus:outline-none" style="background-color: #E3DABF !important; border-color: #2F4A3C !important; color: #2A2823 !important;">
                </div>

                <!-- Password Input -->
                <div class="space-y-1">
                    <label for="password" class="block font-mono-code text-xs font-bold uppercase tracking-wider" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                        {{ __('auth.password_label') }}
                    </label>
                    <input type="password" id="password" name="password" required placeholder="{{ __('auth.password_placeholder') }}" class="w-full px-4 py-2.5 rounded-xs border font-sans text-sm transition-colors focus:outline-none" style="background-color: #E3DABF !important; border-color: #2F4A3C !important; color: #2A2823 !important;">
                </div>

                <!-- Password Confirmation Input -->
                <div class="space-y-1">
                    <label for="password_confirmation" class="block font-mono-code text-xs font-bold uppercase tracking-wider" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                        {{ __('auth.confirm_password_label') }}
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="{{ __('auth.confirm_password_placeholder') }}" class="w-full px-4 py-2.5 rounded-xs border font-sans text-sm transition-colors focus:outline-none" style="background-color: #E3DABF !important; border-color: #2F4A3C !important; color: #2A2823 !important;">
                </div>

                <!-- Submit Button -->
                <div class="pt-3">
                    <button type="submit" class="w-full py-3.5 px-4 font-semibold text-sm rounded-xs transition-colors shadow-xs cursor-pointer" style="background-color: #2F4A3C !important; color: #EDE6D3 !important;">
                        {{ __('auth.register_button') }}
                    </button>
                </div>
            </form>

            <!-- Login Redirect Link -->
            <div class="mt-6 text-center text-sm" style="color: #5C574C !important;">
                <span>{{ __('auth.already_have_account') }} </span>
                <a href="{{ route('login') }}" class="font-bold underline" style="color: #2F4A3C !important;">{{ __('auth.login_now') }}</a>
            </div>

            <!-- Footer Metadata -->
            <div class="mt-6 pt-4 border-t flex justify-between items-center font-mono-code text-[11px]" style="border-color: rgba(156, 102, 68, 0.3) !important; color: #5C574C !important; font-family: 'IBM Plex Mono', monospace !important;">
                <span>VOL. 01 — JURNAL FLORA</span>
                <span>ED. 2026</span>
            </div>
        </div>
    </div>

</body>
</html>
