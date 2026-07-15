<?php

namespace App\Http\Controllers;

use App\Models\SinonimSampah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SinonimSampahController extends Controller
{
    public function index(Request $request)
    {
        $query = SinonimSampah::with(['dibuatOleh'])
            ->orderBy('kata_kunci');

        if ($search = $request->get('q')) {
            $query->where('kata_kunci', 'like', "%{$search}%");
        }

        if ($tipe = $request->get('tipe')) {
            $query->where('tipe_sampah', $tipe);
        }

        $sinonims = $query->paginate(30)->withQueryString();

        return view('sips.sinonim.index', compact('sinonims', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kata_kunci'  => ['required', 'string', 'max:100', 'unique:sinonim_sampah,kata_kunci'],
            'tipe_sampah' => ['required', 'in:dipilah,tidak_dipilah'],
        ], [
            'kata_kunci.unique'    => 'Kata kunci ini sudah terdaftar di kamus sinonim.',
            'tipe_sampah.required' => 'Pilih status pemilahan untuk kata kunci ini.',
        ]);

        SinonimSampah::create([
            'kata_kunci'          => mb_strtolower(trim($validated['kata_kunci'])),
            'tipe_sampah'         => $validated['tipe_sampah'],
            'gunakan_flat'        => true,
            'dibuat_oleh_user_id' => auth()->id(),
        ]);

        return redirect()->route('sips.sinonim.index')
            ->with('success', "Sinonim «{$validated['kata_kunci']}» berhasil ditambahkan.");
    }

    public function update(Request $request, SinonimSampah $sinonim): RedirectResponse
    {
        $validated = $request->validate([
            'kata_kunci'  => ['required', 'string', 'max:100', "unique:sinonim_sampah,kata_kunci,{$sinonim->id}"],
            'tipe_sampah' => ['required', 'in:dipilah,tidak_dipilah'],
        ], [
            'kata_kunci.unique'    => 'Kata kunci ini sudah dipakai oleh sinonim lain.',
            'tipe_sampah.required' => 'Pilih status pemilahan untuk kata kunci ini.',
        ]);

        $sinonim->update([
            'kata_kunci'   => mb_strtolower(trim($validated['kata_kunci'])),
            'tipe_sampah'  => $validated['tipe_sampah'],
            'gunakan_flat' => true,
        ]);

        return redirect()->route('sips.sinonim.index')
            ->with('success', "Sinonim «{$sinonim->kata_kunci}» berhasil diperbarui.");
    }

    public function destroy(SinonimSampah $sinonim): RedirectResponse
    {
        $kunci = $sinonim->kata_kunci;
        $sinonim->delete();

        return redirect()->route('sips.sinonim.index')
            ->with('success', "Sinonim «{$kunci}» dihapus.");
    }
}
