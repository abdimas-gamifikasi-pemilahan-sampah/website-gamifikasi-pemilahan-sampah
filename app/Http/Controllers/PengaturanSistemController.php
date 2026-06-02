<?php

namespace App\Http\Controllers;

use App\Models\PengaturanSistem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PengaturanSistemController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tarif_flat_per_kg' => ['required', 'numeric', 'min:0'],
            'nilai_dipilah_per_kg' => ['required', 'numeric', 'min:0'],
            'nama_tps' => ['nullable', 'string', 'max:255'],
        ]);

        PengaturanSistem::set(
            'tarif_flat_per_kg',
            (string) $validated['tarif_flat_per_kg'],
            'Tarif iuran per kilogram sampah tidak dipilah.'
        );
        PengaturanSistem::set(
            'nilai_dipilah_per_kg',
            (string) $validated['nilai_dipilah_per_kg'],
            'Nilai per kilogram untuk sampah yang dipilah.'
        );
        PengaturanSistem::set(
            'nama_tps',
            $validated['nama_tps'] ?? (string) PengaturanSistem::get('nama_tps', 'TPS 3R Banjarsari'),
            'Nama TPS yang tampil di laporan dan ekspor.'
        );

        return redirect()
            ->route('sips.tarif.index')
            ->with('success', 'Pengaturan tarif global berhasil diperbarui.');
    }
}
