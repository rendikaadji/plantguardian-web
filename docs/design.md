# Design — PlantGuardian

> Rujuk `prd.md` untuk fitur dan `architecture.md` untuk struktur teknis frontend. Dokumen ini mendefinisikan tampilan, gaya visual, dan pemetaan layar → modul kode agar AI tidak menebak-nebak desain di setiap sesi.

## 1. Arah Visual

PlantGuardian adalah aplikasi edukasi konservasi tumbuhan untuk masyarakat umum — **bukan** proyek dengan tema dark/premium (matte black/titanium/gold) seperti proyek klien Compost Software lainnya. Arah visual di sini sengaja dibuat **cerah, hangat, dan ramah (nature/education tone)** karena target penggunanya masyarakat umum termasuk pelajar, dan temanya konservasi alam.

> **Asumsi desain:** jika ternyata tim menginginkan gaya visual berbeda (misal lebih playful/kartun atau lebih serius/dokumenter), sampaikan agar bagian ini direvisi — jangan ubah palet warna secara sepihak saat vibe coding tanpa update dokumen ini dulu.

### 1.1 Palet Warna

| Token | Hex | Penggunaan |
|---|---|---|
| `--color-primary` | `#2E7D32` | Hijau daun tua — tombol utama, header |
| `--color-primary-light` | `#66BB6A` | Aksen hijau muda — hover, highlight |
| `--color-secondary` | `#8D6E63` | Cokelat tanah — elemen MiniGame (lahan, pot) |
| `--color-accent` | `#FBC02D` | Kuning keemasan — Coin, reward, badge EXP |
| `--color-background` | `#F5F5F0` | Latar terang, natural (bukan putih polos) |
| `--color-surface` | `#FFFFFF` | Kartu/panel |
| `--color-text-primary` | `#1B1B1B` | Teks utama |
| `--color-text-muted` | `#6B6B6B` | Teks sekunder/deskripsi |
| `--color-danger` | `#C62828` | Error, peringatan |

### 1.2 Tipografi

- Font utama: **Poppins** atau **Nunito** (rounded, ramah, mudah dibaca di layar kecil).
- Heading: bold, ukuran besar untuk judul layar (`Home`, `Peta`, `Galeri`, `MiniGame`).
- Body text: regular, kontras cukup terhadap background untuk keterbacaan outdoor (mengingat fitur AR dipakai di luar ruangan).

### 1.3 Prinsip UI

- **Thumb-friendly**: tombol utama besar dan mudah dijangkau satu tangan (pengguna memegang HP sambil mengarahkan kamera).
- **Overlay AR minim distraksi**: saat mode Scan aktif, UI overlay dibuat transparan/minimal agar tidak menutupi kamera.
- **Feedback instan**: setiap aksi (scan berhasil, panen, klaim reward) diberi animasi/transisi singkat + suara/haptic ringan bila memungkinkan.

## 2. Pemetaan Layar (Screen Map)

Berdasarkan flowchart final (`revisi3_flowchart_rangers.jpg`):

| Layar | View (Blade) | Modul JS terkait | Ringkasan |
|---|---|---|---|
| Sign Up / Login | `auth/register.blade.php`, `auth/login.blade.php` | — (form standar) | Form daftar akun & pilih role |
| Pilih Role | bagian dari alur onboarding | — | Ranger vs Viewer (menentukan redirect) |
| Tutorial | `onboarding.blade.php` | `home.js` | Onboarding singkat untuk Viewer |
| Home Screen | `home.blade.php` | `home.js` | Hub navigasi ke Peta/Galeri/MiniGame |
| Peta / Main (AR Scan) | `peta.blade.php` | `ar-scanner.js` | Kamera AR, capture, kirim ke API scan |
| Hasil Scan | komponen modal di `peta.blade.php` | `ar-scanner.js` | Tampilkan detail tumbuhan + tombol simpan |
| Penyimpanan / Galeri | `galeri.blade.php` | `gallery.js` | List/grid tumbuhan tersimpan milik user |
| MiniGame | `minigame.blade.php` | `minigame.js` | Canvas kebun virtual: lahan, tanam, rawat, panen |

## 3. Komponen UI Utama

### 3.1 Home Screen
- Kartu navigasi besar (3 kartu): **Peta/Main**, **Penyimpanan/Galeri**, **MiniGame**.
- Header menampilkan avatar/nama user, total **EXP** dan **Coin** (selalu terlihat, karena ini reward inti).

### 3.2 Peta / Main (AR Scan)
- Full-screen camera view (AR.js/MindAR feed).
- Tombol shutter besar di tengah bawah untuk capture.
- Loading state saat gambar dikirim ke backend untuk klasifikasi ("Menganalisis tumbuhan...").
- Modal hasil: foto, nama tumbuhan, deskripsi singkat (dari data Ranger), tombol "Simpan ke Galeri" + notifikasi EXP/Coin didapat.
- State gagal kenali: pesan ramah ("Belum bisa dikenali, coba sudut lain") — bukan pesan error teknis.

### 3.3 Penyimpanan / Galeri
- Grid foto tumbuhan tersimpan, tiap kartu menampilkan thumbnail + nama tumbuhan.
- Tap kartu → detail penuh (foto besar, deskripsi, tanggal ditemukan).
- Empty state ramah jika belum ada temuan ("Ayo scan tumbuhan pertamamu di Peta!").

### 3.4 MiniGame (Kebun Virtual)
- Grid lahan (petak-petak), tiap petak menunjukkan status: kosong / sedang tumbuh (progress bar) / siap panen (highlight + animasi).
- Tombol aksi kontekstual per petak: **Tanam**, **Rawat/Siram**, **Panen** (hanya muncul sesuai status).
- Tombol "Beli Lahan" dan "Beli Benih/Alat" membuka panel toko sederhana (list item + harga Coin).
- Setelah panen: animasi singkat + notifikasi EXP/Coin, opsi lanjut tanam lagi di petak yang sama.

## 4. Microcopy & Nada Bahasa

- Bahasa Indonesia santai tapi tetap informatif — sesuai audiens pelajar/masyarakat umum.
- Pesan edukasi tumbuhan (dari data Ranger) ditulis singkat, mudah dipahami, tidak terlalu ilmiah.
- Pesan sistem (error, empty state, konfirmasi) selalu ramah, tidak menyalahkan pengguna.

## 5. Aksesibilitas & Performa

- Kontras warna teks terhadap background minimal memenuhi standar keterbacaan (hindari teks abu-abu muda di atas putih).
- Ukuran font dasar tidak lebih kecil dari 14px untuk teks body.
- Aset gambar dikompresi (WebP jika didukung) mengingat target perangkat HP kelas menengah-bawah dan penggunaan AR yang sudah cukup berat di sisi browser.
