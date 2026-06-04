<?php

namespace App\Support;

use Illuminate\Support\Collection;

class SignedMoney
{
    protected static function systemFlowValue(int|float|string|null $amount): float
    {
        return -1 * (float) ($amount ?? 0);
    }

    public static function formatCurrency(int|float|string|null $amount): string
    {
        $value = (float) ($amount ?? 0);

        return 'Rp ' . number_format(abs($value), 0, ',', '.');
    }

    public static function formatSigned(int|float|string|null $amount): string
    {
        $value = self::systemFlowValue($amount);

        if ($value > 0) {
            return '+'.self::formatCurrency($value);
        }

        if ($value < 0) {
            return '-'.self::formatCurrency($value);
        }

        return self::formatCurrency(0);
    }

    public static function describe(int|float|string|null $amount, string $subject = 'Warga'): string
    {
        $value = self::systemFlowValue($amount);

        if ($value > 0) {
            return "Pemasukan " . self::formatCurrency($value);
        }

        if ($value < 0) {
            return "Pengeluaran " . self::formatCurrency($value);
        }

        return "Tidak ada pemasukan atau pengeluaran";
    }

    public static function positiveTotal(iterable $amounts): float
    {
        return (float) collect($amounts)
            ->map(fn ($amount) => (float) $amount)
            ->filter(fn (float $amount) => $amount > 0)
            ->sum();
    }

    public static function negativeTotal(iterable $amounts): float
    {
        return (float) collect($amounts)
            ->map(fn ($amount) => (float) $amount)
            ->filter(fn (float $amount) => $amount < 0)
            ->map(fn (float $amount) => abs($amount))
            ->sum();
    }

    public static function netTotal(iterable $amounts): float
    {
        return (float) collect($amounts)
            ->map(fn ($amount) => (float) $amount)
            ->sum();
    }

    public static function toCollection(iterable $amounts): Collection
    {
        return collect($amounts)->map(fn ($amount) => (float) $amount);
    }
}
