@extends('layouts.app')

@section('title', __('map.title'))

@push('scripts')
<!-- Leaflet.js CSS & JS CDN -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    window.translations = Object.assign(window.translations || {}, @json(__('map')));
</script>
@endpush

@section('content')
<div class="space-y-4 sm:space-y-6 w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4">
        <div>
            <span class="text-xs font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">{{ __('map.radar_exploration') }}</span>
            <h1 class="font-baloo font-extrabold text-2xl sm:text-3xl text-[#1F3D20]">
                {{ in_array(auth()->user()->role, ['ranger', 'admin']) ? __('map.heading_ranger') : __('map.heading_viewer') }}
            </h1>
        </div>
        <div class="px-3 py-1 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold text-xs self-start sm:self-auto shadow-xs">
            {{ auth()->user()->role === 'admin' ? __('map.mode_admin') : (auth()->user()->role === 'ranger' ? __('map.mode_ranger') : __('map.mode_viewer')) }}
        </div>
    </div>

    <!-- Map Container Container (Responsive Full-Width & Dynamic Screen Height) -->
    <div class="relative w-full h-[64vh] sm:h-[74vh] lg:h-[78vh] min-h-[420px] max-h-[780px] rounded-3xl overflow-hidden shadow-lg card-gg p-1 border-2 border-[#1F3D20]/15">
        <div id="leaflet-map" class="w-full h-full rounded-2xl z-0"></div>

        <!-- Floating Controls: Auto-Follow & Map Rotation Toggle Buttons -->
        <div class="absolute top-4 right-4 z-20 flex flex-col items-end gap-2">
            <button id="recenter-gps-btn" class="px-3.5 py-2 bg-[#1F3D20] text-[#F5F4DA] backdrop-blur-md rounded-full border border-[#F5F4DA]/30 shadow-xl font-baloo font-bold text-xs flex items-center gap-1.5 hover:bg-[#2D4A2E] active:scale-95 transition-all cursor-pointer">
                <span class="animate-pulse text-sm">🎯</span>
                <span id="recenter-gps-label">Auto-Follow On</span>
            </button>
            <button id="toggle-rotation-btn" class="px-3.5 py-2 bg-[#1F3D20] text-[#F5F4DA] backdrop-blur-md rounded-full border border-[#F5F4DA]/30 shadow-xl font-baloo font-bold text-xs flex items-center gap-1.5 hover:bg-[#2D4A2E] active:scale-95 transition-all cursor-pointer">
                <span id="toggle-rotation-label">Mode: Arah Jalan 🧭</span>
            </button>
        </div>

        <!-- Floating Action Controls - FOR RANGER & ADMIN ROLES -->
        @if(in_array(auth()->user()->role, ['ranger', 'admin']))
            <div class="absolute bottom-4 sm:bottom-6 left-1/2 -translate-x-1/2 z-20 px-3 py-1.5 bg-[#1F3D20]/90 backdrop-blur-md rounded-full border border-[#F5F4DA]/20 shadow-2xl flex items-center gap-2 sm:gap-3 max-w-[95vw] sm:max-w-none">
                <button id="open-ar-btn" class="px-3.5 py-2 rounded-full bg-[#F5F4DA] text-[#1F3D20] font-baloo font-extrabold text-xs flex items-center gap-1.5 shadow-sm hover:bg-white transition-all cursor-pointer whitespace-nowrap active:scale-95">
                    <span>📷</span>
                    <span>{{ __('map.open_ar_camera') }}</span>
                </button>

                <button id="open-verif-btn" class="px-3.5 py-2 rounded-full bg-[#8B6A4C] text-[#FBFAF0] font-baloo font-bold text-xs flex items-center gap-1.5 shadow-sm hover:bg-[#a67e5a] transition-all cursor-pointer whitespace-nowrap active:scale-95 border border-[#FBFAF0]/20">
                    <span>📋</span>
                    <span>{{ __('map.moderation_queue') }}</span>
                    <span id="verif-badge-count" class="px-2 py-0.5 rounded-full bg-[#FBFAF0] text-[#8B6A4C] font-extrabold text-[10px]">0</span>
                </button>
            </div>
        @endif
    </div>
</div>

