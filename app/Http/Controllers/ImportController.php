<?php

namespace App\Http\Controllers;

use App\Models\ImportLog;
use App\Models\Warga;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    public function index()
    {
        $logs = ImportLog::with('uploader')->latest()->paginate(20);
        return view('sips.import.index', compact('logs'));
    }

    public function downloadTemplate(): StreamedResponse
    {
        $rows = [
            ['nama', 'no_kk', 'rt', 'rw', 'dusun', 'no_hp', 'tanggal_terdaftar'],
            ['Siti Rahayu', '1234567890123456', '1', '1', 'Melati', '081234567890', '2026-01-15'],
            ['Budi Santoso', '9876543210987654', '2', '2', 'Anggrek', '', '2026-02-01'],
        ];

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'template_import_warga.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $file  = $request->file('csv_file');
        $path  = $file->getRealPath();
        $rows  = [];
        $errors = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle);
            $header = array_map('trim', $header);

            $expected = ['nama', 'no_kk', 'rt', 'rw', 'dusun', 'no_hp', 'tanggal_terdaftar'];
            $missing  = array_diff($expected, $header);
            if (!empty($missing)) {
                return back()->withErrors(['csv_file' => 'Kolom wajib tidak ada: ' . implode(', ', $missing)]);
            }

            $lineNum = 1;
            while (($data = fgetcsv($handle)) !== false) {
                $lineNum++;
                if (count($data) < count($header)) {
                    $errors[] = "Baris $lineNum: Jumlah kolom tidak sesuai.";
                    continue;
                }
                $row = array_combine($header, array_map('trim', $data));
                $row['_line'] = $lineNum;

                // Basic validation
                $rowErrors = [];
                if (empty($row['nama']))    $rowErrors[] = 'nama kosong';
                if (strlen($row['no_kk'] ?? '') !== 16) $rowErrors[] = 'no_kk harus 16 digit';
                if (empty($row['rt']))      $rowErrors[] = 'rt kosong';
                if (empty($row['rw']))      $rowErrors[] = 'rw kosong';
                if (empty($row['dusun']))   $rowErrors[] = 'dusun kosong';
                if (!empty($row['tanggal_terdaftar'])) {
                    try { Carbon::parse($row['tanggal_terdaftar']); }
                    catch (\Exception $e) { $rowErrors[] = 'tanggal_terdaftar tidak valid'; }
                }

                $row['_errors'] = $rowErrors;
                $rows[] = $row;

                if (count($rows) >= 200) {
                    break; // Safety cap
                }
            }
            fclose($handle);
        }

        // Store file temporarily for confirm step
        $tmpPath = $file->storeAs('imports/tmp', uniqid('warga_') . '.csv');

        $preview = array_slice($rows, 0, 10);
        $totalRows = count($rows);
        $errorCount = count(array_filter($rows, fn($r) => !empty($r['_errors'])));

        return view('sips.import.preview', compact(
            'preview', 'totalRows', 'errorCount', 'tmpPath', 'errors'
        ));
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'tmp_path' => ['required', 'string'],
        ]);

        $fullPath = storage_path('app/' . $request->input('tmp_path'));
        if (!file_exists($fullPath)) {
            return redirect()->route('sips.import.index')
                ->withErrors(['tmp_path' => 'File sementara tidak ditemukan. Ulangi proses upload.']);
        }

        $total    = 0;
        $berhasil = 0;
        $gagal    = 0;
        $catatan  = [];

        if (($handle = fopen($fullPath, 'r')) !== false) {
            $header = array_map('trim', fgetcsv($handle));
            $lineNum = 1;

            DB::transaction(function () use ($handle, $header, &$total, &$berhasil, &$gagal, &$catatan, &$lineNum) {
                while (($data = fgetcsv($handle)) !== false) {
                    $lineNum++;
                    $total++;

                    if (count($data) < count($header)) {
                        $gagal++;
                        $catatan[] = "Baris $lineNum: Jumlah kolom tidak sesuai.";
                        continue;
                    }

                    $row = array_combine($header, array_map('trim', $data));

                    try {
                        Warga::updateOrCreate(
                            ['no_kk' => $row['no_kk']],
                            [
                                'nama'               => $row['nama'],
                                'rt'                 => $row['rt'],
                                'rw'                 => $row['rw'],
                                'dusun'              => $row['dusun'],
                                'no_hp'              => $row['no_hp'] ?? null,
                                'tanggal_terdaftar'  => Carbon::parse($row['tanggal_terdaftar']),
                                'status_keanggotaan' => 'aktif',
                            ]
                        );
                        $berhasil++;
                    } catch (\Exception $e) {
                        $gagal++;
                        $catatan[] = "Baris $lineNum ({$row['nama']}): " . $e->getMessage();
                    }
                }
            });

            fclose($handle);
        }

        ImportLog::create([
            'filename'              => basename($fullPath),
            'jenis'                 => 'warga',
            'total_baris'           => $total,
            'berhasil'              => $berhasil,
            'gagal'                 => $gagal,
            'status'                => $gagal === $total ? 'gagal' : 'selesai',
            'diupload_oleh_user_id' => auth()->id(),
            'catatan'               => empty($catatan) ? null : implode("\n", array_slice($catatan, 0, 20)),
        ]);

        @unlink($fullPath);

        return redirect()->route('sips.import.index')
            ->with('success', "Import selesai: $berhasil berhasil, $gagal gagal dari $total baris.");
    }
}
