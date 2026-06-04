@extends('partials.layouts.master')

@section('title', 'Manajemen Tarif | SIPS')
@section('title-sub', 'Pengaturan')
@section('pagetitle', 'Manajemen Tarif Sampah')

@section('content')
<div id="layout-wrapper">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <strong>Terdapat kesalahan pada form:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @php
        $tipeMeta = [
            'organik' => ['label' => 'Organik', 'class' => 'success', 'icon' => 'ri-leaf-line'],
            'anorganik' => ['label' => 'Anorganik', 'class' => 'info', 'icon' => 'ri-recycle-line'],
        ];
    @endphp

    <div class="row">
        @foreach($tipeMeta as $tipe => $meta)
        <div class="col-md-6">
            <div class="card border border-{{ $meta['class'] }} shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fs-13 text-muted mb-1">Total Item</div>
                            <h4 class="mb-1">{{ $ringkasanTipe[$tipe] ?? 0 }}</h4>
                            <div class="fw-semibold text-{{ $meta['class'] }}">{{ $meta['label'] }}</div>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-{{ $meta['class'] }}-subtle text-{{ $meta['class'] }} rounded-circle fs-4">
                                <i class="{{ $meta['icon'] }}"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-h-100">
                <div class="card-header">
                    <h5 class="card-title mb-1">Pengaturan Tarif Default</h5>
                    <p class="text-muted mb-0 fs-13">Kelola nilai default</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sips.pengaturan.update') }}" class="row g-3 align-items-end">
                        @csrf
                        @method('PATCH')

                        <div class="col-md-5">
                            <label for="tarif_flat_per_kg" class="form-label">Tarif Default Tidak Dipilah (Rp/kg) <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('tarif_flat_per_kg') is-invalid @enderror"
                                   id="tarif_flat_per_kg"
                                   name="tarif_flat_per_kg"
                                   min="0"
                                   step="100"
                                   value="{{ old('tarif_flat_per_kg', $settings['tarif_flat_per_kg']) }}"
                                   required>
                            <div class="form-text">Dipakai sebagai tarif default untuk sampah tidak dipilah.</div>
                            @error('tarif_flat_per_kg')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-5">
                            <label for="nilai_dipilah_per_kg" class="form-label">Nilai Dipilah (Rp/kg) <span class="text-danger">*</span></label>
                            <input type="number"
                                   class="form-control @error('nilai_dipilah_per_kg') is-invalid @enderror"
                                   id="nilai_dipilah_per_kg"
                                   name="nilai_dipilah_per_kg"
                                   min="0"
                                   step="100"
                                   value="{{ old('nilai_dipilah_per_kg', $settings['nilai_dipilah_per_kg']) }}"
                                   required>
                            <div class="form-text">Dipakai sebagai nilai default untuk sampah yang dipilah.</div>
                            @error('nilai_dipilah_per_kg')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-2 d-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ri-save-line me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1">Daftar Item Tarif</h5>
                        <p class="text-muted mb-0 fs-13">Kelola item sampah, status item, dan histori harga per item.</p>
                    </div>
                    <a href="{{ route('sips.tarif.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Item Tarif
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('sips.tarif.index') }}" class="row g-2 mb-4">
                        <div class="col-md-5">
                            <label for="search" class="form-label fs-13">Cari Item</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Nama item sampah">
                        </div>
                        <div class="col-md-3">
                            <label for="tipe" class="form-label fs-13">Tipe Utama</label>
                            <select class="form-select form-select-sm" id="tipe" name="tipe">
                                <option value="">Semua Tipe</option>
                                @foreach($tipeMeta as $tipe => $meta)
                                <option value="{{ $tipe }}" {{ request('tipe') === $tipe ? 'selected' : '' }}>
                                    {{ $meta['label'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label fs-13">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">Semua</option>
                                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="arsip" {{ request('status') === 'arsip' ? 'selected' : '' }}>Arsip</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('sips.tarif.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                        </div>
                    </form>

                    @if($tarifItems->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="ri-price-tag-3-line display-4 d-block mb-2"></i>
                        <p class="mb-2">Belum ada item tarif yang cocok dengan filter saat ini.</p>
                        <a href="{{ route('sips.tarif.create') }}">Tambahkan item tarif pertama</a>.
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle mb-0 table-hover">
                            <thead class="table-light sips-table-head">
                                <tr>
                                    <th>Item</th>
                                    <th>Tipe</th>
                                    <th>Tarif Aktif</th>
                                    <th>Mulai Berlaku</th>
                                    <th>Status</th>
                                    <th>Riwayat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tarifItems as $item)
                                @php
                                    $today = now()->startOfDay();
                                    $riwayatBenarAktif = $item->riwayatTarif->first(function ($riwayat) use ($today) {
                                        return $riwayat->tanggal_mulai?->lte($today)
                                            && (is_null($riwayat->tanggal_akhir) || $riwayat->tanggal_akhir?->gte($today));
                                    });
                                    $riwayatAktif = $riwayatBenarAktif ?? $item->riwayatTarif->first();
                                    $isPending    = $item->status === 'aktif' && is_null($riwayatBenarAktif);
                                    $meta = $tipeMeta[$item->tipe_sampah] ?? ['label' => strtoupper($item->tipe_sampah), 'class' => 'secondary'];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $item->nama_item }}</div>
                                        <small class="text-muted">ID #{{ $item->id }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $meta['class'] }}-subtle text-{{ $meta['class'] }}">
                                            {{ $meta['label'] }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold">
                                        @if($riwayatAktif)
                                        Rp {{ number_format($riwayatAktif->harga_per_kg, 0, ',', '.') }}/kg
                                        @else
                                        <span class="text-muted">Belum ada harga</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($riwayatAktif?->tanggal_mulai)->translatedFormat('d M Y') ?: '-' }}</td>
                                    <td>
                                        @if($item->status === 'arsip')
                                            <span class="badge bg-secondary-subtle text-secondary">Arsip</span>
                                        @elseif($isPending)
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">Aktif</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->riwayatTarif->count() }} entri</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('sips.tarif.show', $item->id) }}" class="btn btn-sm btn-light border" title="Detail item tarif">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('sips.tarif.edit', $item->id) }}" class="btn btn-sm btn-light border" title="Edit item tarif">
                                                <i class="ri-edit-2-line"></i>
                                            </a>
                                            <a href="{{ route('sips.tarif.price.create', $item->id) }}" class="btn btn-sm btn-primary" title="Tambah harga baru">
                                                <i class="ri-price-tag-3-line"></i>
                                            </a>
                                            <form method="POST" action="{{ route('sips.tarif.status', $item->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $item->status === 'aktif' ? 'arsip' : 'aktif' }}">
                                                <input type="hidden" name="filter_search" value="{{ request('search') }}">
                                                <input type="hidden" name="filter_tipe" value="{{ request('tipe') }}">
                                                <input type="hidden" name="filter_status" value="{{ request('status') }}">
                                                <input type="hidden" name="page" value="{{ request('page') }}">
                                                <button type="submit"
                                                        class="btn btn-sm {{ $item->status === 'aktif' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                        title="{{ $item->status === 'aktif' ? 'Arsipkan item' : 'Aktifkan item' }}">
                                                    <i class="ri-{{ $item->status === 'aktif' ? 'archive' : 'restart' }}-line"></i>
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
                            Menampilkan {{ $tarifItems->firstItem() }}-{{ $tarifItems->lastItem() }} dari {{ $tarifItems->total() }} item tarif
                        </span>
                        {{ $tarifItems->onEachSide(1)->links('partials.pagination') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Riwayat Pembaruan Tarif Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm text-nowrap align-middle mb-0">
                            <thead class="table-light sips-table-head">
                                <tr>
                                    <th>Tanggal Efektif</th>
                                    <th>Item</th>
                                    <th>Tipe</th>
                                    <th>Harga</th>
                                    <th>Diperbarui Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($riwayatTerbaru as $riwayat)
                                <tr>
                                    <td>{{ optional($riwayat->tanggal_mulai)->translatedFormat('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('sips.tarif.show', $riwayat->tarif_item_id) }}" class="fw-semibold">
                                            {{ $riwayat->tarifItem->nama_item }}
                                        </a>
                                    </td>
                                    <td>{{ $tipeMeta[$riwayat->tarifItem->tipe_sampah]['label'] ?? strtoupper($riwayat->tarifItem->tipe_sampah) }}</td>
                                    <td>Rp {{ number_format($riwayat->harga_per_kg, 0, ',', '.') }}/kg</td>
                                    <td>{{ $riwayat->pengubahUser->name ?? 'System' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat tarif.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
