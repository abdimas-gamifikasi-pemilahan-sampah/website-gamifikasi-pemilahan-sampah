# SIPS — Sistem Informasi Pemilahan Sampah

Aplikasi web manajemen setoran sampah untuk **TPS 3R Banjarsari**, Desa Banjarsari.  
Dibangun dengan **Laravel 12**, server-side rendered (Blade only), MySQL production, SQLite untuk testing.

---

## Daftar Isi

1. [Gambaran Sistem](#1-gambaran-sistem)
2. [Tech Stack](#2-tech-stack)
3. [Cara Menjalankan](#3-cara-menjalankan)
4. [Arsitektur](#4-arsitektur)
5. [Model Data & Database](#5-model-data--database)
6. [Sistem Role & Middleware](#6-sistem-role--middleware)
7. [Model Keuangan (Signed Money)](#7-model-keuangan-signed-money)
8. [Fitur & Modul](#8-fitur--modul)
9. [Routes Lengkap](#9-routes-lengkap)
10. [Services & Helpers](#10-services--helpers)
11. [Import Data](#11-import-data)
12. [Ekspor Laporan](#12-ekspor-laporan)
13. [Tarif & Pengaturan Sistem](#13-tarif--pengaturan-sistem)
14. [Leaderboard & Ranking](#14-leaderboard--ranking)
15. [Testing](#15-testing)
16. [Seeders & Data Awal](#16-seeders--data-awal)

---

## 1. Gambaran Sistem

SIPS mencatat setiap **setoran sampah** dari warga ke TPS 3R. Setiap setoran bisa mencakup **beberapa warga sekaligus** (multi-penyetor per pengangkutan). Sistem menghitung nilai finansial otomatis, menghasilkan leaderboard warga aktif, dan mengekspor laporan dalam format Excel yang sudah dipakai petugas TPS sebelumnya.

**Dua alur utama:**

- **Dipilah** — warga memilah sampah → TPS *membayar* nilai sampah ke warga (pengeluaran TPS)
- **Tidak Dipilah** — warga tidak memilah → warga *membayar* iuran ke TPS (pemasukan TPS)

---

## 2. Tech Stack

| Komponen | Detail |
|---|---|
| Framework | Laravel 12, PHP 8.2+ |
| Frontend | Blade SSR, Bootstrap 5, Remix Icon, Vite |
| Database (prod) | MySQL 8+ — database `sips` |
| Database (test) | SQLite (in-memory) |
| Queue | Database driver (via `php artisan queue:listen`) |
| Excel | `phpoffice/phpspreadsheet ^5.7` |
| Dev tools | Laravel Pail (log viewer), Laravel Pint (code style), PHPUnit 11 |
| API tambahan | `routes/api.php` — JSON API, dikonsumsi Postman / mobile app (belum live) |

---

## 3. Cara Menjalankan

```bash
# Clone dan install
composer install
npm install
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate:fresh
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=PengaturanSistemSeeder
# (opsional) data dummy lengkap:
php artisan db:seed --class=WargaSeeder
php artisan db:seed --class=TarifSeeder
php artisan db:seed --class=SetoranSeeder
php artisan db:seed --class=SinonimSampahSeeder

# Jalankan semua proses sekaligus (server + queue + log + vite)
composer run dev

# Atau manual:
php artisan serve
npm run dev
php artisan queue:listen --tries=1
php artisan pail --timeout=0
```

### Akun uji coba (setelah UserSeeder)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@sips.test | password123 |
| Petugas | petugas@sips.test | password123 |

### Perintah lain

```bash
composer test                                    # jalankan semua test
php artisan test --filter NamaTest               # test spesifik
./vendor/bin/pint                                # format kode
php artisan migrate:status                       # cek status migrasi
```

---

## 4. Arsitektur

### Rendering

**Blade SSR only** — tidak ada SPA, tidak ada Inertia, tidak ada API call dari browser.  
`routes/api.php` ada tapi terpisah, untuk konsumsi eksternal saja.

### Layout

Semua halaman terautentikasi extends `partials.layouts.master`.  
Dua yield penting:
- `@yield('content')` — body halaman
- `@yield('js')` — script per halaman (setelah Bootstrap/vendor tersedia)

Semua view SIPS ada di `resources/views/sips/`.  
Halaman publik di `resources/views/public/`.

### Struktur Direktori Kunci

```
app/
├── Http/
│   ├── Controllers/          # satu controller per modul
│   │   └── Api/              # API controllers (eksternal)
│   └── Middleware/
│       └── EnsureUserHasRole.php   # guard role admin/petugas
├── Models/                   # Eloquent models
├── Services/                 # business logic tebal
│   ├── RankingService.php
│   ├── TarifPricingService.php
│   ├── WargaMatcher.php      # fuzzy match nama warga dari Excel
│   ├── WasteMatcher.php      # fuzzy match nama sampah ke TarifItem
│   └── OcrExcelParser.php    # parse Excel hasil OCR/AI
├── Support/
│   └── SignedMoney.php       # konvensi signed decimal untuk nilai setoran
└── Helpers/
    ├── RwParser.php           # normalisasi string RW dari berbagai format
    └── TanggalParser.php      # parse tanggal dari serial Excel / dd/mm/yyyy / teks Indonesia
```

---

## 5. Model Data & Database

### Entity Relationship

```
Warga ──────────────────────> Setoran (warga_id nullable)
                               │
                               ├──hasMany──> ItemSetoran
                               │              ├── warga_id (nullable, untuk multi-penyetor)
                               │              ├── tarif_item_id → TarifItem
                               │              └── riwayat_tarif_id → RiwayatTarif
                               │
                               └──hasOne───> Pembayaran

TarifItem ──hasMany──> RiwayatTarif   (history harga dengan date range)

User (petugas/admin) ──> Setoran (sebagai petugas_id)

SinonimSampah ──belongsTo──> TarifItem   (alias nama sampah untuk matching OCR)

SnapshotPeringkatBulanan   (hasil komputasi RankingService, tidak auto-update)
ImportLog                  (log setiap import warga/setoran)
PengaturanSistem           (key-value settings)
```

### Tabel & Model

| Tabel | Model | Keterangan |
|---|---|---|
| `users` | `User` | Admin & petugas. Field: `role` (admin/petugas), `is_active` |
| `warga` | `Warga` | Anggota TPS. Field: nama, no_kk (nullable), alamat, rt, rw, dusun, no_hp, tanggal_terdaftar, status_keanggotaan |
| `setoran` | `Setoran` | Satu baris = satu pengangkutan. Field: warga_id (nullable), petugas_id, tanggal_setoran, nilai (signed), total_kg, area_rw, mode, is_selesai |
| `item_setoran` | `ItemSetoran` | Satu baris = satu item sampah dari satu penyetor. Field: setoran_id, warga_id (nullable), tipe_sampah, status_pemilahan, berat_kg, harga_per_kg_saat_itu, subtotal, catatan_item |
| `pembayaran` | `Pembayaran` | 1:1 dengan setoran, mencatat transaksi keuangan |
| `tarif_items` | `TarifItem` | Jenis sampah (organik/anorganik). status: aktif/nonaktif |
| `riwayat_tarif` | `RiwayatTarif` | History harga per TarifItem dengan tanggal_mulai & tanggal_akhir |
| `sinonim_sampah` | `SinonimSampah` | Alias nama sampah → TarifItem (untuk OCR matching) |
| `snapshot_peringkat_bulanan` | `SnapshotPeringkatBulanan` | Snapshot hasil RankingService per bulan |
| `import_log` | `ImportLog` | Log import: warga/setoran_perolehan/setoran_rivan/setoran_detail |
| `pengaturan_sistem` | `PengaturanSistem` | Key-value: tarif_flat_per_kg, nilai_dipilah_per_kg, chatgpt_ocr_link, dll |

### Field Penting `setoran`

| Field | Tipe | Keterangan |
|---|---|---|
| `nilai` | decimal signed | Positif = TPS bayar ke warga; Negatif = warga bayar ke TPS; 0 = tidak ada transaksi |
| `mode` | string | `penyetor` (default), `detail`, `legacy`, `agregat` |
| `sumber_input` | string | `manual`, `import`, `ocr` |
| `is_selesai` | boolean | Draft (false) atau Selesai (true) |
| `warga_id` | FK nullable | Null untuk setoran multi-penyetor (data warga ada di item_setoran) |
| `area_rw` | string | RW agregat dari semua penyetor, format: "01,02,06" |

### Field Penting `item_setoran`

| Field | Tipe | Keterangan |
|---|---|---|
| `status_pemilahan` | enum | `dipilah` atau `tidak_dipilah` |
| `harga_per_kg_saat_itu` | decimal | Snapshot harga saat setoran dibuat (immutable) |
| `catatan_item` | string nullable | Diisi "flat rate" jika dari OCR belum dapat TarifItem |
| `warga_id` | FK nullable | Identitas penyetor dalam mode multi-penyetor |

---

## 6. Sistem Role & Middleware

Middleware `EnsureUserHasRole` terdaftar sebagai alias `role`.

```php
// Admin only
Route::middleware('role:admin')->group(...)

// Admin atau Petugas
Route::middleware('role:admin,petugas')->group(...)
```

Helper di model `User`:
```php
$user->isAdmin();    // role === 'admin'
$user->isPetugas();  // role === 'petugas'
```

**Perbedaan akses Admin vs Petugas:**

| Fitur | Admin | Petugas |
|---|---|---|
| Data Warga (CRUD) | ✅ | ✅ (read + toggle status) |
| Setoran (buat & lihat) | ✅ | ✅ |
| Import Setoran Excel | ✅ | ✅ |
| Import OCR/AI | ✅ | ✅ |
| Pembayaran | ✅ | ✅ |
| Leaderboard | ✅ | ✅ |
| Analitik | ✅ | ❌ |
| Import Warga CSV | ✅ | ❌ |
| Tarif (CRUD) | ✅ | ❌ |
| Ekspor Laporan | ✅ | ❌ |
| Sinonim Sampah | ✅ | ❌ |
| Manajemen Petugas | ✅ | ❌ |
| Pengaturan Sistem | ✅ | ❌ |

---

## 7. Model Keuangan (Signed Money)

**`setoran.nilai` adalah desimal bertanda (signed decimal):**

```
nilai > 0  →  TPS membayar ke warga  (dipilah, sampah punya nilai jual)
nilai < 0  →  Warga membayar ke TPS  (tidak dipilah, dikenai iuran)
nilai = 0  →  Tidak ada transaksi keuangan
```

**`App\Support\SignedMoney`** mengonversi perspektif untuk tampilan dari sisi TPS/akuntansi:

```php
// nilai > 0 → "Pengeluaran" (TPS keluar uang)
// nilai < 0 → "Pemasukan"   (TPS dapat uang)

SignedMoney::describe($nilai)        // → "Pengeluaran Rp 50.000" atau "Pemasukan Rp 10.000"
SignedMoney::formatSigned($nilai)    // → "+Rp 50.000" atau "-Rp 10.000"
SignedMoney::formatCurrency($nilai)  // → "Rp 50.000" (tanpa tanda, nilai absolut)
```

**Dua tarif terpisah di `pengaturan_sistem`:**

| Kunci | Keterangan |
|---|---|
| `tarif_flat_per_kg` | Iuran yang dikenakan saat warga tidak dipilah (Rp/KK) |
| `nilai_dipilah_per_kg` | Nilai yang dibayarkan ke warga saat dipilah (Rp/kg) |

Keduanya dibaca via `PengaturanSistem::get('key', $default)` atau `::getFloat('key')`.  
**Wajib di-seed** via `PengaturanSistemSeeder` sebelum form setoran bisa bekerja.

---

## 8. Fitur & Modul

### 8.1 Dashboard (`/sips/dashboard`)

Kartu statistik bulan berjalan: total warga, total berat setoran, total nilai dipilah, total iuran masuk.  
Daftar warga yang aktif menyetor bulan ini.  
Akses: Admin & Petugas.

### 8.2 Data Warga (`/sips/warga`)

CRUD lengkap. Filter: pencarian teks (nama/KK/dusun), filter RW, filter status keanggotaan, tombol cepat "Belum Setor Bulan Ini".  
`quickStore` — endpoint POST untuk tambah warga tanpa reload halaman (dipakai dari form setoran inline).  
Akses: Admin saja.

### 8.3 Setoran Sampah (`/sips/setoran`)

**Mode input setoran** (field `mode` di POST):

| Mode | Deskripsi |
|---|---|
| `penyetor` | Default. Form multi-card: satu card per warga penyetor, masing-masing punya item sendiri |
| `detail` | Baris flat per item sampah |
| `legacy` | Backward compat form lama |

Flow `SetoranController::store()`:
1. Validasi POST
2. Dispatch ke `storePenyetor()` / `storeDetail()` / `storeLegacy()`
3. `storePenyetor()` membuat satu `Setoran` (agregat), lalu per penyetor membuat banyak `ItemSetoran`
4. `setoran.nilai` = sum dari semua `item_setoran.subtotal`

Setoran punya status `is_selesai` (draft/selesai) yang bisa di-toggle via PATCH.  
`kwitansi` — endpoint cetak kwitansi (GET `/sips/setoran/{id}/kwitansi`).

Akses: Admin & Petugas.

### 8.4 Pembayaran (`/sips/pembayaran`)

Riwayat transaksi keuangan. Filter bulan/tahun dan jenis.  
`PembayaranController::store()` — POST `/sips/setoran/{id}/bayar` untuk menandai setoran sudah dibayar.  
Akses: Admin & Petugas.

### 8.5 Tarif (`/sips/tarif`)

`TarifItem` = jenis sampah (misal: Botol Plastik, Kardus, Organik).  
`RiwayatTarif` = history harga per item dengan `tanggal_mulai` dan `tanggal_akhir`.  
`TarifItem::tarifAktif(?string $tanggal)` — cari harga yang berlaku pada tanggal tertentu.  
Akses: Admin saja.

### 8.6 Leaderboard (`/sips/leaderboard`)

Papan peringkat warga berdasarkan berat sampah. Filter: bulan, tahun, RW.  
Data dibaca **langsung dari `item_setoran` JOIN** (bukan dari snapshot).  
Snapshot (`RankingService::computeMonth()`) dibuat manual, tidak dijadwalkan otomatis.  
Akses: Admin & Petugas. Versi publik: `/leaderboard` (tanpa login).

### 8.7 Analitik (`/sips/analitik`)

Grafik tren, perbandingan dipilah vs tidak dipilah, statistik per RW.  
Filter periode dan RW.  
Akses: Admin saja.

### 8.8 Ekspor Laporan (`/sips/ekspor`)

Unduh Excel via `EksporController::download()`. Format: `perolehan`, `rivan`, `detail`.  
Menggunakan PhpSpreadsheet dengan streaming (`response()->streamDownload(...)`).  
Akses: Admin saja.

### 8.9 Sinonim Sampah (`/sips/sinonim`)

Alias nama sampah → TarifItem. Digunakan `WasteMatcher` saat import OCR.  
Contoh: "botol bening" → TarifItem "Botol PET".  
Admin bisa tambah/edit/hapus sinonim.

### 8.10 Manajemen Petugas (`/sips/petugas`)

Admin bisa tambah akun petugas, edit, toggle status aktif/nonaktif, hapus.  
Akses: Admin saja.

### 8.11 Halaman Publik

| URL | Keterangan |
|---|---|
| `/` | Landing page TPS 3R Banjarsari |
| `/leaderboard` | Leaderboard publik (tanpa login), mobile-friendly |

---

## 9. Routes Lengkap

### Publik (tanpa auth)

| Method | URI | Nama | Handler |
|---|---|---|---|
| GET | `/` | `landing` | `public.landing` view |
| GET | `/leaderboard` | `public.leaderboard` | `LeaderboardController@index` |
| GET | `/login` | `login` | `AuthController@showLoginForm` |
| POST | `/login` | `login.attempt` | `AuthController@login` |

### Semua pengguna terautentikasi

| Method | URI | Nama |
|---|---|---|
| POST | `/logout` | `logout` |
| GET | `/sips/dashboard` | `sips.dashboard` |
| GET | `/sips/leaderboard` | `sips.leaderboard` |
| GET | `/sips/analitik` | `sips.analitik.index` |

### Admin only (`role:admin`)

| Method | URI | Nama Route |
|---|---|---|
| GET/POST | `/sips/warga` | `sips.warga.index` / `sips.warga.store` |
| GET/POST | `/sips/warga/create` | `sips.warga.create` |
| POST | `/sips/warga/quick-store` | `sips.warga.quick-store` |
| GET/PUT | `/sips/warga/{id}/edit` | `sips.warga.edit` / `sips.warga.update` |
| PATCH | `/sips/warga/{id}/status` | `sips.warga.status` |
| DELETE | `/sips/warga/{id}` | `sips.warga.destroy` |
| GET/POST | `/sips/tarif` & sub-routes | `sips.tarif.*` |
| GET | `/sips/ekspor` | `sips.ekspor.index` |
| GET | `/sips/ekspor/download` | `sips.ekspor.download` |
| GET/POST | `/sips/import` (warga CSV) | `sips.import.*` |
| GET/POST | `/sips/sinonim` & CRUD | `sips.sinonim.*` |
| GET/POST | `/sips/petugas` & CRUD | `sips.petugas.*` |
| GET/POST | `/sips/import/ocr/flat-rate-review` | `sips.import.ocr.flat-rate-review` |
| POST | `/sips/import/ocr/assign-tarif` | `sips.import.ocr.assign-tarif` |
| POST | `/sips/import/ocr/save-ai-links` | `sips.import.ocr.save-ai-links` |
| PATCH | `/sips/pengaturan` | `sips.pengaturan.update` |

### Admin & Petugas (`role:admin,petugas`)

| Method | URI | Nama Route |
|---|---|---|
| GET/POST | `/sips/setoran` | `sips.setoran.index` / `sips.setoran.store` |
| GET | `/sips/setoran/create` | `sips.setoran.create` |
| GET | `/sips/setoran/{id}` | `sips.setoran.show` |
| GET | `/sips/setoran/{id}/kwitansi` | `sips.setoran.kwitansi` |
| PATCH | `/sips/setoran/{id}/selesai` | `sips.setoran.selesai` |
| POST | `/sips/setoran/{id}/bayar` | `sips.pembayaran.store` |
| GET | `/sips/pembayaran` | `sips.pembayaran.index` |
| GET/POST | `/sips/import/setoran` | `sips.import.setoran.*` |
| GET | `/sips/import/setoran/template/{format}` | `sips.import.setoran.template` |
| GET/POST | `/sips/import/ocr` | `sips.import.ocr.*` |
| GET | `/sips/import/ocr/panduan` | `sips.import.ocr.panduan` |

---

## 10. Services & Helpers

### `App\Services\RankingService`

```php
app(RankingService::class)->computeMonth(Carbon::parse('2026-07-01'));
// Menghitung dan menyimpan snapshot peringkat ke snapshot_peringkat_bulanan
// Tidak dijadwalkan — harus dipanggil manual (dari controller atau Artisan)
```

### `App\Services\TarifPricingService`

Menghitung harga item setoran berdasarkan `RiwayatTarif` yang berlaku pada tanggal setoran.

### `App\Services\WargaMatcher`

Fuzzy-match nama warga dari Excel import ke data `warga` di database.  
Digunakan `ImportController` dan `ImportSetoranController`.

### `App\Services\WasteMatcher`

Fuzzy-match nama sampah dari Excel/OCR ke `TarifItem` aktif, dengan fallback ke `SinonimSampah`.  
Threshold fuzzy minimum: 75% similarity.  
Keyword list built-in untuk organik/anorganik.

### `App\Services\OcrExcelParser`

Parse file Excel yang dihasilkan dari AI/OCR eksternal (ChatGPT Custom GPT).  
Kolom tidak terstruktur standar — parser mendeteksi kolom secara heuristik.

### `App\Helpers\RwParser`

```php
RwParser::normalize('06 & 01')   // → "01,06"
RwParser::normalize('RW 6')       // → "06"
RwParser::normalize('5, 1, 6')    // → "01,05,06"
```

Gunakan selalu saat menyimpan atau membandingkan `area_rw`.

### `App\Helpers\TanggalParser`

```php
TanggalParser::parse(45000)                    // Excel serial integer → Carbon
TanggalParser::parse('15/07/2026')             // dd/mm/yyyy → Carbon
TanggalParser::parse('Senin, 9 Maret 2026')   // teks Indonesia → Carbon
// Returns Carbon|null
```

### `App\Support\SignedMoney`

Lihat [Bagian 7](#7-model-keuangan-signed-money). Method utama:
- `::describe($nilai)` — label Pengeluaran/Pemasukan
- `::formatSigned($nilai)` — tanda +/−
- `::formatCurrency($nilai)` — nilai absolut
- `::positiveTotal($amounts)` — sum hanya nilai positif
- `::negativeTotal($amounts)` — sum nilai negatif (absolut)

---

## 11. Import Data

### Import Warga (CSV) — Admin only

Route: `GET/POST /sips/import`  
Flow: Upload CSV → preview (session) → konfirmasi → simpan  
Template bisa diunduh via `GET /sips/import/template`.

**Kolom CSV:** `nama, no_kk, alamat, dusun, rt, rw, no_hp, tanggal_terdaftar, status_keanggotaan`

### Import Setoran (Excel) — Admin & Petugas

Route: `GET/POST /sips/import/setoran`  
Format tersedia: `perolehan`, `rivan`, `detail`, `ocr`  
Template: `GET /sips/import/setoran/template/{format}`

Flow: Upload Excel → `preview()` → simpan di session → `confirm()` → simpan ke DB  
`ImportLog` mencatat setiap import dengan jenis `setoran_perolehan` / `setoran_rivan` / `setoran_detail`.

| Format | Struktur |
|---|---|
| `perolehan` | Rekap bulanan — satu baris per warga per bulan |
| `rivan` | Rekap harian per pengangkutan — satu baris per setoran |
| `detail` | Flat per item, lengkap per penyetor |

### Import OCR/AI — Admin & Petugas

Route: `GET/POST /sips/import/ocr`  
Flow: Foto nota/rekap → upload ke ChatGPT Custom GPT → GPT output Excel → upload ke SIPS  
`ImportOcrController` memproses dengan `OcrExcelParser` + `WargaMatcher` + `WasteMatcher`.

**Flat-rate review** (`/sips/import/ocr/flat-rate-review` — Admin only):  
Item dari OCR yang belum dapat `tarif_item_id` (ditandai `catatan_item LIKE '%flat rate%'`) bisa di-assign manual ke TarifItem yang tepat.

**Simpan link AI:**  
Admin bisa simpan URL ChatGPT Custom GPT ke `pengaturan_sistem.chatgpt_ocr_link` agar tersedia di halaman panduan OCR.

---

## 12. Ekspor Laporan

Controller: `EksporController`  
Route: `GET /sips/ekspor/download?format=perolehan&bulan=7&tahun=2026`

| Format | Deskripsi |
|---|---|
| `perolehan` | Rekap bulanan per warga — kolom per jenis sampah |
| `rivan` | Rekap harian per pengangkutan |
| `detail` | Semua item per penyetor, lengkap untuk audit |

Semua format menggunakan streaming download via PhpSpreadsheet:

```php
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
return response()->streamDownload(
    fn() => $writer->save('php://output'),
    'laporan.xlsx',
    ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
);
```

`ImportSetoranController::downloadTemplate()` berisi contoh lengkap membangun ketiga format — jadikan referensi saat membuat ekspor serupa.

---

## 13. Tarif & Pengaturan Sistem

### PengaturanSistem

Key-value store di tabel `pengaturan_sistem`:

```php
PengaturanSistem::get('tarif_flat_per_kg', 0)     // → string atau default
PengaturanSistem::getFloat('nilai_dipilah_per_kg') // → float
PengaturanSistem::set('kunci', 'nilai', 'deskripsi opsional')
```

**Kunci yang dipakai sistem:**

| Kunci | Deskripsi |
|---|---|
| `tarif_flat_per_kg` | Iuran flat tidak dipilah (Rp/KK) |
| `nilai_dipilah_per_kg` | Nilai dipilah (Rp/kg) |
| `chatgpt_ocr_link` | URL ChatGPT Custom GPT untuk import OCR |

Seed via `PengaturanSistemSeeder` — **wajib** sebelum form setoran bisa berjalan (form create akan tampilkan Rp 0/kg tanpa ini).

### TarifItem & RiwayatTarif

Harga per jenis sampah bisa berubah seiring waktu. `RiwayatTarif` menyimpan range tanggal berlaku.  
`TarifItem::tarifAktif($tanggal)` mencari harga yang berlaku pada tanggal tertentu.  
Saat item setoran disimpan, `harga_per_kg_saat_itu` di-snapshot — **tidak berubah kalau tarif diupdate kemudian**.

---

## 14. Leaderboard & Ranking

### Live Query (dari DB)

`LeaderboardController` membaca langsung dari `item_setoran JOIN setoran` — bukan dari snapshot.  
Filter: bulan, tahun, RW.  
Halaman publik (`/leaderboard`) menggunakan controller yang sama, tanpa auth middleware.

### Snapshot (RankingService)

```php
app(RankingService::class)->computeMonth(Carbon::parse('2026-07-01'));
// Menyimpan hasil ke snapshot_peringkat_bulanan
```

**Tidak ada schedule** — harus dipanggil manual dari admin UI atau Artisan command custom.

---

## 15. Testing

Berjalan di **SQLite** (in-memory). Beberapa kolom MySQL-specific (tipe ALTER) di-skip via guard:

```php
if (DB::getDriverName() !== 'mysql') { /* no-op on SQLite */ }
```

```bash
composer test                                         # semua test
php artisan test --filter SetoranTest                 # filter class
php artisan test --filter "test_status_pembayaran"    # filter method
```

Test ada di `tests/Feature/`:
- `WargaValidationTest.php` — validasi form warga

---

## 16. Seeders & Data Awal

| Seeder | Isi |
|---|---|
| `UserSeeder` | 1 admin + 1 petugas (akun test) |
| `PengaturanSistemSeeder` | Tarif default & pengaturan sistem awal — **wajib** |
| `WargaSeeder` | ~20 warga dummy berbagai RW/dusun |
| `TarifSeeder` | Item tarif (Botol PET, Kardus, Organik, dll) + harga awal |
| `SetoranSeeder` | Setoran dummy beberapa bulan terakhir |
| `SinonimSampahSeeder` | Alias nama sampah umum untuk OCR matching |
| `DummyLaporanSeeder` | Data laporan historis untuk testing ekspor |

**Urutan minimum untuk dev:**

```bash
php artisan migrate:fresh
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=PengaturanSistemSeeder
```

**Data lengkap (untuk demo/staging):**

```bash
php artisan db:seed
# DatabaseSeeder memanggil semua seeder secara berurutan
```

---

## Catatan Migrasi

- Tiga migrasi `2026_06_02_000001/2/3` harus berjalan berurutan (extend setoran v5, nullable warga_id, extend item_setoran v5).
- Migrasi yang alter kolom MySQL menggunakan `DB::statement()` dan di-guard `if (DB::getDriverName() !== 'mysql')` — no-op di SQLite.
- **Jangan modifikasi migrasi yang sudah di-deploy.** Tambah migrasi baru untuk perubahan kolom.

---

*SIPS dikembangkan untuk program abdimas pemilahan sampah TPS 3R Banjarsari.*  
*Laravel 12 · PHP 8.2 · MySQL · Bootstrap 5 · PhpSpreadsheet*
