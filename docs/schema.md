# Database Schema — PlantGuardian

> Rujuk `architecture.md` untuk konteks penggunaan tabel-tabel ini. Semua tabel menggunakan MySQL, dikelola lewat Laravel Migration + Eloquent Model. Nama tabel snake_case jamak, nama kolom snake_case, primary key `id` (bigIncrements), setiap tabel punya `created_at`/`updated_at` kecuali disebutkan lain.

## 1. Entity Relationship (Ringkasan)

```
users ──< plant_sightings >── plant_species
users ──< garden_plots ──< plantings >── plant_species (opsional, jika minigame pakai jenis nyata)
users ──< coin_transactions
users ──< exp_logs
users ──< inventory_items
```

## 2. Tabel `users`

| Kolom                   | Tipe                          | Keterangan                                                                                                                                       |
| ----------------------- | ----------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------ |
| id                      | bigIncrements                 | PK                                                                                                                                               |
| name                    | string                        | Nama pengguna                                                                                                                                    |
| email                   | string, unique                |                                                                                                                                                  |
| password                | string (hashed)               |                                                                                                                                                  |
| role                    | enum(`ranger`,`viewer`)       | Menentukan hak akses & flow (lihat `prd.md` §3)                                                                                                  |
| exp                     | integer, default 0            | Total EXP saat ini                                                                                                                               |
| coin                    | integer, default 0            | Total Coin saat ini                                                                                                                              |
| locale                  | enum(`en`,`id`), default `en` | Preferensi bahasa pengguna — dipakai untuk menampilkan UI dalam bahasa yang dipilih, persisten lintas sesi/device (lihat `architecture.md` §4.9) |
| created_at / updated_at | timestamp                     |                                                                                                                                                  |

**Catatan:** `exp` dan `coin` disimpan sebagai kolom cache di `users` untuk kecepatan baca, tapi **riwayat perubahan wajib tetap dicatat** di `exp_logs` dan `coin_transactions` (jangan hanya update kolom ini tanpa log — untuk audit dan mencegah exploit).

## 3. Tabel `plant_species` (Katalog — diisi oleh Ranger)

| Kolom                   | Tipe                   | Keterangan                                                              |
| ----------------------- | ---------------------- | ----------------------------------------------------------------------- |
| id                      | bigIncrements          | PK                                                                      |
| species_code            | string, unique         | Kode unik dipakai untuk mencocokkan hasil AI (`predicted_species_code`) |
| common_name             | string                 | Nama umum (contoh: "Pohon Mangga")                                      |
| scientific_name         | string, nullable       | Nama ilmiah                                                             |
| description             | text                   | Deskripsi/edukasi tentang tumbuhan                                      |
| conservation_status     | string, nullable       | Status konservasi (opsional)                                            |
| reference_image_path    | string, nullable       | Foto referensi diinput Ranger                                           |
| created_by              | foreign key → users.id | Ranger yang menginput                                                   |
| created_at / updated_at | timestamp              |                                                                         |

## 4. Tabel `plant_sightings` (Temuan — hasil Scan **Ranger**)

> **Perubahan penting:** tabel ini sebelumnya didokumentasikan sebagai "hasil scan Viewer". Sekarang **Scan adalah fitur Ranger**, jadi kolom `user_id` di tabel ini merujuk ke **Ranger** yang melakukan scan di lapangan, bukan Viewer. **Perlu migration baru** untuk rename kolom `user_id` → `ranger_id` di database yang sudah berjalan (jangan drop tabel, cukup rename kolom + sesuaikan foreign key & kode yang mereferensikannya).

