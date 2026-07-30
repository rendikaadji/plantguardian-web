@extends('layouts.app')

@section('title', ($speciesId ? 'Edit Spesies' : 'Tambah Spesies Baru') . ' — Ranger PlantGuardian')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header Page -->
    <div class="border-b border-[#5C574C]/30 pb-4">
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('ranger.species.index') }}" class="font-mono-code text-xs text-[#5C574C] hover:underline" style="font-family: 'IBM Plex Mono', monospace;">← KEMBALI KE KATALOG SPESIES</a>
        </div>
        <h1 class="font-serif-headline text-2xl font-bold" style="font-family: 'Fraunces', Georgia, serif; color: #2F4A3C !important;">
            {{ $speciesId ? 'Mengisi Ulang Kartu Arsip Spesies' : 'Pendaftaran Spesies Tumbuhan Baru' }}
        </h1>
        <p class="text-xs text-[#5C574C]">
            Isi formulir spesimen dengan lengkap. Kode spesies harus unik (UPPER_SNAKE_CASE).
        </p>
    </div>

    <!-- Form Container -->
    <form id="species-form" class="space-y-4 p-6 border-2 border-[#5C574C]/30 rounded-xs shadow-xs" style="background-color: #EDE6D3 !important;">
        <input type="hidden" id="species-id" value="{{ $speciesId ?? '' }}">

        <div class="space-y-1">
            <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                KODE SPESIES (AI CODE) <span class="text-[#8B3A3A]">*</span>
            </label>
            <input type="text" id="species_code" name="species_code" placeholder="Contoh: MANGIFERA_INDICA" required class="w-full px-3 py-2 border border-[#5C574C]/40 rounded-xs font-mono-code text-sm uppercase focus:outline-none focus:border-[#2F4A3C]" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important;">
            <p class="text-[10px] text-[#5C574C]">Kode ini dicocokkan otomatis oleh Python AI Service (`predicted_species_code`).</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
                <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                    NAMA UMUM <span class="text-[#8B3A3A]">*</span>
                </label>
                <input type="text" id="common_name" name="common_name" placeholder="Contoh: Pohon Mangga" required class="w-full px-3 py-2 border border-[#5C574C]/40 rounded-xs text-sm focus:outline-none focus:border-[#2F4A3C]" style="background-color: #E3DABF !important;">
            </div>

            <div class="space-y-1">
                <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                    NAMA ILMIAH
                </label>
                <input type="text" id="scientific_name" name="scientific_name" placeholder="Contoh: Mangifera indica" class="w-full px-3 py-2 border border-[#5C574C]/40 rounded-xs text-sm italic focus:outline-none focus:border-[#2F4A3C]" style="background-color: #E3DABF !important;">
            </div>
        </div>

        <div class="space-y-1">
            <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                STATUS KONSERVASI
            </label>
            <input type="text" id="conservation_status" name="conservation_status" placeholder="Contoh: Risiko Rendah (Least Concern / LC)" class="w-full px-3 py-2 border border-[#5C574C]/40 rounded-xs text-sm focus:outline-none focus:border-[#2F4A3C]" style="background-color: #E3DABF !important;">
        </div>

        <div class="space-y-1">
            <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                DESKRIPSI & EDUKASI <span class="text-[#8B3A3A]">*</span>
            </label>
            <textarea id="description" name="description" rows="3" required placeholder="Tuliskan penjelasan edukatif mengenai ciri fisik, habitat, dan manfaat tumbuhan ini..." class="w-full px-3 py-2 border border-[#5C574C]/40 rounded-xs text-sm focus:outline-none focus:border-[#2F4A3C]" style="background-color: #E3DABF !important;"></textarea>
        </div>

        <div class="space-y-1">
            <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                🌱 CARA MERAWAT POHON (CARE INSTRUCTIONS)
            </label>
            <textarea id="care_instructions" name="care_instructions" rows="3" placeholder="Tuliskan panduan penyiraman, pemupukan kompos, dan pencahayaan agar viewer dapat merawat tumbuhan ini..." class="w-full px-3 py-2 border border-[#5C574C]/40 rounded-xs text-sm focus:outline-none focus:border-[#2F4A3C]" style="background-color: #E3DABF !important;"></textarea>
        </div>

        <div class="space-y-1">
            <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                FOTO REFERENSI (OPSIONAL)
            </label>
            <input type="file" id="reference_image" name="reference_image" accept="image/jpeg,image/png,image/webp" class="w-full text-xs text-[#5C574C] file:mr-4 file:py-2 file:px-4 file:rounded-xs file:border-0 file:text-xs file:font-mono-code file:font-bold file:bg-[#2F4A3C] file:text-[#EDE6D3] hover:file:bg-[#2A2823]">
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#5C574C]/20">
            <a href="{{ route('ranger.species.index') }}" class="px-4 py-2 border border-[#5C574C] text-[#5C574C] font-mono-code text-xs font-bold rounded-xs hover:bg-[#E3DABF]" style="font-family: 'IBM Plex Mono', monospace;">BATAL</a>
            <button type="submit" id="btn-save" class="px-6 py-2 bg-[#2F4A3C] text-[#EDE6D3] font-mono-code text-xs font-bold rounded-xs uppercase tracking-wider hover:bg-[#2A2823] transition-all cursor-pointer" style="font-family: 'IBM Plex Mono', monospace;">
                SIMPAN SPESIES
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script type="module">
    import apiClient from '/resources/js/api-client.js';

    document.addEventListener('DOMContentLoaded', async () => {
        const speciesId = document.querySelector('#species-id').value;
        const form = document.querySelector('#species-form');

        if (speciesId) {
            try {
                const res = await apiClient.get(`/ranger/species/${speciesId}`);
                const item = res.data;
                document.querySelector('#species_code').value = item.species_code || '';
                document.querySelector('#common_name').value = item.common_name || '';
                document.querySelector('#scientific_name').value = item.scientific_name || '';
                document.querySelector('#conservation_status').value = item.conservation_status || '';
                document.querySelector('#description').value = item.description || '';
                document.querySelector('#care_instructions').value = item.care_instructions || '';
            } catch (err) {
                alert('Gagal mengambil data spesies: ' + err.message);
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btnSave = document.querySelector('#btn-save');
            btnSave.disabled = true;
            btnSave.textContent = 'MENYIMPAN...';

            const formData = new FormData(form);
            if (!formData.get('reference_image')?.size) {
                formData.delete('reference_image');
            }

            try {
                if (speciesId) {
                    formData.append('_method', 'PUT');
                    await apiClient.post(`/ranger/species/${speciesId}`, formData, true);
                    alert('Data spesies berhasil diperbarui!');
                } else {
                    await apiClient.post('/ranger/species', formData, true);
                    alert('Spesies baru berhasil ditambahkan ke katalog!');
                }
                window.location.href = "{{ route('ranger.species.index') }}";
            } catch (err) {
                alert('Gagal menyimpan spesies: ' + err.message);
                btnSave.disabled = false;
                btnSave.textContent = 'SIMPAN SPESIES';
            }
        });
    });
</script>
@endpush
