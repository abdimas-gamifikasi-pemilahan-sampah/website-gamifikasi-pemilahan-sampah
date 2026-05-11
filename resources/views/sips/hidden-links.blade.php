@extends('partials.layouts.master')

@section('title', 'Tautan Template Tersembunyi | SIPS')

@section('title-sub', 'Sistem Informasi Pemilahan Sampah')
@section('pagetitle', 'Tautan Template Tersembunyi')

@section('content')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card card-h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Halaman Template Disembunyikan dari Sidebar</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Halaman di bawah ini tidak tampil di navbar utama supaya fokus pengembangan Anda tetap di modul SIPS 1-3.
                        Link masih aktif untuk testing atau referensi UI.
                    </p>

                    @if(empty($hiddenPages))
                        <div class="alert alert-warning mb-0" role="alert">
                            Tidak ada halaman template tersembunyi yang terdeteksi.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col">Nama Halaman</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hiddenPages as $page)
                                        <tr>
                                            <td>{{ $page['name'] }}</td>
                                            <td>
                                                <a href="{{ $page['url'] }}" class="btn btn-light-primary border-primary btn-sm" target="_blank" rel="noopener noreferrer">
                                                    Buka Halaman
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
