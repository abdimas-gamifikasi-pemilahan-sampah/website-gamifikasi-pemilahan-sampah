<?php

namespace Tests\Unit;

use App\Models\Warga;
use App\Services\WargaMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WargaMatcherTest extends TestCase
{
    use RefreshDatabase;

    private function makeWarga(array $attrs): Warga
    {
        return Warga::create(array_merge([
            'no_kk'              => str_pad((string) rand(1000000000000000, 9999999999999999), 16, '0'),
            'rt'                 => '01',
            'rw'                 => '06',
            'dusun'              => 'Dusun A',
            'tanggal_terdaftar'  => '2024-01-01',
            'status_keanggotaan' => 'aktif',
        ], $attrs));
    }

    private function matcher(array $wargaAttrs): WargaMatcher
    {
        $list = collect($wargaAttrs)->map(fn ($a) => $this->makeWarga($a));
        return new WargaMatcher($list);
    }

    #[Test]
    public function exact_name_returns_100_confidence(): void
    {
        $m = $this->matcher([['nama' => 'Siti Rahayu Wulandari']]);

        $result = $m->match('Siti Rahayu Wulandari');

        $this->assertSame(100, $result['confidence']);
        $this->assertTrue($result['auto_matched']);
        $this->assertNotNull($result['warga']);
        $this->assertSame('Siti Rahayu Wulandari', $result['warga']->nama);
    }

    #[Test]
    public function honorific_bu_is_stripped_before_matching(): void
    {
        $m = $this->matcher([['nama' => 'Siti Rahayu']]);

        $result = $m->match('Bu Siti Rahayu');

        $this->assertGreaterThanOrEqual(WargaMatcher::THRESHOLD_AUTO, $result['confidence']);
        $this->assertTrue($result['auto_matched']);
    }

    #[Test]
    public function honorific_pak_is_stripped_before_matching(): void
    {
        $m = $this->matcher([['nama' => 'Budi Santoso']]);

        $result = $m->match('Pak Budi Santoso');

        $this->assertGreaterThanOrEqual(WargaMatcher::THRESHOLD_AUTO, $result['confidence']);
    }

    #[Test]
    public function partial_first_name_matches_with_high_confidence(): void
    {
        $m = $this->matcher([['nama' => 'Siti Rahayu Wulandari']]);

        $result = $m->match('Siti Rahayu');

        $this->assertGreaterThanOrEqual(WargaMatcher::THRESHOLD_AUTO, $result['confidence']);
        $this->assertTrue($result['auto_matched']);
    }

    #[Test]
    public function one_char_typo_still_matches(): void
    {
        $m = $this->matcher([['nama' => 'Siti Rahayu']]);

        // "Rahyu" = Rahayu with missing 'a'
        $result = $m->match('Siti Rahyu');

        $this->assertGreaterThanOrEqual(WargaMatcher::THRESHOLD_SUGGEST, $result['confidence']);
        $this->assertNotEmpty($result['candidates']);
    }

    #[Test]
    public function rw_match_increases_confidence(): void
    {
        $m = $this->matcher([
            ['nama' => 'Budi Santoso', 'rw' => '06'],
            ['nama' => 'Budi Prasetyo', 'rw' => '03'],
        ]);

        $result = $m->match('Budi', '06');

        // "Budi Santoso" same RW should rank higher than "Budi Prasetyo"
        $this->assertSame('Budi Santoso', $result['candidates'][0]['warga']->nama);
    }

    #[Test]
    public function question_marks_returns_empty_result(): void
    {
        $m = $this->matcher([['nama' => 'Budi Santoso']]);

        $result = $m->match('???');

        $this->assertSame(0, $result['confidence']);
        $this->assertFalse($result['auto_matched']);
        $this->assertNull($result['warga']);
        $this->assertEmpty($result['candidates']);
    }

    #[Test]
    public function empty_name_returns_empty_result(): void
    {
        $m = $this->matcher([['nama' => 'Budi Santoso']]);

        $result = $m->match('');

        $this->assertSame(0, $result['confidence']);
        $this->assertFalse($result['auto_matched']);
    }

    #[Test]
    public function completely_different_name_has_low_confidence(): void
    {
        $m = $this->matcher([['nama' => 'Siti Rahayu']]);

        $result = $m->match('Ahmad Fauzi');

        $this->assertLessThan(WargaMatcher::THRESHOLD_SUGGEST, $result['confidence']);
        $this->assertEmpty($result['candidates']);
        $this->assertFalse($result['auto_matched']);
    }

    #[Test]
    public function case_insensitive_match(): void
    {
        $m = $this->matcher([['nama' => 'Siti Rahayu']]);

        $result = $m->match('siti rahayu');

        $this->assertSame(100, $result['confidence']);
    }

    #[Test]
    public function returns_top_3_candidates_max(): void
    {
        $m = $this->matcher([
            ['nama' => 'Siti Rahayu'],
            ['nama' => 'Siti Aminah'],
            ['nama' => 'Siti Nurbaya'],
            ['nama' => 'Siti Khodijah'],
        ]);

        $result = $m->match('Siti');

        $this->assertLessThanOrEqual(3, count($result['candidates']));
    }
}
