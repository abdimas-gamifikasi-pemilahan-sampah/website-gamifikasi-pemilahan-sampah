@extends('partials.layouts.master')

@section('title', 'Laporan Pembayaran | SIPS')
@section('title-sub', 'Pembayaran')
@section('pagetitle', 'Laporan Pembayaran')

@section('content')
<div id="layout-wrapper">

    <div class="row">
        <div class="col-12">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Riwayat Pembayaran</h5>
                    <a href="{{ route('sips.setoran.index') }}" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Ke Daftar Setoran
                    </a>
                </div>
                <div class="card-body">

                    {{-- Filter --}}
                    <form method="GET" action="{{ route('sips.pembayaran.index') }}"
                          class="row g-2 align-items-end mb-4">
                        <div class="col-sm-auto">
                            <label class="form-label form-label-sm mb-1">Dari Tanggal</label>
                            <input type="date" class="form-control form-control-sm"
                                   name="dari" value="{{ request('dari') }}">
                        </div>
                        <div class="col-sm-auto">
                            <label class="form-label form-label-sm mb-1">Sampai Tanggal</label>
                            <input type="date" class="form-control form-control-sm"
                                   name="sampai" value="{{ request('sampai') }}">
                        </div>
                        <div class="col-sm-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                            @if(request('dari') || request('sampai'))
                            <a href="{{ route('sips.pembayaran.index') }}" class="btn btn-light btn-sm">Reset</a>
                            @endif
                        </div>
                    </form>

                    {{-- Summary card --}}
                    @if(request('dari') || request('sampai'))
                    <div class="alert alert-primary d-flex align-items-center gap-3 mb-4 py-3">
                        <i class="ri-bar-chart-line fs-3"></i>
                        <div>
                            <div class="fw-semibold">Total Pembayaran Periode Ini</div>
                            <div class="fs-4 fw-bold">Rp {{ number_format($totalPeriode, 0, ',', '.') }}</div>
                        </div>
                    </div>
                    @endif

                    @if($pembayaran->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="ri-wallet-line display-4 d-block mb-2"></i>
                        <p>Belum ada data pembayaran{{ request('dari') ? ' pada periode ini' : '' }}.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tgl. Bayar</th>
                                    <th>Warga</th>
                                    <th>Setoran</th>
                                    <th class="text-end">Nilai Setoran (Rp)</th>
                                    <th class="text-end">Dibayar (Rp)</th>
                                    <th>Petugas Bayar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pembayaran as $p)
                                <tr>
                                    <td>
                                        <div>{{ $p->tanggal_bayar->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $p->tanggal_bayar->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $p->setoran->warga->nama }}</div>
                                        <small class="text-muted">
                                            RT {{ $p->setoran->warga->rt }} / {{ $p->setoran->warga->dusun }}
                                        </small>
                                    </td>
                                    <td class="fw-medium">
                                        #{{ str_pad($p->setoran_id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="text-end">
                                        Rp {{ number_format($p->setoran->total_nilai, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end fw-semibold">
                                        Rp {{ number_format($p->jumlah_dibayar, 0, ',', '.') }}
                                        @if($p->jumlah_dibayar != $p->setoran->total_nilai)
                                            <br><small class="text-warning">*disesuaikan</small>
                                        @endif
                                    </td>
                                    <td>{{ $p->petugasPembayar->name ?? '-' }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('sips.setoran.show', $p->setoran_id) }}"
                                               class="btn btn-sm btn-light border" title="Detail Setoran">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('sips.setoran.kwitansi', $p->setoran_id) }}"
                                               class="btn btn-sm btn-light border" title="Cetak Kwitansi"
                                               target="_blank">
                                                <i class="ri-printer-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $pembayaran->links() }}
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
