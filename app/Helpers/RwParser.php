<?php

namespace App\Helpers;

class RwParser
{
    /**
     * Normalize any RW string from real Excel data into a clean comma-separated list.
     *
     * Handles:
     * - Single:   "6", "06", "RW 06"
     * - Multi &:  "06 & 01", "06 & 02"
     * - Multi ,:  "01, 05", "05, 01, 06"
     * - Mixed:    "02, 01, 05"
     *
     * Returns e.g. "06, 01" (zero-padded, comma-separated).
     * Returns empty string if input is blank.
     */
    public static function normalize(mixed $input): string
    {
        if ($input === null || trim((string) $input) === '') {
            return '';
        }

        $str = (string) $input;

        // Remove "RW" prefix anywhere
        $str = preg_replace('/\bRW\s*/i', '', $str);

        // Split on '&', ',', or whitespace-only separators
        $parts = preg_split('/[\&,\s]+/', $str, -1, PREG_SPLIT_NO_EMPTY);

        $normalized = array_map(function (string $part): string {
            $part = trim($part);
            if (is_numeric($part)) {
                return str_pad($part, 2, '0', STR_PAD_LEFT);
            }
            return $part;
        }, $parts);

        $normalized = array_filter($normalized);

        return implode(', ', $normalized);
    }

    /**
     * Return individual RW codes as an array.
     * Useful when you need to iterate over each RW in a multi-RW entry.
     */
    public static function toArray(mixed $input): array
    {
        $normalized = self::normalize($input);
        if ($normalized === '') {
            return [];
        }

        return array_map('trim', explode(', ', $normalized));
    }
}
