/**
 * Leaflet Real Map Module (Plant Guardian Design System)
 * Rujuk docs/architecture.md §5 & §5.0a & docs/design.md §3.2
 */

import apiClient from '../api-client.js';

export class MapManager {
  constructor(containerId = 'leaflet-map', options = {}) {
    this.containerId = containerId;
    this.userRole = options.userRole || window.USER_ROLE || 'viewer';
    this.map = null;
    this.markersGroup = null;
    this.userCoords = { latitude: -6.2088, longitude: 106.8456 }; // Default: Jakarta
    this.hasUserLocation = false;
    this.userLocationMarker = null;
  }

  /**
   * Initialize Leaflet map instance, request position & load markers
   */
  async init() {
    const container = document.getElementById(this.containerId);
    if (!container) {
      console.warn('[MapManager] Kontainer map tidak ditemukan:', this.containerId);
      return;
    }

    if (typeof L === 'undefined') {
      console.warn('[MapManager] Library Leaflet.js (L) belum dimuat.');
      return;
    }

    // Initialize Leaflet map
    this.map = L.map(this.containerId, {
      center: [this.userCoords.latitude, this.userCoords.longitude],
      zoom: 15,
      zoomControl: true,
    });

    // OpenStreetMap Tile Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(this.map);

    this.markersGroup = L.layerGroup().addTo(this.map);

    // Setup global discovery function for Viewer popup button
    window.discoverPlantFromMap = async (sightingId) => {
      try {
        const btn = document.getElementById(`discover-btn-${sightingId}`);
        if (btn) {
          btn.disabled = true;
          btn.textContent = 'Menemukan...';
        }

        const res = await apiClient.post('/plant-discoveries', {
          plant_sighting_id: sightingId,
          latitude: this.userCoords.latitude,
          longitude: this.userCoords.longitude,
        });

        alert('✨ ' + (res.data?.message || 'Berhasil menemukan tumbuhan! (+100 EXP, +50 NC)'));

        // Refresh markers to update "Sudah Ditemukan" status
        await this.loadNearbySightings();
      } catch (err) {
        alert('Gagal menemukan: ' + (err.response?.data?.message || err.message || 'Terjadi kesalahan'));
        const btn = document.getElementById(`discover-btn-${sightingId}`);
        if (btn) {
          btn.disabled = false;
          btn.textContent = '✨ Temukan!';
        }
      }
    };

    await this.fetchAndShowUserPosition();
    await this.loadNearbySightings();
  }

  /**
   * Geolocation helper
   */
  fetchAndShowUserPosition() {
    return new Promise((resolve) => {
      if (typeof navigator === 'undefined' || !navigator.geolocation) {
        return resolve();
      }

      navigator.geolocation.getCurrentPosition(
        (pos) => {
          this.userCoords.latitude = pos.coords.latitude;
          this.userCoords.longitude = pos.coords.longitude;
          this.hasUserLocation = true;

          if (this.map) {
            this.map.setView([pos.coords.latitude, pos.coords.longitude], 15);

            if (this.userLocationMarker) {
              this.map.removeLayer(this.userLocationMarker);
            }

            this.userLocationMarker = L.circleMarker([pos.coords.latitude, pos.coords.longitude], {
              radius: 9,
              fillColor: '#1F3D20',
              fillOpacity: 1.0,
              color: '#F5F4DA',
              weight: 3,
            }).addTo(this.map);

            this.userLocationMarker.bindPopup('<strong style="font-family:Baloo 2,sans-serif;font-size:13px;color:#1F3D20;">📍 Lokasi Kamu</strong>');
          }
          resolve();
        },
        () => resolve(),
        { enableHighAccuracy: false, timeout: 5000 }
      );
    });
  }

  /**
   * Load nearby sightings from API
   */
  async loadNearbySightings() {
    try {
      const res = await apiClient.get(`/plant-sightings/nearby?lat=${this.userCoords.latitude}&lng=${this.userCoords.longitude}&radius=25`);
      const sightings = res.data || [];

      this.markersGroup.clearLayers();
      sightings.forEach((sighting) => this.addSightingMarker(sighting));
    } catch (err) {
      console.warn('[MapManager] Gagal memuat marker temuan:', err);
    }
  }