<!-- AR Scanner Modal View (Ranger & Admin) -->
@if(in_array(auth()->user()->role, ['ranger', 'admin']))
    <div id="ar-modal" class="fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
        <div class="card-gg max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-4 relative bg-[#FBFAF0]">
            <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
                <div>
                    <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#1F3D20] px-2.5 py-0.5 rounded-full uppercase">{{ __('map.ar_scan_mode') }}</span>
                    <h3 class="font-baloo font-extrabold text-xl text-[#1F3D20] mt-1">{{ __('map.computer_vision_scan') }}</h3>
                </div>
                <button id="close-ar-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] hover:bg-[#1F3D20] hover:text-[#F5F4DA] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <!-- Video Stream Container -->
            <div class="relative aspect-4/3 bg-[#1F3D20] rounded-2xl overflow-hidden shadow-inner">
                <video id="ar-video" class="w-full h-full object-cover" autoplay playsinline muted></video>

                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-48 h-48 border-2 border-dashed border-[#F5F4DA]/80 rounded-2xl flex items-center justify-center">
                        <span class="bg-[#1F3D20]/80 text-[#F5F4DA] px-3 py-1 font-baloo text-xs rounded-full">{{ __('map.point_at_plant') }}</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons: Camera & Gallery Upload -->
            <div class="space-y-2.5">
                <button id="scan-trigger-btn" class="w-full btn-gg-primary py-3 rounded-full flex items-center justify-center gap-2 cursor-pointer shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="2"/></svg>
                    <span>{{ __('map.take_specimen_photo') }}</span>
                </button>

                <div class="relative flex py-0.5 items-center">
                    <div class="flex-grow border-t border-[#1F3D20]/15"></div>
                    <span class="shrink-0 mx-2 text-[10px] text-[#6B6B55] font-baloo font-bold uppercase">ATAU PILIH DARI GALERI HP</span>
                    <div class="flex-grow border-t border-[#1F3D20]/15"></div>
                </div>

                <label class="w-full py-2.5 px-4 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs flex items-center justify-center gap-2 cursor-pointer hover:bg-[#d5d4b3] transition-colors border border-[#1F3D20]/20 text-center shadow-xs">
                    <span>📁 Upload Foto dari HP / Galeri</span>
                    <input type="file" id="gallery-file-input" accept="image/*" class="hidden">
                </label>
            </div>
        </div>
    </div>

    <!-- Scan Result & Plant Data Form Modal (Ranger & Admin On-Site Data Entry) -->
    <div id="scan-result-modal" class="fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden overflow-y-auto">
        <div class="card-gg max-w-lg w-full p-6 shadow-2xl space-y-4 bg-[#FBFAF0] my-auto max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
                <div>
                    <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#1F3D20] px-2.5 py-0.5 rounded-full uppercase">{{ __('map.field_species_form') }}</span>
                    <h3 class="font-baloo font-extrabold text-2xl text-[#1F3D20] mt-1">{{ __('map.field_plant_data') }}</h3>
                </div>
                <button id="close-result-modal-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <!-- Preview Photo Captured / File Upload Option -->
            <div class="space-y-2">
                <div class="h-40 rounded-2xl overflow-hidden bg-[#1F3D20] relative border border-[#1F3D20]/20 flex items-center justify-center">
                    <img id="result-img" src="" class="w-full h-full object-cover hidden" />
                    <div id="no-photo-placeholder" class="text-center p-3">
                        <span class="text-3xl block mb-1">📷</span>
                        <span class="text-xs text-[#F5F4DA] font-baloo font-bold block">Belum Ada Foto Tumbuhan</span>
                        <span class="text-[10px] text-[#F5F4DA]/70 font-nunito block">Klik tombol di bawah untuk memilih foto dari HP</span>
                    </div>
                    <span id="photo-badge" class="absolute bottom-2 left-2 bg-[#1F3D20]/80 text-[#F5F4DA] text-[10px] px-2.5 py-0.5 rounded-full font-baloo font-bold hidden">{{ __('map.live_photo') }}</span>
                </div>

                <label class="w-full py-2.5 px-4 rounded-xl bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs flex items-center justify-center gap-2 cursor-pointer hover:bg-[#d5d4b3] transition-colors border border-[#1F3D20]/20 text-center shadow-xs">
                    <span>📸 Pilih / Upload Foto Tumbuhan dari HP</span>
                    <input type="file" id="direct-modal-file-input" accept="image/*" class="hidden">
                </label>
            </div>

            <!-- Live Form Inputs -->
            <form id="ranger-live-plant-form" class="space-y-3 pt-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">{{ __('map.common_name_label') }} <span class="text-red-600">*</span></label>
                        <input type="text" id="input-common-name" required class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" placeholder="{{ __('map.common_name_placeholder') }}" />
                    </div>
                    <div>
                        <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">{{ __('map.scientific_name_label') }}</label>
                        <input type="text" id="input-scientific-name" class="w-full p-2.5 text-xs font-nunito italic border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" placeholder="{{ __('map.scientific_name_placeholder') }}" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">{{ __('map.conservation_status_label') }}</label>
                    <select id="input-conservation-status" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]">
                        <option value="Common">{{ __('map.status_common') }}</option>
                        <option value="Vulnerable">{{ __('map.status_vulnerable') }}</option>
                        <option value="Endangered">{{ __('map.status_endangered') }}</option>
                        <option value="Protected">{{ __('map.status_protected') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">{{ __('map.description_label') }}</label>
                    <textarea id="input-description" rows="2" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" placeholder="{{ __('map.description_placeholder') }}"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">{{ __('map.care_instructions_label') }}</label>
                    <textarea id="input-care-instructions" rows="2" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" placeholder="{{ __('map.care_instructions_placeholder') }}"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" id="save-live-plant-btn" class="w-full btn-gg-primary py-3 rounded-full flex items-center justify-center gap-2 cursor-pointer shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ __('map.save_publish') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Direct On-Map Verification Queue Modal (Ranger & Admin) -->
    <div id="verification-modal" class="fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden overflow-y-auto">
        <div class="card-gg max-w-xl w-full p-6 shadow-2xl space-y-4 bg-[#FBFAF0] my-auto max-h-[85vh] overflow-y-auto">
            <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
                <div>
                    <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#1F3D20] px-2.5 py-0.5 rounded-full uppercase">{{ __('map.moderation_queue') }}</span>
                    <h3 class="font-baloo font-extrabold text-2xl text-[#1F3D20] mt-1">{{ __('map.moderation_title') }}</h3>
                </div>
                <button id="close-verif-modal-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <div id="verif-modal-list" class="space-y-3">
                <p class="text-xs text-[#6B6B55] text-center py-4 font-nunito">...</p>
            </div>
        </div>
    </div>

    <!-- Edit Marker Sighting Modal (For Ranger & Admin editing plant markers directly from map popups) -->
    <div id="edit-sighting-modal" class="fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden overflow-y-auto">
        <div class="card-gg max-w-lg w-full p-6 shadow-2xl space-y-4 bg-[#FBFAF0] my-auto max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
                <div>
                    <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#8B6A4C] px-2.5 py-0.5 rounded-full uppercase">{{ __('map.edit_sighting_tag') }}</span>
                    <h3 class="font-baloo font-extrabold text-2xl text-[#1F3D20] mt-1">{{ __('map.edit_plant_data') }}</h3>
                </div>
                <button id="close-edit-modal-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <!-- Preview Photo -->
            <div class="h-40 rounded-2xl overflow-hidden bg-[#1F3D20] relative border border-[#1F3D20]/20">
                <img id="edit-sighting-img" src="" class="w-full h-full object-cover" />
            </div>

            <!-- Form Edit Inputs -->
            <form id="ranger-edit-plant-form" class="space-y-3 pt-1">
                <input type="hidden" id="edit-sighting-id" value="" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">{{ __('map.common_name_label') }} <span class="text-red-600">*</span></label>
                        <input type="text" id="edit-common-name" required class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
                    </div>
                    <div>
                        <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">{{ __('map.scientific_name_label') }}</label>
                        <input type="text" id="edit-scientific-name" class="w-full p-2.5 text-xs font-nunito italic border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">{{ __('map.conservation_status_label') }}</label>
                    <select id="edit-conservation-status" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]">
                        <option value="Common">{{ __('map.status_common') }}</option>
                        <option value="Vulnerable">{{ __('map.status_vulnerable') }}</option>
                        <option value="Endangered">{{ __('map.status_endangered') }}</option>
                        <option value="Protected">{{ __('map.status_protected') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">{{ __('map.description_label') }}</label>
                    <textarea id="edit-description" rows="2" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">{{ __('map.care_instructions_label') }}</label>
                    <textarea id="edit-care-instructions" rows="2" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-between gap-3">
                    <button type="submit" id="update-sighting-btn" class="flex-1 btn-gg-primary py-3 rounded-full flex items-center justify-center gap-2 cursor-pointer shadow-md">
                        <span>{{ __('map.save_changes') }}</span>
                    </button>
                    <button type="button" id="delete-sighting-btn" class="px-4 py-3 rounded-full bg-red-600 text-white font-baloo font-bold text-xs hover:bg-red-700 transition-colors cursor-pointer">
                        <span>{{ __('map.delete_marker') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
    window.USER_ROLE = "{{ auth()->user()->role ?? 'viewer' }}";

    document.addEventListener('DOMContentLoaded', async () => {
        let mapManager = null;

        if (window.MapManager) {
            mapManager = new window.MapManager('leaflet-map', {
                userRole: window.USER_ROLE
            });
            await mapManager.init();

            window.addEventListener('resize', () => {
                if (mapManager && mapManager.map) {
                    mapManager.map.invalidateSize();
                }
            });
        }

        // AR Scanner, On-Map Verification Modal & Live Form logic for Ranger & Admin
        if (window.USER_ROLE === 'ranger' || window.USER_ROLE === 'admin') {
            const openVerifBtn = document.querySelector('#open-verif-btn');
            const verifModal = document.querySelector('#verification-modal');
            const closeVerifModalBtn = document.querySelector('#close-verif-modal-btn');
            const verifListEl = document.querySelector('#verif-modal-list');
            const verifBadgeCount = document.querySelector('#verif-badge-count');
            const t = window.translations || {};

            const fetchPendingVerifications = async () => {
                try {
                    const res = await window.apiClient.get('/ranger/verifications/pending');
                    const pendingList = res.data?.pending_sightings || res.pending_sightings || [];
                    if (verifBadgeCount) verifBadgeCount.textContent = pendingList.length;

                    if (!verifListEl) return;
                    if (pendingList.length === 0) {
                        verifListEl.innerHTML = `
                            <div class="p-6 text-center border border-dashed border-[#1F3D20]/20 rounded-2xl">
                                <p class="font-baloo font-bold text-base text-[#1F3D20]">${t.moderation_empty || 'Antrean Kosong ✨'}</p>
                                <p class="text-xs text-[#6B6B55] font-nunito">${t.moderation_empty_desc || 'Seluruh temuan tumbuhan telah diverifikasi.'}</p>
                            </div>
                        `;
                        return;
                    }

                    verifListEl.innerHTML = pendingList.map(item => `
                        <div class="p-4 border border-[#1F3D20]/15 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white shadow-xs">
                            <div class="flex items-start gap-3">
                                <div class="w-16 h-16 bg-[#E2E1C4] rounded-xl overflow-hidden shrink-0 flex items-center justify-center border border-[#1F3D20]/10">
                                    ${item.photo_path ? `<img src="/storage/${item.photo_path}" class="w-full h-full object-cover" />` : `<span class="text-[10px] text-[#6B6B55]">NO FOTO</span>`}
                                </div>
                                <div class="space-y-0.5">
                                    <span class="text-[10px] font-baloo font-bold text-[#8B6A4C] uppercase block">Pengguna: ${item.user?.name || 'Viewer'}</span>
                                    <h4 class="font-baloo font-bold text-sm text-[#1F3D20]">${item.plant_species ? item.plant_species.common_name : 'Spesies Lapangan'}</h4>
                                    <span class="text-[10px] text-[#6B6B55] block font-nunito">AI Confidence: ${item.confidence_score ? (item.confidence_score * 100).toFixed(0) + '%' : '-'}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 w-full sm:w-auto justify-end border-t sm:border-t-0 pt-2 sm:pt-0 border-[#1F3D20]/10">
                                <button onclick="window.decideMapSighting(${item.id}, 'verified')" class="px-3.5 py-1.5 bg-[#1F3D20] text-[#F5F4DA] font-baloo font-bold text-xs rounded-full hover:bg-[#2D4A2E] cursor-pointer">${t.verify_btn || 'VERIFIKASI'}</button>
                                <button onclick="window.decideMapSighting(${item.id}, 'rejected')" class="px-3.5 py-1.5 border border-red-600 text-red-600 font-baloo font-bold text-xs rounded-full hover:bg-red-600 hover:text-white cursor-pointer">${t.reject_btn || 'TOLAK'}</button>
                            </div>
                        </div>
                    `).join('');
                } catch (err) {
                    console.warn('Gagal memuat verifikasi map:', err.message);
                }
            };

            // Fetch initial pending count for badge
            fetchPendingVerifications();

            if (openVerifBtn && verifModal) {
                openVerifBtn.addEventListener('click', async () => {
                    verifModal.classList.remove('hidden');
                    await fetchPendingVerifications();
                });
            }

            if (closeVerifModalBtn && verifModal) {
                closeVerifModalBtn.addEventListener('click', () => verifModal.classList.add('hidden'));
            }

            window.decideMapSighting = async function(id, status) {
                try {
                    await window.apiClient.post(`/ranger/verifications/sightings/${id}`, { status });
                    alert(`Status berhasil diperbarui ke: ${status}`);
                    await fetchPendingVerifications();
                    if (mapManager) await mapManager.refreshMarkers();
                } catch (err) {
                    alert('Gagal memproses verifikasi: ' + err.message);
                }
            };

            // AR Camera Scanner logic
            if (window.ArScanner) {
                let scanner = null;
                let capturedBlob = null;
                let currentLat = -6.2088;
                let currentLng = 106.8456;

                const openArBtn = document.querySelector('#open-ar-btn');
                const arModal = document.querySelector('#ar-modal');
                const closeArBtn = document.querySelector('#close-ar-btn');
                const scanTriggerBtn = document.querySelector('#scan-trigger-btn');
                const galleryFileInput = document.querySelector('#gallery-file-input');

                const scanResultModal = document.querySelector('#scan-result-modal');
                const closeResultModalBtn = document.querySelector('#close-result-modal-btn');
                const liveForm = document.querySelector('#ranger-live-plant-form');
                const directModalFileInput = document.querySelector('#direct-modal-file-input');

                const updatePhotoPreview = (srcUrl) => {
                    const imgEl = document.querySelector('#result-img');
                    const placeholder = document.querySelector('#no-photo-placeholder');
                    const badge = document.querySelector('#photo-badge');

                    if (imgEl && srcUrl) {
                        imgEl.src = srcUrl;
                        imgEl.classList.remove('hidden');
                    }
                    if (placeholder) placeholder.classList.add('hidden');
                    if (badge) badge.classList.remove('hidden');
                };

                if (galleryFileInput) {
                    galleryFileInput.addEventListener('change', (e) => {
                        const file = e.target.files && e.target.files[0];
                        if (file) {
                            capturedBlob = file;
                            const reader = new FileReader();
                            reader.onload = (event) => {
                                updatePhotoPreview(event.target.result);
                                document.querySelector('#input-common-name').value = '';
                                document.querySelector('#input-scientific-name').value = '';
                                document.querySelector('#input-description').value = '';
                                document.querySelector('#input-care-instructions').value = '';

                                if (scanner) scanner.stop();
                                arModal.classList.add('hidden');
                                scanResultModal.classList.remove('hidden');
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }

                if (directModalFileInput) {
                    directModalFileInput.addEventListener('change', (e) => {
                        const file = e.target.files && e.target.files[0];
                        if (file) {
                            capturedBlob = file;
                            const reader = new FileReader();
                            reader.onload = (event) => {
                                updatePhotoPreview(event.target.result);
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }

                const syncCurrentCoordinates = () => {
                    return new Promise((resolve) => {
                        if (mapManager && mapManager.userLat !== null && mapManager.userLng !== null) {
                            currentLat = mapManager.userLat;
                            currentLng = mapManager.userLng;
                        }

                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(
                                (pos) => {
                                    currentLat = pos.coords.latitude;
                                    currentLng = pos.coords.longitude;
                                    if (mapManager && mapManager.addUserMarker) {
                                        mapManager.addUserMarker(currentLat, currentLng);
                                    }
                                    resolve({ lat: currentLat, lng: currentLng });
                                },
                                (err) => {
                                    console.warn('Gagal memperoleh GPS presisi, menggunakan fallback:', err.message);
                                    if ((currentLat === null || currentLat === -6.2088) && mapManager && mapManager.map) {
                                        const center = mapManager.map.getCenter();
                                        currentLat = center.lat;
                                        currentLng = center.lng;
                                    }
                                    resolve({ lat: currentLat, lng: currentLng });
                                },
                                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                            );
                        } else {
                            if ((currentLat === null || currentLat === -6.2088) && mapManager && mapManager.map) {
                                const center = mapManager.map.getCenter();
                                currentLat = center.lat;
                                currentLng = center.lng;
                            }
                            resolve({ lat: currentLat, lng: currentLng });
                        }
                    });
                };

                if (openArBtn) {
                    openArBtn.addEventListener('click', async () => {
                        await syncCurrentCoordinates();
                        arModal.classList.remove('hidden');
                        if (!scanner) {
                            scanner = new window.ArScanner({
                                videoElement: document.querySelector('#ar-video')
                            });
                        }
                        await scanner.init();
                    });
                }

                if (closeArBtn) {
                    closeArBtn.addEventListener('click', () => {
                        if (scanner) scanner.stop();
                        arModal.classList.add('hidden');
                    });
                }

                const dataURLtoBlob = (dataurl) => {
                    const arr = dataurl.split(',');
                    const mime = arr[0].match(/:(.*?);/)[1];
                    const bstr = atob(arr[1]);
                    let n = bstr.length;
                    const u8arr = new Uint8Array(n);
                    while (n--) {
                        u8arr[n] = bstr.charCodeAt(n);
                    }
                    return new Blob([u8arr], { type: mime });
                };

                if (scanTriggerBtn) {
                    scanTriggerBtn.addEventListener('click', async () => {
                        try {
                            scanTriggerBtn.disabled = true;
                            await syncCurrentCoordinates();

                            const base64Image = scanner.captureFrame();
                            capturedBlob = dataURLtoBlob(base64Image);
                            updatePhotoPreview(base64Image);

                            document.querySelector('#input-common-name').value = '';
                            document.querySelector('#input-scientific-name').value = '';
                            document.querySelector('#input-description').value = '';
                            document.querySelector('#input-care-instructions').value = '';

                            if (scanner) scanner.stop();
                            arModal.classList.add('hidden');
                            scanResultModal.classList.remove('hidden');

                        } catch (err) {
                            alert('Gagal mengambil foto: ' + (err.message || 'Terjadi kesalahan'));
                        } finally {
                            scanTriggerBtn.disabled = false;
                        }
                    });
                }

                if (liveForm) {
                    liveForm.addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const submitBtn = document.querySelector('#save-live-plant-btn');

                        if (!capturedBlob) {
                            alert('Harap pilih atau ambil foto tumbuhan terlebih dahulu dengan menekan tombol "Pilih / Upload Foto Tumbuhan"!');
                            return;
                        }

                        submitBtn.disabled = true;

                        try {
                            await syncCurrentCoordinates();

                            const formData = new FormData();
                            if (capturedBlob) {
                                formData.append('image', capturedBlob, capturedBlob.name || 'live_plant.jpg');
                            }
                            formData.append('latitude', currentLat);
                            formData.append('longitude', currentLng);
                            formData.append('common_name', document.querySelector('#input-common-name').value);
                            formData.append('scientific_name', document.querySelector('#input-scientific-name').value);
                            formData.append('conservation_status', document.querySelector('#input-conservation-status').value);
                            formData.append('description', document.querySelector('#input-description').value);
                            formData.append('care_instructions', document.querySelector('#input-care-instructions').value);

                            const res = await window.apiClient.post('/scan', formData, true);
                            const sighting = res.data?.data || res.data || res;

                            alert('Tumbuhan berhasil disimpan & dipublikasikan di lokasi peta saat ini!');
                            scanResultModal.classList.add('hidden');

                            if (mapManager) {
                                await mapManager.refreshMarkers();
                                if (sighting && sighting.latitude && sighting.longitude) {
                                    mapManager.map.setView([sighting.latitude, sighting.longitude], 16);
                                }
                            }
                        } catch (err) {
                            alert('Gagal menyimpan tumbuhan: ' + (err.response?.data?.message || err.message));
                        } finally {
                            submitBtn.disabled = false;
                        }
                    });
                }

                if (closeResultModalBtn) closeResultModalBtn.addEventListener('click', () => scanResultModal.classList.add('hidden'));
            }
        }
    });

    // Global popup trigger to open Edit Sighting Modal from Leaflet popup button (Ranger & Admin)
    window.openEditSightingModal = async function(sightingId) {
        const editModal = document.querySelector('#edit-sighting-modal');
        if (!editModal) return;

        try {
            const res = await window.apiClient.get(`/ranger/sightings/${sightingId}`);
            const sighting = res.data?.data || res.data || res;

            document.querySelector('#edit-sighting-id').value = sighting.id;
            document.querySelector('#edit-sighting-img').src = sighting.photo_url || '';
            document.querySelector('#edit-common-name').value = sighting.species ? sighting.species.common_name : '';
            document.querySelector('#edit-scientific-name').value = sighting.species ? sighting.species.scientific_name : '';
            document.querySelector('#edit-conservation-status').value = (sighting.species && sighting.species.conservation_status) ? sighting.species.conservation_status : 'Common';
            document.querySelector('#edit-description').value = sighting.species ? sighting.species.description : '';
            document.querySelector('#edit-care-instructions').value = sighting.species ? sighting.species.care_instructions : '';

            editModal.classList.remove('hidden');
        } catch (err) {
            alert('Gagal memuat data temuan: ' + (err.response?.data?.message || err.message));
        }
    };

    // Attach listeners for Edit Modal submit & delete
    document.addEventListener('DOMContentLoaded', () => {
        const editModal = document.querySelector('#edit-sighting-modal');
        const closeEditModalBtn = document.querySelector('#close-edit-modal-btn');
        const editForm = document.querySelector('#ranger-edit-plant-form');
        const deleteSightingBtn = document.querySelector('#delete-sighting-btn');

        if (closeEditModalBtn) {
            closeEditModalBtn.addEventListener('click', () => editModal.classList.add('hidden'));
        }

        if (editForm) {
            editForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const sightingId = document.querySelector('#edit-sighting-id').value;
                const updateBtn = document.querySelector('#update-sighting-btn');
                updateBtn.disabled = true;

                try {
                    const payload = {
                        common_name: document.querySelector('#edit-common-name').value,
                        scientific_name: document.querySelector('#edit-scientific-name').value,
                        conservation_status: document.querySelector('#edit-conservation-status').value,
                        description: document.querySelector('#edit-description').value,
                        care_instructions: document.querySelector('#edit-care-instructions').value,
                    };

                    await window.apiClient.put(`/ranger/sightings/${sightingId}`, payload);
                    alert('Data tumbuhan berhasil diperbarui!');
                    editModal.classList.add('hidden');
                    window.location.reload();
                } catch (err) {
                    alert('Gagal memperbarui data: ' + (err.response?.data?.message || err.message));
                } finally {
                    updateBtn.disabled = false;
                }
            });
        }

        if (deleteSightingBtn) {
            deleteSightingBtn.addEventListener('click', async () => {
                const sightingId = document.querySelector('#edit-sighting-id').value;
                if (confirm('Apakah Anda yakin ingin menghapus marker temuan tumbuhan ini dari peta?')) {
                    try {
                        await window.apiClient.delete(`/ranger/sightings/${sightingId}`);
                        alert('Marker temuan tumbuhan berhasil dihapus dari peta.');
                        editModal.classList.add('hidden');
                        window.location.reload();
                    } catch (err) {
                        alert('Gagal menghapus temuan: ' + (err.response?.data?.message || err.message));
                    }
                }
            });
        }
    });
</script>
@endpush
