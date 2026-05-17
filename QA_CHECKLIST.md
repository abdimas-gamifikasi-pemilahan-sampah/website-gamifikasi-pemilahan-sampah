# SIPS — QA Testing Checklist
**Sistem Informasi Pemilahan Sampah · Desa Banjarsari**
Version: 1.0 · Tanggal: 2026-05-17

---

## Cara Mengisi Checklist
- `[ ]` = Belum diuji
- `[x]` = Lulus (PASS)
- `[!]` = Gagal (FAIL) — tulis catatan di kolom Catatan

---

## A. Environment Setup

> Lakukan langkah ini **satu kali** sebelum mulai pengujian.

### A.1 Reset Database ke Kondisi Kosong

Jalankan perintah berikut di terminal project:

```bash
php artisan migrate:fresh
php artisan db:seed --class=UserSeeder
php artisan serve
```

Ini menghasilkan database kosong (tanpa data warga, tarif, atau setoran) dan **3 akun pengguna** siap pakai.

---

## B. Akun Pengguna untuk Pengujian

| Role     | Username         | Email                  | Password    |
|----------|------------------|------------------------|-------------|
| Admin    | `admin.sips`     | `admin@sips.test`      | `password123` |
| Petugas 1| `petugas.sips`   | `petugas@sips.test`    | `password123` |
| Petugas 2| `petugas.dua`    | `petugas2@sips.test`   | `password123` |

> Gunakan email **atau** username untuk login.

---

## C. Data Master yang Perlu Dibuat Manual oleh QA

Buat data ini sendiri selama pengujian (bagian dari test case).
Gunakan data referensi berikut sebagai panduan input:

### Tarif Item (dibuat di Manajemen Tarif)

| Nama Item        | Tipe       | Harga/kg (Rp) |
|------------------|------------|---------------|
| Sisa Makanan     | organik    | 300           |
| Sisa Sayur       | organik    | 220           |
| Plastik PET      | anorganik  | 1.500         |
| Kardus           | anorganik  | 650           |
| Kaleng Aluminium | anorganik  | 2.200         |

### Warga (dibuat di Data Warga)

| Nama            | No. KK           | RT | RW | Dusun   | No. HP       |
|-----------------|------------------|----|----|---------|--------------|
| Budi Santoso    | 3301010101010001 | 01 | 01 | Melati  | 081210000001 |
| Siti Aminah     | 3301010101010002 | 02 | 01 | Melati  | 081210000002 |
| Agus Pratama    | 3301010101010003 | 01 | 02 | Anggrek | 081210000003 |

---

## 1. Autentikasi & Akses

### 1.1 Halaman Publik (tanpa login)

| ID   | Langkah                                             | Hasil yang Diharapkan                              | Catatan |
|------|-----------------------------------------------------|----------------------------------------------------|---------|
| 1.1.1 | [ ] Buka `http://127.0.0.1:8000/`                 | Halaman landing tampil tanpa error                 |         |
| 1.1.2 | [ ] Buka `http://127.0.0.1:8000/leaderboard`      | Papan peringkat publik tampil (data kosong OK)     |         |
| 1.1.3 | [ ] Buka `http://127.0.0.1:8000/sips/dashboard`   | Diarahkan ke halaman login                         |         |
| 1.1.4 | [ ] Buka `http://127.0.0.1:8000/sips/warga`       | Diarahkan ke halaman login                         |         |

### 1.2 Login

| ID   | Langkah                                                         | Hasil yang Diharapkan                              | Catatan |
|------|-----------------------------------------------------------------|----------------------------------------------------|---------|
| 1.2.1 | [ ] Login dengan email `admin@sips.test` / `password123`      | Berhasil masuk, diarahkan ke Dasbor               |         |
| 1.2.2 | [ ] Login dengan email salah, password benar                   | Pesan error "Kredensial tidak cocok"              |         |
| 1.2.3 | [ ] Login dengan password salah                                | Pesan error tampil, tidak masuk                   |         |
| 1.2.4 | [ ] Form login kosong → klik Login                             | Validasi browser/server mencegah submit            |         |
| 1.2.5 | [ ] Login sebagai Petugas (`petugas@sips.test` / `password123`)| Berhasil masuk ke Dasbor                         |         |

