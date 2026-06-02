<?php

namespace App\Models;

use App\Support\SignedMoney;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Setoran extends Model
{
    public const PAYMENT_STATUS_FILTERS = [
        'belum_dibayar_warga',
        'belum_dibayar_petugas',
        'sudah_dibayar_warga',
        'sudah_dibayar_petugas',
    ];

    protected $table = 'setoran';

    protected $fillable = [
        'warga_id',
        'petugas_id',
        'tanggal_setoran',
        'catatan_kondisi',
        'total_nilai',
        'status_pembayaran',
        // v5 fields
        'area_rw',
        'total_kg',
        'total_kg_dipilah',
        'total_kg_tidak_dipilah',
        'nilai',
        'mode',
        'sumber_input',
        'is_selesai',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_setoran'         => 'datetime',
            'total_nilai'             => 'decimal:2',
            'total_kg'                => 'decimal:2',
            'total_kg_dipilah'        => 'decimal:2',
            'total_kg_tidak_dipilah'  => 'decimal:2',
            'nilai'                   => 'decimal:2',
            'is_selesai'              => 'boolean',
        ];
    }

    public function isAgregat(): bool
    {
        return $this->mode === 'agregat';
    }

    public function nilaiFormatted(): string
    {
        return SignedMoney::describe($this->nilai);
    }

    public function nilaiSignedFormatted(): string
    {
        return SignedMoney::formatSigned($this->nilai);
    }

    public function statusSederhanaLabel(): string
    {
        return $this->is_selesai ? 'Selesai' : 'Draft';
    }

    public static function paymentStatusFilters(): array
    {
        return self::PAYMENT_STATUS_FILTERS;
    }

    public function isPaymentByWarga(): bool
    {
        return (float) $this->nilai < 0;
    }

    public function isPaymentByPetugas(): bool
    {
        return (float) $this->nilai > 0;
    }

    public function hasPaymentFlow(): bool
    {
        return (float) $this->nilai !== 0.0;
    }

    public function paymentActorLabel(): string
    {
        if ($this->isPaymentByWarga()) {
            return 'Warga';
        }

        if ($this->isPaymentByPetugas()) {
            return 'Petugas';
        }

        return 'Tidak Ada';
    }

    public function paymentActorLabelLower(): string
    {
        return strtolower($this->paymentActorLabel());
    }

    public function paymentStatusKey(): string
    {
        if (!$this->hasPaymentFlow()) {
            return 'tidak_ada_pembayaran';
        }

        return $this->sudahDibayar()
            ? 'sudah_dibayar_' . $this->paymentActorLabelLower()
            : 'belum_dibayar_' . $this->paymentActorLabelLower();
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->paymentStatusKey()) {
            'belum_dibayar_warga' => 'Belum Dibayar Warga',
            'belum_dibayar_petugas' => 'Belum Dibayar Petugas',
            'sudah_dibayar_warga' => 'Sudah Dibayar Warga',
            'sudah_dibayar_petugas' => 'Sudah Dibayar Petugas',
            default => 'Tidak Ada Pembayaran',
        };
    }

    public function paymentStatusBadgeClasses(): string
    {
        return match ($this->paymentStatusKey()) {
            'belum_dibayar_warga' => 'bg-danger-subtle text-danger',
            'belum_dibayar_petugas' => 'bg-warning-subtle text-warning',
            'sudah_dibayar_warga', 'sudah_dibayar_petugas' => 'bg-success-subtle text-success',
            default => 'bg-secondary-subtle text-secondary',
        };
    }

    public function paymentStatusCardClasses(): string
    {
        return match ($this->paymentStatusKey()) {
            'belum_dibayar_warga' => 'border-danger',
            'belum_dibayar_petugas' => 'border-warning',
            'sudah_dibayar_warga', 'sudah_dibayar_petugas' => 'border-success',
            default => 'border-secondary',
        };
    }

    public function paymentStatusIconClasses(): string
    {
        return match ($this->paymentStatusKey()) {
            'belum_dibayar_warga' => 'ri-alert-line text-danger',
            'belum_dibayar_petugas' => 'ri-time-line text-warning',
            'sudah_dibayar_warga', 'sudah_dibayar_petugas' => 'ri-checkbox-circle-fill text-success',
            default => 'ri-information-line text-secondary',
        };
    }

    public function signedAmountTextClasses(): string
    {
        if ($this->isPaymentByWarga()) {
            return 'text-success';
        }

        if ($this->isPaymentByPetugas()) {
            return 'text-danger';
        }

        return 'text-muted';
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
