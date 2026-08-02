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
- **Leaflet.js + OpenStreetMap** untuk peta real di fitur Peta/Main (ringan, gratis, tanpa API key/billing — cocok untuk stack yang sudah dijaga tetap ringan). Alternatif Google Maps JS API bisa dipakai kalau butuh fitur lebih lengkap, tapi berbayar & butuh API key — tidak dipilih di versi awal.
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
      map.js                 # inisialisasi Leaflet, tampilkan lokasi user + marker temuan
      minigame.js            # logic canvas: render lahan, tanam, siram, panen
      compost.js              # logic tantangan kompos: mulai proses, check-in, real planting
      gallery.js             # render daftar tumbuhan tersimpan (penyimpanan/galeri)
      leaderboard.js          # render papan peringkat mingguan
      home.js                # navigasi antar screen dari Home Screen
  css/
    app.css
views/
  home.blade.php
  peta.blade.php            # Peta/Main (Peta Real + AR Scan)
  galeri.blade.php          # Penyimpanan/Galeri
  minigame.blade.php        # MiniGame kebun virtual
  compost/                   # Tantangan Kompos & Real Planting
  leaderboard.blade.php
```

### 2.4 Peta Real (Leaflet.js)

- `map.js` inisialisasi peta dengan `L.map()`, tile layer dari OpenStreetMap (`https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png` — gratis untuk trafik wajar, tidak butuh API key untuk skala proyek sekolah).
- Lokasi pengguna diambil lewat `navigator.geolocation.getCurrentPosition` (sesuai prinsip privasi di §4.4 — sesaat, bukan `watchPosition` berkelanjutan) untuk memusatkan peta.
- Marker temuan diambil dari endpoint `GET /api/plant-sightings/nearby?lat=...&lng=...&radius=...` (Laravel, query `plant_sightings` yang punya `latitude`/`longitude` terisi — kolom ini sudah ada di `schema.md` §4, tidak perlu tabel baru).
- Marker dirender pakai custom icon bergaya "kartu spesimen mini" sesuai `design.md` §1.3, bukan pin default Leaflet.

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
    Middleware/
      EnsureUserIsRanger.php     # cek role === 'ranger', dipakai di route group Ranger
    Controllers/
      Api/
        ScanController.php        # (RANGER only, middleware 'ranger') terima gambar, panggil PlantScanService
        MapController.php          # GET nearby plant_sightings untuk marker peta (middleware 'viewer')
        DiscoveryController.php    # (VIEWER only, middleware 'viewer') POST catch/temukan sighting -> plant_discoveries
        GalleryController.php      # CRUD sighting milik user
        MiniGameController.php     # aksi tanam/rawat/panen
        WalletController.php       # klaim EXP/Coin
      Ranger/
        SpeciesCatalogController.php       # CRUD plant_species
        CompostCatalogController.php       # CRUD compost_materials
        VerificationController.php         # list queue + verifikasi sighting/real_planting
    Requests/
      ScanRequest.php              # validasi upload gambar
      PlantSightingRequest.php
      PlantingRequest.php
      Ranger/
        SpeciesRequest.php
        CompostMaterialRequest.php
        VerificationDecisionRequest.php
  Services/
    PlantScanService.php           # panggil Python AI service, parsing hasil (dipakai RANGER)
    DiscoveryService.php            # logic Viewer "catch" sighting -> plant_discoveries, cek unik, panggil RewardService
    GardenService.php              # logic tanam/rawat/cek siap panen/panen (MiniGame virtual)
    RewardService.php              # hitung & catat EXP/Coin
    CompostService.php             # logic tantangan kompos: mulai proses, check-in, tandai matang, catat real planting
    LeaderboardService.php         # hitung peringkat mingguan dari exp_logs, simpan snapshot ke weekly_rewards
    SpeciesCatalogService.php      # CRUD logic katalog spesies (Ranger)
    CompostCatalogService.php      # CRUD logic katalog bahan kompos (Ranger)
    VerificationService.php        # logic tandai verified/rejected pada sighting & real_planting
  Models/
    User.php
    PlantSpecies.php               # katalog, diisi Ranger
    PlantSighting.php              # hasil scan RANGER di lapangan (rename user_id->ranger_id)
    PlantDiscovery.php              # koleksi "catch" milik VIEWER (tabel baru, lihat schema.md 4a)
    GardenPlot.php
    Planting.php
    InventoryItem.php
    CoinTransaction.php
    ExpLog.php
    CompostMaterial.php            # katalog bahan kompos, diisi Ranger
    CompostProcess.php              # proses kompos aktif milik Viewer
    CompostProgressLog.php          # riwayat check-in kompos
    RealPlanting.php                 # bukti penanaman pohon nyata
    WeeklyReward.php                 # snapshot peringkat & penghargaan mingguan
  Http/Resources/
    PlantSightingResource.php
    PlantDiscoveryResource.php
    PlantingResource.php
    CompostProcessResource.php
    RealPlantingResource.php
    WeeklyRewardResource.php
    Ranger/
      SpeciesResource.php
      CompostMaterialResource.php
  Console/
    Commands/
      CalculateWeeklyLeaderboard.php  # artisan command, dijadwalkan via scheduler (lihat §6)
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

## 4.4 Izin Lokasi (Frontend)

- Diminta lewat **browser Geolocation API** (`navigator.geolocation.getCurrentPosition`), bukan dari native app permission.
- Diminta **satu kali di alur onboarding** (setelah login, sebelum tutorial) untuk memberi konteks ke pengguna kenapa lokasi dibutuhkan.
- **Prinsip privasi wajib:** lokasi **tidak disimpan sebagai data pelacakan berkelanjutan**. Koordinat hanya diambil ulang dan dikirim ke backend **pada momen upload bukti aksi nyata** (check-in kompos, bukti tanam pohon) — lihat kolom `latitude`/`longitude` di `compost_progress_logs` dan `real_plantings` (`schema.md` §13–14). Tidak ada tabel `user_locations` atau kolom lokasi permanen di tabel `users`.
- Jika pengguna menolak izin lokasi, fitur Tantangan Kompos & Real Planting tetap bisa dipakai tapi kolom lokasi disimpan `null` — jangan blokir fitur hanya karena lokasi ditolak.

## 4.5 Tantangan Kompos & Real Planting (Backend)

- Endpoint terkait (semua lewat `CompostService`, controller tetap tipis sesuai `rules.md`):
    - `POST /api/compost-processes` — mulai proses kompos baru (pilih `compost_material_id` atau bebas).
    - `POST /api/compost-processes/{id}/checkin` — kirim `CompostProgressLog` baru (foto, stage_label, lokasi opsional).
    - `POST /api/compost-processes/{id}/mature` — tandai proses sebagai `matured` (dipicu Viewer secara self-report).
    - `POST /api/real-plantings` — catat bukti tanam pohon nyata, opsional relasi ke `compost_process_id` yang sudah matang.
- Setiap endpoint di atas memanggil `RewardService` untuk mencatat EXP sesuai milestone (lihat `prd.md` §4.5).

## 4.6 Papan Peringkat Mingguan (Backend)

- `LeaderboardService::calculateForWeek(Carbon $weekStart)`:
    1. Ambil total EXP tiap user dari `exp_logs` dalam rentang `weekStart` s/d `weekStart + 6 hari`.
    2. Urutkan, tentukan `rank`.
    3. Simpan snapshot ke tabel `weekly_rewards` (satu baris per user per minggu).
- Dijalankan otomatis lewat **Laravel Scheduler** (`routes/console.php` atau `Kernel.php`), dijadwalkan tiap Senin 00:00 (awal minggu baru → tutup minggu sebelumnya).
- Endpoint baca: `GET /api/leaderboard/current` (peringkat minggu berjalan, dihitung real-time dari `exp_logs` tanpa nunggu job) dan `GET /api/leaderboard/history` (baca dari snapshot `weekly_rewards`).

## 4.7 Dashboard & Endpoint Ranger (Backend)

- **Proteksi akses:** semua route Ranger dikelompokkan dengan middleware `ranger` (alias untuk `EnsureUserIsRanger`), dicek dari kolom `users.role === 'ranger'`. Route Viewer tidak boleh bisa diakses lewat akun Ranger jika logic-nya spesifik role (walau di versi ini kebanyakan data katalog bersifat baca-publik untuk kedua role).
- **Katalog bersama, bukan personal:** query katalog spesies & bahan kompos **tidak** discope per `created_by` untuk keperluan baca (Ranger manapun bisa lihat semua katalog) — scoping `created_by` hanya dipakai untuk mencatat siapa yang menginput, bukan untuk membatasi akses baca. Ini pengecualian sadar terhadap aturan umum scoping di `rules.md` §2.4 (yang berlaku untuk data personal seperti `plant_sightings` milik Viewer), karena katalog memang didesain sebagai data bersama.

**Endpoint:**

- `GET/POST/PUT/DELETE /api/ranger/species` — CRUD `plant_species` lewat `SpeciesCatalogController` + `SpeciesCatalogService`.
- `GET/POST/PUT/DELETE /api/ranger/compost-materials` — CRUD `compost_materials` lewat `CompostCatalogController` + `CompostCatalogService`.
- `GET /api/ranger/verifications/pending` — daftar `plant_sightings` (status `pending`) dan `real_plantings` (status `self_reported`) yang menunggu tinjauan.
- `POST /api/ranger/verifications/sightings/{id}` — `VerificationController` memanggil `VerificationService::verifySighting()`, body berisi keputusan (`verified`/`rejected`).
- `POST /api/ranger/verifications/real-plantings/{id}` — sama, untuk `real_plantings`.
- Setiap keputusan verifikasi mengisi `verified_by` (user Ranger yang login) dan `verified_at` (lihat `schema.md` §4 dan §14).

## 4.8 Pemisahan Total Akses Viewer vs Ranger (Wajib)

Kesalahan yang pernah terjadi: hanya route Ranger yang dilindungi middleware, route Viewer dibiarkan terbuka untuk siapa saja yang login — akibatnya Ranger bisa mengakses Home/Peta/Galeri/MiniGame Viewer dan sebaliknya. Untuk mencegah ini:

- **Middleware `EnsureUserIsViewer`** (baru, pasangan dari `EnsureUserIsRanger` di §4.7): mengecek `auth()->user()->role === 'viewer'`, dipasang di seluruh route group Viewer (`home`, `peta`, `galeri`, `minigame`, `compost/*`, `leaderboard`).
- **Redirect berbasis role setelah login:** dibuat di `LoginResponse` custom (jika pakai Laravel Fortify) atau di method `authenticated()` pada `LoginController`/`AuthenticatedSessionController` (jika pakai Breeze) — cek `role` user yang baru login, arahkan ke:
    - `role === 'ranger'` → route `ranger.dashboard`
    - `role === 'viewer'` → route `home`
- Redirect ini berlaku **setiap kali login**, tidak hanya sekali di alur onboarding pilih-role. Alur onboarding pilih-role tetap dipakai khusus untuk **penentuan role pertama kali** (saat `role` user masih kosong/null setelah register).
- Navigasi (nav bar) juga wajib dirender kondisional sesuai role — Viewer tidak boleh melihat link ke Dashboard Ranger di nav bar-nya, begitu pula sebaliknya.

## 5. Alur Data End-to-End (Contoh: Fitur Scan — **sekarang milik Ranger**)

> **Perubahan penting:** langkah-langkah di bawah ini sebelumnya ditulis untuk Viewer. Sekarang **pelakunya adalah Ranger** — route `POST /api/scan` wajib dibungkus middleware `ranger` (`EnsureUserIsRanger`), bukan `viewer`.

1. Ranger buka **Peta/Main** → peta real menampilkan posisi device saat ini (titik biru, dari `navigator.geolocation.getCurrentPosition` sekali saat halaman dibuka — bukan `watchPosition`, sesuai `rules.md` §4.1).
2. Ranger tekan tombol kamera → kamera aktif via AR.js/MindAR.
3. Saat tombol shutter/capture ditekan: frontend mengambil **koordinat lokasi saat itu juga** DAN gambar tumbuhan, lalu keduanya dikirim **dalam satu request** ke `POST /api/scan` (Laravel, middleware `ranger`).
4. `ScanController` validasi via `ScanRequest` (menerima `image`, `latitude` nullable, `longitude` nullable) → panggil `PlantScanService`.
5. `PlantScanService` mengirim gambar ke Python `/classify`.
6. Python mengembalikan `predicted_species_code` + confidence.
7. `PlantScanService` mencocokkan kode ke tabel `plant_species` → ambil detail (nama, deskripsi).
8. Laravel membuat record `PlantSighting` baru dengan `ranger_id` = Ranger yang scan, status **`pending`** — **termasuk `latitude`/`longitude`** dari langkah 3 (`schema.md` §4). **Tidak** langsung memberi EXP/Coin ke sighting yang masih `pending` — EXP scan diberikan setelah sighting itu diverifikasi (lihat §4.7), untuk mencegah Ranger menyalahgunakan scan asal-asalan demi EXP.
9. Response JSON dikirim balik ke frontend Ranger → overlay AR menampilkan hasil + status "Menunggu verifikasi".
10. Marker BELUM muncul untuk Viewer sampai status berubah `verified` oleh Ranger lain (lihat §4.7).

**Catatan privasi:** jika Ranger menolak izin lokasi, langkah 3 tetap lanjut dengan `latitude`/`longitude` bernilai `null` (`rules.md` §4.1, §7).

## 5.0a Alur Data End-to-End (Baru: Viewer "Menemukan"/Catch di Peta)

1. Viewer buka **Peta/Main** → `map.js` memanggil `GET /api/plant-sightings/nearby?lat=&lng=&radius=` (`MapController`, middleware `viewer`) — query **hanya** `plant_sightings` dengan `verification_status = 'verified'`.
2. Marker ditampilkan sebagai kartu koleksi (§design.md 1.3 poin 4) — terkunci (ikon "?") jika belum pernah di-catch Viewer ini, terbuka jika sudah ada di `plant_discoveries` miliknya.
3. Viewer tap marker terkunci → bottom sheet muncul, tombol **"Temukan!"**.
4. Tombol ditekan → `POST /api/plant-discoveries` (body: `plant_sighting_id`, lokasi Viewer opsional) → `DiscoveryController` (atau ditambahkan ke `MapController`) memanggil `DiscoveryService`.
5. `DiscoveryService` cek constraint unik (`user_id` + `plant_sighting_id` belum pernah ada) → buat record `plant_discoveries` baru → panggil `RewardService` untuk EXP/Coin.
6. Response dikirim balik → marker di peta & kartu di Galeri (Seedex) langsung update jadi "ditemukan" tanpa reload halaman.

## 5.1 Alur Data End-to-End (Contoh: Tantangan Kompos → Real Planting)

1. Viewer buka fitur **Tantangan Kompos** dari Home Screen.
2. Sistem menampilkan daftar `compost_materials` — **Viewer memilih langsung dari daftar** (bukan scan, karena Scan kini eksklusif Ranger).
3. Viewer pilih bahan → `POST /api/compost-processes` → `CompostService` buat record `CompostProcess` status `started`, `RewardService` beri EXP awal.
4. Sistem tampilkan instruksi (`compost_materials.instructions`).
5. Secara berkala, Viewer check-in: `POST /api/compost-processes/{id}/checkin` — upload foto + label tahap + lokasi (jika izin diberikan) → tersimpan sebagai `CompostProgressLog`, EXP kecil diberikan tiap check-in.
6. Setelah dirasa matang, Viewer tandai `POST /api/compost-processes/{id}/mature` → status berubah `matured`, `matured_at` diisi, EXP diberikan.
7. Viewer memakai kompos untuk tanam pohon nyata → `POST /api/real-plantings` (foto, lokasi, `compost_process_id`, jenis pohon) → status `compost_processes` berubah jadi `used_for_planting`, `RewardService` memberi EXP milestone terbesar.
8. Semua EXP dari langkah 3–7 otomatis terakumulasi ke `exp_logs` → dipakai `LeaderboardService` untuk peringkat mingguan.

## 4.9 Bahasa (i18n) — Bahasa Inggris Default, Pilihan Bahasa Indonesia

**Prinsip inti: satu sumber teks per key, tidak ada teks hardcode di Blade/JS manapun.** Ini yang menjamin konsistensi — lihat aturan wajib di `rules.md` §4.2.

### Struktur file bahasa (Laravel)

```
lang/
  en/
    app.php          # umum: nav, tombol, label bersama
    auth.php          # login, register, pilih role
    home.php           # Home/Beranda
    map.php             # Peta/Main (scan Ranger, catch Viewer)
    gallery.php          # Galeri/Seedex
    minigame.php          # MiniGame, Bag, Shop
    compost.php            # Tantangan Kompos, Real Planting
    leaderboard.php
    ranger.php              # Dashboard Ranger, Katalog, Verifikasi
  id/
    (struktur file sama persis, isi terjemahan Indonesia)
```

- Setiap file **wajib punya key yang sama persis** di `en/` dan `id/` — kalau nambah key baru, tambahkan di KEDUA bahasa di saat yang sama, jangan cuma satu.
- Blade memakai `{{ __('map.catch_button') }}`, bukan teks literal.

### Middleware & penyimpanan preferensi

- `SetLocale` middleware (baru): urutan penentuan locale saat request masuk:
    1. Jika user login dan `users.locale` terisi → pakai itu.
    2. Jika ada di session (`session('locale')`) → pakai itu (untuk guest/sebelum login).
    3. Default → `'en'`.
- Middleware ini dipasang global (bukan cuma route tertentu) di `bootstrap/app.php`, supaya berlaku di semua halaman termasuk halaman auth sebelum login.
- `POST /locale/switch` (route sederhana, tidak perlu Form Request kompleks — cukup validasi `locale` in `['en','id']`): update `session('locale')`, dan jika user sedang login, update juga `users.locale` supaya persisten lintas device.

### Pengalih Bahasa (Frontend)

- Komponen kecil di header (dekat pill Coin/EXP) atau di halaman Profile — dropdown/toggle "EN / ID".
- Memanggil `POST /locale/switch` lalu reload halaman (paling sederhana & aman, hindari kompleksitas ganti bahasa tanpa reload untuk versi awal).

### Untuk teks di dalam JavaScript (map.js, minigame.js, dst)

- JS tidak bisa akses `__()` langsung. Solusi: Blade meng-inject objek terjemahan yang relevan ke `window` saat halaman dimuat, contoh di `<head>` layout utama:
    ```blade
    <script>
      window.translations = @json(__('map'));
    </script>
    ```
- JS memakai `window.translations.catch_button`, dst — **bukan** string hardcode di file `.js`.

## 6. Environment & Konfigurasi

| Variabel                   | Layer          | Keterangan                                               |
| -------------------------- | -------------- | -------------------------------------------------------- |
| `AI_SERVICE_URL`           | Laravel `.env` | Base URL Python service, contoh `http://ai-service:8000` |
| `AI_SERVICE_TIMEOUT`       | Laravel `.env` | Timeout request ke AI service (detik)                    |
| `SANCTUM_STATEFUL_DOMAINS` | Laravel `.env` | Domain frontend yang diizinkan auth                      |
| `MODEL_PATH`               | Python `.env`  | Lokasi file model klasifikasi                            |

## 7. Deployment (Ringkas)

- Laravel dan Python service di-deploy sebagai service terpisah (bisa dua container/dua proses), berkomunikasi lewat HTTP internal.
- MySQL sebagai database tunggal, hanya diakses oleh Laravel.
- Frontend disajikan sebagai Blade view dari Laravel (tidak perlu deployment terpisah) kecuali diputuskan lain di kemudian hari.

## 8. Batasan Arsitektur (Jangan Dilanggar)

- Frontend **tidak boleh** memanggil Python service secara langsung — selalu lewat Laravel.
- Python service **tidak boleh** menulis ke MySQL utama — hanya mengembalikan hasil klasifikasi.
- Tidak menambah database kedua (NoSQL, dsb) tanpa alasan kuat dan tanpa update dokumen ini.
