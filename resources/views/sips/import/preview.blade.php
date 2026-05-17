@extends('partials.layouts.master')

@section('title', 'Pratinjau Import | SIPS')
@section('title-sub', 'Import Data')
@section('pagetitle', 'Pratinjau Data CSV')

@section('content')
<div id="layout-wrapper">

    @if(!empty($errors))
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <strong>Peringatan format:</strong>
        @foreach($errors as $e)<div>{{ $e }}</div>@endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap gap-3 align-items-center justify-content-between">
            <div>
                <h5 class="mb-1">Pratinjau Data (10 baris pertama dari {{ $totalRows }})</h5>
                <p class="text-muted mb-0 fs-13">
                    Total: <strong>{{ $totalRows }}</strong> baris &nbsp;·&nbsp;
                    <span class="text-success"><i class="ri-checkbox-circle-line"></i> {{ $totalRows - $errorCount }} valid</span>
                    @if($errorCount > 0)
                    &nbsp;·&nbsp; <span class="text-danger"><i class="ri-error-warning-line"></i> {{ $errorCount }} error</span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('sips.import.index') }}" class="btn btn-light btn-sm">
                    <i class="ri-arrow-left-line me-1"></i> Batalkan
                </a>
                @if($errorCount < $totalRows)
                <form method="POST" action="{{ route('sips.import.confirm') }}">
                    @csrf
                    <input type="hidden" name="tmp_path" value="{{ $tmpPath }}">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="ri-upload-cloud-2-line me-1"></i> Konfirmasi Import {{ $totalRows }} Baris
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Preview Data</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>No KK</th>
                            <th>RT</th>
                            <th>RW</th>
                            <th>Dusun</th>
                            <th>No HP</th>
                            <th>Tgl Daftar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($preview as $row)
                        <tr class="{{ !empty($row['_errors']) ? 'table-danger' : '' }}">
                            <td class="text-muted fs-12">{{ $row['_line'] }}</td>
                            <td>{{ $row['nama'] ?? '-' }}</td>
                            <td class="fs-12">{{ $row['no_kk'] ?? '-' }}</td>
                            <td>{{ $row['rt'] ?? '-' }}</td>
                            <td>{{ $row['rw'] ?? '-' }}</td>
                            <td>{{ $row['dusun'] ?? '-' }}</td>
                            <td class="fs-12 text-muted">{{ $row['no_hp'] ?? '-' }}</td>
                            <td class="fs-12">{{ $row['tanggal_terdaftar'] ?? '-' }}</td>
                            <td>
                                @if(empty($row['_errors']))
                                    <span class="badge bg-success-subtle text-success">OK</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger" title="{{ implode(', ', $row['_errors']) }}">
                                        Error
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @if(!empty($row['_errors']))
                        <tr class="table-danger">
                            <td colspan="9" class="pt-0 pb-1 px-3">
                                <small class="text-danger"><i class="ri-error-warning-line me-1"></i>{{ implode(' · ', $row['_errors']) }}</small>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if($totalRows > 10)
        <div class="card-footer text-muted fs-12">
            Menampilkan 10 dari {{ $totalRows }} baris. Semua baris akan diproses saat konfirmasi.
        </div>
        @endif
    </div>

</div>
@endsection
