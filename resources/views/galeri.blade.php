@extends('layouts.app')

@section('title', 'Seedex — Koleksi Tumbuhan & Benih')

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex items-center justify-between gap-3">
        <div>
            <span class="text-xs font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">HERBARIUM DIGITAL</span>
            <h1 class="font-baloo font-extrabold text-2xl sm:text-3xl text-[#1F3D20]">Koleksi Seedex</h1>
        </div>
        <div class="flex items-center gap-2">
            <!-- Achievement Button inside Plants Menu -->
            <button id="open-achievement-modal-btn" class="px-3.5 py-1.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] hover:bg-[#2D4A2E] font-baloo font-bold text-xs flex items-center gap-1.5 transition-colors cursor-pointer shadow-xs">
                <span>🏆</span>
                <span>Achievement</span>
            </button>
            <div class="hidden sm:block px-3 py-1 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold text-xs">
                SEEDEX ALBUM
            </div>
        </div>
    </div>

    <!-- Seedex Progress Bar Card Signature -->
    <div class="card-gg p-5 space-y-3">
        <div class="flex items-center justify-between font-baloo font-bold text-sm text-[#1F3D20]">
            <span id="seedex-progress-text">0 / 0 Seedex Ditemukan</span>
            <span class="text-xs text-[#6B6B55]">Koleksi Kamu</span>
        </div>

        <div class="progress-bar-gg">
            <div id="seedex-progress-bar" class="progress-fill-gg" style="width: 0%;"></div>
        </div>
    </div>

    <!-- Gallery Grid Container -->
    <div id="gallery-container" class="min-h-[50vh]">
        <!-- Dynamic Seedex Cards rendered by gallery.js -->
    </div>
</div>

<!-- Achievement Viewer Modal -->
<div id="achievement-modal" class="fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
    <div class="card-gg max-w-lg w-full p-6 shadow-2xl space-y-5 bg-[#FBFAF0] max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#1F3D20] text-[#F5F4DA] flex items-center justify-center text-xl">
                    🏆
                </div>
                <div>
                    <h3 class="font-baloo font-extrabold text-2xl text-[#1F3D20]">Achievement Viewer</h3>
                    <p class="font-nunito text-xs text-[#6B6B55]">Koleksi lencana dan pencapaian eksplorasi floramu.</p>
                </div>
            </div>
            <button id="achievement-modal-close-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
        </div>

        <!-- Badges Grid List -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
            <!-- Badge 1 -->
            <div class="p-3.5 rounded-2xl bg-[#FBFAF0] border border-[#1F3D20]/15 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#1F3D20] text-2xl flex items-center justify-center shadow-xs">
                    🌱
                </div>
                <div>
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">Penjelajah Flora</h4>
                    <p class="text-[11px] text-[#6B6B55]">Temukan spesies tumbuhan pertama di Peta.</p>
                    <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20]">Terselesaikan ✨</span>
                </div>
            </div>

            <!-- Badge 2 -->
            <div class="p-3.5 rounded-2xl bg-[#FBFAF0] border border-[#1F3D20]/15 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#8B6A4C] text-2xl flex items-center justify-center shadow-xs">
                    ♻️
                </div>
                <div>
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">Pahlawan Kompos</h4>
                    <p class="text-[11px] text-[#6B6B55]">Selesaikan proses pembuatan kompos organik.</p>
                    <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20]">Terselesaikan ✨</span>
                </div>
            </div>

            <!-- Badge 3 -->
            <div class="p-3.5 rounded-2xl bg-[#FBFAF0] border border-[#1F3D20]/15 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#2E6DA4] text-2xl flex items-center justify-center shadow-xs">
                    🌳
                </div>
                <div>
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">Penanam Sejati</h4>
                    <p class="text-[11px] text-[#6B6B55]">Kirim bukti penanaman pohon nyata di lapangan.</p>
                    <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20]">Terselesaikan ✨</span>
                </div>
            </div>

            <!-- Badge 4 -->
            <div class="p-3.5 rounded-2xl bg-[#FBFAF0] border border-[#1F3D20]/15 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#7D5BA6] text-2xl flex items-center justify-center shadow-xs">
                    📚
                </div>
                <div>
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">Pakar Seedex</h4>
                    <p class="text-[11px] text-[#6B6B55]">Kumpulkan 5+ spesies tumbuhan di album Seedex.</p>
                    <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#E7E6BE] text-[#6B6B55]">Sedang Berjalan</span>
                </div>
            </div>
        </div>

        <div class="pt-2 text-center">
            <button id="achievement-modal-done-btn" class="w-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold py-2.5 rounded-full text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer">
                Tutup Achievement
            </button>
        </div>
    </div>
