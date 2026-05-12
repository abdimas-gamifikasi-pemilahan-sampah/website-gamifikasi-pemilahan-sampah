<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WargaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $warga = [
            ['nama' => 'Budi Santoso',    'no_kk' => '3301010101010001', 'rt' => '01', 'rw' => '01', 'dusun' => 'Melati'],
            ['nama' => 'Siti Aminah',     'no_kk' => '3301010101010002', 'rt' => '02', 'rw' => '01', 'dusun' => 'Melati'],
            ['nama' => 'Agus Pratama',    'no_kk' => '3301010101010003', 'rt' => '01', 'rw' => '02', 'dusun' => 'Anggrek'],
            ['nama' => 'Dewi Rahayu',     'no_kk' => '3301010101010004', 'rt' => '03', 'rw' => '02', 'dusun' => 'Anggrek'],
            ['nama' => 'Hendra Wijaya',   'no_kk' => '3301010101010005', 'rt' => '01', 'rw' => '03', 'dusun' => 'Kenanga'],
            ['nama' => 'Rina Kusumawati', 'no_kk' => '3301010101010006', 'rt' => '02', 'rw' => '03', 'dusun' => 'Kenanga'],
        ];

        foreach ($warga as $w) {
            DB::table('warga')->insert([
                'nama'               => $w['nama'],
                'no_kk'              => $w['no_kk'],
                'rt'                 => $w['rt'],
                'rw'                 => $w['rw'],
                'dusun'              => $w['dusun'],
                'no_hp'              => null,
                'tanggal_terdaftar'  => '2026-01-01',
                'status_keanggotaan' => 'aktif',
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }
    }
}
