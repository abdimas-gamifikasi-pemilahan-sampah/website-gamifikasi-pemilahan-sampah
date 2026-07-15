<?php

namespace Tests\Unit;

use App\Models\SinonimSampah;
use App\Models\TarifItem;
use App\Models\RiwayatTarif;
use App\Models\User;
use App\Services\WasteMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WasteMatcherTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function makeTarifItem(string $nama, string $tipe, float $harga): TarifItem
    {
        $item = TarifItem::create([
            'nama_item'          => $nama,
            'tipe_sampah'        => $tipe,
            'status'             => 'aktif',
            'dibuat_oleh_user_id'=> $this->admin->id,
        ]);

        RiwayatTarif::create([
            'tarif_item_id'      => $item->id,
            'harga_per_kg'       => $harga,
            'tanggal_mulai'      => '2024-01-01',
            'tanggal_akhir'      => null,
            'diubah_oleh_user_id'=> $this->admin->id,
        ]);

        return $item->fresh();
    }

    private function makeSinonim(string $kunci, ?TarifItem $item, ?string $tipe = null, bool $flat = false): void
    {
        SinonimSampah::create([
            'kata_kunci'          => $kunci,
            'tarif_item_id'       => $item?->id,
            'tipe_sampah'         => $tipe ?? $item?->tipe_sampah,
            'gunakan_flat'        => $flat,
            'dibuat_oleh_user_id' => $this->admin->id,
        ]);
    }

    private function matcher(?Collection $items = null, ?Collection $sinonims = null): WasteMatcher
    {
        return new WasteMatcher(
            $items    ?? TarifItem::where('status', 'aktif')->get(),
            $sinonims ?? SinonimSampah::with('tarifItem')->get()
        );
    }

    #[Test]
    public function sinonim_exact_hit_returns_100_confidence(): void
    {
        $kardus = $this->makeTarifItem('Kardus', 'anorganik', 1500);
        $this->makeSinonim('karton', $kardus);

        $result = $this->matcher()->match('karton');

        $this->assertSame(100, $result['confidence']);
        $this->assertSame('sinonim', $result['method']);
        $this->assertFalse($result['gunakan_flat']);
        $this->assertSame(1500.0, $result['harga_per_kg']);
    }

    #[Test]
    public function sinonim_flat_rate_returns_flat_result(): void
    {
        $this->makeSinonim('campur', null, null, true);

        $result = $this->matcher()->match('campur');

        $this->assertTrue($result['gunakan_flat']);
        $this->assertSame('sinonim_flat', $result['method']);
        $this->assertSame(100, $result['confidence']);
    }

    #[Test]
    public function exact_match_tarif_item_name(): void
    {
        $this->makeTarifItem('Botol PET', 'anorganik', 800);

        $result = $this->matcher()->match('Botol PET');

        $this->assertSame(100, $result['confidence']);
        $this->assertSame('exact', $result['method']);
        $this->assertFalse($result['gunakan_flat']);
    }

    #[Test]
    public function exact_match_is_case_insensitive(): void
    {
        $this->makeTarifItem('Kardus', 'anorganik', 1500);

        $result = $this->matcher()->match('kardus');

        $this->assertSame('exact', $result['method']);
        $this->assertSame(100, $result['confidence']);
    }

    #[Test]
    public function fuzzy_match_finds_close_name(): void
    {
        $this->makeTarifItem('Kardus', 'anorganik', 1500);

        // "kardu" = Kardus missing last letter
        $result = $this->matcher()->match('kardu');

        $this->assertSame('fuzzy', $result['method']);
        $this->assertGreaterThanOrEqual(WasteMatcher::THRESHOLD_FUZZY, $result['confidence']);
        $this->assertFalse($result['gunakan_flat']);
    }

    #[Test]
    public function infer_organik_type_from_keyword(): void
    {
        $result = $this->matcher()->match('sisa makanan');

        $this->assertSame('infer_type', $result['method']);
        $this->assertSame('organik', $result['tipe_sampah']);
        $this->assertTrue($result['gunakan_flat']);
    }

    #[Test]
    public function infer_anorganik_type_from_keyword(): void
    {
        $result = $this->matcher()->match('plastik hitam');

        $this->assertSame('infer_type', $result['method']);
        $this->assertSame('anorganik', $result['tipe_sampah']);
        $this->assertTrue($result['gunakan_flat']);
    }

    #[Test]
    public function unknown_waste_returns_flat_with_zero_confidence(): void
    {
        $result = $this->matcher()->match('xyz benda asing');

        $this->assertSame('unknown', $result['method']);
        $this->assertSame(0, $result['confidence']);
        $this->assertTrue($result['gunakan_flat']);
        $this->assertNull($result['tipe_sampah']);
    }

    #[Test]
    public function empty_string_returns_flat_empty_method(): void
    {
        $result = $this->matcher()->match('');

        $this->assertSame('empty', $result['method']);
        $this->assertTrue($result['gunakan_flat']);
    }

    #[Test]
    public function sinonim_takes_priority_over_exact_tarif_item(): void
    {
        $kardus  = $this->makeTarifItem('Kardus', 'anorganik', 1500);
        $botol   = $this->makeTarifItem('Botol PET', 'anorganik', 800);

        // Sinonim maps "kardus" to Botol PET (hypothetical test override)
        $this->makeSinonim('kardus', $botol);

        $result = $this->matcher()->match('kardus');

        $this->assertSame('sinonim', $result['method']);
        $this->assertSame($botol->id, $result['tarif_item']->id);
    }

    #[Test]
    public function tarif_item_without_active_tarif_falls_to_flat(): void
    {
        // TarifItem exists but no RiwayatTarif (tarifAktif returns null)
        TarifItem::create([
            'nama_item'           => 'Item Tanpa Tarif',
            'tipe_sampah'         => 'organik',
            'status'              => 'aktif',
            'dibuat_oleh_user_id' => $this->admin->id,
        ]);

        $result = $this->matcher()->match('Item Tanpa Tarif');

        $this->assertSame('exact', $result['method']);
        $this->assertTrue($result['gunakan_flat']); // no active tarif → flat
        $this->assertNull($result['harga_per_kg']);
    }
}
