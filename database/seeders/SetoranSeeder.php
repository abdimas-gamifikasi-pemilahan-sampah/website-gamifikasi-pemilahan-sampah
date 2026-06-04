<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetoranSeeder extends Seeder
{
    private int   $petugasId;
    private int   $adminId;
    private array $wargaMap   = [];   // ['Nama Warga' => id]
    private array $tarifMap   = [];   // ['Nama Item' => {id, tipe, harga}]
    private float $tarifFlat  = 500;

    // ─────────────────────────────────────────────────────────────────────────
    public function run(): void
    {
        DB::table('item_setoran')->delete();
        DB::table('pembayaran')->delete();
        DB::table('setoran')->delete();
        DB::table('snapshot_peringkat_bulanan')->delete();

        $this->petugasId = DB::table('users')->where('role', 'petugas')->value('id');
        $this->adminId   = DB::table('users')->where('role', 'admin')->value('id');
        $this->tarifFlat = (float) DB::table('pengaturan_sistem')->where('kunci', 'tarif_flat_per_kg')->value('nilai') ?: 500;

        // Build warga lookup: nama → id
        DB::table('warga')->get(['id', 'nama'])
            ->each(fn($w) => $this->wargaMap[$w->nama] = $w->id);

        // Build tarif item lookup: nama → {id, tipe, harga}
        DB::table('tarif_items')
            ->join('riwayat_tarif', 'riwayat_tarif.tarif_item_id', '=', 'tarif_items.id')
            ->where('tarif_items.status', 'aktif')
            ->whereNull('riwayat_tarif.tanggal_akhir')
            ->select('tarif_items.id', 'tarif_items.nama_item', 'tarif_items.tipe_sampah', 'riwayat_tarif.harga_per_kg', 'riwayat_tarif.id as riwayat_id')
            ->get()
            ->each(function ($t) {
                $this->tarifMap[$t->nama_item] = [
                    'id'        => $t->id,
                    'tipe'      => $t->tipe_sampah,
                    'harga'     => (float) $t->harga_per_kg,
                    'riwayat_id'=> $t->riwayat_id,
                ];
            });

        $this->seedHistory();
        $this->seedCurrentMonth();
    }

    // ── Historical months Jan–May 2026 ───────────────────────────────────────
    // Simple old-model setoran: warga linked, dipilah items (positive nilai),
    // all sudah_dibayar & selesai. These fill the tren chart.

    private function seedHistory(): void
    {
        $scenarios = [
            // [tanggal, wargaNama, items: [[tarifNama, berat, dipilah]]]
            // January 2026
            ['2026-01-05', 'Budi Santoso',   [['Sisa Makanan', 12.5, true], ['Plastik PET', 4.0, true]]],
            ['2026-01-07', 'Agus Pratama',   [['Kardus', 20.0, true]]],
            ['2026-01-10', 'Hendra Wijaya',  [['Sisa Sayur', 8.0, true], ['Kertas HVS', 6.0, true]]],
            ['2026-01-14', 'Rina Kusumawati',[['Sisa Makanan', 10.0, false]]],
            ['2026-01-18', 'Yusuf Maulana',  [['Kaleng Aluminium', 3.5, true]]],
            ['2026-01-22', 'Sulastri',       [['Sisa Sayur', 15.0, false]]],

            // February 2026
            ['2026-02-03', 'Budi Santoso',   [['Kardus', 18.0, true], ['Plastik HDPE', 5.5, true]]],
            ['2026-02-06', 'Dewi Rahayu',    [['Sisa Makanan', 9.0, false]]],
            ['2026-02-10', 'Fajar Nugroho',  [['Kertas HVS', 14.0, true]]],
            ['2026-02-14', 'Maya Puspitasari',[['Sisa Buah', 7.5, true]]],
            ['2026-02-20', 'Lina Marlina',   [['Sisa Makanan', 22.0, false]]],
            ['2026-02-24', 'Agus Pratama',   [['Kaleng Aluminium', 4.0, true], ['Besi Tua', 8.0, true]]],

            // March 2026
            ['2026-03-04', 'Budi Santoso',   [['Sisa Makanan', 11.0, true], ['Kardus', 9.0, true]]],
            ['2026-03-07', 'Hendra Wijaya',  [['Plastik PET', 6.5, true]]],
            ['2026-03-11', 'Yusuf Maulana',  [['Kertas HVS', 17.0, true], ['Sisa Sayur', 12.0, false]]],
            ['2026-03-15', 'Rina Kusumawati',[['Sisa Makanan', 14.0, false]]],
            ['2026-03-18', 'Intan Permata',  [['Kardus', 11.0, true]]],
            ['2026-03-22', 'Fajar Nugroho',  [['Kaleng Aluminium', 5.0, true]]],
            ['2026-03-26', 'Sulastri',       [['Sisa Buah', 8.0, true], ['Sisa Makanan', 18.0, false]]],

            // April 2026
            ['2026-04-02', 'Budi Santoso',   [['Plastik PET', 7.0, true], ['Sisa Sayur', 10.0, false]]],
            ['2026-04-05', 'Agus Pratama',   [['Kardus', 25.0, true]]],
            ['2026-04-09', 'Dewi Rahayu',    [['Sisa Makanan', 16.0, false]]],
            ['2026-04-13', 'Maya Puspitasari',[['Kertas HVS', 12.0, true]]],
            ['2026-04-17', 'Hendra Wijaya',  [['Besi Tua', 9.5, true], ['Sisa Makanan', 11.0, false]]],
            ['2026-04-21', 'Rudi Hartono',   [['Sisa Sayur', 13.0, false]]],
            ['2026-04-26', 'Farhan Ramadhan',[['Kaleng Aluminium', 4.5, true]]],

            // May 2026
            ['2026-05-05', 'Budi Santoso',   [['Sisa Makanan', 10.0, true], ['Plastik HDPE', 6.0, true]]],
            ['2026-05-08', 'Agus Pratama',   [['Kardus', 30.0, true], ['Kertas HVS', 8.0, true]]],
            ['2026-05-12', 'Siti Aminah',    [['Sisa Makanan', 20.0, false]]],
            ['2026-05-15', 'Nita Sari',      [['Sisa Sayur', 9.0, true]]],
            ['2026-05-18', 'Tono Saputra',   [['Sisa Makanan', 25.0, false]]],
            ['2026-05-20', 'Intan Permata',  [['Kaleng Aluminium', 6.0, true], ['Plastik PET', 8.0, true]]],
            ['2026-05-22', 'Hendra Wijaya',  [['Kardus', 14.0, true]]],
            ['2026-05-25', 'Sulastri',       [['Sisa Makanan', 30.0, false]]],
            ['2026-05-26', 'Rudi Hartono',   [['Kertas HVS', 16.0, true]]],
        ];

        foreach ($scenarios as [$tanggalStr, $wargaNama, $itemsDef]) {
            $wargaId = $this->wargaMap[$wargaNama] ?? null;
            if (!$wargaId) continue;

            $tanggal    = Carbon::parse($tanggalStr);
            $itemRows   = [];
            $totalNilai = 0.0;
            $totalKg    = 0.0;
            $kgDipilah  = 0.0;
            $kgTidak    = 0.0;

            foreach ($itemsDef as [$itemNama, $berat, $dipilah]) {
                $tarif    = $this->tarifMap[$itemNama] ?? null;
                $harga    = $tarif ? $tarif['harga'] : 0;
                $subtotal = $dipilah ? round($berat * $harga, 2) : round($berat * $this->tarifFlat * -1, 2);

                $totalNilai += $subtotal;
                $totalKg    += $berat;
                $dipilah ? ($kgDipilah += $berat) : ($kgTidak += $berat);

                $itemRows[] = [
                    'tarif_item_id'         => $dipilah && $tarif ? $tarif['id'] : null,
                    'riwayat_tarif_id'      => $dipilah && $tarif ? $tarif['riwayat_id'] : null,
                    'tipe_sampah'           => $dipilah && $tarif ? $tarif['tipe'] : null,
                    'status_pemilahan'      => $dipilah ? 'dipilah' : 'tidak_dipilah',
                    'berat_kg'              => $berat,
                    'harga_per_kg_saat_itu' => $dipilah ? $harga : $this->tarifFlat,
                    'subtotal'              => $subtotal,
                    'nama_penyetor'         => null,
                    'rw'                    => null,
                    'rt'                    => null,
                    'created_at'            => $tanggal,
                    'updated_at'            => $tanggal,
                ];
            }

            $setoranId = DB::table('setoran')->insertGetId([
                'warga_id'               => $wargaId,
                'petugas_id'             => $this->petugasId,
                'tanggal_setoran'        => $tanggal,
                'total_nilai'            => round(abs($totalNilai), 2),
                'nilai'                  => round($totalNilai, 2),
                'total_kg'               => $totalKg,
                'total_kg_dipilah'       => $kgDipilah,
                'total_kg_tidak_dipilah' => $kgTidak,
                'status_pembayaran'      => 'sudah_dibayar',
                'is_selesai'             => true,
                'mode'                   => 'detail',
                'sumber_input'           => 'manual',
                'area_rw'                => null,
                'catatan_kondisi'        => null,
                'created_at'             => $tanggal,
                'updated_at'             => $tanggal,
            ]);

            foreach ($itemRows as &$ir) { $ir['setoran_id'] = $setoranId; }
            DB::table('item_setoran')->insert($itemRows);

            if (abs($totalNilai) > 0) {
                $bayar = $tanggal->copy()->addHours(2);
                DB::table('pembayaran')->insert([
                    'setoran_id'          => $setoranId,
                    'petugas_pembayar_id' => $this->petugasId,
                    'tanggal_bayar'       => $bayar,
                    'jumlah_dibayar'      => round(abs($totalNilai), 2),
                    'catatan'             => null,
                    'created_at'          => $bayar,
                ]);
            }
        }
    }

    // ── Current month Jun 2026 ───────────────────────────────────────────────
    // Comprehensive: every payment status + both is_selesai states + all modes.

    private function seedCurrentMonth(): void
    {
        // ── 1. Sudah Dibayar Petugas — nilai > 0 — Selesai ───────────────────
        // Warga menyetor sampah dipilah, TPS sudah bayar ke warga.
        $this->insertWargaSetoran(
            wargaNama:  'Budi Santoso',
            tanggal:    '2026-06-01 08:30:00',
            items:      [['Sisa Makanan', 8.0, true], ['Plastik PET', 3.0, true]],
            statusBayar: 'sudah_dibayar',
            selesai:    true,
            bayarNotes: 'Diterima langsung.'
        );

        // ── 2. Belum Dibayar Warga — nilai < 0 — Draft ───────────────────────
        // Warga tidak memilah, belum bayar iuran, masih draft.
        $this->insertWargaSetoran(
            wargaNama:  'Siti Aminah',
            tanggal:    '2026-06-02 09:15:00',
            items:      [['Sisa Makanan', 25.0, false]],
            statusBayar: 'belum_dibayar',
            selesai:    false,
        );

        // ── 3. Belum Dibayar Petugas — nilai > 0 — Selesai ───────────────────
        // Warga memilah, data dicatat & selesai, tapi TPS belum bayar ke warga.
        $this->insertWargaSetoran(
            wargaNama:  'Agus Pratama',
            tanggal:    '2026-06-02 10:00:00',
            items:      [['Kardus', 15.0, true], ['Kertas HVS', 10.0, true]],
            statusBayar: 'belum_dibayar',
            selesai:    true,
        );

        // ── 4. Sudah Dibayar Warga — nilai < 0 — Selesai ─────────────────────
        // Warga tidak memilah, sudah bayar iuran ke TPS.
        $this->insertWargaSetoran(
            wargaNama:  'Dewi Rahayu',
            tanggal:    '2026-06-03 07:45:00',
            items:      [['Sisa Makanan', 30.0, false]],
            statusBayar: 'sudah_dibayar',
            selesai:    true,
            bayarNotes: 'Iuran diterima tunai.'
        );

        // ── 5. Tidak Ada Pembayaran — nilai = 0 — Selesai ────────────────────
        // Nilai dipilah dan iuran tepat saling hapus (net nol).
        $this->insertWargaSetoran(
            wargaNama:  'Tono Saputra',
            tanggal:    '2026-06-03 11:00:00',
            items:      [['Ampas Kopi', 6.0, true], ['Sisa Makanan', 6.0, false]],
            // 6×300=1800 dipilah, 6×500=3000 tidak_dipilah → nilai = 1800-3000 = -1200
            // Use forceZeroNilai below for a clean zero example
            statusBayar: 'belum_dibayar',
            selesai:    true,
            forceZeroNilai: true,
        );

        // ── 6. Belum Dibayar Warga — nilai < 0 — Draft — Campuran ────────────
        // Warga setoran campuran (sebagian dipilah, sebagian tidak), belum selesai.
        $this->insertWargaSetoran(
            wargaNama:  'Hendra Wijaya',
            tanggal:    '2026-06-04 08:00:00',
            items:      [['Daun Kering', 5.0, true], ['Sisa Makanan', 20.0, false]],
            statusBayar: 'belum_dibayar',
            selesai:    false,
        );

        // ── 7. Tambahan: Sudah Dibayar Warga — nilai < 0 — Selesai ───────────
        $this->insertWargaSetoran(
            wargaNama:  'Lina Marlina',
            tanggal:    '2026-06-01 14:00:00',
            items:      [['Sisa Sayur', 22.0, false]],
            statusBayar: 'sudah_dibayar',
            selesai:    true,
            bayarNotes: 'Bayar via kas RT.'
        );

        // ── 8. Belum Dibayar Warga — nilai < 0 — Selesai ─────────────────────
        $this->insertWargaSetoran(
            wargaNama:  'Maya Puspitasari',
            tanggal:    '2026-06-04 09:30:00',
            items:      [['Sisa Buah', 18.0, false]],
            statusBayar: 'belum_dibayar',
            selesai:    true,
        );

        // ─────────────────────────────────────────────────────────────────────
        // V5.1 PENYETOR MODE — Unregistered penyetors
        // ─────────────────────────────────────────────────────────────────────

        // ── 9. Sudah Dibayar Warga + Selesai (unregistered, tidak dipilah) ───
        $this->insertPenyetorSetoran(
            penyetorNama: 'Pak Warung',
            rw: '02', rt: '03',
            tanggal:     '2026-06-01 09:00:00',
            kgTidakDipilah: 45.0, kgDipilah: 0,
            statusBayar: 'sudah_dibayar',
            selesai:     true,
        );

        // ── 10. Belum Dibayar Warga + Draft (unregistered, tidak dipilah) ────
        $this->insertPenyetorSetoran(
            penyetorNama: 'Bu Sari Rahmawati',
            rw: '03', rt: '01',
            tanggal:     '2026-06-02 08:30:00',
            kgTidakDipilah: 18.0, kgDipilah: 0,
            statusBayar: 'belum_dibayar',
            selesai:     false,
        );

        // ── 11. Belum Dibayar Petugas + Draft (unregistered, dipilah) ─────────
        $this->insertPenyetorSetoran(
            penyetorNama: 'Toko Maju Jaya',
            rw: '01', rt: '02',
            tanggal:     '2026-06-03 10:00:00',
            kgTidakDipilah: 0, kgDipilah: 12.0,
            statusBayar: 'belum_dibayar',
            selesai:     false,
        );

        // ── 12. Sudah Dibayar Petugas + Selesai (unregistered, dipilah) ───────
        $this->insertPenyetorSetoran(
            penyetorNama: 'CV Daur Ulang',
            rw: '04', rt: '01',
            tanggal:     '2026-06-02 13:00:00',
            kgTidakDipilah: 0, kgDipilah: 28.0,
            statusBayar: 'sudah_dibayar',
            selesai:     true,
        );

        // ─────────────────────────────────────────────────────────────────────
        // V5.1 AGGREGATE MODE — per-area pickup without per-warga breakdown
        // ─────────────────────────────────────────────────────────────────────

        // ── 13. Sudah Dibayar Warga (aggregate) + Selesai ────────────────────
        $this->insertAgregatSetoran(
            areaRw:  '02 & 03',
            tanggal: '2026-06-01 07:00:00',
            totalKg: 217.0, kgDipilah: 0,
            statusBayar: 'sudah_dibayar',
            selesai: true,
        );

        // ── 14. Belum Dibayar Warga (aggregate) + Draft ──────────────────────
        $this->insertAgregatSetoran(
            areaRw:  '04',
            tanggal: '2026-06-04 06:30:00',
            totalKg: 95.0, kgDipilah: 0,
            statusBayar: 'belum_dibayar',
            selesai: false,
        );

        // ── 15. Sudah Dibayar Warga (aggregate, dipilah) + Selesai ───────────
        $this->insertAgregatSetoran(
            areaRw:  '05 & 06',
            tanggal: '2026-06-03 07:00:00',
            totalKg: 150.0, kgDipilah: 38.0,
            statusBayar: 'sudah_dibayar',
            selesai: true,
        );
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function insertWargaSetoran(
        string $wargaNama,
        string $tanggal,
        array  $items,         // [['TarifNama', berat, dipilah?], ...]
        string $statusBayar,
        bool   $selesai,
        ?string $bayarNotes   = null,
        bool   $forceZeroNilai = false,
    ): void {
        $wargaId = $this->wargaMap[$wargaNama] ?? null;
        if (!$wargaId) return;

        $ts        = Carbon::parse($tanggal);
        $itemRows  = [];
        $totalNilai = 0.0;
        $totalKg   = 0.0;
        $kgDipilah = 0.0;
        $kgTidak   = 0.0;

        foreach ($items as [$itemNama, $berat, $dipilah]) {
            $tarif    = $this->tarifMap[$itemNama] ?? null;
            $harga    = $dipilah ? ($tarif ? $tarif['harga'] : $this->tarifFlat) : $this->tarifFlat;
            $subtotal = $dipilah ? round($berat * $harga, 2) : round($berat * $harga * -1, 2);

            $totalNilai += $subtotal;
            $totalKg    += $berat;
            $dipilah ? ($kgDipilah += $berat) : ($kgTidak += $berat);

            $itemRows[] = [
                'tarif_item_id'         => $dipilah && $tarif ? $tarif['id'] : null,
                'riwayat_tarif_id'      => $dipilah && $tarif ? $tarif['riwayat_id'] : null,
                'tipe_sampah'           => $dipilah && $tarif ? $tarif['tipe'] : null,
                'status_pemilahan'      => $dipilah ? 'dipilah' : 'tidak_dipilah',
                'berat_kg'              => $berat,
                'harga_per_kg_saat_itu' => $harga,
                'subtotal'              => $subtotal,
                'nama_penyetor'         => null,
                'rw'                    => null,
                'rt'                    => null,
                'created_at'            => $ts,
                'updated_at'            => $ts,
            ];
        }

        if ($forceZeroNilai) {
            $totalNilai = 0;
        }

        $setoranId = DB::table('setoran')->insertGetId([
            'warga_id'               => $wargaId,
            'petugas_id'             => $this->petugasId,
            'tanggal_setoran'        => $ts,
            'total_nilai'            => round(abs($totalNilai), 2),
            'nilai'                  => round($totalNilai, 2),
            'total_kg'               => $totalKg,
            'total_kg_dipilah'       => $kgDipilah,
            'total_kg_tidak_dipilah' => $kgTidak,
            'status_pembayaran'      => $statusBayar,
            'is_selesai'             => $selesai,
            'mode'                   => 'detail',
            'sumber_input'           => 'manual',
            'area_rw'                => null,
            'catatan_kondisi'        => null,
            'created_at'             => $ts,
            'updated_at'             => $ts,
        ]);

        foreach ($itemRows as &$ir) { $ir['setoran_id'] = $setoranId; }
        DB::table('item_setoran')->insert($itemRows);

        if ($statusBayar === 'sudah_dibayar' && abs($totalNilai) > 0) {
            $bayar = $ts->copy()->addHours(1);
            DB::table('pembayaran')->insert([
                'setoran_id'          => $setoranId,
                'petugas_pembayar_id' => $this->petugasId,
                'tanggal_bayar'       => $bayar,
                'jumlah_dibayar'      => round(abs($totalNilai), 2),
                'catatan'             => $bayarNotes,
                'created_at'          => $bayar,
            ]);
        }
    }

    private function insertPenyetorSetoran(
        string $penyetorNama,
        string $rw,
        string $rt,
        string $tanggal,
        float  $kgTidakDipilah,
        float  $kgDipilah,
        string $statusBayar,
        bool   $selesai,
    ): void {
        $ts       = Carbon::parse($tanggal);
        $nilaiPos = round($kgDipilah * $this->tarifFlat, 2);
        $nilaiNeg = round($kgTidakDipilah * $this->tarifFlat * -1, 2);
        $nilai    = $nilaiPos + $nilaiNeg;
        $totalKg  = $kgDipilah + $kgTidakDipilah;
        $itemRows = [];

        if ($kgDipilah > 0) {
            $itemRows[] = [
                'tarif_item_id'         => null,
                'riwayat_tarif_id'      => null,
                'tipe_sampah'           => null,
                'status_pemilahan'      => 'dipilah',
                'berat_kg'              => $kgDipilah,
                'harga_per_kg_saat_itu' => $this->tarifFlat,
                'subtotal'              => $nilaiPos,
                'nama_penyetor'         => $penyetorNama,
                'rw'                    => $rw,
                'rt'                    => $rt,
                'created_at'            => $ts,
                'updated_at'            => $ts,
            ];
        }
        if ($kgTidakDipilah > 0) {
            $itemRows[] = [
                'tarif_item_id'         => null,
                'riwayat_tarif_id'      => null,
                'tipe_sampah'           => null,
                'status_pemilahan'      => 'tidak_dipilah',
                'berat_kg'              => $kgTidakDipilah,
                'harga_per_kg_saat_itu' => $this->tarifFlat,
                'subtotal'              => $nilaiNeg,
                'nama_penyetor'         => $penyetorNama,
                'rw'                    => $rw,
                'rt'                    => $rt,
                'created_at'            => $ts,
                'updated_at'            => $ts,
            ];
        }

        $setoranId = DB::table('setoran')->insertGetId([
            'warga_id'               => null,
            'petugas_id'             => $this->petugasId,
            'tanggal_setoran'        => $ts,
            'total_nilai'            => round(abs($nilai), 2),
            'nilai'                  => round($nilai, 2),
            'total_kg'               => $totalKg,
            'total_kg_dipilah'       => $kgDipilah,
            'total_kg_tidak_dipilah' => $kgTidakDipilah,
            'area_rw'                => str_pad($rw, 2, '0', STR_PAD_LEFT),
            'status_pembayaran'      => $statusBayar,
            'is_selesai'             => $selesai,
            'mode'                   => 'detail',
            'sumber_input'           => 'manual',
            'catatan_kondisi'        => null,
            'created_at'             => $ts,
            'updated_at'             => $ts,
        ]);

        foreach ($itemRows as &$ir) { $ir['setoran_id'] = $setoranId; }
        if ($itemRows) DB::table('item_setoran')->insert($itemRows);

        if ($statusBayar === 'sudah_dibayar' && abs($nilai) > 0) {
            $bayar = $ts->copy()->addHours(1);
            DB::table('pembayaran')->insert([
                'setoran_id'          => $setoranId,
                'petugas_pembayar_id' => $this->petugasId,
                'tanggal_bayar'       => $bayar,
                'jumlah_dibayar'      => round(abs($nilai), 2),
                'catatan'             => null,
                'created_at'          => $bayar,
            ]);
        }
    }

    private function insertAgregatSetoran(
        string $areaRw,
        string $tanggal,
        float  $totalKg,
        float  $kgDipilah,
        string $statusBayar,
        bool   $selesai,
    ): void {
        $ts        = Carbon::parse($tanggal);
        $kgTidak   = $totalKg - $kgDipilah;
        $nilaiPos  = round($kgDipilah * $this->tarifFlat, 2);
        $nilaiNeg  = round($kgTidak * $this->tarifFlat * -1, 2);
        $nilai     = $nilaiPos + $nilaiNeg;

        $setoranId = DB::table('setoran')->insertGetId([
            'warga_id'               => null,
            'petugas_id'             => $this->petugasId,
            'tanggal_setoran'        => $ts,
            'total_nilai'            => round(abs($nilai), 2),
            'nilai'                  => round($nilai, 2),
            'total_kg'               => $totalKg,
            'total_kg_dipilah'       => $kgDipilah,
            'total_kg_tidak_dipilah' => $kgTidak,
            'area_rw'                => $areaRw,
            'status_pembayaran'      => $statusBayar,
            'is_selesai'             => $selesai,
            'mode'                   => 'agregat',
            'sumber_input'           => 'manual',
            'catatan_kondisi'        => null,
            'created_at'             => $ts,
            'updated_at'             => $ts,
        ]);

        if ($statusBayar === 'sudah_dibayar' && abs($nilai) > 0) {
            $bayar = $ts->copy()->addHours(1);
            DB::table('pembayaran')->insert([
                'setoran_id'          => $setoranId,
                'petugas_pembayar_id' => $this->petugasId,
                'tanggal_bayar'       => $bayar,
                'jumlah_dibayar'      => round(abs($nilai), 2),
                'catatan'             => null,
                'created_at'          => $bayar,
            ]);
        }
    }
}
