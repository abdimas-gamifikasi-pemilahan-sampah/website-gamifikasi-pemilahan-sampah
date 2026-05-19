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
        $trenLabels      = [];
        $trenTotalKg     = [];
        $trenDipilah     = [];
        $trenPencairan   = [];
        $trenPersen      = [];

        for ($i = 11; $i >= 0; $i--) {
            $m    = Carbon::now()->subMonths($i)->startOfMonth();
            $data = $this->kpiMonth($m);

            $trenLabels[]    = $m->translatedFormat('M Y');
            $trenTotalKg[]   = round($data['total_kg'], 1);
            $trenDipilah[]   = round($data['kg_dipilah'], 1);
            $trenPencairan[] = round($data['pencairan'] / 1000, 1); // ribu rupiah
            $trenPersen[]    = $data['persen_dipilah'];
        }

        // ── Per-item breakdown (bulan dipilih) ────────────────────
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

        // ── Per-RW breakdown (bulan dipilih) ───────────────────────
        $rwData = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('warga', 'warga.id', '=', 'setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->select([
                DB::raw("CONCAT('RW ', warga.rw, ' / ', warga.dusun) as label"),
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
                DB::raw('COUNT(DISTINCT warga.id) as jumlah_warga'),
            ])
            ->groupBy('warga.rw', 'warga.dusun')
            ->orderByDesc('kg_dipilah')
            ->get()
            ->map(function ($row) {
                $row->persen = $row->total_kg > 0
                    ? round(($row->kg_dipilah / $row->total_kg) * 100)
                    : 0;
                return $row;
            });

        // ── Status pembayaran (bulan dipilih) ──────────────────────
        $statusBayar = DB::table('setoran')
            ->whereBetween('tanggal_setoran', [$awal, $akhir])
            ->select('status_pembayaran', DB::raw('COUNT(*) as jumlah'), DB::raw('SUM(total_nilai) as total_nilai'))
            ->groupBy('status_pembayaran')
            ->get();

        $sudahBayar  = $statusBayar->where('status_pembayaran', 'sudah_dibayar')->first();
        $belumBayar  = $statusBayar->where('status_pembayaran', 'belum_dibayar')->first();
        $nilaiTerbayar  = (float) ($sudahBayar->total_nilai ?? 0);
        $nilaiTertunda  = (float) ($belumBayar->total_nilai ?? 0);

        // ── Top 10 warga (bulan dipilih) ───────────────────────────
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
        $wargaAktif       = DB::table('warga')->where('status_keanggotaan', 'aktif')->count();
        $wargaBerkontribusi = DB::table('setoran')
            ->whereBetween('tanggal_setoran', [$awal, $akhir])
            ->distinct('warga_id')
            ->count('warga_id');

        $kpiBulan = $this->kpiMonth($bulan);

        return view('sips.analitik.index', [
            'bulanDipilih'       => $bulan,
            'availableMonths'    => $availableMonths,
            'kpi'                => $kpiBulan,
            // Tren chart data (12 months, JSON-encoded)
            'trenLabels'         => $trenLabels,
            'trenTotalKg'        => $trenTotalKg,
            'trenDipilah'        => $trenDipilah,
            'trenPencairan'      => $trenPencairan,
            'trenPersen'         => $trenPersen,
            // Per-item chart
            'perItem'            => $perItem,
            // Komposisi donut
            'kgOrganik'          => $kgOrganik,
            'kgAnorganik'        => $kgAnorganik,
            'kgTidakDipilah'     => $kgTidakDipilah,
            // Per-RW bar
            'rwData'             => $rwData,
            // Payment status
            'nilaiTerbayar'      => $nilaiTerbayar,
            'nilaiTertunda'      => $nilaiTertunda,
            'jumlahSudah'        => (int) ($sudahBayar->jumlah ?? 0),
            'jumlahBelum'        => (int) ($belumBayar->jumlah ?? 0),
            // Top warga table
            'topWarga'           => $topWarga,
            // Partisipasi
            'wargaAktif'         => $wargaAktif,
            'wargaBerkontribusi' => $wargaBerkontribusi,
        ]);
    }

    private function kpiMonth(Carbon $bulan): array
    {
        $awal  = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        $data = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->select([
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
            ])
            ->first();

        $totalKg   = (float) ($data->total_kg ?? 0);
        $kgDipilah = (float) ($data->kg_dipilah ?? 0);

        $pencairan = DB::table('pembayaran')
            ->join('setoran', 'setoran.id', '=', 'pembayaran.setoran_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->sum('pembayaran.jumlah_dibayar');

        $totalSetoran = DB::table('setoran')
            ->whereBetween('tanggal_setoran', [$awal, $akhir])
            ->count();

        return [
            'total_kg'       => $totalKg,
            'kg_dipilah'     => $kgDipilah,
            'persen_dipilah' => $totalKg > 0 ? round(($kgDipilah / $totalKg) * 100) : 0,
            'pencairan'      => (float) $pencairan,
            'total_setoran'  => $totalSetoran,
        ];
    }
}
