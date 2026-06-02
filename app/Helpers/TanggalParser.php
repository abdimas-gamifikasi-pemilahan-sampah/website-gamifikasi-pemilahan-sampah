<?php

namespace App\Helpers;

use Carbon\Carbon;

class TanggalParser
{
    private static array $bulan = [
        'januari'   => 1,  'february'  => 2,  'februari'  => 2,
        'maret'     => 3,  'april'     => 4,  'mei'       => 5,
        'juni'      => 6,  'juli'      => 7,  'agustus'   => 8,
        'september' => 9,  'oktober'   => 10, 'november'  => 11,
        'desember'  => 12,
    ];

    /**
     * Parse any date format found in real village Excel files.
     * Returns null if unparseable.
     *
     * Handles:
     * - Excel date serial integer  (e.g. 46090)
     * - dd/mm/yyyy                 (e.g. "09/03/2026")
     * - yyyy-mm-dd                 (e.g. "2026-03-09")
     * - Indonesian long text       (e.g. "Senin, 9 Maret 2026")
     * - Indonesian short text      (e.g. "9 Maret 2026")
     */
    public static function parse(mixed $input): ?Carbon
    {
        if ($input === null || $input === '') {
            return null;
        }

        // Excel serial number
        if (is_int($input) || (is_numeric($input) && !str_contains((string) $input, '/'))) {
            $serial = (int) $input;
            if ($serial > 1000 && $serial < 100000) {
                // Excel epoch: Dec 30, 1899 (accounts for Excel's 1900 leap year bug)
                return Carbon::createFromFormat('Y-m-d', '1899-12-30')->addDays($serial);
            }
        }

        $str = trim((string) $input);

        // yyyy-mm-dd
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $str)) {
            return Carbon::createFromFormat('Y-m-d', $str);
        }

        // dd/mm/yyyy
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $str, $m)) {
            return Carbon::createFromFormat('Y-m-d', "{$m[3]}-{$m[2]}-{$m[1]}");
        }

        // "Senin, 9 Maret 2026" or "9 Maret 2026"
        if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/', $str, $m)) {
            $bulanNama = strtolower($m[2]);
            $bulanNum  = self::$bulan[$bulanNama] ?? null;
            if ($bulanNum) {
                return Carbon::createFromFormat(
                    'Y-n-j',
                    "{$m[3]}-{$bulanNum}-{$m[1]}"
                );
            }
        }

        // Last resort: Carbon::parse (handles many standard formats)
        try {
            return Carbon::parse($str);
        } catch (\Exception) {
            return null;
        }
    }
}
