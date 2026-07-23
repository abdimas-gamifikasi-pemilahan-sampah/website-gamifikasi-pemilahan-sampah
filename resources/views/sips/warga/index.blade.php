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

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="ri-error-warning-line me-1"></i> {{ session('error') }}
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
                    <div class="d-flex align-items-center gap-2">
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('sips.import.index') }}" class="text-muted fs-13">
                            <i class="ri-upload-2-line me-1"></i>Import CSV
                        </a>
                        @endif
                        <a href="{{ route('sips.warga.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Warga
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $hasWargaFilter = request('search') || request('rw') || request('status_keanggotaan') || request('belum_setor');
                        $ringkasanWarga = [
                            [
                                'label' => 'Total Warga',
                                'value' => (int) ($ringkasan->total_warga ?? 0),
                                'class' => 'primary',
                                'icon' => 'ri-team-line',
                            ],
                            [
                                'label' => 'Warga Aktif',
                                'value' => (int) ($ringkasan->total_aktif ?? 0),
                                'class' => 'success',
                                'icon' => 'ri-user-follow-line',
                            ],
                            [
                                'label' => 'Warga Non Aktif',
                                'value' => (int) ($ringkasan->total_non_aktif ?? 0),
                                'class' => 'secondary',
                                'icon' => 'ri-user-unfollow-line',
                            ],
                        ];
                    @endphp

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="mb-1">Ringkasan Data Warga</h6>
                                <p class="text-muted mb-0 fs-13">
                                    {{ $hasWargaFilter ? 'Menampilkan hasil filter data warga.' : 'Menampilkan seluruh data warga terdaftar.' }}
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            @foreach($ringkasanWarga as $item)
                            <div class="col-6 col-md-4">
                                <div class="card border border-{{ $item['class'] }} shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fs-13 text-muted mb-1">{{ $item['label'] }}</div>
                                                <h4 class="mb-0">{{ number_format($item['value'], 0, ',', '.') }}</h4>
                                            </div>
                                            <div class="avatar-sm">
                                                <span class="avatar-title bg-{{ $item['class'] }}-subtle text-{{ $item['class'] }} rounded-circle fs-4">
                                                    <i class="{{ $item['icon'] }}"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

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

                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="text-muted fs-13">Filter cepat:</span>
                        @if(request('belum_setor'))
                            <a href="{{ route('sips.warga.index', array_merge(request()->except('belum_setor', 'page'), [])) }}"
                               class="btn btn-warning btn-sm">
                                <i class="ri-user-unfollow-line me-1"></i> Belum Setor Bulan Ini
                                <i class="ri-close-line ms-1"></i>
                            </a>
                        @else
                            <a href="{{ route('sips.warga.index', array_merge(request()->query(), ['belum_setor' => 1])) }}"
                               class="btn btn-outline-warning btn-sm">
                                <i class="ri-user-unfollow-line me-1"></i> Belum Setor Bulan Ini
                            </a>
                        @endif
                    </div>

                    @if($warga->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="ri-team-line display-4 d-block mb-2"></i>
                        <p class="mb-2">Belum ada data warga yang cocok dengan filter saat ini.</p>
                        <a href="{{ route('sips.warga.create') }}">Tambahkan warga pertama</a>.
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light sips-table-head">
                                <tr>
                                    <th>Nama &amp; Alamat</th>
                                    <th class="d-none d-md-table-cell">Wilayah</th>
                                    <th class="d-none d-lg-table-cell">No. KK</th>
                                    <th class="d-none d-lg-table-cell">Nomor HP</th>
                                    <th class="d-none d-lg-table-cell">Terdaftar</th>
                                    <th>Partisipasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warga as $item)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->nama }}</div>
                                        @if($item->alamat)
                                        <small class="text-muted">{{ Str::limit($item->alamat, 48) }}</small>
                                        @else
                                        <small class="text-muted fst-italic">Alamat belum diisi</small>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        @if($item->rt || $item->rw)
                                        <div>RT {{ $item->rt ?? '-' }} / RW {{ $item->rw ?? '-' }}</div>
                                        @endif
                                        @if($item->dusun)<small class="text-muted">Dusun {{ $item->dusun }}</small>@endif
                                        @if(!$item->rt && !$item->rw && !$item->dusun)<span class="text-muted">-</span>@endif
                                    </td>
                                    <td class="d-none d-lg-table-cell">{{ $item->no_kk ?? '-' }}</td>
                                    <td class="d-none d-lg-table-cell">{{ $item->no_hp ?: '-' }}</td>
                                    <td class="d-none d-lg-table-cell">{{ optional($item->tanggal_terdaftar)->translatedFormat('d M Y') }}</td>
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
                                            <form method="POST"
                                                  action="{{ route('sips.warga.destroy', $item->id) }}"
                                                  onsubmit="return confirm('Hapus data warga {{ addslashes($item->nama) }}?\n\nTindakan ini permanen dan tidak dapat dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-light border text-danger"
                                                        title="Hapus data warga">
                                                    <i class="ri-delete-bin-line"></i>
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
