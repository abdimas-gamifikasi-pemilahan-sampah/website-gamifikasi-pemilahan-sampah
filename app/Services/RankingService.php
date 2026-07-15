<?php

namespace App\Services;

use App\Models\Setoran;
use App\Models\SnapshotPeringkatBulanan;
use App\Models\Warga;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RankingService
{
    /**
     * Compute and persist snapshots for all active warga for a given month.
     * $bulan should be the first day of the target month (e.g. Carbon::parse('2026-05-01')).
     */
    public function computeMonth(Carbon $bulan): void
    {
        $bulanStr  = $bulan->format('Y-m-01');
        $bulanAwal = Carbon::parse($bulanStr)->startOfMonth();
        $bulanAkhir = Carbon::parse($bulanStr)->endOfMonth();

        $wargaList = Warga::where('status_keanggotaan', 'aktif')->get();

        foreach ($wargaList as $warga) {
            $items = DB::table('item_setoran')
                ->join('setoran', 'setoran.id', '=', 'item_setoran.setoran_id')
                ->where('setoran.warga_id', $warga->id)
                ->whereBetween('setoran.tanggal_setoran', [$bulanAwal, $bulanAkhir])
                ->select([
                    DB::raw('SUM(item_setoran.berat_kg) as total_kg'),
                    DB::raw("SUM(CASE WHEN item_setoran.status_pemilahan = 'dipilah' THEN item_setoran.berat_kg ELSE 0 END) as kg_dipilah"),
                ])
                ->first();

            $totalKg  = (float) ($items->total_kg ?? 0);
            $kgDipilah = (float) ($items->kg_dipilah ?? 0);
            $persenDipilah = $totalKg > 0 ? round(($kgDipilah / $totalKg) * 100, 2) : 0;
            $bulanBerturut = $totalKg > 0 ? $this->hitungBulanBerturut($warga->id, $bulan) : 0;

            $poin = ($kgDipilah * 10) + ($bulanBerturut * 50) + ($persenDipilah * 2);

            SnapshotPeringkatBulanan::updateOrCreate(
                ['warga_id' => $warga->id, 'bulan' => $bulanStr],
                [
                    'total_kg'       => $totalKg,
                    'kg_dipilah'     => $kgDipilah,
                    'persen_dipilah' => $persenDipilah,
                    'bulan_berturut' => $bulanBerturut,
                    'poin'           => $poin,
                ]
            );
        }

        $this->updateRankings($bulanStr);
    }

    /**
     * Count how many consecutive months (including current) the warga had at least one setoran.
     */
    private function hitungBulanBerturut(int $wargaId, Carbon $bulan): int
    {
        $count  = 0;
        $cursor = $bulan->copy()->startOfMonth();

        while (true) {
            $ada = DB::table('setoran')
                ->where('warga_id', $wargaId)
                ->whereBetween('tanggal_setoran', [
                    $cursor->copy()->startOfMonth(),
                    $cursor->copy()->endOfMonth(),
                ])
                ->exists();

            if (!$ada) {
                break;
            }

            $count++;
            $cursor->subMonth();
        }

        return $count;
    }

    /**
     * Assign peringkat (overall) and peringkat_rw for a given month, ordered by poin DESC.
     */
    private function updateRankings(string $bulanStr): void
    {
        // Overall ranking
        $rows = SnapshotPeringkatBulanan::where('bulan', $bulanStr)
            ->orderByDesc('poin')
            ->get();

        foreach ($rows as $i => $row) {
            $row->peringkat = $i + 1;
            $row->save();
        }

        // Per-RW ranking
        $rwGroups = $rows->groupBy(fn($row) => $row->warga->rw ?? 'unknown');
        foreach ($rwGroups as $rt => $group) {
            $sorted = $group->sortByDesc('poin')->values();
            foreach ($sorted as $i => $row) {
                $row->peringkat_rw = $i + 1;
                $row->save();
            }
        }
    }

    /**
     * Return leaderboard entries for a given month (Carbon or 'Y-m' string).
     */
    public function leaderboard(string $bulanStr): Collection
    {
        return SnapshotPeringkatBulanan::with('warga')
            ->where('bulan', Carbon::parse($bulanStr)->format('Y-m-01'))
            ->orderBy('peringkat')
            ->get();
    }
}
