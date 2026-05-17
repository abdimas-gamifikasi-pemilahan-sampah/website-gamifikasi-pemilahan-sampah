@extends('partials.layouts.master')

@section('title', 'Import Data Warga | SIPS')
@section('title-sub', 'Import Data')
@section('pagetitle', 'Import Data Warga')

@section('content')
<div id="layout-wrapper">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="ri-error-warning-line me-1"></i>
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        {{-- Upload form --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload File CSV</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-13 mb-3">
                        Upload file CSV dengan kolom berikut (baris pertama = header):
                    </p>
                    <div class="alert alert-info py-2 fs-13">
                        <code>nama, no_kk, rt, rw, dusun, no_hp, tanggal_terdaftar</code>
                    </div>
                    <p class="text-muted fs-12 mb-4">
                        <strong>no_kk</strong> digunakan sebagai kunci unik — jika sudah ada, data warga akan diperbarui.<br>
                        <strong>tanggal_terdaftar</strong> format: <code>YYYY-MM-DD</code><br>
                        <strong>no_hp</strong> boleh kosong.
                    </p>

                    <form method="POST" action="{{ route('sips.import.preview') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">File CSV <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="csv_file" accept=".csv,.txt" required>
                            <div class="form-text">Maksimum 2 MB. Format: CSV (UTF-8).</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-upload-2-line me-1"></i> Pratinjau & Verifikasi
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mt-3 bg-light border-0">
                <div class="card-body fs-13">
                    <h6 class="fw-semibold mb-2">Contoh format CSV</h6>
                    <pre class="bg-white p-2 rounded fs-12 mb-0" style="overflow-x:auto;">nama,no_kk,rt,rw,dusun,no_hp,tanggal_terdaftar
Siti Rahayu,1234567890123456,01,01,Melati,081234567890,2026-01-15
Budi Santoso,9876543210987654,02,02,Anggrek,,2026-02-01</pre>
                </div>
            </div>
        </div>

        {{-- Log riwayat --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Riwayat Import</h5>
                    <span class="badge bg-light text-dark">{{ $logs->total() }} entri</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>File</th>
                                    <th class="text-center">Baris</th>
                                    <th class="text-center">Berhasil</th>
                                    <th class="text-center">Gagal</th>
                                    <th>Status</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                <tr>
                                    <td class="text-muted fs-12">{{ $log->created_at->format('d/m/y H:i') }}</td>
                                    <td class="fs-12" style="max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $log->filename }}">
                                        {{ $log->filename }}
                                    </td>
                                    <td class="text-center">{{ $log->total_baris }}</td>
                                    <td class="text-center text-success fw-semibold">{{ $log->berhasil }}</td>
                                    <td class="text-center {{ $log->gagal > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $log->gagal }}</td>
                                    <td>
                                        @if($log->status === 'selesai')
                                            <span class="badge bg-success-subtle text-success">Selesai</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Gagal</span>
                                        @endif
                                    </td>
                                    <td class="fs-12 text-muted">{{ $log->uploader->name ?? '-' }}</td>
                                </tr>
                                @if($log->catatan)
                                <tr>
                                    <td colspan="7" class="pt-0 pb-2 px-3">
                                        <div class="alert alert-warning py-1 px-2 mb-0 fs-12">
                                            <i class="ri-error-warning-line me-1"></i>{{ Str::limit($log->catatan, 150) }}
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat import.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($logs->hasPages())
                <div class="card-footer">
                    {{ $logs->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
