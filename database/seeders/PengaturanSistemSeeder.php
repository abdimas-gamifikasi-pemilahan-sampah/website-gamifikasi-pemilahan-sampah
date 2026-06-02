<?php

namespace Database\Seeders;

use App\Models\PengaturanSistem;
use Illuminate\Database\Seeder;

class PengaturanSistemSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'kunci'     => 'tarif_flat_per_kg',
                'nilai'     => '500',
                'deskripsi' => 'Tarif iuran per kilogram sampah (Rp). Berlaku untuk semua jenis sampah tidak dipilah.',
            ],
            [
                'kunci'     => 'nama_tps',
                'nilai'     => 'TPS 3R Banjarsari',
                'deskripsi' => 'Nama TPS yang tampil di laporan dan ekspor.',
            ],
        ];

        foreach ($settings as $setting) {
            PengaturanSistem::updateOrCreate(
                ['kunci' => $setting['kunci']],
                ['nilai' => $setting['nilai'], 'deskripsi' => $setting['deskripsi']]
            );
        }
    }
}
