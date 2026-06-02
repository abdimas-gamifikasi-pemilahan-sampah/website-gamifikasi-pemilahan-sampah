<?php

namespace App\Http\Controllers;

use App\Models\Warga;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WargaController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'rw' => ['nullable', 'string', 'max:5'],
            'status_keanggotaan' => ['nullable', Rule::in(['aktif', 'non_aktif'])],
            'belum_setor' => ['nullable', 'boolean'],
        ]);

        $periodeMulai = now()->startOfMonth();
        $periodeAkhir = now()->endOfMonth();

        $filteredQuery = Warga::query()
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
            ->when(
                $validated['belum_setor'] ?? false,
                fn ($query) => $query->whereDoesntHave('setoran', fn ($q) => $q
                    ->whereBetween('tanggal_setoran', [$periodeMulai, $periodeAkhir]))
            );

        $warga = (clone $filteredQuery)
            ->withExists([
                'setoran as memiliki_setoran_bulan_ini' => fn ($query) => $query
                    ->whereBetween('tanggal_setoran', [$periodeMulai, $periodeAkhir]),
            ])
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        $ringkasan = (clone $filteredQuery)
            ->reorder()
            ->selectRaw("
                COUNT(*) AS total_warga,
                SUM(CASE WHEN status_keanggotaan = 'aktif' THEN 1 ELSE 0 END) AS total_aktif,
                SUM(CASE WHEN status_keanggotaan = 'non_aktif' THEN 1 ELSE 0 END) AS total_non_aktif
            ")
            ->first();

        $rwOptions = Warga::query()
            ->select('rw')
            ->distinct()
            ->orderBy('rw')
            ->pluck('rw');

        return view('sips.warga.index', compact('warga', 'rwOptions', 'ringkasan'));
    }

    public function create(Request $request): View
    {
        $prefill = $request->only(['nama', 'rt', 'rw']);
        return view('sips.warga.create', compact('prefill'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        Warga::create($validated);

        return redirect()
            ->route('sips.warga.index')
            ->with('success', 'Data warga berhasil ditambahkan.');
    }

    public function edit(Warga $warga): View
    {
        return view('sips.warga.edit', compact('warga'));
    }

    public function update(Request $request, Warga $warga): RedirectResponse
    {
        $validated = $this->validatePayload($request, $warga);

        $warga->update($validated);

        return redirect()
            ->route('sips.warga.index')
            ->with('success', 'Data warga berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Warga $warga): RedirectResponse
    {
        $validated = $request->validate([
            'status_keanggotaan' => ['required', Rule::in(['aktif', 'non_aktif'])],
            'filter_search' => ['nullable', 'string', 'max:100'],
            'filter_rw' => ['nullable', 'string', 'max:5'],
            'filter_status_keanggotaan' => ['nullable', Rule::in(['aktif', 'non_aktif'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $warga->update([
            'status_keanggotaan' => $validated['status_keanggotaan'],
        ]);

        return redirect()
            ->route('sips.warga.index', [
                'search' => $validated['filter_search'] ?? null,
                'rw' => $validated['filter_rw'] ?? null,
                'status_keanggotaan' => $validated['filter_status_keanggotaan'] ?? null,
                'page' => $validated['page'] ?? null,
            ])
            ->with('success', 'Status warga berhasil diperbarui.');
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
            'rt' => ['required', 'integer', 'min:1', 'max:999'],
            'rw' => ['required', 'integer', 'min:1', 'max:999'],
            'dusun' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'tanggal_terdaftar' => ['required', 'date'],
            'status_keanggotaan' => ['required', Rule::in(['aktif', 'non_aktif'])],
        ], [
            'no_kk.regex' => 'Nomor KK harus 16 digit angka.',
        ]);
    }
}