### 1.3 Logout

| ID   | Langkah                             | Hasil yang Diharapkan                                 | Catatan |
|------|-------------------------------------|-------------------------------------------------------|---------|
| 1.3.1 | [ ] Klik tombol logout (Admin)    | Sesi berakhir, diarahkan ke halaman login             |         |
| 1.3.2 | [ ] Setelah logout, tekan Back browser | Tidak bisa kembali ke halaman yang butuh login   |         |

---

## 2. Kontrol Akses Berdasarkan Role

> Login bergantian sebagai **Admin** dan **Petugas** untuk menguji pembatasan akses.

### 2.1 Akses Admin

| ID   | Langkah (login sebagai Admin)                              | Hasil yang Diharapkan                                | Catatan |
|------|------------------------------------------------------------|------------------------------------------------------|---------|
| 2.1.1 | [ ] Cek sidebar — apakah menu "Data Master" tampil?       | Ya, menu Data Master tampil di sidebar               |         |
| 2.1.2 | [ ] Buka `/sips/warga`                                    | Halaman Data Warga tampil                            |         |
| 2.1.3 | [ ] Buka `/sips/tarif`                                    | Halaman Manajemen Tarif tampil                       |         |
| 2.1.4 | [ ] Buka `/sips/import`                                   | Halaman Import Data Warga tampil                     |         |

### 2.2 Akses Petugas (harus dibatasi)

| ID   | Langkah (login sebagai Petugas)                            | Hasil yang Diharapkan                                | Catatan |
|------|------------------------------------------------------------|------------------------------------------------------|---------|
| 2.2.1 | [ ] Cek sidebar — apakah menu "Data Master" tampil?       | **Tidak tampil** (hanya Admin yang bisa lihat)       |         |
| 2.2.2 | [ ] Paksa buka `http://127.0.0.1:8000/sips/warga`        | Diarahkan / error 403 Forbidden                      |         |
| 2.2.3 | [ ] Paksa buka `http://127.0.0.1:8000/sips/tarif`        | Diarahkan / error 403 Forbidden                      |         |
| 2.2.4 | [ ] Paksa buka `http://127.0.0.1:8000/sips/import`       | Diarahkan / error 403 Forbidden                      |         |
| 2.2.5 | [ ] Buka `/sips/setoran` (diizinkan untuk Petugas)        | Halaman Riwayat Setoran tampil normal                |         |
| 2.2.6 | [ ] Buka `/sips/dashboard`                                | Dasbor tampil normal                                 |         |

---

## 3. Manajemen Tarif (Admin Only)

> Login sebagai **Admin**. Pastikan belum ada tarif di database (fresh DB).

### 3.1 Tambah Jenis Sampah Baru

| ID   | Langkah                                                         | Hasil yang Diharapkan                                      | Catatan |
|------|-----------------------------------------------------------------|------------------------------------------------------------|---------|
| 3.1.1 | [ ] Buka Manajemen Tarif → klik "Tambah Jenis Sampah"        | Form tambah tarif tampil                                   |         |
| 3.1.2 | [ ] Isi: Nama = `Sisa Makanan`, Tipe = `Organik` → Simpan    | Berhasil, item muncul di daftar dengan status Aktif        |         |
| 3.1.3 | [ ] Tambah: `Sisa Sayur` / `Organik` → Simpan                | Berhasil                                                   |         |
| 3.1.4 | [ ] Tambah: `Plastik PET` / `Anorganik` → Simpan             | Berhasil                                                   |         |
| 3.1.5 | [ ] Tambah: `Kardus` / `Anorganik` → Simpan                  | Berhasil                                                   |         |
| 3.1.6 | [ ] Tambah: `Kaleng Aluminium` / `Anorganik` → Simpan        | Berhasil                                                   |         |
| 3.1.7 | [ ] Coba simpan dengan nama kosong                            | Validasi gagal, tidak tersimpan                            |         |
| 3.1.8 | [ ] Coba tambah item dengan nama yang sama persis             | Validasi gagal / pesan error duplikat                      |         |

