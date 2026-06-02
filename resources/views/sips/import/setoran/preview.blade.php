@extends('partials.layouts.master')

@section('title', 'Preview Import | SIPS')
@section('title-sub', 'Import')
@section('pagetitle', 'Preview Data Import')

@section('content')
<div id="layout-wrapper">

    {{-- Summary banner --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 bg-primary-subtle">
                <div class="card-body py-3">
                    <div class="fs-24 fw-bold text-primary">{{ $totalRows }}</div>
                    <div class="text-muted fs-13">Total Baris</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 bg-success-subtle">
                <div class="card-body py-3">
                    <div class="fs-24 fw-bold text-success">{{ $validCount }}</div>
                    <div class="text-muted fs-13">Baris Valid</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 {{ $errorCount > 0 ? 'bg-danger-subtle' : 'bg-light' }}">
                <div class="card-body py-3">
                    <div class="fs-24 fw-bold {{ $errorCount > 0 ? 'text-danger' : 'text-muted' }}">{{ $errorCount }}</div>
                    <div class="text-muted fs-13">Baris Error</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 bg-info-subtle">
                <div class="card-body py-3">
                    <div class="fs-14 fw-bold text-info text-capitalize">{{ $format }}</div>
                    <div class="text-muted fs-13">Format Terdeteksi</div>
                </div>
            </div>
        </div>
    </div>

    @if($errorCount > 0)
    <div class="alert alert-warning mb-4">
        <i class="ri-alert-line me-1"></i>
        <strong>{{ $errorCount }} baris bermasalah</strong> tidak akan diimpor (baris merah di bawah).
        Baris valid ({{ $validCount }}) tetap bisa diimpor.
    </div>
    @endif

    @if($validCount === 0)
    <div class="alert alert-danger mb-4">
        <i class="ri-error-warning-line me-1"></i>
        <strong>Tidak ada baris yang valid.</strong> Perbaiki file dan upload ulang.
    </div>
    @endif

    {{-- Confirm form --}}
    <form method="POST" action="{{ route('sips.import.setoran.confirm') }}">
        @csrf
        <input type="hidden" name="tanggal_setoran" value="{{ $tanggalSetoran }}">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-0">Preview Data — Format: <span class="text-primary text-capitalize">{{ $format }}</span></h5>
                    @if($format === 'detail')
                    <small class="text-muted">
                        Semua baris akan dijadikan SATU setoran — tanggal: <strong>{{ \Carbon\Carbon::parse($tanggalSetoran)->translatedFormat('d F Y') }}</strong>
                    </small>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sips.import.setoran.index') }}" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Kembali / Upload Ulang
                    </a>
                    @if($validCount > 0)
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="ri-check-line me-1"></i>
                        Konfirmasi Import {{ $validCount }} Baris
                    </button>
                    @endif
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:36px">#</th>
                                @if($format === 'perolehan')
                                    <th>Tanggal</th><th>RW</th><th>Jumlah Sampah (kg)</th>
                                @elseif($format === 'rivan')
                                    <th>Tanggal</th><th>Jumlah Sampah (kg)</th>
                                @else
                                    <th>Nama Penyetor</th><th>RW</th><th>RT</th><th>Berat (kg)</th><th>Status Pilah</th>
                                @endif
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $i => $row)
                            @php $hasError = !empty($row['errors']); @endphp
                            <tr class="{{ $hasError ? 'table-danger' : '' }}">
                                <td class="text-muted">{{ $i + 1 }}</td>

                                @if($format === 'perolehan')
                                    <td>{{ $row['tanggal'] ?? '—' }}</td>
                                    <td>{{ $row['area_rw'] ?: '—' }}</td>
                                    <td>{{ $row['kg'] !== null ? number_format($row['kg'], 1) . ' kg' : '—' }}</td>
                                @elseif($format === 'rivan')
                                    <td>{{ $row['tanggal'] ?? '—' }}</td>
                                    <td>{{ $row['kg'] !== null ? number_format($row['kg'], 1) . ' kg' : '—' }}</td>
                                @else
                                    <td>{{ $row['nama_penyetor'] ?: '—' }}</td>
                                    <td>{{ $row['rw'] ?: '—' }}</td>
                                    <td>{{ $row['rt'] ?: '—' }}</td>
                                    <td>{{ $row['berat_kg'] !== null ? number_format($row['berat_kg'], 1) . ' kg' : '—' }}</td>
                                    <td>
                                        @if($row['status_pemilahan'] === 'dipilah')
                                            <span class="badge bg-success-subtle text-success">Dipilah</span>
                                        @elseif($row['status_pemilahan'] === 'tidak_dipilah')
                                            <span class="badge bg-danger-subtle text-danger">Tidak Dipilah</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endif

                                <td>
                                    @if($hasError)
                                        @foreach($row['errors'] as $err)
                                            <div class="text-danger fs-12"><i class="ri-error-warning-line me-1"></i>{{ $err }}</div>
                                        @endforeach
                                    @else
                                        @foreach($row['warnings'] as $warn)
                                            <div class="text-warning fs-12"><i class="ri-alert-line me-1"></i>{{ $warn }}</div>
                                        @endforeach
                                        @if(empty($row['warnings']))
                                            <span class="text-success fs-12"><i class="ri-checkbox-circle-line me-1"></i>Valid</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($validCount > 0)
            <div class="card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('sips.import.setoran.index') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn btn-success">
                    <i class="ri-check-double-line me-1"></i>
                    Simpan {{ $validCount }} Baris ke Database
                </button>
            </div>
            @endif
        </div>
    </form>

</div>
@endsection
