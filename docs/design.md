# Design — PlantGuardian

> Rujuk `prd.md` untuk fitur dan `architecture.md` untuk struktur teknis frontend. Dokumen ini mendefinisikan tampilan, gaya visual, dan pemetaan layar → modul kode agar AI tidak menebak-nebak desain di setiap sesi.

## 1. Arah Visual

**Update besar (menggantikan arah sebelumnya):** arah visual lama (Herbarium/Field Journal — serif Fraunces, palet kertas tua) **digantikan total** dengan arah baru: **"Garden Guardian" — RPG fantasi berkebun**, mengikuti referensi visual yang sudah dibuat tim (7 screenshot referensi: Profile, Seedex/Galeri, Peta/Home, Misi Harian, Bag/Inventaris, Shop). Semua halaman (Beranda, Peta, Galeri, MiniGame, Shop, Profile, Dashboard Ranger) memakai sistem desain ini — bukan cuma halaman baru, halaman yang sudah ada juga di-restyle ulang.

### 1.1 Palet Warna

| Token | Hex (perkiraan dari referensi) | Penggunaan |
|---|---|---|
| `--color-bg` | `#F5F4DA` | Latar utama — krem kehijauan pucat |
| `--color-surface` | `#FBFAF0` | Kartu/panel (sedikit lebih terang dari bg) |
| `--color-primary` | `#1F3D20` | Hijau hutan tua — tombol utama, nav aktif, header |
| `--color-primary-text` | `#2D4A2E` | Teks judul hijau |
| `--color-text` | `#2A2A22` | Teks utama, hitam hangat |
| `--color-text-muted` | `#6B6B55` | Teks sekunder |
| `--color-accent-blue` | `#2E6DA4` | Ikon vitalitas/potion |
| `--color-accent-coral` | `#D96C63` | Ikon hidrasi, badge hangat |
| `--color-accent-brown` | `#8B6A4C` | Ikon tool/soil |
| `--color-danger` | `#C0392B` | Alert, status error/gagal |
| `--color-rarity-common` | `#9E9E8A` | Badge rarity Common |
| `--color-rarity-uncommon` | `#4C8C4A` | Badge rarity Uncommon |
| `--color-rarity-rare` | `#7D5BA6` | Badge rarity Rare |

### 1.2 Tipografi

- **Display/Heading:** **Baloo 2** (rounded, bold, playful) — judul layar, nama spesies, angka level/EXP besar.
- **Body:** **Nunito** atau **Quicksand** — deskripsi, label, form.
- Fraunces & IBM Plex Mono (arah lama) **tidak dipakai lagi**.

### 1.3 Komponen Signature (wajib konsisten di semua layar)

1. **Avatar + Level Badge** — foto profil bulat border warna primary, badge level menempel sudut bawah.
2. **Progress Bar Rounded Tebal** — track abu muda, fill hijau tua. Dipakai untuk EXP, progress misi, progress kompos.
3. **Bottom Tab Bar** — 5 ikon (Peta, Galeri, Bag/Inventaris, Shop, Profile), item aktif jadi pill hijau tua penuh.
4. **Kartu Koleksi/Seedex** — grid kartu spesies: ditemukan = foto+nama+badge rarity; belum ditemukan = abu-abu + ikon petunjuk (?, daun, awan, gunung) + label "Locked"/"Unknown".
5. **Badge Pencapaian** — ikon bulat + angka kecil di sudut (mis. "x5").
6. **Kartu Hero Banner** — foto latar penuh + overlay judul/deskripsi, dipakai di layar Peta untuk info/event.
7. **Kartu Misi** — ikon kotak berwarna + judul + deskripsi + progress bar pecahan (3/5) + ikon reward.
8. **Grid Toko** — tombol kategori bulat + grid item dengan harga (ikon mata uang + angka).
9. **Pill Mata Uang** — badge bulat di header berisi ikon + jumlah Coin, selalu terlihat.

