# SIPS — Sistem Informasi Pemilahan Sampah

Platform berbasis web untuk mendukung program pengelolaan sampah berbasis komunitas di tingkat RW/Dusun, Desa Banjarsari.

---

## Tentang Sistem

SIPS mencatat setoran sampah harian warga, menghitung nilai pembayaran otomatis berdasarkan tarif, mencatat konfirmasi pembayaran, dan menampilkan peringkat partisipasi per bulan secara transparan.

**SIPS bukan sistem keuangan.** Pembayaran tetap dilakukan tunai di luar sistem; sistem hanya mencatat bahwa pembayaran telah terjadi.

### Pengguna Sistem

| Aktor | Akses |
|---|---|
| **Admin Desa** | Akses penuh: master warga, tarif, semua setoran, pembayaran, dashboard, peringkat |
| **Petugas Bank Sampah** | Input setoran, cari warga, catat pembayaran, rekap setoran hari ini |
| **Publik (Ketua RW / Warga)** | Halaman leaderboard & dashboard publik — tanpa login |

> Warga **tidak memiliki akun login**. Semua pencatatan dilakukan oleh petugas.

---

## Tech Stack

| Layer | Tech |
|---|---|
| Backend | Laravel 12 · PHP 8.2 |
| Database | SQLite (dev) → MySQL/PostgreSQL (prod) |
| Frontend Publik | Blade + **Landrick v5** template (Shreethemes) |
| Frontend Admin | Blade + **Fabkin** admin template (Pixeleyez) |
| Auth | Laravel built-in (session-based) |
| Assets | Bootstrap 5 · Feather Icons · Unicons · Landrick JS stack |

### Struktur Layout Penting

```
resources/views/
├── layouts/
│   ├── landrick/main.blade.php   ← layout halaman publik (landing, leaderboard)
│   └── partials/layouts/         ← layout admin Fabkin
├── public/
│   ├── landing.blade.php
│   └── leaderboard/index.blade.php
└── includes/landrick-sips/       ← komponen publik (navbar, footer, dll)
```

```
public/assets/
├── css/style.css                 ← Landrick stylesheet (JANGAN timpa dengan Fabkin)
├── js/landrick-app.js            ← Landrick app.js (terpisah dari Fabkin app.js)
└── js/app.js                     ← Fabkin admin JS (JANGAN timpa)
```

---

## Modul Sistem

| Modul | Nama | Owner Dev |
|---|---|---|
| 1 | Data Master Warga | **Dev A** |
| 2 | Pendataan Setoran Sampah | **Dev B** |
| 3 | Manajemen Tarif | **Dev A** |
| 4 | Pencatatan Pembayaran | **Dev B** |
| 5 | Dashboard & Sistem Peringkat | **Dev C** |
| 6 | Import Data & Tools | **Dev C** |

---

## Pembagian Tugas Tim (Vertical Slice)

Setiap dev memiliki tanggung jawab **end-to-end** (Database + Backend + Frontend) untuk slice fiturnya. Tujuan: tiap dev bekerja paralel tanpa saling memblokir.

### Dev A — Master Data (Modul 1 + 3)

**Owns:** Data referensi sistem — warga dan tarif. Fondasi yang dipakai Dev B dan C.

Tabel database: `warga`, `tarif_items`, `riwayat_tarif`, `users`

Halaman yang dibuat:
- `/admin/warga` — kelola daftar warga
- `/admin/tarif` — kelola item tarif & riwayat harga
- `/login` — halaman login admin/petugas

**Key API yang Dev B & C konsumsi dari Dev A:**
- `GET /api/warga?search=&rw=` — untuk search warga di form setoran
- `GET /api/tarif/lookup?item_id=&tanggal=` — lookup harga berlaku pada tanggal tertentu
- `GET /api/tarif/items?tipe=` — dropdown item tarif

### Dev B — Operasional (Modul 2 + 4)

**Owns:** Alur transaksi inti — input setoran multi-item dan konfirmasi pembayaran.

Tabel database: `setoran`, `item_setoran`, `pembayaran`

