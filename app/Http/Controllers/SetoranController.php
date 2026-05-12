<?php

namespace App\Http\Controllers;

use App\Models\Setoran;
use App\Models\TarifItem;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'items.*.tarif_item_id'         => ['required', 'exists:tarif_items,id'],
            'items.*.berat_kg'              => ['required', 'numeric', 'min:0.1'],
            'items.*.status_pemilahan'      => ['required', 'in:dipilah,tidak_dipilah'],
        ], [
            'warga_id.required'             => 'Warga harus dipilih.',
            'tanggal_setoran.required'      => 'Tanggal setoran harus diisi.',
            'tanggal_setoran.before_or_equal' => 'Tanggal setoran tidak boleh lebih dari hari ini.',
            'items.required'                => 'Minimal satu item sampah harus ditambahkan.',
            'items.*.tarif_item_id.required' => 'Jenis sampah harus dipilih.',
            'items.*.berat_kg.min'          => 'Berat minimal 0.1 kg.',
            'items.*.status_pemilahan.required' => 'Status pemilahan harus dipilih.',
        ]);

        $setoran = DB::transaction(function () use ($validated) {
            $totalNilai = 0;
            $itemRows   = [];

            foreach ($validated['items'] as $item) {
                $tarifItem    = TarifItem::find($item['tarif_item_id']);
                $riwayatTarif = $tarifItem->tarifAktif();

                abort_if(!$riwayatTarif, 422, "Tarif aktif tidak ditemukan untuk: {$tarifItem->nama_item}");

                $subtotal    = round($item['berat_kg'] * $riwayatTarif->harga_per_kg, 2);
                $totalNilai += $subtotal;

                $itemRows[] = [
                    'tarif_item_id'         => $item['tarif_item_id'],
                    'riwayat_tarif_id'      => $riwayatTarif->id,
                    'tipe_sampah'           => $tarifItem->tipe_sampah,
                    'status_pemilahan'      => $item['status_pemilahan'],
                    'berat_kg'              => $item['berat_kg'],
                    'harga_per_kg_saat_itu' => $riwayatTarif->harga_per_kg,
                    'subtotal'              => $subtotal,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ];
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
