<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $bulan     = Carbon::now()->startOfMonth();
        $bulanLalu = $bulan->copy()->subMonth();

        // ── KPI bulan ini ──────────────────────────────────────────
        $kpiBulanIni  = $this->kpiMonth($bulan);
        $kpiBulanLalu = $this->kpiMonth($bulanLalu);

        $totalKgDelta = $kpiBulanLalu['total_kg'] > 0
            ? round((($kpiBulanIni['total_kg'] - $kpiBulanLalu['total_kg']) / $kpiBulanLalu['total_kg']) * 100, 1)
            : null;

        // ── Warga aktif ────────────────────────────────────────────
        $wargaAktif = DB::table('warga')->where('status_keanggotaan', 'aktif')->count();

        // ── Komposisi sampah bulan ini ─────────────────────────────
        // From old-model item_setoran (has per-type breakdown)
        $komposisi = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->whereBetween('setoran.tanggal_setoran', [
                $bulan->copy()->startOfMonth(),
                $bulan->copy()->endOfMonth(),
            ])
            ->select([
                'item_setoran.tipe_sampah',
                'item_setoran.status_pemilahan',
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
            ])
            ->groupBy('item_setoran.tipe_sampah', 'item_setoran.status_pemilahan')
            ->get();

        $kgOrganik      = $komposisi->where('tipe_sampah', 'organik')->sum('total_kg');
        $kgAnorganik    = $komposisi->where('tipe_sampah', 'anorganik')->sum('total_kg');
        $kgTidakDipilah = $komposisi->where('status_pemilahan', 'tidak_dipilah')->sum('total_kg');
        $kgTidakDicatat = $komposisi->whereNull('status_pemilahan')->sum('total_kg');

        // Add v5.1 setoran totals (no per-item type data)
        $v5Totals = DB::table('setoran')
            ->whereNull('warga_id')
            ->whereBetween('tanggal_setoran', [
                $bulan->copy()->startOfMonth(),
                $bulan->copy()->endOfMonth(),
            ])
            ->selectRaw('SUM(COALESCE(total_kg_tidak_dipilah, 0)) as sum_tidak_dipilah')
            ->first();
        $kgTidakDipilah += (float) ($v5Totals->sum_tidak_dipilah ?? 0);

        $totalKgAll = max($kpiBulanIni['total_kg'], 0.01);

        // ── Peringkat RW (5 teratas) ───────────────────────────────
        // Query 1: warga-linked setoran (old model + v5.1 penyetor single warga)
        $rwWarga = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('warga', 'warga.id', '=', 'setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [
                $bulan->copy()->startOfMonth(),
                $bulan->copy()->endOfMonth(),
            ])
            ->select([
                DB::raw("CONCAT('RW ', LPAD(CAST(warga.rw AS CHAR), 2, '0')) as rw_display"),
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
            ])
            ->groupBy('warga.rw');

        // Query 2: v5.1 area_rw setoran (aggregate / multi-penyetor)
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
            ->whereBetween('setoran.tanggal_setoran', [
                $bulan->copy()->startOfMonth(),
                $bulan->copy()->endOfMonth(),
            ])
            ->select([
                DB::raw("COALESCE(NULLIF(TRIM(setoran.area_rw), ''), 'Area lain') as rw_display"),
                DB::raw('SUM(COALESCE(setoran.total_kg, items_agg.sum_kg, 0)) as total_kg'),
                DB::raw('SUM(COALESCE(setoran.total_kg_dipilah, items_agg.sum_dipilah, 0)) as kg_dipilah'),
            ])
            ->groupBy('setoran.area_rw');

        $rwRanking = $rwWarga->unionAll($rwAreaRw)
            ->orderByDesc('kg_dipilah')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $row->persen_dipilah = $row->total_kg > 0
                    ? round(($row->kg_dipilah / $row->total_kg) * 100)
                    : 0;
                return $row;
            });

        // ── Tren 5 bulan ───────────────────────────────────────────
        $tren = [];
        for ($i = 4; $i >= 0; $i--) {
            $m      = Carbon::now()->subMonths($i)->startOfMonth();
            $data   = $this->kpiMonth($m);
            $tren[] = [
                'bulan'          => $m->translatedFormat('F'),
                'total_kg'       => $data['total_kg'],
                'persen_dipilah' => $data['persen_dipilah'],
                'pencairan'      => $data['pencairan'],
                'warga_aktif'    => $wargaAktif,
            ];
        }

        // ── Top 5 warga bulan ini (leaderboard preview) ───────────
        $awalBulan  = $bulan->copy()->startOfMonth();
        $akhirBulan = $bulan->copy()->endOfMonth();

        $topWarga = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('warga', 'warga.id', '=', 'setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [$awalBulan, $akhirBulan])
            ->select([
                'warga.nama',
                'warga.rw',
                'warga.dusun',
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
            ])
            ->groupBy('warga.id', 'warga.nama', 'warga.rw', 'warga.dusun')
            ->get()
            ->map(function ($row) {
                $row->persen_dipilah = $row->total_kg > 0
                    ? round(($row->kg_dipilah / $row->total_kg) * 100, 1)
                    : 0;
                $row->poin = round(($row->kg_dipilah * 10) + ($row->persen_dipilah * 2), 0);
                return $row;
            })
            ->sortByDesc('poin')
            ->take(5)
            ->values();

        // ── Status pembayaran keseluruhan ──────────────────────────
        $statusBayar = DB::table('setoran')
            ->select('status_pembayaran', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('status_pembayaran')
            ->pluck('jumlah', 'status_pembayaran');

        $nilaiTertunda = DB::table('setoran')
            ->where('status_pembayaran', 'belum_dibayar')
            ->sum('total_nilai');

        return view('sips.dashboard.index', [
            'kpi'            => $kpiBulanIni,
            'totalKgDelta'   => $totalKgDelta,
            'wargaAktif'     => $wargaAktif,
            'kgOrganik'      => $kgOrganik,
            'kgAnorganik'    => $kgAnorganik,
            'kgTidakDipilah' => $kgTidakDipilah,
            'kgTidakDicatat' => $kgTidakDicatat,
            'totalKgAll'     => $totalKgAll,
            'rwRanking'      => $rwRanking,
            'topWarga'       => $topWarga,
            'tren'           => $tren,
            'statusBayar'    => $statusBayar,
            'nilaiTertunda'  => $nilaiTertunda,
            'periodeBulan'   => Carbon::now()->translatedFormat('F Y'),
        ]);
    }

    private function kpiMonth(Carbon $bulan): array
    {
        $awal  = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        // Use setoran as primary source (handles v5.1 aggregate mode).
        // Fall back to item_setoran aggregates for old records where total_kg = NULL.
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

        // Pencairan from confirmed payments (old model)
        $pencairan = DB::table('pembayaran')
            ->join('setoran', 'setoran.id', '=', 'pembayaran.setoran_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->sum('pembayaran.jumlah_dibayar');

        // V5.1: selesai setoran without a pembayaran record
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
