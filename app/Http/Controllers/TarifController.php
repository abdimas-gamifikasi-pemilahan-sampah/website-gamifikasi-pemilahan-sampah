<?php

namespace App\Http\Controllers;

use App\Models\PengaturanSistem;
use App\Models\RiwayatTarif;
use App\Models\TarifItem;
use App\Models\User;
use App\Services\TarifPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TarifController extends Controller
{
    public function __construct(
        protected TarifPricingService $tarifPricingService
    ) {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'tipe' => ['nullable', Rule::in(TarifItem::mainTypes())],
            'status' => ['nullable', Rule::in(['aktif', 'arsip'])],
        ]);

        $tarifItems = TarifItem::query()
            ->with(['riwayatTarif' => fn ($query) => $query->orderByDesc('tanggal_mulai')])
            ->when($validated['search'] ?? null, fn ($query, $search) => $query->where('nama_item', 'like', "%{$search}%"))
            ->when($validated['tipe'] ?? null, fn ($query, $tipe) => $query->where('tipe_sampah', $tipe))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('tipe_sampah')
            ->orderBy('nama_item')
            ->paginate(10)
            ->withQueryString();

        $ringkasanTipe = TarifItem::query()
            ->select('tipe_sampah', DB::raw('COUNT(*) as total'))
            ->groupBy('tipe_sampah')
            ->pluck('total', 'tipe_sampah');

        $riwayatTerbaru = RiwayatTarif::query()
            ->with(['tarifItem', 'pengubahUser'])
            ->latest('tanggal_mulai')
            ->limit(8)
            ->get();

        $settings = [
            'tarif_flat_per_kg' => (float) PengaturanSistem::get('tarif_flat_per_kg', 500),
            'nilai_dipilah_per_kg' => (float) PengaturanSistem::get(
                'nilai_dipilah_per_kg',
                PengaturanSistem::get('tarif_flat_per_kg', 500)
            ),
        ];

        return view('sips.tarif.index', compact('tarifItems', 'ringkasanTipe', 'riwayatTerbaru', 'settings'));
    }

    public function create(): View
    {
        return view('sips.tarif.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateStorePayload($request);

        $tarifItem = DB::transaction(function () use ($validated) {
            $tarifItem = TarifItem::create([
                'nama_item' => $validated['nama_item'],
                'tipe_sampah' => $validated['tipe_sampah'],
                'status' => $validated['status'] ?? 'aktif',
                'dibuat_oleh_user_id' => $this->resolveActorId(),
            ]);

            $this->createRiwayatTarif(
                $tarifItem,
                $validated['harga_per_kg_awal'],
                $validated['tanggal_mulai_awal'],
                $validated['alasan_perubahan_awal'] ?? 'Tarif awal item.'
            );

            return $tarifItem;
        });

        return redirect()
            ->route('sips.tarif.show', $tarifItem->id)
            ->with('success', 'Item tarif dan harga awal berhasil ditambahkan.');
    }

    public function show(TarifItem $tarif): View
    {
        $tarif->load([
            'pembuatUser',
            'riwayatTarif' => fn ($query) => $query->with('pengubahUser')->orderByDesc('tanggal_mulai'),
        ]);

        return view('sips.tarif.show', [
            'tarifItem' => $tarif,
        ]);
    }

    public function edit(TarifItem $tarif): View
    {
        return view('sips.tarif.edit', [
            'tarifItem' => $tarif,
        ]);
    }

    public function update(Request $request, TarifItem $tarif): RedirectResponse
    {
        $validated = $this->validateItemPayload($request, $tarif);

        $tarif->update($validated);

        return redirect()
            ->route('sips.tarif.show', $tarif->id)
            ->with('success', 'Item tarif berhasil diperbarui.');
    }

    public function createPrice(TarifItem $tarif): View
    {
        $tarif->load(['riwayatTarif' => fn ($query) => $query->orderByDesc('tanggal_mulai')]);

        return view('sips.tarif.price', [
            'tarifItem' => $tarif,
        ]);
    }

    public function storePrice(Request $request, TarifItem $tarif): RedirectResponse
    {
        $validated = $request->validate([
            'harga_per_kg' => ['required', 'numeric', 'min:1'],
            'tanggal_mulai' => ['required', 'date'],
            'alasan_perubahan' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->createRiwayatTarif(
            $tarif,
            $validated['harga_per_kg'],
            $validated['tanggal_mulai'],
            $validated['alasan_perubahan'] ?? null
        );

        return redirect()
            ->route('sips.tarif.show', $tarif->id)
            ->with('success', 'Harga tarif berhasil disimpan.');
    }

    public function updateStatus(Request $request, TarifItem $tarif): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['aktif', 'arsip'])],
            'filter_search' => ['nullable', 'string', 'max:100'],
            'filter_tipe' => ['nullable', Rule::in(TarifItem::mainTypes())],
            'filter_status' => ['nullable', Rule::in(['aktif', 'arsip'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $tarif->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('sips.tarif.index', [
                'search' => $validated['filter_search'] ?? null,
                'tipe' => $validated['filter_tipe'] ?? null,
                'status' => $validated['filter_status'] ?? null,
                'page' => $validated['page'] ?? null,
            ])
            ->with('success', 'Status item tarif berhasil diperbarui.');
    }

    protected function validateStorePayload(Request $request): array
    {
        return array_merge(
            $this->validateItemPayload($request),
            $request->validate([
                'harga_per_kg_awal' => ['required', 'numeric', 'min:1'],
                'tanggal_mulai_awal' => ['required', 'date'],
                'alasan_perubahan_awal' => ['nullable', 'string', 'max:1000'],
            ])
        );
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
            'status' => ['required', Rule::in(['aktif', 'arsip'])],
        ]);
    }

    protected function createRiwayatTarif(
        TarifItem $tarifItem,
        int|float|string $hargaPerKg,
        string $tanggalMulaiInput,
        ?string $alasanPerubahan = null
    ): RiwayatTarif {
        return $this->tarifPricingService->addPriceHistory(
            $tarifItem,
            $hargaPerKg,
            $tanggalMulaiInput,
            $alasanPerubahan,
            $this->resolveActorId()
        );
    }

    protected function resolveActorId(): int
    {
        return auth()->id() ?? User::query()->value('id') ?? User::factory()->create()->id;
    }
}
