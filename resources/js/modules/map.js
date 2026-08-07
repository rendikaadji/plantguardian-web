export default class MapManager {
  constructor(mapContainerId, options = {}) {
    this.mapContainerId = mapContainerId;
    this.userRole = (options.userRole || 'viewer').toLowerCase();
    this.map = null;
    this.markersGroup = null;
    this.userLocationMarker = null;
    this.userLocationCircle = null;
    this.userLat = null;
    this.userLng = null;
    this.lastLat = null;
    this.lastLng = null;
    this.heading = 0;
    this.isAutoFollow = true;
    this.isRotateMode = true; // Heading-Up Map Rotation enabled by default
    this.watchId = null;
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

    // Disable auto-follow when user manually drags the map
    this.map.on('dragstart', () => {
      this.setAutoFollow(false);
    });

    // Setup Recenter & Auto-Follow Button Listener
    const recenterBtn = document.getElementById('recenter-gps-btn');
    if (recenterBtn) {
      recenterBtn.addEventListener('click', () => {
        this.recenterUser();
      });
    }

    // Setup Map Rotation Mode Toggle Button
    const rotateBtn = document.getElementById('toggle-rotation-btn');
    if (rotateBtn) {
      rotateBtn.addEventListener('click', () => {
        this.toggleRotateMode();
      });
    }

    // Continuous High Accuracy HTML5 GPS Geolocation tracking
    this.startGpsTracking();

    await this.refreshMarkers();

    // Global discover helper for Viewer Catching
    window.discoverPlantFromMap = async (sightingId) => {
      try {
        const payload = { plant_sighting_id: sightingId };
        if (this.userLat !== null && this.userLng !== null) {
          payload.latitude = this.userLat;
          payload.longitude = this.userLng;
        }

        const res = await window.apiClient.post(`/map/sightings/${sightingId}/claim`, payload);
        const data = res.data || res;
        alert(data.message || 'Selamat! Spesies tumbuhan berhasil kamu temukan dan masuk ke album Seedex!');
        await this.refreshMarkers();
      } catch (err) {
        alert('Gagal mengklaim temuan: ' + (err.response?.data?.message || err.message));
      }
    };
  }

  toggleRotateMode() {
    this.isRotateMode = !this.isRotateMode;
    const rotateLabel = document.getElementById('toggle-rotation-label');
    const rotateBtn = document.getElementById('toggle-rotation-btn');

    if (rotateLabel) {
      rotateLabel.textContent = this.isRotateMode ? 'Mode: Arah Jalan 🧭' : 'Mode: Utara 🧭';
    }
    if (rotateBtn) {
      if (this.isRotateMode) {
        rotateBtn.classList.add('bg-[#1F3D20]', 'border-[#F5F4DA]/40');
        rotateBtn.classList.remove('bg-gray-800/80', 'border-gray-500/40');
      } else {
        rotateBtn.classList.remove('bg-[#1F3D20]', 'border-[#F5F4DA]/40');
        rotateBtn.classList.add('bg-gray-800/80', 'border-gray-500/40');
      }
    }

    this.applyMapRotation(this.heading);
  }

  setAutoFollow(enabled) {
    this.isAutoFollow = enabled;
    const labelEl = document.getElementById('recenter-gps-label');
    const btnEl = document.getElementById('recenter-gps-btn');

    if (labelEl) {
      labelEl.textContent = enabled ? 'Auto-Follow On' : 'Ikuti Saya';
    }
    if (btnEl) {
      if (enabled) {
        btnEl.classList.add('bg-[#1F3D20]', 'border-[#F5F4DA]/40');
        btnEl.classList.remove('bg-gray-800/80', 'border-gray-500/40');
      } else {
        btnEl.classList.remove('bg-[#1F3D20]', 'border-[#F5F4DA]/40');
        btnEl.classList.add('bg-gray-800/80', 'border-gray-500/40');
      }
    }
  }

  recenterUser() {
    this.setAutoFollow(true);
    if (this.userLat != null && this.userLng != null && this.map) {
      this.map.flyTo([this.userLat, this.userLng], 17, {
        animate: true,
        duration: 0.8
      });
    }
  }

  calculateHeading(lat1, lon1, lat2, lon2) {
    if (lat1 == null || lon1 == null || lat2 == null || lon2 == null) return 0;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const lat1Rad = lat1 * Math.PI / 180;
    const lat2Rad = lat2 * Math.PI / 180;

    const y = Math.sin(dLon) * Math.cos(lat2Rad);
    const x = Math.cos(lat1Rad) * Math.sin(lat2Rad) - Math.sin(lat1Rad) * Math.cos(lat2Rad) * Math.cos(dLon);

    let brng = Math.atan2(y, x) * 180 / Math.PI;
    return (brng + 360) % 360;
  }

  applyMapRotation(headingDeg) {
    if (!this.map) return;
    const mapPane = this.map.getPane('mapPane');
    if (!mapPane) return;

    if (this.isRotateMode && headingDeg != null && !isNaN(headingDeg)) {
      mapPane.style.transformOrigin = '50% 50%';
      mapPane.style.transition = 'transform 0.4s ease-out';
      mapPane.style.transform = `rotate(-${headingDeg}deg)`;

      // Counter-rotate markers and popups so text remains readable upright
      const markers = document.querySelectorAll('.gg-map-marker, .leaflet-popup-content-wrapper');
      markers.forEach(el => {
        el.style.transformOrigin = 'center center';
        el.style.transition = 'transform 0.4s ease-out';
        el.style.transform = `rotate(${headingDeg}deg)`;
      });
    } else {
      mapPane.style.transform = 'none';
      const markers = document.querySelectorAll('.gg-map-marker, .leaflet-popup-content-wrapper');
      markers.forEach(el => {
        el.style.transform = 'none';
      });
    }
  }

  startGpsTracking() {
    if (!navigator.geolocation) return;

    const options = {
      enableHighAccuracy: true,
      timeout: 15000,
      maximumAge: 0
    };

    let hasCentered = false;

    const handleGpsUpdate = (position) => {
      const lat = position.coords.latitude;
      const lng = position.coords.longitude;
      const hwHeading = position.coords.heading;

      // Calculate heading direction if user is walking
      if (hwHeading !== null && !isNaN(hwHeading)) {
        this.heading = hwHeading;
      } else if (this.lastLat !== null && this.lastLng !== null) {
        const movedDist = this.calculateDistanceMeters(this.lastLat, this.lastLng, lat, lng);
        if (movedDist && movedDist > 1.2) {
          this.heading = this.calculateHeading(this.lastLat, this.lastLng, lat, lng);
        }
      }

      this.userLat = lat;
      this.userLng = lng;

      if (!hasCentered && this.map) {
        this.map.setView([lat, lng], 17);
        hasCentered = true;
      }

      this.addUserMarker(lat, lng);
      this.applyMapRotation(this.heading);

      // Refresh nearby sighting distances when walking
      if (this.lastLat !== null && this.lastLng !== null) {
        const movedMeters = this.calculateDistanceMeters(this.lastLat, this.lastLng, lat, lng);
        if (movedMeters && movedMeters > 3) {
          this.refreshMarkers();
        }
      }

      this.lastLat = lat;
      this.lastLng = lng;
    };

    navigator.geolocation.getCurrentPosition(
      (position) => {
        handleGpsUpdate(position);
        this.refreshMarkers();
      },
      (err) => console.warn('Akses lokasi GPS ditolak/tidak tersedia:', err.message),
      options
    );

    this.watchId = navigator.geolocation.watchPosition(
      (position) => {
        handleGpsUpdate(position);
      },
      (err) => console.warn('GPS Watch Error:', err.message),
      options
    );
  }

  addUserMarker(lat, lng) {
    if (!this.map || lat == null || lng == null) return;
    this.userLat = lat;
    this.userLng = lng;

    const t = window.translations || {};
    const gpsText = t.gps_active || 'GPS Presisi Aktif';

    // Heading directional cone SVG rotated according to heading direction
    const headingDeg = Math.round(this.heading || 0);

    const userIcon = L.divIcon({
      className: 'user-gps-marker',
      html: `
        <div style="position:relative;width:36px;height:36px;display:flex;align-items:center;justify-content:center;">
          <!-- Walking Heading Direction Cone -->
          <div style="position:absolute;width:36px;height:36px;transform:rotate(${headingDeg}deg);transition:transform 0.3s ease-out;pointer-events:none;">
            <svg viewBox="0 0 36 36" style="width:100%;height:100%;">
              <path d="M18 2 L26 18 L18 14 L10 18 Z" fill="#3B82F6" opacity="0.85" />
            </svg>
          </div>

          <!-- Pulsing Radar Ring -->
          <div style="position:absolute;inset:6px;background-color:#3B82F6;border-radius:9999px;opacity:0.35;animation:ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>

          <!-- Blue GPS Center Dot -->
          <div style="position:relative;width:18px;height:18px;background-color:#2563EB;border:3px solid #FFFFFF;border-radius:9999px;box-shadow:0 2px 10px rgba(0,0,0,0.4);margin:auto;"></div>
        </div>
      `,
      iconSize: [36, 36],
      iconAnchor: [18, 18]
    });

    const targetLatLng = L.latLng(lat, lng);

    // Auto-pan map smooth camera follow as user walks
    if (this.isAutoFollow && this.map) {
      this.map.panTo(targetLatLng, { animate: true, duration: 0.5 });
    }

    // 1. Update/Create 50-meter Claim Radar Circle (Always bound to exact same LatLng)
    if (this.userLocationCircle) {
      this.userLocationCircle.setLatLng(targetLatLng);
    } else {
      this.userLocationCircle = L.circle(targetLatLng, {
        radius: 50, // 50 meters claim radius
        color: '#1F3D20',
        fillColor: '#1F3D20',
        fillOpacity: 0.12,
        weight: 2,
        dashArray: '6, 6'
      }).addTo(this.map).bindPopup('<b style="font-family:Baloo 2,sans-serif;font-size:11px;color:#1F3D20;">🎯 Radar Jangkauan Klaim Spesies (50 Meter)</b>');
    }
    if (this.userLocationCircle.bringToBack) {
      this.userLocationCircle.bringToBack();
    }

    // 2. Update/Create User GPS Marker (Centered at exact same LatLng)
    if (this.userLocationMarker) {
      this.userLocationMarker.setLatLng(targetLatLng);
      this.userLocationMarker.setIcon(userIcon);
    } else {
      this.userLocationMarker = L.marker(targetLatLng, { icon: userIcon, zIndexOffset: 1000 })
        .addTo(this.map)
        .bindPopup(`<b style="font-family:Baloo 2,sans-serif;">📍 ${gpsText}</b>`);
    }
    if (this.userLocationMarker.bringToFront) {
      this.userLocationMarker.bringToFront();
    }
  }

  async refreshMarkers() {
    if (!this.markersGroup) return;
    this.markersGroup.clearLayers();

    try {
      const endpoint = '/plant-sightings/nearby';

      const queryParams = (this.userLat !== null && this.userLng !== null)
        ? `?lat=${this.userLat}&lng=${this.userLng}`
        : '';

      const res = await window.apiClient.get(`${endpoint}${queryParams}`);
      const list = Array.isArray(res) ? res : (res.data || []);

      list.forEach((sighting) => {
        this.addSightingMarker(sighting);
      });

      this.applyMapRotation(this.heading);
    } catch (err) {
      console.warn('Gagal memuat marker temuan:', err.message);
    }
  }

  calculateDistanceMeters(lat1, lon1, lat2, lon2) {
    if (lat1 == null || lon1 == null || lat2 == null || lon2 == null) return null;
    const R = 6371000;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
      Math.sin(dLon / 2) * Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
  }

  addSightingMarker(sighting) {
    if (!this.map || !this.markersGroup || !sighting.latitude || !sighting.longitude) return;

    const t = window.translations || {};
    const speciesName = sighting.species ? sighting.species.common_name : 'Tumbuhan Nyata';
    const speciesCode = sighting.species ? sighting.species.species_code : 'FLORA';
    const photoUrl = sighting.photo_url || '';
    const isDiscovered = sighting.sudah_ditemukan;

    const isRangerOrAdmin = ['ranger', 'admin'].includes(this.userRole);
    const rawMystery = t.mystery_plant || 'Tanaman Misterius';
    const mysteryPlantName = rawMystery.replace(/^❓\s*/, '');
    const displayName = (isRangerOrAdmin || isDiscovered) ? speciesName : mysteryPlantName;
    const iconColor = isDiscovered ? '#1F3D20' : (isRangerOrAdmin ? '#8B6A4C' : '#D96C63');
    const iconLabel = isDiscovered ? '🌿' : (isRangerOrAdmin ? '📍' : '❓');

    const markerHtml = `
      <div style="background-color:#FBFAF0;border:2px solid ${iconColor};padding:4px 10px;border-radius:9999px;font-family:'Baloo 2',sans-serif;font-size:11px;font-weight:bold;color:${iconColor};box-shadow:0 3px 8px rgba(0,0,0,0.2);white-space:nowrap;display:inline-flex;align-items:center;gap:4px;max-width:200px;box-sizing:border-box;">
        <span style="flex-shrink:0;">${iconLabel}</span>
        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:150px;display:inline-block;">${displayName}</span>
      </div>
    `;

    const customTagIcon = L.divIcon({
      className: 'gg-map-marker',
      html: markerHtml,
      iconSize: null,
      iconAnchor: [60, 14],
    });

    let popupHtml = '';

    if (this.userRole === 'viewer') {
      const verifiedText = t.verified_badge || 'Spesies Terverifikasi';
      const discoverText = t.discover_button || '✨ Temukan & Klaim!';
      const alreadyDiscoveredText = t.already_discovered || '✓ Sudah Ditemukan';
      const unclaimedBadge = t.unclaimed_badge || '🔒 Belum Diklaim';
      const unclaimedTreeText = t.unclaimed_tree || 'Pohon ini belum diklaim! Tekan tombol di bawah untuk membuka dan mengklaim.';

      // Calculate distance between user position & sighting location
      const distanceMeters = this.calculateDistanceMeters(
        this.userLat,
        this.userLng,
        parseFloat(sighting.latitude),
        parseFloat(sighting.longitude)
      );

      let distanceBadge = '';
      let isClaimable = true;
      let buttonLabelText = discoverText;

      if (distanceMeters !== null) {
        const roundDist = Math.round(distanceMeters);
        if (roundDist > 50) {
          isClaimable = false;
          distanceBadge = `<div style="font-size:10px;color:#DC2626;font-weight:bold;margin-bottom:6px;text-align:center;background-color:#FEE2E2;padding:2px 6px;border-radius:9999px;">📍 Jarak: ${roundDist}m (Maks 50m)</div>`;
          buttonLabelText = `🔒 Terlalu Jauh (${roundDist}m > 50m)`;
        } else {
          distanceBadge = `<div style="font-size:10px;color:#16A34A;font-weight:bold;margin-bottom:6px;text-align:center;background-color:#DCFCE7;padding:2px 6px;border-radius:9999px;">📍 Jarak: ${roundDist}m (Dalam Jangkauan)</div>`;
        }
      } else {
        distanceBadge = `<div style="font-size:10px;color:#D97706;font-weight:bold;margin-bottom:6px;text-align:center;background-color:#FEF3C7;padding:2px 6px;border-radius:9999px;">📍 Aktifkan GPS untuk mengklaim (Maks 50m)</div>`;
      }

      // Viewer Popup with "Temukan!" action (Hides real species name until claimed)
      popupHtml = `
        <div style="font-family:Nunito,sans-serif;max-width:220px;color:#2A2A22;padding:4px;box-sizing:border-box;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
            <span style="background-color:#E2E1C4;color:#1F3D20;font-family:Baloo 2;font-size:10px;font-weight:bold;padding:1px 6px;border-radius:9999px;">${isDiscovered ? speciesCode : 'MYSTERY'}</span>
            <span style="font-size:10px;color:#6B6B55;font-weight:bold;">${isDiscovered ? '✓ ' + verifiedText : unclaimedBadge}</span>
          </div>

          <h4 style="font-family:Baloo 2,sans-serif;font-weight:800;font-size:15px;margin:2px 0 6px 0;color:#1F3D20;line-height:1.2;word-break:break-word;">
            ${isDiscovered ? speciesName : '❓ ' + mysteryPlantName}
          </h4>

          ${isDiscovered 
            ? (photoUrl ? `<img src="${photoUrl}" style="width:100%;height:105px;object-fit:cover;border-radius:12px;margin-bottom:8px;border:1.5px solid rgba(31,61,32,0.15);"/>` : '')
            : `<div style="background-color:rgba(226,225,196,0.4);border:1.5px dashed #8B6A4C;border-radius:12px;padding:10px 12px;text-align:center;margin-bottom:8px;font-size:11px;color:#6B6B55;font-style:italic;word-break:break-word;overflow-wrap:break-word;box-sizing:border-box;">
                ${unclaimedTreeText}
               </div>`
          }

          ${!isDiscovered ? distanceBadge : ''}

          ${isDiscovered 
            ? `<button disabled style="width:100%;background-color:#1F3D20;color:#F5F4DA;font-family:Baloo 2;font-weight:bold;font-size:12px;padding:7px 0;border-radius:9999px;border:none;cursor:default;">${alreadyDiscoveredText}</button>`
            : (!isClaimable
                ? `<button disabled style="width:100%;background-color:#9CA3AF;color:#FFFFFF;font-family:Baloo 2;font-weight:bold;font-size:11px;padding:7px 0;border-radius:9999px;border:none;cursor:not-allowed;box-shadow:none;">${buttonLabelText}</button>`
                : `<button id="discover-btn-${sighting.id}" onclick="window.discoverPlantFromMap(${sighting.id})" style="width:100%;background-color:#1F3D20;color:#F5F4DA;font-family:Baloo 2;font-weight:bold;font-size:12px;padding:7px 0;border-radius:9999px;border:none;cursor:pointer;box-shadow:0 3px 8px rgba(0,0,0,0.2);">${discoverText}</button>`
              )
          }
        </div>
      `;
    } else {
      // Ranger / Admin Popup with Edit Action
      const editDataBtnText = t.edit_data_button || '✏️ Edit Data Tumbuhan';
      const statusLabelText = t.status_label || 'Status';

      popupHtml = `
        <div style="font-family:Nunito,sans-serif;max-width:210px;color:#2A2A22;padding:4px;box-sizing:border-box;">
          <span style="background-color:#8B6A4C;color:#F5F4DA;font-family:Baloo 2;font-size:10px;font-weight:bold;padding:1px 6px;border-radius:9999px;">${this.userRole.toUpperCase()} SIGHTING</span>
          <h4 style="font-family:Baloo 2,sans-serif;font-weight:800;font-size:15px;margin:4px 0;color:#1F3D20;word-break:break-word;">${speciesName}</h4>
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
