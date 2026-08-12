@extends('layouts.app')

@php
    $isRangerOrAdmin = auth()->user() && in_array(auth()->user()->role, ['ranger', 'admin']);
@endphp

@section('title', $isRangerOrAdmin ? __('gallery.ranger_title') : __('gallery.title'))

@push('scripts')
<script>
    window.translations = Object.assign(window.translations || {}, @json(__('gallery')));
</script>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Top Header -->
    <div class="flex items-center justify-between gap-3">
        <div>
            <span class="text-xs font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">
                {{ $isRangerOrAdmin ? __('gallery.ranger_subheading') : __('gallery.herbarium_digital') }}
            </span>
            <h1 class="font-baloo font-extrabold text-2xl sm:text-3xl text-[#1F3D20]">
                {{ $isRangerOrAdmin ? __('gallery.ranger_heading') : __('gallery.heading') }}
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <!-- Achievement Button inside Plants Menu -->
            <a href="{{ route('achievement') }}" class="px-3.5 py-1.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] hover:bg-[#2D4A2E] font-baloo font-bold text-xs flex items-center gap-1.5 transition-colors cursor-pointer shadow-xs">
                <span>🏆</span>
                <span>Achievement</span>
            </a>
            <div class="hidden sm:block px-3 py-1 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold text-xs">
                {{ $isRangerOrAdmin ? __('gallery.ranger_badge') : __('gallery.seedex_album') }}
            </div>
        </div>
    </div>

    <!-- Seedex / Ranger Progress Bar Card Signature -->
    <div class="card-gg p-5 space-y-3">
        <div class="flex items-center justify-between font-baloo font-bold text-sm text-[#1F3D20]">
            <span id="seedex-progress-text">
                0 {{ $isRangerOrAdmin ? __('gallery.ranger_uploaded') : ('/ 0 ' . __('gallery.discovered')) }}
            </span>
            <span class="text-xs text-[#6B6B55]">
                {{ $isRangerOrAdmin ? __('gallery.ranger_your_uploads') : __('gallery.your_collection') }}
            </span>
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
<div id="achievement-modal" class="fixed inset-0 bg-[#1F3D20]/85 backdrop-blur-md z-[100] flex items-center justify-center p-4 sm:p-6 hidden overflow-y-auto">
    <div class="card-gg max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-5 bg-[#FBFAF0] my-auto max-h-[85vh] overflow-y-auto">
        <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#1F3D20] text-[#F5F4DA] flex items-center justify-center text-xl">
                    🏆
                </div>
                <div>
                    <h3 class="font-baloo font-extrabold text-2xl text-[#1F3D20]">{{ __('gallery.achievement_title') }}</h3>
                    <p class="font-nunito text-xs text-[#6B6B55]">{{ __('gallery.achievement_subtitle') }}</p>
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
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">{{ __('achievement.cards.flora_explorer.title') }}</h4>
                    <p class="text-[11px] text-[#6B6B55]">{{ __('achievement.cards.flora_explorer.desc') }}</p>
                    <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20]">{{ __('achievement.status.completed') }}</span>
                </div>
            </div>

            <!-- Badge 2 -->
            <div class="p-3.5 rounded-2xl bg-[#FBFAF0] border border-[#1F3D20]/15 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#8B6A4C] text-2xl flex items-center justify-center shadow-xs">
                    ♻️
                </div>
                <div>
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">{{ __('achievement.cards.digital_farmer.title') }}</h4>
                    <p class="text-[11px] text-[#6B6B55]">{{ __('achievement.cards.digital_farmer.desc') }}</p>
                    <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20]">{{ __('achievement.status.completed') }}</span>
                </div>
            </div>

            <!-- Badge 3 -->
            <div class="p-3.5 rounded-2xl bg-[#FBFAF0] border border-[#1F3D20]/15 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#2E6DA4] text-2xl flex items-center justify-center shadow-xs">
                    🌳
                </div>
                <div>
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">{{ __('achievement.cards.region_mapper.title') }}</h4>
                    <p class="text-[11px] text-[#6B6B55]">{{ __('achievement.cards.region_mapper.desc') }}</p>
                    <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#E2E1C4] text-[#1F3D20]">{{ __('achievement.status.completed') }}</span>
                </div>
            </div>

            <!-- Badge 4 -->
            <div class="p-3.5 rounded-2xl bg-[#FBFAF0] border border-[#1F3D20]/15 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#7D5BA6] text-2xl flex items-center justify-center shadow-xs">
                    📚
                </div>
                <div>
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">{{ __('achievement.cards.seedex_expert.title') }}</h4>
                    <p class="text-[11px] text-[#6B6B55]">{{ __('achievement.cards.seedex_expert.desc') }}</p>
                    <span class="inline-block mt-1 text-[9px] font-bold px-2 py-0.5 rounded-full bg-[#E7E6BE] text-[#6B6B55]">{{ __('achievement.status.in_progress') }}</span>
                </div>
            </div>
        </div>

        <div class="pt-2 text-center">
            <button id="achievement-modal-done-btn" class="w-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold py-2.5 rounded-full text-xs hover:bg-[#2D4A2E] transition-colors cursor-pointer">
                {{ __('gallery.close_modal') }}
            </button>
        </div>
    </div>
