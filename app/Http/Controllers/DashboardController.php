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
        $totalKgAll     = max($kpiBulanIni['total_kg'], 0.01);

        // ── Peringkat RW (dari semua data bulan ini) ───────────────
        $rwRanking = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('warga', 'warga.id', '=', 'setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [
                $bulan->copy()->startOfMonth(),
                $bulan->copy()->endOfMonth(),
            ])
            ->select([
                'warga.rw',
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
            ])
            ->groupBy('warga.rw')
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
            $m    = Carbon::now()->subMonths($i)->startOfMonth();
            $data = $this->kpiMonth($m);
            $tren[] = [
                'bulan'       => $m->translatedFormat('F'),
                'total_kg'    => $data['total_kg'],
                'persen_dipilah' => $data['persen_dipilah'],
                'pencairan'   => $data['pencairan'],
                'warga_aktif' => DB::table('warga')->where('status_keanggotaan', 'aktif')->count(),
            ];
        }

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