### 3.2 Tambah Harga Tarif

> Setiap jenis sampah harus punya minimal 1 harga aktif sebelum bisa dipakai di setoran.

| ID   | Langkah                                                                          | Hasil yang Diharapkan                                | Catatan |
|------|----------------------------------------------------------------------------------|------------------------------------------------------|---------|
| 3.2.1 | [ ] Klik item `Sisa Makanan` → klik "Tambah Harga"                             | Form tambah harga tampil                             |         |
| 3.2.2 | [ ] Isi: Harga = `300`, Tanggal Mulai = `2026-01-01` → Simpan                  | Harga tersimpan, tampil di riwayat tarif             |         |
| 3.2.3 | [ ] Beri harga pada semua item lainnya (lihat tabel referensi di atas)          | Semua item memiliki 1 harga aktif                    |         |
| 3.2.4 | [ ] Coba tambah harga dengan nilai 0 atau negatif                               | Validasi gagal                                       |         |
| 3.2.5 | [ ] Coba tambah harga dengan tanggal mulai di masa depan jauh                  | Tersimpan tapi belum aktif (tidak bisa dipakai setoran) |       |

### 3.3 Edit & Arsipkan Tarif

| ID   | Langkah                                                       | Hasil yang Diharapkan                                     | Catatan |
|------|---------------------------------------------------------------|-----------------------------------------------------------|---------|
| 3.3.1 | [ ] Edit nama item (jika fitur tersedia)                     | Perubahan tersimpan                                       |         |
| 3.3.2 | [ ] Arsipkan item `Kardus` (ubah status ke arsip)            | Item tidak muncul lagi di dropdown form setoran           |         |
| 3.3.3 | [ ] Aktifkan kembali `Kardus`                                | Item muncul kembali di dropdown setoran                   |         |

---

## 4. Data Warga (Admin Only)

### 4.1 Tambah Warga

| ID   | Langkah                                                                          | Hasil yang Diharapkan                               | Catatan |
|------|----------------------------------------------------------------------------------|-----------------------------------------------------|---------|
| 4.1.1 | [ ] Buka Data Warga → klik "Tambah Warga"                                      | Form tambah warga tampil                            |         |
| 4.1.2 | [ ] Isi data lengkap Budi Santoso (lihat tabel referensi) → Simpan             | Berhasil, muncul di daftar dengan status Aktif      |         |
| 4.1.3 | [ ] Tambah Siti Aminah (data referensi) → Simpan                               | Berhasil                                            |         |
| 4.1.4 | [ ] Tambah Agus Pratama (data referensi) → Simpan                              | Berhasil                                            |         |
| 4.1.5 | [ ] Coba tambah warga dengan No. KK yang sudah ada                             | Validasi gagal, pesan error duplikat                |         |
| 4.1.6 | [ ] Coba tambah warga dengan form kosong                                        | Validasi gagal                                      |         |
| 4.1.7 | [ ] Coba isi No. KK kurang dari 16 digit                                        | Validasi gagal                                      |         |

### 4.2 Edit Warga

| ID   | Langkah                                                       | Hasil yang Diharapkan                                | Catatan |
|------|---------------------------------------------------------------|------------------------------------------------------|---------|
| 4.2.1 | [ ] Edit Budi Santoso → ubah nomor HP → Simpan              | Perubahan tersimpan                                  |         |
| 4.2.2 | [ ] Edit → coba kosongkan nama → Simpan                     | Validasi gagal                                       |         |

