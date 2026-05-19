<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SetoranSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('item_setoran')->delete();
        DB::table('pembayaran')->delete();
        DB::table('setoran')->delete();
        DB::table('snapshot_peringkat_bulanan')->delete();

        $petugasId = DB::table('users')->where('role', 'petugas')->value('id');
        $adminId   = DB::table('users')->where('role', 'admin')->value('id');

        $wargaIds = DB::table('warga')->where('status_keanggotaan', 'aktif')->pluck('id')->toArray();

        $months = [
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-02-01'),
            Carbon::parse('2026-03-01'),
            Carbon::parse('2026-04-01'),
            Carbon::parse('2026-05-01'),
        ];

        $skipMap = [
            2  => [1],
            5  => [3],
            8  => [1, 2],
            11 => [0],
            14 => [3, 4],
            17 => [2],
        ];

        $organikIds   = DB::table('tarif_items')->where('status', 'aktif')->where('tipe_sampah', 'organik')->pluck('id')->toArray();
        $anorganikIds = DB::table('tarif_items')->where('status', 'aktif')->where('tipe_sampah', 'anorganik')->pluck('id')->toArray();

        foreach ($wargaIds as $wi => $wargaId) {
            foreach ($months as $mi => $bulan) {
                if (isset($skipMap[$wi]) && in_array($mi, $skipMap[$wi], true)) {
                    continue;
                }

                $setoranCount = ($wi % 3 === 0) ? 2 : 1;

                for ($s = 0; $s < $setoranCount; $s++) {
                    $day     = $s === 0 ? rand(1, 14) : rand(15, $bulan->copy()->endOfMonth()->day);
                    $tanggal = $bulan->copy()->setDay($day)->setTime(rand(7, 16), rand(0, 59));
                    $petugas = ($wi % 5 === 0) ? $adminId : $petugasId;

                    $numItems   = rand(1, 3);
                    $totalNilai = 0;
                    $newItemRows = [];

                    for ($ii = 0; $ii < $numItems; $ii++) {
                        if ($ii % 2 === 0) {
                            $tarifItemId = $organikIds[array_rand($organikIds)];
                            $tipe = 'organik';
                        } else {
                            $tarifItemId = $anorganikIds[array_rand($anorganikIds)];
                            $tipe = 'anorganik';
                        }

                        $beratKg = round(rand(5, 30) / 10, 1);
                        $riwayat = $this->getRiwayatAktif($tarifItemId, $tanggal->toDateString());

                        if (!$riwayat) {
                            continue;
                        }

                        $statusPemilahan = rand(1, 10) <= 7 ? 'dipilah' : 'tidak_dipilah';

                        if ($statusPemilahan === 'dipilah') {
                            $subtotal             = round($beratKg * $riwayat->harga_per_kg, 2);
                            $hargaPerKgSaatItu    = $riwayat->harga_per_kg;
                            $riwayatTarifId       = $riwayat->id;
                            $tarifItemIdRow       = $tarifItemId;
                            $tipeRow              = $tipe;
                        } else {
                            $subtotal             = 0;
                            $hargaPerKgSaatItu    = null;
                            $riwayatTarifId       = null;
                            $tarifItemIdRow       = null;
                            $tipeRow              = null;
                        }
                        $totalNilai += $subtotal;

                        $newItemRows[] = [
                            'tarif_item_id'         => $tarifItemIdRow,
                            'riwayat_tarif_id'      => $riwayatTarifId,
                            'tipe_sampah'           => $tipeRow,
                            'berat_kg'              => $beratKg,
                            'harga_per_kg_saat_itu' => $hargaPerKgSaatItu,
                            'subtotal'              => $subtotal,
                            'status_pemilahan'      => $statusPemilahan,
                            'created_at'            => $tanggal,
                            'updated_at'            => $tanggal,
                        ];
                    }

                    if (empty($newItemRows)) {
                        continue;
                    }

                    $setoranId = DB::table('setoran')->insertGetId([
                        'warga_id'          => $wargaId,
                        'petugas_id'        => $petugas,
                        'tanggal_setoran'   => $tanggal,
                        'total_nilai'       => $totalNilai,
                        'status_pembayaran' => 'sudah_dibayar',
                        'catatan_kondisi'   => null,
                        'created_at'        => $tanggal,
                        'updated_at'        => $tanggal,
                    ]);

                    foreach ($newItemRows as &$ir) {
                        $ir['setoran_id'] = $setoranId;
                    }
                    DB::table('item_setoran')->insert($newItemRows);

                    $tanggalBayar = $tanggal->copy()->addHours(rand(0, 2));
                    DB::table('pembayaran')->insert([
                        'setoran_id'          => $setoranId,
                        'petugas_pembayar_id' => $petugas,
                        'tanggal_bayar'       => $tanggalBayar,
                        'jumlah_dibayar'      => $totalNilai,
                        'catatan'             => null,
                        'created_at'          => $tanggalBayar,
                    ]);
                }
            }
        }

        // A few recent unpaid setorans (May 2026)
        $unpaidWarga = array_slice($wargaIds, 0, 3);
        foreach ($unpaidWarga as $wargaId) {
            $tanggal     = Carbon::parse('2026-05-' . rand(10, 16) . ' ' . rand(8, 14) . ':00:00');
            $tarifItemId = $organikIds[array_rand($organikIds)];
            $riwayat     = $this->getRiwayatAktif($tarifItemId, $tanggal->toDateString());

            if (!$riwayat) {
                continue;
            }

            $beratKg  = round(rand(5, 20) / 10, 1);
            $subtotal = round($beratKg * $riwayat->harga_per_kg, 2);

            $setoranId = DB::table('setoran')->insertGetId([
                'warga_id'          => $wargaId,
                'petugas_id'        => $petugasId,
                'tanggal_setoran'   => $tanggal,
                'total_nilai'       => $subtotal,
                'status_pembayaran' => 'belum_dibayar',
                'catatan_kondisi'   => null,
                'created_at'        => $tanggal,
                'updated_at'        => $tanggal,
            ]);

            DB::table('item_setoran')->insert([
                'setoran_id'            => $setoranId,
                'tarif_item_id'         => $tarifItemId,
                'riwayat_tarif_id'      => $riwayat->id,
                'tipe_sampah'           => 'organik',
                'berat_kg'              => $beratKg,
                'harga_per_kg_saat_itu' => $riwayat->harga_per_kg,
                'subtotal'              => $subtotal,
                'status_pemilahan'      => 'dipilah',
                'created_at'            => $tanggal,
                'updated_at'            => $tanggal,
            ]);
        }
    }

    private function getRiwayatAktif(int $tarifItemId, string $tanggal): ?object
    {
        return DB::table('riwayat_tarif')
            ->where('tarif_item_id', $tarifItemId)
            ->where('tanggal_mulai', '<=', $tanggal)
            ->where(function ($q) use ($tanggal) {
                $q->whereNull('tanggal_akhir')
                  ->orWhere('tanggal_akhir', '>=', $tanggal);
            })
            ->orderByDesc('tanggal_mulai')
            ->select(['id', 'harga_per_kg'])
            ->first();
    }
}
