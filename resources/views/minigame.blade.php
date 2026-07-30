@extends('layouts.app')

@section('title', 'Kebun Virtual — Mini Game — PlantGuardian')

@section('content')
<div class="space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-xs font-semibold mb-2 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <span>Manajemen Lahan Tanam Interaktif</span>
            </div>
            <h1 class="text-3xl font-extrabold text-[#1F3D20] tracking-tight font-baloo">Kebun Virtual</h1>
            <p class="text-xs text-[#6B6B55] mt-1 font-nunito">Tanam benih yang dibeli dari Shop, rawat, dan panen hasilnya untuk kumpulkan Coin & EXP.</p>
        </div>

        <a href="{{ route('shop') }}" class="btn-gg-primary text-xs py-2.5 px-4 cursor-pointer inline-flex items-center justify-center gap-2 self-start sm:self-auto shadow-xs">
            <span>🛒</span>
            <span>Beli Benih di Shop</span>
        </a>
    </div>

    <!-- Garden Plots Grid Container -->
    <div id="garden-plots-container" class="farm-field-background rounded-3xl p-6 sm:p-8 min-h-[55vh]">
        <!-- Rendered dynamically by minigame.js -->
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        if (window.MiniGameModule) {
            const minigame = new window.MiniGameModule({
                containerElement: document.querySelector('#garden-plots-container')
            });
            await minigame.init();
        }
    });
</script>
@endpush
