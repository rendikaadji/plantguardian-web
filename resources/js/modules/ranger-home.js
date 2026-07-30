/**
 * Ranger Dashboard Module (Meja Arsip & Kurasi Katalog)
 * Rujuk docs/design.md §3.9 & docs/architecture.md §4.7
 */

import apiClient from '../api-client.js';

export class RangerHome {
  /**
   * Fetch and display stats for the 3 archive drawer cards
   */
  async loadDashboardStats() {
    // 1. Fetch species catalog count
    const speciesEl = document.querySelector('#count-species');
    try {
      const speciesRes = await apiClient.get('/ranger/species');
      const speciesData = speciesRes.data || speciesRes;
      const speciesCount = Array.isArray(speciesData) ? speciesData.length : 0;
      if (speciesEl) {
        speciesEl.textContent = `${speciesCount} Spesies Terdaftar`;
      }
    } catch (error) {
      console.warn('Gagal memuat statistik spesies Ranger:', error.message);
      if (speciesEl) {
        speciesEl.textContent = '0 Spesies Terdaftar';
      }
    }

    // 2. Fetch compost materials count
    const compostEl = document.querySelector('#count-compost');
    try {
      const compostRes = await apiClient.get('/ranger/compost-materials');
      const compostData = compostRes.data || compostRes;
      const compostCount = Array.isArray(compostData) ? compostData.length : 0;
      if (compostEl) {
        compostEl.textContent = `${compostCount} Bahan Terdaftar`;
      }
    } catch (error) {
      console.warn('Gagal memuat statistik bahan kompos Ranger:', error.message);
      if (compostEl) {
        compostEl.textContent = '0 Bahan Terdaftar';
      }
    }

    // 3. Fetch plant sightings count
    const sightingsEl = document.querySelector('#count-sightings');
    try {
      const sightingsRes = await apiClient.get('/ranger/sightings');
      const sightingsData = sightingsRes.data || sightingsRes;
      const sightingsCount = Array.isArray(sightingsData) ? sightingsData.length : 0;
      if (sightingsEl) {
        sightingsEl.textContent = `${sightingsCount} Temuan Uploaded`;
      }
    } catch (error) {
      console.warn('Gagal memuat statistik temuan Ranger:', error.message);
      if (sightingsEl) {
        sightingsEl.textContent = '0 Temuan Uploaded';
      }
    }
  }
}

export default RangerHome;
