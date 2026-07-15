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
            ['nama' => 'Budi Santoso',    'no_kk' => '3301010101010001', 'alamat' => 'Jl. Melati No. 5 RT 1 RW 1',       'rt' => 1, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000001', 'tanggal_terdaftar' => '2026-01-01', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Siti Aminah',     'no_kk' => '3301010101010002', 'alamat' => 'Jl. Melati No. 12 RT 2 RW 1',      'rt' => 2, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000002', 'tanggal_terdaftar' => '2026-01-03', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Agus Pratama',    'no_kk' => '3301010101010003', 'alamat' => 'Jl. Anggrek No. 3 RT 1 RW 2',      'rt' => 1, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000003', 'tanggal_terdaftar' => '2026-01-05', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Dewi Rahayu',     'no_kk' => '3301010101010004', 'alamat' => 'Jl. Anggrek No. 8 RT 3 RW 2',      'rt' => 3, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000004', 'tanggal_terdaftar' => '2026-01-08', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Hendra Wijaya',   'no_kk' => '3301010101010005', 'alamat' => 'Jl. Kenanga No. 2 RT 1 RW 3',      'rt' => 1, 'rw' => 3, 'dusun' => 'Kenanga',   'no_hp' => '081210000005', 'tanggal_terdaftar' => '2026-01-10', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Rina Kusumawati', 'no_kk' => '3301010101010006', 'alamat' => 'Jl. Kenanga No. 7 RT 2 RW 3',      'rt' => 2, 'rw' => 3, 'dusun' => 'Kenanga',   'no_hp' => '081210000006', 'tanggal_terdaftar' => '2026-01-11', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Tono Saputra',    'no_kk' => '3301010101010007', 'alamat' => 'Jl. Melati No. 15 RT 1 RW 1',      'rt' => 1, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000007', 'tanggal_terdaftar' => '2026-01-12', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Lina Marlina',    'no_kk' => '3301010101010008', 'alamat' => 'Jl. Melati No. 21 RT 3 RW 1',      'rt' => 3, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000008', 'tanggal_terdaftar' => '2026-01-13', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Fajar Nugroho',   'no_kk' => '3301010101010009', 'alamat' => 'Jl. Anggrek No. 17 RT 2 RW 2',     'rt' => 2, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000009', 'tanggal_terdaftar' => '2026-01-14', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Maya Puspitasari','no_kk' => '3301010101010010', 'alamat' => 'Jl. Anggrek No. 24 RT 3 RW 2',     'rt' => 3, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000010', 'tanggal_terdaftar' => '2026-01-16', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Rudi Hartono',    'no_kk' => '3301010101010011', 'alamat' => 'Jl. Kenanga No. 9 RT 1 RW 3',      'rt' => 1, 'rw' => 3, 'dusun' => 'Kenanga',   'no_hp' => '081210000011', 'tanggal_terdaftar' => '2026-01-18', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Nita Sari',       'no_kk' => '3301010101010012', 'alamat' => 'Jl. Kenanga No. 14 RT 2 RW 3',     'rt' => 2, 'rw' => 3, 'dusun' => 'Kenanga',   'no_hp' => '081210000012', 'tanggal_terdaftar' => '2026-01-20', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Yusuf Maulana',   'no_kk' => '3301010101010013', 'alamat' => 'Jl. Flamboyan No. 4 RT 1 RW 4',    'rt' => 1, 'rw' => 4, 'dusun' => 'Flamboyan', 'no_hp' => '081210000013', 'tanggal_terdaftar' => '2026-01-22', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Intan Permata',   'no_kk' => '3301010101010014', 'alamat' => 'Jl. Flamboyan No. 11 RT 2 RW 4',   'rt' => 2, 'rw' => 4, 'dusun' => 'Flamboyan', 'no_hp' => '081210000014', 'tanggal_terdaftar' => '2026-01-24', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Farhan Ramadhan', 'no_kk' => '3301010101010015', 'alamat' => 'Jl. Flamboyan No. 18 RT 3 RW 4',   'rt' => 3, 'rw' => 4, 'dusun' => 'Flamboyan', 'no_hp' => '081210000015', 'tanggal_terdaftar' => '2026-01-26', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Sulastri',        'no_kk' => '3301010101010016', 'alamat' => 'Jl. Cempaka No. 6 RT 1 RW 5',      'rt' => 1, 'rw' => 5, 'dusun' => 'Cempaka',   'no_hp' => '081210000016', 'tanggal_terdaftar' => '2026-01-28', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Arman Syah',      'no_kk' => '3301010101010017', 'alamat' => 'Jl. Cempaka No. 13 RT 2 RW 5',     'rt' => 2, 'rw' => 5, 'dusun' => 'Cempaka',   'no_hp' => '081210000017', 'tanggal_terdaftar' => '2026-02-01', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Riska Amelia',    'no_kk' => '3301010101010018', 'alamat' => 'Jl. Cempaka No. 20 RT 3 RW 5',     'rt' => 3, 'rw' => 5, 'dusun' => 'Cempaka',   'no_hp' => '081210000018', 'tanggal_terdaftar' => '2026-02-03', 'status_keanggotaan' => 'aktif'],
            ['nama' => 'Dani Kurniawan',  'no_kk' => '3301010101010019', 'alamat' => 'Jl. Anggrek No. 31 RT 1 RW 2',     'rt' => 1, 'rw' => 2, 'dusun' => 'Anggrek',   'no_hp' => '081210000019', 'tanggal_terdaftar' => '2026-02-05', 'status_keanggotaan' => 'non_aktif'],
            ['nama' => 'Kartika Dewi',    'no_kk' => '3301010101010020', 'alamat' => 'Jl. Melati No. 28 RT 2 RW 1',      'rt' => 2, 'rw' => 1, 'dusun' => 'Melati',    'no_hp' => '081210000020', 'tanggal_terdaftar' => '2026-02-07', 'status_keanggotaan' => 'aktif'],
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
            ['nama', 'alamat', 'rt', 'rw', 'dusun', 'no_hp', 'tanggal_terdaftar', 'status_keanggotaan', 'updated_at']
        );
    }
}
