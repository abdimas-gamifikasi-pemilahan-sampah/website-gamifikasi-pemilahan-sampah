<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TarifItem extends Model
{
    protected $table = 'tarif_items';

    protected $fillable = [
        'nama_item',
        'tipe_sampah',
        'status',
        'dibuat_oleh_user_id',
    ];

    public function pembuatUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_user_id');
    }

    public function riwayatTarif(): HasMany
    {
        return $this->hasMany(RiwayatTarif::class, 'tarif_item_id');
    }

    public function tarifAktif(): ?RiwayatTarif
    {
        return $this->riwayatTarif()
            ->whereNull('tanggal_akhir')
            ->latest('tanggal_mulai')
            ->first();
    }
}
