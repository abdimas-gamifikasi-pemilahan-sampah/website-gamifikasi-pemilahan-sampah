<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TarifItem extends Model
{
    protected $table = 'tarif_items';

    public const MAIN_TYPES = ['organik', 'anorganik'];

    public const TYPE_LABELS = [
        'organik' => 'Organik',
        'anorganik' => 'Anorganik',
    ];

    protected $fillable = [
        'nama_item',
        'tipe_sampah',
        'status',
        'dibuat_oleh_user_id',
    ];

    public static function mainTypes(): array
    {
        return self::MAIN_TYPES;
    }

    public static function typeLabels(): array
    {
        return self::TYPE_LABELS;
    }

    public function pembuatUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh_user_id');
    }

    public function riwayatTarif(): HasMany
    {
        return $this->hasMany(RiwayatTarif::class, 'tarif_item_id');
    }

    public function tarifAktif(?string $tanggal = null): ?RiwayatTarif
    {
        $tanggalLookup = Carbon::parse($tanggal ?? now())->toDateString();

        return $this->riwayatTarif()
            ->where('tanggal_mulai', '<=', $tanggalLookup)
            ->where(function ($query) use ($tanggalLookup) {
                $query
                    ->whereNull('tanggal_akhir')
                    ->orWhere('tanggal_akhir', '>=', $tanggalLookup);
            })
            ->orderByDesc('tanggal_mulai')
            ->first();
    }
}
