# Execution Prompt — PlantGuardian
**Gunakan file ini sebagai instruksi awal di setiap sesi vibe coding (chat baru, Claude Code, dsb).**

---

## PROMPT UTAMA (copy-paste ini di awal sesi)

```
Kamu adalah developer fullstack profesional yang mengerjakan proyek PlantGuardian.

WAJIB baca dulu sebelum menulis kode apa pun:
- docs/prd.md       (fitur & cakupan)
- docs/architecture.md  (struktur teknis 3-layer)
- docs/schema.md    (struktur database)
- docs/design.md    (tampilan & UX)
- docs/rules.md     (aturan clean code wajib)

Aturan kerja:
1. Kerjakan HANYA satu fase/task yang saya minta saat ini. Jangan mengerjakan fase lain di depan tanpa diminta.
2. Sebelum menulis kode, sebutkan singkat: file apa yang akan diubah, dan bagian dokumen mana yang jadi rujukan.
3. Ikuti konvensi penamaan dan struktur folder di rules.md §5 — jangan improvisasi struktur baru.
4. Controller tetap tipis (rules.md §2.1). Business logic wajib di Service class.
5. Semua query data milik user wajib discope per user_id (rules.md §2.4).
6. Jangan menambah package/library/dependency baru tanpa menyebutkan alasan dan menunggu konfirmasi saya.
7. Jangan mengubah isi docs/*.md kecuali saya minta — kalau kamu merasa ada ketidaksesuaian, tanyakan dulu, jangan diam-diam menyimpang.
8. Setelah selesai satu fase, beri ringkasan singkat: file apa saja yang dibuat/diubah, dan apa langkah selanjutnya yang disarankan.

Fase yang saya minta sekarang: [ISI SESUAI FASE DI BAWAH]
```

---

## Tahapan (kerjakan berurutan, satu fase = satu sesi/prompt terpisah)

### F0 — Setup (sudah selesai)
Laravel project, Sanctum, folder skeleton, stub Model/Controller/Request/Resource, Python venv, migration table siap. ✅

### F1 — Migration (isi kolom)
```
Fase sekarang: F1 - Migration
Isi migration untuk tabel: plant_species
Rujuk docs/schema.md bagian 3.
Hanya kerjakan 1 file migration ini dulu, jangan lanjut ke tabel lain.
```
Ulangi F1 untuk tiap tabel satu-satu: `plant_sightings`, `garden_plots`, `plantings`, `inventory_items`, `coin_transactions`, `exp_logs`. Setelah semua terisi:
```powershell
php artisan migrate:fresh
```

### F2 — Model (relasi Eloquent + $fillable)
```
Fase sekarang: F2 - Model
Isi Model PlantSpecies.php: relasi, $fillable, sesuai docs/schema.md bagian 3 dan ERD di bagian 1.
```
Ulangi per model: `PlantSighting`, `GardenPlot`, `Planting`, `InventoryItem`, `CoinTransaction`, `ExpLog`, dan update `User.php` (tambah relasi + kolom exp/coin).

### F3 — Service Layer (business logic inti)
Urutan prioritas:
```
Fase sekarang: F3 - Service Layer
Buat GardenService.php: logic tanam benih, rawat tanaman, cek status siap panen, proses panen.
Rujuk docs/architecture.md bagian 3.2 dan docs/schema.md bagian 6.
```
Lanjut: `RewardService.php` (hitung & catat EXP/Coin, rujuk schema.md §8-9), lalu `PlantScanService.php` (nanti setelah F6 Python service siap, karena butuh kontrak API-nya).

### F4 — Form Request (validasi input)
```
Fase sekarang: F4 - Form Request
Isi ScanRequest.php: validasi upload gambar untuk fitur scan.
Rujuk docs/architecture.md bagian 5 (alur data scan).
```
Lanjut: `PlantSightingRequest`, `PlantingRequest`.

