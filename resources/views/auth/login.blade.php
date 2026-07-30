<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk ke Jurnalmu — PlantGuardian</title>

    <!-- Google Fonts: Fraunces, Public Sans, IBM Plex Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-plantGuardian.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 antialiased" style="background-color: #EDE6D3 !important; color: #2A2823 !important; font-family: 'Public Sans', sans-serif;">

    <!-- Outer Frame Container -->
    <div class="w-full max-w-lg p-3 rounded-sm border" style="background-color: #EDE6D3 !important; border-color: #9C6644 !important;">
        <!-- Inner Specimen Card Container -->
        <div class="p-8 sm:p-10 rounded-sm relative" style="background-color: #EDE6D3 !important; border: 2px dashed rgba(156, 102, 68, 0.4) !important;">
            
            <!-- Brand Logo & Sub-header -->
            <div class="flex items-center gap-3 mb-8">
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

            <!-- Page Title & Subtitle -->
            <div class="space-y-2 mb-8">
                <h1 class="font-serif-headline text-3xl sm:text-4xl font-bold tracking-tight" style="font-family: 'Fraunces', Georgia, serif !important; color: #2F4A3C !important;">
                    Masuk ke Jurnalmu
                </h1>
                <p class="text-sm leading-relaxed" style="color: #5C574C !important;">
                    Lanjutkan dokumentasi spesies dan pantau kebun virtualmu.
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
                        EMAIL
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="nama@email.com" class="w-full px-4 py-3 rounded-xs border font-sans text-sm transition-colors focus:outline-none" style="background-color: #E3DABF !important; border-color: #2F4A3C !important; color: #2A2823 !important;">
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="block font-mono-code text-xs font-bold uppercase tracking-wider" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                        KATA SANDI
                    </label>
                    <input type="password" id="password" name="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xs border font-sans text-sm transition-colors focus:outline-none" style="background-color: #E3DABF !important; border-color: #2F4A3C !important; color: #2A2823 !important;">
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full py-3.5 px-4 font-semibold text-sm rounded-xs transition-colors shadow-xs" style="background-color: #2F4A3C !important; color: #EDE6D3 !important;">
                        Masuk
                    </button>
                </div>
            </form>

            <!-- Register Redirect Link -->
            <div class="mt-8 text-center text-sm" style="color: #5C574C !important;">
                <span>Belum punya akun? </span>
                <a href="{{ route('register') }}" class="font-bold underline" style="color: #2F4A3C !important;">Daftar sebagai Viewer</a>
            </div>

            <!-- Footer Metadata -->
            <div class="mt-8 pt-4 border-t flex justify-between items-center font-mono-code text-[11px]" style="border-color: rgba(156, 102, 68, 0.3) !important; color: #5C574C !important; font-family: 'IBM Plex Mono', monospace !important;">
                <span>VOL. 01 — JURNAL FLORA</span>
                <span>ED. 2026</span>
            </div>
        </div>
    </div>

</body>
</html>
