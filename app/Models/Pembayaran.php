<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    public $timestamps = false;

    protected $fillable = [
        'setoran_id',
        'petugas_pembayar_id',
        'tanggal_bayar',
        'jumlah_dibayar',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_bayar'  => 'datetime',
            'jumlah_dibayar' => 'decimal:2',
            'created_at'     => 'datetime',
        ];
    }

    public function setoran(): BelongsTo
    {
        return $this->belongsTo(Setoran::class);
    }

    public function petugasPembayar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_pembayar_id');
    }
}
