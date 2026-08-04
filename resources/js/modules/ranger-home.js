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

    // 2. Fetch pending verifications count
    const verificationsEl = document.querySelector('#count-verifications');
    try {
      const verifRes = await apiClient.get('/ranger/verifications/pending');
      const verifData = verifRes.data || verifRes;
      const pendingSightings = verifData.pending_sightings || [];
      if (verificationsEl) {
        verificationsEl.textContent = `${pendingSightings.length} Antrean Pending`;
      }
    } catch (error) {
      console.warn('Gagal memuat statistik verifikasi Ranger:', error.message);
      if (verificationsEl) {
        verificationsEl.textContent = '0 Antrean Pending';
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