### F5 — Controller & Routes
```
Fase sekarang: F5 - Controller
Isi ScanController.php: terima ScanRequest, panggil PlantScanService, kembalikan PlantSightingResource.
Controller HARUS tipis sesuai rules.md §2.1 - jangan taruh logic di sini.
Tambahkan route terkait di routes/api.php.
```
Lanjut: `GalleryController`, `MiniGameController`, `WalletController`.

### F6 — Python AI Service
```
Fase sekarang: F6 - AI Service
Buat main.py di ai_service/ dengan endpoint POST /classify sesuai kontrak di docs/architecture.md bagian 4.2.
Gunakan FastAPI, OpenCV untuk preprocessing, load model TensorFlow dari models/plant_classifier.h5.
Service harus stateless, tidak menyentuh MySQL.
```
Setelah ini baru kembali ke F3 untuk isi `PlantScanService.php` yang memanggil endpoint ini.

### F7 — Frontend (WebAR & MiniGame)
Kerjakan satu modul per prompt, rujuk `docs/design.md`:
```
Fase sekarang: F7 - Frontend
Buat resources/js/modules/ar-scanner.js: inisialisasi AR.js/MindAR, capture frame, kirim ke api-client.js.
Rujuk docs/design.md bagian 3.2 untuk alur UI Peta/Main.
```
Lanjut modul: `api-client.js` (dibuat duluan sebelum ar-scanner.js kalau belum ada), `gallery.js`, `minigame.js`, `home.js`, lalu view Blade terkait.

### F8 — Integrasi & Testing Manual
```
Fase sekarang: F8 - Integration Test
Buat test manual/checklist untuk alur: sign up -> pilih role -> home -> scan -> simpan galeri -> dapat EXP/Coin.
Cek juga alur minigame: beli lahan -> tanam -> rawat -> panen -> EXP/Coin.
Tidak perlu buat automated test dulu kecuali diminta - fokus checklist manual.
```

### F9 — Izin Lokasi & Tantangan Kompos (Migration + Model)
```
Fase sekarang: F9 - Migration & Model Kompos
Isi migration untuk tabel: compost_materials, compost_processes, compost_progress_logs, real_plantings.
Rujuk docs/schema.md bagian 11-14.
Kerjakan satu migration dulu, saya review, baru lanjut migration berikutnya.
```
Setelah semua migration terisi, jalankan `php artisan migrate:fresh`, lalu isi Model terkait (`CompostMaterial`, `CompostProcess`, `CompostProgressLog`, `RealPlanting`) satu per satu — relasi & `$fillable` sesuai `schema.md` §11-14.

### F10 — CompostService & Endpoint Kompos
```
Fase sekarang: F10 - CompostService
Buat CompostService.php: mulai proses kompos, catat check-in, tandai matang, catat real planting.
Rujuk docs/architecture.md bagian 4.5 dan bagian 5.1 (alur end-to-end).
Panggil RewardService untuk EXP di tiap milestone.
```
Lanjut Form Request (`CompostProcessRequest`, `CompostCheckinRequest`, `RealPlantingRequest`) dan Controller (`CompostController`), sesuai endpoint di `architecture.md` §4.5.

### F11 — Leaderboard Mingguan
```
Fase sekarang: F11 - Leaderboard
Isi migration & model weekly_rewards sesuai docs/schema.md bagian 15.
Buat LeaderboardService.php: hitung EXP per user dalam rentang minggu dari exp_logs, simpan snapshot ke weekly_rewards.
Buat Artisan command CalculateWeeklyLeaderboard sesuai docs/architecture.md bagian 4.6.
Tambahkan endpoint GET /api/leaderboard/current dan /api/leaderboard/history.
```
Jadwalkan command di scheduler (`routes/console.php`), rujuk `architecture.md` §4.6 untuk waktu eksekusi (Senin 00:00).

