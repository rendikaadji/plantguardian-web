/**
 * Seedex Gallery Module (Plant Guardian Design System)
 * Rujuk docs/design.md §3.3 & §1.3
 */

import apiClient from '../api-client.js';

export class GalleryModule {
  constructor(options = {}) {
    this.containerElement = options.containerElement || document.querySelector('#gallery-container');
    this.modalElement = options.modalElement || document.querySelector('#gallery-modal');
    this.progressTextElement = options.progressTextElement || document.querySelector('#seedex-progress-text');
    this.progressBarElement = options.progressBarElement || document.querySelector('#seedex-progress-bar');
    this.items = [];
    this.speciesCatalog = [];
    this.role = window.USER_ROLE || 'viewer';
  }

  /**
   * Load personal gallery entries & species catalog for locked cards
   */
  async loadGallery() {
    try {
      const response = await apiClient.get('/gallery');
      this.items = response.data?.data || response.data || [];
      this.role = response.data?.role || this.role;

      // Fetch species catalog to render locked cards for undiscovered items
      try {
        const catRes = await apiClient.get('/ranger/species');
        this.speciesCatalog = catRes.data?.data || catRes.data || [];
      } catch (e) {
        this.speciesCatalog = [];
      }

      this.render();
    } catch (error) {
      console.error('[GalleryModule] Gagal memuat Seedex:', error);
    }
  }

  /**
   * Render Seedex Grid & Progress
   */
  render() {
    if (!this.containerElement) return;

    const isRangerOrAdmin = ['ranger', 'admin'].includes(this.role);
    const totalSpecies = isRangerOrAdmin ? this.items.length : Math.max(this.speciesCatalog.length, 12);
    const discoveredCount = this.items.length;
    const progressPercent = isRangerOrAdmin
      ? (discoveredCount > 0 ? 100 : 0)
      : Math.min(Math.round((discoveredCount / totalSpecies) * 100), 100);

    // Update Progress Header
    if (this.progressTextElement) {
      if (isRangerOrAdmin) {
        const uploadedLabel = window.translations?.ranger_uploaded || 'Spesimen Di-upload';
        this.progressTextElement.textContent = `${discoveredCount} ${uploadedLabel}`;
      } else {
        const discoveredLabel = window.translations?.discovered || 'Ditemukan';
        this.progressTextElement.textContent = `${discoveredCount} / ${totalSpecies} Seedex ${discoveredLabel}`;
      }
    }
    if (this.progressBarElement) {
      this.progressBarElement.style.width = `${progressPercent}%`;
    }

    if (this.items.length === 0 && (isRangerOrAdmin || this.speciesCatalog.length === 0)) {
      const emptyTitle = isRangerOrAdmin
        ? (window.translations?.ranger_empty_title || 'Belum Ada Temuan Di-upload')
        : (window.translations?.empty_gallery || 'Seedex Masih Kosong');
      const emptySubtitle = isRangerOrAdmin
        ? (window.translations?.ranger_empty_subtitle || 'Gunakan Peta Digital atau Kamera AR untuk mulai mendokumentasikan tumbuhan di lapangan!')
        : 'Jelajahi peta dan temukan marker spesies tumbuhan untuk mengumpulkan entri ke Seedex!';

      this.containerElement.innerHTML = `
        <div class="card-gg p-12 text-center space-y-4 max-w-md mx-auto">
          <div class="w-16 h-16 rounded-full bg-[#E2E1C4] text-[#1F3D20] flex items-center justify-center mx-auto">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
          </div>
          <h3 class="font-baloo font-extrabold text-xl text-[#1F3D20]">${emptyTitle}</h3>
          <p class="text-xs text-[#6B6B55] font-nunito leading-relaxed">${emptySubtitle}</p>
          <a href="/peta" class="btn-gg-primary inline-flex items-center gap-2 text-xs">Buka Peta Temuan</a>
        </div>
      `;
      return;
    }

    // Map discovered species IDs
    const discoveredSpeciesIds = new Set();
    const itemMap = new Map();

    this.items.forEach(item => {
      const speciesObj = item.sighting?.species || item.species || item.plant_species;
      if (speciesObj?.id) {
        discoveredSpeciesIds.add(speciesObj.id);
        itemMap.set(speciesObj.id, item);
      }
    });

    let cardsHtml = '<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">';

    // 1. Render Discovered Cards / Ranger Sightings Cards
    this.items.forEach(item => {
      const sightingObj = item.sighting || item;
      const speciesObj = sightingObj.species || item.plant_species;
      const title = speciesObj?.common_name || 'Spesimen Flora';
      const sciName = speciesObj?.scientific_name || '';
      const photoUrl = sightingObj.photo_url || item.photo_url || '';
      const rarity = speciesObj?.conservation_status || 'Common';

      let rarityBadgeColor = '#9E9E8A';
      if (rarity.toLowerCase().includes('rare')) rarityBadgeColor = '#7D5BA6';
      else if (rarity.toLowerCase().includes('uncommon')) rarityBadgeColor = '#4C8C4A';

      cardsHtml += `
        <div class="seedex-card card-gg card-gg-hover p-3 rounded-2xl overflow-hidden cursor-pointer group" data-id="${item.id}">
          <div class="h-36 rounded-xl bg-[#E2E1C4] overflow-hidden relative mb-2.5">
            <img src="${photoUrl}" alt="${title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy" />
            <span class="absolute top-2 right-2 text-[10px] font-baloo font-extrabold px-2 py-0.5 rounded-full text-[#FBFAF0]" style="background-color: ${rarityBadgeColor};">
              ${rarity.toUpperCase()}
            </span>
          </div>
          <div>
            <h4 class="font-baloo font-bold text-sm text-[#1F3D20] leading-tight truncate group-hover:text-[#4C8C4A] transition-colors">${title}</h4>
            <p class="font-nunito text-[11px] text-[#6B6B55] italic truncate">${sciName || 'Ditemukan'}</p>
          </div>
        </div>
      `;
    });

    // 2. Render Locked Cards for remaining catalog items or placeholders ONLY for Viewers
    if (!isRangerOrAdmin) {
      const lockedCount = Math.max(totalSpecies - discoveredCount, 4);
      for (let i = 0; i < lockedCount; i++) {
        cardsHtml += `
          <div class="card-gg p-3 rounded-2xl opacity-60 bg-[#E2E1C4]/40 flex flex-col justify-between">
            <div class="h-36 rounded-xl bg-[#E2E1C4] flex items-center justify-center text-[#6B6B55] mb-2.5">
              <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            <div>
              <h4 class="font-baloo font-bold text-sm text-[#6B6B55]">???</h4>
              <p class="font-nunito text-[11px] text-[#6B6B55]">Locked / Belum Ditemukan</p>
            </div>
          </div>
        `;
      }
    }

    cardsHtml += '</div>';
    this.containerElement.innerHTML = cardsHtml;

    // Attach click listeners to cards
    this.containerElement.querySelectorAll('.seedex-card').forEach(card => {
      card.addEventListener('click', () => {
        const id = card.getAttribute('data-id');
        this.openDetail(id);
      });
    });
  }

