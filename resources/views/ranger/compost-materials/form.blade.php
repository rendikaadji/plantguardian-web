@extends('layouts.app')

@section('title', ($materialId ? 'Edit Bahan Kompos' : 'Tambah Bahan Kompos') . ' — Ranger PlantGuardian')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header Page -->
    <div class="border-b border-[#5C574C]/30 pb-4">
        <div class="flex items-center gap-2 mb-1">
            <a href="{{ route('ranger.compost-materials.index') }}" class="font-mono-code text-xs text-[#5C574C] hover:underline" style="font-family: 'IBM Plex Mono', monospace;">← KEMBALI KE KATALOG BAHAN KOMPOS</a>
        </div>
        <h1 class="font-serif-headline text-2xl font-bold" style="font-family: 'Fraunces', Georgia, serif; color: #2F4A3C !important;">
            {{ $materialId ? 'Mengisi Ulang Kartu Bahan Kompos' : 'Pendaftaran Bahan Kompos Baru' }}
        </h1>
        <p class="text-xs text-[#5C574C]">
            Isi data bahan kompos dan instruksi panduan langkah pembuatan secara mendetail.
        </p>
    </div>

    <!-- Form Container -->
    <form id="material-form" class="space-y-4 p-6 border-2 border-[#5C574C]/30 rounded-xs shadow-xs" style="background-color: #EDE6D3 !important;">
        <input type="hidden" id="material-id" value="{{ $materialId ?? '' }}">

        <div class="space-y-1">
            <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                KODE BAHAN (UPPER_SNAKE_CASE) <span class="text-[#8B3A3A]">*</span>
            </label>
            <input type="text" id="material_code" name="material_code" placeholder="Contoh: DAUN_KERING" required class="w-full px-3 py-2 border border-[#5C574C]/40 rounded-xs font-mono-code text-sm uppercase focus:outline-none focus:border-[#2F4A3C]" style="font-family: 'IBM Plex Mono', monospace; background-color: #E3DABF !important;">
        </div>

        <div class="space-y-1">
            <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                NAMA BAHAN KOMPOS <span class="text-[#8B3A3A]">*</span>
            </label>
            <input type="text" id="name" name="name" placeholder="Contoh: Daun Kering & Sisa Kebun" required class="w-full px-3 py-2 border border-[#5C574C]/40 rounded-xs text-sm focus:outline-none focus:border-[#2F4A3C]" style="background-color: #E3DABF !important;">
        </div>

        <div class="space-y-1">
            <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                DESKRIPSI BAHAN <span class="text-[#8B3A3A]">*</span>
            </label>
            <textarea id="description" name="description" rows="3" required placeholder="Jelaskan alasan kenapa bahan organik ini cocok untuk diolah menjadi kompos..." class="w-full px-3 py-2 border border-[#5C574C]/40 rounded-xs text-sm focus:outline-none focus:border-[#2F4A3C]" style="background-color: #E3DABF !important;"></textarea>
        </div>

        <div class="space-y-1">
            <label class="block font-mono-code text-xs font-bold text-[#2A2823] uppercase" style="font-family: 'IBM Plex Mono', monospace;">
                PANDUAN / INSTRUKSI LANGKAH PEMBUATAN <span class="text-[#8B3A3A]">*</span>
            </label>
            <textarea id="instructions" name="instructions" rows="5" required placeholder="Tuliskan instruksi langkah-demi-langkah dari pemotongan, kelembaban, hingga estimasi kematangan..." class="w-full px-3 py-2 border border-[#5C574C]/40 rounded-xs text-sm focus:outline-none focus:border-[#2F4A3C]" style="background-color: #E3DABF !important;"></textarea>
        </div>

        <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#5C574C]/20">
            <a href="{{ route('ranger.compost-materials.index') }}" class="px-4 py-2 border border-[#5C574C] text-[#5C574C] font-mono-code text-xs font-bold rounded-xs hover:bg-[#E3DABF]" style="font-family: 'IBM Plex Mono', monospace;">BATAL</a>
            <button type="submit" id="btn-save" class="px-6 py-2 bg-[#2F4A3C] text-[#EDE6D3] font-mono-code text-xs font-bold rounded-xs uppercase tracking-wider hover:bg-[#2A2823] transition-all cursor-pointer" style="font-family: 'IBM Plex Mono', monospace;">
                SIMPAN BAHAN KOMPOS
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script type="module">
    import apiClient from '/resources/js/api-client.js';

    document.addEventListener('DOMContentLoaded', async () => {
        const materialId = document.querySelector('#material-id').value;
        const form = document.querySelector('#material-form');

        if (materialId) {
            try {
                const res = await apiClient.get(`/ranger/compost-materials/${materialId}`);
                const item = res.data;
                document.querySelector('#material_code').value = item.material_code || '';
                document.querySelector('#name').value = item.name || '';
                document.querySelector('#description').value = item.description || '';
                document.querySelector('#instructions').value = item.instructions || '';
            } catch (err) {
                alert('Gagal mengambil bahan kompos: ' + err.message);
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btnSave = document.querySelector('#btn-save');
            btnSave.disabled = true;
            btnSave.textContent = 'MENYIMPAN...';

            const payload = {
                material_code: document.querySelector('#material_code').value,
                name: document.querySelector('#name').value,
                description: document.querySelector('#description').value,
                instructions: document.querySelector('#instructions').value,
            };

            try {
                if (materialId) {
                    await apiClient.put(`/ranger/compost-materials/${materialId}`, payload);
                    alert('Bahan kompos berhasil diperbarui!');
                } else {
                    await apiClient.post('/ranger/compost-materials', payload);
                    alert('Bahan kompos baru berhasil ditambahkan!');
                }
                window.location.href = "{{ route('ranger.compost-materials.index') }}";
            } catch (err) {
                alert('Gagal menyimpan bahan kompos: ' + err.message);
                btnSave.disabled = false;
                btnSave.textContent = 'SIMPAN BAHAN KOMPOS';
            }
        });
    });
</script>
@endpush
