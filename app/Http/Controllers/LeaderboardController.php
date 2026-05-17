<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function sips(Request $request)
    {
        $bulan = $request->filled('bulan')
            ? Carbon::parse($request->bulan)->startOfMonth()
            : Carbon::now()->startOfMonth();

        [$entries, $rws] = $this->computeRankings($bulan);

        $wargaAktif     = DB::table('warga')->where('status_keanggotaan', 'aktif')->count();
        $totalKgDipilah = $entries->sum('kg_dipilah');
        $rwTerbaik      = $rws->first();
        $rataRataPoin   = $entries->take(10)->avg('poin');

        // Build list of available months for the filter (months with any setoran)
        $availableMonths = DB::table('setoran')
            ->selectRaw('DATE_FORMAT(tanggal_setoran, "%Y-%m-01") as bulan_str')
            ->groupByRaw('DATE_FORMAT(tanggal_setoran, "%Y-%m-01")')
            ->orderByDesc('bulan_str')
            ->pluck('bulan_str')
            ->map(fn($m) => Carbon::parse($m));

        return view('sips.leaderboard.index', [
            'entries'        => $entries,
            'rws'            => $rws,
            'wargaAktif'     => $wargaAktif,
            'totalKgDipilah' => $totalKgDipilah,
            'rwTerbaik'      => $rwTerbaik,
            'rataRataPoin'   => round($rataRataPoin ?? 0),
            'bulanDipilih'   => $bulan,
            'availableMonths' => $availableMonths,
        ]);
    }

    public function index()
    {
        $bulan = Carbon::now()->startOfMonth();
        [$entries, $rws] = $this->computeRankings($bulan);

        $wargaAktif     = DB::table('warga')->where('status_keanggotaan', 'aktif')->count();
        $totalKgDipilah = $entries->sum('kg_dipilah');
        $rwTerbaik      = $rws->first();

        return view('public.leaderboard.index', [
            'entries'        => $entries,
            'rws'            => $rws,
            'wargaAktif'     => $wargaAktif,
            'totalKgDipilah' => $totalKgDipilah,
            'rwTerbaik'      => $rwTerbaik,
            'periodeBulan'   => $bulan->translatedFormat('F Y'),
        ]);
    }

    private function computeRankings(Carbon $bulan): array
    {
        $awal  = $bulan->copy()->startOfMonth();
        $akhir = $bulan->copy()->endOfMonth();

        $entries = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('warga', 'warga.id', '=', 'setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->select([
                'warga.id',
                'warga.nama',
                'warga.rt',
                'warga.rw',
                'warga.dusun',
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
            ])
            ->groupBy('warga.id', 'warga.nama', 'warga.rt', 'warga.rw', 'warga.dusun')
            ->get()
            ->map(function ($row) {
                $row->persen_dipilah = $row->total_kg > 0
                    ? round(($row->kg_dipilah / $row->total_kg) * 100, 1)
                    : 0;
                $row->poin = round(($row->kg_dipilah * 10) + ($row->persen_dipilah * 2), 0);
                return $row;
            })
            ->sortByDesc('poin')
            ->values();

        $rws = DB::table('item_setoran')
            ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
            ->join('warga', 'warga.id', '=', 'setoran.warga_id')
            ->whereBetween('setoran.tanggal_setoran', [$awal, $akhir])
            ->select([
                'warga.rw',
                'warga.dusun',
                DB::raw('COUNT(DISTINCT warga.id) as jumlah_warga'),
                DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
            ])
            ->groupBy('warga.rw', 'warga.dusun')
            ->get()
            ->map(function ($row) {
                $row->persen_dipilah = $row->total_kg > 0
                    ? round(($row->kg_dipilah / $row->total_kg) * 100)
                    : 0;
                return $row;
            })
            ->sortByDesc('persen_dipilah')
            ->values();

        return [$entries, $rws];
    }
}