</div>

<!-- Seedex Item Detail Modal -->
<div id="gallery-modal" class="fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
    <div class="card-gg max-w-md w-full p-6 shadow-2xl space-y-4 bg-[#FBFAF0] max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
            <div>
                <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#1F3D20] px-2.5 py-0.5 rounded-full uppercase">DETAIL SEEDEX</span>
                <h3 id="modal-title" class="font-baloo font-extrabold text-2xl text-[#1F3D20] mt-1">Detail Spesies</h3>
                <p id="modal-scientific" class="font-nunito text-xs text-[#6B6B55] italic"></p>
            </div>
            <button id="modal-close-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
        </div>

        <!-- Foto Spesimen -->
        <div class="h-48 rounded-2xl overflow-hidden bg-[#1F3D20] border border-[#1F3D20]/10">
            <img id="modal-img" src="" class="w-full h-full object-cover" />
        </div>

        <!-- Deskripsi Spesies -->
        <p id="modal-desc" class="text-xs text-[#6B6B55] leading-relaxed bg-[#E2E1C4]/40 p-3.5 rounded-2xl"></p>

        <!-- Cara Merawat Pohon / Care Instructions Section -->
        <div class="bg-[#FBFAF0] rounded-2xl p-4 border border-[#1F3D20]/15 space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-[#1F3D20] text-[#F5F4DA] flex items-center justify-center font-bold text-xs">🌱</div>
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">Cara Merawat Pohon</h4>
                </div>
                <button id="toggle-care-edit-btn" class="hidden text-xs font-baloo font-bold text-[#8B6A4C] hover:underline cursor-pointer">
                    ✏️ Edit Petunjuk
                </button>
            </div>

            <p id="modal-care-text" class="text-xs font-nunito text-[#2A2A22] leading-relaxed italic bg-[#E2E1C4]/40 p-3 rounded-xl border border-[#1F3D20]/5">
                Belum ada petunjuk perawatan dari Ranger.
            </p>

            <!-- Form Edit Petunjuk Perawatan oleh Ranger -->
            <div id="care-edit-form" class="hidden space-y-2.5 pt-2 border-t border-[#1F3D20]/10">
                <label class="block text-xs font-baloo font-bold text-[#1F3D20]">Petunjuk Perawatan Ranger:</label>
                <textarea id="care-instructions-input" rows="3" class="w-full text-xs font-nunito p-3 rounded-xl border border-[#1F3D20]/20 bg-white focus:outline-none focus:border-[#1F3D20]" placeholder="Contoh: Siram 2x sehari pada pagi dan sore. Beri pupuk kompos setiap 2 minggu sekali dan tempatkan di lokasi dengan sinar matahari cukup."></textarea>
                <div class="flex gap-2 justify-end">
                    <button id="cancel-care-edit-btn" type="button" class="px-3 py-1.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs cursor-pointer">Batal</button>
                    <button id="save-care-edit-btn" type="button" class="px-4 py-1.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs cursor-pointer">Simpan Petunjuk</button>
                </div>
            </div>
        </div>

        <div class="pt-2">
            <button id="modal-delete-btn" class="w-full bg-[#C0392B]/10 hover:bg-[#C0392B]/20 text-[#C0392B] font-baloo font-bold py-2.5 rounded-full text-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Hapus dari Seedex
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', async () => {
        if (window.GalleryModule) {
            const gallery = new window.GalleryModule({
                containerElement: document.querySelector('#gallery-container'),
                modalElement: document.querySelector('#gallery-modal'),
                progressTextElement: document.querySelector('#seedex-progress-text'),
                progressBarElement: document.querySelector('#seedex-progress-bar')
            });
            await gallery.loadGallery();

            document.querySelector('#modal-close-btn').addEventListener('click', () => {
                document.querySelector('#gallery-modal').classList.add('hidden');
            });
        }

        // Achievement Modal Handlers inside Plants Page
        const achievementModal = document.querySelector('#achievement-modal');
        const openAchievementBtn = document.querySelector('#open-achievement-modal-btn');
        const closeAchievementBtn = document.querySelector('#achievement-modal-close-btn');
        const doneAchievementBtn = document.querySelector('#achievement-modal-done-btn');

        if (openAchievementBtn && achievementModal) {
            openAchievementBtn.addEventListener('click', () => {
                achievementModal.classList.remove('hidden');
            });
        }

        [closeAchievementBtn, doneAchievementBtn].forEach(btn => {
            if (btn && achievementModal) {
                btn.addEventListener('click', () => {
                    achievementModal.classList.add('hidden');
                });
            }
        });
    });
</script>
@endpush
