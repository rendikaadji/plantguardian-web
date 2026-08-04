# Integration Test Checklist — PlantGuardian

Checklist pengujian manual untuk meyakinkan seluruh alur pengguna (Ranger vs Viewer), Aksi Nyata (Kompos & Real Planting), Izin Lokasi, dan MiniGame berjalan sesuai `prd.md`, `architecture.md`, `design.md`, dan `rules.md`.

---

## 1. Alur Auth, Onboarding & Pemisahan Role

- [ ] **1.1 Sign Up / Pendaftaran Akun Baru**
  - Buka `/register`. Isi nama, email, dan password.
  - Verifikasi pengguna berhasil terdaftar dan diarahkan ke layar **Pilih Role** (`/onboarding/pilih-role`).
- [ ] **1.2 Pemilihan Role (Ranger vs Viewer)**
  - Pilih role **Ranger**: dipastikan diarahkan ke Dashboard Ranger (`/ranger/dashboard`).
  - Pilih role **Viewer**: dipastikan diarahkan ke Beranda Viewer (`/`).
- [ ] **1.3 Persistent Role-based Redirect di Login**
  - Logout, lalu login kembali sebagai Ranger → otomatis masuk ke `/ranger/dashboard`.
  - Logout, lalu login kembali sebagai Viewer → otomatis masuk ke `/`.
- [ ] **1.4 Hak Akses Route (Role Separation)**
  - Coba buka `/ranger/dashboard` saat login sebagai Viewer → dipastikan ditolak (`403 Forbidden` / redirect).
  - Coba buka `/minigame` saat login sebagai Ranger → dipastikan ditolak (`403 Forbidden` / redirect).

---

## 2. Alur Izin Lokasi & Privasi (Location Privacy & Fallback)

- [ ] **2.1 Penanganan Izin Lokasi (GPS Handling)**
  - Verifikasi prompt penjelasan izin lokasi tampil sebelum dialog native browser.
  - Saat pengguna menekan **"Izinkan"**, koordinat lokasi diambil 1x saat submit bukti (`getCurrentPosition`).
- [ ] **2.2 Fallback Izin Lokasi Ditolak / Lewati**
  - Saat pengguna menekan **"Tolak"** / **"Lewati"**, sistem **tetap mengizinkan pengguna melanjutkan aksi**.
  - Aksi check-in kompos dan submit tanam pohon nyata tetap berhasil diproses dengan nilai koordinat `latitude: null` & `longitude: null` tanpa error / memblokir pengguna (mematuhi `rules.md` §4.1).
  - Dipastikan tidak ada pelacakan lokasi persisten/berkelanjutan (`watchPosition`).

---

## 3. Alur End-to-End Tantangan Kompos & Tanam Pohon Nyata

- [ ] **3.1 Mulai Tantangan Kompos**
  - Buka katalog bahan kompos (`/compost-materials`). Pilih bahan → tekan **Mulai Proses Kompos** (`POST /api/compost-processes`).
  - Verifikasi proses kompos aktif terbuat (`status: started`) dan reward **+50 EXP** bertambah di saldo.
- [ ] **3.2 Check-in Berkala Foto Kompos**
  - Buka detail progress kompos (`/compost-processes/{id}`).
  - Lakukan check-in foto berkala beberapa kali dengan label tahap & catatan (`POST /api/compost-processes/{id}/checkin`).
  - Verifikasi status berubah ke `in_progress`, log timeline check-in tersimpan, dan reward **+20 EXP** bertambah di setiap check-in.
- [ ] **3.3 Tandai Kompos Matang**
  - Tekan tombol **Tandai Kompos Matang** (`POST /api/compost-processes/{id}/mature`).
  - Verifikasi status kompos berubah ke `matured` (`matured_at` terisi) dan reward **+100 EXP** bertambah.
- [ ] **3.4 Submit Bukti Tanam Pohon Nyata**
  - Lanjut ke form tanam pohon nyata (`/real-plantings`).
  - Upload foto bukti penanaman pohon nyata beserta pilihan jenis spesies (`POST /api/real-plantings`).
  - Verifikasi status proses kompos berubah ke `used_for_planting`, bukti tanam terbuat berstatus `self_reported`, dan reward milestone tertinggi **+300 EXP** berhasil diberikan.

---

## 4. Alur Ranger: Peta Scan AR & Verifikasi Marker

- [ ] **4.1 Peta & Mode Scan AR (Ranger Only)**
  - Buka `/ranger/peta` sebagai Ranger.
  - Tombol melayang Kamera AR Scan tampil.
  - Tekan tombol camera shutter → frame terambil dan lokasi GPS terambil (1 kali saat capture).
