# Rules — PlantGuardian (Panduan Wajib untuk AI Vibe Coding)

> Dokumen ini adalah **pagar pengaman** agar setiap sesi vibe coding (baik oleh AI maupun developer) tetap konsisten dengan `prd.md`, `architecture.md`, `design.md`, dan `schema.md`. Sebelum menulis satu baris kode pun, baca dokumen ini dan 4 dokumen lainnya. Jika ada permintaan baru yang bertentangan dengan aturan di sini, **klarifikasi dan update dokumen dulu, jangan diam-diam menyimpang.**

## 1. Prinsip Umum

1. **Konsistensi di atas kecepatan.** Lebih baik bertanya/klarifikasi daripada menebak dan menghasilkan kode yang menyimpang dari 4 dokumen lain.
2. **Tidak ada scope creep diam-diam.** Fitur baru yang tidak ada di `prd.md` tidak boleh langsung dikerjakan — catat dulu di `prd.md`, baru implementasi.
3. **Clean code, bukan quick hack.** Proyek ini dikerjakan dengan standar developer profesional walau berasal dari proyek sekolah — kode harus bisa dipelihara jangka panjang.
4. **Role separation (Ranger vs Viewer) adalah aturan keras.** Semua route Ranger wajib dibungkus middleware `ranger` (`EnsureUserIsRanger`, lihat `architecture.md` §4.7) — **termasuk `POST /api/scan`, yang sekarang eksklusif Ranger** (perubahan — sebelumnya dianggap fitur Viewer). **Semua route Viewer (home, peta [catch/discover], galeri, minigame, kompos, leaderboard) wajib dibungkus middleware `viewer` (`EnsureUserIsViewer`)** — bukan cuma Ranger yang dibatasi, Viewer juga tidak boleh bisa mengakses route Ranger dan sebaliknya. Jangan pernah mencampur logic/akses dua role ini dalam satu controller/service yang sama tanpa pemisahan jelas.
   4.1. **Redirect berbasis role wajib berlaku di SETIAP login, bukan cuma sekali saat onboarding.** Setelah proses login berhasil (baik login pertama kali maupun login berikutnya), sistem wajib mengecek `role` user dan mengarahkan ke dashboard yang sesuai (`ranger.dashboard` untuk Ranger, `home` untuk Viewer) — lihat `architecture.md` §4.8.
5. **Pengecualian scoping untuk data katalog bersama.** `plant_species` dan `compost_materials` adalah data bersama (dibaca lintas-role), bukan data personal — query bacanya **tidak** perlu discope per `created_by` (beda dengan aturan scoping umum di §2.4). Ini pengecualian yang sudah dicatat sadar di `architecture.md` §4.7, bukan pelanggaran.

## 2. Aturan Backend (Laravel)

### 2.1 Controller

- Controller **hanya** boleh berisi: menerima Form Request → memanggil satu/lebih Service → mengembalikan Resource/JSON response.
- **Dilarang** menulis query Eloquent kompleks, business logic, atau kalkulasi langsung di controller.
- Satu method controller = satu tanggung jawab (single responsibility). Jangan buat method controller yang menangani banyak skenario lewat banyak `if`.

### 2.2 Form Request

- Setiap input dari user (form, upload gambar, aksi minigame) **wajib** divalidasi lewat Form Request class sendiri, bukan validasi manual di controller.
- Pesan error validasi dalam Bahasa Indonesia yang ramah pengguna.

### 2.3 Service Class

- Semua business logic (contoh: `GardenService::harvest()`, `RewardService::grantExp()`) hidup di Service class, bukan di Model atau Controller.
- Service tidak boleh mengetahui detail HTTP (request/response) — hanya menerima data murni (DTO/array/model) dan mengembalikan hasil murni.

### 2.4 Eloquent Model & Query

- Semua query yang mengambil data milik user **wajib discope** (`where('user_id', ...)` atau global scope) — tidak ada pengecualian, ini untuk mencegah satu Viewer mengakses data Viewer lain.
- Relasi antar model didefinisikan eksplisit di Model (`hasMany`, `belongsTo`, dst) — jangan andalkan raw query join manual kalau relasi Eloquent bisa dipakai.
- Tidak ada raw SQL kecuali benar-benar diperlukan untuk performa, dan harus diberi komentar alasan penggunaannya.

### 2.5 API Resource

- Response API menggunakan Laravel API Resource (`PlantSightingResource`, `PlantingResource`, dst), bukan `return $model` mentah — agar bentuk response konsisten dan terkontrol field mana yang diekspos.

### 2.6 Integrasi ke AI Service

- Semua komunikasi ke Python AI service **wajib** lewat `PlantScanService`, jangan panggil HTTP client langsung dari controller.
- Wajib ada timeout dan fallback yang jelas jika AI service tidak merespons (jangan biarkan request menggantung).

## 3. Aturan Frontend (WebAR)

- Satu file JS = satu tanggung jawab modul (`ar-scanner.js` hanya urusan AR, `minigame.js` hanya urusan kebun virtual, dst) — sesuai struktur di `architecture.md` §2.2.
- Semua panggilan ke backend lewat `api-client.js` — dilarang menulis `fetch()` mentah tersebar di berbagai file.
- Tidak menambahkan framework SPA (React/Vue/dst) ke proyek ini kecuali disepakati ulang dan `architecture.md` diperbarui.
- State UI dikelola dengan Alpine.js secukupnya — hindari state global yang tidak terorganisir di `window`.

## 4. Aturan Database

- Perubahan skema **selalu** lewat migration baru (`php artisan make:migration`) — dilarang mengedit migration lama yang sudah pernah dijalankan.
- Semua foreign key didefinisikan di migration dengan `onDelete` yang eksplisit.
- Nama tabel/kolom mengikuti `schema.md` — jika butuh kolom/tabel baru, tambahkan dulu ke `schema.md` sebelum membuat migration.

