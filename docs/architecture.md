# Architecture — PlantGuardian

> Rujuk `prd.md` untuk definisi fitur. Dokumen ini menjelaskan **bagaimana** sistem dibangun secara teknis. AI wajib mengikuti struktur ini secara konsisten di setiap sesi vibe coding — jangan memperkenalkan framework/library baru di luar yang tercantum tanpa alasan kuat dan tanpa mencatatnya di sini terlebih dahulu.

## 1. Gambaran Umum (High-Level)

PlantGuardian adalah sistem **3-layer** yang terpisah tanggung jawabnya:

```
┌─────────────────────────┐        ┌──────────────────────────┐        ┌───────────────────────────┐
│   1. FRONTEND / WebAR    │  HTTP  │   2. BACKEND (Laravel)     │  HTTP  │  3. AI / CV SERVICE (Py)   │
│  Browser HP Pengguna     │ <────> │  Server Aplikasi Utama     │ <────> │  Microservice Klasifikasi  │
│  HTML5 + JS + AR.js      │  JSON  │  Auth, DB, Bisnis Logic    │  JSON  │  OpenCV + TensorFlow/ANN   │
└─────────────────────────┘        └──────────────────────────┘        └───────────────────────────┘
                                              │
                                              ▼
                                     ┌──────────────────┐
                                     │   MySQL Database   │
                                     └──────────────────┘
```

**Prinsip inti:** frontend tidak pernah bicara langsung ke AI service. Semua request dari browser masuk ke Laravel dulu, Laravel yang menjadi orkestrator (source of truth untuk auth, data, dan bisnis logic), lalu Laravel yang meneruskan ke Python service bila perlu klasifikasi gambar.

## 2. Layer 1 — Frontend & WebAR

### 2.1 Teknologi
- HTML5, CSS3, JavaScript (Vanilla JS), Alpine.js untuk state/interaktivitas ringan.
- **AR.js / MindAR** untuk modul Scan Tumbuhan (baca kamera browser, overlay info di atas objek yang disorot).
- **HTML5 Canvas** untuk modul MiniGame (kebun virtual): render lahan, tanaman, progress bar, animasi sederhana.
- Tidak menggunakan framework SPA berat (React/Vue) di proyek ini — cukup Vanilla JS + Alpine.js agar ringan di browser HP kelas menengah-bawah.

### 2.2 Struktur Folder (Frontend, disajikan sebagai Laravel `public/` atau `resources/` assets)

```
resources/
  js/
    app.js                 # entry point, inisialisasi Alpine
    api-client.js          # wrapper fetch() ke Laravel API (base URL, auth header, error handling)
    modules/
      ar-scanner.js         # inisialisasi AR.js/MindAR, capture frame, kirim ke API scan
      minigame.js            # logic canvas: render lahan, tanam, siram, panen
      gallery.js             # render daftar tumbuhan tersimpan (penyimpanan/galeri)
      home.js                # navigasi antar screen dari Home Screen
  css/
    app.css
views/
  home.blade.php
  peta.blade.php            # Peta/Main (AR Scan)
  galeri.blade.php          # Penyimpanan/Galeri
  minigame.blade.php        # MiniGame kebun virtual
```

### 2.3 Alur Komunikasi Frontend → Backend

- Semua panggilan API menggunakan **satu wrapper** (`api-client.js`) — jangan ada `fetch()` mentah tersebar di banyak file.
- Autentikasi memakai token/session dari Laravel Sanctum, disimpan di memory/cookie httpOnly (bukan localStorage untuk token sensitif).
- Setiap modul (`ar-scanner.js`, `minigame.js`, `gallery.js`) hanya bertanggung jawab atas satu fitur — tidak boleh saling memanggil fungsi internal modul lain secara langsung, komunikasi antar modul lewat event atau lewat `api-client.js`.

## 3. Layer 2 — Backend (Laravel)

### 3.1 Tanggung Jawab
1. **Autentikasi & Role Management** — membedakan `ranger` dan `viewer` (lihat `schema.md` tabel `users`).
2. **Routing & REST API** — endpoint untuk Home, Peta/Scan, Galeri, MiniGame.
3. **Database MySQL** — relasi user, katalog tumbuhan (input Ranger), hasil temuan (Viewer), inventaris minigame, transaksi coin/EXP.
4. **Orkestrasi ke AI Service** — meneruskan gambar dari frontend ke Python service, menerima hasil klasifikasi, menyimpannya sebagai temuan (sighting).

### 3.2 Struktur Folder (Clean & Thin Controller Discipline)

