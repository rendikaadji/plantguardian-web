# PRD — PlantGuardian

**Game Berbasis Augmented Reality untuk Meningkatkan Kesadaran Konservasi Tumbuhan**

|                                  |                                                                                         |
| -------------------------------- | --------------------------------------------------------------------------------------- |
| **Nama Proyek**                  | PlantGuardian                                                                           |
| **Institusi**                    | MTsN 4 Jakarta                                                                          |
| **Tim**                          | M. Robi Albihan, Mikail Muhammad A, M. Sabiq Oezil A, Diaz Fahrezi, Zaidan Sakha Wibowo |
| **Guru Pembimbing**              | Aulia Farah Dina Nur Al Iman, S.Pd                                                      |
| **Technical Mentor / Developer** | Kak Rendi (Compost Software)                                                            |
| **Dokumen Pendukung**            | `architecture.md`, `design.md`, `schema.md`, `rules.md`                                 |

> Dokumen ini adalah sumber kebenaran (source of truth) untuk _apa_ yang dibangun dan _kenapa_. Saat melakukan vibe coding, AI wajib membaca dokumen ini beserta 4 dokumen pendukung sebelum menulis kode apa pun, dan tidak boleh menambah fitur di luar cakupan tanpa dicatat ulang di sini terlebih dahulu.

---

## 1. Latar Belakang & Masalah

Masyarakat Indonesia — khususnya generasi muda — kurang mengenali keanekaragaman hayati (tumbuhan) di lingkungan sekitar mereka, dan dokumentasi terhadap tumbuhan lokal seringkali tidak terstruktur atau tidak ada sama sekali. Tidak ada cara yang menyenangkan untuk mengedukasi masyarakat tentang pentingnya menjaga dan mengenali tumbuhan di sekitar mereka.

**PlantGuardian** menjawab masalah ini dengan menggabungkan edukasi konservasi tumbuhan dan mekanisme _gamifikasi_ (scan AR, koleksi, minigame berkebun virtual, EXP & Coin) sehingga pengguna termotivasi untuk mengenali dan mendokumentasikan tumbuhan nyata di sekitar mereka.

## 2. Tujuan Proyek

1. Membantu menjaga kelestarian alam dengan mendorong masyarakat mengenali dan mendokumentasikan tumbuhan di lingkungan sekitar.
2. Mengedukasi masyarakat tentang keanekaragaman hayati Indonesia melalui pengalaman interaktif berbasis AR.
3. Menyediakan pengalaman yang tidak membosankan (game loop, reward system) agar edukasi tetap berkelanjutan.

## 3. Peran Pengguna (User Roles)

Aplikasi memiliki dua peran dengan alur dan hak akses berbeda. **Pemisahan role ini adalah keputusan arsitektur inti dan tidak boleh dicampur.**

### 3.1 Ranger

- Peran "kontributor data / kurator katalog / **pemindai lapangan**". **Siapa saja bisa mendaftar sebagai Ranger** — tidak dibatasi tim inti, sama seperti pendaftaran Viewer (pilih role saat onboarding).
- Tanggung jawab Ranger:
    1. **Mengelola katalog spesies tumbuhan** (`plant_species`) — tambah/edit data lewat form manual.
    2. **Mengelola katalog bahan kompos** (`compost_materials`) — tambah/edit bahan beserta instruksi pembuatan kompos.
    3. **Melakukan Scan tumbuhan nyata di lapangan (AR + AI)** — **fitur ini sekarang milik Ranger, bukan Viewer** (perubahan dari versi sebelumnya). Ranger memotret tumbuhan nyata via kamera, AI mengidentifikasi, hasil tersimpan sebagai `plant_sightings` berstatus `pending`.
    4. **Verifikasi/moderasi temuan** — meninjau hasil scan Ranger lain (`plant_sightings`) dan bukti tanam pohon nyata milik Viewer (`real_plantings`), menandai `verified`/`rejected` (lihat §4.7).
