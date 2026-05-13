@extends('partials.layouts.master')

@section('title', 'Detail Item Tarif | SIPS')
@section('title-sub', 'Pengaturan')
@section('pagetitle', 'Detail Item Tarif')

@section('content')
<div id="layout-wrapper">
    @php
        $today = now()->startOfDay();
        $tarifAktif = $tarifItem->riwayatTarif->first(function ($riwayat) use ($today) {
            return $riwayat->tanggal_mulai?->lte($today)
                && (is_null($riwayat->tanggal_akhir) || $riwayat->tanggal_akhir?->gte($today));
        }) ?? $tarifItem->riwayatTarif->first();
        $meta = [
            'organik' => ['label' => 'Organik', 'class' => 'success'],
            'anorganik' => ['label' => 'Anorganik', 'class' => 'info'],
        ][$tarifItem->tipe_sampah] ?? ['label' => strtoupper($tarifItem->tipe_sampah), 'class' => 'secondary'];
    @endphp

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Ringkasan Item</h5>
                    <span class="badge bg-{{ $meta['class'] }}-subtle text-{{ $meta['class'] }}">{{ $meta['label'] }}</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted fs-13">Nama Item</div>
                        <div class="fw-semibold">{{ $tarifItem->nama_item }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted fs-13">Status</div>
                        <span class="badge {{ $tarifItem->status === 'aktif' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                            {{ $tarifItem->status === 'aktif' ? 'Aktif' : 'Arsip' }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted fs-13">Tarif Aktif</div>
                        <div class="fw-semibold">
                            @if($tarifAktif)
                            Rp {{ number_format($tarifAktif->harga_per_kg, 0, ',', '.') }}/kg
                            @else
                            Belum ada harga
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted fs-13">Mulai Berlaku</div>
                        <div>{{ optional($tarifAktif?->tanggal_mulai)->translatedFormat('d M Y') ?: '-' }}</div>
                    </div>
                    <div class="mb-4">
                        <div class="text-muted fs-13">Dibuat Oleh</div>
                        <div>{{ $tarifItem->pembuatUser->name ?? 'System' }}</div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('sips.tarif.edit', $tarifItem->id) }}" class="btn btn-primary btn-sm">
                            <i class="ri-edit-2-line me-1"></i> Edit Item
                        </a>
                        <a href="{{ route('sips.tarif.price.create', $tarifItem->id) }}" class="btn btn-light btn-sm border">
                            <i class="ri-price-tag-3-line me-1"></i> Tambah Harga
                        </a>
                        <a href="{{ route('sips.tarif.index') }}" class="btn btn-light btn-sm">Kembali</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Riwayat Harga</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle mb-0">
                            <thead class="table-light sips-table-head">
                                <tr>
                                    <th>Harga</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Akhir</th>
                                    <th>Alasan</th>
                                    <th>Diperbarui Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tarifItem->riwayatTarif as $riwayat)
                                <tr>
                                    <td class="fw-semibold">Rp {{ number_format($riwayat->harga_per_kg, 0, ',', '.') }}/kg</td>
                                    <td>{{ optional($riwayat->tanggal_mulai)->translatedFormat('d M Y') }}</td>
                                    <td>{{ optional($riwayat->tanggal_akhir)->translatedFormat('d M Y') ?: 'Masih berlaku' }}</td>
                                    <td>{{ $riwayat->alasan_perubahan ?: '-' }}</td>
                                    <td>{{ $riwayat->pengubahUser->name ?? 'System' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat harga.</td>
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
