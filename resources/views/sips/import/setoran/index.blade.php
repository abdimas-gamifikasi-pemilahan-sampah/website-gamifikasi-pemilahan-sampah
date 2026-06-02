@extends('partials.layouts.master')

@section('title', 'Import Setoran | SIPS')
@section('title-sub', 'Import')
@section('pagetitle', 'Import Setoran dari Excel')

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
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">

        {{-- ── Template Downloads ── --}}
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-download-2-line me-2"></i> Unduh Template</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Unduh salah satu template sesuai format data yang dimiliki, isi datanya, lalu upload di bawah.</p>
                    <div class="row g-3">

                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="bg-primary-subtle rounded p-2 me-3">
                                        <i class="ri-file-excel-line text-primary fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Template Perolehan</h6>
                                        <small class="text-muted">DATA PEROLEHAN SAMPAH</small>
                                    </div>
                                </div>
                                <p class="text-muted fs-13 mb-3">
                                    Format: No, Hari/Tanggal, RW, Jumlah Sampah/kg, Rp, Jumlah, Pengeluaran.
                                    Satu baris = satu pengangkutan.
                                </p>
                                <a href="{{ route('sips.import.setoran.template', 'perolehan') }}"
                                   class="btn btn-sm btn-outline-primary w-100">
                                    <i class="ri-download-line me-1"></i> Unduh .xlsx
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="bg-warning-subtle rounded p-2 me-3">
                                        <i class="ri-file-excel-line text-warning fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Template Rivan</h6>
                                        <small class="text-muted">DATA PENGELOLAAN SAMPAH</small>
                                    </div>
                                </div>
                                <p class="text-muted fs-13 mb-3">
                                    Format: Tanggal, Kompos*, Pakan Magot*, Residu*, Daur Ulang*, Jumlah Sampah, Iuran.
                                    <em>*kolom kosong untuk Pak Rivan.</em>
                                </p>
                                <a href="{{ route('sips.import.setoran.template', 'rivan') }}"
                                   class="btn btn-sm btn-outline-warning w-100">
                                    <i class="ri-download-line me-1"></i> Unduh .xlsx
                                </a>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="bg-success-subtle rounded p-2 me-3">
                                        <i class="ri-file-excel-line text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Template Detail</h6>
                                        <small class="text-muted">Per Penyetor</small>
                                    </div>
                                </div>
                                <p class="text-muted fs-13 mb-3">
                                    Format: No, Nama Penyetor, RW, RT, Berat (kg), Status Pilah, Keterangan.
                                    Semua baris → satu setoran.
                                </p>
                                <a href="{{ route('sips.import.setoran.template', 'detail') }}"
                                   class="btn btn-sm btn-outline-success w-100">
                                    <i class="ri-download-line me-1"></i> Unduh .xlsx
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ── Upload Form ── --}}
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-upload-cloud-2-line me-2"></i> Upload File</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sips.import.setoran.preview') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">File Excel / CSV <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('file') is-invalid @enderror"
                                   name="file" accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Format: .xlsx, .xls, atau .csv — maks 4MB. Format otomatis terdeteksi.</div>
                            @error('file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3" id="tanggal-wrapper">
                            <label class="form-label">Tanggal Setoran
                                <span class="text-muted fs-12">(untuk format Detail — diabaikan untuk Perolehan/Rivan)</span>
                            </label>
                            <input type="date" class="form-control" name="tanggal_setoran"
                                   value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}">
                        </div>

                        <div class="alert alert-info py-2 fs-13">
                            <i class="ri-information-line me-1"></i>
                            Format file akan dideteksi otomatis dari header kolom. Pastikan header sesuai template.
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-eye-line me-1"></i> Preview Data
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Recent Logs ── --}}
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-history-line me-2"></i> Riwayat Import</h5>
                </div>
                <div class="card-body p-0">
                    @if($logs->isEmpty())
                    <div class="text-center py-4 text-muted fs-13">
                        <i class="ri-inbox-line d-block fs-1 mb-2"></i>
                        Belum ada riwayat import setoran.
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu</th>
                                    <th>Format</th>
                                    <th class="text-center">Berhasil</th>
                                    <th class="text-center">Gagal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <div>{{ $log->created_at->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $log->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @php $fmt = str_replace('setoran_', '', $log->jenis); @endphp
                                        <span class="badge bg-secondary-subtle text-secondary text-capitalize">{{ $fmt }}</span>
                                    </td>
                                    <td class="text-center text-success fw-semibold">{{ $log->berhasil }}</td>
                                    <td class="text-center {{ $log->gagal > 0 ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $log->gagal }}</td>
                                    <td>
                                        @if($log->status === 'selesai')
                                            <span class="badge bg-success-subtle text-success">Selesai</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Gagal</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">{{ $logs->links() }}</div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
