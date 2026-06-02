<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Setoran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'aktor' => ['nullable', Rule::in(['warga', 'petugas'])],
        ]);

        $query = Pembayaran::with(['setoran.warga', 'petugasPembayar'])
            ->latest('tanggal_bayar');

        if ($request->filled('dari')) {
            $query->whereDate('tanggal_bayar', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_bayar', '<=', $request->sampai);
        }
        if (!empty($validated['aktor'])) {
            $operator = $validated['aktor'] === 'warga' ? '<' : '>';
            $query->whereHas('setoran', fn ($setoran) => $setoran->where('nilai', $operator, 0));
        }

        $pembayaran = $query->paginate(20)->withQueryString();

        $ringkasan = (clone $query)
            ->reorder()
            ->selectRaw("
                SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM setoran
                    WHERE setoran.id = pembayaran.setoran_id
                      AND setoran.nilai < 0
                ) THEN jumlah_dibayar ELSE 0 END) AS total_warga,
                SUM(CASE WHEN EXISTS (
                    SELECT 1 FROM setoran
                    WHERE setoran.id = pembayaran.setoran_id
                      AND setoran.nilai > 0
                ) THEN jumlah_dibayar ELSE 0 END) AS total_petugas
            ")
            ->first();

        return view('sips.pembayaran.index', compact('pembayaran', 'ringkasan'));
    }

    public function store(Request $request, Setoran $setoran)
    {
        abort_if($setoran->sudahDibayar(), 409, 'Setoran ini sudah dibayar.');

        $validated = $request->validate([
            'jumlah_dibayar' => ['required', 'numeric', 'min:0'],
            'catatan'        => ['nullable', 'string', 'max:500'],
        ], [
            'jumlah_dibayar.required' => 'Jumlah dibayar harus diisi.',
            'jumlah_dibayar.min'      => 'Jumlah tidak boleh negatif.',
        ]);

        DB::transaction(function () use ($setoran, $validated) {
            $setoran->pembayaran()->create([
                'petugas_pembayar_id' => auth()->id() ?? 1,
                'tanggal_bayar'       => now(),
                'jumlah_dibayar'      => $validated['jumlah_dibayar'],
                'catatan'             => $validated['catatan'] ?? null,
            ]);

            $setoran->update(['status_pembayaran' => 'sudah_dibayar']);
        });

        return redirect()->route('sips.setoran.show', $setoran->id)
            ->with('success', 'Pembayaran berhasil dicatat.');
    }
}