</div>

<!-- Seedex Item Detail Modal -->
<div id="gallery-modal" class="fixed inset-0 bg-[#1F3D20]/85 backdrop-blur-md z-[100] flex items-center justify-center p-4 sm:p-6 hidden overflow-y-auto">
    <div class="card-gg max-w-md w-full p-5 sm:p-6 shadow-2xl space-y-4 bg-[#FBFAF0] my-auto max-h-[85vh] overflow-y-auto">
        <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
            <div>
                <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#1F3D20] px-2.5 py-0.5 rounded-full uppercase">{{ __('gallery.modal_detail') }}</span>
                <h3 id="modal-title" class="font-baloo font-extrabold text-2xl text-[#1F3D20] mt-1">{{ __('gallery.modal_detail') }}</h3>
                <p id="modal-scientific" class="font-nunito text-xs text-[#6B6B55] italic"></p>
            </div>
            <button id="modal-close-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] hover:bg-[#1F3D20] hover:text-[#F5F4DA] border border-[#1F3D20]/15 flex items-center justify-center cursor-pointer transition-all duration-200 p-0 shrink-0 shadow-xs" title="Tutup" aria-label="Tutup">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
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
                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">{{ __('gallery.care_instructions') }}</h4>
                </div>
                <button id="toggle-care-edit-btn" class="hidden text-xs font-baloo font-bold text-[#8B6A4C] hover:underline cursor-pointer">
                    ✏️ {{ __('gallery.edit_instructions') }}
                </button>
            </div>

            <p id="modal-care-text" class="text-xs font-nunito text-[#2A2A22] leading-relaxed italic bg-[#E2E1C4]/40 p-3 rounded-xl border border-[#1F3D20]/5">
                {{ __('gallery.no_care_instructions') }}
            </p>

            <!-- Form Edit Petunjuk Perawatan oleh Ranger -->
            <div id="care-edit-form" class="hidden space-y-2.5 pt-2 border-t border-[#1F3D20]/10">
                <label class="block text-xs font-baloo font-bold text-[#1F3D20]">{{ __('gallery.care_instructions') }}:</label>
                <textarea id="care-instructions-input" rows="3" class="w-full text-xs font-nunito p-3 rounded-xl border border-[#1F3D20]/20 bg-white focus:outline-none focus:border-[#1F3D20]" placeholder="..."></textarea>
                <div class="flex gap-2 justify-end">
                    <button id="cancel-care-edit-btn" type="button" class="px-3 py-1.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs cursor-pointer">{{ __('gallery.cancel') }}</button>
                    <button id="save-care-edit-btn" type="button" class="px-4 py-1.5 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs cursor-pointer">{{ __('gallery.save_instructions') }}</button>
                </div>
            </div>
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