### F12 — Frontend: Izin Lokasi, Kompos, Real Planting, Leaderboard
Kerjakan satu modul per prompt, rujuk `docs/design.md` §3.5-3.8:
```
Fase sekarang: F12 - Frontend Kompos
Buat resources/js/modules/compost.js: daftar bahan, mulai proses, form check-in, tandai matang, form real planting.
Rujuk docs/design.md bagian 3.6 dan 3.7.
Gunakan navigator.geolocation.getCurrentPosition HANYA saat submit check-in/real planting (bukan pelacakan berkelanjutan) - lihat rules.md bagian 4.1.
```
Lanjut modul `leaderboard.js` dan view Blade terkait (`compost/index`, `compost/show`, `compost/plant`, `leaderboard.blade.php`).

### F13 — Integrasi & Testing Manual (update dari F8)
```
Fase sekarang: F13 - Integration Test
Tambahkan ke checklist manual sebelumnya:
- izin lokasi -> lanjut meski ditolak
- mulai tantangan kompos -> check-in beberapa kali -> tandai matang -> tanam pohon nyata -> EXP bertambah
- cek papan peringkat mingguan menampilkan urutan yang benar
```

### F14 — Migration Kolom Verifikasi & Middleware Role
```
Fase sekarang: F14 - Migration Verifikasi & Middleware
1. Buat migration tambah kolom ke plant_sightings: verification_status, verified_by, verified_at.
   Rujuk docs/schema.md bagian 4 (sudah diupdate).
2. Buat migration tambah kolom ke real_plantings: verified_by, verified_at.
   Rujuk docs/schema.md bagian 14 (sudah diupdate).
3. Buat middleware EnsureUserIsRanger.php, daftarkan alias 'ranger' di bootstrap/app.php (Laravel 11).
   Rujuk docs/architecture.md bagian 4.7.
```
Jalankan `php artisan migrate` setelah kedua migration terisi.

### F15 — Katalog Spesies & Kompos (Ranger)
```
Fase sekarang: F15 - Katalog Ranger
Buat SpeciesCatalogService.php dan SpeciesCatalogController.php: CRUD plant_species.
Buat SpeciesRequest.php untuk validasi.
Rujuk docs/architecture.md bagian 4.7 - ingat, query baca TIDAK discope per user
(katalog bersama, lihat rules.md bagian 1 poin 5).
Tambahkan route group /api/ranger/species dengan middleware 'ranger'.
```
Lanjut sama untuk `CompostCatalogService`/`CompostCatalogController`/`CompostMaterialRequest` di `/api/ranger/compost-materials`.

### F16 — Verifikasi Temuan (Ranger)
```
Fase sekarang: F16 - Verifikasi
Buat VerificationService.php: method verifySighting() dan verifyRealPlanting(),
mengisi verified_by (auth user) dan verified_at, update status.
Buat VerificationController.php dan VerificationDecisionRequest.php.
Tambahkan route:
GET /api/ranger/verifications/pending
POST /api/ranger/verifications/sightings/{id}
POST /api/ranger/verifications/real-plantings/{id}
Rujuk docs/architecture.md bagian 4.7.
```

### F17 — Frontend Ranger
Kerjakan satu modul per prompt, rujuk `docs/design.md` §3.9-3.11:
```
Fase sekarang: F17 - Frontend Ranger
Buat resources/views/ranger/dashboard.blade.php dan resources/js/modules/ranger-home.js.
Rujuk docs/design.md bagian 3.9 - style "meja arsip", 3 kartu navigasi dengan
jumlah entri/antrean.
```
Lanjut: `ranger/species/*` + `ranger-species.js`, `ranger/compost-materials/*` + `ranger-compost.js`, `ranger/verifications/*` + `ranger-verify.js` (rujuk §3.10-3.11).