### 4.3 Ubah Status Warga

| ID   | Langkah                                                            | Hasil yang Diharapkan                                          | Catatan |
|------|--------------------------------------------------------------------|----------------------------------------------------------------|---------|
| 4.3.1 | [ ] Non-aktifkan Agus Pratama (toggle status)                    | Status berubah ke Non-Aktif                                    |         |
| 4.3.2 | [ ] Coba buat setoran untuk Agus Pratama (non-aktif)             | Agus **tidak muncul** di dropdown warga form setoran           |         |
| 4.3.3 | [ ] Aktifkan kembali Agus Pratama                                | Status kembali Aktif, muncul di dropdown setoran               |         |

---

## 5. Import Data Warga (Admin Only)

### 5.1 Upload CSV Valid

> Buat file CSV dengan kolom: `nama,no_kk,rt,rw,dusun,no_hp`

Contoh isi file `warga_import.csv`:
```
nama,no_kk,rt,rw,dusun,no_hp
Dani Kurniawan,3301010101010019,01,02,Anggrek,081210000019
Kartika Dewi,3301010101010020,02,01,Melati,081210000020
```

| ID   | Langkah                                                              | Hasil yang Diharapkan                                       | Catatan |
|------|----------------------------------------------------------------------|-------------------------------------------------------------|---------|
| 5.1.1 | [ ] Buka Import Data Warga → upload file CSV valid                 | Halaman preview tampil, menampilkan 10 baris pertama        |         |
| 5.1.2 | [ ] Klik Konfirmasi Import                                          | Berhasil diimpor, warga baru muncul di Data Warga           |         |
| 5.1.3 | [ ] Import lagi CSV yang sama (upsert test)                        | Data tidak duplikat, berhasil update data yang ada          |         |
| 5.1.4 | [ ] Cek riwayat import di halaman Import                           | Log import muncul dengan jumlah baris dan nama file         |         |

### 5.2 Upload CSV Invalid

| ID   | Langkah                                                              | Hasil yang Diharapkan                                       | Catatan |
|------|----------------------------------------------------------------------|-------------------------------------------------------------|---------|
| 5.2.1 | [ ] Upload file bukan CSV (misal: `.xlsx` atau `.txt`)             | Error, file ditolak                                         |         |
| 5.2.2 | [ ] Upload CSV dengan kolom header yang salah                       | Error header tidak valid, preview tidak tampil              |         |
| 5.2.3 | [ ] Upload CSV kosong (hanya header)                                | Pesan informasi tidak ada data                              |         |

---

## 6. Input Setoran (Admin + Petugas)

> Pastikan sudah ada minimal **1 warga aktif** dan **1 tarif dengan harga aktif** sebelum uji ini.

### 6.1 Setoran Sampah Dipilah

| ID   | Langkah                                                                                       | Hasil yang Diharapkan                                          | Catatan |
|------|-----------------------------------------------------------------------------------------------|----------------------------------------------------------------|---------|
| 6.1.1 | [ ] Buka Input Setoran                                                                       | Form tampil dengan 1 item row otomatis                         |         |
| 6.1.2 | [ ] Pilih warga: Budi Santoso                                                                | Warga terpilih                                                 |         |
| 6.1.3 | [ ] Pada item row: pilih Status Pemilahan = **Dipilah**                                     | Dropdown "Item/Kategori" **muncul** di sebelah kanannya        |         |
| 6.1.4 | [ ] Pilih kategori: `Sisa Makanan`, isi berat: `2.5 kg`                                     | Subtotal otomatis terhitung (2.5 × 300 = **Rp 750**)          |         |
| 6.1.5 | [ ] Klik "+ Tambah Item Sampah" → pilih Dipilah → `Plastik PET` → `1.0 kg`                 | Item ke-2 tampil, estimasi total bertambah (Rp 750 + Rp 1.500 = **Rp 2.250**) |         |
| 6.1.6 | [ ] Klik Simpan Setoran                                                                      | Berhasil, diarahkan ke halaman Detail Setoran                  |         |
| 6.1.7 | [ ] Cek detail: kolom Status Pemilahan = "Dipilah", Kategori = nama item, Subtotal benar    | Semua data tampil sesuai                                       |         |