  /**
   * Add marker based on user role (Viewer vs Ranger)
   */
  addSightingMarker(sighting) {
    if (!this.map || !this.markersGroup || !sighting.latitude || !sighting.longitude) return;

    const speciesName = sighting.species ? sighting.species.common_name : 'Tumbuhan Nyata';
    const speciesCode = sighting.species ? sighting.species.species_code : 'FLORA';
    const photoUrl = sighting.photo_url || '';
    const isDiscovered = sighting.sudah_ditemukan;

    // Custom Tag HTML Marker
    const iconColor = isDiscovered ? '#1F3D20' : '#8B6A4C';
    const iconLabel = isDiscovered ? '🌿' : '❓';

    const markerHtml = `
      <div style="background-color:#FBFAF0;border:2px solid ${iconColor};padding:4px 8px;border-radius:9999px;font-family:Baloo 2,sans-serif;font-size:11px;font-weight:bold;color:${iconColor};box-shadow:0 3px 8px rgba(0,0,0,0.15);white-space:nowrap;">
        ${iconLabel} ${speciesName}
      </div>
    `;

    const customTagIcon = L.divIcon({
      className: 'gg-map-marker',
      html: markerHtml,
      iconSize: [110, 26],
      iconAnchor: [55, 13],
    });

    let popupHtml = '';

    if (this.userRole === 'viewer') {
      // Viewer Popup with "Temukan!" action
      popupHtml = `
        <div style="font-family:Nunito,sans-serif;max-width:210px;color:#2A2A22;padding:4px;">
          <div style="display:flex;justify-between;align-items:center;margin-bottom:4px;">
            <span style="background-color:#E2E1C4;color:#1F3D20;font-family:Baloo 2;font-size:10px;font-weight:bold;padding:1px 6px;border-radius:9999px;">${speciesCode}</span>
            <span style="font-size:10px;color:#6B6B55;font-weight:bold;">${isDiscovered ? '✓ Ditemukan' : '🔒 Terkunci'}</span>
          </div>

          <h4 style="font-family:Baloo 2,sans-serif;font-weight:800;font-size:15px;margin:2px 0 6px 0;color:#1F3D20;line-height:1.2;">
            ${isDiscovered ? speciesName : '??? (Belum Ditemukan)'}
          </h4>

          ${photoUrl ? `<img src="${photoUrl}" style="width:100%;height:105px;object-fit:cover;border-radius:12px;margin-bottom:8px;border:1.5px solid rgba(31,61,32,0.15);"/>` : ''}

          ${isDiscovered 
            ? `<button disabled style="width:100%;background-color:#1F3D20;color:#F5F4DA;font-family:Baloo 2;font-weight:bold;font-size:12px;padding:6px 0;border-radius:9999px;border:none;cursor:default;">✓ Sudah Ditemukan</button>`
            : `<button id="discover-btn-${sighting.id}" onclick="window.discoverPlantFromMap(${sighting.id})" style="width:100%;background-color:#1F3D20;color:#F5F4DA;font-family:Baloo 2;font-weight:bold;font-size:12px;padding:6px 0;border-radius:9999px;border:none;cursor:pointer;box-shadow:0 3px 6px rgba(0,0,0,0.15);">✨ Temukan!</button>`
          }
        </div>
      `;
    } else {
      // Ranger Popup with Edit Action
      popupHtml = `
        <div style="font-family:Nunito,sans-serif;max-width:210px;color:#2A2A22;padding:4px;">
          <span style="background-color:#8B6A4C;color:#F5F4DA;font-family:Baloo 2;font-size:10px;font-weight:bold;padding:1px 6px;border-radius:9999px;">RANGER SIGHTING</span>
          <h4 style="font-family:Baloo 2,sans-serif;font-weight:800;font-size:15px;margin:4px 0;color:#1F3D20;">${speciesName}</h4>
          ${photoUrl ? `<img src="${photoUrl}" style="width:100%;height:105px;object-fit:cover;border-radius:12px;margin-bottom:6px;"/>` : ''}
          <p style="font-size:11px;color:#6B6B55;margin:0 0 6px 0;">Status: <strong>${sighting.verification_status}</strong></p>
          <button onclick="window.openEditSightingModal(${sighting.id})" style="width:100%;background-color:#8B6A4C;color:#F5F4DA;font-family:Baloo 2,sans-serif;font-weight:bold;font-size:12px;padding:6px 0;border-radius:9999px;border:none;cursor:pointer;box-shadow:0 3px 6px rgba(0,0,0,0.15);">
            ✏️ Edit Data Tumbuhan
          </button>
        </div>
      `;
    }

    L.marker([sighting.latitude, sighting.longitude], { icon: customTagIcon })
      .addTo(this.markersGroup)
      .bindPopup(popupHtml);
  }
}

export default MapManager;