### F18 — Pemisahan Total Akses Viewer vs Ranger (Perbaikan Wajib)
```
Fase sekarang: F18 - Pemisahan Akses Role
Rujuk docs/architecture.md bagian 4.8 dan docs/rules.md bagian 4 & 4.1.

1. Buat middleware EnsureUserIsViewer.php (pasangan EnsureUserIsRanger yang
   sudah ada), daftarkan alias 'viewer' di bootstrap/app.php.
2. Bungkus SEMUA route Viewer (home, peta, galeri, minigame, compost/*,
   leaderboard) dengan middleware 'viewer' - saat ini route-route itu
   kemungkinan tidak dibatasi role sama sekali, itu sebabnya Ranger bisa
   mengaksesnya.
3. Cari file yang menangani response setelah login berhasil (LoginResponse
   custom untuk Fortify, atau method authenticated() di
   AuthenticatedSessionController untuk Breeze). Tambahkan logic: cek
   auth()->user()->role, redirect ke route('ranger.dashboard') jika ranger,
   route('home') jika viewer. INI HARUS JALAN SETIAP LOGIN, bukan cuma di
   alur onboarding pilih-role.
4. Cek komponen nav bar - pastikan link menu dirender kondisional sesuai
   role user yang login (Viewer tidak lihat link Dashboard Ranger, dst).

Setelah selesai, tunjukkan dulu isi middleware & logic redirect yang dibuat
sebelum saya anggap ini selesai - saya mau pastikan tidak ada role yang bisa
saling akses.
```

### F19 — Migration Pembalikan Peran Scan (Wajib Duluan Sebelum yang Lain)
```
Fase sekarang: F19 - Migration Pembalikan Scan
Rujuk docs/schema.md bagian 4 dan 4a (sudah diupdate - PENTING baca dulu).

1. Buat migration RENAME kolom plant_sightings.user_id menjadi ranger_id
   (gunakan Schema::table + renameColumn, JANGAN drop tabel).
2. Buat migration baru untuk tabel plant_discoveries sesuai schema.md 4a
   (kolom: user_id, plant_sighting_id, discovered_at, latitude, longitude,
   dengan unique constraint pada kombinasi user_id+plant_sighting_id).
3. Update Model PlantSighting.php: relasi ranger() bukan user(), $fillable
   sesuaikan ke ranger_id.
4. Buat Model PlantDiscovery.php baru dengan relasi ke User dan PlantSighting.

Jalankan php artisan migrate setelah semua migration ini dibuat.
```

### F20 — Backend: Pindahkan Scan ke Ranger, Buat Endpoint Catch untuk Viewer
```
Fase sekarang: F20 - Backend Pembalikan Peran
Rujuk docs/architecture.md bagian 5 dan 5.0a (sudah diupdate).

1. Pindahkan route POST /api/scan dari middleware 'viewer' ke middleware
   'ranger'. Update ScanController/PlantScanService: saat membuat PlantSighting
   baru, isi ranger_id (bukan user_id) dari auth()->id(), status default 'pending'.
   JANGAN beri EXP/Coin saat scan dibuat (baru pending) - EXP diberikan saat
   sighting itu diverifikasi (lihat VerificationService yang sudah ada).

2. Buat DiscoveryController.php + DiscoveryService.php (middleware 'viewer'):
   POST /api/plant-discoveries - terima plant_sighting_id, cek sighting
   tsb berstatus 'verified', cek belum pernah di-catch user ini (unique
   constraint), buat record plant_discoveries, panggil RewardService untuk
   EXP/Coin.

3. Update MapController: endpoint GET /api/plant-sightings/nearby untuk
   Viewer HANYA mengembalikan sighting dengan verification_status='verified'.
   Sertakan flag "sudah_ditemukan" (cek exists di plant_discoveries milik
   user yang login) di setiap item response.

4. Update VerificationController/VerificationService: saat Ranger menandai
   sighting 'verified', PANGGIL RewardService untuk beri EXP ke ranger_id
   pemilik sighting (reward scan yang tertunda dari langkah 1).

Setelah selesai, tunjukkan route:list untuk /api/scan dan /api/plant-discoveries
beserta middleware-nya - saya perlu pastikan sudah benar sebelum lanjut ke frontend.
```

