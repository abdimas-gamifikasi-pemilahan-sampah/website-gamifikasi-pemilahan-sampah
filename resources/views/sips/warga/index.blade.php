@extends('partials.layouts.master')

@section('title', 'Data Master Warga | SIPS')
@section('title-sub', 'Sistem Informasi Pemilahan Sampah')
@section('pagetitle', 'Data Master Warga')

@section('content')
<div id="layout-wrapper">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1">Daftar Warga Terdaftar</h5>
                        <p class="text-muted mb-0 fs-13">Data ini dipakai sebagai referensi utama saat petugas mencatat setoran.</p>
                    </div>
                    <a href="{{ route('sips.warga.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Warga
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('sips.warga.index') }}" class="row g-2 mb-4">
                        <div class="col-md-5">
                            <label for="search" class="form-label fs-13">Cari Warga</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Nama, No KK, atau dusun">
                        </div>
                        <div class="col-md-3">
                            <label for="rw" class="form-label fs-13">Filter RW</label>
                            <select class="form-select form-select-sm" id="rw" name="rw">
                                <option value="">Semua RW</option>
                                @foreach($rwOptions as $rw)
                                <option value="{{ $rw }}" {{ request('rw') === $rw ? 'selected' : '' }}>
                                    RW {{ $rw }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status_keanggotaan" class="form-label fs-13">Status</label>
                            <select class="form-select form-select-sm" id="status_keanggotaan" name="status_keanggotaan">
                                <option value="">Semua</option>
                                <option value="aktif" {{ request('status_keanggotaan') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="non_aktif" {{ request('status_keanggotaan') === 'non_aktif' ? 'selected' : '' }}>Non Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('sips.warga.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                        </div>
                    </form>

                    @if($warga->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="ri-team-line display-4 d-block mb-2"></i>
                        <p class="mb-2">Belum ada data warga yang cocok dengan filter saat ini.</p>
                        <a href="{{ route('sips.warga.create') }}">Tambahkan warga pertama</a>.
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle mb-0 table-hover">
                            <thead class="table-light sips-table-head">
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <th>Nomor KK</th>
                                    <th>Wilayah</th>
                                    <th>Nomor HP</th>
                                    <th>Tanggal Terdaftar</th>
                                    <th>Partisipasi Bulan Ini</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warga as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->nama }}</div>
                                        <small class="text-muted">RT {{ $item->rt }} / RW {{ $item->rw }}</small>
                                    </td>
                                    <td>{{ $item->no_kk }}</td>
                                    <td>Dusun {{ $item->dusun }}</td>
                                    <td>{{ $item->no_hp ?: '-' }}</td>
                                    <td>{{ optional($item->tanggal_terdaftar)->translatedFormat('d M Y') }}</td>
                                    <td>
                                        @if($item->memiliki_setoran_bulan_ini)
                                        <span class="badge bg-success-subtle text-success">Aktif</span>
                                        @else
                                        <span class="badge bg-secondary-subtle text-secondary">Belum Setor</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->status_keanggotaan === 'aktif')
                                        <span class="badge border border-success text-success">Aktif</span>
                                        @else
                                        <span class="badge border border-danger text-danger">Non Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('sips.warga.edit', $item->id) }}"
                                               class="btn btn-sm btn-light border" title="Edit data warga">
                                                <i class="ri-edit-2-line"></i>
                                            </a>
                                            <form method="POST" action="{{ route('sips.warga.status', $item->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status_keanggotaan"
                                                       value="{{ $item->status_keanggotaan === 'aktif' ? 'non_aktif' : 'aktif' }}">
                                                <input type="hidden" name="filter_search" value="{{ request('search') }}">
                                                <input type="hidden" name="filter_rw" value="{{ request('rw') }}">
                                                <input type="hidden" name="filter_status_keanggotaan" value="{{ request('status_keanggotaan') }}">
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <button type="submit"
                                                        class="btn btn-sm {{ $item->status_keanggotaan === 'aktif' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                        title="{{ $item->status_keanggotaan === 'aktif' ? 'Nonaktifkan warga' : 'Aktifkan warga' }}">
                                                    <i class="ri-{{ $item->status_keanggotaan === 'aktif' ? 'user-unfollow' : 'user-follow' }}-line"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="text-muted fs-13">
                            Menampilkan {{ $warga->firstItem() }}-{{ $warga->lastItem() }} dari {{ $warga->total() }} warga
                        </span>
                        {{ $warga->onEachSide(1)->links('partials.pagination') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
