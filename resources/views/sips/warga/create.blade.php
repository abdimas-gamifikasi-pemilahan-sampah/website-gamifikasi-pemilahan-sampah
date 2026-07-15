@extends('partials.layouts.master')

@section('title', 'Tambah Warga | SIPS')
@section('title-sub', 'Data Master')
@section('pagetitle', 'Tambah Warga')

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
                    <h5 class="card-title mb-1">Form Warga Baru</h5>
                    <p class="text-muted mb-0 fs-13">Tambahkan warga agar bisa dipilih saat input setoran oleh petugas.</p>
                </div>
                @if(!empty($prefill['nama']))
                <div class="alert alert-info border-0 rounded-0 mb-0 fs-13 py-2 px-4">
                    <i class="ri-user-add-line me-1"></i>
                    Data dari setoran sudah diisi: <strong>{{ $prefill['nama'] }}</strong>
                    @if(!empty($prefill['rw'])) · RW {{ $prefill['rw'] }} @endif
                    @if(!empty($prefill['rt'])) · RT {{ $prefill['rt'] }} @endif.
                    Lengkapi <strong>Dusun</strong>, lalu klik <strong>Simpan Warga</strong>.
                </div>
                @endif
                <div class="card-body">
                    <form method="POST" action="{{ route('sips.warga.store') }}">
                        @csrf
                        @include('sips.warga._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