### F21 — Redesign Total ke "Garden Guardian" (Visual)
```
Fase sekarang: F21 - Redesign Garden Guardian
Baca docs/design.md bagian 1 (sudah ditulis ulang total - PENTING baca semua
sub-bagian 1.1-1.5) beserta 7 screenshot referensi tim yang saya lampirkan.

Redesign ULANG semua halaman yang sudah ada (Beranda/Home, Peta, Galeri,
MiniGame, Dashboard Ranger, Katalog Spesies/Kompos, Verifikasi) ke sistem
visual baru ini:

1. Ganti font ke Baloo 2 (heading) + Nunito (body) - HAPUS Fraunces dan
   IBM Plex Mono dari seluruh project.
2. Ganti semua warna ke token baru di design.md 1.1 (background krem-hijau
   pucat #F5F4DA, primary hijau tua #1F3D20, dst) - HAPUS palet paper/ink/
   forest/clay/brass lama.
3. Bottom tab bar 5 item (Peta, Galeri, Bag, Shop, Profile) menggantikan
   nav bar lama di semua halaman Viewer.
4. Terapkan komponen signature sesuai design.md 1.3: avatar+level badge,
   progress bar rounded tebal, kartu koleksi dengan rarity badge & locked
   state, badge pencapaian, kartu misi.

JANGAN implementasikan Alliance Friends, Skins, Daily Free Gift Box, Story
Quest, atau Faction/Rank - itu di luar cakupan (lihat design.md 1.4), hanya
ambil sistem visualnya saja untuk fitur yang sudah ada.

Kerjakan SATU HALAMAN per prompt (mulai dari Home/Beranda dulu), jangan
semua sekaligus.
```

### F22 — Frontend: Restyle Peta (Catch untuk Viewer, Scan untuk Ranger) & Galeri Seedex
```
Fase sekarang: F22 - Frontend Peta & Galeri
Rujuk docs/design.md bagian 3.2 dan 3.3 (sudah diupdate untuk pembalikan peran).

1. map.js: untuk role Viewer, marker punya tombol "Temukan!" (bukan kamera)
   yang memanggil POST /api/plant-discoveries. Untuk role Ranger, tetap ada
   tombol kamera mengambang untuk buka ar-scanner.js seperti sebelumnya.
   Cek role user yang login untuk menentukan UI mana yang ditampilkan.

2. galeri.js: restyle jadi "Seedex" - header progress "X/Y Ditemukan",
   grid kartu koleksi dengan rarity badge, kartu terkunci untuk yang belum
   ditemukan. Data diambil dari GET /api/plant-discoveries milik user (Viewer)
   atau GET /api/plant-sightings milik ranger_id sendiri (Ranger, tampilan beda).

Setelah selesai, saya perlu screenshot dari kedua role (Viewer dan Ranger)
di halaman Peta yang sama, untuk pastikan tombolnya benar-benar beda sesuai role.
```

---

## Tips Hemat Token

- Satu prompt = satu file/satu fitur kecil. Jangan gabung banyak file dalam satu permintaan.
- Selalu commit ke Git setelah satu fase selesai dan direview, sebelum lanjut fase berikutnya — supaya kalau AI di sesi berikutnya "lupa" konteks, kamu tinggal `git diff`/`git log` untuk mengingatkan.
- Kalau butuh AI membaca ulang konteks project di sesi baru, cukup lampirkan `docs/*.md` (5 file), bukan seluruh riwayat chat sebelumnya.
- Kalau satu fase gagal/errornya panjang, jangan tempel seluruh log error — ambil bagian intinya saja (pesan `ERROR` dan 2-3 baris di sekitarnya).
