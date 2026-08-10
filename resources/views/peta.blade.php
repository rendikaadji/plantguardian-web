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
<div class="space-y-3 sm:space-y-4 w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4 px-1">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-[#1F3D20] text-[#F5F4DA] flex items-center justify-center font-baloo font-bold text-xl shadow-md border border-[#F5F4DA]/20 shrink-0">
                🗺️
            </div>
            <div>
                <span class="text-[11px] font-baloo font-bold text-[#6B6B55] uppercase tracking-widest">{{ __('map.radar_exploration') }}</span>
                <h1 class="font-baloo font-extrabold text-xl sm:text-2xl text-[#1F3D20] leading-none">
                    {{ in_array(auth()->user()->role, ['ranger', 'admin']) ? __('map.heading_ranger') : __('map.heading_viewer') }}
                </h1>
            </div>
        </div>
        <div class="px-3.5 py-1 rounded-full bg-[#1F3D20] text-[#F5F4DA] font-baloo font-extrabold text-xs self-start sm:self-auto shadow-sm border border-[#F5F4DA]/20">
            {{ auth()->user()->role === 'admin' ? __('map.mode_admin') : (auth()->user()->role === 'ranger' ? __('map.mode_ranger') : __('map.mode_viewer')) }}
        </div>
    </div>

    <!-- Map Container Container (Responsive Full-Width & Dynamic Screen Height) -->
    <div class="relative w-full h-[calc(100vh-200px)] sm:h-[calc(100vh-210px)] min-h-[560px] max-h-[860px] rounded-3xl overflow-hidden shadow-2xl card-gg p-1 border-2 border-[#1F3D20]/20">
        <div id="leaflet-map" class="w-full h-full rounded-2xl z-0"></div>

        <!-- Floating Recenter / Auto-Follow GPS Button -->
        <button id="recenter-gps-btn" class="absolute top-4 right-4 z-20 px-4 py-2.5 bg-[#1F3D20]/95 text-[#F5F4DA] backdrop-blur-md rounded-full border border-[#F5F4DA]/30 shadow-2xl font-baloo font-bold text-xs flex items-center gap-2 hover:bg-[#2D4A2E] active:scale-95 transition-all cursor-pointer">
            <span class="animate-pulse text-sm">🎯</span>
            <span id="recenter-gps-label">{{ __('map.auto_follow_on') }}</span>
        </button>

        <!-- Floating Action Controls - FOR RANGER & ADMIN ROLES -->
        @if(in_array(auth()->user()->role, ['ranger', 'admin']))
            <div class="absolute bottom-4 sm:bottom-6 left-1/2 -translate-x-1/2 z-20 px-3.5 py-2 bg-[#1F3D20]/95 backdrop-blur-md rounded-full border border-[#F5F4DA]/20 shadow-2xl flex items-center gap-2 sm:gap-3 max-w-[95vw] sm:max-w-none">
                <button id="open-ar-btn" class="px-4 py-2 rounded-full bg-[#F5F4DA] text-[#1F3D20] font-baloo font-extrabold text-xs flex items-center gap-2 shadow-md hover:bg-white transition-all cursor-pointer whitespace-nowrap active:scale-95">
                    <span>📷</span>
                    <span>{{ __('map.open_ar_camera') }}</span>
                </button>
            </div>
        @endif
    </div>
</div>

<!-- AR Scanner Modal View (Ranger & Admin) -->
@if(in_array(auth()->user()->role, ['ranger', 'admin']))
    <div id="ar-modal" class="fixed inset-0 bg-[#1F3D20]/85 backdrop-blur-md z-[100] flex items-center justify-center p-4 sm:p-6 hidden overflow-y-auto">
        <div class="card-gg max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-4 relative bg-[#FBFAF0] my-auto max-h-[85vh] overflow-y-auto">
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
    <div id="scan-result-modal" class="fixed inset-0 bg-[#1F3D20]/85 backdrop-blur-md z-[100] flex items-center justify-center p-4 sm:p-6 hidden overflow-y-auto">
        <div class="card-gg max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-4 bg-[#FBFAF0] my-auto max-h-[85vh] overflow-y-auto">
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



    <!-- Edit Marker Sighting Modal (For Ranger & Admin editing plant markers directly from map popups) -->
    <div id="edit-sighting-modal" class="fixed inset-0 bg-[#1F3D20]/85 backdrop-blur-md z-[100] flex items-center justify-center p-4 sm:p-6 hidden overflow-y-auto">
        <div class="card-gg max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-4 bg-[#FBFAF0] my-auto max-h-[85vh] overflow-y-auto">
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

<!-- View Discovered Plant Detail Modal (For Viewers & All Roles viewing plant details directly from map) -->
<div id="view-sighting-modal" class="fixed inset-0 bg-[#1F3D20]/85 backdrop-blur-md z-[100] flex items-center justify-center p-4 sm:p-6 hidden overflow-y-auto">
    <div class="card-gg max-w-lg w-full p-5 sm:p-6 shadow-2xl space-y-4 bg-[#FBFAF0] my-auto max-h-[85vh] overflow-y-auto">
        <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
            <div>
                <span id="view-conservation-status" class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#1F3D20] px-2.5 py-0.5 rounded-full uppercase">COMMON</span>
                <h3 id="view-common-name" class="font-baloo font-extrabold text-2xl text-[#1F3D20] mt-1">Nama Tumbuhan</h3>
                <p id="view-scientific-name" class="font-nunito text-xs text-[#6B6B55] italic">Scientific Name</p>
                <span id="view-uploader-badge" class="inline-block mt-1 text-[10px] font-nunito font-bold text-[#8B6A4C] bg-[#8B6A4C]/15 px-2.5 py-0.5 rounded-full">👤 Ranger: ...</span>
            </div>
            <button id="close-view-modal-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer hover:bg-[#1F3D20] hover:text-[#F5F4DA] transition-colors">&times;</button>
        </div>

        <!-- Preview Photo -->
        <div class="h-48 rounded-2xl overflow-hidden bg-[#1F3D20] relative border border-[#1F3D20]/20 shadow-xs">
            <img id="view-sighting-img" src="" class="w-full h-full object-cover" onerror="this.onerror=null; this.src='/images/logo-plantGuardian.jpeg';" />
        </div>

        <!-- Details Content -->
        <div class="space-y-3 pt-1">
            <div class="p-3.5 bg-white border border-[#1F3D20]/10 rounded-2xl space-y-1 shadow-2xs">
                <h4 class="font-baloo font-bold text-xs text-[#1F3D20] uppercase tracking-wider">{{ __('map.modal_plant_description') }}</h4>
                <p id="view-description" class="font-nunito text-xs text-[#6B6B55] leading-relaxed">...</p>
            </div>

            <div class="p-3.5 bg-[#E2E1C4]/40 border border-[#1F3D20]/10 rounded-2xl space-y-1 shadow-2xs">
                <h4 class="font-baloo font-bold text-xs text-[#1F3D20] uppercase tracking-wider">{{ __('map.modal_care_instructions') }}</h4>
                <p id="view-care-instructions" class="font-nunito text-xs text-[#1F3D20]/80 leading-relaxed font-medium">...</p>
            </div>

            <div class="pt-2 flex flex-col gap-2">
                <a href="/gallery" class="w-full btn-gg-primary py-3 rounded-full flex items-center justify-center gap-2 cursor-pointer shadow-md text-xs font-baloo font-bold text-center">
                    <span>{{ __('map.modal_open_seedex') }}</span>
                </a>

                <button type="button" id="modal-report-trigger-btn" class="w-full py-2.5 rounded-full bg-red-100/80 hover:bg-red-200/80 text-[#C0392B] font-baloo font-bold text-xs flex items-center justify-center gap-1.5 transition-colors border border-red-300/40 cursor-pointer">
                    <span>{{ __('map.report_sighting') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Report Plant Sighting Modal View -->
<div id="report-sighting-modal" class="fixed inset-0 bg-[#1F3D20]/85 backdrop-blur-md z-[110] flex items-center justify-center p-4 sm:p-6 hidden overflow-y-auto">
    <div class="card-gg max-w-md w-full p-5 sm:p-6 shadow-2xl space-y-4 bg-[#FBFAF0] my-auto max-h-[85vh] overflow-y-auto">
        <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
            <div>
                <span class="text-[10px] font-baloo font-bold text-white bg-[#C0392B] px-2.5 py-0.5 rounded-full uppercase tracking-wider">MODERASI LAPANGAN</span>
                <h3 class="font-baloo font-extrabold text-2xl text-[#1F3D20] mt-1">{{ __('map.report_sighting_title') }}</h3>
                <p class="font-nunito text-xs text-[#6B6B55] mt-0.5 leading-relaxed">{{ __('map.report_sighting_subtitle') }}</p>
            </div>
            <button id="close-report-modal-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer hover:bg-[#C0392B] hover:text-white transition-colors">&times;</button>
        </div>

        <form id="report-sighting-form" class="space-y-3.5 pt-1">
            <input type="hidden" id="report-sighting-id" value="" />

            <div>
                <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-2 uppercase tracking-wider">PILIH ALASAN PELAPORAN <span class="text-red-600">*</span></label>
                
                <div class="space-y-2">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-[#1F3D20]/15 bg-white cursor-pointer hover:border-[#C0392B] transition-colors shadow-2xs">
                        <input type="radio" name="report_reason" value="fake_specimen" required class="mt-0.5 accent-[#C0392B]" />
                        <span class="text-xs font-nunito font-bold text-[#1F3D20] leading-snug">{{ __('map.reason_fake_specimen') }}</span>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-[#1F3D20]/15 bg-white cursor-pointer hover:border-[#C0392B] transition-colors shadow-2xs">
                        <input type="radio" name="report_reason" value="plant_missing_or_dead" class="mt-0.5 accent-[#C0392B]" />
                        <span class="text-xs font-nunito font-bold text-[#1F3D20] leading-snug">{{ __('map.reason_plant_missing_or_dead') }}</span>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-[#1F3D20]/15 bg-white cursor-pointer hover:border-[#C0392B] transition-colors shadow-2xs">
                        <input type="radio" name="report_reason" value="species_mismatch_or_replaced" class="mt-0.5 accent-[#C0392B]" />
                        <span class="text-xs font-nunito font-bold text-[#1F3D20] leading-snug">{{ __('map.reason_species_mismatch_or_replaced') }}</span>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-[#1F3D20]/15 bg-white cursor-pointer hover:border-[#C0392B] transition-colors shadow-2xs">
                        <input type="radio" name="report_reason" value="other" class="mt-0.5 accent-[#C0392B]" />
                        <span class="text-xs font-nunito font-bold text-[#1F3D20] leading-snug">{{ __('map.reason_other') }}</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1 uppercase tracking-wider">CATATAN TAMBAHAN (OPSIONAL)</label>
                <textarea id="report-notes" rows="2" class="w-full text-xs font-nunito p-3 rounded-xl border border-[#1F3D20]/20 bg-white focus:outline-none focus:border-[#C0392B]" placeholder="{{ __('map.notes_placeholder') }}"></textarea>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
                <button type="button" id="cancel-report-btn" class="px-4 py-2.5 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-bold text-xs cursor-pointer hover:bg-[#d5d4b3] transition-colors">
                    {{ __('map.cancel') }}
                </button>
                <button type="submit" id="submit-report-btn" class="flex-1 py-2.5 rounded-full bg-[#C0392B] text-white font-baloo font-bold text-xs hover:bg-[#a93224] transition-colors cursor-pointer shadow-md flex items-center justify-center gap-1.5">
                    <span>{{ __('map.submit_report') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.USER_ROLE = "{{ auth()->user()->role ?? 'viewer' }}";
    window.CURRENT_USER_ID = Number("{{ auth()->id() ?? 0 }}");

    window.openViewSightingModal = async function(sightingId) {
        const viewModal = document.querySelector('#view-sighting-modal');
        if (!viewModal) return;
        const t = window.translations || {};

        try {
            const res = await window.apiClient.get(`/map/sightings/${sightingId}`);
            const sighting = res.data?.data || res.data || res;
            const species = sighting.species || sighting.plant_species || {};

            const commonNameEl = document.querySelector('#view-common-name');
            if (commonNameEl) commonNameEl.textContent = species.common_name || 'Tumbuhan Spesimen';

            const sciNameEl = document.querySelector('#view-scientific-name');
            if (sciNameEl) sciNameEl.textContent = species.scientific_name || '';

            const uploaderEl = document.querySelector('#view-uploader-badge');
            if (uploaderEl) {
                const uploaderName = sighting.ranger?.name || sighting.ranger_name || 'Ranger';
                const uploaderLabelText = t.uploader_label || 'Ranger Pengunggah';
                uploaderEl.textContent = `👤 ${uploaderLabelText}: ${uploaderName}`;
            }

            const statusEl = document.querySelector('#view-conservation-status');
            if (statusEl) {
                const rawStatus = (species.conservation_status || 'Common').toLowerCase();
                let statusText = t.status_common || 'UMUM';
                if (rawStatus.includes('vulnerab') || rawStatus.includes('rentan')) statusText = t.status_vulnerable || 'RENTAN';
                else if (rawStatus.includes('endanger') || rawStatus.includes('terancam')) statusText = t.status_endangered || 'TERANCAM PUNAH';
                else if (rawStatus.includes('protect') || rawStatus.includes('lindung')) statusText = t.status_protected || 'DILINDUNGI';
                else if (rawStatus.includes('least') || rawStatus.includes('rendah')) statusText = t.status_least_concern || 'RISIKO RENDAH';
                statusEl.textContent = statusText.toUpperCase();
            }

            const imgEl = document.querySelector('#view-sighting-img');
            if (imgEl) {
                imgEl.onerror = function() { this.onerror = null; this.src = '/images/logo-plantGuardian.jpeg'; };
                imgEl.src = sighting.photo_url || species.reference_image_url || '/images/logo-plantGuardian.jpeg';
            }

            const descEl = document.querySelector('#view-description');
            if (descEl) descEl.textContent = species.description || (t.modal_no_description || 'Deskripsi spesimen belum tersedia.');

            const careEl = document.querySelector('#view-care-instructions');
            if (careEl) careEl.textContent = species.care_instructions || (t.modal_no_care || 'Petunjuk perawatan belum tersedia dari Ranger.');

            const reportTriggerBtn = document.querySelector('#modal-report-trigger-btn');
            if (reportTriggerBtn) {
                reportTriggerBtn.onclick = function() {
                    viewModal.classList.add('hidden');
                    window.openReportSightingModal(sightingId);
                };
            }

            viewModal.classList.remove('hidden');
        } catch (err) {
            alert('Gagal memuat detail tumbuhan: ' + (err.response?.data?.message || err.message));
        }
    };

    window.openReportSightingModal = function(sightingId) {
        const reportModal = document.querySelector('#report-sighting-modal');
        const sightingIdInput = document.querySelector('#report-sighting-id');
        if (!reportModal || !sightingIdInput) return;

        sightingIdInput.value = sightingId;
        const notesEl = document.querySelector('#report-notes');
        if (notesEl) notesEl.value = '';

        const radios = document.querySelectorAll('input[name="report_reason"]');
        radios.forEach(r => r.checked = false);

        reportModal.classList.remove('hidden');
    };

    document.addEventListener('DOMContentLoaded', async () => {
        const closeViewModalBtn = document.querySelector('#close-view-modal-btn');
        if (closeViewModalBtn) {
            closeViewModalBtn.addEventListener('click', () => {
                document.querySelector('#view-sighting-modal')?.classList.add('hidden');
            });
        }

        const reportModal = document.querySelector('#report-sighting-modal');
        const closeReportBtn = document.querySelector('#close-report-modal-btn');
        const cancelReportBtn = document.querySelector('#cancel-report-btn');
        const reportForm = document.querySelector('#report-sighting-form');

        if (closeReportBtn) {
            closeReportBtn.addEventListener('click', () => reportModal?.classList.add('hidden'));
        }
        if (cancelReportBtn) {
            cancelReportBtn.addEventListener('click', () => reportModal?.classList.add('hidden'));
        }

        if (reportForm) {
            reportForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const sightingId = document.querySelector('#report-sighting-id').value;
                const selectedReason = document.querySelector('input[name="report_reason"]:checked')?.value;
                const notes = document.querySelector('#report-notes')?.value;

                if (!selectedReason) {
                    alert('Silakan pilih alasan pelaporan terlebih dahulu.');
                    return;
                }

                try {
                    const res = await window.apiClient.post(`/map/sightings/${sightingId}/report`, {
                        reason: selectedReason,
                        notes: notes
                    });
                    const msg = res.data?.message || res.message || 'Laporan berhasil dikirim ke Admin.';
                    if (window.showToast) {
                        window.showToast(msg, 'success');
                    } else {
                        alert(msg);
                    }
                    reportModal.classList.add('hidden');
                } catch (err) {
                    alert('Gagal mengirim laporan: ' + (err.response?.data?.message || err.message));
                }
            });
        }
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

            const currentUserId = Number("{{ auth()->id() }}");
            const currentUserRole = "{{ auth()->user()->role }}";
            const canModify = currentUserRole === 'admin' || sighting.ranger_id === currentUserId;

            if (!canModify) {
                alert('Anda hanya dapat mengedit temuan tumbuhan yang Anda buat sendiri. Hanya Admin yang dapat mengedit milik Ranger lain.');
                return;
            }

            document.querySelector('#edit-sighting-id').value = sighting.id;

            const editImgEl = document.querySelector('#edit-sighting-img');
            if (editImgEl) {
                editImgEl.onerror = function() { this.onerror = null; this.src = '/images/logo-plantGuardian.jpeg'; };
                editImgEl.src = sighting.photo_url || (sighting.species && sighting.species.reference_image_url) || '/images/logo-plantGuardian.jpeg';
            }
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
