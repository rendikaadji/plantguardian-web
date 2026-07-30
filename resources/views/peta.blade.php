@extends('layouts.app')

@section('title', 'Peta Temuan & Radar Flora — Garden Guardian')

@push('scripts')
<!-- Leaflet.js CSS & JS CDN -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <span class="text-xs font-baloo font-bold text-[#6B6B55] uppercase tracking-wider">RADAR EKSPLORASI</span>
            <h1 class="font-baloo font-extrabold text-2xl sm:text-3xl text-[#1F3D20]">
                {{ auth()->user()->role === 'ranger' ? 'Peta Lapangan Ranger & Scan AR' : 'Peta Temuan Spesies' }}
            </h1>
        </div>
        <div class="px-3 py-1 rounded-full bg-[#E2E1C4] text-[#1F3D20] font-baloo font-extrabold text-xs">
            {{ auth()->user()->role === 'ranger' ? 'MODE RANGER' : 'MODE CATCH VIEWER' }}
        </div>
    </div>

    <!-- Map Container Container -->
    <div class="relative w-full h-[72vh] rounded-3xl overflow-hidden shadow-md card-gg p-1">
        <div id="leaflet-map" class="w-full h-full rounded-2xl z-0"></div>

        <!-- Floating Camera Button - ONLY FOR RANGER ROLE -->
        @if(auth()->user()->role === 'ranger')
            <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20">
                <button id="open-ar-btn" class="btn-gg-primary shadow-xl flex items-center gap-3 active:scale-95 cursor-pointer">
                    <div class="w-7 h-7 rounded-full bg-[#F5F4DA] text-[#1F3D20] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="2"/></svg>
                    </div>
                    <span>Buka Kamera Scan AR</span>
                </button>
            </div>
        @endif
    </div>
</div>

