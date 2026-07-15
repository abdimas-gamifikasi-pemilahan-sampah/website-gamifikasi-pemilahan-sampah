<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Generates realistic demo data for 3 months: May, June, July 2026.
 * Covers all setoran modes, payment statuses, and warga variations.
 *
 * Usage:
 *   php artisan migrate:fresh
 *   php artisan db:seed --class=UserSeeder
 *   php artisan db:seed --class=PengaturanSistemSeeder
 *   php artisan db:seed --class=TarifSeeder
 *   php artisan db:seed --class=DummyLaporanSeeder
 */
class DummyLaporanSeeder extends Seeder
{
    private float $tarifFlat;
    private float $nilaiDipilah;
    private int   $petugasId;
    private array $wargaList = [];

    // ── Extended warga list (adds 32 to the original 20) ─────────────────────

    private const WARGA_TAMBAHAN = [
        // RW 01 – Melati
        ['nama' => 'Bambang Suharto',  'no_kk' => '3301010101010021', 'alamat' => 'Jl. Melati No. 33 RT 3 RW 1',    'rt' => 3, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000021'],
        ['nama' => 'Sri Wahyuni',      'no_kk' => '3301010101010022', 'alamat' => 'Jl. Melati No. 7 RT 1 RW 1',     'rt' => 1, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000022'],
        ['nama' => 'Eko Purnomo',      'no_kk' => '3301010101010023', 'alamat' => 'Jl. Melati No. 19 RT 2 RW 1',    'rt' => 2, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000023'],
        ['nama' => 'Mulyati',          'no_kk' => '3301010101010024', 'alamat' => 'Jl. Melati No. 42 RT 3 RW 1',    'rt' => 3, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000024'],
        ['nama' => 'Hendro Susanto',   'no_kk' => '3301010101010025', 'alamat' => 'Jl. Melati No. 11 RT 1 RW 1',    'rt' => 1, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000025'],
        ['nama' => 'Wati Handayani',   'no_kk' => '3301010101010026', 'alamat' => 'Jl. Melati No. 25 RT 2 RW 1',    'rt' => 2, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000026'],
        // RW 02 – Anggrek
        ['nama' => 'Slamet Riyadi',    'no_kk' => '3301010101010027', 'alamat' => 'Jl. Anggrek No. 6 RT 1 RW 2',    'rt' => 1, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000027'],
        ['nama' => 'Suwarno',          'no_kk' => '3301010101010028', 'alamat' => 'Jl. Anggrek No. 14 RT 2 RW 2',   'rt' => 2, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000028'],
        ['nama' => 'Endang Sulistyo',  'no_kk' => '3301010101010029', 'alamat' => 'Jl. Anggrek No. 27 RT 3 RW 2',   'rt' => 3, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000029'],
        ['nama' => 'Parno Widodo',     'no_kk' => '3301010101010030', 'alamat' => 'Jl. Anggrek No. 38 RT 1 RW 2',   'rt' => 1, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000030'],
        ['nama' => 'Sumarni',          'no_kk' => '3301010101010031', 'alamat' => 'Jl. Anggrek No. 45 RT 2 RW 2',   'rt' => 2, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000031'],
        ['nama' => 'Yatno Haryanto',   'no_kk' => '3301010101010032', 'alamat' => 'Jl. Anggrek No. 52 RT 3 RW 2',   'rt' => 3, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000032'],
        // RW 03 – Kenanga
        ['nama' => 'Marwan Hidayat',   'no_kk' => '3301010101010033', 'alamat' => 'Jl. Kenanga No. 4 RT 1 RW 3',    'rt' => 1, 'rw' => 3, 'dusun' => 'Kenanga',   'no_hp' => '081210000033'],
        ['nama' => 'Suwito',           'no_kk' => '3301010101010034', 'alamat' => 'Jl. Kenanga No. 16 RT 2 RW 3',   'rt' => 2, 'rw' => 3, 'dusun' => 'Kenanga',   'no_hp' => '081210000034'],
        ['nama' => 'Eni Sulistyowati', 'no_kk' => '3301010101010035', 'alamat' => 'Jl. Kenanga No. 29 RT 3 RW 3',   'rt' => 3, 'rw' => 3, 'dusun' => 'Kenanga',   'no_hp' => '081210000035'],
        ['nama' => 'Parimin',          'no_kk' => '3301010101010036', 'alamat' => 'Jl. Kenanga No. 37 RT 1 RW 3',   'rt' => 1, 'rw' => 3, 'dusun' => 'Kenanga',   'no_hp' => '081210000036'],
        // RW 04 – Flamboyan
        ['nama' => 'Karyanto',         'no_kk' => '3301010101010037', 'alamat' => 'Jl. Flamboyan No. 5 RT 1 RW 4',  'rt' => 1, 'rw' => 4, 'dusun' => 'Flamboyan', 'no_hp' => '081210000037'],
        ['nama' => 'Suparjo',          'no_kk' => '3301010101010038', 'alamat' => 'Jl. Flamboyan No. 18 RT 2 RW 4', 'rt' => 2, 'rw' => 4, 'dusun' => 'Flamboyan', 'no_hp' => '081210000038'],
        ['nama' => 'Nanik Rahayu',     'no_kk' => '3301010101010039', 'alamat' => 'Jl. Flamboyan No. 31 RT 3 RW 4', 'rt' => 3, 'rw' => 4, 'dusun' => 'Flamboyan', 'no_hp' => '081210000039'],
        ['nama' => 'Wahono',           'no_kk' => '3301010101010040', 'alamat' => 'Jl. Flamboyan No. 43 RT 1 RW 4', 'rt' => 1, 'rw' => 4, 'dusun' => 'Flamboyan', 'no_hp' => '081210000040'],
        ['nama' => 'Sucipto',          'no_kk' => '3301010101010041', 'alamat' => 'Jl. Flamboyan No. 56 RT 2 RW 4', 'rt' => 2, 'rw' => 4, 'dusun' => 'Flamboyan', 'no_hp' => '081210000041'],
        // RW 05 – Cempaka
        ['nama' => 'Budiman',          'no_kk' => '3301010101010042', 'alamat' => 'Jl. Cempaka No. 8 RT 1 RW 5',    'rt' => 1, 'rw' => 5, 'dusun' => 'Cempaka',   'no_hp' => '081210000042'],
        ['nama' => 'Pujiastuti',       'no_kk' => '3301010101010043', 'alamat' => 'Jl. Cempaka No. 22 RT 2 RW 5',   'rt' => 2, 'rw' => 5, 'dusun' => 'Cempaka',   'no_hp' => '081210000043'],
        ['nama' => 'Hartono',          'no_kk' => '3301010101010044', 'alamat' => 'Jl. Cempaka No. 35 RT 3 RW 5',   'rt' => 3, 'rw' => 5, 'dusun' => 'Cempaka',   'no_hp' => '081210000044'],
        ['nama' => 'Sumini',           'no_kk' => '3301010101010045', 'alamat' => 'Jl. Cempaka No. 47 RT 1 RW 5',   'rt' => 1, 'rw' => 5, 'dusun' => 'Cempaka',   'no_hp' => '081210000045'],
        ['nama' => 'Sarwanto',         'no_kk' => '3301010101010046', 'alamat' => 'Jl. Cempaka No. 59 RT 2 RW 5',   'rt' => 2, 'rw' => 5, 'dusun' => 'Cempaka',   'no_hp' => '081210000046'],
        // RW 06 – Mawar (new dusun)
        ['nama' => 'Wuryanto',         'no_kk' => '3301010101010047', 'alamat' => 'Jl. Mawar No. 3 RT 1 RW 6',      'rt' => 1, 'rw' => 6, 'dusun' => 'Mawar',     'no_hp' => '081210000047'],
        ['nama' => 'Siti Maryam',      'no_kk' => '3301010101010048', 'alamat' => 'Jl. Mawar No. 17 RT 2 RW 6',     'rt' => 2, 'rw' => 6, 'dusun' => 'Mawar',     'no_hp' => '081210000048'],
        ['nama' => 'Joko Prabowo',     'no_kk' => '3301010101010049', 'alamat' => 'Jl. Mawar No. 28 RT 1 RW 6',     'rt' => 1, 'rw' => 6, 'dusun' => 'Mawar',     'no_hp' => '081210000049'],
        ['nama' => 'Ratna Dewi',       'no_kk' => '3301010101010050', 'alamat' => 'Jl. Mawar No. 39 RT 2 RW 6',     'rt' => 2, 'rw' => 6, 'dusun' => 'Mawar',     'no_hp' => '081210000050'],
        ['nama' => 'Gunawan',          'no_kk' => '3301010101010051', 'alamat' => 'Jl. Mawar No. 44 RT 3 RW 6',     'rt' => 3, 'rw' => 6, 'dusun' => 'Mawar',     'no_hp' => '081210000051'],
        ['nama' => 'Tuminah',          'no_kk' => '3301010101010052', 'alamat' => 'Jl. Mawar No. 51 RT 3 RW 6',     'rt' => 3, 'rw' => 6, 'dusun' => 'Mawar',     'no_hp' => '081210000052'],
    ];

    // ─────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        // Clear setoran-related tables (keep warga/users/settings)
        DB::table('item_setoran')->delete();
        DB::table('pembayaran')->delete();
        DB::table('setoran')->delete();
        DB::table('snapshot_peringkat_bulanan')->delete();

        $this->tarifFlat    = (float) (DB::table('pengaturan_sistem')->where('kunci', 'tarif_flat_per_kg')->value('nilai') ?: 500);
        $this->nilaiDipilah = (float) (DB::table('pengaturan_sistem')->where('kunci', 'nilai_dipilah_per_kg')->value('nilai') ?: 500);
        $this->petugasId    = DB::table('users')->where('role', 'petugas')->value('id');

        $this->upsertWarga();

        $this->wargaList = DB::table('warga')
            ->where('status_keanggotaan', 'aktif')
            ->orderBy('rw')->orderBy('rt')->orderBy('id')
            ->get(['id', 'nama', 'rt', 'rw', 'dusun'])
            ->toArray();

        // Mei 2026 — 2 bulan lalu (semua sudah lunas)
        $this->seedMonth(2026, 5, dayLimit: null, closedMonth: true);

        // Juni 2026 — 1 bulan lalu (sebagian besar lunas, sedikit tertunda)
        $this->seedMonth(2026, 6, dayLimit: null, closedMonth: false, paidRatio: 0.85);

        // Juli 2026 — bulan berjalan (hanya 9 hari, banyak yang belum bayar)
        $this->seedMonth(2026, 7, dayLimit: 9, closedMonth: false, paidRatio: 0.50);

        $this->command?->info('DummyLaporanSeeder selesai: ' . count($this->wargaList) . ' warga aktif, 3 bulan data diisi.');
    }

    // ── Month generator ───────────────────────────────────────────────────────

    private function seedMonth(
        int   $year,
        int   $month,
        ?int  $dayLimit,
        bool  $closedMonth,
        float $paidRatio = 1.0,
    ): void {
        $start   = Carbon::create($year, $month, 1);
        $end     = $dayLimit
            ? Carbon::create($year, $month, $dayLimit)
            : $start->copy()->endOfMonth();

        // Aggregate pengangkutan (truck collections) — setiap ~7 hari
        // Jadwal: tanggal 3, 10, 17, 24 (skip jika lewat akhir bulan)
        $pickupSchedule = [
            ['day' => 3,  'area' => '01, 02, 03', 'kg' => 210.0, 'kgDipilah' => 85.0],
            ['day' => 7,  'area' => '04, 05, 06', 'kg' => 185.0, 'kgDipilah' => 60.0],
            ['day' => 10, 'area' => '01, 02, 03', 'kg' => 235.0, 'kgDipilah' => 98.0],
            ['day' => 14, 'area' => '04, 05, 06', 'kg' => 192.0, 'kgDipilah' => 55.0],
            ['day' => 17, 'area' => '01, 02, 03', 'kg' => 221.0, 'kgDipilah' => 90.0],
            ['day' => 21, 'area' => '04, 05, 06', 'kg' => 175.0, 'kgDipilah' => 48.0],
            ['day' => 24, 'area' => '01, 02, 03', 'kg' => 248.0, 'kgDipilah' => 105.0],
            ['day' => 28, 'area' => '04, 05, 06', 'kg' => 168.0, 'kgDipilah' => 42.0],
        ];

        foreach ($pickupSchedule as $p) {
            if ($p['day'] > $end->day) break;
            $ts     = Carbon::create($year, $month, $p['day'], 6, 30);
            $lunas  = $closedMonth || ($this->deterministicPaid($p['day'], $paidRatio));
            $this->insertAgregat($ts, $p['area'], $p['kg'], $p['kgDipilah'], $lunas);
        }

        // Individual warga setoran — setiap warga sekali per bulan
        // (warga ke-$i dapat tanggal yang tersebar merata)
        $total     = count($this->wargaList);
        $daySpan   = $end->day - 1; // 0-based spread

        foreach ($this->wargaList as $i => $warga) {
            $day  = 1 + (int) round(($i / $total) * $daySpan);
            $day  = min($day, $end->day);
            $hour = 8 + ($i % 5);
            $min  = ($i * 11) % 60;
            $ts   = Carbon::create($year, $month, $day, $hour, $min);

            $rw         = (int) $warga->rw;
            $isDipilah  = $this->deterministicDipilah($i, $month, $rw);
            $berat      = $this->determineBerat($i); // 6–20 kg
            $lunas      = $closedMonth || $this->deterministicPaid($i, $paidRatio);

            $this->insertWarga((array) $warga, $ts, $berat, $isDipilah, $lunas);
        }

        // Bonus: a few "tidak dipilah" penyetor (non-warga dropoffs) — warung, toko
        $penyetorBonus = [
            ['nama' => 'Warung Bu Sari',   'rw' => 2, 'rt' => 1, 'kg' => 35.0, 'day' => 5],
            ['nama' => 'Toko Maju Jaya',   'rw' => 4, 'rt' => 2, 'kg' => 28.0, 'day' => 12],
            ['nama' => 'Kantin SDN 01',    'rw' => 1, 'rt' => 1, 'kg' => 18.0, 'day' => 19],
            ['nama' => 'Bengkel Pak Heri', 'rw' => 3, 'rt' => 2, 'kg' => 22.0, 'day' => 26],
        ];

        foreach ($penyetorBonus as $p) {
            if ($p['day'] > $end->day) continue;
            $ts    = Carbon::create($year, $month, $p['day'], 10, 0);
            $lunas = $closedMonth || $this->deterministicPaid($p['day'] + 50, $paidRatio);
            $this->insertPenyetor($ts, $p['nama'], (string)$p['rw'], (string)$p['rt'], $p['kg'], $lunas);
        }
    }

    // ── Inserters ─────────────────────────────────────────────────────────────

    private function insertAgregat(
        Carbon $ts,
        string $areaRw,
        float  $totalKg,
        float  $kgDipilah,
        bool   $lunas,
    ): void {
        $kgTidak   = $totalKg - $kgDipilah;
        $nilaiPos  = round($kgDipilah * $this->nilaiDipilah, 2);
        $nilaiNeg  = round($kgTidak * $this->tarifFlat * -1, 2);
        $nilai     = $nilaiPos + $nilaiNeg;

        $sid = DB::table('setoran')->insertGetId([
            'warga_id'               => null,
            'petugas_id'             => $this->petugasId,
            'tanggal_setoran'        => $ts,
            'total_nilai'            => round(abs($nilai), 2),
            'nilai'                  => round($nilai, 2),
            'total_kg'               => $totalKg,
            'total_kg_dipilah'       => $kgDipilah,
            'total_kg_tidak_dipilah' => $kgTidak,
            'area_rw'                => $areaRw,
            'status_pembayaran'      => $lunas ? 'sudah_dibayar' : 'belum_dibayar',
            'is_selesai'             => $lunas,
            'mode'                   => 'agregat',
            'sumber_input'           => 'manual',
            'catatan_kondisi'        => null,
            'created_at'             => $ts,
            'updated_at'             => $ts,
        ]);

        if ($lunas && abs($nilai) > 0) {
            $bayar = $ts->copy()->addHours(2);
            DB::table('pembayaran')->insert([
                'setoran_id'          => $sid,
                'petugas_pembayar_id' => $this->petugasId,
                'tanggal_bayar'       => $bayar,
                'jumlah_dibayar'      => round(abs($nilai), 2),
                'catatan'             => null,
                'created_at'          => $bayar,
            ]);
        }
    }

    private function insertWarga(
        array  $warga,
        Carbon $ts,
        float  $berat,
        bool   $isDipilah,
        bool   $lunas,
    ): void {
        $harga     = $isDipilah ? $this->nilaiDipilah : $this->tarifFlat;
        $subtotal  = $isDipilah ? round($berat * $harga, 2) : round($berat * $harga * -1, 2);
        $kgDipilah = $isDipilah ? $berat : 0;
        $kgTidak   = $isDipilah ? 0 : $berat;
        $nilai     = $subtotal;

        $sid = DB::table('setoran')->insertGetId([
            'warga_id'               => $warga['id'],
            'petugas_id'             => $this->petugasId,
            'tanggal_setoran'        => $ts,
            'total_nilai'            => round(abs($nilai), 2),
            'nilai'                  => round($nilai, 2),
            'total_kg'               => $berat,
            'total_kg_dipilah'       => $kgDipilah,
            'total_kg_tidak_dipilah' => $kgTidak,
            'area_rw'                => null,
            'status_pembayaran'      => $lunas ? 'sudah_dibayar' : 'belum_dibayar',
            'is_selesai'             => $lunas,
            'mode'                   => 'detail',
            'sumber_input'           => 'manual',
            'catatan_kondisi'        => null,
            'created_at'             => $ts,
            'updated_at'             => $ts,
        ]);

        DB::table('item_setoran')->insert([
            'setoran_id'            => $sid,
            'warga_id'              => $warga['id'],
            'tarif_item_id'         => null,
            'riwayat_tarif_id'      => null,
            'tipe_sampah'           => null,
            'status_pemilahan'      => $isDipilah ? 'dipilah' : 'tidak_dipilah',
            'berat_kg'              => $berat,
            'harga_per_kg_saat_itu' => $harga,
            'subtotal'              => $subtotal,
            'nama_penyetor'         => null,
            'rw'                    => null,
            'rt'                    => null,
            'catatan_item'          => null,
            'created_at'            => $ts,
            'updated_at'            => $ts,
        ]);

        if ($lunas && abs($nilai) > 0) {
            $bayar = $ts->copy()->addMinutes(30);
            DB::table('pembayaran')->insert([
                'setoran_id'          => $sid,
                'petugas_pembayar_id' => $this->petugasId,
                'tanggal_bayar'       => $bayar,
                'jumlah_dibayar'      => round(abs($nilai), 2),
                'catatan'             => null,
                'created_at'          => $bayar,
            ]);
        }
    }

    private function insertPenyetor(
        Carbon $ts,
        string $namaPenyetor,
        string $rw,
        string $rt,
        float  $kg,
        bool   $lunas,
    ): void {
        $nilai = round($kg * $this->tarifFlat * -1, 2);

        $sid = DB::table('setoran')->insertGetId([
            'warga_id'               => null,
            'petugas_id'             => $this->petugasId,
            'tanggal_setoran'        => $ts,
            'total_nilai'            => round(abs($nilai), 2),
            'nilai'                  => round($nilai, 2),
            'total_kg'               => $kg,
            'total_kg_dipilah'       => 0,
            'total_kg_tidak_dipilah' => $kg,
            'area_rw'                => str_pad($rw, 2, '0', STR_PAD_LEFT),
            'status_pembayaran'      => $lunas ? 'sudah_dibayar' : 'belum_dibayar',
            'is_selesai'             => $lunas,
            'mode'                   => 'detail',
            'sumber_input'           => 'manual',
            'catatan_kondisi'        => null,
            'created_at'             => $ts,
            'updated_at'             => $ts,
        ]);

        DB::table('item_setoran')->insert([
            'setoran_id'            => $sid,
            'warga_id'              => null,
            'tarif_item_id'         => null,
            'riwayat_tarif_id'      => null,
            'tipe_sampah'           => null,
            'status_pemilahan'      => 'tidak_dipilah',
            'berat_kg'              => $kg,
            'harga_per_kg_saat_itu' => $this->tarifFlat,
            'subtotal'              => $nilai,
            'nama_penyetor'         => $namaPenyetor,
            'rw'                    => str_pad($rw, 2, '0', STR_PAD_LEFT),
            'rt'                    => str_pad($rt, 2, '0', STR_PAD_LEFT),
            'catatan_item'          => null,
            'created_at'            => $ts,
            'updated_at'            => $ts,
        ]);

        if ($lunas) {
            $bayar = $ts->copy()->addMinutes(15);
            DB::table('pembayaran')->insert([
                'setoran_id'          => $sid,
                'petugas_pembayar_id' => $this->petugasId,
                'tanggal_bayar'       => $bayar,
                'jumlah_dibayar'      => round(abs($nilai), 2),
                'catatan'             => null,
                'created_at'          => $bayar,
            ]);
        }
    }

    // ── Deterministic helpers (no random — reproducible every run) ────────────

    /** Returns true if this warga/item should be marked dipilah, based on RW tendency. */
    private function deterministicDipilah(int $index, int $month, int $rw): bool
    {
        // Higher RW = less sorted waste (inverse of civic engagement in this demo)
        $threshold = match ($rw) {
            1 => 7,  // 70% dipilah
            2 => 7,
            3 => 5,  // 50%
            4 => 5,
            5 => 4,  // 40%
            6 => 3,  // 30%
            default => 5,
        };
        return (($index + $month) % 10) < $threshold;
    }

    /** Returns true if this entry should be paid, based on ratio. */
    private function deterministicPaid(int $seed, float $ratio): bool
    {
        $threshold = (int) ($ratio * 10);
        return ($seed % 10) < $threshold;
    }

    /** Returns berat in kg — deterministic, cycles 6–20 kg. */
    private function determineBerat(int $index): float
    {
        $cycle = [6, 8, 10, 12, 14, 8, 16, 10, 18, 12, 20, 8, 14, 6, 10, 16];
        return (float) $cycle[$index % count($cycle)];
    }

    // ── Warga upsert ─────────────────────────────────────────────────────────

    private function upsertWarga(): void
    {
        $now  = Carbon::now();
        $rows = array_map(fn(array $w) => array_merge($w, [
            'tanggal_terdaftar'   => '2026-01-01',
            'status_keanggotaan'  => 'aktif',
            'created_at'          => $now,
            'updated_at'          => $now,
        ]), self::WARGA_TAMBAHAN);

        DB::table('warga')->upsert(
            $rows,
            ['no_kk'],
            ['nama', 'alamat', 'rt', 'rw', 'dusun', 'no_hp', 'status_keanggotaan', 'updated_at']
        );
    }
}
