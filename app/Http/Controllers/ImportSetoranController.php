<?php

namespace App\Http\Controllers;

use App\Helpers\RwParser;
use App\Helpers\TanggalParser;
use App\Models\ImportLog;
use App\Models\PengaturanSistem;
use App\Models\Setoran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportSetoranController extends Controller
{
    // Keywords that identify each format from the header row
    private const FORMAT_KEYWORDS = [
        'perolehan' => ['hari/tanggal', 'jumlah sampah', 'pengeluaran'],
        'rivan'     => ['kompos', 'pakan magot', 'daur ulang'],
        'detail'    => ['nama penyetor', 'status pilah'],
    ];

    public function index()
    {
        $logs = ImportLog::where('jenis', 'like', 'setoran_%')
            ->with('uploader')
            ->latest()
            ->paginate(10);

        $chatGptLink = PengaturanSistem::get('chatgpt_ocr_link', '');

        return view('sips.import.setoran.index', compact('logs', 'chatGptLink'));
    }

    public function downloadTemplate(string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['perolehan', 'rivan', 'detail', 'ocr']), 404);

        $spreadsheet = match ($format) {
            'perolehan' => $this->buildPerolehanTemplate(),
            'rivan'     => $this->buildRivanTemplate(),
            'detail'    => $this->buildDetailTemplate(),
            'ocr'       => $this->buildOcrTemplate(),
        };

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            "template_setoran_{$format}.xlsx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file'             => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:4096'],
            'tanggal_setoran'  => ['nullable', 'date'],
        ], [
            'file.required' => 'File harus dipilih.',
            'file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max'      => 'Ukuran file maksimal 4MB.',
        ]);

        try {
            $result = $this->parseFile($request->file('file'));
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal membaca file: ' . $e->getMessage()]);
        }

        $result['tanggal_setoran'] = $request->input('tanggal_setoran', today()->format('Y-m-d'));

        // For detail format: fuzzy-match each nama_penyetor against registered warga
        if ($result['format'] === 'detail') {
            $wargaAll = \App\Models\Warga::select('id', 'nama', 'rt', 'rw')->orderBy('nama')->get();
            foreach ($result['rows'] as &$row) {
                $row['warga_id']    = null;
                $row['warga_score'] = 0;
                $namaInput = $this->stripHonorifics(strtolower($row['nama_penyetor'] ?? ''));
                if ($namaInput && $namaInput !== '???') {
                    $best = 0; $bestId = null;
                    foreach ($wargaAll as $w) {
                        similar_text($namaInput, strtolower($w->nama), $pct);
                        if ($pct > $best) { $best = $pct; $bestId = $w->id; }
                    }
                    if ($best >= 70) {
                        $row['warga_id']    = $bestId;
                        $row['warga_score'] = (int) round($best);
                    }
                }
            }
            unset($row);
        } else {
            $wargaAll = collect();
        }

        session(['import_setoran' => $result]);

        $errorCount = collect($result['rows'])->filter(fn($r) => !empty($r['errors']))->count();

        return view('sips.import.setoran.preview', [
            'format'          => $result['format'],
            'rows'            => $result['rows'],
            'tanggalSetoran'  => $result['tanggal_setoran'],
            'totalRows'       => count($result['rows']),
            'errorCount'      => $errorCount,
            'validCount'      => count($result['rows']) - $errorCount,
            'wargaList'       => $wargaAll,
        ]);
    }

    public function confirm(Request $request)
    {
        $preview = session('import_setoran');

        if (!$preview) {
            return redirect()->route('sips.import.setoran.index')
                ->withErrors(['confirm' => 'Sesi import habis. Ulangi upload file.']);
        }

        $format  = $preview['format'];
        $tarif   = (float) PengaturanSistem::get('tarif_flat_per_kg', 500);
        $tanggal = $request->input('tanggal_setoran', $preview['tanggal_setoran'] ?? today()->format('Y-m-d'));

        // Merge user edits from the preview form into session rows, then re-validate
        $rows = $preview['rows'];
        if ($request->has('rows')) {
            $edited = $request->input('rows', []);
            foreach ($rows as $i => &$row) {
                $e = $edited[$i] ?? [];
                if ($format === 'detail') {
                    $wargaId = isset($e['warga_id']) && is_numeric($e['warga_id']) && (int)$e['warga_id'] > 0
                               ? (int) $e['warga_id'] : null;
                    $row['warga_id']         = $wargaId;
                    $row['nama_penyetor']    = trim($e['nama_penyetor']    ?? $row['nama_penyetor']    ?? '');
                    $row['rw']               = trim($e['rw']               ?? $row['rw']               ?? '');
                    $row['rt']               = trim($e['rt']               ?? $row['rt']               ?? '');
                    $row['berat_kg']         = is_numeric($e['berat_kg'] ?? '') ? (float) $e['berat_kg'] : ($row['berat_kg'] ?? null);
                    $row['status_pemilahan'] = in_array($e['status_pemilahan'] ?? '', ['dipilah', 'tidak_dipilah'])
                                               ? $e['status_pemilahan']
                                               : ($row['status_pemilahan'] ?? 'dipilah');
                    // If warga selected but nama empty, fill from warga record
                    if ($wargaId && empty($row['nama_penyetor'])) {
                        $row['nama_penyetor'] = \App\Models\Warga::find($wargaId)?->nama ?? '';
                    }
                    $row['errors'] = [];
                    if (empty($row['nama_penyetor'])) $row['errors'][] = 'Nama penyetor kosong';
                    if ($row['berat_kg'] === null || $row['berat_kg'] <= 0) $row['errors'][] = 'Berat harus lebih dari 0';
                } elseif ($format === 'perolehan') {
                    $row['tanggal'] = trim($e['tanggal'] ?? $row['tanggal'] ?? '');
                    $row['area_rw'] = trim($e['area_rw'] ?? $row['area_rw'] ?? '');
                    $row['kg']      = is_numeric($e['kg'] ?? '') ? (float) $e['kg'] : ($row['kg'] ?? null);
                    $row['errors'] = [];
                    if ($row['kg'] === null || $row['kg'] <= 0) $row['errors'][] = 'Berat harus lebih dari 0';
                } elseif ($format === 'rivan') {
                    $row['tanggal'] = trim($e['tanggal'] ?? $row['tanggal'] ?? '');
                    $row['kg']      = is_numeric($e['kg'] ?? '') ? (float) $e['kg'] : ($row['kg'] ?? null);
                    $row['errors'] = [];
                    if ($row['kg'] === null || $row['kg'] <= 0) $row['errors'][] = 'Berat harus lebih dari 0';
                }
            }
            unset($row);
        }

        $validRows = collect($rows)->filter(fn($r) => empty($r['errors']))->values();

        if ($validRows->isEmpty()) {
            return back()->withErrors(['confirm' => 'Tidak ada baris valid untuk diimpor.']);
        }

        $berhasil = 0;
        $gagal    = 0;

        DB::transaction(function () use ($validRows, $format, $tarif, $tanggal, &$berhasil, &$gagal) {
            if ($format === 'detail') {
                // All rows → one setoran with multiple items
                try {
                    $this->saveDetailBatch($validRows->all(), $tarif, $tanggal);
                    $berhasil = $validRows->count();
                } catch (\Exception $e) {
                    $gagal = $validRows->count();
                }
            } else {
                // Each row → one setoran
                foreach ($validRows as $row) {
                    try {
                        $this->saveAgregatRow($row, $tarif);
                        $berhasil++;
                    } catch (\Exception) {
                        $gagal++;
                    }
                }
            }
        });

        ImportLog::create([
            'filename'              => "import_setoran_{$format}_" . now()->format('Ymd_His') . '.xlsx',
            'jenis'                 => 'setoran_' . $format,
            'total_baris'           => $validRows->count(),
            'berhasil'              => $berhasil,
            'gagal'                 => $gagal,
            'status'                => $gagal === $validRows->count() ? 'gagal' : 'selesai',
            'diupload_oleh_user_id' => auth()->id(),
            'catatan'               => null,
        ]);

        session()->forget('import_setoran');

        return redirect()->route('sips.setoran.index')
            ->with('success', "Import {$format} selesai: {$berhasil} berhasil, {$gagal} gagal.");
    }

    // ── Parsing ───────────────────────────────────────────────────────────────

    private function parseFile(\Illuminate\Http\UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $readerType = match ($ext) {
            'xlsx'  => 'Xlsx',
            'xls'   => 'Xls',
            default => 'Csv',
        };

        $reader = IOFactory::createReader($readerType);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());
        $sheet       = $spreadsheet->getActiveSheet();

        $headerRow = $this->findHeaderRow($sheet);

        if ($headerRow === null) {
            throw new \RuntimeException('Header kolom tidak ditemukan dalam 15 baris pertama. Pastikan file menggunakan template yang disediakan.');
        }

        $headers = $this->rowValues($sheet, $headerRow);
        $format  = $this->detectFormat($headers);

        $rows = match ($format) {
            'perolehan' => $this->parsePerolehan($sheet, $headerRow, $headers),
            'rivan'     => $this->parseRivan($sheet, $headerRow, $headers),
            'detail'    => $this->parseDetail($sheet, $headerRow, $headers),
        };

        return compact('format', 'rows');
    }

    private function findHeaderRow(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): ?int
    {
        $allKeywords = array_merge(...array_values(self::FORMAT_KEYWORDS));
        $commonWords = ['tanggal', 'rw', 'kg', 'no', 'nama', 'berat', 'status'];

        $bestRow   = null;
        $bestScore = 0;

        for ($r = 1; $r <= min($sheet->getHighestRow(), 15); $r++) {
            $text  = strtolower(implode(' ', $this->rowValues($sheet, $r)));
            $score = 0;
            foreach ($allKeywords as $kw) { if (str_contains($text, $kw)) $score += 2; }
            foreach ($commonWords as $kw) { if (str_contains($text, $kw)) $score += 1; }

            if ($score > $bestScore) { $bestScore = $score; $bestRow = $r; }
        }

        return $bestScore >= 3 ? $bestRow : null;
    }

    private function detectFormat(array $headers): string
    {
        $text = strtolower(implode(' ', $headers));

        foreach (self::FORMAT_KEYWORDS as $format => $keywords) {
            $hits = 0;
            foreach ($keywords as $kw) { if (str_contains($text, $kw)) $hits++; }
            if ($hits >= 2) return $format;
        }

        if (str_contains($text, 'penyetor')) return 'detail';
        if (str_contains($text, 'kompos'))   return 'rivan';

        return 'perolehan';
    }

    private function parsePerolehan(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $headerRow, array $headers): array
    {
        $col = $this->mapColumns($headers, [
            'tanggal' => ['hari/tanggal', 'tanggal', 'hari'],
            'rw'      => ['rw', 'area'],
            'kg'      => ['jumlah sampah/kg', 'jumlah sampah', 'kg'],
            'jumlah'  => ['jumlah', 'total'],
        ]);

        $tarif = (float) PengaturanSistem::get('tarif_flat_per_kg', 500);
        $rows  = [];

        for ($r = $headerRow + 1; $r <= $sheet->getHighestRow(); $r++) {
            $vals = $this->rowValues($sheet, $r);
            if ($this->isBlankRow($vals)) continue;

            // Skip summary rows (JUMLAH row)
            $rowText = strtolower(implode('', $vals));
            if (preg_match('/^[\s0-9.jumlah]+$/', $rowText) && str_contains($rowText, 'jumlah')) continue;

            $tanggalRaw = $vals[$col['tanggal']] ?? null;
            $rwRaw      = $vals[$col['rw']] ?? null;
            $kgRaw      = $vals[$col['kg']] ?? null;

            $errors   = [];
            $warnings = [];

            $tanggal = TanggalParser::parse($tanggalRaw);
            if (!$tanggal) $errors[] = "Tanggal tidak valid: «{$tanggalRaw}»";

            $kg = $this->toFloat($kgRaw);
            if ($kg === null || $kg <= 0) $errors[] = "Berat tidak valid: «{$kgRaw}»";

            $rw = RwParser::normalize($rwRaw);
            if (!$rw) $errors[] = 'Kolom RW kosong.';

            // Warn if Jumlah column doesn't match kg × tarif
            $jumlah = $this->toFloat($vals[$col['jumlah']] ?? null);
            if ($jumlah !== null && $kg !== null && $kg > 0 && abs($jumlah - $kg * $tarif) > 1) {
                $warnings[] = "Jumlah (Rp {$jumlah}) ≠ {$kg} kg × {$tarif}. Nilai dihitung ulang.";
            }

            $rows[] = [
                'tanggal'  => $tanggal?->format('Y-m-d'),
                'area_rw'  => $rw,
                'kg'       => $kg,
                'errors'   => $errors,
                'warnings' => $warnings,
                'raw'      => array_slice($vals, 0, 7),
            ];
        }

        return $rows;
    }

    private function parseRivan(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $headerRow, array $headers): array
    {
        $col = $this->mapColumns($headers, [
            'tanggal' => ['tanggal'],
            'kg'      => ['jumlah sampah (kg)', 'jumlah sampah', 'kg'],
            'iuran'   => ['iuran (rp)', 'iuran'],
        ]);

        $rows = [];

        for ($r = $headerRow + 1; $r <= $sheet->getHighestRow(); $r++) {
            $vals = $this->rowValues($sheet, $r);
            if ($this->isBlankRow($vals)) continue;

            $rowText = strtolower(trim(implode('', $vals)));
            if (preg_match('/^jumlah\s*[\d.]*$/', $rowText)) continue;

            $tanggalRaw = $vals[$col['tanggal']] ?? null;
            $kgRaw      = $vals[$col['kg']] ?? null;

            $errors   = [];
            $tanggal  = TanggalParser::parse($tanggalRaw);
            if (!$tanggal) $errors[] = "Tanggal tidak valid: «{$tanggalRaw}»";

            $kg = $this->toFloat($kgRaw);
            if ($kg === null || $kg <= 0) $errors[] = "Berat tidak valid: «{$kgRaw}»";

            $rows[] = [
                'tanggal'  => $tanggal?->format('Y-m-d'),
                'area_rw'  => null,
                'kg'       => $kg,
                'errors'   => $errors,
                'warnings' => [],
                'raw'      => array_slice($vals, 0, 7),
            ];
        }

        return $rows;
    }

    private function parseDetail(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $headerRow, array $headers): array
    {
        $col = $this->mapColumns($headers, [
            'nama'  => ['nama penyetor', 'nama'],
            'rw'    => ['rw'],
            'rt'    => ['rt'],
            'berat' => ['berat (kg)', 'berat', 'kg'],
            'pilah' => ['status pilah', 'status pemilahan', 'pilah', 'status'],
            'ket'   => ['keterangan', 'catatan'],
        ]);

        $rows = [];

        for ($r = $headerRow + 1; $r <= $sheet->getHighestRow(); $r++) {
            $vals = $this->rowValues($sheet, $r);
            if ($this->isBlankRow($vals)) continue;

            $namaRaw  = $vals[$col['nama']]  ?? null;
            $rwRaw    = $vals[$col['rw']]    ?? null;
            $rtRaw    = $vals[$col['rt']]    ?? null;
            $beratRaw = $vals[$col['berat']] ?? null;
            $pilahRaw = $vals[$col['pilah']] ?? null;

            $errors = [];

            $nama = trim((string) $namaRaw);
            if (!$nama) $errors[] = 'Nama penyetor kosong.';

            $berat = $this->toFloat($beratRaw);
            if ($berat === null || $berat <= 0) $errors[] = "Berat tidak valid: «{$beratRaw}»";

            $status = $this->normalizePilah($pilahRaw);
            if (!$status) $errors[] = "Status pilah tidak valid: «{$pilahRaw}». Isi 'Dipilah' atau 'Tidak Dipilah'.";

            $rows[] = [
                'nama_penyetor'   => $nama,
                'rw'              => RwParser::normalize($rwRaw),
                'rt'              => trim((string) $rtRaw),
                'berat_kg'        => $berat,
                'status_pemilahan'=> $status,
                'errors'          => $errors,
                'warnings'        => [],
                'raw'             => array_slice($vals, 0, 7),
            ];
        }

        return $rows;
    }

    // ── DB save ───────────────────────────────────────────────────────────────

    private function saveAgregatRow(array $row, float $tarif): void
    {
        $kg = (float) $row['kg'];

        Setoran::create([
            'warga_id'               => null,
            'petugas_id'             => auth()->id() ?? 1,
            'tanggal_setoran'        => $row['tanggal'],
            'area_rw'                => $row['area_rw'] ?? null,
            'total_kg'               => $kg,
            'total_kg_dipilah'       => 0,
            'total_kg_tidak_dipilah' => $kg,
            'nilai'                  => round($kg * $tarif * -1, 2),
            'total_nilai'            => round($kg * $tarif, 2),
            'mode'                   => 'agregat',
            'sumber_input'           => 'excel',
            'status_pembayaran'      => 'belum_dibayar',
        ]);
    }

    private function saveDetailBatch(array $rows, float $tarif, string $tanggal): void
    {
        $totalKg   = 0;
        $kgDipilah = 0;
        $kgTidak   = 0;
        $itemRows  = [];
        $rwSeen    = [];

        foreach ($rows as $row) {
            $berat   = (float) $row['berat_kg'];
            $dipilah = $row['status_pemilahan'] === 'dipilah';

            $totalKg += $berat;
            $dipilah ? ($kgDipilah += $berat) : ($kgTidak += $berat);

            if ($row['rw']) {
                $rwSeen[] = $row['rw'];
            }

            $itemRows[] = [
                'warga_id'              => $row['warga_id'] ?? null,
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

        $areaRw  = implode(', ', array_unique(array_filter($rwSeen))) ?: null;

        $setoran = Setoran::create([
            'warga_id'               => null,
            'petugas_id'             => auth()->id() ?? 1,
            'tanggal_setoran'        => $tanggal,
            'area_rw'                => $areaRw,
            'total_kg'               => $totalKg,
            'total_kg_dipilah'       => $kgDipilah,
            'total_kg_tidak_dipilah' => $kgTidak,
            'nilai'                  => round(($kgDipilah - $kgTidak) * $tarif, 2),
            'total_nilai'            => round($totalKg * $tarif, 2),
            'mode'                   => 'detail',
            'sumber_input'           => 'excel',
            'status_pembayaran'      => 'belum_dibayar',
        ]);

        $setoran->items()->createMany($itemRows);
    }

    // ── Template builders ─────────────────────────────────────────────────────

    private function buildPerolehanTemplate(): Spreadsheet
    {
        $sp    = new Spreadsheet();
        $sheet = $sp->getActiveSheet()->setTitle('Perolehan Sampah');

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'Perolehan Dana Dari Pengambilan Sampah Warga');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headers = ['No', 'Hari/Tanggal', 'RW', 'Jumlah Sampah/kg', 'Rp', 'Jumlah', 'Pengeluaran'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . '3', $h);
        }
        $sheet->getStyle('A3:G3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
        ]);

        $samples = [
            [1, 'Senin, 9 Maret 2026',   '06 & 01', 475,   500, '=D4*E4', ''],
            [2, 'Rabu, 11 Maret 2026',   '01 & 05', 381,   500, '=D5*E5', ''],
            [3, 'Kamis, 12 Maret 2026',  '06 & 01', 249,   500, '=D6*E6', ''],
        ];
        foreach ($samples as $ri => $row) {
            foreach ($row as $ci => $val) {
                $sheet->setCellValue(chr(65 + $ci) . (4 + $ri), $val);
            }
        }

        // Yellow for Pengeluaran (Col G)
        $sheet->getStyle('G4:G100')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF2CC']],
        ]);

        $sheet->setCellValue('A8', 'Keterangan:');
        $sheet->setCellValue('A9', 'Kolom Rp = tarif flat Rp 500/kg. Kolom Jumlah = kg × Rp (otomatis).');
        $sheet->setCellValue('A10', 'Kolom Pengeluaran (kuning) dikosongkan untuk diisi pihak desa.');
        $sheet->setCellValue('A11', 'RW boleh gabungan beberapa RW (contoh: 06 & 01, atau 05, 01, 06).');
        $sheet->getStyle('A8')->getFont()->setBold(true);
        $sheet->getStyle('A9:A11')->getFont()->setItalic(true);

        $sheet->getColumnDimension('B')->setWidth(26);
        $sheet->getColumnDimension('D')->setWidth(18);

        return $sp;
    }

    private function buildRivanTemplate(): Spreadsheet
    {
        $sp    = new Spreadsheet();
        $sheet = $sp->getActiveSheet()->setTitle('Pengelolaan Sampah');

        $namaTps = PengaturanSistem::get('nama_tps', 'TPS 3R Banjarsari');

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'DATA PENGELOLAAN SAMPAH ' . strtoupper($namaTps));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'Laporan Periode 2 Mingguan (Ekspor otomatis dari SIPS)');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headers = ['Tanggal', 'Kompos', 'Pakan Magot', 'Residu', 'Daur Ulang', 'Jumlah Sampah (kg)', 'Iuran (Rp)'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . '4', $h);
        }
        $sheet->getStyle('A4:G4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
        ]);

        // Yellow for Pak Rivan's columns (B-E)
        $sheet->getStyle('B5:E100')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF2CC']],
        ]);

        $samples = [
            ['2026-05-13', '', '', '', '', 124, 62000],
            ['2026-05-15', '', '', '', '', 90,  45000],
            ['2026-05-18', '', '', '', '', 156, 78000],
        ];
        foreach ($samples as $ri => $row) {
            foreach ($row as $ci => $val) {
                $sheet->setCellValue(chr(65 + $ci) . (5 + $ri), $val);
            }
        }

        $sheet->setCellValue('A9', 'Keterangan:');
        $sheet->setCellValue('A10', 'Kolom kuning (Kompos/Magot/Residu/Daur Ulang) dikosongkan untuk diisi Pak Rivan.');
        $sheet->setCellValue('A11', 'Kolom Tanggal, Jumlah Sampah, Iuran diisi otomatis oleh sistem SIPS.');
        $sheet->getStyle('A9')->getFont()->setBold(true);
        $sheet->getStyle('A10:A11')->getFont()->setItalic(true);

        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(20);

        return $sp;
    }

    private function buildDetailTemplate(): Spreadsheet
    {
        $sp    = new Spreadsheet();
        $sheet = $sp->getActiveSheet()->setTitle('Detail Penyetor');

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'Rekap Detail Setoran per Penyetor');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headers = ['No', 'Nama Penyetor', 'RW', 'RT', 'Berat (kg)', 'Status Pilah', 'Keterangan'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . '3', $h);
        }
        $sheet->getStyle('A3:G3')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF70AD47']],
        ]);

        $samples = [
            [1, 'Budi Santoso',     '01', '02', 12.5, 'Dipilah',       ''],
            [2, 'Es Teler Pak Eko', '01', '',   8.0,  'Tidak Dipilah', 'Sampah campur'],
            [3, 'Apotek Sehat',     '02', '01', 5.5,  'Dipilah',       ''],
        ];
        foreach ($samples as $ri => $row) {
            foreach ($row as $ci => $val) {
                $sheet->setCellValue(chr(65 + $ci) . (4 + $ri), $val);
            }
        }

        $sheet->setCellValue('A8', 'Keterangan:');
        $sheet->setCellValue('A9', 'Status Pilah: isi "Dipilah" atau "Tidak Dipilah" (hurus sesuai).');
        $sheet->setCellValue('A10', 'Nama Penyetor: boleh nama orang, toko, atau usaha.');
        $sheet->setCellValue('A11', 'Semua baris akan digabung menjadi SATU setoran dengan tanggal yang dipilih saat upload.');
        $sheet->getStyle('A8')->getFont()->setBold(true);
        $sheet->getStyle('A9:A11')->getFont()->setItalic(true);

        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(16);

        return $sp;
    }

    private function buildOcrTemplate(): Spreadsheet
    {
        $sp    = new Spreadsheet();
        $sheet = $sp->getActiveSheet()->setTitle('Template OCR');

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'Template Import Setoran dari AI / OCR');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Dihasilkan dari foto catatan kertas menggunakan AI (Gemini/ChatGPT)');
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF666666']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headers = ['tanggal', 'nama_penyetor', 'rt', 'rw', 'status_pemilahan', 'jenis_sampah', 'berat_kg', 'catatan'];
        foreach ($headers as $i => $h) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}4", $h);
        }
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF7030A0']],
        ]);

        $samples = [
            ['2026-07-08', 'Budi Santoso', '02', '06', 'dipilah', 'kardus', 3.5, ''],
            ['2026-07-08', 'Budi Santoso', '02', '06', 'dipilah', 'botol plastik', 1.2, ''],
            ['2026-07-08', 'Ibu Siti Rahayu', '01', '06', 'tidak_dipilah', '', 8.0, 'sampah campur'],
            ['2026-07-08', '???', '', '05', 'dipilah', 'kertas koran', 2.0, 'nama tidak terbaca'],
        ];
        foreach ($samples as $ri => $row) {
            foreach ($row as $ci => $val) {
                $col = Coordinate::stringFromColumnIndex($ci + 1);
                $sheet->setCellValue("{$col}" . (5 + $ri), $val);
            }
        }

        $sheet->setCellValue('A10', 'Keterangan:');
        $sheet->setCellValue('A11', 'status_pemilahan: isi "dipilah" atau "tidak_dipilah"');
        $sheet->setCellValue('A12', 'jenis_sampah: boleh kosong jika tidak dipilah');
        $sheet->setCellValue('A13', 'Jika nama tidak terbaca, tulis "???" dan sistem akan meminta klarifikasi');
        $sheet->setCellValue('A14', 'Satu baris = satu jenis sampah dari satu orang (pisahkan jika lebih dari satu jenis)');
        $sheet->getStyle('A10')->getFont()->setBold(true);
        $sheet->getStyle('A11:A14')->getFont()->setItalic(true)->setSize(10);

        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(18);

        return $sp;
    }

    // ── Utilities ─────────────────────────────────────────────────────────────

    private function stripHonorifics(string $name): string
    {
        return trim(preg_replace('/^(bu|pak|bapak|ibu|hj|h|dr|drs)\s+/i', '', $name));
    }

    private function rowValues(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, int $row): array
    {
        $maxCol = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        $values = [];
        for ($c = 1; $c <= $maxCol; $c++) {
            $values[] = $sheet->getCell([$c, $row])->getFormattedValue();
        }
        return $values;
    }

    private function mapColumns(array $headers, array $fieldKeywords): array
    {
        $map = [];
        foreach ($fieldKeywords as $field => $keywords) {
            foreach ($headers as $idx => $header) {
                $h = strtolower(trim((string) $header));
                foreach ($keywords as $kw) {
                    if (str_contains($h, $kw)) { $map[$field] = $idx; break 2; }
                }
            }
            $map[$field] ??= 99; // sentinel — column not found
        }
        return $map;
    }

    private function isBlankRow(array $values): bool
    {
        return empty(array_filter($values, fn($v) => trim((string) $v) !== ''));
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') return null;
        $cleaned = preg_replace('/[^0-9.,\-]/', '', (string) $value);
        $cleaned = str_replace(',', '.', $cleaned);
        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    private function normalizePilah(mixed $value): ?string
    {
        $v = strtolower(trim((string) $value));
        if (str_contains($v, 'tidak') || str_contains($v, 'campur')) return 'tidak_dipilah';
        if (str_contains($v, 'dipilah') || str_contains($v, 'pilah')) return 'dipilah';
        return null;
    }
}
