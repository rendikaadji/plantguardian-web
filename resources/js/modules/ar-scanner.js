/**
 * AR Scanner Module (WebAR Camera & Frame Capture)
 * Rujuk docs/architecture.md §2.2 & §5 serta docs/rules.md §4.1 (Privasi Lokasi)
 */

import apiClient from '../api-client.js';

export class ArScanner {
  constructor(options = {}) {
    this.videoElement = options.videoElement || document.querySelector('#ar-video');
    this.canvasElement = options.canvasElement || document.createElement('canvas');
    this.stream = null;
    this.isScanning = false;
  }

  /**
   * Initialize camera stream feed
   */
  async init() {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      console.warn('Akses kamera (getUserMedia) membutuhkan koneksi HTTPS atau host localhost.');
      const warningEl = document.querySelector('#camera-warning');
      if (warningEl) {
        warningEl.classList.remove('hidden');
        warningEl.textContent = 'Kamera AR memerlukan protokol HTTPS (atau http://localhost:8000) sesuai aturan keamanan browser.';
      }
      return;
    }

    try {
      if (this.videoElement) {
        this.stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } },
          audio: false,
        });
        this.videoElement.srcObject = this.stream;
        await this.videoElement.play();
      }
    } catch (error) {
      console.warn('Gagal mengakses kamera:', error);
    }
  }

  /**
   * Get fresh GPS location moment of capture (STRICT PRIVACY RULE: getCurrentPosition ONCE per capture)
   */
  fetchCurrentLocation() {
    return new Promise((resolve) => {
      if (typeof navigator === 'undefined' || !navigator.geolocation || typeof navigator.geolocation.getCurrentPosition !== 'function') {
        return resolve({ latitude: null, longitude: null });
      }

      navigator.geolocation.getCurrentPosition(
        (pos) => {
          resolve({
            latitude: pos.coords.latitude,
            longitude: pos.coords.longitude,
          });
        },
        (err) => {
          console.warn('Lokasi GPS tidak dapat diakses saat capture:', err.message);
          resolve({ latitude: null, longitude: null });
        },
        { enableHighAccuracy: false, timeout: 4000 }
      );
    });
  }

  /**
   * Capture frame from video feed as base64 string
   */
  captureFrame() {
    if (!this.videoElement || this.videoElement.readyState !== 4) {
      throw new Error('Kamera belum siap atau tidak diizinkan di asal koneksi non-HTTPS.');
    }

    const width = this.videoElement.videoWidth || 640;
    const height = this.videoElement.videoHeight || 480;

    this.canvasElement.width = width;
    this.canvasElement.height = height;

    const ctx = this.canvasElement.getContext('2d');
    ctx.drawImage(this.videoElement, 0, 0, width, height);

    return this.canvasElement.toDataURL('image/jpeg', 0.85);
  }

  /**
   * Perform plant scan by capturing current frame and getting fresh GPS location at exact moment
   */
  async performScan() {
    if (this.isScanning) return;
    this.isScanning = true;

    try {
      const base64Image = this.captureFrame();

      // Get fresh GPS coordinates at the exact moment of shutter press
      const coords = await this.fetchCurrentLocation();

      const payload = {
        image_base64: base64Image,
        latitude: coords.latitude,
        longitude: coords.longitude,
      };

      const result = await apiClient.post('/scan', payload);
      this.isScanning = false;
      return result;
    } catch (error) {
      this.isScanning = false;
      throw error;
    }
  }

  /**
   * Stop camera stream
   */
  stop() {
    if (this.stream) {
      this.stream.getTracks().forEach((track) => track.stop());
      this.stream = null;
    }
  }
}

export default ArScanner;
