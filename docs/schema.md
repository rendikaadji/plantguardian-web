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

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigIncrements | PK |
| name | string | Nama pengguna |
| email | string, unique | |
| password | string (hashed) | |
| role | enum(`ranger`,`viewer`) | Menentukan hak akses & flow (lihat `prd.md` §3) |
| exp | integer, default 0 | Total EXP saat ini |
| coin | integer, default 0 | Total Coin saat ini |
| created_at / updated_at | timestamp | |

**Catatan:** `exp` dan `coin` disimpan sebagai kolom cache di `users` untuk kecepatan baca, tapi **riwayat perubahan wajib tetap dicatat** di `exp_logs` dan `coin_transactions` (jangan hanya update kolom ini tanpa log — untuk audit dan mencegah exploit).

## 3. Tabel `plant_species` (Katalog — diisi oleh Ranger)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigIncrements | PK |
| species_code | string, unique | Kode unik dipakai untuk mencocokkan hasil AI (`predicted_species_code`) |
| common_name | string | Nama umum (contoh: "Pohon Mangga") |
| scientific_name | string, nullable | Nama ilmiah |
| description | text | Deskripsi/edukasi tentang tumbuhan |
| conservation_status | string, nullable | Status konservasi (opsional) |
| reference_image_path | string, nullable | Foto referensi diinput Ranger |
| created_by | foreign key → users.id | Ranger yang menginput |
| created_at / updated_at | timestamp | |

## 4. Tabel `plant_sightings` (Temuan — hasil scan Viewer)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigIncrements | PK |
| user_id | foreign key → users.id | Viewer yang melakukan scan |
| plant_species_id | foreign key → plant_species.id, nullable | Hasil pencocokan AI; null jika gagal dikenali |
| photo_path | string | Foto hasil scan milik Viewer |
| confidence_score | float, nullable | Skor keyakinan dari AI service |
| latitude / longitude | decimal, nullable | Lokasi saat scan (opsional, untuk fitur Peta) |
| saved_to_gallery | boolean, default true | Apakah disimpan ke Penyimpanan/Galeri |
| created_at / updated_at | timestamp | |

**Scoping wajib:** semua query `plant_sightings` untuk ditampilkan ke user harus difilter `where('user_id', auth()->id())`.

## 5. Tabel `garden_plots` (Lahan Tanam — MiniGame)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigIncrements | PK |
| user_id | foreign key → users.id | Pemilik lahan |
| slot_number | integer | Urutan slot lahan milik user (1, 2, 3, dst) |
| unlocked | boolean, default true | Apakah lahan ini sudah dibeli/terbuka |
| purchase_cost | integer, nullable | Harga beli lahan (jika berbayar Coin) |
| created_at / updated_at | timestamp | |

## 6. Tabel `plantings` (Instance Tanaman di MiniGame)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigIncrements | PK |
| garden_plot_id | foreign key → garden_plots.id | Lahan tempat tanam |
| plant_species_id | foreign key → plant_species.id, nullable | Jenis benih yang ditanam (jika minigame pakai jenis nyata dari katalog) |
| planted_at | timestamp | Waktu tanam |
| ready_at | timestamp | Waktu diperkirakan siap panen (planted_at + durasi tumbuh) |
| last_watered_at | timestamp, nullable | Waktu terakhir disiram/dirawat |
| status | enum(`growing`,`ready`,`harvested`) | Status siklus tanaman |
| harvested_at | timestamp, nullable | Waktu dipanen |
| created_at / updated_at | timestamp | |

**Business rule (lihat `GardenService`):** status berubah dari `growing` → `ready` otomatis ketika `now() >= ready_at` (dihitung saat query, bukan lewat cron wajib, tapi bisa dioptimalkan dengan scheduled job di iterasi berikutnya).

## 7. Tabel `inventory_items` (Benih, Alat, Bahan)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigIncrements | PK |
| user_id | foreign key → users.id | Pemilik inventaris |
| item_type | enum(`seed`,`tool`,`material`) | Kategori item |
| item_code | string | Kode item (contoh: `SEED_MANGGA`) |
| quantity | integer, default 0 | Jumlah dimiliki |
| created_at / updated_at | timestamp | |

## 8. Tabel `coin_transactions` (Audit Log Coin)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigIncrements | PK |
| user_id | foreign key → users.id | |
| amount | integer | Positif (dapat coin) atau negatif (belanja) |
| reason | string | Contoh: `scan_reward`, `harvest_reward`, `buy_seed`, `buy_plot` |
| reference_type / reference_id | string / bigInteger, nullable | Polymorphic ref ke sumber transaksi (misal `plant_sightings.id` atau `plantings.id`) |
| created_at | timestamp | |

## 9. Tabel `exp_logs` (Audit Log EXP)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigIncrements | PK |
| user_id | foreign key → users.id | |
| amount | integer | Selalu positif (EXP tidak dikurangi) |
| reason | string | Contoh: `scan_reward`, `harvest_reward` |
| reference_type / reference_id | string / bigInteger, nullable | |
| created_at | timestamp | |

## 10. Aturan Umum Skema

- Semua foreign key wajib `onDelete('cascade')` kecuali disebutkan lain, dan wajib didefinisikan di migration (bukan hanya di model).
- Semua tabel yang datanya milik user wajib bisa di-scope lewat `user_id` — tidak boleh ada tabel data personal tanpa kolom ini.
- Perubahan skema (tambah/ubah kolom atau tabel) **wajib lewat migration baru**, tidak boleh mengedit migration lama yang sudah pernah dijalankan di lingkungan manapun.
- Penamaan `species_code` dan `item_code` harus UPPER_SNAKE_CASE agar konsisten dengan kontrak API `predicted_species_code` dari Python service (lihat `architecture.md` §4.2).
