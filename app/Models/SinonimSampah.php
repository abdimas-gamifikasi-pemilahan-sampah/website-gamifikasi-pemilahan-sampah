<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SinonimSampah extends Model
{
    protected $table = 'sinonim_sampah';

    protected $fillable = [
        'kata_kunci',
        'tarif_item_id',
        'tipe_sampah',
        'gunakan_flat',
        'dibuat_oleh_user_id',
    ];

    protected function casts(): array
    {
        return [
            'gunakan_flat' => 'boolean',
        ];
    }

    public function tarifItem(): BelongsTo
    {
        return $this->belongsTo(TarifItem::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_user_id');
    }

    public static function findByKata(string $kata): ?self
    {
        return static::where('kata_kunci', mb_strtolower(trim($kata)))->first();
    }
}
