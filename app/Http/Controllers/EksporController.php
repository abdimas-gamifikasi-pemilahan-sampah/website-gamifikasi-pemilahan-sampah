<?php

namespace App\Http\Controllers;

use App\Helpers\RwParser;
use App\Models\PengaturanSistem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class EksporController extends Controller
{
    public function index(Request $request)
    {
        $dari   = $request->input('dari',   now()->subDays(13)->format('Y-m-d'));
        $sampai = $request->input('sampai', now()->format('Y-m-d'));

        $preview = $this->previewStats($dari, $sampai);

        return view('sips.ekspor.index', compact('dari', 'sampai', 'preview'));
    }

    public function download(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'format' => ['required', 'in:perolehan,rivan'],
            'dari'   => ['required', 'date'],
            'sampai' => ['required', 'date', 'after_or_equal:dari'],
        ], [
            'format.required'         => 'Format laporan harus dipilih.',
            'format.in'               => 'Format tidak valid.',
            'dari.required'           => 'Tanggal awal harus diisi.',
            'sampai.required'         => 'Tanggal akhir harus diisi.',
            'sampai.after_or_equal'   => 'Tanggal akhir harus sama atau setelah tanggal awal.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('sips.ekspor.index', $request->only('dari', 'sampai'))
                ->withErrors($validator);
        }

        $dari   = $request->input('dari');
        $sampai = $request->input('sampai');
        $format = $request->input('format');

        $rows = $this->querySetoran($dari, $sampai);

        $spreadsheet = $format === 'rivan'
            ? $this->buildRivanExcel($rows, $dari, $sampai)
            : $this->buildPerolehanExcel($rows, $dari, $sampai);

        $writer   = new Xlsx($spreadsheet);
        $filename = "laporan_{$format}_"
            . str_replace('-', '', $dari) . '_'
            . str_replace('-', '', $sampai) . '.xlsx';

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    // ── Data query ────────────────────────────────────────────────────────────

    private function querySetoran(string $dari, string $sampai)
    {
        return DB::table('setoran')
            ->leftJoin('warga', 'warga.id', '=', 'setoran.warga_id')
            ->leftJoinSub(
                DB::table('item_setoran')
                    ->select('setoran_id', DB::raw('SUM(berat_kg) as sum_kg'))
                    ->groupBy('setoran_id'),
                'items_agg',
                fn($join) => $join->on('items_agg.setoran_id', '=', 'setoran.id')
            )
            ->whereDate('setoran.tanggal_setoran', '>=', $dari)
            ->whereDate('setoran.tanggal_setoran', '<=', $sampai)
            ->orderBy('setoran.tanggal_setoran')
            ->select([
                'setoran.tanggal_setoran',
                'setoran.area_rw',
                'warga.rw as warga_rw',
                DB::raw('COALESCE(setoran.total_kg, items_agg.sum_kg, 0) as total_kg'),
                DB::raw('COALESCE(setoran.total_nilai, 0) as total_nilai'),
            ])
            ->get()
            ->map(function ($row) {
                $row->rw_display = $this->resolveRw($row->area_rw, $row->warga_rw);
                return $row;
            });
    }

    private function previewStats(string $dari, string $sampai): array
    {
        $row = DB::table('setoran')
            ->leftJoinSub(
                DB::table('item_setoran')
                    ->select('setoran_id', DB::raw('SUM(berat_kg) as sum_kg'))
                    ->groupBy('setoran_id'),
                'items_agg',
                fn($join) => $join->on('items_agg.setoran_id', '=', 'setoran.id')
            )
            ->whereDate('setoran.tanggal_setoran', '>=', $dari)
            ->whereDate('setoran.tanggal_setoran', '<=', $sampai)
            ->selectRaw('COUNT(setoran.id) as jumlah')
            ->selectRaw('COALESCE(SUM(COALESCE(setoran.total_kg, items_agg.sum_kg, 0)), 0) as total_kg')
            ->selectRaw('COALESCE(SUM(setoran.total_nilai), 0) as total_nilai')
            ->first();

        return [
            'jumlah'     => (int) ($row->jumlah ?? 0),
            'total_kg'   => (float) ($row->total_kg ?? 0),
            'total_nilai' => (float) ($row->total_nilai ?? 0),
        ];
    }

    private function resolveRw(?string $areaRw, mixed $wargaRw): string
    {
        if ($areaRw && trim($areaRw) !== '') {
            return RwParser::normalize($areaRw) ?: $areaRw;
        }
        if ($wargaRw !== null) {
            return 'RW ' . str_pad((string) $wargaRw, 2, '0', STR_PAD_LEFT);
        }
        return '-';
    }

    // ── Perolehan format ──────────────────────────────────────────────────────

    private function buildPerolehanExcel($rows, string $dari, string $sampai): Spreadsheet
    {
        $sp    = new Spreadsheet();
        $sheet = $sp->getActiveSheet()->setTitle('Perolehan Sampah');

        $namaTps = PengaturanSistem::get('nama_tps', 'TPS 3R Banjarsari');
        $tarif   = (int) PengaturanSistem::get('tarif_flat_per_kg', 500);
        $periode = Carbon::parse($dari)->translatedFormat('d F Y')
            . ' s/d '
            . Carbon::parse($sampai)->translatedFormat('d F Y');

        // Row 1 – title
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'PEROLEHAN DANA DARI PENGAMBILAN SAMPAH WARGA');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 2 – TPS + periode
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', strtoupper($namaTps) . ' — Periode: ' . $periode);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 3 – blank gap, Row 4 – headers
        $headers = ['No', 'Hari/Tanggal', 'RW', 'Jumlah Sampah (kg)', 'Rp', 'Jumlah', 'Pengeluaran'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . '4', $h);
        }
        $sheet->getStyle('A4:G4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
        ]);

        // Data rows starting at row 5
        $dataStart = 5;
        $no        = 1;
        foreach ($rows as $row) {
            $r       = $dataStart + $no - 1;
            $tanggal = Carbon::parse($row->tanggal_setoran)->translatedFormat('l, j F Y');
            $kg      = (float) $row->total_kg;
            $jumlah  = (float) $row->total_nilai;

            $sheet->setCellValue("A{$r}", $no);
            $sheet->setCellValue("B{$r}", $tanggal);
            $sheet->setCellValue("C{$r}", $row->rw_display);
            $sheet->setCellValue("D{$r}", $kg);
            $sheet->setCellValue("E{$r}", $tarif);
            $sheet->setCellValue("F{$r}", $jumlah > 0 ? $jumlah : round($kg * $tarif, 2));
            // Column G (Pengeluaran) left empty — for admin to fill
            $no++;
        }

        $lastDataRow = $dataStart + $rows->count() - 1;
        $totalRow    = max($lastDataRow + 1, $dataStart);

        // JUMLAH footer
        $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", 'JUMLAH');
        $sheet->setCellValue("D{$totalRow}", round($rows->sum('total_kg'), 2));
        $sheet->setCellValue("F{$totalRow}", round($rows->sum('total_nilai'), 2));
        $sheet->getStyle("A{$totalRow}:G{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']],
        ]);

        // Yellow highlight for Pengeluaran column — to be filled by admin
        if ($rows->isNotEmpty()) {
            $sheet->getStyle("G{$dataStart}:G{$lastDataRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF2CC']],
            ]);

            $sheet->getStyle("A4:G{$totalRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFBFBFBF'],
                    ],
                ],
            ]);
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(18);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(16);

        return $sp;
    }

    // ── Rivan format ──────────────────────────────────────────────────────────

    private function buildRivanExcel($rows, string $dari, string $sampai): Spreadsheet
    {
        $sp    = new Spreadsheet();
        $sheet = $sp->getActiveSheet()->setTitle('Pengelolaan Sampah');

        $namaTps = PengaturanSistem::get('nama_tps', 'TPS 3R Banjarsari');
        $periode = Carbon::parse($dari)->translatedFormat('d F Y')
            . ' s/d '
            . Carbon::parse($sampai)->translatedFormat('d F Y');

        // Row 1 – title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'DATA PENGELOLAAN SAMPAH ' . strtoupper($namaTps));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 2 – periode
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Laporan Periode: ' . $periode);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Row 3 – blank, Row 4 – headers
        $headers = ['No', 'Tanggal', 'Kompos', 'Pakan Magot', 'Residu', 'Daur Ulang', 'Jumlah Sampah (kg)', 'Iuran (Rp)'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue(chr(65 + $i) . '4', $h);
        }
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
        ]);

        // Data rows starting at row 5
        $dataStart = 5;
        $no        = 1;
        foreach ($rows as $row) {
            $r      = $dataStart + $no - 1;
            $kg     = (float) $row->total_kg;
            $iuran  = (float) $row->total_nilai;

            $sheet->setCellValue("A{$r}", $no);
            $sheet->setCellValue("B{$r}", Carbon::parse($row->tanggal_setoran)->format('d/m/Y'));
            // C–F (Kompos, Pakan Magot, Residu, Daur Ulang) = empty — for Pak Rivan to fill
            $sheet->setCellValue("G{$r}", $kg);
            $sheet->setCellValue("H{$r}", $iuran);
            $no++;
        }

        $lastDataRow = $dataStart + $rows->count() - 1;
        $totalRow    = max($lastDataRow + 1, $dataStart);

        // JUMLAH footer
        $sheet->mergeCells("A{$totalRow}:B{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", 'JUMLAH');
        $sheet->setCellValue("G{$totalRow}", round($rows->sum('total_kg'), 2));
        $sheet->setCellValue("H{$totalRow}", round($rows->sum('total_nilai'), 2));
        $sheet->getStyle("A{$totalRow}:H{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9E1F2']],
        ]);

        // Yellow cells C–F = Pak Rivan's columns
        if ($rows->isNotEmpty()) {
            $sheet->getStyle("C{$dataStart}:F{$lastDataRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF2CC']],
            ]);

            $sheet->getStyle("A4:H{$totalRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FFBFBFBF'],
                    ],
                ],
            ]);
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(14);

        return $sp;
    }
}
