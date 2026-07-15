<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Period selection — default to current month
        $selectedBulanParam = $request->get('bulan'); // 'YYYY-MM'
        $bulan = $selectedBulanParam
            ? Carbon::createFromFormat('Y-m', $selectedBulanParam)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $bulanLalu = $bulan->copy()->subMonth();

        // ── KPI for selected period ────────────────────────────────
        $kpiBulanIni  = $this->kpiMonth($bulan);
        $kpiBulanLalu = $this->kpiMonth($bulanLalu);

        $totalKgDelta = $kpiBulanLalu['total_kg'] > 0
            ? round((($kpiBulanIni['total_kg'] - $kpiBulanLalu['total_kg']) / $kpiBulanLalu['total_kg']) * 100, 1)
            : null;

        // ── Warga aktif ────────────────────────────────────────────
        $wargaAktif = DB::table('warga')->where('status_keanggotaan', 'aktif')->count();

        // ── Komposisi sampah periode terpilih ──────────────────────
        $kgDipilah      = (float) $kpiBulanIni['kg_dipilah'];
        $kgTidakDipilah = max(0, (float) $kpiBulanIni['total_kg'] - $kgDipilah);
        $totalKgAll     = max($kpiBulanIni['total_kg'], 0.01);

        // ── Peringkat RW (5 teratas, periode terpilih) ─────────────
        $awalBulan  = $bulan->copy()->startOfMonth();
        $akhirBulan = $bulan->copy()->endOfMonth();

        $rwWarga = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('warga', 'warga.id', '=', 'setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [$awalBulan, $akhirBulan])
            ->select([
                DB::raw("CONCAT('RW ', LPAD(CAST(warga.rw AS CHAR), 2, '0')) as rw_display"),
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
            ])
            ->groupBy('warga.rw');

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
            ->whereBetween('setoran.tanggal_setoran', [$awalBulan, $akhirBulan])
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

        // ── Top 5 warga periode terpilih ───────────────────────────
        $topWarga = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('warga', 'warga.id', '=', 'setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [$awalBulan, $akhirBulan])
            ->select([
                'warga.nama',
                'warga.alamat',
                'warga.rw',
                'warga.dusun',
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
            ])
            ->groupBy('warga.id', 'warga.nama', 'warga.alamat', 'warga.rw', 'warga.dusun')
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

        // ── Tren 5 bulan (berakhir di periode terpilih) ────────────
        $tren = [];
        for ($i = 4; $i >= 0; $i--) {
            $m    = $bulan->copy()->subMonths($i)->startOfMonth();
            $data = $this->kpiMonth($m);
            $tren[] = [
                'bulan'          => $m->translatedFormat('M Y'),
                'total_kg'       => $data['total_kg'],
                'kg_dipilah'     => $data['kg_dipilah'],
                'kg_tidak'       => max(0, $data['total_kg'] - $data['kg_dipilah']),
                'persen_dipilah' => $data['persen_dipilah'],
                'pencairan'      => $data['pencairan'],
                'warga_aktif'    => $wargaAktif,
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

        // ── OCR flat-rate items needing review ─────────────────────
        $flatRateUnreviewed = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->where('setoran.sumber_input', 'ocr')
            ->where('item_setoran.status_pemilahan', 'dipilah')
            ->whereNull('item_setoran.tarif_item_id')
            ->whereNotNull('item_setoran.catatan_item')
            ->where('item_setoran.catatan_item', 'like', '%flat rate%')
            ->count();

        // ── Available months for period picker (last 12 months) ────
        $availableMonths = [];
        for ($i = 0; $i < 12; $i++) {
            $m = Carbon::now()->subMonths($i)->startOfMonth();
            $availableMonths[] = [
                'value' => $m->format('Y-m'),
                'label' => $m->translatedFormat('F Y'),
            ];
        }

        return view('sips.dashboard.index', [
            'kpi'                => $kpiBulanIni,
            'totalKgDelta'       => $totalKgDelta,
            'wargaAktif'         => $wargaAktif,
            'kgDipilah'          => $kgDipilah,
            'kgTidakDipilah'     => $kgTidakDipilah,
            'totalKgAll'         => $totalKgAll,
            'rwRanking'          => $rwRanking,
            'topWarga'           => $topWarga,
            'tren'               => $tren,
            'statusBayar'        => $statusBayar,
            'nilaiTertunda'      => $nilaiTertunda,
            'flatRateUnreviewed' => $flatRateUnreviewed,
            'periodeBulan'       => $bulan->translatedFormat('F Y'),
            'selectedBulan'      => $bulan->format('Y-m'),
            'availableMonths'    => $availableMonths,
        ]);
    }

    private function kpiMonth(Carbon $bulan): array
    {
        $awal  = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

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

        $pencairan = DB::table('pembayaran')
            ->join('setoran', 'setoran.id', '=', 'pembayaran.setoran_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->sum('pembayaran.jumlah_dibayar');

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
