@extends('partials.layouts.master')

@section('title', 'Edit Warga | SIPS')
@section('title-sub', 'Data Master')
@section('pagetitle', 'Edit Warga')

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
                        <h5 class="card-title mb-1">Edit Data Warga</h5>
                        <p class="text-muted mb-0 fs-13">Perbarui data dasar warga tanpa mengubah riwayat transaksi yang sudah ada.</p>
                    </div>
                    <span class="badge {{ $warga->status_keanggotaan === 'aktif' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                        {{ $warga->status_keanggotaan === 'aktif' ? 'Aktif' : 'Non Aktif' }}
                    </span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sips.warga.update', $warga->id) }}">
                        @csrf
                        @method('PATCH')
                        @include('sips.warga._form', ['warga' => $warga])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