### 6.2 Setoran Sampah Tidak Dipilah

| ID   | Langkah                                                                                       | Hasil yang Diharapkan                                          | Catatan |
|------|-----------------------------------------------------------------------------------------------|----------------------------------------------------------------|---------|
| 6.2.1 | [ ] Buat setoran baru untuk Siti Aminah                                                     | Form tampil                                                    |         |
| 6.2.2 | [ ] Pilih Status Pemilahan = **Tidak Dipilah**                                              | Dropdown "Item/Kategori" **tidak muncul / tersembunyi**        |         |
| 6.2.3 | [ ] Isi berat: `3.0 kg`                                                                     | Subtotal tampil Rp 0 (tidak ada nilai untuk sampah tidak dipilah) |         |
| 6.2.4 | [ ] Cek bagian Estimasi Total di sidebar kanan                                              | Item "Tidak Dipilah 3.0 kg" tampil dengan nilai Rp 0          |         |
| 6.2.5 | [ ] Simpan Setoran                                                                           | Berhasil disimpan                                              |         |
| 6.2.6 | [ ] Cek detail: Status = "Tidak Dipilah", Kategori = "—", Harga/kg = "—", Subtotal = Rp 0  | Semua data tampil benar                                        |         |

### 6.3 Setoran Campuran (dipilah + tidak dipilah dalam 1 transaksi)

| ID   | Langkah                                                                                       | Hasil yang Diharapkan                                          | Catatan |
|------|-----------------------------------------------------------------------------------------------|----------------------------------------------------------------|---------|
| 6.3.1 | [ ] Buat setoran baru untuk Agus Pratama                                                    | Form tampil                                                    |         |
| 6.3.2 | [ ] Item 1: Dipilah → Kardus → 2.0 kg                                                      | Subtotal Rp 1.300                                              |         |
| 6.3.3 | [ ] Tambah Item 2: Tidak Dipilah → 5.0 kg                                                  | Subtotal Rp 0                                                  |         |
| 6.3.4 | [ ] Total di sidebar = Rp 1.300 (hanya dari item dipilah)                                   | Benar                                                          |         |
| 6.3.5 | [ ] Simpan → cek detail                                                                     | 2 item tampil dengan status berbeda, total = Rp 1.300          |         |

### 6.4 Validasi Form Setoran

| ID   | Langkah                                                                                       | Hasil yang Diharapkan                                          | Catatan |
|------|-----------------------------------------------------------------------------------------------|----------------------------------------------------------------|---------|
| 6.4.1 | [ ] Submit form tanpa pilih warga                                                           | Validasi gagal                                                 |         |
| 6.4.2 | [ ] Submit dengan Status Pemilahan belum dipilih                                            | Validasi gagal                                                 |         |
| 6.4.3 | [ ] Pilih Dipilah tapi tidak pilih Item/Kategori → Submit                                   | Validasi gagal, pesan "jenis sampah harus dipilih"             |         |
| 6.4.4 | [ ] Isi berat = 0 atau negatif                                                              | Validasi gagal                                                 |         |
| 6.4.5 | [ ] Klik hapus satu-satunya item row                                                        | Muncul alert "Minimal satu item harus ada"                     |         |
| 6.4.6 | [ ] Pilih tanggal setoran di masa depan                                                     | Validasi gagal                                                 |         |

---

## 7. Riwayat Setoran

