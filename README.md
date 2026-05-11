# SIPS — Waste Sorting Information System

A web-based platform to support community-level waste sorting programs at the RW/Dusun level, Desa Banjarsari.

---

## About

SIPS records daily waste deposits from residents, automatically calculates payment values based on configured rates, logs payment confirmations, and displays monthly participation rankings transparently.

**SIPS is not a financial system.** Cash payments are handled outside the system; SIPS only records that payment has been made.

### User Roles

| Actor | Access |
|---|---|
| **Village Admin** | Full access: resident master data, rates, all deposits, payments, dashboard, rankings |
| **Waste Bank Officer** | Input deposits, search residents, record payments, today's deposit summary |
| **Public (RW Head / Residents)** | Public leaderboard & dashboard — no login required |

> Residents **do not have login accounts**. All data entry is performed by officers.

---

## Tech Stack

| Layer | Tech |
|---|---|
| Backend | Laravel 12 · PHP 8.2 |
| Database | MySQL (dev & production) |
| Public Frontend | Blade + **Landrick v5** template (Shreethemes) |
| Admin Frontend | Blade + **Fabkin** admin template (Pixeleyez) |
| Auth | Laravel built-in (session-based) |
| Assets | Bootstrap 5 · Feather Icons · Unicons · Landrick JS stack |

### Important View Structure

```
resources/views/
├── layouts/
│   ├── landrick/main.blade.php   ← public page layout (landing, leaderboard)
│   └── partials/layouts/         ← Fabkin admin layout
├── public/
│   ├── landing.blade.php
│   └── leaderboard/index.blade.php
└── includes/landrick-sips/       ← public components (navbar, footer, etc.)
```

```
public/assets/
├── css/style.css                 ← Landrick stylesheet (DO NOT overwrite with Fabkin)
├── js/landrick-app.js            ← Landrick app.js (separate from Fabkin app.js)
└── js/app.js                     ← Fabkin admin JS (DO NOT overwrite)
```

---

## System Modules

| Module | Name | Owner |
|---|---|---|
| 1 | Resident Master Data | **Dev A** |
| 2 | Waste Deposit Recording | **Dev B** |
| 3 | Rate Management | **Dev A** |
| 4 | Payment Recording | **Dev B** |
| 5 | Dashboard & Ranking System | **Dev C** |
| 6 | Data Import & Tools | **Dev C** |

---

## Team Task Division (Vertical Slice)

Each developer owns **end-to-end responsibility** (Database + Backend + Frontend) for their feature slice. Goal: all three devs work in parallel without blocking each other.

### Dev A — Master Data (Module 1 + 3)

**Owns:** Core reference data — residents and waste rates. Foundation used by Dev B and C.

Database tables: `warga`, `tarif_items`, `riwayat_tarif`, `users`

Pages:
- `/admin/warga` — manage resident list
- `/admin/tarif` — manage rate items & price history
- `/login` — admin/officer login page

**Key APIs consumed by Dev B & C:**
- `GET /api/warga?search=&rw=` — search residents in deposit form
- `GET /api/tarif/lookup?item_id=&tanggal=` — look up the rate active on a given date
- `GET /api/tarif/items?tipe=` — rate item dropdown

### Dev B — Operations (Module 2 + 4)

**Owns:** Core transaction flow — multi-item deposit input and payment confirmation.

Database tables: `setoran`, `item_setoran`, `pembayaran`

Pages:
- `/petugas/setoran/baru` — multi-item deposit form (core page, mobile-first)
- `/petugas/setoran/:id/kwitansi` — receipt after submission
- `/petugas/setoran/:id/bayar` — payment confirmation
- `/admin/setoran` — deposit monitoring
- `/admin/pembayaran` — payment report

**Important:** The `harga_per_kg_saat_itu` field in `item_setoran` is a **price snapshot** at the time of the transaction — not a live FK. Call `GET /api/tarif/lookup` from Dev A to get the value, then store it permanently.

### Dev C — Analytics & Tools (Module 5 + 6)

**Owns:** Aggregation dashboard, ranking system, and Excel import. No other module depends on Dev C.

Database tables: `snapshot_peringkat_bulanan`, `import_log`, `pengaturan_sistem`

Pages:
- `/leaderboard` — public, resident & RW rankings (UI done, needs real data)
- `/` (landing) — public landing page (UI done)
- `/dashboard` — public, per-RW data summary
- `/admin/import` — Excel upload, preview, confirm

**Individual points formula:**
```
Points = (Total kg sorted × 10) + (Consecutive months depositing × 50) + (% sorted of total × 2)
```

---

## Business Rules

| Code | Rule |
|---|---|
| BR-01 | Historical rates **never change retroactively** — old deposits keep their original price |
| BR-02 | Minimum deposit weight is **0.1 kg** — below this the system rejects the input |
| BR-03 | **One waste type per deposit** — organic + inorganic = 2 separate deposit entries |
| BR-04 | Officers can edit a deposit within **24 hours**; after that only Admin can edit or void |
| BR-05 | Resident with **no deposit for 30 days** is automatically marked Inactive |
| BR-06 | Official rankings are set at **end of month** — real-time points are not final |
| BR-07 | Recorded payment amount **may differ** from system value (rounding, local policy) |
| BR-08 | One deposit can only be paid **once** — prevents double payment |

---

## Local Setup

**Prerequisites:** PHP 8.2+, Composer, MySQL

```bash
git clone <repo-url>
cd pemilhan-sampah

composer install
cp .env.example .env
php artisan key:generate
```

Configure your MySQL credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sips
DB_USERNAME=root
DB_PASSWORD=your_password
```

```bash
php artisan migrate
php artisan db:seed

php artisan serve
```

**Asset note:** Do not run `npm run dev` — it will overwrite `public/assets/js/app.js` which is the Fabkin admin JS. Landrick JS is already available at `public/assets/js/landrick-app.js`.

---

## Git Conventions

```
main        ← production-ready
develop     ← integration branch for all devs
feature/*   ← per-feature branch (e.g. feature/deposit-form)
```

- Pull from `develop` every morning before starting work
- Do not touch another dev's slice — open an issue or ask the owner directly
- API contract is frozen after Week 1; changes must be agreed on by all three devs

---

## Rough Timeline

| Week | Target |
|---|---|
| W1 | Repo setup, agree on ER diagram & API contract, build shared components |
| W2 | Dev A: warga & tarif API · Dev B: deposit form skeleton (mock API) · Dev C: chart components |
| W3 | Dev B: integrate Dev A's real API · Dev C: dashboard with live data |
| W4 | Dev B: payment & receipt flow · Dev C: ranking cron job & Excel import |
| W5 | UI polish, performance optimization |
| W6 | Joint UAT, bug bash, deployment |

---

## Reference Documents

| File | Contents |
|---|---|
| `SIPS_Deskripsi_Sistem_v4_2.docx` | Full system specification, workflows, data entities, business rules |
| `SIPS_Pembagian_Tugas_3_Dev.docx` | Per-dev task breakdown, API contracts, database schema, definition of done |
