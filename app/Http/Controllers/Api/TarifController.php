<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RiwayatTarif;
use App\Models\TarifItem;
use App\Services\TarifPricingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TarifController extends Controller
{
    public function __construct(
        protected TarifPricingService $tarifPricingService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tipe' => ['nullable', Rule::in(TarifItem::mainTypes())],
            'status' => ['nullable', Rule::in(['aktif', 'arsip'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $items = TarifItem::query()
            ->with(['riwayatTarif' => fn ($query) => $query->orderByDesc('tanggal_mulai')])
            ->when($validated['tipe'] ?? null, fn ($query, $tipe) => $query->where('tipe_sampah', $tipe))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($validated['search'] ?? null, fn ($query, $search) => $query->where('nama_item', 'like', "%{$search}%"))
            ->orderBy('tipe_sampah')
            ->orderBy('nama_item')
            ->get();

        return response()->json([
            'data' => $items->map(fn (TarifItem $item) => $this->transformTarifItem($item))->values(),
        ]);
    }

    public function showItem(TarifItem $tarifItem): JsonResponse
    {
        $tarifItem->load(['riwayatTarif' => fn ($query) => $query->orderByDesc('tanggal_mulai')]);

        return response()->json([
            'data' => $this->transformTarifItem($tarifItem, includeHistory: true),
        ]);
    }

    public function storeItem(Request $request): JsonResponse
    {
        $validated = $this->validateItemPayload($request);

        $tarifItem = TarifItem::create([
            'nama_item' => $validated['nama_item'],
            'tipe_sampah' => $validated['tipe_sampah'],
            'status' => $validated['status'] ?? 'aktif',
            'dibuat_oleh_user_id' => auth()->id() ?? 1,
        ]);

        $tarifItem->load('riwayatTarif');

        return response()->json([
            'message' => 'Item tarif berhasil ditambahkan.',
            'data' => $this->transformTarifItem($tarifItem),
        ], 201);
    }

    public function updateItem(Request $request, TarifItem $tarifItem): JsonResponse
    {
        $validated = $this->validateItemPayload($request, $tarifItem);

        $tarifItem->update([
            'nama_item' => $validated['nama_item'],
            'tipe_sampah' => $validated['tipe_sampah'],
            'status' => $validated['status'] ?? $tarifItem->status,
        ]);

        return response()->json([
            'message' => 'Item tarif berhasil diperbarui.',
            'data' => $this->transformTarifItem($tarifItem->fresh('riwayatTarif')),
        ]);
    }

    public function storePrice(Request $request, TarifItem $tarifItem): JsonResponse
    {
        $validated = $request->validate([
            'harga_per_kg' => ['required', 'numeric', 'min:0'],
            'tanggal_mulai' => ['required', 'date'],
            'alasan_perubahan' => ['nullable', 'string', 'max:1000'],
        ]);

        $riwayatBaru = $this->tarifPricingService->addPriceHistory(
            $tarifItem,
            $validated['harga_per_kg'],
            $validated['tanggal_mulai'],
            $validated['alasan_perubahan'] ?? null,
            auth()->id() ?? 1
        );

        return response()->json([
            'message' => 'Riwayat tarif berhasil ditambahkan.',
            'data' => $this->transformRiwayatTarif($riwayatBaru->fresh()),
        ], 201);
    }

    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => ['required', 'exists:tarif_items,id'],
            'tanggal' => ['required', 'date'],
        ]);

        $riwayatTarif = $this->tarifPricingService->lookupByItemAndDate(
            (int) $validated['item_id'],
            $validated['tanggal']
        );

        if (! $riwayatTarif) {
            throw new NotFoundHttpException('Tarif tidak ditemukan untuk tanggal tersebut.');
        }

        return response()->json([
            'item_id' => $riwayatTarif->tarif_item_id,
            'harga_per_kg' => (float) $riwayatTarif->harga_per_kg,
            'riwayat_id' => $riwayatTarif->id,
        ]);
    }

    protected function validateItemPayload(Request $request, ?TarifItem $tarifItem = null): array
    {
        return $request->validate([
            'nama_item' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tarif_items', 'nama_item')
                    ->where(fn ($query) => $query->where('tipe_sampah', $request->input('tipe_sampah')))
                    ->ignore($tarifItem?->id),
            ],
            'tipe_sampah' => ['required', Rule::in(TarifItem::mainTypes())],
            'status' => ['nullable', Rule::in(['aktif', 'arsip'])],
        ]);
    }

    protected function transformTarifItem(TarifItem $tarifItem, bool $includeHistory = false): array
    {
        $riwayatCollection = $tarifItem->relationLoaded('riwayatTarif')
            ? $tarifItem->riwayatTarif->sortByDesc('tanggal_mulai')->values()
            : collect();

        $tarifAktif = $this->tarifPricingService->resolveActiveHistory($riwayatCollection);

        $data = [
            'id' => $tarifItem->id,
            'nama_item' => $tarifItem->nama_item,
            'tipe_sampah' => $tarifItem->tipe_sampah,
            'status' => $tarifItem->status,
            'dibuat_oleh_user_id' => $tarifItem->dibuat_oleh_user_id,
            'tarif_aktif' => $tarifAktif ? $this->transformRiwayatTarif($tarifAktif) : null,
        ];

        if ($includeHistory) {
            $data['riwayat_tarif'] = $riwayatCollection
                ->map(fn (RiwayatTarif $riwayat) => $this->transformRiwayatTarif($riwayat))
                ->values();
        }

        return $data;
    }

    protected function transformRiwayatTarif(RiwayatTarif $riwayatTarif): array
    {
        return [
            'id' => $riwayatTarif->id,
            'tarif_item_id' => $riwayatTarif->tarif_item_id,
            'harga_per_kg' => (float) $riwayatTarif->harga_per_kg,
            'tanggal_mulai' => optional($riwayatTarif->tanggal_mulai)->toDateString(),
            'tanggal_akhir' => optional($riwayatTarif->tanggal_akhir)->toDateString(),
            'alasan_perubahan' => $riwayatTarif->alasan_perubahan,
            'diubah_oleh_user_id' => $riwayatTarif->diubah_oleh_user_id,
        ];
    }
}
