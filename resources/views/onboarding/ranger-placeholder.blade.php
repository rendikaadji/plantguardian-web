<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alur Ranger — PlantGuardian</title>

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
    <meta property="og:title" content="Alur Ranger — PlantGuardian">
    <meta property="og:image" content="{{ asset('images/logo-plantGuardian.jpeg') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center p-4 antialiased" style="background-color: #EDE6D3 !important; color: #2A2823 !important; font-family: 'Public Sans', sans-serif;">

    <div class="w-full max-w-md p-3 rounded-sm border" style="background-color: #EDE6D3 !important; border-color: #9C6644 !important;">
        <div class="p-8 text-center space-y-6 rounded-sm" style="background-color: #EDE6D3 !important; border: 2px dashed rgba(156, 102, 68, 0.4) !important;">
            
            <div class="w-12 h-12 mx-auto rounded-xs border p-2 flex items-center justify-center" style="background-color: #E3DABF !important; border-color: #9C6644 !important;">
                <svg class="w-6 h-6" style="color: #9C6644 !important;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>

            <div class="space-y-2">
                <span class="font-mono-code text-xs font-bold uppercase tracking-wider px-2 py-0.5 border" style="font-family: 'IBM Plex Mono', monospace !important; background-color: #E3DABF !important; border-color: #9C6644 !important; color: #9C6644 !important;">
                    STATUS: IN DEVELOPMENT
                </span>
                <h2 class="font-serif-headline text-2xl font-bold" style="font-family: 'Fraunces', Georgia, serif !important; color: #2F4A3C !important;">
                    Alur Ranger Belum Tersedia
                </h2>
                <p class="text-xs leading-relaxed" style="color: #5C574C !important;">
                    Fitur khusus Ranger untuk mengelola dan memasukkan katalog data spesies tumbuhan masih dalam tahap pengembangan.
                </p>
            </div>

            <div class="pt-2">
                <a href="{{ route('home') }}" class="inline-block w-full py-3 px-4 font-semibold text-xs rounded-xs transition-colors" style="background-color: #2F4A3C !important; color: #EDE6D3 !important;">
                    &larr; Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

</body>
</html>
