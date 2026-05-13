@extends('partials.layouts.master')

@section('title', 'Edit Item Tarif | SIPS')
@section('title-sub', 'Pengaturan')
@section('pagetitle', 'Edit Item Tarif')

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
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1">Edit Metadata Item Tarif</h5>
                        <p class="text-muted mb-0 fs-13">Perbarui nama item, tipe sampah, atau status arsip tanpa mengubah histori harga yang sudah tercatat.</p>
                    </div>
                    <a href="{{ route('sips.tarif.show', $tarifItem->id) }}" class="btn btn-light btn-sm">Kembali ke Detail</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sips.tarif.update', $tarifItem->id) }}">
                        @csrf
                        @method('PATCH')

                        @include('sips.tarif._form', ['tarifItem' => $tarifItem])

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('sips.tarif.show', $tarifItem->id) }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