- Dashboard Ranger terpisah dari Home Screen Viewer — lihat detail alur di §4.7 dan `design.md` §3.9-3.11.

### 3.2 Viewer

- Peran "penjelajah / pemain". **Tidak memiliki fitur Scan/kamera AR** (perubahan dari versi sebelumnya — Scan sekarang eksklusif milik Ranger).
- Sign up → pilih role → tutorial → Home Screen.
- Dari Home Screen bisa mengakses: **Peta/Main**, **Penyimpanan/Galeri**, **MiniGame**.
- **Aktivitas inti di Peta:** menjelajah peta nyata dan **"menemukan" (catch)** spesies yang sudah pernah di-scan & diverifikasi Ranger di sekitar lokasinya — mirip menangkap di Pokemon GO, tanpa kamera sama sekali. Detail lengkap di `design.md` §3.2 dan `architecture.md` §4.5.
- Mendapat EXP dan Coin dari aktivitas menemukan (catch), minigame, maupun tantangan kompos.

> Referensi alur lengkap Viewer sebelumnya ada di flowchart `revisi3_flowchart_rangers.jpg` — **catatan: flowchart tsb masih menampilkan Viewer yang scan langsung; bagian itu sudah tidak berlaku lagi setelah perubahan peran ini. Flowchart baru yang mencerminkan pembalikan ini perlu dibuat sebelum dianggap final.**

## 4. Fitur Utama (Cakupan Wajib)

| #   | Fitur                                      | Deskripsi Singkat                                                                                                                                                                                                                          |
| --- | ------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| 1   | **Izin Lokasi**                            | Diminta setelah login, sebelum masuk tutorial. Dipakai untuk geotag bukti aksi nyata (progress kompos & bukti tanam pohon) — **bukan** untuk pelacakan lokasi berkelanjutan (lihat §4.6 dan `rules.md`).                                   |
| 1a  | **Bahasa (i18n)**                          | **Bahasa Inggris (English) sebagai default**, dengan pilihan ganti ke Bahasa Indonesia. Pengalih bahasa tersedia di header/Profile. Detail teknis di `architecture.md` §4.9, aturan konsistensi wajib di `rules.md` §4.2.                  |
| 2   | **SCAN**                                   | **Eksklusif milik Ranger** (perubahan — sebelumnya Viewer). Ranger memindai tumbuhan nyata via kamera (WebAR) di lapangan, AI mengidentifikasi jenis, hasil masuk sebagai `plant_sightings` berstatus `pending` menunggu verifikasi.       |
| 3   | **PENYIMPANAN (Galeri)**                   | Untuk Viewer: koleksi pribadi hasil "menemukan" (catch) spesies di Peta — gaya Seedex (lihat `design.md` §3.3). Untuk Ranger: bisa lihat riwayat scan miliknya sendiri.                                                                    |
| 4   | **PETA/MAIN**                              | Mode eksplorasi mirip Pokemon GO — **perilaku beda per role**: Viewer menjelajah & "menemukan" (catch, tanpa kamera) marker yang sudah di-scan+verified Ranger; Ranger memakai tombol kamera untuk Scan lapangan (lihat `design.md` §3.2). |
| 5   | **COIN & EXP**                             | Sistem reward, didapat dari Scan, MiniGame, maupun Tantangan Kompos. Coin dipakai membeli lahan/benih/alat di MiniGame. EXP dipakai untuk peringkat mingguan (§4.6).                                                                       |
| 6   | **MiniGame (Kebun Virtual)**               | Fitur sampingan (side-game), **murni digital/simulasi**: beli lahan → beli benih/alat → tanam → rawat → panen → dapat EXP/Coin. Loop ini terpisah dari fitur Scan/AR maupun Tantangan Kompos, dan bisa diulang terus-menerus.              |
| 7   | **Tantangan Kompos & Penanaman Nyata**     | Fitur baru — lihat detail penuh di §4.5. Berbeda dari MiniGame karena ini melibatkan **aksi fisik nyata** yang dipantau sistem, bukan simulasi.                                                                                            |
| 8   | **Papan Peringkat Mingguan & Penghargaan** | Lihat detail penuh di §4.6.                                                                                                                                                                                                                |

