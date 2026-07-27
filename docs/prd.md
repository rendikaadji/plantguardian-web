# PRD — PlantGuardian
**Game Berbasis Augmented Reality untuk Meningkatkan Kesadaran Konservasi Tumbuhan**

| | |
|---|---|
| **Nama Proyek** | PlantGuardian |
| **Institusi** | MTsN 4 Jakarta |
| **Tim** | M. Robi Albihan, Mikail Muhammad A, M. Sabiq Oezil A, Diaz Fahrezi, Zaidan Sakha Wibowo |
| **Guru Pembimbing** | Aulia Farah Dina Nur Al Iman, S.Pd |
| **Technical Mentor / Developer** | Kak Rendi (Compost Software) |
| **Dokumen Pendukung** | `architecture.md`, `design.md`, `schema.md`, `rules.md` |

> Dokumen ini adalah sumber kebenaran (source of truth) untuk *apa* yang dibangun dan *kenapa*. Saat melakukan vibe coding, AI wajib membaca dokumen ini beserta 4 dokumen pendukung sebelum menulis kode apa pun, dan tidak boleh menambah fitur di luar cakupan tanpa dicatat ulang di sini terlebih dahulu.

---

## 1. Latar Belakang & Masalah

Masyarakat Indonesia — khususnya generasi muda — kurang mengenali keanekaragaman hayati (tumbuhan) di lingkungan sekitar mereka, dan dokumentasi terhadap tumbuhan lokal seringkali tidak terstruktur atau tidak ada sama sekali. Tidak ada cara yang menyenangkan untuk mengedukasi masyarakat tentang pentingnya menjaga dan mengenali tumbuhan di sekitar mereka.

**PlantGuardian** menjawab masalah ini dengan menggabungkan edukasi konservasi tumbuhan dan mekanisme *gamifikasi* (scan AR, koleksi, minigame berkebun virtual, EXP & Coin) sehingga pengguna termotivasi untuk mengenali dan mendokumentasikan tumbuhan nyata di sekitar mereka.

## 2. Tujuan Proyek

1. Membantu menjaga kelestarian alam dengan mendorong masyarakat mengenali dan mendokumentasikan tumbuhan di lingkungan sekitar.
2. Mengedukasi masyarakat tentang keanekaragaman hayati Indonesia melalui pengalaman interaktif berbasis AR.
3. Menyediakan pengalaman yang tidak membosankan (game loop, reward system) agar edukasi tetap berkelanjutan.

## 3. Peran Pengguna (User Roles)

Aplikasi memiliki dua peran dengan alur dan hak akses berbeda. **Pemisahan role ini adalah keputusan arsitektur inti dan tidak boleh dicampur.**

### 3.1 Ranger
- Peran "petugas lapangan / kontributor data".
- Bertanggung jawab menginput katalog data tumbuhan (nama, deskripsi, klasifikasi, foto referensi) ke dalam sistem.
- Memiliki alur/flowchart tersendiri (lihat *Beda Flowchart* pada diagram alur), **di luar cakupan dokumen fitur Viewer di bawah**, namun tabelnya tetap didefinisikan di `schema.md` karena datanya dipakai lintas-role.

### 3.2 Viewer
- Peran "pemain / pengguna umum".
- Sign up → pilih role → tutorial → Home Screen.
- Dari Home Screen bisa mengakses: **Peta/Main (AR Scan)**, **Penyimpanan/Galeri**, **MiniGame (Kebun Virtual)**.
- Saat scan tumbuhan lewat AR, detail yang muncul adalah data yang **sudah diinput oleh Ranger sebelumnya** (bukan diinput ulang oleh Viewer).
- Mendapat EXP dan Coin dari aktivitas scan maupun minigame.

> Referensi alur lengkap Viewer ada di flowchart `revisi3_flowchart_rangers.jpg` yang sudah disepakati sebelumnya. Semua PRD/kode turunan harus konsisten dengan flow tersebut.

## 4. Fitur Utama (Cakupan Wajib)

| # | Fitur | Deskripsi Singkat |
|---|-------|--------------------|
| 1 | **SCAN** | Viewer memindai tumbuhan nyata via kamera (WebAR) untuk mengidentifikasi jenis dan menampilkan info yang sudah diinput Ranger. |
| 2 | **PENYIMPANAN (Galeri)** | Menyimpan hasil scan/foto tumbuhan yang sudah ditemukan Viewer sebagai koleksi pribadi. |
| 3 | **PETA/MAIN** | Mode AR mirip Pokemon GO — pintu masuk ke fitur Scan, direpresentasikan sebagai "peta eksplorasi". |
| 4 | **COIN & EXP** | Sistem reward yang didapat dari Scan maupun MiniGame, dipakai untuk membeli lahan/benih/alat di MiniGame. |
| 5 | **MiniGame (Kebun Virtual)** | Fitur sampingan (side-game): beli lahan → beli benih/alat → tanam → rawat → panen → dapat EXP/Coin. Loop ini terpisah dari fitur Scan/AR dan bisa diulang terus-menerus. |

Detail alur langkah-per-langkah setiap fitur mengikuti flowchart yang sudah final — **jangan improvisasi urutan langkah baru tanpa persetujuan.**

## 5. Target Pengguna

- **Pengguna utama:** Masyarakat umum, khususnya pelajar dan generasi muda, sebagai *Viewer*.
- **Pengguna sekunder:** Kontributor/data steward (guru, komunitas botani, tim inti) sebagai *Ranger*.

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
- Alur lengkap Ranger untuk input data (dicatat sebagai flowchart terpisah, tabel database sudah disiapkan tapi UI belum termasuk dalam sprint Viewer).
- Fitur sosial (leaderboard antar pengguna, chat, dsb) — belum menjadi prioritas kecuali diminta ulang.
- Multiplayer / real-time interaction pada MiniGame.

## 9. Tantangan yang Diketahui

| Tantangan | Mitigasi |
|---|---|
| Waktu pengerjaan terbatas (proyek sekolah) | Prioritaskan fitur wajib (SCAN, PENYIMPANAN, PETA) dulu, COIN/MiniGame sebagai tambahan jika waktu cukup. |
| Akurasi model AI klasifikasi tumbuhan | Batasi dulu jumlah kategori tumbuhan yang didukung di versi awal (lihat `schema.md` tabel `plant_species`). |
| Performa AR di HP kelas menengah-bawah | Gunakan library ringan (AR.js/MindAR), hindari model 3D berat. |

## 10. Catatan Konsistensi untuk AI (Vibe Coding)

- Dokumen ini adalah rujukan fitur. Untuk struktur teknis baca `architecture.md`, untuk tampilan baca `design.md`, untuk struktur data baca `schema.md`, dan untuk aturan penulisan kode baca `rules.md`.
- Jika ada instruksi baru dari user yang bertentangan dengan dokumen ini, **update dokumen ini dulu**, baru lanjut coding — jangan diam-diam menyimpang.