| Kolom                   | Tipe                                                     | Keterangan                                                                                                                                                                    |
| ----------------------- | -------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| id                      | bigIncrements                                            | PK                                                                                                                                                                            |
| ranger_id               | foreign key → users.id (**rename dari `user_id`**)       | Ranger yang melakukan scan di lapangan                                                                                                                                        |
| plant_species_id        | foreign key → plant_species.id, nullable                 | Hasil pencocokan AI; null jika gagal dikenali                                                                                                                                 |
| photo_path              | string                                                   | Foto hasil scan Ranger                                                                                                                                                        |
| confidence_score        | float, nullable                                          | Skor keyakinan dari AI service                                                                                                                                                |
| latitude / longitude    | decimal, nullable                                        | Lokasi saat scan — dipakai sebagai marker di Peta untuk "ditemukan" Viewer                                                                                                    |
| verification_status     | enum(`pending`,`verified`,`rejected`), default `pending` | Status tinjauan Ranger lain atas hasil identifikasi AI. **Hanya sighting berstatus `verified` yang muncul sebagai marker yang bisa "ditemukan" (catch) oleh Viewer di Peta.** |
| verified_by             | foreign key → users.id, nullable                         | Ranger (lain) yang memverifikasi                                                                                                                                              |
| verified_at             | timestamp, nullable                                      | Waktu diverifikasi                                                                                                                                                            |
| created_at / updated_at | timestamp                                                |                                                                                                                                                                               |

**Scoping:** untuk tampilan "riwayat scan saya" di Ranger, filter `where('ranger_id', auth()->id())`. Untuk tampilan marker di Peta (dilihat Viewer), **tidak** discope per user — query berdasarkan `verification_status = 'verified'` dan radius lokasi (data bersama, mirip pengecualian katalog di `rules.md` §1 poin 5).

## 4a. Tabel `plant_discoveries` (Koleksi "Catch" Milik Viewer — Tabel Baru)

Menggantikan asumsi lama bahwa `plant_sightings` langsung jadi koleksi Viewer. Sekarang koleksi personal Viewer (Galeri/Seedex) adalah tabel terpisah ini — dibuat saat Viewer menekan tombol "Temukan!" di marker peta.

| Kolom                | Tipe                             | Keterangan                                            |
| -------------------- | -------------------------------- | ----------------------------------------------------- |
| id                   | bigIncrements                    | PK                                                    |
| user_id              | foreign key → users.id           | Viewer yang menemukan/catch                           |
| plant_sighting_id    | foreign key → plant_sightings.id | Sighting (hasil scan Ranger) yang ditemukan           |
| discovered_at        | timestamp                        | Waktu ditemukan/catch                                 |
| latitude / longitude | decimal, nullable                | Lokasi Viewer saat catch (opsional, dari izin lokasi) |
| created_at           | timestamp                        |                                                       |

**Aturan:**

- Satu Viewer hanya bisa catch satu `plant_sighting_id` yang sama sekali (unique constraint pada kombinasi `user_id` + `plant_sighting_id`) — mencegah spam EXP dari sighting yang sama berulang kali.
- Scoping wajib: `where('user_id', auth()->id())` untuk tampilan Galeri Viewer.
- EXP/Coin diberikan oleh `RewardService` saat record baru berhasil dibuat (lihat `architecture.md`).

## 5. Tabel `garden_plots` (Lahan Tanam — MiniGame)

| Kolom                   | Tipe                   | Keterangan                                  |
| ----------------------- | ---------------------- | ------------------------------------------- |
| id                      | bigIncrements          | PK                                          |
| user_id                 | foreign key → users.id | Pemilik lahan                               |
| slot_number             | integer                | Urutan slot lahan milik user (1, 2, 3, dst) |
| unlocked                | boolean, default true  | Apakah lahan ini sudah dibeli/terbuka       |
| purchase_cost           | integer, nullable      | Harga beli lahan (jika berbayar Coin)       |
| created_at / updated_at | timestamp              |                                             |

## 6. Tabel `plantings` (Instance Tanaman di MiniGame)

| Kolom                   | Tipe                                     | Keterangan                                                              |
| ----------------------- | ---------------------------------------- | ----------------------------------------------------------------------- |
| id                      | bigIncrements                            | PK                                                                      |
| garden_plot_id          | foreign key → garden_plots.id            | Lahan tempat tanam                                                      |
| plant_species_id        | foreign key → plant_species.id, nullable | Jenis benih yang ditanam (jika minigame pakai jenis nyata dari katalog) |
| planted_at              | timestamp                                | Waktu tanam                                                             |
| ready_at                | timestamp                                | Waktu diperkirakan siap panen (planted_at + durasi tumbuh)              |
| last_watered_at         | timestamp, nullable                      | Waktu terakhir disiram/dirawat                                          |
| status                  | enum(`growing`,`ready`,`harvested`)      | Status siklus tanaman                                                   |
| harvested_at            | timestamp, nullable                      | Waktu dipanen                                                           |
| created_at / updated_at | timestamp                                |                                                                         |

