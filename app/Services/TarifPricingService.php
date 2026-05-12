<?php

namespace App\Services;

use App\Models\RiwayatTarif;
use App\Models\TarifItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TarifPricingService
{
    public function addPriceHistory(
        TarifItem $tarifItem,
        int|float|string $hargaPerKg,
        string $tanggalMulaiInput,
        ?string $alasanPerubahan,
        int $actorId
    ): RiwayatTarif {
        return DB::transaction(function () use ($tarifItem, $hargaPerKg, $tanggalMulaiInput, $alasanPerubahan, $actorId) {
            $tanggalMulai = Carbon::parse($tanggalMulaiInput)->startOfDay();

            $riwayatSebelumnya = $tarifItem->riwayatTarif()
                ->where('tanggal_mulai', '<=', $tanggalMulai->toDateString())
                ->where(function ($query) use ($tanggalMulai) {
                    $query
                        ->whereNull('tanggal_akhir')
                        ->orWhere('tanggal_akhir', '>=', $tanggalMulai->toDateString());
                })
                ->orderByDesc('tanggal_mulai')
                ->first();

            $riwayatBerikutnya = $tarifItem->riwayatTarif()
                ->where('tanggal_mulai', '>', $tanggalMulai->toDateString())
                ->orderBy('tanggal_mulai')
                ->first();

            if ($riwayatSebelumnya && $riwayatSebelumnya->tanggal_mulai->isSameDay($tanggalMulai)) {
                throw ValidationException::withMessages([
                    'tanggal_mulai' => 'Sudah ada tarif yang dimulai pada tanggal tersebut.',
                ]);
            }

            if ($riwayatBerikutnya && $riwayatBerikutnya->tanggal_mulai->isSameDay($tanggalMulai)) {
                throw ValidationException::withMessages([
                    'tanggal_mulai' => 'Sudah ada tarif lain yang dimulai pada tanggal tersebut.',
                ]);
            }

            if ($riwayatSebelumnya) {
                $tanggalAkhirSebelumnya = $tanggalMulai->copy()->subDay()->toDateString();

                if ($riwayatSebelumnya->tanggal_mulai->gt($tanggalAkhirSebelumnya)) {
                    throw ValidationException::withMessages([
                        'tanggal_mulai' => 'Tanggal mulai baru bertabrakan dengan riwayat tarif sebelumnya.',
                    ]);
                }

                $riwayatSebelumnya->update([
                    'tanggal_akhir' => $tanggalAkhirSebelumnya,
                ]);
            }

            $tanggalAkhirBaru = $riwayatBerikutnya
                ? $riwayatBerikutnya->tanggal_mulai->copy()->subDay()->toDateString()
                : null;

            if ($tanggalAkhirBaru !== null && $tanggalMulai->gt($tanggalAkhirBaru)) {
                throw ValidationException::withMessages([
                    'tanggal_mulai' => 'Tanggal mulai baru bertabrakan dengan riwayat tarif berikutnya.',
                ]);
            }

            return RiwayatTarif::create([
                'tarif_item_id' => $tarifItem->id,
                'harga_per_kg' => $hargaPerKg,
                'tanggal_mulai' => $tanggalMulai->toDateString(),
                'tanggal_akhir' => $tanggalAkhirBaru,
                'alasan_perubahan' => $alasanPerubahan,
                'diubah_oleh_user_id' => $actorId,
            ]);
        });
    }

    public function lookupByItemAndDate(TarifItem|int $tarifItem, string $tanggal): ?RiwayatTarif
    {
        $tarifItemId = $tarifItem instanceof TarifItem ? $tarifItem->id : $tarifItem;
        $tanggalLookup = Carbon::parse($tanggal)->toDateString();

        return RiwayatTarif::query()
            ->where('tarif_item_id', $tarifItemId)
            ->where('tanggal_mulai', '<=', $tanggalLookup)
            ->where(function ($query) use ($tanggalLookup) {
                $query
                    ->whereNull('tanggal_akhir')
                    ->orWhere('tanggal_akhir', '>=', $tanggalLookup);
            })
            ->orderByDesc('tanggal_mulai')
            ->first();
    }

    public function resolveActiveHistory(
        EloquentCollection|Collection $riwayatCollection,
        ?string $tanggal = null
    ): ?RiwayatTarif {
        $tanggalLookup = Carbon::parse($tanggal ?? now())->startOfDay();
        $sorted = collect($riwayatCollection)->sortByDesc('tanggal_mulai')->values();

        return $sorted->first(function (RiwayatTarif $riwayat) use ($tanggalLookup) {
            return $riwayat->tanggal_mulai?->lte($tanggalLookup)
                && (is_null($riwayat->tanggal_akhir) || $riwayat->tanggal_akhir?->gte($tanggalLookup));
        }) ?? $sorted->first();
    }
}
