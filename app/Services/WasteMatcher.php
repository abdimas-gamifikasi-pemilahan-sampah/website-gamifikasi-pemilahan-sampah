<?php

namespace App\Services;

use App\Models\SinonimSampah;
use App\Models\TarifItem;
use App\Models\RiwayatTarif;
use App\Models\PengaturanSistem;
use Illuminate\Support\Collection;

class WasteMatcher
{
    public const THRESHOLD_FUZZY = 75; // minimum % similarity untuk fuzzy match

    private const ORGANIK_KEYWORDS   = ['organik', 'basah', 'sisa makanan', 'daun', 'sayur', 'buah', 'nasi', 'kompos'];
    private const ANORGANIK_KEYWORDS = ['anorganik', 'plastik', 'botol', 'kaca', 'besi', 'logam', 'kaleng', 'kertas', 'karton', 'kardus', 'koran', 'styrofoam', 'aluminium'];

    private Collection $tarifItems;
    private Collection $sinonims;

    public function __construct(?Collection $tarifItems = null, ?Collection $sinonims = null)
    {
        $this->tarifItems = $tarifItems
            ?? TarifItem::where('status', 'aktif')->get();

        $this->sinonims = $sinonims
            ?? SinonimSampah::with('tarifItem')->get();
    }

    /**
     * Match a raw waste type string to a registered TarifItem or flat-rate fallback.
     *
     * @param  string|null $tanggal  ISO date string for tarif lookup (defaults to today)
     * @return array{
     *   tarif_item: TarifItem|null,
     *   riwayat_tarif: RiwayatTarif|null,
     *   tipe_sampah: string|null,
     *   harga_per_kg: float|null,
     *   gunakan_flat: bool,
     *   confidence: int,
     *   method: string,
     *   suggestions: array
     * }
     */
    public function match(string $jenisInput, ?string $tanggal = null): array
    {
        $normalized = mb_strtolower(trim($jenisInput));

        if ($normalized === '') {
            return $this->flatResult('empty');
        }

        // Level 1 — sinonim table (exact, lowercase)
        $sinonim = $this->sinonims->first(fn ($s) => $s->kata_kunci === $normalized);
        if ($sinonim) {
            if ($sinonim->gunakan_flat) {
                return $this->flatResult('sinonim_flat', $sinonim->tipe_sampah, 100);
            }
            if ($sinonim->tarifItem) {
                return $this->buildItemResult($sinonim->tarifItem, 100, 'sinonim', $tanggal);
            }
        }

        // Level 2 — exact match against tarif_items.nama_item
        $exact = $this->tarifItems->first(
            fn ($t) => mb_strtolower($t->nama_item) === $normalized
        );
        if ($exact) {
            return $this->buildItemResult($exact, 100, 'exact', $tanggal);
        }

        // Level 3 — fuzzy match
        $bestItem  = null;
        $bestScore = 0;
        $suggestions = [];

        foreach ($this->tarifItems as $item) {
            $score = $this->fuzzyScore($normalized, mb_strtolower($item->nama_item));
            if ($score >= self::THRESHOLD_FUZZY) {
                $suggestions[] = ['item' => $item, 'score' => $score];
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestItem  = $item;
            }
        }

        usort($suggestions, fn ($a, $b) => $b['score'] <=> $a['score']);

        if ($bestItem && $bestScore >= self::THRESHOLD_FUZZY) {
            $result               = $this->buildItemResult($bestItem, (int) $bestScore, 'fuzzy', $tanggal);
            $result['suggestions'] = array_slice($suggestions, 0, 3);
            return $result;
        }

        // Level 4 — infer tipe_sampah from keywords
        foreach (self::ORGANIK_KEYWORDS as $kw) {
            if (str_contains($normalized, $kw)) {
                return $this->flatResult('infer_type', 'organik', 60);
            }
        }
        foreach (self::ANORGANIK_KEYWORDS as $kw) {
            if (str_contains($normalized, $kw)) {
                return $this->flatResult('infer_type', 'anorganik', 60);
            }
        }

        // Level 5 — completely unknown
        return $this->flatResult('unknown', null, 0);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildItemResult(TarifItem $item, int $confidence, string $method, ?string $tanggal): array
    {
        $riwayat = $item->tarifAktif($tanggal);

        return [
            'tarif_item'   => $item,
            'riwayat_tarif'=> $riwayat,
            'tipe_sampah'  => $item->tipe_sampah,
            'harga_per_kg' => $riwayat ? (float) $riwayat->harga_per_kg : null,
            'gunakan_flat' => $riwayat === null,
            'confidence'   => $confidence,
            'method'       => $method,
            'suggestions'  => [],
        ];
    }

    private function flatResult(string $method, ?string $tipe = null, int $confidence = 0): array
    {
        return [
            'tarif_item'   => null,
            'riwayat_tarif'=> null,
            'tipe_sampah'  => $tipe,
            'harga_per_kg' => null,
            'gunakan_flat' => true,
            'confidence'   => $confidence,
            'method'       => $method,
            'suggestions'  => [],
        ];
    }

    private function fuzzyScore(string $a, string $b): float
    {
        similar_text($a, $b, $pct);

        $dist   = levenshtein($a, $b);
        $maxLen = max(strlen($a), strlen($b), 1);
        $levPct = (1 - $dist / $maxLen) * 100;

        return max($pct, $levPct);
    }
}
