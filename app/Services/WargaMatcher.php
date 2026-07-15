<?php

namespace App\Services;

use App\Models\Warga;
use Illuminate\Support\Collection;

class WargaMatcher
{
    public const THRESHOLD_AUTO    = 80; // auto-link, bisa di-override
    public const THRESHOLD_SUGGEST = 50; // tampilkan sebagai kandidat dropdown

    private const HONORIFICS = [
        'bapak ', 'ibu ', 'pak ', 'bu ', 'hj. ', 'h. ', 'hj ', 'h ',
        'drs. ', 'dr. ', 'prof. ', 'ir. ',
    ];

    private Collection $wargaList;

    public function __construct(?Collection $wargaList = null)
    {
        $this->wargaList = $wargaList
            ?? Warga::where('status_keanggotaan', 'aktif')
                    ->get(['id', 'nama', 'rt', 'rw', 'dusun']);
    }

    /**
     * Match a raw name string against registered warga.
     *
     * @return array{
     *   warga: Warga|null,
     *   confidence: int,
     *   auto_matched: bool,
     *   candidates: array<int, array{warga: Warga, confidence: int}>
     * }
     */
    public function match(string $namaInput, ?string $rw = null): array
    {
        $namaInput = trim($namaInput);

        // "???" or empty = tidak bisa diproses
        if ($namaInput === '' || $namaInput === '???') {
            return ['warga' => null, 'confidence' => 0, 'auto_matched' => false, 'candidates' => []];
        }

        $inputNorm = $this->normalize($namaInput);
        $candidates = [];

        foreach ($this->wargaList as $warga) {
            $wargaNorm = $this->normalize($warga->nama);
            $score = $this->computeScore($inputNorm, $wargaNorm, $rw, (string) $warga->rw);

            if ($score >= self::THRESHOLD_SUGGEST) {
                $candidates[] = ['warga' => $warga, 'confidence' => $score];
            }
        }

        usort($candidates, fn ($a, $b) => $b['confidence'] <=> $a['confidence']);
        $candidates = array_slice($candidates, 0, 3);

        $best       = $candidates[0] ?? null;
        $confidence = $best['confidence'] ?? 0;

        return [
            'warga'        => ($best && $confidence >= self::THRESHOLD_AUTO) ? $best['warga'] : null,
            'confidence'   => $confidence,
            'auto_matched' => $best && $confidence >= self::THRESHOLD_AUTO,
            'candidates'   => $candidates,
        ];
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private function normalize(string $name): string
    {
        $name = mb_strtolower(trim($name));

        foreach (self::HONORIFICS as $h) {
            if (str_starts_with($name, $h)) {
                $name = ltrim(substr($name, strlen($h)));
                break; // hanya strip satu honorific terdepan
            }
        }

        return preg_replace('/\s+/', ' ', $name);
    }

    private function computeScore(string $input, string $target, ?string $inputRw, string $targetRw): int
    {
        // Level 1 — exact
        if ($input === $target) {
            return 100;
        }

        // Level 2 — contains
        if (str_contains($target, $input)) {
            return $this->rwBonus(85, $inputRw, $targetRw);
        }
        if ($input !== '' && str_contains($input, $target)) {
            return $this->rwBonus(80, $inputRw, $targetRw);
        }

        // Level 3 — token intersection with levenshtein tolerance
        $inputTokens  = explode(' ', $input);
        $targetTokens = explode(' ', $target);
        $matched = 0;

        foreach ($inputTokens as $it) {
            if (strlen($it) < 2) {
                continue;
            }
            foreach ($targetTokens as $tt) {
                if ($it === $tt) {
                    $matched++;
                    break;
                }
                // levenshtein tolerance: 1 typo for short words, 2 for longer
                $tolerance = strlen($it) >= 6 ? 2 : 1;
                if (levenshtein($it, $tt) <= $tolerance) {
                    $matched++;
                    break;
                }
            }
        }

        if ($matched > 0) {
            $tokenScore = (int) round(($matched / max(1, count($inputTokens))) * 72);
            return $this->rwBonus($tokenScore, $inputRw, $targetRw);
        }

        // Level 4 — overall levenshtein similarity
        $dist   = levenshtein($input, $target);
        $maxLen = max(strlen($input), strlen($target), 1);
        $levScore = (int) round((1 - $dist / $maxLen) * 60);

        return $this->rwBonus($levScore, $inputRw, $targetRw);
    }

    private function rwBonus(int $score, ?string $inputRw, string $targetRw): int
    {
        if ($inputRw && $inputRw === $targetRw) {
            return min(100, $score + 7);
        }
        return $score;
    }
}
