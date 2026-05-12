<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Setoran extends Model
{
    protected $table = 'setoran';

    protected $fillable = [
        'warga_id',
        'petugas_id',
        'tanggal_setoran',
        'catatan_kondisi',
        'total_nilai',
        'status_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_setoran'  => 'datetime',
            'total_nilai'      => 'decimal:2',
        ];
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemSetoran::class);
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class);
    }

    public function sudahDibayar(): bool
    {
        return $this->status_pembayaran === 'sudah_dibayar';
    }
}