| ID   | Langkah                                                                         | Hasil yang Diharapkan                                         | Catatan |
|------|---------------------------------------------------------------------------------|---------------------------------------------------------------|---------|
| 7.1  | [ ] Buka Riwayat Setoran                                                       | Daftar semua setoran tampil                                   |         |
| 7.2  | [ ] Filter status = "Belum Dibayar"                                            | Hanya setoran belum dibayar tampil                            |         |
| 7.3  | [ ] Filter status = "Sudah Dibayar"                                            | Hanya setoran sudah dibayar tampil                            |         |
| 7.4  | [ ] Klik tombol "Detail" (ikon mata) pada setoran                              | Halaman Detail Setoran tampil dengan info lengkap             |         |
| 7.5  | [ ] Klik tombol "Catat Bayar" pada setoran belum dibayar                       | Modal konfirmasi pembayaran muncul                            |         |
| 7.6  | [ ] Di modal: jumlah dibayar terisi otomatis sesuai total setoran              | Benar                                                         |         |
| 7.7  | [ ] Konfirmasi pembayaran (tanpa ubah jumlah)                                  | Status berubah ke "Sudah Dibayar", tombol Catat Bayar hilang  |         |
| 7.8  | [ ] Konfirmasi pembayaran dengan jumlah diubah (misal pembulatan)              | Tersimpan dengan jumlah baru, muncul catatan "*disesuaikan"   |         |
| 7.9  | [ ] Klik tombol "Cetak Kwitansi"                                               | Halaman kwitansi terbuka di tab baru, bisa dicetak            |         |
| 7.10 | [ ] Cek kwitansi: item "Tidak Dipilah" tampil tanpa harga × tarif             | Hanya berat yang tampil untuk item tidak dipilah              |         |
| 7.11 | [ ] Cek pagination: buat lebih dari 20 setoran, cek halaman 2                 | Pagination berfungsi                                          |         |

---

## 8. Laporan Pembayaran

| ID   | Langkah                                                                          | Hasil yang Diharapkan                                        | Catatan |
|------|----------------------------------------------------------------------------------|--------------------------------------------------------------|---------|
| 8.1  | [ ] Buka Laporan Pembayaran                                                     | Riwayat pembayaran yang sudah dikonfirmasi tampil            |         |
| 8.2  | [ ] Filter: Dari Tanggal = hari ini, Sampai = hari ini → klik Filter           | Data difilter sesuai rentang, total periode muncul           |         |
| 8.3  | [ ] Filter dengan rentang tanggal yang tidak ada data                           | Pesan "Belum ada data pembayaran pada periode ini"            |         |
| 8.4  | [ ] Klik Reset filter                                                           | Filter bersih, semua pembayaran tampil kembali               |         |
| 8.5  | [ ] Klik ikon mata pada baris pembayaran                                        | Diarahkan ke Detail Setoran terkait                          |         |
| 8.6  | [ ] Klik ikon printer pada baris pembayaran                                     | Kwitansi terbuka di tab baru                                 |         |

---

## 9. Dasbor

> Buat beberapa setoran terlebih dahulu agar dasbor memiliki data untuk ditampilkan.

| ID   | Langkah                                                                          | Hasil yang Diharapkan                                        | Catatan |
|------|----------------------------------------------------------------------------------|--------------------------------------------------------------|---------|
| 9.1  | [ ] Buka Dasbor                                                                 | Semua KPI card tampil tanpa error                            |         |
| 9.2  | [ ] Cek: "Total Sampah Bulan Ini" — sesuai data yang diinput?                  | Angka cocok dengan total berat setoran bulan ini             |         |
| 9.3  | [ ] Cek: "Tingkat Pemilahan" — persentase masuk akal?                          | Benar sesuai hitungan (kg dipilah / total kg × 100%)         |         |
| 9.4  | [ ] Cek: "Nilai Tertunda" — sesuai total setoran belum dibayar?                | Benar                                                        |         |
| 9.5  | [ ] Tidak ada error atau angka NaN / null tampil di kartu KPI                  | Semua angka tampil dengan format Rp atau kg                  |         |