**Business rule (lihat `GardenService`):** status berubah dari `growing` → `ready` otomatis ketika `now() >= ready_at` (dihitung saat query, bukan lewat cron wajib, tapi bisa dioptimalkan dengan scheduled job di iterasi berikutnya).

## 7. Tabel `inventory_items` (Benih, Alat, Bahan)

| Kolom                   | Tipe                           | Keterangan                        |
| ----------------------- | ------------------------------ | --------------------------------- |
| id                      | bigIncrements                  | PK                                |
| user_id                 | foreign key → users.id         | Pemilik inventaris                |
| item_type               | enum(`seed`,`tool`,`material`) | Kategori item                     |
| item_code               | string                         | Kode item (contoh: `SEED_MANGGA`) |
| quantity                | integer, default 0             | Jumlah dimiliki                   |
| created_at / updated_at | timestamp                      |                                   |

## 8. Tabel `coin_transactions` (Audit Log Coin)

| Kolom                         | Tipe                          | Keterangan                                                                           |
| ----------------------------- | ----------------------------- | ------------------------------------------------------------------------------------ |
| id                            | bigIncrements                 | PK                                                                                   |
| user_id                       | foreign key → users.id        |                                                                                      |
| amount                        | integer                       | Positif (dapat coin) atau negatif (belanja)                                          |
| reason                        | string                        | Contoh: `scan_reward`, `harvest_reward`, `buy_seed`, `buy_plot`                      |
| reference_type / reference_id | string / bigInteger, nullable | Polymorphic ref ke sumber transaksi (misal `plant_sightings.id` atau `plantings.id`) |
| created_at                    | timestamp                     |                                                                                      |

## 9. Tabel `exp_logs` (Audit Log EXP)

| Kolom                         | Tipe                          | Keterangan                              |
| ----------------------------- | ----------------------------- | --------------------------------------- |
| id                            | bigIncrements                 | PK                                      |
| user_id                       | foreign key → users.id        |                                         |
| amount                        | integer                       | Selalu positif (EXP tidak dikurangi)    |
| reason                        | string                        | Contoh: `scan_reward`, `harvest_reward` |
| reference_type / reference_id | string / bigInteger, nullable |                                         |
| created_at                    | timestamp                     |                                         |

## 11. Tabel `compost_materials` (Katalog Bahan Kompos)

| Kolom                   | Tipe                   | Keterangan                                       |
| ----------------------- | ---------------------- | ------------------------------------------------ |
| id                      | bigIncrements          | PK                                               |
| material_code           | string, unique         | Kode unik bahan (contoh: `DAUN_KERING`)          |
| name                    | string                 | Nama bahan yang ditampilkan                      |
| description             | text                   | Penjelasan kenapa bahan ini cocok untuk kompos   |
| instructions            | text                   | Panduan langkah pembuatan kompos untuk bahan ini |
| created_by              | foreign key → users.id | Ranger yang menginput                            |
| created_at / updated_at | timestamp              |                                                  |

## 12. Tabel `compost_processes` (Proses Kompos Aktif Milik Viewer)

| Kolom                   | Tipe                                                                    | Keterangan                                                        |
| ----------------------- | ----------------------------------------------------------------------- | ----------------------------------------------------------------- |
| id                      | bigIncrements                                                           | PK                                                                |
| user_id                 | foreign key → users.id                                                  | Pemilik proses                                                    |
| compost_material_id     | foreign key → compost_materials.id, nullable                            | Bahan yang dipakai (nullable jika bahan bebas/tidak dikatalogkan) |
| status                  | enum(`started`,`in_progress`,`matured`,`used_for_planting`,`abandoned`) | Status siklus kompos                                              |
| started_at              | timestamp                                                               | Waktu mulai                                                       |
| matured_at              | timestamp, nullable                                                     | Waktu tercatat matang                                             |
| created_at / updated_at | timestamp                                                               |                                                                   |