### 1.4 Catatan Cakupan (Penting — jangan dikerjakan diam-diam)

Beberapa elemen di referensi tim **belum tercatat di `prd.md`**:
- **Alliance Friends** (daftar teman + status online) — fitur sosial, sebelumnya tercatat *out of scope* (`prd.md` §8).
- **Skins/kosmetik** di Shop — belum ada di cakupan fitur manapun.
- **Daily Free Gift Box** & **Story Quest** — konsep baru, belum tercatat di PRD.
- **Faction/Rank title** ("GRAND ARBITER RANK") — belum ada konsep fraksi di skema data manapun.

**Untuk sekarang:** ambil sistem visual & komponennya saja, terapkan ke fitur yang **sudah** ada di `prd.md` (Peta, Galeri, MiniGame/Inventaris, Shop untuk beli benih/alat, Profile untuk EXP/Coin/stat). 4 poin di atas ditunda sampai didiskusikan & dicatat resmi di `prd.md` — jangan diimplementasikan dulu.

### 1.5 Prinsip UI

- **Thumb-friendly**: tombol utama besar dan mudah dijangkau satu tangan (pengguna memegang HP sambil mengarahkan kamera).
- **Overlay AR minim distraksi**: saat mode Scan aktif, UI overlay dibuat transparan/minimal agar tidak menutupi kamera.
- **Feedback instan**: setiap aksi (scan berhasil, panen, klaim reward) diberi animasi/transisi singkat + suara/haptic ringan bila memungkinkan — animasi dipakai secukupnya di satu momen penting saja (lihat catatan restraint di bawah), bukan tersebar di semua elemen.
- **Restraint**: satu risiko visual besar (Kartu Spesimen + tipografi Fraunces) sudah cukup jadi pembeda. Elemen lain di sekitarnya dibuat tenang & disiplin — jangan tambah lagi gradient, glow, atau animasi berlebih di tempat lain.

## 2. Pemetaan Layar (Screen Map)

Berdasarkan flowchart final (`revisi3_flowchart_rangers.jpg`):

| Layar | View (Blade) | Modul JS terkait | Ringkasan |
|---|---|---|---|
| Sign Up / Login | `auth/register.blade.php`, `auth/login.blade.php` | — (form standar) | Form daftar akun & pilih role |
| Pilih Role | bagian dari alur onboarding | — | Ranger vs Viewer (menentukan redirect) |
| Tutorial | `onboarding.blade.php` | `home.js` | Onboarding singkat untuk Viewer |
| Home Screen | `home.blade.php` | `home.js` | Hub navigasi ke Peta/Galeri/MiniGame |
| Peta / Main (Peta Real + AR Scan) | `peta.blade.php` | `map.js`, `ar-scanner.js` | Peta GPS sungguhan dengan marker lokasi temuan, tap marker/tombol scan untuk buka kamera AR |
| Hasil Scan | komponen modal di `peta.blade.php` | `ar-scanner.js` | Tampilkan detail tumbuhan + tombol simpan |
| Penyimpanan / Galeri | `galeri.blade.php` | `gallery.js` | List/grid tumbuhan tersimpan milik user |
| MiniGame | `minigame.blade.php` | `minigame.js` | Canvas kebun virtual: lahan, tanam, rawat, panen |
| Izin Lokasi | komponen modal di alur onboarding | `home.js` | Prompt sekali di awal, jelaskan kenapa lokasi dibutuhkan |
| Tantangan Kompos | `compost/index.blade.php` | `compost.js` | Daftar bahan, mulai proses, lihat instruksi |
| Progress Kompos | `compost/show.blade.php` | `compost.js` | Timeline check-in, tombol "Check-in", tombol "Tandai Matang" |
| Tanam Pohon Nyata | `compost/plant.blade.php` | `compost.js` | Form upload bukti tanam (foto + lokasi + jenis pohon) |
| Papan Peringkat | `leaderboard.blade.php` | `leaderboard.js` | Ranking mingguan, badge/penghargaan |
| Dashboard Ranger | `ranger/dashboard.blade.php` | `ranger-home.js` | Hub navigasi ke Katalog Spesies/Kompos/Verifikasi |
| Katalog Spesies | `ranger/species/index.blade.php`, `.../form.blade.php` | `ranger-species.js` | List + tambah/edit `plant_species` |
| Katalog Bahan Kompos | `ranger/compost-materials/index.blade.php`, `.../form.blade.php` | `ranger-compost.js` | List + tambah/edit `compost_materials` |
| Verifikasi Temuan | `ranger/verifications/index.blade.php` | `ranger-verify.js` | Queue peninjauan sighting & real planting |

