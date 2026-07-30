@extends('layouts.app')

@section('title', 'Katalog Spesies Tumbuhan — Ranger PlantGuardian')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-[#5C574C]/30 pb-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('ranger.dashboard') }}" class="font-mono-code text-xs text-[#5C574C] hover:underline" style="font-family: 'IBM Plex Mono', monospace;">← KEMBALI KE MEJA ARSIP</a>
                <span class="text-xs text-[#5C574C]">•</span>
                <span class="font-mono-code text-xs text-[#9C6644] font-bold" style="font-family: 'IBM Plex Mono', monospace;">KATALOG SPESIES</span>
            </div>
            <h1 class="font-serif-headline text-2xl font-bold" style="font-family: 'Fraunces', Georgia, serif; color: #2F4A3C !important;">
                Katalog Induk Spesies Tumbuhan
            </h1>
            <p class="text-xs text-[#5C574C]">
                Kartu indeks spesies yang dipakai oleh AI Service untuk mencocokkan hasil scan AR pengguna.
            </p>
        </div>

        <a href="{{ route('ranger.species.create') }}" class="px-4 py-2 bg-[#2F4A3C] text-[#EDE6D3] font-mono-code text-xs font-bold rounded-xs uppercase tracking-wider hover:bg-[#2A2823] transition-all self-start md:self-auto flex items-center gap-2" style="font-family: 'IBM Plex Mono', monospace;">
            <span>+ TAMBAH SPESIES BARU</span>
        </a>
    </div>

    <!-- Index Cards Container -->
    <div id="species-list-container" class="space-y-3">
        <div class="p-8 text-center border border-dashed border-[#5C574C]/40 rounded-xs" style="background-color: #E3DABF !important;">
            <span class="font-mono-code text-xs text-[#5C574C]" style="font-family: 'IBM Plex Mono', monospace;">Memuat data katalog spesies...</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    import apiClient from '/resources/js/api-client.js';

    document.addEventListener('DOMContentLoaded', async () => {
        const container = document.querySelector('#species-list-container');
        try {
            const res = await apiClient.get('/ranger/species');
            const speciesList = Array.isArray(res.data) ? res.data : (Array.isArray(res) ? res : []);

            if (speciesList.length === 0) {
                container.innerHTML = `
                    <div class="p-8 text-center border border-dashed border-[#5C574C]/40 rounded-xs" style="background-color: #E3DABF !important;">
                        <p class="font-serif-headline text-base font-bold text-[#2A2823] mb-1" style="font-family: 'Fraunces', Georgia, serif;">Belum ada data spesies</p>
                        <p class="text-xs text-[#5C574C]">Silakan klik tombol "+ TAMBAH SPESIES BARU" untuk menginput data pertama.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = speciesList.map(item => `
                <div class="p-4 border border-[#5C574C]/30 rounded-xs flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xs" style="background-color: #EDE6D3 !important;">
                    <div class="flex items-start gap-4">
                        <div class="w-16 h-16 rounded-xs bg-[#E3DABF] border border-[#5C574C]/30 flex items-center justify-center overflow-hidden shrink-0">
                            ${item.reference_image_path ? `<img src="/storage/${item.reference_image_path}" class="w-full h-full object-cover" />` : `<span class="font-mono-code text-xs text-[#5C574C]" style="font-family: 'IBM Plex Mono', monospace;">NO IMG</span>`}
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono-code text-xs font-bold px-2 py-0.5 border border-[#5C574C]/40 text-[#2F4A3C]" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important;">
                                    ${item.species_code}
                                </span>
                                ${item.conservation_status ? `<span class="font-mono-code text-[10px] px-2 py-0.5 border border-[#9C6644]/40 text-[#9C6644]" style="font-family: 'IBM Plex Mono', monospace;">${item.conservation_status}</span>` : ''}
                            </div>
                            <h3 class="font-serif-headline text-base font-bold text-[#2A2823]" style="font-family: 'Fraunces', Georgia, serif;">
                                ${item.common_name} ${item.scientific_name ? `<span class="italic font-normal text-xs text-[#5C574C]">(${item.scientific_name})</span>` : ''}
                            </h3>
                            <p class="text-xs text-[#5C574C] line-clamp-2 max-w-2xl">${item.description}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 border-t md:border-t-0 pt-3 md:pt-0 border-[#5C574C]/20 shrink-0">
                        <a href="/ranger/species/${item.id}/edit" class="px-3 py-1.5 border border-[#2F4A3C] text-[#2F4A3C] font-mono-code text-xs font-bold hover:bg-[#2F4A3C] hover:text-[#EDE6D3] transition-all rounded-xs" style="font-family: 'IBM Plex Mono', monospace;">EDIT</a>
                        <button onclick="deleteSpecies(${item.id})" class="px-3 py-1.5 border border-[#8B3A3A] text-[#8B3A3A] font-mono-code text-xs font-bold hover:bg-[#8B3A3A] hover:text-[#EDE6D3] transition-all rounded-xs cursor-pointer" style="font-family: 'IBM Plex Mono', monospace;">HAPUS</button>
                    </div>
                </div>
            `).join('');
        } catch (err) {
            container.innerHTML = `
                <div class="p-4 border border-[#8B3A3A]/40 rounded-xs text-[#8B3A3A] text-xs font-mono-code" style="font-family: 'IBM Plex Mono', monospace;">
                    Gagal memuat data katalog spesies: ${err.message}
                </div>
            `;
        }
    });

    window.deleteSpecies = async (id) => {
        if (!confirm('Apakah Anda yakin ingin menghapus data spesies ini dari katalog?')) return;
        try {
            await apiClient.delete(`/ranger/species/${id}`);
            alert('Spesies berhasil dihapus.');
            location.reload();
        } catch (err) {
            alert('Gagal menghapus spesies: ' + err.message);
        }
    };
</script>
@endpush