## 13. Tabel `compost_progress_logs` (Check-in Berkala)

| Kolom                | Tipe                               | Keterangan                                            |
| -------------------- | ---------------------------------- | ----------------------------------------------------- |
| id                   | bigIncrements                      | PK                                                    |
| compost_process_id   | foreign key → compost_processes.id |                                                       |
| stage_label          | string                             | Label tahap saat check-in (contoh: "Fermentasi Awal") |
| photo_path           | string                             | Foto bukti kondisi kompos saat check-in               |
| latitude / longitude | decimal, nullable                  | Lokasi saat check-in (dari izin lokasi)               |
| note                 | text, nullable                     | Catatan tambahan dari Viewer                          |
| created_at           | timestamp                          |                                                       |

## 14. Tabel `real_plantings` (Bukti Penanaman Pohon Nyata)

| Kolom                   | Tipe                                                                 | Keterangan                                                |
| ----------------------- | -------------------------------------------------------------------- | --------------------------------------------------------- |
| id                      | bigIncrements                                                        | PK                                                        |
| user_id                 | foreign key → users.id                                               |                                                           |
| compost_process_id      | foreign key → compost_processes.id, nullable                         | Kompos yang dipakai (nullable jika tanpa kompos tercatat) |
| plant_species_id        | foreign key → plant_species.id, nullable                             | Jenis pohon yang ditanam, jika diketahui/cocok katalog    |
| photo_path              | string                                                               | Foto bukti penanaman                                      |
| latitude / longitude    | decimal, nullable                                                    | Lokasi penanaman                                          |
| planted_at              | timestamp                                                            | Waktu ditanam                                             |
| verification_status     | enum(`self_reported`,`verified`,`rejected`), default `self_reported` | Status verifikasi                                         |
| verified_by             | foreign key → users.id, nullable                                     | Ranger yang memverifikasi                                 |
| verified_at             | timestamp, nullable                                                  | Waktu diverifikasi                                        |
| created_at / updated_at | timestamp                                                            |                                                           |

## 15. Tabel `weekly_rewards` (Riwayat Peringkat & Penghargaan Mingguan)

| Kolom              | Tipe                   | Keterangan                                               |
| ------------------ | ---------------------- | -------------------------------------------------------- |
| id                 | bigIncrements          | PK                                                       |
| user_id            | foreign key → users.id |                                                          |
| week_start_date    | date                   | Tanggal mulai minggu (contoh: Senin)                     |
| week_end_date      | date                   | Tanggal akhir minggu                                     |
| exp_earned         | integer                | Total EXP user tsb dalam periode minggu itu              |
| rank               | integer                | Peringkat pada minggu itu                                |
| reward_description | string, nullable       | Deskripsi penghargaan yang didapat (jika masuk top rank) |
| claimed_at         | timestamp, nullable    | Waktu penghargaan diklaim Viewer                         |
| created_at         | timestamp              |                                                          |

**Catatan implementasi:** tabel ini adalah **snapshot hasil kalkulasi mingguan**, bukan dihitung on-the-fly setiap request. Kalkulasi peringkat dijalankan lewat scheduled job/command Artisan (lihat `architecture.md` §3.2 — `LeaderboardService`) yang membaca `exp_logs` dalam rentang tanggal, lalu menyimpan hasilnya ke sini.

## 16. Aturan Umum Skema

- Semua foreign key wajib `onDelete('cascade')` kecuali disebutkan lain, dan wajib didefinisikan di migration (bukan hanya di model).
- Semua tabel yang datanya milik user wajib bisa di-scope lewat `user_id` — tidak boleh ada tabel data personal tanpa kolom ini.
- Perubahan skema (tambah/ubah kolom atau tabel) **wajib lewat migration baru**, tidak boleh mengedit migration lama yang sudah pernah dijalankan di lingkungan manapun.
- Penamaan `species_code` dan `item_code` harus UPPER_SNAKE_CASE agar konsisten dengan kontrak API `predicted_species_code` dari Python service (lihat `architecture.md` §4.2).
