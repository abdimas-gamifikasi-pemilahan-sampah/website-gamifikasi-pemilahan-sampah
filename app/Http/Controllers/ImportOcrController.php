<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Models\ItemSetoran;
use App\Models\PengaturanSistem;
use App\Models\Setoran;
use App\Models\SinonimSampah;
use App\Models\TarifItem;
use App\Models\Warga;
use App\Services\OcrExcelParser;
use App\Services\WargaMatcher;
use App\Services\WasteMatcher;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportOcrController extends Controller
{
    // ── Index: upload form + prompt template ──────────────────────────────────

    public function index()
    {
        $chatGptLink = PengaturanSistem::get('chatgpt_ocr_link', '');

        return view('sips.import.ocr.index', compact('chatGptLink'));
    }

    public function saveAiLinks(Request $request): RedirectResponse
    {
        $request->validate([
            'chatgpt_ocr_link' => ['nullable', 'url', 'max:500'],
        ]);

        PengaturanSistem::set('chatgpt_ocr_link', $request->input('chatgpt_ocr_link', ''), 'Link Custom GPT untuk import OCR sampah');

        return redirect()->route('sips.import.ocr.index')
            ->with('success', 'Link AI berhasil disimpan.');
    }

    // ── Panduan ───────────────────────────────────────────────────────────────

    public function panduan()
    {
        return view('sips.import.ocr.panduan');
    }

    // ── Flat-rate review: list OCR items still without tarif assignment ────────

    public function flatRateReview()
    {
        $items = ItemSetoran::with(['setoran'])
            ->whereHas('setoran', fn ($q) => $q->where('sumber_input', 'ocr'))
            ->whereNull('tarif_item_id')
            ->whereNotNull('catatan_item')
            ->where('catatan_item', 'like', '%flat rate%')
            ->where('status_pemilahan', 'dipilah')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by extracted raw jenis
        $grouped = $items->groupBy(function ($item) {
            if (preg_match('/jenis: (.+?) \[flat rate/', $item->catatan_item, $m)) {
                return mb_strtolower(trim($m[1]));
            }
            return '__tidak_disebutkan__';
        })->map(function ($group, $jenisRaw) {
            return [
                'jenis_raw'  => $jenisRaw === '__tidak_disebutkan__' ? null : $jenisRaw,
                'item_ids'   => $group->pluck('id')->all(),
                'total_kg'   => round($group->sum('berat_kg'), 2),
                'jumlah'     => $group->count(),
                'tanggal'    => $group->first()->setoran?->tanggal_setoran?->format('Y-m-d'),
            ];
        })->values();

        $tarifItems = TarifItem::where('status', 'aktif')->orderBy('nama_item')->get(['id', 'nama_item', 'tipe_sampah']);

        return view('sips.import.ocr.flat_rate_review', compact('grouped', 'tarifItems'));
    }

    // ── Assign tarif to flat-rate group ──────────────────────────────────────

    public function assignTarifItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_ids'          => ['required', 'array'],
            'item_ids.*'        => ['required', 'integer', 'exists:item_setoran,id'],
            'tarif_item_id'     => ['required', 'integer', 'exists:tarif_items,id'],
            'jenis_raw'         => ['nullable', 'string', 'max:100'],
            'simpan_sinonim'    => ['nullable'],
        ]);

        $tarifItem = TarifItem::findOrFail($validated['tarif_item_id']);
        $riwayat   = $tarifItem->tarifAktif();

        if (!$riwayat) {
            return back()->withErrors(['tarif_item_id' => 'Tarif item ini tidak memiliki harga aktif.']);
        }

        $hargaPerKg = (float) $riwayat->harga_per_kg;

        DB::transaction(function () use ($validated, $tarifItem, $riwayat, $hargaPerKg) {
            foreach ($validated['item_ids'] as $itemId) {
                $item        = ItemSetoran::findOrFail($itemId);
                $oldSubtotal = (float) $item->subtotal;
                $newSubtotal = round((float) $item->berat_kg * $hargaPerKg, 2);

                $item->update([
                    'tarif_item_id'         => $tarifItem->id,
                    'riwayat_tarif_id'      => $riwayat->id,
                    'tipe_sampah'           => $tarifItem->tipe_sampah,
                    'harga_per_kg_saat_itu' => $hargaPerKg,
                    'subtotal'              => $newSubtotal,
                    'catatan_item'          => null,
                ]);

                // Adjust parent setoran totals
                $setoran              = $item->setoran;
                $setoran->nilai       = round((float) $setoran->nilai + ($newSubtotal - $oldSubtotal), 2);
                $setoran->total_nilai = round(abs((float) $setoran->nilai), 2);
                $setoran->save();
            }
        });

        if (!empty($validated['simpan_sinonim']) && !empty($validated['jenis_raw'])) {
            $kunci = mb_strtolower(trim($validated['jenis_raw']));
            if ($kunci) {
                SinonimSampah::firstOrCreate(
                    ['kata_kunci' => $kunci],
                    [
                        'tarif_item_id'       => $tarifItem->id,
                        'tipe_sampah'         => $tarifItem->tipe_sampah,
                        'gunakan_flat'        => false,
                        'dibuat_oleh_user_id' => auth()->id(),
                    ]
                );
            }
        }

        $count = count($validated['item_ids']);
        return redirect()->route('sips.import.ocr.flat-rate-review')
            ->with('success', "{$count} item berhasil ditetapkan ke «{$tarifItem->nama_item}».");
    }

    // ── Preview: parse Excel → run matching → render review table ─────────────

    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'Format file harus .xlsx atau .xls.',
            'file.max'      => 'Ukuran file maksimal 10 MB.',
        ]);

        try {
            $rows = (new OcrExcelParser())->parse($request->file('file'));
        } catch (\RuntimeException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        $wargaMatcher = new WargaMatcher();
        $wasteMatcher = new WasteMatcher();
        $tarifDipilah = (float) PengaturanSistem::get('nilai_dipilah_per_kg', 500);
        $tarifTidak   = (float) PengaturanSistem::get('tarif_flat_per_kg', 500);

        $previewRows = [];

        foreach ($rows as $row) {
            $isDipilah = $row['status_pemilahan'] === 'dipilah';
            $isBlocked = $row['nama_penyetor'] === '' || $row['nama_penyetor'] === '???';

            $wargaMatch = $wargaMatcher->match($row['nama_penyetor'], $row['rw'] ?: null);

            if ($isDipilah) {
                $wasteMatch = $wasteMatcher->match($row['jenis_sampah'], $row['tanggal']);
            } else {
                $wasteMatch = [
                    'tarif_item'    => null,
                    'riwayat_tarif' => null,
                    'tipe_sampah'   => null,
                    'harga_per_kg'  => $tarifTidak,
                    'gunakan_flat'  => true,
                    'confidence'    => 100,
                    'method'        => 'tidak_dipilah',
                    'suggestions'   => [],
                ];
            }

            $harga = $isDipilah
                ? ($wasteMatch['harga_per_kg'] ?? $tarifDipilah)
                : $tarifTidak;

            $nilaiEstimasi = (!$isBlocked && $row['berat_kg'] > 0)
                ? round($row['berat_kg'] * $harga * ($isDipilah ? 1 : -1), 2)
                : null;

            $previewRows[] = [
                'tanggal'          => $row['tanggal'],
                'nama_raw'         => $row['nama_penyetor'],
                'rt_raw'           => $row['rt'],
                'rw_raw'           => $row['rw'],
                'status_pemilahan' => $row['status_pemilahan'],
                'jenis_raw'        => $row['jenis_sampah'],
                'berat_kg'         => $row['berat_kg'],
                'catatan'          => $row['catatan'],
                '_row_num'         => $row['_row_num'],
                'warga_match'      => $wargaMatch,
                'waste_match'      => $wasteMatch,
                'nilai_estimasi'   => $nilaiEstimasi,
                'is_blocked'       => $isBlocked,
            ];
        }

        $summary = $this->buildSummary($previewRows);

        // Keep in session so confirm can fall back to original data if needed
        session(['ocr_preview' => $previewRows]);

        $allWarga      = Warga::where('status_keanggotaan', 'aktif')->orderBy('nama')->get(['id', 'nama', 'rt', 'rw', 'dusun']);
        $allTarifItems = TarifItem::where('status', 'aktif')->orderBy('nama_item')->get(['id', 'nama_item', 'tipe_sampah']);

        // Harga per tarif item (for JS recalculation in preview)
        $hargaMap = [];
        foreach ($allTarifItems as $t) {
            $riwayat = $t->tarifAktif();
            $hargaMap[$t->id] = $riwayat ? (float) $riwayat->harga_per_kg : null;
        }

        $defaultTanggal = collect($previewRows)->pluck('tanggal')->filter()->first() ?? date('Y-m-d');

        return view('sips.import.ocr.preview', compact(
            'previewRows', 'summary', 'allWarga', 'allTarifItems',
            'tarifDipilah', 'tarifTidak', 'hargaMap', 'defaultTanggal'
        ));
    }

    // ── Confirm: validate overrides → create Setoran records ──────────────────

    public function confirm(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal'                      => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'rows'                         => ['required', 'array', 'min:1'],
            'rows.*.skip'                  => ['nullable', 'boolean'],
            'rows.*.warga_id'              => ['nullable', 'integer'],
            'rows.*.nama_penyetor'         => ['nullable', 'string', 'max:255'],
            'rows.*.tarif_item_id'         => ['nullable', 'integer'],
            'rows.*.status_pemilahan'      => ['nullable', 'in:dipilah,tidak_dipilah'],
            'rows.*.berat_kg'              => ['nullable', 'numeric', 'min:0'],
            'rows.*.rw_raw'                => ['nullable', 'string', 'max:10'],
            'rows.*.rt_raw'                => ['nullable', 'string', 'max:10'],
            'rows.*.jenis_raw'             => ['nullable', 'string', 'max:100'],
            'rows.*.simpan_sinonim_jenis'  => ['nullable'],
        ]);

        $tanggal    = $validated['tanggal'];
        $allRows    = $validated['rows'];

        // Filter skipped rows
        $activeRows = array_filter($allRows, fn ($r) => empty($r['skip']));

        if (empty($activeRows)) {
            return back()->withErrors(['rows' => 'Pilih minimal satu baris untuk disimpan.']);
        }

        // Validate non-skipped rows
        foreach ($activeRows as $i => $row) {
            if (($row['berat_kg'] ?? 0) <= 0) {
                return back()
                    ->withErrors(["rows.{$i}.berat_kg" => 'Berat harus lebih dari 0.'])
                    ->withInput();
            }
            $wargaId = (int) ($row['warga_id'] ?? 0);
            if ($wargaId === 0 && empty(trim($row['nama_penyetor'] ?? ''))) {
                return back()
                    ->withErrors(["rows.{$i}.nama_penyetor" => 'Nama penyetor wajib diisi untuk penyetor baru.'])
                    ->withInput();
            }
        }

        $tarifDipilah = (float) PengaturanSistem::get('nilai_dipilah_per_kg', 500);
        $tarifTidak   = (float) PengaturanSistem::get('tarif_flat_per_kg', 500);

        $grouped = $this->groupByPenyetor(array_values($activeRows));

        $setoranIds = DB::transaction(function () use ($grouped, $tanggal, $tarifDipilah, $tarifTidak) {
            $ids = [];

            foreach ($grouped as $group) {
                $totalKg    = 0;
                $kgDipilah  = 0;
                $kgTidak    = 0;
                $totalNilai = 0;
                $itemRows   = [];
                $rwSeen     = [];

                foreach ($group['items'] as $row) {
                    $berat   = (float) ($row['berat_kg'] ?? 0);
                    $dipilah = ($row['status_pemilahan'] ?? 'dipilah') === 'dipilah';

                    $tarifItemId    = null;
                    $riwayatTarifId = null;
                    $tipeSampah     = null;
                    $hargaPerKg     = $dipilah ? $tarifDipilah : $tarifTidak;
                    $catatanItem    = null;

                    if ($dipilah && !empty($row['tarif_item_id'])) {
                        $tarifItem = TarifItem::find((int) $row['tarif_item_id']);
                        $riwayat   = $tarifItem?->tarifAktif($tanggal);
                        if ($riwayat) {
                            $hargaPerKg     = (float) $riwayat->harga_per_kg;
                            $tarifItemId    = $tarifItem->id;
                            $riwayatTarifId = $riwayat->id;
                            $tipeSampah     = $tarifItem->tipe_sampah;
                        }
                    } elseif ($dipilah) {
                        $jenisRaw    = trim($row['jenis_raw'] ?? '');
                        $catatanItem = $jenisRaw
                            ? "jenis: {$jenisRaw} [flat rate — belum terdaftar]"
                            : 'flat rate — jenis tidak disebutkan';
                    }

                    $subtotal    = round($berat * $hargaPerKg * ($dipilah ? 1 : -1), 2);
                    $totalKg    += $berat;
                    $totalNilai += $subtotal;
                    $dipilah ? ($kgDipilah += $berat) : ($kgTidak += $berat);

                    $rwVal = trim($row['rw_raw'] ?? '');
                    if ($rwVal !== '') {
                        $rwSeen[] = str_pad($rwVal, 2, '0', STR_PAD_LEFT);
                    }

                    $itemRows[] = [
                        'nama_penyetor'         => $group['nama_penyetor'],
                        'rw'                    => $rwVal ?: null,
                        'rt'                    => trim($row['rt_raw'] ?? '') ?: null,
                        'tarif_item_id'         => $tarifItemId,
                        'riwayat_tarif_id'      => $riwayatTarifId,
                        'tipe_sampah'           => $tipeSampah,
                        'status_pemilahan'      => $row['status_pemilahan'] ?? 'dipilah',
                        'berat_kg'              => $berat,
                        'harga_per_kg_saat_itu' => $hargaPerKg,
                        'subtotal'              => $subtotal,
                        'catatan_item'          => $catatanItem,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                }

                $areaRw = !empty($rwSeen)
                    ? implode(', ', array_unique($rwSeen))
                    : null;

                $setoran = Setoran::create([
                    'warga_id'               => $group['warga_id'],
                    'petugas_id'             => auth()->id(),
                    'tanggal_setoran'        => Carbon::parse($tanggal)
                                                    ->setTime(now()->hour, now()->minute, now()->second),
                    'area_rw'                => $areaRw,
                    'total_kg'               => round($totalKg, 2),
                    'total_kg_dipilah'       => round($kgDipilah, 2),
                    'total_kg_tidak_dipilah' => round($kgTidak, 2),
                    'nilai'                  => round($totalNilai, 2),
                    'total_nilai'            => round(abs($totalNilai), 2),
                    'mode'                   => 'detail',
                    'sumber_input'           => 'ocr',
                    'status_pembayaran'      => 'belum_dibayar',
                ]);

                $setoran->items()->createMany($itemRows);
                $ids[] = $setoran->id;
            }

            return $ids;
        });

        // Save sinonim entries where requested
        $this->saveSinonims(array_values($activeRows));

        // Log the import
        $wargaMatched = count(array_filter(array_values($activeRows), fn ($r) => (int) ($r['warga_id'] ?? 0) > 0));

        ImportLog::create([
            'filename'              => 'ocr_' . now()->format('Ymd_His') . '.xlsx',
            'jenis'                 => 'setoran_ocr',
            'total_baris'           => count($activeRows),
            'jumlah_warga_matched'  => $wargaMatched,
            'jumlah_warga_baru'     => count($grouped) - $wargaMatched,
            'jumlah_item_flat_rate' => count(array_filter(
                array_values($activeRows),
                fn ($r) => empty($r['tarif_item_id']) && ($r['status_pemilahan'] ?? '') === 'dipilah'
            )),
            'berhasil'              => count($setoranIds),
            'gagal'                 => 0,
            'status'                => 'selesai',
            'diupload_oleh_user_id' => auth()->id(),
            'catatan'               => count($setoranIds) . ' setoran dari import OCR',
        ]);

        session()->forget('ocr_preview');

        $msg = count($setoranIds) === 1
            ? 'Setoran berhasil dicatat dari import OCR.'
            : count($setoranIds) . ' setoran berhasil dicatat dari import OCR.';

        return redirect()->route('sips.setoran.index')->with('success', $msg);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function buildSummary(array $rows): array
    {
        return [
            'total'        => count($rows),
            'auto_matched' => count(array_filter($rows, fn ($r) => $r['warga_match']['auto_matched'])),
            'needs_review' => count(array_filter($rows, fn ($r) =>
                !$r['warga_match']['auto_matched'] &&
                !$r['is_blocked'] &&
                $r['warga_match']['confidence'] >= WargaMatcher::THRESHOLD_SUGGEST
            )),
            'unregistered' => count(array_filter($rows, fn ($r) =>
                !$r['is_blocked'] &&
                $r['warga_match']['confidence'] < WargaMatcher::THRESHOLD_SUGGEST
            )),
            'blocked'      => count(array_filter($rows, fn ($r) => $r['is_blocked'])),
            'flat_rate'    => count(array_filter($rows, fn ($r) =>
                $r['waste_match']['gunakan_flat'] && $r['status_pemilahan'] === 'dipilah'
            )),
        ];
    }

    private function groupByPenyetor(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $wargaId      = (int) ($row['warga_id'] ?? 0);
            $namaPenyetor = trim($row['nama_penyetor'] ?? '');

            $key = $wargaId > 0
                ? "warga:{$wargaId}"
                : 'nama:' . mb_strtolower($namaPenyetor);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'warga_id'      => $wargaId > 0 ? $wargaId : null,
                    'nama_penyetor' => $wargaId > 0
                        ? (Warga::find($wargaId)?->nama ?? $namaPenyetor)
                        : $namaPenyetor,
                    'rw'            => trim($row['rw_raw'] ?? ''),
                    'rt'            => trim($row['rt_raw'] ?? ''),
                    'items'         => [],
                ];
            }

            $grouped[$key]['items'][] = $row;
        }

        return array_values($grouped);
    }

    private function saveSinonims(array $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['simpan_sinonim_jenis'])) {
                continue;
            }

            $kunci = mb_strtolower(trim($row['jenis_raw'] ?? ''));
            if ($kunci === '') {
                continue;
            }

            $tarifItemId = !empty($row['tarif_item_id']) ? (int) $row['tarif_item_id'] : null;
            $tipeSampah  = $tarifItemId
                ? TarifItem::find($tarifItemId)?->tipe_sampah
                : null;

            SinonimSampah::firstOrCreate(
                ['kata_kunci' => $kunci],
                [
                    'tarif_item_id'       => $tarifItemId,
                    'tipe_sampah'         => $tipeSampah,
                    'gunakan_flat'        => $tarifItemId === null,
                    'dibuat_oleh_user_id' => auth()->id(),
                ]
            );
        }
    }
}
