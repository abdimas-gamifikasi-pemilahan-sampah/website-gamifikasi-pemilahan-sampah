<?php

namespace App\Http\Controllers;

use App\Models\ItemSetoran;
use App\Models\PengaturanSistem;
use App\Models\Setoran;
use App\Models\TarifItem;
use App\Models\Warga;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SetoranController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(Setoran::paymentStatusFilters())],
        ]);

        $query = Setoran::query()->latest('tanggal_setoran');

        if (!empty($validated['status'])) {
            match ($validated['status']) {
                'belum_dibayar_warga' => $query
                    ->where('status_pembayaran', 'belum_dibayar')
                    ->where('nilai', '<', 0),
                'belum_dibayar_petugas' => $query
                    ->where('status_pembayaran', 'belum_dibayar')
                    ->where('nilai', '>', 0),
                'sudah_dibayar_warga' => $query
                    ->where('status_pembayaran', 'sudah_dibayar')
                    ->where('nilai', '<', 0),
                'sudah_dibayar_petugas' => $query
                    ->where('status_pembayaran', 'sudah_dibayar')
                    ->where('nilai', '>', 0),
                default => null,
            };
        }

        $setoran = (clone $query)
            ->with(['warga', 'items', 'pembayaran'])
            ->paginate(20)
            ->withQueryString();

        $ringkasan = (clone $query)
            ->reorder()
            ->selectRaw("
                COUNT(*) AS jumlah_setoran,
                SUM(CASE WHEN status_pembayaran = 'belum_dibayar' AND nilai < 0 THEN 1 ELSE 0 END) AS belum_dibayar_warga,
                SUM(CASE WHEN status_pembayaran = 'belum_dibayar' AND nilai > 0 THEN 1 ELSE 0 END) AS belum_dibayar_petugas,
                SUM(CASE WHEN status_pembayaran = 'sudah_dibayar' THEN 1 ELSE 0 END) AS sudah_dibayar,
                SUM(CASE WHEN nilai > 0 THEN nilai ELSE 0 END) AS total_nilai_plus,
                SUM(CASE WHEN nilai < 0 THEN ABS(nilai) ELSE 0 END) AS total_nilai_minus
            ")
            ->first();

        return view('sips.setoran.index', compact('setoran', 'ringkasan'));
    }

    public function create()
    {
        $tarifFlat = (float) PengaturanSistem::get('tarif_flat_per_kg', 500);

        $warga = Warga::where('status_keanggotaan', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama', 'rt', 'rw', 'dusun']);

        // For legacy per-item mode
        $tarifItems = TarifItem::where('status', 'aktif')
            ->get()
            ->each(fn($item) => $item->activeRate = $item->tarifAktif())
            ->filter(fn($item) => $item->activeRate !== null)
            ->values();

        $tarifJson = $tarifItems->mapWithKeys(fn($item) => [
            $item->id => [
                'nama'  => $item->nama_item,
                'tipe'  => $item->tipe_sampah,
                'harga' => (float) $item->activeRate->harga_per_kg,
            ],
        ]);

        $wargaJson = $warga->map(fn($w) => [
            'id'    => $w->id,
            'nama'  => $w->nama,
            'rt'    => $w->rt,
            'rw'    => $w->rw,
            'dusun' => $w->dusun,
        ]);

        return view('sips.setoran.create', compact('tarifFlat', 'tarifItems', 'tarifJson', 'wargaJson'));
    }

    public function store(Request $request): RedirectResponse
    {
        $mode = $request->input('mode', 'penyetor');

        return match ($mode) {
            'penyetor' => $this->storePenyetor($request),
            'detail'   => $this->storeDetail($request),
            default    => $this->storeLegacy($request),
        };
    }

    public function toggleSelesai(Request $request, Setoran $setoran): JsonResponse|RedirectResponse
    {
        $setoran->update(['is_selesai' => !$setoran->is_selesai]);

        if ($request->expectsJson()) {
            return response()->json(['is_selesai' => $setoran->is_selesai]);
        }

        $msg = $setoran->is_selesai ? 'Setoran ditandai Selesai.' : 'Setoran dibuka kembali.';
        return back()->with('success', $msg);
    }

    public function show(Setoran $setoran)
    {
        $setoran->load([
            'warga',
            'petugas',
            'items.tarifItem',
            'pembayaran.petugasPembayar',
        ]);

        return view('sips.setoran.show', compact('setoran'));
    }

    public function kwitansi(Setoran $setoran)
    {
        $setoran->load([
            'warga',
            'petugas',
            'items.tarifItem',
            'pembayaran.petugasPembayar',
        ]);

        return view('sips.setoran.kwitansi', compact('setoran'));
    }

    // ── Unified: penyetor cards with nested items ─────────────────────────────

    private function storePenyetor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_setoran'                         => ['required', 'date', 'before_or_equal:today'],
            'catatan_kondisi'                         => ['nullable', 'string', 'max:500'],
            'penyetor'                                => ['required', 'array', 'min:1'],
            'penyetor.*.warga_id'                     => ['nullable', 'exists:warga,id'],
            'penyetor.*.nama_penyetor'                => ['required', 'string', 'max:255'],
            'penyetor.*.rw'                           => ['nullable', 'string', 'max:10'],
            'penyetor.*.rt'                           => ['nullable', 'string', 'max:10'],
            'penyetor.*.items'                        => ['required', 'array', 'min:1'],
            'penyetor.*.items.*.status_pemilahan'     => ['required', Rule::in(['dipilah', 'tidak_dipilah'])],
            'penyetor.*.items.*.tarif_item_id'        => ['nullable', 'exists:tarif_items,id'],
            'penyetor.*.items.*.berat_kg'             => ['required', 'numeric', 'min:0.1'],
        ], [
            'penyetor.required'                           => 'Minimal satu penyetor harus diisi.',
            'penyetor.*.nama_penyetor.required'           => 'Nama penyetor wajib diisi.',
            'penyetor.*.items.required'                   => 'Setiap penyetor harus memiliki minimal satu item.',
            'penyetor.*.items.*.status_pemilahan.required'=> 'Status pilah harus dipilih untuk setiap item.',
            'penyetor.*.items.*.berat_kg.min'             => 'Berat minimal 0.1 kg.',
            'tanggal_setoran.before_or_equal'             => 'Tanggal tidak boleh lebih dari hari ini.',
        ]);

        $tarif = (float) PengaturanSistem::get('tarif_flat_per_kg', 500);

        $setoran = DB::transaction(function () use ($validated, $tarif) {
            $totalKg   = 0;
            $kgDipilah = 0;
            $kgTidak   = 0;
            $totalNilai = 0;
            $itemRows  = [];
            $rwSeen    = [];

            // If exactly one penyetor is a registered warga → link setoran to them for leaderboard
            $setoranWargaId = null;
            $penyetorList   = array_values($validated['penyetor']);
            if (count($penyetorList) === 1 && !empty($penyetorList[0]['warga_id'])) {
                $setoranWargaId = (int) $penyetorList[0]['warga_id'];
            }

            foreach ($validated['penyetor'] as $pData) {
                $namaPenyetor = $pData['nama_penyetor'];

                if (!empty($pData['rw'])) {
                    $rwSeen[] = str_pad(trim($pData['rw']), 2, '0', STR_PAD_LEFT);
                }

                foreach ($pData['items'] as $item) {
                    $berat   = (float) $item['berat_kg'];
                    $dipilah = $item['status_pemilahan'] === 'dipilah';

                    // Use item-specific tariff if selected, otherwise fall back to flat rate
                    $hargaPerKg     = $tarif;
                    $tarifItemId    = null;
                    $riwayatTarifId = null;
                    $tipeSampah     = null;

                    if ($dipilah && !empty($item['tarif_item_id'])) {
                        $tarifItem = TarifItem::find($item['tarif_item_id']);
                        $riwayat   = $tarifItem?->tarifAktif();
                        if ($riwayat) {
                            $hargaPerKg     = (float) $riwayat->harga_per_kg;
                            $tarifItemId    = $tarifItem->id;
                            $riwayatTarifId = $riwayat->id;
                            $tipeSampah     = $tarifItem->tipe_sampah;
                        }
                    }

                    $subtotal    = round($berat * $hargaPerKg * ($dipilah ? 1 : -1), 2);
                    $totalKg    += $berat;
                    $totalNilai += $subtotal;
                    $dipilah ? ($kgDipilah += $berat) : ($kgTidak += $berat);

                    $itemRows[] = [
                        'nama_penyetor'         => $namaPenyetor,
                        'tarif_item_id'         => $tarifItemId,
                        'riwayat_tarif_id'      => $riwayatTarifId,
                        'tipe_sampah'           => $tipeSampah,
                        'status_pemilahan'      => $item['status_pemilahan'],
                        'berat_kg'              => $berat,
                        'harga_per_kg_saat_itu' => $hargaPerKg,
                        'subtotal'              => $subtotal,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                }
            }

            $setoran = Setoran::create([
                'warga_id'               => $setoranWargaId,
                'petugas_id'             => auth()->id() ?? 1,
                'tanggal_setoran'        => $validated['tanggal_setoran'],
                'area_rw'                => implode(', ', array_unique($rwSeen)) ?: null,
                'total_kg'               => $totalKg,
                'total_kg_dipilah'       => $kgDipilah,
                'total_kg_tidak_dipilah' => $kgTidak,
                'nilai'                  => round($totalNilai, 2),
                'total_nilai'            => round(abs($totalNilai), 2),
                'mode'                   => 'detail',
                'sumber_input'           => 'manual',
                'status_pembayaran'      => 'belum_dibayar',
                'catatan_kondisi'        => $validated['catatan_kondisi'] ?? null,
            ]);

            $setoran->items()->createMany($itemRows);

            return $setoran;
        });

        return redirect()->route('sips.setoran.show', $setoran->id)
            ->with('success', 'Setoran berhasil dicatat.');
    }

    // ── v5 Detail per penyetor ────────────────────────────────────────────────

    private function storeDetail(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_setoran'         => ['required', 'date', 'before_or_equal:today'],
            'catatan_kondisi'         => ['nullable', 'string', 'max:500'],
            'rows'                    => ['required', 'array', 'min:1'],
            'rows.*.nama_penyetor'    => ['required', 'string', 'max:255'],
            'rows.*.rw'               => ['nullable', 'string', 'max:10'],
            'rows.*.rt'               => ['nullable', 'string', 'max:10'],
            'rows.*.berat_kg'         => ['required', 'numeric', 'min:0.1'],
            'rows.*.status_pemilahan' => ['required', Rule::in(['dipilah', 'tidak_dipilah'])],
        ], [
            'rows.required'                    => 'Minimal satu baris penyetor harus diisi.',
            'rows.*.nama_penyetor.required'    => 'Nama penyetor wajib diisi.',
            'rows.*.berat_kg.min'              => 'Berat minimal 0.1 kg.',
            'rows.*.status_pemilahan.required' => 'Status pilah harus dipilih.',
            'tanggal_setoran.before_or_equal'  => 'Tanggal tidak boleh lebih dari hari ini.',
        ]);

        $tarif = (float) PengaturanSistem::get('tarif_flat_per_kg', 500);

        $setoran = DB::transaction(function () use ($validated, $tarif) {
            $totalKg   = 0;
            $kgDipilah = 0;
            $kgTidak   = 0;
            $itemRows  = [];
            $rwSeen    = [];

            foreach ($validated['rows'] as $row) {
                $berat   = (float) $row['berat_kg'];
                $dipilah = $row['status_pemilahan'] === 'dipilah';

                $totalKg += $berat;
                $dipilah ? ($kgDipilah += $berat) : ($kgTidak += $berat);

                if (!empty($row['rw'])) {
                    $rwSeen[] = str_pad(trim($row['rw']), 2, '0', STR_PAD_LEFT);
                }

                $itemRows[] = [
                    'nama_penyetor'         => $row['nama_penyetor'],
                    'tarif_item_id'         => null,
                    'riwayat_tarif_id'      => null,
                    'tipe_sampah'           => null,
                    'status_pemilahan'      => $row['status_pemilahan'],
                    'berat_kg'              => $berat,
                    'harga_per_kg_saat_itu' => $tarif,
                    'subtotal'              => round($berat * $tarif * ($dipilah ? 1 : -1), 2),
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ];
            }

            $areaRw = implode(', ', array_unique($rwSeen)) ?: null;

            $setoran = Setoran::create([
                'warga_id'               => null,
                'petugas_id'             => auth()->id() ?? 1,
                'tanggal_setoran'        => $validated['tanggal_setoran'],
                'area_rw'                => $areaRw,
                'total_kg'               => $totalKg,
                'total_kg_dipilah'       => $kgDipilah,
                'total_kg_tidak_dipilah' => $kgTidak,
                'nilai'                  => round(($kgDipilah - $kgTidak) * $tarif, 2),
                'total_nilai'            => round($totalKg * $tarif, 2),
                'mode'                   => 'detail',
                'sumber_input'           => 'manual',
                'status_pembayaran'      => 'belum_dibayar',
                'catatan_kondisi'        => $validated['catatan_kondisi'] ?? null,
            ]);

            $setoran->items()->createMany($itemRows);

            return $setoran;
        });

        return redirect()->route('sips.setoran.show', $setoran->id)
            ->with('success', 'Setoran detail berhasil dicatat.');
    }

    // ── Legacy per-item with complex tariff (backward compat) ─────────────────

    private function storeLegacy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'warga_id'                      => ['required', 'exists:warga,id'],
            'tanggal_setoran'               => ['required', 'date', 'before_or_equal:today'],
            'catatan_kondisi'               => ['nullable', 'string', 'max:500'],
            'items'                         => ['required', 'array', 'min:1'],
            'items.*.status_pemilahan'      => ['required', Rule::in(ItemSetoran::statusPemilahanOptions())],
            'items.*.tarif_item_id'         => ['nullable', 'exists:tarif_items,id'],
            'items.*.berat_kg'              => ['required', 'numeric', 'min:0.1'],
            'items.*.harga_tidak_dipilah'   => ['nullable', 'numeric'],
        ], [
            'warga_id.required'               => 'Warga harus dipilih.',
            'tanggal_setoran.required'        => 'Tanggal setoran harus diisi.',
            'tanggal_setoran.before_or_equal' => 'Tanggal setoran tidak boleh lebih dari hari ini.',
            'items.required'                  => 'Minimal satu item sampah harus ditambahkan.',
            'items.*.status_pemilahan.required' => 'Status pemilahan harus dipilih.',
            'items.*.berat_kg.min'            => 'Berat minimal 0.1 kg.',
        ]);

        foreach ($validated['items'] as $i => $item) {
            if ($item['status_pemilahan'] === 'dipilah' && empty($item['tarif_item_id'])) {
                return back()->withErrors([
                    "items.$i.tarif_item_id" => 'Jenis sampah harus dipilih untuk sampah yang dipilah.',
                ])->withInput();
            }
        }

        $setoran = DB::transaction(function () use ($validated) {
            $totalNilai = 0;
            $itemRows   = [];

            foreach ($validated['items'] as $item) {
                if ($item['status_pemilahan'] === 'dipilah') {
                    $tarifItem    = TarifItem::find($item['tarif_item_id']);
                    $riwayatTarif = $tarifItem->tarifAktif();

                    abort_if(!$riwayatTarif, 422, "Tarif aktif tidak ditemukan untuk: {$tarifItem->nama_item}");

                    $subtotal    = round($item['berat_kg'] * $riwayatTarif->harga_per_kg, 2);
                    $totalNilai += $subtotal;

                    $itemRows[] = [
                        'tarif_item_id'         => $tarifItem->id,
                        'riwayat_tarif_id'      => $riwayatTarif->id,
                        'tipe_sampah'           => $tarifItem->tipe_sampah,
                        'status_pemilahan'      => 'dipilah',
                        'berat_kg'              => $item['berat_kg'],
                        'harga_per_kg_saat_itu' => $riwayatTarif->harga_per_kg,
                        'subtotal'              => $subtotal,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                } else {
                    $nilaiTukar    = (float) ($item['harga_tidak_dipilah'] ?? 0);
                    $totalNilai   += $nilaiTukar;

                    $itemRows[] = [
                        'tarif_item_id'         => null,
                        'riwayat_tarif_id'      => null,
                        'tipe_sampah'           => null,
                        'status_pemilahan'      => 'tidak_dipilah',
                        'berat_kg'              => $item['berat_kg'],
                        'harga_per_kg_saat_itu' => null,
                        'subtotal'              => round($nilaiTukar, 2),
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                }
            }

            $setoran = Setoran::create([
                'warga_id'          => $validated['warga_id'],
                'petugas_id'        => auth()->id() ?? 1,
                'tanggal_setoran'   => $validated['tanggal_setoran'],
                'catatan_kondisi'   => $validated['catatan_kondisi'] ?? null,
                'total_nilai'       => $totalNilai,
                'status_pembayaran' => 'belum_dibayar',
                'mode'              => 'detail',
                'sumber_input'      => 'manual',
            ]);

            $setoran->items()->createMany($itemRows);

            return $setoran;
        });

        return redirect()->route('sips.setoran.show', $setoran->id)
            ->with('success', 'Setoran berhasil dicatat.');
    }
}
