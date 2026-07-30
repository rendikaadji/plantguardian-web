@extends('layouts.app')

@section('title', 'Edit Data Temuan Tumbuhan — Ranger PlantGuardian')

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <!-- Header Page -->
    <div class="border-b border-[#5C574C]/30 pb-4">
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('ranger.sightings.index') }}" class="font-mono-code text-xs text-[#5C574C] hover:underline" style="font-family: 'IBM Plex Mono', monospace;">← KEMBALI KE DAFTAR TEMUAN</a>
            <span class="text-xs text-[#5C574C]">•</span>
            <span class="font-mono-code text-xs text-[#9C6644] font-bold" style="font-family: 'IBM Plex Mono', monospace;">EDIT SPESIES & DATA TEMUAN</span>
        </div>
        <h1 class="font-serif-headline text-2xl font-bold" style="font-family: 'Fraunces', Georgia, serif; color: #2F4A3C !important;">
            Koreksi Data Temuan Tumbuhan
        </h1>
        <p class="text-xs text-[#5C574C]">
            Pilih spesies yang benar jika hasil identifikasi AI sebelumnya kurang tepat.
        </p>
    </div>

    <!-- Edit Form Card -->
    <div class="p-6 border border-[#5C574C]/30 rounded-xs shadow-xs space-y-6" style="background-color: #EDE6D3 !important;">
        <!-- Photo Preview -->
        <div class="flex flex-col sm:flex-row items-center gap-4 p-4 border border-[#5C574C]/30 rounded-xs bg-[#E3DABF]">
            <div id="photo-container" class="w-28 h-28 bg-[#EDE6D3] border border-[#5C574C]/30 rounded-xs overflow-hidden shrink-0 flex items-center justify-center">
                <span class="text-xs font-mono-code text-[#5C574C]">Memuat foto...</span>
            </div>
            <div class="space-y-1 text-center sm:text-left">
                <span class="font-mono-code text-[10px] text-[#9C6644] uppercase font-bold" style="font-family: 'IBM Plex Mono', monospace;">FOTO SPESIMEN DARI LAPANGAN</span>
                <h4 id="current-species-name" class="font-serif-headline text-lg font-bold text-[#2A2823]" style="font-family: 'Fraunces', Georgia, serif;">Memuat data...</h4>
                <p id="sighting-date-info" class="text-xs text-[#5C574C] font-mono-code" style="font-family: 'IBM Plex Mono', monospace;"></p>
            </div>
        </div>

        <form id="sighting-form" class="space-y-5">
            <!-- Species Dropdown -->
            <div class="space-y-1.5">
                <label for="plant_species_id" class="font-mono-code text-xs font-bold uppercase text-[#2F4A3C] block" style="font-family: 'IBM Plex Mono', monospace;">
                    Pilih Spesies Tumbuhan yang Benar <span class="text-red-600">*</span>
                </label>
                <select id="plant_species_id" name="plant_species_id" required class="w-full p-3 border border-[#5C574C]/40 rounded-xs bg-[#F5F4DA] font-serif-headline text-sm font-semibold text-[#2A2823] focus:border-[#2F4A3C] focus:outline-none">
                    <option value="">-- Memuat Katalog Spesies... --</option>
                </select>
                <p class="text-[11px] text-[#5C574C]">Pilih nama tumbuhan yang tepat dari katalog master untuk memperbarui data temuan ini.</p>
            </div>

            <!-- Latitude & Longitude -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="latitude" class="font-mono-code text-xs font-bold uppercase text-[#2F4A3C] block" style="font-family: 'IBM Plex Mono', monospace;">
                        Latitude (Garis Lintang)
                    </label>
                    <input type="number" step="any" id="latitude" name="latitude" class="w-full p-3 border border-[#5C574C]/40 rounded-xs bg-[#F5F4DA] font-mono-code text-xs text-[#2A2823] focus:border-[#2F4A3C] focus:outline-none" style="font-family: 'IBM Plex Mono', monospace;" placeholder="-6.2088000" />
                </div>

                <div class="space-y-1.5">
                    <label for="longitude" class="font-mono-code text-xs font-bold uppercase text-[#2F4A3C] block" style="font-family: 'IBM Plex Mono', monospace;">
                        Longitude (Garis Bujur)
                    </label>
                    <input type="number" step="any" id="longitude" name="longitude" class="w-full p-3 border border-[#5C574C]/40 rounded-xs bg-[#F5F4DA] font-mono-code text-xs text-[#2A2823] focus:border-[#2F4A3C] focus:outline-none" style="font-family: 'IBM Plex Mono', monospace;" placeholder="106.8456000" />
                </div>
            </div>

            <!-- Submit & Action Buttons -->
            <div class="pt-4 border-t border-[#5C574C]/30 flex flex-col sm:flex-row items-center justify-between gap-3">
                <button type="submit" id="submit-btn" class="w-full sm:w-auto px-6 py-3 bg-[#2F4A3C] text-[#EDE6D3] font-mono-code text-xs font-bold rounded-xs uppercase tracking-wider hover:bg-[#2A2823] transition-all cursor-pointer" style="font-family: 'IBM Plex Mono', monospace;">
                    <span>💾 SIMPAN PERUBAHAN TEMUAN</span>
                </button>

                <button type="button" id="delete-btn" class="w-full sm:w-auto px-4 py-3 border border-red-700 text-red-700 font-mono-code text-xs font-bold rounded-xs uppercase tracking-wider hover:bg-red-700 hover:text-white transition-all cursor-pointer" style="font-family: 'IBM Plex Mono', monospace;">
                    <span>🗑️ HAPUS TEMUAN INI</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="module">
    import apiClient from '/resources/js/api-client.js';

    const sightingId = {{ $sightingId }};

    document.addEventListener('DOMContentLoaded', async () => {
        const speciesSelect = document.querySelector('#plant_species_id');
        const photoContainer = document.querySelector('#photo-container');
        const currentSpeciesName = document.querySelector('#current-species-name');
        const sightingDateInfo = document.querySelector('#sighting-date-info');
        const latInput = document.querySelector('#latitude');
        const lngInput = document.querySelector('#longitude');
        const form = document.querySelector('#sighting-form');
        const deleteBtn = document.querySelector('#delete-btn');

        try {
            // 1. Fetch Species list
            const speciesRes = await apiClient.get('/ranger/species');
            const speciesList = Array.isArray(speciesRes.data) ? speciesRes.data : (Array.isArray(speciesRes) ? speciesRes : []);

            speciesSelect.innerHTML = '<option value="">-- Pilih Spesies Tumbuhan --</option>' + speciesList.map(s => `
                <option value="${s.id}">${s.common_name} (${s.scientific_name})</option>
            `).join('');

            // 2. Fetch Sighting data
            const sightingRes = await apiClient.get(`/ranger/sightings/${sightingId}`);
            const sighting = sightingRes.data?.data || sightingRes.data || sightingRes;

            if (sighting) {
                if (sighting.photo_url) {
                    photoContainer.innerHTML = `<img src="${sighting.photo_url}" class="w-full h-full object-cover" />`;
                } else {
                    photoContainer.innerHTML = '<span class="text-xs">No Photo</span>';
                }

                currentSpeciesName.textContent = sighting.species ? sighting.species.common_name : 'Belum Teridentifikasi';
                const dateStr = sighting.created_at ? new Date(sighting.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
                sightingDateInfo.textContent = `Waktu Scan: ${dateStr}`;

                if (sighting.plant_species_id) {
                    speciesSelect.value = sighting.plant_species_id;
                }
                latInput.value = sighting.latitude || '';
                lngInput.value = sighting.longitude || '';
            }

        } catch (err) {
            console.error('Error loading data:', err);
            alert('Gagal memuat data temuan: ' + err.message);
        }

        // Handle Submit Form
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const updatedData = {
                plant_species_id: speciesSelect.value,
                latitude: latInput.value ? parseFloat(latInput.value) : null,
                longitude: lngInput.value ? parseFloat(lngInput.value) : null,
            };

            try {
                await apiClient.put(`/ranger/sightings/${sightingId}`, updatedData);
                alert('Data temuan tumbuhan berhasil diperbarui!');
                window.location.href = "{{ route('ranger.sightings.index') }}";
            } catch (err) {
                alert('Gagal memperbarui temuan: ' + (err.response?.data?.message || err.message));
            }
        });

        // Handle Delete
        deleteBtn.addEventListener('click', async () => {
            if (confirm('Apakah Anda yakin ingin menghapus temuan tumbuhan ini dari peta?')) {
                try {
                    await apiClient.delete(`/ranger/sightings/${sightingId}`);
                    alert('Data temuan berhasil dihapus.');
                    window.location.href = "{{ route('ranger.sightings.index') }}";
                } catch (err) {
                    alert('Gagal menghapus temuan: ' + (err.response?.data?.message || err.message));
                }
            }
        });
    });
</script>
@endpush
