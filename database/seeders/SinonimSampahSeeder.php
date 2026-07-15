<?php

namespace Database\Seeders;

use App\Models\SinonimSampah;
use App\Models\TarifItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class SinonimSampahSeeder extends Seeder
{
    /**
     * Seed common field synonyms collected from village waste records.
     * Kata kunci disimpan lowercase agar matching konsisten.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->warn('SinonimSampahSeeder: tidak ada user admin, seeder dilewati.');
            return;
        }

        // Format: ['kata_kunci' => ..., 'nama_item' => ... (nullable), 'tipe' => ..., 'flat' => bool]
        $entries = [
            // ── Kardus / Karton ────────────────────────────────────────────
            ['kunci' => 'karton',        'item' => 'Kardus'],
            ['kunci' => 'kardus',        'item' => 'Kardus'],
            ['kunci' => 'dus',           'item' => 'Kardus'],
            ['kunci' => 'box',           'item' => 'Kardus'],
            ['kunci' => 'karton bekas',  'item' => 'Kardus'],

            // ── Botol ──────────────────────────────────────────────────────
            ['kunci' => 'botol',         'item' => 'Botol PET'],
            ['kunci' => 'botol plastik', 'item' => 'Botol PET'],
            ['kunci' => 'pet',           'item' => 'Botol PET'],

            // ── Koran / Kertas ─────────────────────────────────────────────
            ['kunci' => 'koran',         'item' => 'Koran'],
            ['kunci' => 'kertas koran',  'item' => 'Koran'],
            ['kunci' => 'koran bekas',   'item' => 'Koran'],

            // ── Kaleng ─────────────────────────────────────────────────────
            ['kunci' => 'kaleng',        'item' => 'Kaleng'],
            ['kunci' => 'besi',          'item' => 'Kaleng'],
            ['kunci' => 'logam',         'item' => 'Kaleng'],

            // ── Flat rate (tidak dipilah / campur / tidak dikenal) ─────────
            ['kunci' => 'campur',        'item' => null, 'tipe' => null,        'flat' => true],
            ['kunci' => 'tidak dipilah', 'item' => null, 'tipe' => null,        'flat' => true],
            ['kunci' => 'sampah campur', 'item' => null, 'tipe' => null,        'flat' => true],
            ['kunci' => 'styrofoam',     'item' => null, 'tipe' => 'anorganik', 'flat' => true],
            ['kunci' => 'gabus',         'item' => null, 'tipe' => 'anorganik', 'flat' => true],

            // ── Organik umum (flat, tipe diketahui) ───────────────────────
            ['kunci' => 'organik',       'item' => null, 'tipe' => 'organik',   'flat' => true],
            ['kunci' => 'sampah organik','item' => null, 'tipe' => 'organik',   'flat' => true],
            ['kunci' => 'daun',          'item' => null, 'tipe' => 'organik',   'flat' => true],
            ['kunci' => 'sisa makanan',  'item' => null, 'tipe' => 'organik',   'flat' => true],
        ];

        foreach ($entries as $entry) {
            $tarifItem = null;
            $tipeSampah = $entry['tipe'] ?? null;

            if (!empty($entry['item'])) {
                $tarifItem  = TarifItem::where('nama_item', $entry['item'])->first();
                $tipeSampah = $tarifItem?->tipe_sampah;
            }

            SinonimSampah::firstOrCreate(
                ['kata_kunci' => $entry['kunci']],
                [
                    'tarif_item_id'       => $tarifItem?->id,
                    'tipe_sampah'         => $tipeSampah,
                    'gunakan_flat'        => $entry['flat'] ?? false,
                    'dibuat_oleh_user_id' => $admin->id,
                ]
            );
        }

        $this->command->info('SinonimSampahSeeder: ' . count($entries) . ' sinonim dimuat.');
    }
}
