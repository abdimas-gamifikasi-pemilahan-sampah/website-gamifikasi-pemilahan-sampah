<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatTarif extends Model
{
    protected $table = 'riwayat_tarif';

    public $timestamps = false;

    protected $fillable = [
        'tarif_item_id',
        'harga_per_kg',
        'tanggal_mulai',
        'tanggal_akhir',
        'alasan_perubahan',
        'diubah_oleh_user_id',
    ];

    protected function casts(): array
    {
        return [
            'harga_per_kg'   => 'decimal:2',
            'tanggal_mulai'  => 'date',
            'tanggal_akhir'  => 'date',
            'created_at'     => 'datetime',
        ];
    }

    public function tarifItem(): BelongsTo
    {
        return $this->belongsTo(TarifItem::class, 'tarif_item_id');
    }

    public function pengubahUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diubah_oleh_user_id');
    }
}
