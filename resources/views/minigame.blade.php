@extends('layouts.app')

@section('title', 'Koleksi Achievement — PlantGuardian')

@section('content')
<div class="space-y-8">
    <div>
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#1F3D20] text-[#F5F4DA] text-xs font-semibold mb-2 shadow-xs">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            <span>Koleksi Lencana & Penghargaan</span>
        </div>
        <h1 class="text-3xl font-extrabold text-[#1F3D20] tracking-tight font-baloo">Koleksi Achievement</h1>
        <p class="text-xs text-[#6B6B55] mt-1 font-nunito">Daftar lencana, pencapaian, dan penghargaan yang telah berhasil didapatkan oleh Viewer.</p>
    </div>

    <!-- Achievement Container -->
    <div id="garden-plots-container" class="card-gg rounded-3xl p-6 min-h-[55vh]">
        <!-- Rendered dynamically -->
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
