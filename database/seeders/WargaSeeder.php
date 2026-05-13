<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WargaSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $warga = [
            ['nama' => 'Budi Santoso', 'no_kk' => '3301010101010001', 'rt' => '01', 'rw' => '01', 'dusun' => 'Melati', 'no_hp' => '081210000001', 'tanggal_terdaftar' => '2026-01-01', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Siti Aminah', 'no_kk' => '3301010101010002', 'rt' => '02', 'rw' => '01', 'dusun' => 'Melati', 'no_hp' => '081210000002', 'tanggal_terdaftar' => '2026-01-03', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Agus Pratama', 'no_kk' => '3301010101010003', 'rt' => '01', 'rw' => '02', 'dusun' => 'Anggrek', 'no_hp' => '081210000003', 'tanggal_terdaftar' => '2026-01-05', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Dewi Rahayu', 'no_kk' => '3301010101010004', 'rt' => '03', 'rw' => '02', 'dusun' => 'Anggrek', 'no_hp' => '081210000004', 'tanggal_terdaftar' => '2026-01-08', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Hendra Wijaya', 'no_kk' => '3301010101010005', 'rt' => '01', 'rw' => '03', 'dusun' => 'Kenanga', 'no_hp' => '081210000005', 'tanggal_terdaftar' => '2026-01-10', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Rina Kusumawati', 'no_kk' => '3301010101010006', 'rt' => '02', 'rw' => '03', 'dusun' => 'Kenanga', 'no_hp' => '081210000006', 'tanggal_terdaftar' => '2026-01-11', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Tono Saputra', 'no_kk' => '3301010101010007', 'rt' => '01', 'rw' => '01', 'dusun' => 'Melati', 'no_hp' => '081210000007', 'tanggal_terdaftar' => '2026-01-12', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Lina Marlina', 'no_kk' => '3301010101010008', 'rt' => '03', 'rw' => '01', 'dusun' => 'Melati', 'no_hp' => '081210000008', 'tanggal_terdaftar' => '2026-01-13', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Fajar Nugroho', 'no_kk' => '3301010101010009', 'rt' => '02', 'rw' => '02', 'dusun' => 'Anggrek', 'no_hp' => '081210000009', 'tanggal_terdaftar' => '2026-01-14', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Maya Puspitasari', 'no_kk' => '3301010101010010', 'rt' => '03', 'rw' => '02', 'dusun' => 'Anggrek', 'no_hp' => '081210000010', 'tanggal_terdaftar' => '2026-01-16', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Rudi Hartono', 'no_kk' => '3301010101010011', 'rt' => '01', 'rw' => '03', 'dusun' => 'Kenanga', 'no_hp' => '081210000011', 'tanggal_terdaftar' => '2026-01-18', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Nita Sari', 'no_kk' => '3301010101010012', 'rt' => '02', 'rw' => '03', 'dusun' => 'Kenanga', 'no_hp' => '081210000012', 'tanggal_terdaftar' => '2026-01-20', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Yusuf Maulana', 'no_kk' => '3301010101010013', 'rt' => '01', 'rw' => '04', 'dusun' => 'Flamboyan', 'no_hp' => '081210000013', 'tanggal_terdaftar' => '2026-01-22', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Intan Permata', 'no_kk' => '3301010101010014', 'rt' => '02', 'rw' => '04', 'dusun' => 'Flamboyan', 'no_hp' => '081210000014', 'tanggal_terdaftar' => '2026-01-24', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Farhan Ramadhan', 'no_kk' => '3301010101010015', 'rt' => '03', 'rw' => '04', 'dusun' => 'Flamboyan', 'no_hp' => '081210000015', 'tanggal_terdaftar' => '2026-01-26', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Sulastri', 'no_kk' => '3301010101010016', 'rt' => '01', 'rw' => '05', 'dusun' => 'Cempaka', 'no_hp' => '081210000016', 'tanggal_terdaftar' => '2026-01-28', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Arman Syah', 'no_kk' => '3301010101010017', 'rt' => '02', 'rw' => '05', 'dusun' => 'Cempaka', 'no_hp' => '081210000017', 'tanggal_terdaftar' => '2026-02-01', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Riska Amelia', 'no_kk' => '3301010101010018', 'rt' => '03', 'rw' => '05', 'dusun' => 'Cempaka', 'no_hp' => '081210000018', 'tanggal_terdaftar' => '2026-02-03', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Dani Kurniawan', 'no_kk' => '3301010101010019', 'rt' => '01', 'rw' => '02', 'dusun' => 'Anggrek', 'no_hp' => '081210000019', 'tanggal_terdaftar' => '2026-02-05', 'status_keanggotaan' => 'non_aktif'],
            ['nama' => 'Kartika Dewi', 'no_kk' => '3301010101010020', 'rt' => '02', 'rw' => '01', 'dusun' => 'Melati', 'no_hp' => '081210000020', 'tanggal_terdaftar' => '2026-02-07', 'status_keanggotaan' => 'aktif'],
        ];

        $rows = array_map(function (array $item) use ($now): array {
            return array_merge($item, [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }, $warga);

        DB::table('warga')->upsert(
            $rows,
            ['no_kk'],
            ['nama', 'rt', 'rw', 'dusun', 'no_hp', 'tanggal_terdaftar', 'status_keanggotaan', 'updated_at']
        );
    }
}
