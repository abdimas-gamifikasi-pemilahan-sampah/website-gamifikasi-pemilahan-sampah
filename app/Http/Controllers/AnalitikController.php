<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalitikController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->filled('bulan')
            ? Carbon::parse($request->bulan)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $awal  = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        // ── Available months for filter ────────────────────────────
        $availableMonths = DB::table('setoran')
            ->selectRaw('DATE_FORMAT(tanggal_setoran, "%Y-%m-01") as bulan_str')
            ->groupByRaw('DATE_FORMAT(tanggal_setoran, "%Y-%m-01")')
            ->orderByDesc('bulan_str')
            ->pluck('bulan_str')
            ->map(fn($m) => Carbon::parse($m));

        // ── Tren 12 bulan ──────────────────────────────────────────
        $trenLabels    = [];
        $trenTotalKg   = [];
        $trenDipilah   = [];
        $trenPencairan = [];
        $trenPersen    = [];

        for ($i = 11; $i >= 0; $i--) {
            $m    = Carbon::now()->subMonths($i)->startOfMonth();
            $data = $this->kpiMonth($m);

            $trenLabels[]    = $m->translatedFormat('M Y');
            $trenTotalKg[]   = round($data['total_kg'], 1);
            $trenDipilah[]   = round($data['kg_dipilah'], 1);
            $trenPencairan[] = round($data['pencairan'] / 1000, 1); // ribu rupiah
            $trenPersen[]    = $data['persen_dipilah'];
        }

        // ── Per-item breakdown (bulan dipilih) ─────────────────────
        // Only for setoran that have item_setoran rows with tarif_item link
        $perItem = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('tarif_items', 'tarif_items.id', '=', 'item_setoran.tarif_item_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->where('item_setoran.status_pemilahan', 'dipilah')
            ->select([
                'tarif_items.nama_item',
                'tarif_items.tipe_sampah',
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw('COUNT(DISTINCT setoran.id) as jumlah_setoran'),
            ])
            ->groupBy('tarif_items.id', 'tarif_items.nama_item', 'tarif_items.tipe_sampah')
            ->orderByDesc('total_kg')
            ->get();

        // ── Komposisi sampah (bulan dipilih) ───────────────────────
        // From old-model item_setoran (has per-type data)
        $komposisi = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->select([
                'item_setoran.tipe_sampah',
                'item_setoran.status_pemilahan',
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
            ])
            ->groupBy('item_setoran.tipe_sampah', 'item_setoran.status_pemilahan')
            ->get();

        $kgOrganik      = (float) $komposisi->where('tipe_sampah', 'organik')->sum('total_kg');
        $kgAnorganik    = (float) $komposisi->where('tipe_sampah', 'anorganik')->sum('total_kg');
        $kgTidakDipilah = (float) $komposisi->where('status_pemilahan', 'tidak_dipilah')->sum('total_kg');

        // Add v5.1 setoran totals (aggregate/penyetor without per-item type breakdown)
        $v5Totals = DB::table('setoran')
            ->whereNull('warga_id')
            ->whereBetween('tanggal_setoran', [$awal, $akhir])
            ->selectRaw('SUM(COALESCE(total_kg_dipilah, 0)) as sum_dipilah')
            ->selectRaw('SUM(COALESCE(total_kg_tidak_dipilah, 0)) as sum_tidak_dipilah')
            ->first();

        $kgTidakDipilah  += (float) ($v5Totals->sum_tidak_dipilah ?? 0);
        $kgDipilahAgregat = (float) ($v5Totals->sum_dipilah ?? 0); // dipilah without organik/anorganik split

        // ── Per-RW breakdown (bulan dipilih) ───────────────────────
        // Query 1: warga-linked setoran (old model + v5.1 penyetor with single warga)
        $rwWarga = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('warga', 'warga.id', '=', 'setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->select([
                DB::raw("CONCAT('RW ', LPAD(CAST(warga.rw AS CHAR), 2, '0'), ' / ', warga.dusun) as label"),
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
                DB::raw('COUNT(DISTINCT warga.id) as jumlah_warga'),
            ])
            ->groupBy('warga.rw', 'warga.dusun');

        // Query 2: v5.1 setoran with area_rw (aggregate mode, multi-penyetor)
        $rwAreaRw = DB::table('setoran')
            ->leftJoinSub(
                DB::table('item_setoran')
                    ->select('setoran_id')
                    ->selectRaw('SUM(berat_kg) as sum_kg')
                    ->selectRaw("SUM(CASE WHEN status_pemilahan = 'dipilah' THEN berat_kg ELSE 0 END) as sum_dipilah")
                    ->groupBy('setoran_id'),
                'items_agg',
                fn($j) => $j->on('items_agg.setoran_id', '=', 'setoran.id')
            )
            ->whereNull('setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->select([
                DB::raw("COALESCE(NULLIF(TRIM(setoran.area_rw), ''), 'Tidak Dicatat') as label"),
                DB::raw('SUM(COALESCE(setoran.total_kg, items_agg.sum_kg, 0)) as total_kg'),
                DB::raw('SUM(COALESCE(setoran.total_kg_dipilah, items_agg.sum_dipilah, 0)) as kg_dipilah'),
                DB::raw('0 as jumlah_warga'),
            ])
            ->groupBy('setoran.area_rw');

        $rwData = $rwWarga->unionAll($rwAreaRw)
            ->get()
            ->map(function ($row) {
                $row->persen = $row->total_kg > 0
                    ? round(($row->kg_dipilah / $row->total_kg) * 100)
                    : 0;
                return $row;
            })
            ->sortByDesc('total_kg')
            ->values();

        // ── Status pembayaran (bulan dipilih) ──────────────────────
        $statusBayar = DB::table('setoran')
            ->whereBetween('tanggal_setoran', [$awal, $akhir])
            ->select('status_pembayaran', DB::raw('COUNT(*) as jumlah'), DB::raw('SUM(total_nilai) as total_nilai'))
            ->groupBy('status_pembayaran')
            ->get();

        $sudahBayar    = $statusBayar->where('status_pembayaran', 'sudah_dibayar')->first();
        $belumBayar    = $statusBayar->where('status_pembayaran', 'belum_dibayar')->first();
        $nilaiTerbayar = (float) ($sudahBayar->total_nilai ?? 0);
        $nilaiTertunda = (float) ($belumBayar->total_nilai ?? 0);

        // Also include v5.1 selesai setoran in nilai terbayar
        $nilaiSelesaiV5 = DB::table('setoran')
            ->whereBetween('tanggal_setoran', [$awal, $akhir])
            ->where('is_selesai', true)
            ->where('status_pembayaran', '!=', 'sudah_dibayar')
            ->sum('total_nilai');
        $nilaiTerbayar += (float) $nilaiSelesaiV5;

        // ── Top 10 warga (bulan dipilih) — warga-linked only ───────
        $topWarga = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('warga', 'warga.id', '=', 'setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->select([
                'warga.nama',
                'warga.rt',
                'warga.rw',
                'warga.dusun',
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
                DB::raw('COUNT(DISTINCT setoran.id) as jumlah_setoran'),
            ])
            ->groupBy('warga.id', 'warga.nama', 'warga.rt', 'warga.rw', 'warga.dusun')
            ->orderByDesc('kg_dipilah')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $row->persen = $row->total_kg > 0
                    ? round(($row->kg_dipilah / $row->total_kg) * 100, 1)
                    : 0;
                return $row;
            });

        // ── Partisipasi warga (bulan ini vs aktif) ─────────────────
        $wargaAktif         = DB::table('warga')->where('status_keanggotaan', 'aktif')->count();
        $wargaBerkontribusi = DB::table('setoran')
            ->whereBetween('tanggal_setoran', [$awal, $akhir])
            ->distinct('warga_id')
            ->count('warga_id');

        // V5.1 pickup trips without individual warga data
        $pengangkutanAgregat = DB::table('setoran')
            ->whereNull('warga_id')
            ->whereBetween('tanggal_setoran', [$awal, $akhir])
            ->count();

        $kpiBulan = $this->kpiMonth($bulan);

        return view('sips.analitik.index', [
            'bulanDipilih'        => $bulan,
            'availableMonths'     => $availableMonths,
            'kpi'                 => $kpiBulan,
            // Tren chart data (12 months)
            'trenLabels'          => $trenLabels,
            'trenTotalKg'         => $trenTotalKg,
            'trenDipilah'         => $trenDipilah,
            'trenPencairan'       => $trenPencairan,
            'trenPersen'          => $trenPersen,
            // Per-item chart (old model / penyetor dipilah)
            'perItem'             => $perItem,
            // Komposisi donut
            'kgOrganik'           => $kgOrganik,
            'kgAnorganik'         => $kgAnorganik,
            'kgTidakDipilah'      => $kgTidakDipilah,
            'kgDipilahAgregat'    => $kgDipilahAgregat, // v5.1 dipilah without per-type breakdown
            // Per-RW bar (both models)
            'rwData'              => $rwData,
            // Payment status
            'nilaiTerbayar'       => $nilaiTerbayar,
            'nilaiTertunda'       => $nilaiTertunda,
            'jumlahSudah'         => (int) ($sudahBayar->jumlah ?? 0),
            'jumlahBelum'         => (int) ($belumBayar->jumlah ?? 0),
            // Top warga table (warga-linked only)
            'topWarga'            => $topWarga,
            // Partisipasi
            'wargaAktif'          => $wargaAktif,
            'wargaBerkontribusi'  => $wargaBerkontribusi,
            'pengangkutanAgregat' => $pengangkutanAgregat,
        ]);
    }

    private function kpiMonth(Carbon $bulan): array
    {
        $awal  = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        // Use setoran as primary source.
        // For old records (total_kg = NULL): fall back to SUM from item_setoran.
        // For v5.1 records (total_kg set): use directly.
        $data = DB::table('setoran')
            ->leftJoinSub(
                DB::table('item_setoran')
                    ->select('setoran_id')
                    ->selectRaw('SUM(berat_kg) as sum_kg')
                    ->selectRaw("SUM(CASE WHEN status_pemilahan = 'dipilah' THEN berat_kg ELSE 0 END) as sum_dipilah")
                    ->groupBy('setoran_id'),
                'items_agg',
                fn($j) => $j->on('items_agg.setoran_id', '=', 'setoran.id')
            )
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->selectRaw('SUM(COALESCE(setoran.total_kg, items_agg.sum_kg, 0)) as total_kg')
            ->selectRaw('SUM(COALESCE(setoran.total_kg_dipilah, items_agg.sum_dipilah, 0)) as kg_dipilah')
            ->first();

        $totalKg   = (float) ($data->total_kg ?? 0);
        $kgDipilah = (float) ($data->kg_dipilah ?? 0);

        // Pencairan: actual payments from old model (pembayaran table)
        $pencairan = DB::table('pembayaran')
            ->join('setoran', 'setoran.id', '=', 'pembayaran.setoran_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->sum('pembayaran.jumlah_dibayar');

        // V5.1: selesai setoran that have no pembayaran record (flat-rate payments)
        $pencairanV5 = DB::table('setoran')
            ->whereBetween('tanggal_setoran', [$awal, $akhir])
            ->where('is_selesai', true)
            ->whereNotExists(
                fn($q) => $q->from('pembayaran')->whereColumn('pembayaran.setoran_id', 'setoran.id')
            )
            ->sum('total_nilai');

        $totalSetoran = DB::table('setoran')
            ->whereBetween('tanggal_setoran', [$awal, $akhir])
            ->count();

        return [
            'total_kg'       => $totalKg,
            'kg_dipilah'     => $kgDipilah,
            'persen_dipilah' => $totalKg > 0 ? round(($kgDipilah / $totalKg) * 100) : 0,
            'pencairan'      => (float) $pencairan + (float) $pencairanV5,
            'total_setoran'  => $totalSetoran,
        ];
    }
}