## 3. Komponen UI Utama

### 3.1 Home Screen (Viewer)
- Restyle ke sistem "Garden Guardian" (§1): header dengan avatar+level, pill Coin/EXP di kanan atas.
- Bottom tab bar 5 item: **Peta**, **Galeri (Plants)**, **Bag**, **Shop**, **Profile** — menggantikan 3 kartu navigasi sebelumnya (rujuk §1.3 poin 3).
- Konten utama berupa **hero banner** (event/cuaca harian, opsional) — lihat §1.3 poin 6.

### 3.2 Peta / Main — **Perilaku BERBEDA per role (perubahan penting)**

> **Perubahan besar:** sebelumnya Viewer yang scan langsung via kamera AR. Sekarang **dibalik** — **Ranger** yang melakukan Scan (AR + AI, fieldwork nyata untuk menambah/verifikasi data), **Viewer tidak punya kamera scan** — Viewer hanya **menjelajah peta dan "menemukan" (catch)** spesies yang sudah pernah di-scan Ranger di sekitar lokasinya, mirip menangkap di Pokemon GO tanpa kamera. Ini berlaku untuk SEMUA turunan dokumen (`prd.md` §3, `architecture.md` §4.5 dst, `schema.md`).

**Untuk Viewer:**
- Peta menampilkan lokasi pengguna (titik) + marker di titik-titik `plant_sightings` **yang sudah `verified`** oleh Ranger di sekitar radius tertentu.
- Tap marker → bottom sheet menampilkan foto & nama spesies (hasil kartu koleksi §1.3 poin 4), tombol **"Temukan!"** (bukan "Buka Kamera") — tombol ini memicu aksi "catch" (`POST /api/plant-discoveries`, lihat `architecture.md`), tidak perlu kamera sama sekali.
- Setelah "Temukan!" ditekan → animasi singkat (mirip buka kartu koleksi) + notifikasi EXP/Coin didapat + entri baru masuk ke Galeri pribadi (`plant_discoveries`).
- Marker yang belum pernah ditemukan tampil dengan ikon "?" (kartu terkunci, §1.3 poin 4); setelah ditemukan berubah jadi foto asli.

**Untuk Ranger:**
- Peta dibuka dengan tombol kamera mengambang (seperti desain lama) untuk mode **AR Scan** — Ranger yang jalan-jalan di lapangan, memotret tumbuhan nyata, dikirim ke AI untuk identifikasi.
- Setelah scan berhasil → `plant_sightings` baru dibuat berstatus `pending`, menunggu verifikasi (bisa dari Ranger lain) sebelum muncul sebagai marker yang bisa "ditemukan" Viewer.
- Ranger tetap bisa lihat semua marker di peta (termasuk yang `pending`, ditandai beda — misal ikon jam pasir) untuk konteks lapangan.

