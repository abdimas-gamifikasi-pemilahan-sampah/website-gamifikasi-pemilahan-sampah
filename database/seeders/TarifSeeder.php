<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarifSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        $now = Carbon::now();

        $b3ItemIds = DB::table('tarif_items')
            ->where('tipe_sampah', 'b3')
            ->pluck('id');

        if ($b3ItemIds->isNotEmpty()) {
            DB::table('item_setoran')->whereIn('tarif_item_id', $b3ItemIds)->delete();
            DB::table('riwayat_tarif')->whereIn('tarif_item_id', $b3ItemIds)->delete();
            DB::table('tarif_items')->whereIn('id', $b3ItemIds)->delete();
        }

        $items = [
            ['nama_item' => 'Sisa Makanan', 'tipe_sampah' => 'organik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 250, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => '2026-03-31', 'alasan_perubahan' => 'Tarif awal penetapan SIPS.'],
                ['harga_per_kg' => 300, 'tanggal_mulai' => '2026-04-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Penyesuaian nilai kompos kuartal 2.'],
            ]],
            ['nama_item' => 'Sisa Sayur', 'tipe_sampah' => 'organik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 220, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Tarif awal penetapan SIPS.'],
            ]],
            ['nama_item' => 'Daun Kering', 'tipe_sampah' => 'organik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 180, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => '2026-05-31', 'alasan_perubahan' => 'Tarif awal penetapan SIPS.'],
                ['harga_per_kg' => 210, 'tanggal_mulai' => '2026-06-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Musim penghujan meningkatkan kualitas bahan.'],
            ]],
            ['nama_item' => 'Sisa Buah', 'tipe_sampah' => 'organik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 200, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Tarif awal penetapan SIPS.'],
            ]],
            ['nama_item' => 'Ampas Kopi', 'tipe_sampah' => 'organik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 300, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => '2026-06-30', 'alasan_perubahan' => 'Tarif awal penetapan SIPS.'],
                ['harga_per_kg' => 350, 'tanggal_mulai' => '2026-07-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Permintaan kompos premium meningkat.'],
            ]],
            ['nama_item' => 'Cangkang Telur', 'tipe_sampah' => 'organik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 320, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Tarif awal kategori organik granular.'],
            ]],
            ['nama_item' => 'Ranting Halus', 'tipe_sampah' => 'organik', 'status' => 'arsip', 'riwayat' => [
                ['harga_per_kg' => 160, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Kategori lama sebelum pemurnian item organik.'],
            ]],
            ['nama_item' => 'Plastik PET', 'tipe_sampah' => 'anorganik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 1200, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => '2026-03-31', 'alasan_perubahan' => 'Tarif awal penetapan SIPS.'],
                ['harga_per_kg' => 1500, 'tanggal_mulai' => '2026-04-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Harga pasar plastik PET naik.'],
            ]],
            ['nama_item' => 'Plastik HDPE', 'tipe_sampah' => 'anorganik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 1350, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Tarif awal plastik keras.'],
            ]],
            ['nama_item' => 'Kertas HVS', 'tipe_sampah' => 'anorganik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 700, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => '2026-05-31', 'alasan_perubahan' => 'Tarif awal penetapan SIPS.'],
                ['harga_per_kg' => 850, 'tanggal_mulai' => '2026-06-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Permintaan kertas daur ulang meningkat.'],
            ]],
            ['nama_item' => 'Kardus', 'tipe_sampah' => 'anorganik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 650, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Tarif awal kategori kardus.'],
            ]],
            ['nama_item' => 'Kaleng Aluminium', 'tipe_sampah' => 'anorganik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 1800, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => '2026-02-28', 'alasan_perubahan' => 'Tarif awal penetapan SIPS.'],
                ['harga_per_kg' => 2200, 'tanggal_mulai' => '2026-03-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Harga aluminium naik di pasar lokal.'],
            ]],
            ['nama_item' => 'Besi Tua', 'tipe_sampah' => 'anorganik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 1000, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Tarif awal penetapan SIPS.'],
            ]],
            ['nama_item' => 'Botol Kaca', 'tipe_sampah' => 'anorganik', 'status' => 'aktif', 'riwayat' => [
                ['harga_per_kg' => 250, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Tarif awal penetapan SIPS.'],
            ]],
            ['nama_item' => 'Gelas Kaca', 'tipe_sampah' => 'anorganik', 'status' => 'arsip', 'riwayat' => [
                ['harga_per_kg' => 150, 'tanggal_mulai' => '2026-01-01', 'tanggal_akhir' => null, 'alasan_perubahan' => 'Kategori lama sebelum penggabungan kualitas kaca.'],
            ]],
        ];

        $desiredItemKeys = collect($items)
            ->map(fn (array $item) => $item['tipe_sampah'].'::'.$item['nama_item']);

        $staleTarifItemIds = DB::table('tarif_items')
            ->get(['id', 'nama_item', 'tipe_sampah'])
            ->filter(fn ($item) => ! $desiredItemKeys->contains($item->tipe_sampah.'::'.$item->nama_item))
            ->pluck('id');

        if ($staleTarifItemIds->isNotEmpty()) {
            DB::table('item_setoran')->whereIn('tarif_item_id', $staleTarifItemIds)->delete();
            DB::table('riwayat_tarif')->whereIn('tarif_item_id', $staleTarifItemIds)->delete();
            DB::table('tarif_items')->whereIn('id', $staleTarifItemIds)->delete();
        }

        foreach ($items as $item) {
            DB::table('tarif_items')->updateOrInsert(
                [
                    'nama_item' => $item['nama_item'],
                    'tipe_sampah' => $item['tipe_sampah'],
                ],
                [
                    'status' => $item['status'],
                    'dibuat_oleh_user_id' => $userId,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $tarifItemId = DB::table('tarif_items')
                ->where('nama_item', $item['nama_item'])
                ->where('tipe_sampah', $item['tipe_sampah'])
                ->value('id');

            foreach ($item['riwayat'] as $riwayat) {
                DB::table('riwayat_tarif')->updateOrInsert(
                    [
                        'tarif_item_id' => $tarifItemId,
                        'tanggal_mulai' => $riwayat['tanggal_mulai'],
                    ],
                    [
                        'harga_per_kg' => $riwayat['harga_per_kg'],
                        'tanggal_akhir' => $riwayat['tanggal_akhir'],
                        'alasan_perubahan' => $riwayat['alasan_perubahan'],
                        'diubah_oleh_user_id' => $userId,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }
}