Detail alur langkah-per-langkah setiap fitur mengikuti flowchart yang sudah final — **jangan improvisasi urutan langkah baru tanpa persetujuan.**

### 4.5 Tantangan Kompos & Penanaman Nyata (Fitur Baru)

Fitur ini terpisah dari MiniGame Kebun Virtual. Tujuannya mendorong aksi nyata di dunia fisik, bukan sekadar aktivitas dalam aplikasi.

**Alur:**

1. Sistem memberi **tantangan**: Viewer memilih bahan organik yang bisa dijadikan kompos **langsung dari katalog `compost_materials`** yang sudah diinput Ranger (dulu tertulis "bisa pakai fitur SCAN" — sudah tidak berlaku karena Scan kini eksklusif Ranger; Viewer memilih dari daftar, bukan memindai).
2. Setelah bahan dipilih/dikonfirmasi, sistem menampilkan **panduan cara pembuatan kompos** sesuai bahan tersebut.
3. Viewer memulai proses pembuatan kompos → tercatat sebagai satu proses kompos aktif milik Viewer.
4. Selama proses berjalan, Viewer **check-in berkala** (upload foto + pilih tahap saat ini) — sistem memantau progres sampai tahap "matang/jadi".
5. Setelah kompos matang, Viewer memakainya untuk **menanam pohon secara nyata** (real-life) — diunggah sebagai bukti (foto + lokasi + jenis pohon).
6. Setiap tahap (mulai tantangan, tiap check-in, kompos matang, pohon berhasil ditanam) memberi EXP — penanaman pohon nyata adalah milestone dengan reward EXP terbesar.

**Catatan verifikasi (versi awal):** verifikasi tahap kompos & bukti tanam bersifat **self-report oleh Viewer** (foto + pilihan tahap), belum menggunakan AI verifikasi otomatis atas kematangan kompos. Kemungkinan pengembangan lanjutan: spot-check manual oleh Ranger/admin. Ini dicatat sebagai asumsi — beri tahu jika perlu proses verifikasi lain.

### 4.6 Papan Peringkat Mingguan & Penghargaan

- Total **EXP** yang didapat dalam satu minggu berjalan (Senin–Minggu, atau periode yang disepakati) dipakai untuk menyusun **papan peringkat mingguan (top mingguan)**.
- Pengguna dengan peringkat teratas mendapat **penghargaan** (bentuk penghargaan — badge digital, sertifikat, atau hadiah fisik dari pihak sekolah/komunitas — belum ditentukan, perlu diklarifikasi saat implementasi UI klaim reward).
- Peringkat dihitung ulang tiap minggu; riwayat peringkat & penghargaan sebelumnya tetap tersimpan (lihat `schema.md` §11).

### 4.7 Dashboard & Alur Ranger (Fitur Baru)

Sebelumnya dicatat sebagai "Beda Flowchart" tanpa detail — berikut spesifikasi lengkapnya.

**Kelola Katalog Spesies**

- List semua `plant_species` yang sudah diinput (oleh Ranger manapun, bukan cuma milik sendiri — katalog ini bersama).
- Tambah/edit: `species_code`, `common_name`, `scientific_name`, `description`, `conservation_status`, `reference_image_path`.

**Kelola Katalog Bahan Kompos**

- List semua `compost_materials`.
- Tambah/edit: `material_code`, `name`, `description`, `instructions`.

**Verifikasi Temuan**

- Antrean (queue) berisi:
    - `plant_sightings` berstatus `pending` — Ranger bisa tandai `verified` (identifikasi AI benar) atau `rejected` (identifikasi AI salah/meragukan).
    - `real_plantings` berstatus `self_reported` — Ranger tinjau foto bukti tanam, tandai `verified` atau `rejected`.