- [ ] **4.2 Submit Scan & Hasil Identifikasi AI**
  - Hasil scan dikirim ke `POST /scan`.
  - Record `plant_sightings` baru dibuat berstatus `pending`.
- [ ] **4.3 Queue Verifikasi & Persetujuan Ranger**
  - Buka `/ranger/verifications` sebagai Ranger.
  - Temuan `pending` diverifikasi (`verified`).
  - Setelah `verified`, marker lokasi tumbuhan resmi muncul di Peta publik untuk Viewer.

---

## 5. Alur Viewer: Catch Marker di Peta & Koleksi Seedex

- [ ] **5.1 Penemuan Marker di Peta (Catch)**
  - Buka `/peta` sebagai Viewer.
  - Marker lokasi `verified` muncul di peta.
  - Tap marker → Bottom sheet menampilkan foto & nama spesies + tombol **"Temukan!"**.
- [ ] **5.2 Penambahan Koleksi & Reward**
  - Tekan tombol **"Temukan!"** (`POST /api/plant-discoveries`).
  - Efek animasi/notifikasi reward EXP (**+100 EXP**) & NutriCoin (**+50 NC**) muncul.
  - Saldo EXP & Coin di top header bar langsung bertambah secara real-time.
- [ ] **5.3 Galeri / Seedex**
  - Buka `/galeri`. Entri tanaman yang baru ditemukan tampil di grid Seedex.
  - Bar progress Seedex ("X / Y Ditemukan") bertambah.
  - Tap kartu Seedex → Modal detail spesies tampil lengkap dengan deskripsi dan petunjuk perawatan.

---

## 6. Alur Misi Harian & Reset Otomatis

- [ ] **6.1 Progress Harian (Penjelajah Lapangan)**
  - Di halaman Beranda (`/`), Kartu Misi Harian menampilkan progress penemuan hari ini (`X / 5`).
- [ ] **6.2 Klaim Hadiah Misi Harian**
  - Saat mencapai 5/5 penemuan hari ini, tombol **"Klaim Hadiah Misi (+150 EXP & 50 NC)"** aktif.
  - Tekan klaim → reward ditambahkan dan status berubah menjadi *Selesai & Diklaim*.
- [ ] **6.3 Reset Otomatis 00:00**
  - Saat hari berganti (pukul 00:00), progress harian otomatis teriset menjadi `0 / 5` (0%).

---

## 7. Alur MiniGame (Kebun Virtual)

- [ ] **7.1 Membuka Lahan Tanam (Unlock Plot)**
  - Buka `/minigame`.
  - Tap slot lahan terkunci → konfirmasi beli dengan NutriCoin (`POST /api/minigame/plots/{id}/unlock`).
  - NutriCoin berkurang dan lahan menjadi terbuka (`unlocked`).
- [ ] **7.2 Menanam Benih (Planting)**
  - Buka Shop (`/shop`), beli benih (misal: Benih Bunga Matahari).
  - Di MiniGame, pilih lahan terbuka → pilih benih dari Bag → tekan **Tanam** (`POST /api/minigame/plant`).
  - Lahan menampilkan benih yang sedang tumbuh (`status: growing`) dengan progress bar waktu tumbuh real-time.
- [ ] **7.3 Merawat Tanaman (Water & Fertilize)**
  - Gunakan *Penyiram Otomatis* (`POST /api/minigame/water`) atau *Pupuk Organik Super* (`POST /api/minigame/fertilize`).
  - Durasi tumbuh berkurang secara real-time.
- [ ] **7.4 Panen & Hadiah (Harvest)**
  - Saat progress tumbuh mencapai 100%, status berubah menjadi *Siap Panen* (`ready`).
  - Tekan **Panen** (`POST /api/minigame/harvest`).
  - Animasi panen + notifikasi reward EXP & NutriCoin bertambah.

---

## 8. Leaderboard Mingguan

- [ ] **8.1 Urutan Papan Peringkat Real-time**
  - Buka `/leaderboard`.
  - Verifikasi urutan peringkat terurut secara benar berdasarkan total perolehan EXP minggu berjalan dari yang tertinggi ke terendah.
  - Podium Top 3 (Juara 1 Emas 👑, Juara 2 Perak 🥈, Juara 3 Perunggu 🥉) tampil sesuai total EXP.
  - Kartu posisi pengguna login di-highlight secara khusus.
- [ ] **8.2 Tab Riwayat Snapshot Juara**
  - Switch ke tab *Riwayat Juara* → menampilkan riwayat snapshot leaderboard mingguan sebelumnya (`weekly_rewards`).
