@extends('partials.layouts.master')

@section('title', 'Tambah Harga Tarif | SIPS')
@section('title-sub', 'Pengaturan')
@section('pagetitle', 'Tambah Riwayat Harga')

@section('content')
<div id="layout-wrapper">
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
        $today = now()->startOfDay();
        $tarifAktif = $tarifItem->riwayatTarif->first(function ($riwayat) use ($today) {
            return $riwayat->tanggal_mulai?->lte($today)
                && (is_null($riwayat->tanggal_akhir) || $riwayat->tanggal_akhir?->gte($today));
        }) ?? $tarifItem->riwayatTarif->first();
    @endphp

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-1">Tambah Harga Baru</h5>
                    <p class="text-muted mb-0 fs-13">Item: <strong>{{ $tarifItem->nama_item }}</strong> | Tipe utama: <strong>{{ ucfirst($tarifItem->tipe_sampah) }}</strong></p>
                </div>
                <div class="card-body">
                    @if($tarifAktif)
                    <div class="alert alert-info">
                        Tarif aktif saat ini: <strong>Rp {{ number_format($tarifAktif->harga_per_kg, 0, ',', '.') }}/kg</strong>
                        sejak {{ optional($tarifAktif->tanggal_mulai)->translatedFormat('d M Y') }}.
                    </div>
                    @endif

                    <form method="POST" action="{{ route('sips.tarif.price.store', $tarifItem->id) }}">
                        @csrf

                        @include('sips.tarif._price_form', [
                            'defaultHarga' => $tarifAktif?->harga_per_kg,
                            'defaultTanggal' => now()->toDateString(),
                            'defaultAlasan' => '',
                        ])

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('sips.tarif.show', $tarifItem->id) }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-price-tag-3-line me-1"></i> Simpan Harga Baru
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
