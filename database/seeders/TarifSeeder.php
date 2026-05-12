<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TarifSeeder extends Seeder
{
    public function run(): void
    {
        $userId = DB::table('users')->value('id');
        $now    = Carbon::now();

        $items = [
            ['nama_item' => 'Organik Campur',   'tipe_sampah' => 'organik',   'harga' => 300],
            ['nama_item' => 'Plastik',           'tipe_sampah' => 'anorganik', 'harga' => 500],
            ['nama_item' => 'Kertas / Kardus',   'tipe_sampah' => 'anorganik', 'harga' => 400],
            ['nama_item' => 'Logam / Besi Tua',  'tipe_sampah' => 'anorganik', 'harga' => 1000],
            ['nama_item' => 'Kaca / Botol Kaca', 'tipe_sampah' => 'anorganik', 'harga' => 200],
        ];

        foreach ($items as $item) {
            $tarifItemId = DB::table('tarif_items')->insertGetId([
                'nama_item'          => $item['nama_item'],
                'tipe_sampah'        => $item['tipe_sampah'],
                'status'             => 'aktif',
                'dibuat_oleh_user_id' => $userId,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);

            DB::table('riwayat_tarif')->insert([
                'tarif_item_id'      => $tarifItemId,
                'harga_per_kg'       => $item['harga'],
                'tanggal_mulai'      => '2026-01-01',
                'tanggal_akhir'      => null,
                'alasan_perubahan'   => 'Tarif awal penetapan SIPS.',
                'diubah_oleh_user_id' => $userId,
                'created_at'         => $now,
            ]);
        }
    }
}
