/**
 * Compost & Real Planting Frontend Module
 * Rujuk docs/design.md §3.6 & §3.7 serta docs/rules.md §4.1 (Privasi Lokasi)
 */

import apiClient from '../api-client.js';

export class CompostManager {
  /**
   * Helper to safely obtain GPS coordinates moments before submission.
   * STRICT PRIVACY RULE: Never use watchPosition or store continuous tracking.
   */
  async getSingleLocation() {
    return new Promise((resolve) => {
      if (typeof navigator === 'undefined' || !navigator.geolocation || typeof navigator.geolocation.getCurrentPosition !== 'function') {
        return resolve({ latitude: null, longitude: null });
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          resolve({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
          });
        },
        (error) => {
          console.warn('Izin lokasi ditolak atau tidak tersedia:', error.message);
          resolve({ latitude: null, longitude: null });
        },
        { enableHighAccuracy: false, timeout: 5000 }
      );
    });
  }

  /**
   * Fetch list of available compost material guides (Katalog Bahan Kompos)
   */
  async getMaterials() {
    return await apiClient.get('/compost-materials');
  }

  /**
   * Start a new compost process challenge
   */
  async startProcess(compostMaterialId = null) {
    return await apiClient.post('/compost-processes', {
      compost_material_id: compostMaterialId,
    });
  }

  /**
   * Fetch list of user active/history compost processes
   */
  async getProcesses() {
    return await apiClient.get('/compost-processes');
  }

  /**
   * Fetch detail of a specific compost process including progress logs
   */
  async getProcessDetail(processId) {
    return await apiClient.get(`/compost-processes/${processId}`);
  }

  /**
   * Record periodic check-in progress log for active compost process
   * Gets GPS location moment of submit (if allowed)
   */
  async recordCheckin(processId, { stageLabel, photoPath, photoBase64, note }) {
    const coords = await this.getSingleLocation();

    const payload = {
      stage_label: stageLabel,
      photo_path: photoPath || photoBase64,
      latitude: coords.latitude,
      longitude: coords.longitude,
      note: note || null,
    };

    return await apiClient.post(`/compost-processes/${processId}/checkin`, payload);
  }

  /**
   * Mark a compost process as matured (kompos matang)
   */
  async markMatured(processId) {
    return await apiClient.post(`/compost-processes/${processId}/mature`);
  }

  /**
   * Submit real tree planting proof
   * Gets GPS location moment of submit (if allowed)
   */
  async submitRealPlanting({ compostProcessId, plantSpeciesId, photoPath, photoBase64 }) {
    const coords = await this.getSingleLocation();

    const payload = {
      compost_process_id: compostProcessId || null,
      plant_species_id: plantSpeciesId || null,
      photo_path: photoPath || photoBase64,
      latitude: coords.latitude,
      longitude: coords.longitude,
    };

    return await apiClient.post('/real-plantings', payload);
  }
}

export default new CompostManager();
