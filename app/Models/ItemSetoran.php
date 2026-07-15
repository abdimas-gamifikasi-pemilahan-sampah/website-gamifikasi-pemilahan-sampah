<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Warga;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemSetoran extends Model
{
    protected $table = 'item_setoran';

    public const STATUS_PEMILAHAN = ['dipilah', 'tidak_dipilah'];

    protected $fillable = [
        'setoran_id',
        'warga_id',
        'nama_penyetor',
        'rw',
        'rt',
        'tarif_item_id',
        'riwayat_tarif_id',
        'tipe_sampah',
        'status_pemilahan',
        'berat_kg',
        'harga_per_kg_saat_itu',
        'subtotal',
        'catatan_item',
    ];

    public static function statusPemilahanOptions(): array
    {
        return self::STATUS_PEMILAHAN;
    }

    protected function casts(): array
    {
        return [
            'berat_kg'             => 'decimal:2',
            'harga_per_kg_saat_itu' => 'decimal:2',
            'subtotal'             => 'decimal:2',
        ];
    }

    public function setoran(): BelongsTo
    {
        return $this->belongsTo(Setoran::class);
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class);
    }

    public function tarifItem(): BelongsTo
    {
        return $this->belongsTo(TarifItem::class);
    }

    public function riwayatTarif(): BelongsTo
    {
        return $this->belongsTo(RiwayatTarif::class);
    }
}
