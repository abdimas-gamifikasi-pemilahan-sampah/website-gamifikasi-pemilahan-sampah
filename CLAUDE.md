# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

**SIPS** (Sistem Informasi Pemilahan Sampah) — Laravel 12 waste-management system for TPS 3R Banjarsari village. Records waste deposits, computes leaderboard rankings, and exports reports in two village-specific Excel formats (Perolehan & Rivan).

## Commands

```bash
# Start all dev processes at once (server + queue + pail logs + vite)
composer run dev

# Or individually
php artisan serve
npm run dev

# Run all tests
composer test

# Run a single test class or method
php artisan test --filter SetoranPaymentStatusTest
php artisan test --filter "test_status_pembayaran_netral"

# Code style (Laravel Pint)
./vendor/bin/pint

# Fresh database with required seeds
php artisan migrate:fresh
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=PengaturanSistemSeeder

# Check migration status
php artisan migrate:status
```

### Test accounts (after UserSeeder)
| Role | Email | Password |
|------|-------|----------|
| Admin | admin@sips.test | password123 |
| Petugas | petugas@sips.test | password123 |

## Architecture

### Rendering approach
Server-side rendered — Blade views only. No SPA. The `routes/api.php` is a JSON API consumed by external tools (Postman, future mobile app); all web UI uses `routes/web.php`.

### Layout
All authenticated views extend `partials.layouts.master`. Two yield slots matter:
- `@yield('content')` — page body
- `@yield('js')` — page-specific scripts (placed after vendor scripts, so Bootstrap/etc. is available)

All SIPS views live under `resources/views/sips/`. Public pages are under `resources/views/public/`.

### Role system
Two roles: `admin` and `petugas`. Enforced via `EnsureUserHasRole` middleware, registered as `role` alias. Usage in routes:

```php
Route::middleware('role:admin')->group(...);          // admin only
Route::middleware('role:admin,petugas')->group(...);  // either role
```

`User::isAdmin()` and `User::isPetugas()` are available on the model.

### Money model (critical — read before touching setoran)
`setoran.nilai` is a **signed decimal**:
- **Positive** = TPS pays warga (dipilah — sorted waste has resale value)
- **Negative** = warga pays iuran to TPS (tidak dipilah — unsorted waste)
- **Zero** = no financial transaction

`App\Support\SignedMoney` inverts the sign for display from the TPS/accounting perspective: `nilai > 0` → "Pengeluaran" (TPS outflow), `nilai < 0` → "Pemasukan" (TPS income). This is intentional — always check `SignedMoney` tests before changing display logic.

Two separate tariff settings in `pengaturan_sistem`:
- `tarif_flat_per_kg` — iuran rate (charged when tidak dipilah)
- `nilai_dipilah_per_kg` — value rate (paid out when dipilah)

Both are read via `PengaturanSistem::get('key', $default)`.

### Key models & relationships
```
Setoran ──hasMany──> ItemSetoran
        ──hasOne───> Pembayaran
        ──belongsTo> Warga (nullable — multi-penyetor setoran have no warga link)
        ──belongsTo> User (as petugas_id)

TarifItem ──hasMany──> RiwayatTarif   (price history with date ranges)
```

`Setoran.warga` is **nullable**. Any view accessing `$setoran->warga->nama` must guard with `@if($setoran->warga)` or `$setoran->warga?->nama`. The `show.blade.php` already does this correctly.

### PengaturanSistem
Key-value settings table. Access pattern: `PengaturanSistem::get('key', $default)` (returns string or default), `PengaturanSistem::getFloat('key')`, `PengaturanSistem::set('key', $value, $description)`. Must be seeded (`PengaturanSistemSeeder`) before the setoran form works — the create view will show `Rp 0/kg` otherwise.

### ImportLog
Tracks both warga CSV imports and setoran Excel imports. The `jenis` enum includes: `'warga'`, `'setoran_perolehan'`, `'setoran_rivan'`, `'setoran_detail'`. Migration `000004` extended the enum to add the setoran variants.

### Helpers
- `App\Helpers\RwParser::normalize($input)` — normalises any RW string from real village data into zero-padded comma-separated form. Handles `'06 & 01'`, `'05, 01, 06'`, `'RW 06'`, `'6'`. Use whenever storing or displaying `area_rw`.
- `App\Helpers\TanggalParser::parse($input)` — handles three date formats found in village Excel files: Excel serial integer, `dd/mm/yyyy`, and Indonesian text (`'Senin, 9 Maret 2026'`). Returns `Carbon|null`.

### Excel (phpspreadsheet)
`phpoffice/phpspreadsheet ^5.7` is installed. Pattern used by `ImportSetoranController` for streaming a download:

```php
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
return response()->streamDownload(
    fn() => $writer->save('php://output'),
    'filename.xlsx',
    ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
);
```

`ImportSetoranController::downloadTemplate()` shows complete examples of building Perolehan, Rivan, and Detail templates — reuse for export.

### RankingService
`App\Services\RankingService::computeMonth(Carbon $bulan)` computes and persists monthly ranking snapshots into `snapshot_peringkat_bulanan`. It is **not scheduled** — it must be called manually (admin trigger or Artisan command). `LeaderboardController` reads live data directly from `item_setoran` JOINs, not from the snapshot table.

### Setoran store flow
`SetoranController::store()` dispatches to one of three private methods based on `mode` POST field:
- `storePenyetor` — current default; multi-card form with nested items per penyetor
- `storeDetail` — flat rows mode
- `storeLegacy` — backward compat with old per-item complex tariff form

### Migration notes
- Three v5 migrations `2026_06_02_000001/2/3` must run in order (extend setoran, make warga nullable, extend item_setoran).
- Migrations that alter column types use raw `DB::statement` guarded by `if (DB::getDriverName() !== 'mysql')` — they are no-ops on SQLite. Tests run on SQLite so column types may differ from production MySQL.
- Do not modify already-deployed migrations. Add new ones instead.