<!-- AR Scanner Modal View (Ranger Only) -->
@if(auth()->user()->role === 'ranger')
    <div id="ar-modal" class="fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden">
        <div class="card-gg max-w-lg w-full p-6 shadow-2xl space-y-4 relative bg-[#FBFAF0]">
            <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
                <div>
                    <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#1F3D20] px-2.5 py-0.5 rounded-full uppercase">MODE SCAN AR RANGER</span>
                    <h3 class="font-baloo font-extrabold text-xl text-[#1F3D20] mt-1">Pemindaian Visi Komputer</h3>
                </div>
                <button id="close-ar-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] hover:bg-[#1F3D20] hover:text-[#F5F4DA] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <!-- Video Stream Container -->
            <div class="relative aspect-4/3 bg-[#1F3D20] rounded-2xl overflow-hidden shadow-inner">
                <video id="ar-video" class="w-full h-full object-cover" autoplay playsinline muted></video>

                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-48 h-48 border-2 border-dashed border-[#F5F4DA]/80 rounded-2xl flex items-center justify-center">
                        <span class="bg-[#1F3D20]/80 text-[#F5F4DA] px-3 py-1 font-baloo text-xs rounded-full">Arahkan ke Tumbuhan</span>
                    </div>
                </div>
            </div>

            <button id="scan-trigger-btn" class="w-full btn-gg-primary py-3 rounded-full flex items-center justify-center gap-2 cursor-pointer">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3" stroke-width="2"/></svg>
                <span>Ambil Foto Spesimen</span>
            </button>
        </div>
    </div>

    <!-- Scan Result & Plant Data Form Modal (Ranger On-Site Data Entry) -->
    <div id="scan-result-modal" class="fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden overflow-y-auto">
        <div class="card-gg max-w-lg w-full p-6 shadow-2xl space-y-4 bg-[#FBFAF0] my-auto max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
                <div>
                    <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#1F3D20] px-2.5 py-0.5 rounded-full uppercase">FORM INPUT SPESIES LAPANGAN</span>
                    <h3 class="font-baloo font-extrabold text-2xl text-[#1F3D20] mt-1">Data Tumbuhan Lapangan</h3>
                </div>
                <button id="close-result-modal-btn" class="w-8 h-8 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center font-bold text-lg cursor-pointer">&times;</button>
            </div>

            <!-- Preview Photo Captured -->
            <div class="h-40 rounded-2xl overflow-hidden bg-[#1F3D20] relative border border-[#1F3D20]/20">
                <img id="result-img" src="" class="w-full h-full object-cover" />
                <span class="absolute bottom-2 left-2 bg-[#1F3D20]/80 text-[#F5F4DA] text-[10px] px-2.5 py-0.5 rounded-full font-baloo font-bold">Foto Live Spesimen</span>
            </div>

            <!-- Live Form Inputs -->
            <form id="ranger-live-plant-form" class="space-y-3 pt-1">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Nama Umum Tumbuhan <span class="text-red-600">*</span></label>
                        <input type="text" id="input-common-name" required class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" placeholder="Contoh: Pohon Mangga" />
                    </div>
                    <div>
                        <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Nama Ilmiah / Latin</label>
                        <input type="text" id="input-scientific-name" class="w-full p-2.5 text-xs font-nunito italic border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" placeholder="Contoh: Mangifera indica" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Status / Risiko Konservasi</label>
                    <select id="input-conservation-status" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]">
                        <option value="Common">Melimpah (Common)</option>
                        <option value="Vulnerable">Rentan (Vulnerable)</option>
                        <option value="Endangered">Terancam (Endangered)</option>
                        <option value="Protected">Dilindungi Undang-Undang</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Deskripsi Edukasi Tumbuhan</label>
                    <textarea id="input-description" rows="2" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" placeholder="Jelaskan ciri fisik, manfaat edukasi, atau habitat tumbuhan ini..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Cara Merawat Pohon</label>
                    <textarea id="input-care-instructions" rows="2" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" placeholder="Instruksi penyiraman, sinar matahari, pemupukan kompos..."></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" id="save-live-plant-btn" class="w-full btn-gg-primary py-3 rounded-full flex items-center justify-center gap-2 cursor-pointer shadow-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>🌿 SIMPAN & PUBLIKASIKAN KE PETA</span>
                    </button>
                </div>
    <!-- Edit Marker Sighting Modal (For Ranger editing plant markers directly from map popups) -->
    <div id="edit-sighting-modal" class="fixed inset-0 bg-[#1F3D20]/80 backdrop-blur-md z-50 flex items-center justify-center p-4 hidden overflow-y-auto">
        <div class="card-gg max-w-lg w-full p-6 shadow-2xl space-y-4 bg-[#FBFAF0] my-auto max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start border-b border-[#1F3D20]/10 pb-3">
                <div>
                    <span class="text-[10px] font-baloo font-bold text-[#F5F4DA] bg-[#8B6A4C] px-2.5 py-0.5 rounded-full uppercase">KOREKSI TEMUAN PETA</span>
                    <h3 class="font-baloo font-extrabold text-2xl text-[#1F3D20] mt-1">Edit Data Tumbuhan</h3>
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
                        <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Nama Umum Tumbuhan <span class="text-red-600">*</span></label>
                        <input type="text" id="edit-common-name" required class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
                    </div>
                    <div>
                        <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Nama Ilmiah / Latin</label>
                        <input type="text" id="edit-scientific-name" class="w-full p-2.5 text-xs font-nunito italic border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Status / Risiko Konservasi</label>
                    <select id="edit-conservation-status" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]">
                        <option value="Common">Melimpah (Common)</option>
                        <option value="Vulnerable">Rentan (Vulnerable)</option>
                        <option value="Endangered">Terancam (Endangered)</option>
                        <option value="Protected">Dilindungi Undang-Undang</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Deskripsi Edukasi Tumbuhan</label>
                    <textarea id="edit-description" rows="2" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-baloo font-bold text-[#1F3D20] mb-1">Cara Merawat Pohon</label>
                    <textarea id="edit-care-instructions" rows="2" class="w-full p-2.5 text-xs font-nunito border border-[#1F3D20]/20 rounded-xl bg-white focus:outline-none focus:border-[#1F3D20]"></textarea>
                </div>

                <div class="pt-2 flex items-center justify-between gap-3">
                    <button type="submit" id="update-sighting-btn" class="flex-1 btn-gg-primary py-3 rounded-full flex items-center justify-center gap-2 cursor-pointer shadow-md">
                        <span>💾 SIMPAN PERUBAHAN</span>
                    </button>
                    <button type="button" id="delete-sighting-btn" class="px-4 py-3 rounded-full bg-red-600 text-white font-baloo font-bold text-xs hover:bg-red-700 transition-colors cursor-pointer">
                        🗑️ HAPUS
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
        }

        // AR Scanner & Live Form Input logic for Ranger
        if (window.USER_ROLE === 'ranger' && window.ArScanner) {
            let scanner = null;
            let capturedBlob = null;
            let currentLat = -6.2088;
            let currentLng = 106.8456;

            const openArBtn = document.querySelector('#open-ar-btn');
            const arModal = document.querySelector('#ar-modal');
            const closeArBtn = document.querySelector('#close-ar-btn');
            const scanTriggerBtn = document.querySelector('#scan-trigger-btn');

            const scanResultModal = document.querySelector('#scan-result-modal');
            const closeResultModalBtn = document.querySelector('#close-result-modal-btn');
            const liveForm = document.querySelector('#ranger-live-plant-form');

            if (openArBtn) {
                openArBtn.addEventListener('click', async () => {
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

            // Capture photo and show Live Plant Registration Form
            if (scanTriggerBtn) {
                scanTriggerBtn.addEventListener('click', async () => {
                    try {
                        scanTriggerBtn.disabled = true;
                        
                        // Get current position from navigator if available
                        if (navigator.geolocation) {
                            navigator.geolocation.getCurrentPosition(pos => {
                                currentLat = pos.coords.latitude;
                                currentLng = pos.coords.longitude;
                            }, err => console.warn('GPS default fallback used'));
                        }

                        // Capture photo frame from video
                        const capturedImage = scanner.captureFrame();
                        capturedBlob = capturedImage.blob;
                        document.querySelector('#result-img').src = capturedImage.dataUrl;

                        // Pre-fill fallback defaults
                        document.querySelector('#input-common-name').value = '';
                        document.querySelector('#input-scientific-name').value = '';
                        document.querySelector('#input-description').value = '';
                        document.querySelector('#input-care-instructions').value = 'Siram 2x sehari dan beri pupuk kompos berkala.';

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

            // Save Live Plant Form submission to Backend API /api/scan
            if (liveForm) {
                liveForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const submitBtn = document.querySelector('#save-live-plant-btn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Mengunggah Data...</span>';

                    try {
                        const formData = new FormData();
                        if (capturedBlob) {
                            formData.append('image', capturedBlob, 'live_plant.jpg');
                        }
                        formData.append('latitude', currentLat);
                        formData.append('longitude', currentLng);
                        formData.append('common_name', document.querySelector('#input-common-name').value);
                        formData.append('scientific_name', document.querySelector('#input-scientific-name').value);
                        formData.append('conservation_status', document.querySelector('#input-conservation-status').value);
                        formData.append('description', document.querySelector('#input-description').value);
                        formData.append('care_instructions', document.querySelector('#input-care-instructions').value);

                        const res = await window.apiClient.post('/scan', formData, true);
                        const sighting = res.data || res;

                        if (mapManager && sighting) {
                            mapManager.addSightingMarker(sighting);
                        }

                        alert('Tumbuhan berhasil disimpan & dipublikasikan ke peta!');
                        scanResultModal.classList.add('hidden');
                    } catch (err) {
                        alert('Gagal menyimpan tumbuhan: ' + (err.response?.data?.message || err.message));
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg><span>🌿 SIMPAN & PUBLIKASIKAN KE PETA</span>';
                    }
                });
            }

            if (closeResultModalBtn) closeResultModalBtn.addEventListener('click', () => scanResultModal.classList.add('hidden'));
        }
    });

    // Global popup trigger to open Edit Sighting Modal from Leaflet popup button
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