  /**
   * Open detail modal
   */
  async openDetail(id) {
    try {
      const response = await apiClient.get(`/gallery/${id}`);
      const item = response.data?.data || response.data;
      if (!item) return;

      const sightingObj = item.sighting || item;
      const speciesObj = sightingObj.species || item.plant_species;

      const title = speciesObj?.common_name || 'Hasil Temuan';
      const sciName = speciesObj?.scientific_name || '';
      const desc = speciesObj?.description || 'Deskripsi tanaman belum tersedia.';
      const careInstructions = speciesObj?.care_instructions || '';
      const photoUrl = sightingObj.photo_url || item.photo_url;

      if (this.modalElement) {
        this.modalElement.querySelector('#modal-title').textContent = title;
        this.modalElement.querySelector('#modal-scientific').textContent = sciName;
        this.modalElement.querySelector('#modal-img').src = photoUrl;
        this.modalElement.querySelector('#modal-desc').textContent = desc;

        // Care instructions text
        const careTextElem = this.modalElement.querySelector('#modal-care-text');
        if (careTextElem) {
          careTextElem.textContent = careInstructions || 'Belum ada petunjuk perawatan dari Ranger. Menyiram 2x sehari dan memberi kompos teratur disarankan.';
        }

        // Ranger Care Edit Controls
        const toggleEditBtn = this.modalElement.querySelector('#toggle-care-edit-btn');
        const editForm = this.modalElement.querySelector('#care-edit-form');
        const inputCare = this.modalElement.querySelector('#care-instructions-input');

        if (editForm) editForm.classList.add('hidden');

        if (this.role === 'ranger' && toggleEditBtn && speciesObj?.id) {
          toggleEditBtn.classList.remove('hidden');

          toggleEditBtn.onclick = () => {
            if (inputCare) inputCare.value = speciesObj.care_instructions || '';
            if (editForm) editForm.classList.toggle('hidden');
          };

          const cancelBtn = this.modalElement.querySelector('#cancel-care-edit-btn');
          if (cancelBtn) cancelBtn.onclick = () => editForm.classList.add('hidden');

          const saveBtn = this.modalElement.querySelector('#save-care-edit-btn');
          if (saveBtn) {
            saveBtn.onclick = async () => {
              try {
                saveBtn.disabled = true;
                saveBtn.textContent = 'Menyimpan...';

                const newCareText = inputCare.value.trim();
                await apiClient.put(`/ranger/species/${speciesObj.id}`, {
                  species_code: speciesObj.species_code,
                  common_name: speciesObj.common_name,
                  description: speciesObj.description,
                  care_instructions: newCareText,
                });

                speciesObj.care_instructions = newCareText;
                if (careTextElem) {
                  careTextElem.textContent = newCareText || 'Belum ada petunjuk perawatan dari Ranger.';
                }
                const t = window.translations || {};
                if (editForm) editForm.classList.add('hidden');
                alert(t.instructions_updated || '✨ Petunjuk perawatan pohon berhasil diperbarui!');
              } catch (err) {
                alert('Gagal menyimpan petunjuk perawatan: ' + (err.message || 'Terjadi kesalahan'));
              } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = (window.translations && window.translations.save_instructions) || 'Simpan Petunjuk';
              }
            };
          }
        } else if (toggleEditBtn) {
          toggleEditBtn.classList.add('hidden');
        }

        const deleteBtn = this.modalElement.querySelector('#modal-delete-btn');
        if (deleteBtn) {
          deleteBtn.onclick = () => this.deleteItem(item.id);
        }

        this.modalElement.classList.remove('hidden');
      }
    } catch (error) {
      console.error('[GalleryModule] Gagal memuat detail Seedex:', error);
    }
  }

  /**
   * Delete item
   */
  async deleteItem(id) {
    if (!confirm('Apakah kamu yakin ingin menghapus temuan ini dari Seedex?')) return;

    try {
      await apiClient.delete(`/gallery/${id}`);
      if (this.modalElement) this.modalElement.classList.add('hidden');
      await this.loadGallery();
    } catch (error) {
      alert('Gagal menghapus temuan.');
    }
  }
}

export default GalleryModule;
