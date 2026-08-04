export default class MapManager {
  constructor(mapContainerId, options = {}) {
    this.mapContainerId = mapContainerId;
    this.userRole = options.userRole || 'viewer';
    this.map = null;
    this.markersGroup = null;
    this.userLocationMarker = null;
  }

  async init() {
    const container = document.getElementById(this.mapContainerId);
    if (!container) return;

    // Center on Jakarta Monas coordinates fallback
    const defaultLat = -6.1754;
    const defaultLng = 106.8272;

    this.map = L.map(this.mapContainerId, {
      zoomControl: true,
      attributionControl: true
    }).setView([defaultLat, defaultLng], 14);

    // High quality OpenStreetMap vector tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(this.map);

    this.markersGroup = L.layerGroup().addTo(this.map);

    // Try HTML5 GPS Geolocation
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          const lat = position.coords.latitude;
          const lng = position.coords.longitude;
          this.map.setView([lat, lng], 15);
          this.addUserMarker(lat, lng);
        },
        () => console.warn('Akses lokasi GPS ditolak/tidak tersedia. Menggunakan peta pusat.'),
        { enableHighAccuracy: true, timeout: 7000 }
      );
    }

    await this.refreshMarkers();

    // Global discover helper for Viewer Catching
    window.discoverPlantFromMap = async (sightingId) => {
      try {
        const res = await window.apiClient.post(`/map/sightings/${sightingId}/claim`);
        const data = res.data || res;
        alert(data.message || 'Selamat! Spesies tumbuhan berhasil kamu temukan dan masuk ke album Seedex!');
        await this.refreshMarkers();
      } catch (err) {
        alert('Gagal mengklaim temuan: ' + (err.response?.data?.message || err.message));
      }
    };
  }

  addUserMarker(lat, lng) {
    if (!this.map) return;
    const t = window.translations || {};
    const gpsText = t.gps_active || 'GPS Aktif';

    const userIcon = L.divIcon({
      className: 'user-gps-marker',
      html: `
        <div style="position:relative;width:24px;height:24px;">
          <div style="position:absolute;inset:0;background-color:#3B82F6;border-radius:9999px;opacity:0.4;animation:ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
          <div style="position:relative;width:24px;height:24px;background-color:#2563EB;border:3px solid #FFFFFF;border-radius:9999px;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>
        </div>
      `,
      iconSize: [24, 24],
      iconAnchor: [12, 12]
    });

    if (this.userLocationMarker) {
      this.userLocationMarker.setLatLng([lat, lng]);
    } else {
      this.userLocationMarker = L.marker([lat, lng], { icon: userIcon })
        .addTo(this.map)
        .bindPopup(`<b style="font-family:Baloo 2,sans-serif;">📍 ${gpsText}</b>`);
    }
  }

  async refreshMarkers() {
    if (!this.markersGroup) return;
    this.markersGroup.clearLayers();

    try {
      const sightings = await window.apiClient.get('/sightings');
      const list = Array.isArray(sightings) ? sightings : (sightings.data || []);

      list.forEach((sighting) => {
        this.addSightingMarker(sighting);
      });
    } catch (err) {
      console.warn('Gagal memuat marker temuan:', err.message);
    }
  }

  addSightingMarker(sighting) {
    if (!this.map || !this.markersGroup || !sighting.latitude || !sighting.longitude) return;

    const t = window.translations || {};
    const speciesName = sighting.species ? sighting.species.common_name : 'Tumbuhan Nyata';
    const speciesCode = sighting.species ? sighting.species.species_code : 'FLORA';
    const photoUrl = sighting.photo_url || '';
    const isDiscovered = sighting.sudah_ditemukan;

    const isRangerOrAdmin = this.userRole === 'ranger' || this.userRole === 'admin';
    const displayName = (isRangerOrAdmin || isDiscovered) ? speciesName : (t.mystery_plant || '❓ Tanaman Misterius');
    const iconColor = isDiscovered ? '#1F3D20' : (isRangerOrAdmin ? '#8B6A4C' : '#D96C63');
    const iconLabel = isDiscovered ? '🌿' : (isRangerOrAdmin ? '📍' : '❓');

    const markerHtml = `
      <div style="background-color:#FBFAF0;border:2px solid ${iconColor};padding:4px 10px;border-radius:9999px;font-family:Baloo 2,sans-serif;font-size:11px;font-weight:bold;color:${iconColor};box-shadow:0 3px 8px rgba(0,0,0,0.15);white-space:nowrap;">
        ${iconLabel} ${displayName}
      </div>
    `;

    const customTagIcon = L.divIcon({
      className: 'gg-map-marker',
      html: markerHtml,
      iconSize: [120, 28],
      iconAnchor: [60, 14],
    });

    let popupHtml = '';

    if (this.userRole === 'viewer') {
      const verifiedText = t.verified_badge || 'Spesies Terverifikasi';
      const discoverText = t.discover_button || '✨ Temukan & Klaim!';
      const alreadyDiscoveredText = t.already_discovered || '✓ Sudah Ditemukan';
      const unclaimedBadge = t.unclaimed_badge || '🔒 Belum Diklaim';
      const mysteryPlant = t.mystery_plant || '❓ Tanaman Misterius';
      const unclaimedTreeText = t.unclaimed_tree || 'Pohon ini belum diklaim! Tekan tombol di bawah untuk membuka dan mengklaim.';

      // Viewer Popup with "Temukan!" action (Hides real species name until claimed)
      popupHtml = `
        <div style="font-family:Nunito,sans-serif;max-width:220px;color:#2A2A22;padding:4px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="background-color:#E2E1C4;color:#1F3D20;font-family:Baloo 2;font-size:10px;font-weight:bold;padding:1px 6px;border-radius:9999px;">${isDiscovered ? speciesCode : 'MYSTERY'}</span>
            <span style="font-size:10px;color:#6B6B55;font-weight:bold;">${isDiscovered ? '✓ ' + verifiedText : unclaimedBadge}</span>
          </div>

          <h4 style="font-family:Baloo 2,sans-serif;font-weight:800;font-size:15px;margin:2px 0 6px 0;color:#1F3D20;line-height:1.2;">
            ${isDiscovered ? speciesName : mysteryPlant}
          </h4>

          ${isDiscovered 
            ? (photoUrl ? `<img src="${photoUrl}" style="width:100%;height:105px;object-fit:cover;border-radius:12px;margin-bottom:8px;border:1.5px solid rgba(31,61,32,0.15);"/>` : '')
            : `<div style="background-color:#E2E1C4/40;border:1.5px border-dashed #8B6A4C;border-radius:12px;padding:12px;text-align:center;margin-bottom:8px;font-size:11px;color:#6B6B55;font-style:italic;">
                ${unclaimedTreeText}
               </div>`
          }

          ${isDiscovered 
            ? `<button disabled style="width:100%;background-color:#1F3D20;color:#F5F4DA;font-family:Baloo 2;font-weight:bold;font-size:12px;padding:7px 0;border-radius:9999px;border:none;cursor:default;">${alreadyDiscoveredText}</button>`
            : `<button id="discover-btn-${sighting.id}" onclick="window.discoverPlantFromMap(${sighting.id})" style="width:100%;background-color:#1F3D20;color:#F5F4DA;font-family:Baloo 2;font-weight:bold;font-size:12px;padding:7px 0;border-radius:9999px;border:none;cursor:pointer;box-shadow:0 3px 8px rgba(0,0,0,0.2);">${discoverText}</button>`
          }
        </div>
      `;
    } else {
      // Ranger / Admin Popup with Edit Action
      const editDataBtnText = t.edit_data_button || '✏️ Edit Data Tumbuhan';
      const statusLabelText = t.status_label || 'Status';

      popupHtml = `
        <div style="font-family:Nunito,sans-serif;max-width:210px;color:#2A2A22;padding:4px;">
          <span style="background-color:#8B6A4C;color:#F5F4DA;font-family:Baloo 2;font-size:10px;font-weight:bold;padding:1px 6px;border-radius:9999px;">${this.userRole.toUpperCase()} SIGHTING</span>
          <h4 style="font-family:Baloo 2,sans-serif;font-weight:800;font-size:15px;margin:4px 0;color:#1F3D20;">${speciesName}</h4>
          ${photoUrl ? `<img src="${photoUrl}" style="width:100%;height:105px;object-fit:cover;border-radius:12px;margin-bottom:6px;"/>` : ''}
          <p style="font-size:11px;color:#6B6B55;margin:0 0 6px 0;">${statusLabelText}: <strong>${sighting.verification_status}</strong></p>
          <button onclick="window.openEditSightingModal(${sighting.id})" style="width:100%;background-color:#8B6A4C;color:#F5F4DA;font-family:Baloo 2,sans-serif;font-weight:bold;font-size:12px;padding:6px 0;border-radius:9999px;border:none;cursor:pointer;box-shadow:0 3px 6px rgba(0,0,0,0.15);">
            ${editDataBtnText}
          </button>
        </div>
      `;
    }

    L.marker([sighting.latitude, sighting.longitude], { icon: customTagIcon })
      .addTo(this.markersGroup)
      .bindPopup(popupHtml);
  }
}
