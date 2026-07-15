<?php

namespace App\Services;

use App\Helpers\TanggalParser;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OcrExcelParser
{
    private const REQUIRED = ['tanggal', 'nama_penyetor', 'status_pemilahan', 'berat_kg'];

    /**
     * Parse an AI-generated OCR Excel file into an array of normalized rows.
     *
     * @return array<int, array{
     *   tanggal: string|null,
     *   nama_penyetor: string,
     *   rt: string,
     *   rw: string,
     *   status_pemilahan: string,
     *   jenis_sampah: string,
     *   berat_kg: float,
     *   catatan: string,
     *   _row_num: int
     * }>
     *
     * @throws \RuntimeException on validation failure
     */
    public function parse(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet       = $spreadsheet->getActiveSheet();
        $rows        = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            throw new \RuntimeException('File Excel kosong.');
        }

        // Build column index map from header row
        $header = array_map(
            fn ($h) => mb_strtolower(trim((string) $h)),
            $rows[0]
        );
        $colMap = array_flip($header);

        // Validate required columns exist
        $missing = array_filter(self::REQUIRED, fn ($col) => !isset($colMap[$col]));
        if (!empty($missing)) {
            throw new \RuntimeException(
                'Kolom wajib tidak ditemukan: ' . implode(', ', $missing) . '. '
                . 'Pastikan menggunakan template Excel OCR yang benar.'
            );
        }

        $result = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];

            // Skip completely empty rows
            $values = array_values(array_filter(
                $row,
                fn ($v) => $v !== null && $v !== ''
            ));
            if (empty($values)) {
                continue;
            }

            $tanggal = TanggalParser::parse($row[$colMap['tanggal']] ?? null);

            $result[] = [
                'tanggal'          => $tanggal?->format('Y-m-d'),
                'nama_penyetor'    => trim((string) ($row[$colMap['nama_penyetor']] ?? '')),
                'rt'               => trim((string) ($row[$colMap['rt'] ?? -99] ?? '')),
                'rw'               => trim((string) ($row[$colMap['rw'] ?? -99] ?? '')),
                'status_pemilahan' => $this->parseStatus((string) ($row[$colMap['status_pemilahan']] ?? '')),
                'jenis_sampah'     => trim((string) ($row[$colMap['jenis_sampah'] ?? -99] ?? '')),
                'berat_kg'         => (float) ($row[$colMap['berat_kg']] ?? 0),
                'catatan'          => trim((string) ($row[$colMap['catatan'] ?? -99] ?? '')),
                '_row_num'         => $i + 1,
            ];
        }

        if (empty($result)) {
            throw new \RuntimeException('Tidak ada baris data yang valid di dalam file.');
        }

        return $result;
    }

    private function parseStatus(string $value): string
    {
        $v = mb_strtolower(trim($value));
        return str_contains($v, 'tidak') ? 'tidak_dipilah' : 'dipilah';
    }
}
