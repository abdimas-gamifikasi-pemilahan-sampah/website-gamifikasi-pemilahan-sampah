<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warga extends Model
{
    protected $table = 'warga';

    protected $fillable = [
        'nama',
        'no_kk',
        'alamat',
        'rt',
        'rw',
        'dusun',
        'no_hp',
        'tanggal_terdaftar',
        'status_keanggotaan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_terdaftar' => 'date',
        ];
    }

    protected function noKk(): Attribute
    {
        return Attribute::make(
            set: function (mixed $value) {
                if ($value === null || $value === '') {
                    return null;
                }
                return substr(preg_replace('/\D/', '', trim((string) $value)), 0, 16) ?: null;
            },
        );
    }

    public function setoran(): HasMany
    {
        return $this->hasMany(Setoran::class);
    }
}
