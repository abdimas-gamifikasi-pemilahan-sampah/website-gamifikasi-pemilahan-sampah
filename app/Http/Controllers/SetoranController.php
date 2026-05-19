<?php

namespace App\Http\Controllers;

use App\Models\ItemSetoran;
use App\Models\Setoran;
use App\Models\TarifItem;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SetoranController extends Controller
{
    public function index(Request $request)
    {
        $query = Setoran::with(['warga', 'items', 'pembayaran'])
            ->latest('tanggal_setoran');

        if ($request->filled('status')) {
            $query->where('status_pembayaran', $request->status);
        }

        $setoran = $query->paginate(20)->withQueryString();

        return view('sips.setoran.index', compact('setoran'));
    }

    public function create()
    {
        $warga = Warga::where('status_keanggotaan', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama', 'rt', 'rw', 'dusun']);

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

        return view('sips.setoran.create', compact('warga', 'tarifItems', 'tarifJson'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warga_id'                      => ['required', 'exists:warga,id'],
            'tanggal_setoran'               => ['required', 'date', 'before_or_equal:today'],
            'catatan_kondisi'               => ['nullable', 'string', 'max:500'],
            'items'                         => ['required', 'array', 'min:1'],
            'items.*.status_pemilahan'      => ['required', Rule::in(ItemSetoran::statusPemilahanOptions())],
            'items.*.tarif_item_id'         => ['nullable', 'exists:tarif_items,id'],
            'items.*.berat_kg'              => ['required', 'numeric', 'min:0.1'],
            'items.*.harga_tidak_dipilah'   => ['nullable', 'numeric', 'min:0'],
        ], [
            'warga_id.required'               => 'Warga harus dipilih.',
            'tanggal_setoran.required'        => 'Tanggal setoran harus diisi.',
            'tanggal_setoran.before_or_equal' => 'Tanggal setoran tidak boleh lebih dari hari ini.',
            'items.required'                  => 'Minimal satu item sampah harus ditambahkan.',
            'items.*.status_pemilahan.required' => 'Status pemilahan harus dipilih untuk setiap item.',
            'items.*.berat_kg.min'            => 'Berat minimal 0.1 kg.',
        ]);

        // Extra: dipilah items must have a tarif_item_id
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
                    $subtotalTidak = round($nilaiTukar, 2);
                    $totalNilai   += $subtotalTidak;

                    $itemRows[] = [
                        'tarif_item_id'         => null,
                        'riwayat_tarif_id'      => null,
                        'tipe_sampah'           => null,
                        'status_pemilahan'      => 'tidak_dipilah',
                        'berat_kg'              => $item['berat_kg'],
                        'harga_per_kg_saat_itu' => null,
                        'subtotal'              => $subtotalTidak,
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
            ]);

            $setoran->items()->createMany($itemRows);

            return $setoran;
        });

        return redirect()->route('sips.setoran.show', $setoran->id)
            ->with('success', 'Setoran berhasil dicatat.');
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
}
