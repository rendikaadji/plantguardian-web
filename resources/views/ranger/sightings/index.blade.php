@extends('layouts.app')

@section('title', 'Kelola & Edit Temuan Lapangan — Ranger PlantGuardian')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#5C574C]/30 pb-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('ranger.dashboard') }}" class="font-mono-code text-xs text-[#5C574C] hover:underline" style="font-family: 'IBM Plex Mono', monospace;">← KEMBALI KE MEJA ARSIP</a>
                <span class="text-xs text-[#5C574C]">•</span>
                <span class="font-mono-code text-xs text-[#9C6644] font-bold" style="font-family: 'IBM Plex Mono', monospace;">TEMUAN LAPANGAN</span>
            </div>
            <h1 class="font-serif-headline text-2xl font-bold" style="font-family: 'Fraunces', Georgia, serif; color: #2F4A3C !important;">
                Daftar & Edit Temuan Tumbuhan Lapangan
            </h1>
            <p class="text-xs text-[#5C574C]">
                Kelola hasil scan AR tumbuhan yang telah di-upload. Anda dapat mengubah spesies tumbuhan jika terjadi kesalahan identifikasi AI.
            </p>
        </div>

        <a href="{{ route('peta') }}" class="px-4 py-2 bg-[#2F4A3C] text-[#EDE6D3] font-mono-code text-xs font-bold rounded-xs uppercase tracking-wider hover:bg-[#2A2823] transition-all flex items-center gap-2" style="font-family: 'IBM Plex Mono', monospace;">
            <span>+ SCAN TUMBUHAN BARU</span>
        </a>
    </div>

    <!-- Sightings List Container -->
    <div id="sightings-list-container" class="space-y-4">
        <div class="p-8 text-center border border-dashed border-[#5C574C]/40 rounded-xs" style="background-color: #E3DABF !important;">
            <span class="font-mono-code text-xs text-[#5C574C]" style="font-family: 'IBM Plex Mono', monospace;">Memuat data temuan tumbuhan...</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    import apiClient from '/resources/js/api-client.js';

    document.addEventListener('DOMContentLoaded', async () => {
        const container = document.querySelector('#sightings-list-container');
        try {
            const res = await apiClient.get('/ranger/sightings');
            const sightingsList = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : []);

            if (sightingsList.length === 0) {
                container.innerHTML = `
                    <div class="p-8 text-center border border-dashed border-[#5C574C]/40 rounded-xs" style="background-color: #E3DABF !important;">
                        <p class="font-serif-headline text-base font-bold text-[#2A2823] mb-1" style="font-family: 'Fraunces', Georgia, serif;">Belum ada temuan tumbuhan yang di-upload</p>
                        <p class="text-xs text-[#5C574C]">Buka Peta & Kamera Scan AR untuk mengambil foto tumbuhan nyata pertama Anda.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = sightingsList.map(item => {
                const speciesName = item.species ? item.species.common_name : 'Belum Teridentifikasi';
                const scientificName = item.species ? item.species.scientific_name : '-';
                const dateStr = item.created_at ? new Date(item.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
                const lat = item.latitude ? parseFloat(item.latitude).toFixed(5) : '-';
                const lng = item.longitude ? parseFloat(item.longitude).toFixed(5) : '-';

                return `
                    <div class="p-4 border border-[#5C574C]/30 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs" style="background-color: #EDE6D3 !important;">
                        <div class="flex items-start gap-4">
                            <div class="w-20 h-20 rounded-xs bg-[#E3DABF] border border-[#5C574C]/30 flex items-center justify-center overflow-hidden shrink-0">
                                ${item.photo_url ? `<img src="${item.photo_url}" class="w-full h-full object-cover" />` : '<span class="text-xs">No Photo</span>'}
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-serif-headline text-base font-bold text-[#2A2823]" style="font-family: 'Fraunces', Georgia, serif;">
                                        ${speciesName}
                                    </h3>
                                    <span class="font-mono-code text-[10px] px-2 py-0.5 border border-[#2F4A3C]/40 text-[#2F4A3C] font-bold uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                                        ${item.verification_status || 'VERIFIED'}
                                    </span>
                                </div>
                                <p class="text-xs italic text-[#5C574C]">${scientificName}</p>
                                <div class="text-[11px] text-[#5C574C] font-mono-code flex items-center gap-3 pt-1" style="font-family: 'IBM Plex Mono', monospace;">
                                    <span>📍 ${lat}, ${lng}</span>
                                    <span>🕒 ${dateStr}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 self-end md:self-auto">
                            <a href="/ranger/sightings/${item.id}/edit" class="px-3 py-1.5 border border-[#8B6A4C] text-[#8B6A4C] font-mono-code text-xs font-bold rounded-xs hover:bg-[#8B6A4C] hover:text-[#EDE6D3] transition-all" style="font-family: 'IBM Plex Mono', monospace;">
                                ✏️ EDIT SPESIES / DATA
                            </a>
                            <button data-delete-id="${item.id}" class="delete-btn px-3 py-1.5 border border-red-700 text-red-700 font-mono-code text-xs font-bold rounded-xs hover:bg-red-700 hover:text-white transition-all cursor-pointer" style="font-family: 'IBM Plex Mono', monospace;">
                                🗑️ HAPUS
                            </button>
                        </div>
                    </div>
                `;
            }).join('');

            // Attach event listeners for delete buttons
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const id = e.currentTarget.getAttribute('data-delete-id');
                    if (confirm('Apakah Anda yakin ingin menghapus temuan tumbuhan ini dari peta?')) {
                        try {
                            await apiClient.delete(`/ranger/sightings/${id}`);
                            alert('Data temuan berhasil dihapus.');
                            window.location.reload();
                        } catch (err) {
                            alert('Gagal menghapus data temuan: ' + (err.response?.data?.message || err.message));
                        }
                    }
                });
            });

        } catch (error) {
            console.error('Error fetching sightings:', error);
            container.innerHTML = `
                <div class="p-8 text-center border border-dashed border-red-400 rounded-xs bg-red-50 text-red-700">
                    <p class="font-bold text-sm">Gagal memuat data temuan tumbuhan</p>
                    <p class="text-xs mt-1">${error.message}</p>
                </div>
            `;
        }
    });
</script>
@endpush
