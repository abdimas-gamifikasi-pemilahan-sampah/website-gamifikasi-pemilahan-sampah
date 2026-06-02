<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSistem extends Model
{
    protected $table = 'pengaturan_sistem';

    protected $fillable = ['kunci', 'nilai', 'deskripsi'];

    public static function get(string $kunci, mixed $default = null): mixed
    {
        $row = static::where('kunci', $kunci)->first();
        return $row ? $row->nilai : $default;
    }

    public static function getFloat(string $kunci, int|float $default = 0): float
    {
        return (float) static::get($kunci, $default);
    }

    public static function set(string $kunci, mixed $nilai, ?string $deskripsi = null): void
    {
        static::updateOrCreate(
            ['kunci' => $kunci],
            array_filter(['nilai' => $nilai, 'deskripsi' => $deskripsi], fn($v) => $v !== null)
        );
    }
}