Halaman yang dibuat:
- `/petugas/setoran/baru` — form input setoran multi-item (halaman inti, mobile-first)
- `/petugas/setoran/:id/kwitansi` — kwitansi setelah submit
- `/petugas/setoran/:id/bayar` — konfirmasi pembayaran
- `/admin/setoran` — monitoring semua setoran
- `/admin/pembayaran` — laporan pembayaran

**Catatan penting:** Field `harga_per_kg_saat_itu` di tabel `item_setoran` adalah **snapshot** harga saat transaksi — bukan FK live. Panggil `GET /api/tarif/lookup` dari Dev A untuk dapat nilai ini, lalu simpan permanen.

### Dev C — Analytics & Tools (Modul 5 + 6)

**Owns:** Dashboard agregasi, sistem peringkat, dan import Excel. Tidak ada modul lain yang bergantung pada Dev C.

Tabel database: `snapshot_peringkat_bulanan`, `import_log`, `pengaturan_sistem`

Halaman yang dibuat:
- `/leaderboard` — publik, peringkat warga & RW (sudah ada, perlu disambungkan ke data real)
- `/` (landing) — halaman publik (sudah ada)
- `/dashboard` — publik, ringkasan data per RW
- `/admin/import` — upload Excel, preview, konfirmasi

**Formula poin individu:**
```
Poin = (Total kg dipilah × 10) + (Bulan berturut menyetor × 50) + (% dipilah dari total × 2)
```

---

## Aturan Bisnis Penting

| Kode | Aturan |
|---|---|
| BR-01 | Tarif historis **tidak berubah retroaktif** — setoran lama pakai harga lama |
| BR-02 | Berat minimum setoran **0.1 kg** — di bawah ini sistem menolak |
| BR-03 | **Satu jenis per setoran** — organik + anorganik = 2 setoran terpisah |
| BR-04 | Setoran bisa diedit Petugas dalam **24 jam**; setelah itu hanya Admin |
| BR-05 | Warga **tidak menyetor 30 hari** → otomatis status Tidak Aktif |
| BR-06 | Peringkat resmi ditetapkan **akhir bulan** (bukan real-time) |
| BR-07 | Jumlah pembayaran dicatat boleh **berbeda** dari nilai sistem (pembulatan dll) |
| BR-08 | Satu setoran hanya bisa dibayar **sekali** — mencegah pembayaran ganda |

---

## Setup Lokal

```bash
git clone <repo-url>
cd pemilhan-sampah

composer install
cp .env.example .env
php artisan key:generate

# Database (SQLite default, atau ubah ke MySQL di .env)
php artisan migrate
php artisan db:seed

php artisan serve
```

**Catatan asset:** Jangan jalankan `npm run dev` yang menimpa `public/assets/js/app.js` — file ini adalah Fabkin admin JS. Landrick JS sudah tersedia di `public/assets/js/landrick-app.js`.

---

## Konvensi Git

```
main        ← production-ready
develop     ← integrasi semua dev
feature/*   ← branch per fitur (contoh: feature/form-setoran)
```

- Pull dari `develop` setiap pagi sebelum mulai kerja
- Jangan edit kode slice orang lain — buat issue atau minta langsung ke owner-nya
- API contract di-freeze setelah minggu pertama; perubahan harus disepakati bertiga

---

## Timeline Kasar

| Minggu | Target |
|---|---|
| W1 | Setup repo, sepakati ER diagram & API contract, buat shared components |
| W2 | Dev A: API warga & tarif · Dev B: Skeleton form setoran (mock API) · Dev C: Komponen chart |
| W3 | Dev B: Integrasi API Dev A · Dev C: Dashboard live data |
| W4 | Dev B: Pembayaran & kwitansi · Dev C: Cron peringkat & import Excel |
| W5 | Polish UI, optimasi performa |
| W6 | UAT bersama, bug bash, deployment |

---

## Dokumen Referensi

| File | Isi |
|---|---|
| `SIPS_Deskripsi_Sistem_v4_2.docx` | Spesifikasi lengkap sistem, alur kerja, entitas data, business rules |
| `SIPS_Pembagian_Tugas_3_Dev.docx` | Pembagian tugas per dev, API contract, schema database, definition of done |
