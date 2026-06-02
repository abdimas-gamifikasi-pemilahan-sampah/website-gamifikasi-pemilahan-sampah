<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WargaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'rw' => ['nullable', 'string', 'max:5'],
            'status_keanggotaan' => ['nullable', Rule::in(['aktif', 'non_aktif'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $periodeMulai = now()->startOfMonth();
        $periodeAkhir = now()->endOfMonth();

        $query = Warga::query()
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('nama', 'like', "%{$search}%")
                        ->orWhere('no_kk', 'like', "%{$search}%")
                        ->orWhere('dusun', 'like', "%{$search}%");
                });
            })
            ->when($validated['rw'] ?? null, fn ($query, $rw) => $query->where('rw', $rw))
            ->when(
                $validated['status_keanggotaan'] ?? null,
                fn ($query, $status) => $query->where('status_keanggotaan', $status)
            )
            ->withExists([
                'setoran as memiliki_setoran_bulan_ini' => fn ($query) => $query
                    ->whereBetween('tanggal_setoran', [$periodeMulai, $periodeAkhir]),
            ])
            ->orderBy('nama');

        $warga = $query->paginate($validated['per_page'] ?? 10)->withQueryString();

        return response()->json([
            'data' => $warga->getCollection()->map(fn (Warga $item) => $this->transformWarga($item))->values(),
            'total' => $warga->total(),
            'page' => $warga->currentPage(),
            'per_page' => $warga->perPage(),
            'last_page' => $warga->lastPage(),
        ]);
    }

    public function show(Warga $warga): JsonResponse
    {
        $warga->setAttribute(
            'memiliki_setoran_bulan_ini',
            $warga->setoran()
                ->whereBetween('tanggal_setoran', [now()->startOfMonth(), now()->endOfMonth()])
                ->exists()
        );

        return response()->json([
            'data' => $this->transformWarga($warga),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatePayload($request);

        $warga = Warga::create($validated);

        return response()->json([
            'message' => 'Warga berhasil ditambahkan.',
            'data' => $this->transformWarga($warga),
        ], 201);
    }

    public function update(Request $request, Warga $warga): JsonResponse
    {
        $validated = $this->validatePayload($request, $warga);

        $warga->update($validated);
        $warga->refresh();

        return response()->json([
            'message' => 'Data warga berhasil diperbarui.',
            'data' => $this->transformWarga($warga),
        ]);
    }

    public function updateStatus(Request $request, Warga $warga): JsonResponse
    {
        $validated = $request->validate([
            'status_keanggotaan' => ['required', Rule::in(['aktif', 'non_aktif'])],
        ]);

        $warga->update([
            'status_keanggotaan' => $validated['status_keanggotaan'],
        ]);

        return response()->json([
            'message' => 'Status warga berhasil diperbarui.',
            'data' => $this->transformWarga($warga->refresh()),
        ]);
    }

    protected function validatePayload(Request $request, ?Warga $warga = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'no_kk' => [
                'required',
                'string',
                'regex:/^\d{16}$/',
                Rule::unique('warga', 'no_kk')->ignore($warga?->id),
            ],
            'rt' => ['required', 'string', 'max:5'],
            'rw' => ['required', 'string', 'max:5'],
            'dusun' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'tanggal_terdaftar' => ['required', 'date'],
            'status_keanggotaan' => ['required', Rule::in(['aktif', 'non_aktif'])],
        ], [
            'no_kk.regex' => 'Nomor KK harus 16 digit angka.',
        ]);
    }

    protected function transformWarga(Warga $warga): array
    {
        return [
            'id' => $warga->id,
            'nama' => $warga->nama,
            'no_kk' => $warga->no_kk,
            'rt' => $warga->rt,
            'rw' => $warga->rw,
            'dusun' => $warga->dusun,
            'no_hp' => $warga->no_hp,
            'tanggal_terdaftar' => optional($warga->tanggal_terdaftar)->toDateString(),
            'status_keanggotaan' => $warga->status_keanggotaan,
            'status_partisipasi_bulan_ini' => ($warga->memiliki_setoran_bulan_ini ?? false) ? 'aktif' : 'non_aktif',
        ];
    }
}
