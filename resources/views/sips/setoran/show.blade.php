@extends('partials.layouts.master')

@section('title', 'Detail Setoran #' . str_pad($setoran->id, 5, '0', STR_PAD_LEFT) . ' | SIPS')
@section('title-sub', 'Setoran Sampah')
@section('pagetitle', 'Detail Setoran')

@section('content')
<div id="layout-wrapper">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('sips.setoran.index') }}" class="btn btn-light btn-sm">
            <i class="ri-arrow-left-line me-1"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row">

        {{-- Left: item detail --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        Setoran #{{ str_pad($setoran->id, 5, '0', STR_PAD_LEFT) }}
                    </h5>
                    <span class="badge {{ $setoran->paymentStatusBadgeClasses() }} fs-12">
                        {{ $setoran->paymentStatusLabel() }}
                    </span>
                </div>
                <div class="card-body">

                    {{-- Info setoran --}}
                    <div class="row mb-4 g-3">
                        <div class="col-sm-6">
                            @if($setoran->warga)
                                <p class="text-muted mb-1 fs-12">WARGA</p>
                                <p class="fw-semibold mb-0">{{ $setoran->warga->nama }}</p>
                                <small class="text-muted">
                                    @if($setoran->warga->alamat){{ $setoran->warga->alamat }}
                                    @elseif($setoran->warga->rt || $setoran->warga->dusun)RT {{ $setoran->warga->rt ?? '-' }}{{ $setoran->warga->dusun ? ' / ' . $setoran->warga->dusun : '' }}
                                    @else-@endif
                                </small>
                            @else
                                <p class="text-muted mb-1 fs-12">PENYETOR</p>
                                @php
                                    $namaPenyetor = $setoran->items->pluck('nama_penyetor')->filter()->unique()->values();
                                @endphp
                                <p class="fw-semibold mb-0">
                                    {{ $namaPenyetor->take(3)->implode(', ') }}{{ $namaPenyetor->count() > 3 ? ', ...' : '' }}
                                </p>
                                <small class="text-muted">{{ $setoran->items->count() }} item · area RW {{ $setoran->area_rw ?? '—' }}</small>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted mb-1 fs-12">DICATAT OLEH</p>
                            <p class="fw-semibold mb-0">{{ $setoran->petugas->name ?? '-' }}</p>
                            <small class="text-muted">{{ $setoran->tanggal_setoran->format('d M Y, H:i') }}</small>
                        </div>
                        @if($setoran->catatan_kondisi)
                        <div class="col-12">
                            <p class="text-muted mb-1 fs-12">CATATAN KONDISI</p>
                            <p class="mb-0">{{ $setoran->catatan_kondisi }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- Item table --}}
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Status Pilah</th>
                                    <th>Kategori</th>
                                    <th class="d-none d-md-table-cell">Tipe</th>
                                    <th class="text-end">Berat</th>
                                    <th class="d-none d-md-table-cell text-end">Harga/kg</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($setoran->items as $item)
                                <tr>
                                    <td>
                                        @if($item->status_pemilahan === 'dipilah')
                                            <span class="badge bg-success-subtle text-success">Dipilah</span>
                                        @elseif($item->status_pemilahan === 'tidak_dipilah')
                                            <span class="badge bg-warning-subtle text-warning">Tidak Dipilah</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Tidak Dicatat</span>
                                        @endif
                                    </td>
                                    <td class="fw-medium">
                                        {{ $item->tarifItem?->nama_item ?? '—' }}
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        @if($item->tipe_sampah === 'organik')
                                            <span class="badge bg-success-subtle text-success">Organik</span>
                                        @elseif($item->tipe_sampah === 'anorganik')
                                            <span class="badge bg-primary-subtle text-primary">Anorganik</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($item->berat_kg, 1, ',', '.') }} kg</td>
                                    <td class="d-none d-md-table-cell text-end">
                                        @if($item->harga_per_kg_saat_itu !== null)
                                            Rp {{ number_format($item->harga_per_kg_saat_itu, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="5" class="text-end fw-bold">Total</td>
                                    <td class="text-end fw-bold fs-15">
                                        <span class="{{ $setoran->signedAmountTextClasses() }}">
                                            {{ $setoran->nilaiSignedFormatted() }}
                                        </span>
                                        <div class="small text-muted fw-normal">{{ $setoran->nilaiFormatted() }}</div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        {{-- Right: payment status --}}
        <div class="col-lg-4">
            @if($setoran->status_pembayaran === 'sudah_dibayar' && $setoran->pembayaran && $setoran->hasPaymentFlow())
            <div class="card {{ $setoran->paymentStatusCardClasses() }}">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="{{ $setoran->paymentStatusIconClasses() }} fs-1 me-2"></i>
                        <div>
                            <h6 class="fw-bold mb-0">{{ $setoran->paymentStatusLabel() }}</h6>
                            <small class="text-muted">
                                {{ $setoran->pembayaran->tanggal_bayar->format('d M Y, H:i') }}
                            </small>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td class="text-muted ps-0">Jumlah Dibayar</td>
                                <td class="fw-semibold text-end pe-0">
                                    Rp {{ number_format($setoran->pembayaran->jumlah_dibayar, 0, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Dibayar Oleh</td>
                                <td class="text-end pe-0">{{ $setoran->paymentActorLabel() }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0">Dicatat Oleh Petugas</td>
                                <td class="text-end pe-0">
                                    {{ $setoran->pembayaran->petugasPembayar->name ?? '-' }}
                                </td>
                            </tr>
                            @if($setoran->pembayaran->catatan)
                            <tr>
                                <td class="text-muted ps-0">Catatan</td>
                                <td class="text-end pe-0">{{ $setoran->pembayaran->catatan }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="card {{ $setoran->paymentStatusCardClasses() }}">
                <div class="card-body text-center py-4">
                    <i class="{{ $setoran->paymentStatusIconClasses() }} display-5 d-block mb-2"></i>
                    <h6 class="fw-semibold mb-1">{{ $setoran->paymentStatusLabel() }}</h6>
                    <p class="text-muted fs-13 mb-3">
                        @if($setoran->hasPaymentFlow())
                            Menunggu {{ $setoran->isPaymentByWarga() ? 'pemasukan' : 'pengeluaran' }}:
                            <strong class="{{ $setoran->signedAmountTextClasses() }}">{{ $setoran->nilaiSignedFormatted() }}</strong>
                        @else
                            Tidak ada aliran uang pada setoran ini.
                        @endif
                    </p>
                    @if($setoran->hasPaymentFlow())
                    <form method="POST" action="{{ route('sips.pembayaran.store', $setoran->id) }}">
                        @csrf
                        <input type="hidden" name="jumlah_dibayar" value="{{ $setoran->total_nilai }}">
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="ri-checkbox-circle-line me-1"></i> Konfirmasi Sudah Dibayar
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endif

            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Ringkasan</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Item</span>
                        <span>{{ $setoran->items->count() }} item</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Berat</span>
                        <span>{{ number_format($setoran->items->sum('berat_kg'), 1, ',', '.') }} kg</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Arus Kas Sistem</span>
                        <span class="{{ $setoran->signedAmountTextClasses() }}">
                            {{ $setoran->nilaiSignedFormatted() }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Dipilah</span>
                        <span class="text-success">
                            {{ $setoran->items->where('status_pemilahan', 'dipilah')->count() }} item
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Tidak Dipilah</span>
                        <span class="text-warning">
                            {{ $setoran->items->where('status_pemilahan', 'tidak_dipilah')->count() }} item
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