## 4.1 Aturan Privasi Lokasi (Wajib)

- Koordinat lokasi **hanya** disimpan sebagai kolom pada record aksi spesifik (`compost_progress_logs.latitude/longitude`, `real_plantings.latitude/longitude`) — **dilarang** membuat tabel/kolom lokasi persisten di `users` atau tabel manapun yang menyimpan riwayat pergerakan pengguna.
- Frontend **tidak boleh** memanggil `navigator.geolocation.watchPosition` (pelacakan berkelanjutan) — hanya `getCurrentPosition` (ambil sesaat) pada momen upload bukti.
- Jika izin lokasi ditolak pengguna, backend harus tetap menerima request dengan `latitude`/`longitude` bernilai `null` — jangan menolak/memblokir fitur hanya karena lokasi tidak tersedia.

## 4.2 Aturan Bahasa (i18n) — Wajib Ketat, Sering Jadi Sumber Inkonsistensi

Rujuk `architecture.md` §4.9 untuk detail teknis. Aturan berikut **wajib**, karena campur bahasa (sebagian EN sebagian ID di halaman yang sama) adalah kegagalan paling umum kalau tidak didisiplinkan:

1. **Tidak ada teks UI hardcode.** Setiap teks yang dilihat pengguna (label tombol, judul, placeholder, pesan error, notifikasi) **wajib** lewat `__('file.key')` di Blade, atau `window.translations.key` di JS — tidak terkecuali, termasuk untuk teks yang "kelihatan sepele" seperti label ikon atau tooltip.
2. **Key harus ada di KEDUA bahasa sekaligus.** Setiap kali menambah key baru di `lang/en/*.php`, key yang sama **wajib** langsung ditambahkan juga di `lang/id/*.php` di request/prompt yang sama — jangan menunda salah satu bahasa untuk "nanti".
3. **Bahasa Inggris adalah default sistem.** `locale` default di database dan session adalah `'en'`. Konten yang di-generate otomatis (misal deskripsi dari AI, jika ada) juga default berbahasa Inggris kecuali locale user adalah `id`.
4. **Dilarang menerjemahkan sebagian halaman saja.** Kalau mengerjakan satu halaman/komponen untuk i18n, selesaikan SELURUH teks di halaman itu dalam satu sesi — jangan tinggalkan setengah halaman masih hardcode Bahasa Indonesia sementara setengah lainnya sudah pakai `__()`.
5. **Nama file bahasa mengikuti pemetaan fitur** di `architecture.md` §4.9 (`map.php`, `gallery.php`, dst) — jangan taruh semua key dalam satu file `app.php` raksasa tanpa struktur.

## 5. Konvensi Penamaan

| Elemen        | Konvensi                                | Contoh                 |
| ------------- | --------------------------------------- | ---------------------- |
| Tabel DB      | snake_case, jamak                       | `plant_sightings`      |
| Kolom DB      | snake_case                              | `species_code`         |
| Model         | PascalCase, tunggal                     | `PlantSighting`        |
| Controller    | PascalCase + `Controller`               | `ScanController`       |
| Service       | PascalCase + `Service`                  | `PlantScanService`     |
| Form Request  | PascalCase + `Request`                  | `ScanRequest`          |
| Route API     | kebab-case, prefix `/api`               | `/api/plant-sightings` |
| File JS modul | kebab-case                              | `ar-scanner.js`        |
| CSS variable  | kebab-case dengan prefix `--color-` dsb | `--color-primary`      |

## 6. Alur Kerja Vibe Coding (Checklist Sebelum Ngoding)

Sebelum AI mulai menulis kode untuk sebuah task, pastikan:

1. ☐ Fitur yang diminta ada di `prd.md`. Jika tidak, tanyakan apakah perlu ditambahkan ke PRD dulu.
2. ☐ Struktur teknis yang dipakai sesuai `architecture.md` (folder, layer, kontrak API).
3. ☐ Tabel/kolom yang dibutuhkan sudah ada di `schema.md`. Jika belum, update `schema.md` dulu.
4. ☐ Tampilan yang dibuat sesuai `design.md` (palet warna, pemetaan layar).
5. ☐ Kode mengikuti aturan di dokumen ini (thin controller, service layer, form request, scoped query).

## 7. Larangan Eksplisit

- ❌ Menulis logic bisnis di dalam controller atau route closure.
- ❌ Mengakses data user lain tanpa scoping.
- ❌ Menambahkan library/framework besar tanpa update `architecture.md`.
- ❌ Mengubah palet warna atau struktur navigasi tanpa update `design.md`.
- ❌ Membuat tabel/kolom baru tanpa mencatatnya di `schema.md`.
- ❌ Mencampur logic Ranger dan Viewer dalam satu alur/controller yang sama.
- ❌ Memanggil AI service Python langsung dari frontend.
- ❌ Menyimpan lokasi pengguna secara persisten/berkelanjutan (lihat §4.1) — lokasi hanya menempel pada record aksi spesifik.
- ❌ Menulis teks UI hardcode (Bahasa Indonesia maupun Inggris) langsung di Blade/JS tanpa lewat `__()`/`window.translations` (lihat §4.2) — termasuk menambah key hanya di satu bahasa dan menunda bahasa lainnya.

## 8. Definition of Done (per fitur)

Sebuah fitur dianggap selesai jika:

- Controller tipis, business logic ada di Service.
- Validasi input lewat Form Request.
- Query data personal sudah discope per user.
- Response API konsisten lewat API Resource.
- Tampilan sesuai `design.md` (warna, layout, microcopy).
- Tidak ada penyimpangan dari flowchart alur yang sudah disepakati.
