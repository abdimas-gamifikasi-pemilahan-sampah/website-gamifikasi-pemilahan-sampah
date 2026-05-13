<?php

namespace Tests\Unit;

use App\Models\RiwayatTarif;
use App\Models\TarifItem;
use App\Models\User;
use App\Services\TarifPricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TarifPricingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_lookup_harga_returns_history_that_matches_requested_date(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $tarifItem = TarifItem::create([
            'nama_item' => 'Botol PET',
            'tipe_sampah' => 'anorganik',
            'status' => 'aktif',
            'dibuat_oleh_user_id' => $user->id,
        ]);

        RiwayatTarif::create([
            'tarif_item_id' => $tarifItem->id,
            'harga_per_kg' => 1200,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_akhir' => '2026-03-31',
            'alasan_perubahan' => 'Tarif awal.',
            'diubah_oleh_user_id' => $user->id,
        ]);

        RiwayatTarif::create([
            'tarif_item_id' => $tarifItem->id,
            'harga_per_kg' => 1500,
            'tanggal_mulai' => '2026-04-01',
            'tanggal_akhir' => null,
            'alasan_perubahan' => 'Penyesuaian harga pasar.',
            'diubah_oleh_user_id' => $user->id,
        ]);

        $service = app(TarifPricingService::class);

        $riwayatFebruari = $service->lookupByItemAndDate($tarifItem, '2026-02-10');
        $riwayatApril = $service->lookupByItemAndDate($tarifItem, '2026-04-10');

        $this->assertNotNull($riwayatFebruari);
        $this->assertSame('1200.00', $riwayatFebruari->harga_per_kg);
        $this->assertNotNull($riwayatApril);
        $this->assertSame('1500.00', $riwayatApril->harga_per_kg);
    }

    public function test_add_price_history_auto_closes_previous_active_history(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $tarifItem = TarifItem::create([
            'nama_item' => 'Kaleng Aluminium',
            'tipe_sampah' => 'anorganik',
            'status' => 'aktif',
            'dibuat_oleh_user_id' => $user->id,
        ]);

        $riwayatLama = RiwayatTarif::create([
            'tarif_item_id' => $tarifItem->id,
            'harga_per_kg' => 1800,
            'tanggal_mulai' => '2026-01-01',
            'tanggal_akhir' => null,
            'alasan_perubahan' => 'Tarif awal.',
            'diubah_oleh_user_id' => $user->id,
        ]);

        $service = app(TarifPricingService::class);

        $riwayatBaru = $service->addPriceHistory(
            $tarifItem,
            2200,
            '2026-03-01',
            'Harga pasar naik.',
            $user->id
        );

        $this->assertSame('2026-02-28', $riwayatLama->fresh()->tanggal_akhir->toDateString());
        $this->assertSame('2026-03-01', $riwayatBaru->tanggal_mulai->toDateString());
        $this->assertNull($riwayatBaru->tanggal_akhir);
        $this->assertSame('2200.00', $riwayatBaru->harga_per_kg);
    }
}
