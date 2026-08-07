<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panduan Viewer — PlantGuardian</title>

    <!-- Google Fonts: Fraunces, Public Sans, IBM Plex Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400..800&family=IBM+Plex+Mono:wght@400;500;600&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 sm:p-8 antialiased" style="background-color: #EDE6D3 !important; color: #2A2823 !important; font-family: 'Public Sans', sans-serif;">

    <div class="w-full max-w-5xl p-6 sm:p-10 rounded-sm border" style="background-color: #EDE6D3 !important; border-color: #9C6644 !important;">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            
            <!-- Left Side: Step-by-Step Viewer Guide (7 Columns) -->
            <div class="lg:col-span-7 space-y-6">
                <span class="font-mono-code text-xs font-semibold uppercase tracking-widest" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                    SELAMAT DATANG, VIEWER
                </span>

                <h1 class="font-serif-headline text-3xl sm:text-4xl lg:text-5xl font-bold tracking-tight leading-tight" style="font-family: 'Fraunces', Georgia, serif !important; color: #2F4A3C !important;">
                    Mulai Dokumentasi Pertamamu
                </h1>

                <p class="text-sm leading-relaxed" style="color: #5C574C !important;">
                    Sebelum mulai, ketahui alur singkat pemakaian PlantGuardian sebagai Viewer:
                </p>

                <!-- 4 Onboarding Steps -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-start gap-4">
                        <span class="font-mono-code text-sm font-bold pt-0.5" style="font-family: 'IBM Plex Mono', monospace !important; color: #2F4A3C !important;">01</span>
                        <p class="text-sm" style="color: #2A2823 !important;">Izinkan akses lokasi & kamera saat diminta.</p>
                    </div>

                    <div class="flex items-start gap-4">
                        <span class="font-mono-code text-sm font-bold pt-0.5" style="font-family: 'IBM Plex Mono', monospace !important; color: #2F4A3C !important;">02</span>
                        <p class="text-sm" style="color: #2A2823 !important;">Buka Peta, temukan tumbuhan di sekitarmu, lalu pindai dengan kamera AR.</p>
                    </div>

                    <div class="flex items-start gap-4">
                        <span class="font-mono-code text-sm font-bold pt-0.5" style="font-family: 'IBM Plex Mono', monospace !important; color: #2F4A3C !important;">03</span>
                        <p class="text-sm" style="color: #2A2823 !important;">Simpan hasil temuan ke Galeri — dapatkan EXP & Coin.</p>
                    </div>

                    <div class="flex items-start gap-4">
                        <span class="font-mono-code text-sm font-bold pt-0.5" style="font-family: 'IBM Plex Mono', monospace !important; color: #2F4A3C !important;">04</span>
                        <p class="text-sm" style="color: #2A2823 !important;">Gunakan Coin untuk kebun virtual, atau ikuti tantangan kompos nyata.</p>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="pt-6">
                    <a href="{{ route('home') }}" class="inline-block px-8 py-3.5 font-semibold text-sm rounded-xs shadow-xs transition-colors" style="background-color: #2F4A3C !important; color: #EDE6D3 !important;">
                        Mulai Jelajahi
                    </a>
                </div>
            </div>

            <!-- Right Side: Herbarium Illustration Specimen Frame (5 Columns) -->
            <div class="lg:col-span-5 flex justify-center">
                <div class="w-full max-w-sm aspect-3/4 rounded-sm p-6 relative flex items-center justify-center" style="background-color: #E3DABF !important; border: 2px dashed #9C6644 !important;">
                    <div class="absolute top-3 right-4 font-mono-code text-xs font-semibold" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                        FIG. 01
                    </div>

                    <div class="text-center font-mono-code text-sm" style="font-family: 'IBM Plex Mono', monospace !important; color: #5C574C !important;">
                        [ ilustrasi onboarding ]
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
