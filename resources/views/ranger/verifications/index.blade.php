@extends('layouts.app')

@section('title', 'Verifikasi Temuan — Ranger PlantGuardian')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#5C574C]/30 pb-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('ranger.dashboard') }}" class="font-mono-code text-xs text-[#5C574C] hover:underline" style="font-family: 'IBM Plex Mono', monospace;">← KEMBALI KE MEJA ARSIP</a>
                <span class="text-xs text-[#5C574C]">•</span>
                <span class="font-mono-code text-xs text-[#9C6644] font-bold" style="font-family: 'IBM Plex Mono', monospace;">MODERASI VERIFIKASI</span>
            </div>
            <h1 class="font-serif-headline text-2xl font-bold" style="font-family: 'Fraunces', Georgia, serif; color: #2F4A3C !important;">
                Antrean Verifikasi Temuan Tumbuhan
            </h1>
            <p class="text-xs text-[#5C574C]">
                Tinjau foto hasil pemindaian tumbuhan oleh Ranger & Viewer. Tandai "Verifikasi" atau "Tolak" untuk menjaga integritas data.
            </p>
        </div>
    </div>

    <!-- Tabs Queue Selector -->
    <div class="flex border-b border-[#5C574C]/30 gap-6">
        <button id="tab-sightings" class="pb-2 font-serif-headline text-sm font-bold border-b-2 border-[#2F4A3C] text-[#2F4A3C]" style="font-family: 'Fraunces', Georgia, serif;">
            Hasil Scan Tumbuhan (<span id="count-sighting-badge">0</span>)
        </button>
    </div>

    <!-- Queue List Container -->
    <div id="queue-container" class="space-y-4">
        <div class="p-8 text-center border border-dashed border-[#5C574C]/40 rounded-xs" style="background-color: #E3DABF !important;">
            <span class="font-mono-code text-xs text-[#5C574C]" style="font-family: 'IBM Plex Mono', monospace;">Memuat antrean verifikasi...</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    import apiClient from '/resources/js/api-client.js';

    let queueData = { pending_sightings: [] };

    document.addEventListener('DOMContentLoaded', async () => {
        await loadQueue();
    });

    async function loadQueue() {
        const container = document.querySelector('#queue-container');
        try {
            const res = await apiClient.get('/ranger/verifications/pending');
            queueData = res.data || { pending_sightings: [] };

            document.querySelector('#count-sighting-badge').textContent = queueData.pending_sightings.length;

            renderTab();
        } catch (err) {
            container.innerHTML = `
                <div class="p-4 border border-[#8B3A3A]/40 rounded-xs text-[#8B3A3A] text-xs font-mono-code" style="font-family: 'IBM Plex Mono', monospace;">
                    Gagal memuat antrean verifikasi: ${err.message}
                </div>
            `;
        }
    }

    function renderTab() {
        const container = document.querySelector('#queue-container');
        const list = queueData.pending_sightings || [];

        if (list.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center border border-dashed border-[#5C574C]/40 rounded-xs" style="background-color: #E3DABF !important;">
                    <p class="font-serif-headline text-base font-bold text-[#2A2823] mb-1" style="font-family: 'Fraunces', Georgia, serif;">Antrean Kosong</p>
                    <p class="text-xs text-[#5C574C]">Tidak ada temuan yang memerlukan peninjauan.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = list.map(item => `
            <div class="p-4 border border-[#5C574C]/30 rounded-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-xs" style="background-color: #EDE6D3 !important;">
                <div class="flex items-start gap-4">
                    <div class="w-20 h-20 bg-[#E3DABF] border border-[#5C574C]/30 rounded-xs flex items-center justify-center overflow-hidden shrink-0">
                        ${item.photo_path ? `<img src="/storage/${item.photo_path}" class="w-full h-full object-cover" />` : `<span class="font-mono-code text-xs text-[#5C574C]">NO FOTO</span>`}
                    </div>
                    <div class="space-y-1">
                        <span class="font-mono-code text-[10px] uppercase text-[#9C6644] font-bold" style="font-family: 'IBM Plex Mono', monospace;">
                            PENGGUNA: ${item.user?.name || 'Viewer'}
                        </span>
                        <h3 class="font-serif-headline text-base font-bold text-[#2A2823]" style="font-family: 'Fraunces', Georgia, serif;">
                            ${item.plant_species ? item.plant_species.common_name : 'Spesies Tidak Dikenali'}
                        </h3>
                        <p class="text-xs text-[#5C574C]">Skor AI Confidence: ${item.confidence_score ? (item.confidence_score * 100).toFixed(0) + '%' : '-'}</p>
                        <span class="font-mono-code text-[10px] text-[#5C574C] block" style="font-family: 'IBM Plex Mono', monospace;">
                            Ditemukan: ${new Date(item.created_at).toLocaleString('id-ID')}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 border-t md:border-t-0 pt-3 md:pt-0 border-[#5C574C]/20 shrink-0 w-full md:w-auto justify-end">
                    <button onclick="decideSighting(${item.id}, 'verified')" class="px-4 py-2 bg-[#2F4A3C] text-[#EDE6D3] font-mono-code text-xs font-bold rounded-xs hover:bg-[#2A2823] cursor-pointer" style="font-family: 'IBM Plex Mono', monospace;">VERIFIKASI</button>
                    <button onclick="decideSighting(${item.id}, 'rejected')" class="px-4 py-2 border border-[#8B3A3A] text-[#8B3A3A] font-mono-code text-xs font-bold rounded-xs hover:bg-[#8B3A3A] hover:text-[#EDE6D3] cursor-pointer" style="font-family: 'IBM Plex Mono', monospace;">TOLAK</button>
                </div>
            </div>
        `).join('');
    }

    window.decideSighting = async (id, status) => {
        try {
            await apiClient.post(`/ranger/verifications/sightings/${id}`, { status });
            alert(`Hasil scan berhasil ditandai ${status}.`);
            await loadQueue();
        } catch (err) {
            alert('Gagal memverifikasi: ' + err.message);
        }
    };
</script>
@endpush