- Ranger manapun bisa memverifikasi (tidak harus Ranger yang sama dengan yang menginput katalog terkait) — ini queue bersama, bukan personal.

**Catatan out-of-scope tetap berlaku:** verifikasi ini murni tinjauan manual oleh Ranger (baca foto, putuskan), belum ada AI verifikasi otomatis atas kematangan kompos atau keabsahan foto (lihat §8).

## 5. Target Pengguna

- **Pengguna utama:** Masyarakat umum, khususnya pelajar dan generasi muda, sebagai _Viewer_.
- **Pengguna sekunder:** Kontributor/data steward (guru, komunitas botani, tim inti) sebagai _Ranger_.

## 6. Kriteria Keberhasilan

- Aplikasi mudah dipakai (onboarding singkat, UI jelas).
- Pengalaman tidak membosankan (ada reward loop: EXP, Coin, koleksi galeri, minigame).
- Sistem pengenalan tumbuhan (AI/CV) memberikan hasil yang cukup akurat untuk kategori tumbuhan umum yang didukung.

## 7. Tech Stack (ringkasan — detail penuh di `architecture.md`)

- **Frontend & WebAR:** HTML5, CSS3, Vanilla JS / Alpine.js, AR.js atau MindAR, HTML5 Canvas (MiniGame).
- **Backend:** PHP + Laravel (Auth, Routing/API, MySQL).
- **AI & Computer Vision:** Python (Flask/FastAPI), OpenCV, TensorFlow/ANN untuk klasifikasi gambar tumbuhan.

## 8. Di Luar Cakupan (Out of Scope) untuk Versi Ini

- Native mobile app (aplikasi ini berbasis WebAR di browser, bukan APK/IPA).
- Fitur sosial (chat, follow antar pengguna, dsb) — belum menjadi prioritas kecuali diminta ulang. (Papan peringkat mingguan tetap masuk cakupan, lihat §4.6.)
- Multiplayer / real-time interaction pada MiniGame.
- Verifikasi otomatis (AI/CV) atas kematangan kompos atau keabsahan bukti tanam pohon/identifikasi spesies — verifikasi bersifat tinjauan manual oleh Ranger (lihat §4.7).
- Pelacakan lokasi berkelanjutan/background — lokasi hanya diminta & dipakai pada momen upload bukti aksi nyata (lihat §4.1, `rules.md` untuk aturan privasi).
- Sistem approval berjenjang untuk siapa yang boleh jadi Ranger — versi awal: pendaftaran Ranger terbuka untuk siapa saja (§3.1), belum ada proses seleksi/approval admin.

## 9. Tantangan yang Diketahui

| Tantangan                                  | Mitigasi                                                                                                    |
| ------------------------------------------ | ----------------------------------------------------------------------------------------------------------- |
| Waktu pengerjaan terbatas (proyek sekolah) | Prioritaskan fitur wajib (SCAN, PENYIMPANAN, PETA) dulu, COIN/MiniGame sebagai tambahan jika waktu cukup.   |
| Akurasi model AI klasifikasi tumbuhan      | Batasi dulu jumlah kategori tumbuhan yang didukung di versi awal (lihat `schema.md` tabel `plant_species`). |
| Performa AR di HP kelas menengah-bawah     | Gunakan library ringan (AR.js/MindAR), hindari model 3D berat.                                              |

## 10. Catatan Konsistensi untuk AI (Vibe Coding)

- Dokumen ini adalah rujukan fitur. Untuk struktur teknis baca `architecture.md`, untuk tampilan baca `design.md`, untuk struktur data baca `schema.md`, dan untuk aturan penulisan kode baca `rules.md`.
- Jika ada instruksi baru dari user yang bertentangan dengan dokumen ini, **update dokumen ini dulu**, baru lanjut coding — jangan diam-diam menyimpang.