### 3.3 Penyimpanan / Galeri (Viewer) — Gaya "Seedex"
- Header progress: "Seedex Progress — X / Y Ditemukan" dengan progress bar tebal (§1.3 poin 2).
- Filter tab: Semua / kategori (mengikuti kategori yang relevan dari `plant_species`, jika ada pengelompokan).
- Grid kartu koleksi (§1.3 poin 4): ditemukan = foto + nama + badge rarity; belum ditemukan = kartu abu-abu terkunci dengan ikon petunjuk.
- Tap kartu yang sudah ditemukan → detail (foto besar, deskripsi dari data Ranger, tanggal ditemukan, lokasi).
- Rarity (Common/Uncommon/Rare) dipetakan dari `plant_species.conservation_status` atau field baru — **perlu diputuskan pemetaannya sebelum implementasi**, catat di `schema.md` bila ada kolom baru.

### 3.4 MiniGame (Kebun Virtual) — restyle "Bag/Inventaris" + Misi
- Grid lahan (petak-petak) tetap seperti sebelumnya secara fungsi (kosong/tumbuh/siap panen), tapi ikon & warna mengikuti §1.3.
- Progress tumbuh pakai progress bar rounded tebal (§1.3 poin 2), bukan bar tipis lama.
- Tab terpisah **Bag** menampilkan inventaris (`inventory_items`): grid slot item dengan jumlah (mis. "x12"), slot kosong tampil terkunci (ikon gembok) sesuai kapasitas.
- Tombol "Beli Lahan"/"Beli Benih/Alat" membuka **Shop** (lihat §3.4a) alih-alih panel kecil.
- Setelah panen: animasi + notifikasi EXP/Coin, gaya kartu misi selesai (§1.3 poin 7).

### 3.4a Shop (Layar Baru)
- Grid tombol kategori bulat: Seeds, Tools, (kategori lain sesuai `inventory_items.item_type`).
- List item dengan harga (ikon Coin + angka), tombol beli mengurangi `coin_transactions` (lihat `RewardService`/logic beli yang sudah ada di `GardenService`).
- **Tidak termasuk** dulu: Daily Free Gift Box, Potions/Alchemy, Skins — itu di luar cakupan (§1.4), jangan diimplementasikan tanpa update `prd.md`.

### 3.5 Izin Lokasi
- Modal singkat sebelum browser memunculkan prompt izin lokasi native — jelaskan dulu **kenapa** dibutuhkan ("Dipakai untuk menandai lokasi saat kamu check-in kompos atau menanam pohon nyata").
- Tombol "Izinkan" memicu `navigator.geolocation.getCurrentPosition`. Tombol "Lewati dulu" tetap mengizinkan lanjut (fitur kompos/planting tetap jalan tanpa lokasi, kolom lokasi jadi `null`).
- Tidak ada indikator "lokasi sedang dilacak" yang persisten di UI manapun — sesuai prinsip privasi di `architecture.md` §4.4, lokasi cuma diambil sesaat per aksi.

### 3.6 Tantangan Kompos
- Halaman daftar bahan kompos (`compost_materials`): kartu berisi nama bahan + ikon, tap untuk lihat detail & instruksi.
- Tombol besar "Mulai Proses Kompos" di detail bahan.
- Halaman **Progress Kompos**: tampilan timeline vertikal (mirip riwayat pesanan) — tiap check-in muncul sebagai item dengan foto kecil, label tahap, dan tanggal.
- Tombol mengambang (floating action button) "Check-in Sekarang" — buka kamera, pilih label tahap dari dropdown singkat (bukan free text, supaya konsisten: "Baru Mulai", "Fermentasi Awal", "Fermentasi Lanjut", "Matang").
- Saat tahap "Matang" dipilih, tampilkan tombol khusus "Tandai Kompos Matang" yang mengunci proses dan membuka opsi "Lanjut Tanam Pohon".

### 3.7 Tanam Pohon Nyata
- Form sederhana: foto bukti (wajib), pilih jenis pohon (dropdown dari katalog, atau isi manual jika tidak ada), lokasi (otomatis terisi dari izin lokasi jika diberikan).
- Setelah submit: layar konfirmasi dengan animasi perayaan + rincian EXP yang didapat — beri penekanan visual bahwa ini adalah pencapaian besar (milestone tertinggi dalam sistem reward).