```
app/
  Http/
    Controllers/
      Api/
        ScanController.php        # terima gambar, panggil PlantScanService
        GalleryController.php      # CRUD sighting milik user
        MiniGameController.php     # aksi tanam/rawat/panen
        WalletController.php       # klaim EXP/Coin
    Requests/
      ScanRequest.php              # validasi upload gambar
      PlantSightingRequest.php
      PlantingRequest.php
  Services/
    PlantScanService.php           # panggil Python AI service, parsing hasil
    GardenService.php              # logic tanam/rawat/cek siap panen/panen
    RewardService.php              # hitung & catat EXP/Coin
  Models/
    User.php
    PlantSpecies.php               # katalog, diisi Ranger
    PlantSighting.php              # temuan Viewer (scan hasil)
    GardenPlot.php
    Planting.php
    InventoryItem.php
    CoinTransaction.php
    ExpLog.php
  Http/Resources/
    PlantSightingResource.php
    PlantingResource.php
routes/
  api.php
```

### 3.3 Aturan Wajib (ringkas — detail lengkap di `rules.md`)
- **Controller tetap tipis**: hanya menerima Form Request, memanggil Service, mengembalikan Resource/JSON. Tidak ada query Eloquent kompleks langsung di controller.
- **Form Request** wajib untuk semua input dari user (upload gambar, data planting, dsb).
- **Service class** memegang seluruh business logic (contoh: `GardenService::checkIfReadyToHarvest()`).
- Query yang menyentuh data milik user wajib **scoped** (`where('user_id', auth()->id())` atau global scope) agar Viewer tidak bisa mengakses data Viewer lain.

## 4. Layer 3 — AI & Computer Vision Service (Python)

### 4.1 Teknologi
- Python + **FastAPI** (disarankan dibanding Flask untuk validasi tipe otomatis dan performa async, tapi Flask tetap valid pilihan bila tim lebih familiar).
- **OpenCV** untuk pra-pemrosesan gambar (resize, normalisasi, crop daun).
- **TensorFlow / ANN** untuk model klasifikasi jenis tumbuhan.

### 4.2 Kontrak API (Laravel ↔ Python)

**Endpoint:** `POST /classify`

Request:
```json
{
  "image_base64": "<string>",
  "request_id": "<uuid dari Laravel, untuk tracing/log>"
}
```

Response (sukses):
```json
{
  "success": true,
  "predicted_species_code": "MANGIFERA_INDICA",
  "confidence": 0.92
}
```

Response (gagal / tidak yakin):
```json
{
  "success": false,
  "reason": "confidence_too_low",
  "confidence": 0.31
}
```

- Python service **stateless** — tidak menyimpan data pengguna, tidak terhubung langsung ke MySQL utama. Semua penyimpanan hasil tetap tanggung jawab Laravel.
- `predicted_species_code` harus dicocokkan Laravel ke tabel `plant_species` (data yang diinput Ranger) — bila kode tidak ditemukan, dianggap gagal identifikasi.

### 4.3 Struktur Folder (Python service)

```
ai_service/
  main.py                # entry point FastAPI/Flask
  models/
    plant_classifier.h5   # model terlatih
  services/
    preprocessing.py      # OpenCV: resize, normalize
    classifier.py         # load model, predict
  requirements.txt
```

## 5. Alur Data End-to-End (Contoh: Fitur Scan)

1. Viewer buka **Peta/Main** → kamera aktif via AR.js/MindAR.
2. Frame gambar tumbuhan di-capture di frontend → dikirim ke `POST /api/scan` (Laravel).
3. `ScanController` validasi via `ScanRequest` → panggil `PlantScanService`.
4. `PlantScanService` mengirim gambar ke Python `/classify`.
5. Python mengembalikan `predicted_species_code` + confidence.
6. `PlantScanService` mencocokkan kode ke tabel `plant_species` (data Ranger) → ambil detail (nama, deskripsi).
7. Laravel membuat record `PlantSighting` baru untuk Viewer tsb, memanggil `RewardService` untuk kasih EXP/Coin.
8. Response JSON dikirim balik ke frontend → ditampilkan sebagai overlay AR + opsi "Simpan ke Penyimpanan/Galeri".

## 6. Environment & Konfigurasi

| Variabel | Layer | Keterangan |
|---|---|---|
| `AI_SERVICE_URL` | Laravel `.env` | Base URL Python service, contoh `http://ai-service:8000` |
| `AI_SERVICE_TIMEOUT` | Laravel `.env` | Timeout request ke AI service (detik) |
| `SANCTUM_STATEFUL_DOMAINS` | Laravel `.env` | Domain frontend yang diizinkan auth |
| `MODEL_PATH` | Python `.env` | Lokasi file model klasifikasi |

## 7. Deployment (Ringkas)

- Laravel dan Python service di-deploy sebagai service terpisah (bisa dua container/dua proses), berkomunikasi lewat HTTP internal.
- MySQL sebagai database tunggal, hanya diakses oleh Laravel.
- Frontend disajikan sebagai Blade view dari Laravel (tidak perlu deployment terpisah) kecuali diputuskan lain di kemudian hari.

## 8. Batasan Arsitektur (Jangan Dilanggar)

- Frontend **tidak boleh** memanggil Python service secara langsung — selalu lewat Laravel.
- Python service **tidak boleh** menulis ke MySQL utama — hanya mengembalikan hasil klasifikasi.
- Tidak menambah database kedua (NoSQL, dsb) tanpa alasan kuat dan tanpa update dokumen ini.
