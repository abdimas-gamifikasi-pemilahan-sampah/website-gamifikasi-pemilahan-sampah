@extends('partials.layouts.master')

@section('title', 'Tambah Item Tarif | SIPS')
@section('title-sub', 'Pengaturan')
@section('pagetitle', 'Tambah Item Tarif')

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

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-1">Form Item Tarif Baru</h5>
                    <p class="text-muted mb-0 fs-13">Tambahkan item sampah baru sekaligus harga awal agar langsung siap dipakai pada modul setoran.</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sips.tarif.store') }}">
                        @csrf

                        @include('sips.tarif._form')

                        <hr class="my-4">

                        <h6 class="mb-3">Harga Awal</h6>
                        @include('sips.tarif._price_form', [
                            'hargaField' => 'harga_per_kg_awal',
                            'tanggalField' => 'tanggal_mulai_awal',
                            'alasanField' => 'alasan_perubahan_awal',
                            'defaultHarga' => '',
                            'defaultTanggal' => now()->toDateString(),
                            'defaultAlasan' => 'Tarif awal item.',
                        ])

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('sips.tarif.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Simpan Item Tarif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