### 3.8 Papan Peringkat Mingguan
- Daftar ranking (top 10 misalnya) dengan foto profil, nama, total EXP minggu berjalan.
- Highlight khusus untuk posisi user yang sedang login (meski di luar top 10, tetap tampil di bagian bawah "Posisimu: #37").
- Badge/ikon berbeda untuk peringkat 1-3 (emas/perak/perunggu).
- Tab/switch untuk lihat "Minggu Ini" vs "Riwayat Minggu Lalu" (baca dari `weekly_rewards`).
- Jika user termasuk pemenang minggu lalu dan penghargaan belum diklaim, tampilkan tombol "Klaim Penghargaan" yang mencolok di bagian atas halaman.

### 3.9 Dashboard Ranger

- Visual berbeda dari Home Screen Viewer secara sengaja — nuansa "meja arsip/katalog", bukan hub eksplorasi. Tetap dalam keluarga desain herbarium yang sama (§1), tapi lebih tenang/administratif.
- 3 kartu navigasi bergaya **laci arsip**: **Katalog Spesies**, **Katalog Bahan Kompos**, **Verifikasi Temuan** — tiap kartu menampilkan jumlah entri/antrean (mis. "24 spesies", "7 menunggu verifikasi").
- Header menampilkan label peran jelas: "RANGER" dengan badge monospace, agar tidak tertukar visual dengan Home Screen Viewer.

### 3.10 Katalog Spesies & Katalog Bahan Kompos

- List ditampilkan sebagai **kartu indeks** (rows bergaya kartu katalog perpustakaan lama) — kode (`species_code`/`material_code`) di kiri dalam monospace, nama & ringkasan deskripsi di kanan, tombol edit di ujung.
- Form tambah/edit dibuka sebagai halaman penuh (bukan modal kecil) bergaya "mengisi kartu arsip" — label field pakai style konsisten dengan §1.2 (monospace untuk kode, serif untuk judul form).
- Upload foto referensi (khusus spesies) memakai area drop-zone bergaya "tempel spesimen" sesuai signature §1.3.

### 3.11 Verifikasi Temuan

- Tampilan queue/antrean: tiap item (sighting atau real planting) sebagai kartu horizontal — foto di kiri, detail (nama spesies hasil AI/deskripsi user, tanggal, lokasi jika ada) di tengah, dua tombol besar di kanan: **"Verifikasi"** (hijau forest) dan **"Tolak"** (clay/danger).
- Tab untuk memisahkan antrean: "Hasil Scan" vs "Bukti Tanam Pohon" (dua sumber data berbeda, `plant_sightings` dan `real_plantings`).
- Setelah diputuskan, item hilang dari antrean dengan transisi singkat — beri konfirmasi ringan ("Ditandai terverifikasi") tanpa modal besar yang mengganggu alur tinjauan cepat berulang.

## 4. Microcopy & Nada Bahasa

- Bahasa Indonesia santai tapi tetap informatif — sesuai audiens pelajar/masyarakat umum.
- Pesan edukasi tumbuhan (dari data Ranger) ditulis singkat, mudah dipahami, tidak terlalu ilmiah.
- Pesan sistem (error, empty state, konfirmasi) selalu ramah, tidak menyalahkan pengguna.

## 5. Aksesibilitas & Performa

- Kontras warna teks terhadap background minimal memenuhi standar keterbacaan (hindari teks abu-abu muda di atas putih).
- Ukuran font dasar tidak lebih kecil dari 14px untuk teks body.
- Aset gambar dikompresi (WebP jika didukung) mengingat target perangkat HP kelas menengah-bawah dan penggunaan AR yang sudah cukup berat di sisi browser.