---

## 10. Analitik Data

| ID   | Langkah                                                                          | Hasil yang Diharapkan                                        | Catatan |
|------|----------------------------------------------------------------------------------|--------------------------------------------------------------|---------|
| 10.1 | [ ] Buka Analitik & Laporan → Analitik Data                                    | Halaman tampil dengan 4 KPI card di atas                     |         |
| 10.2 | [ ] Cek chart "Tren 12 Bulan Terakhir" tampil (bar + line)                     | Chart tampil tanpa error JS                                  |         |
| 10.3 | [ ] Cek chart "Komposisi Sampah" (donut)                                        | Tampil dengan data organik/anorganik/tidak dipilah           |         |
| 10.4 | [ ] Cek chart "Kontribusi per RW" (bar)                                         | Tampil, jumlah RW sesuai data yang diinput                   |         |
| 10.5 | [ ] Cek chart "Status Pembayaran" (donut kecil)                                 | Tampil, angka sesuai transaksi                               |         |
| 10.6 | [ ] Cek chart "Tren Persentase Pemilahan" (line)                                | Tampil tanpa error                                           |         |
| 10.7 | [ ] Ganti filter bulan ke bulan berbeda                                         | Semua chart dan tabel memperbarui data sesuai bulan          |         |
| 10.8 | [ ] Tabel "Tingkat Pemilahan per RW" — data benar?                             | Persentase dan berat sesuai setoran yang diinput             |         |
| 10.9 | [ ] Tabel "Top 10 Warga" — warga yang paling banyak setor dipilah?            | Urutan benar berdasarkan kg dipilah                          |         |
| 10.10| [ ] Kotak "Partisipasi Warga" — persentase masuk akal?                         | (Warga berkontribusi / warga aktif) × 100%                   |         |
| 10.11| [ ] Klik tombol "Cetak Laporan"                                                 | Dialog print browser muncul                                  |         |
| 10.12| [ ] Klik "Papan Peringkat Lengkap"                                              | Diarahkan ke halaman Papan Peringkat bulan yang sama         |         |

---

## 11. Papan Peringkat (Staff)

| ID   | Langkah                                                                          | Hasil yang Diharapkan                                        | Catatan |
|------|----------------------------------------------------------------------------------|--------------------------------------------------------------|---------|
| 11.1 | [ ] Buka Papan Peringkat (dari sidebar)                                         | Halaman tampil, filter bulan default = bulan ini             |         |
| 11.2 | [ ] Cek tabel Ranking Warga — urutan sesuai poin tertinggi?                    | Benar (warga dengan kg dipilah lebih banyak di atas)         |         |
| 11.3 | [ ] Cek badge rank: #1 = emas, #2 = abu, #3 = merah                           | Badge warna sesuai                                           |         |
| 11.4 | [ ] Ganti filter bulan → pilih bulan berbeda                                    | Data berubah sesuai bulan, dropdown bulan tersedia           |         |
| 11.5 | [ ] Cek bagian "Ranking RW" — progress bar tampil?                             | Progress bar muncul, persentase sesuai                       |         |
| 11.6 | [ ] Klik "Lihat versi publik"                                                   | Papan peringkat publik terbuka di tab baru                   |         |

---

## 12. Papan Peringkat Publik

| ID   | Langkah                                                                          | Hasil yang Diharapkan                                        | Catatan |
|------|----------------------------------------------------------------------------------|--------------------------------------------------------------|---------|
| 12.1 | [ ] Buka `http://127.0.0.1:8000/leaderboard` tanpa login                       | Halaman publik tampil, tidak perlu login                     |         |
| 12.2 | [ ] Data ranking warga tampil (jika ada setoran bulan ini)                     | Nama warga, poin, dan persentase tampil                      |         |

---

## 13. Sinkronisasi Sidebar

| ID   | Langkah                                                                          | Hasil yang Diharapkan                                        | Catatan |
|------|----------------------------------------------------------------------------------|--------------------------------------------------------------|---------|
| 13.1 | [ ] Buka Dasbor → cek sidebar                                                  | "Dasbor Ringkasan" aktif (highlight)                         |         |
| 13.2 | [ ] Buka Data Warga → cek sidebar                                              | Grup "Data Master" terbuka, "Data Warga" aktif               |         |
| 13.3 | [ ] Buka Manajemen Tarif → cek sidebar                                         | Grup "Data Master" terbuka, "Manajemen Tarif" aktif          |         |
| 13.4 | [ ] Buka Import Data Warga → cek sidebar                                       | Grup "Data Master" terbuka, "Import Data Warga" aktif        |         |
| 13.5 | [ ] Buka Input Setoran → cek sidebar                                           | Grup "Transaksi" terbuka, "Input Setoran" aktif              |         |
| 13.6 | [ ] Buka Riwayat Setoran → cek sidebar                                         | Grup "Transaksi" terbuka, "Riwayat Setoran" aktif            |         |
| 13.7 | [ ] Buka Laporan Pembayaran → cek sidebar                                      | Grup "Transaksi" terbuka, "Laporan Pembayaran" aktif         |         |
| 13.8 | [ ] Buka Analitik Data → cek sidebar                                           | Grup "Analitik & Laporan" terbuka, "Analitik Data" aktif     |         |
| 13.9 | [ ] Buka Papan Peringkat → cek sidebar                                         | Grup "Analitik & Laporan" terbuka, "Papan Peringkat" aktif   |         |

---

## 14. Pengujian Multi-Pengguna (Simultan)

| ID   | Langkah                                                                                           | Hasil yang Diharapkan                                        | Catatan |
|------|---------------------------------------------------------------------------------------------------|--------------------------------------------------------------|---------|
| 14.1 | [ ] Login sebagai Admin di browser A, login sebagai Petugas di browser B (incognito)            | Keduanya bisa login bersamaan                                |         |
| 14.2 | [ ] Admin buat setoran baru dari browser A, Petugas refresh Riwayat Setoran di browser B        | Setoran Admin muncul di daftar Petugas                       |         |
| 14.3 | [ ] Petugas konfirmasi pembayaran setoran. Admin refresh Riwayat Setoran                        | Status berubah di tampilan Admin                             |         |

---

## Ringkasan Hasil Pengujian

| Modul                      | Total Test | Lulus | Gagal | Catatan |
|----------------------------|------------|-------|-------|---------|
| 1. Autentikasi             | 10         |       |       |         |
| 2. Kontrol Akses Role      | 10         |       |       |         |
| 3. Manajemen Tarif         | 13         |       |       |         |
| 4. Data Warga              | 9          |       |       |         |
| 5. Import Data Warga       | 7          |       |       |         |
| 6. Input Setoran           | 15         |       |       |         |
| 7. Riwayat Setoran         | 11         |       |       |         |
| 8. Laporan Pembayaran      | 6          |       |       |         |
| 9. Dasbor                  | 5          |       |       |         |
| 10. Analitik Data          | 12         |       |       |         |
| 11. Papan Peringkat Staff  | 6          |       |       |         |
| 12. Papan Peringkat Publik | 2          |       |       |         |
| 13. Sinkronisasi Sidebar   | 9          |       |       |         |
| 14. Multi-Pengguna         | 3          |       |       |         |
| **TOTAL**                  | **118**    |       |       |         |

---

## Catatan Umum QA

> Tulis temuan bug, UI aneh, atau catatan penting di sini:

1. 
2. 
3. 

---

*Dokumen ini dibuat untuk pengujian internal SIPS · Desa Banjarsari*
