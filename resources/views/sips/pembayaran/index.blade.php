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
                    @php
                        $hasPembayaranFilter = request('dari') || request('sampai') || request('aktor');
                        $ringkasanPembayaran = [
                            [
                                'label' => 'Pemasukan',
                                'value' => 'Rp ' . number_format((float) ($ringkasan->total_warga ?? 0), 0, ',', '.'),
                                'class' => 'success',
                                'icon' => 'ri-arrow-down-circle-line',
                            ],
                            [
                                'label' => 'Pengeluaran',
                                'value' => 'Rp ' . number_format((float) ($ringkasan->total_petugas ?? 0), 0, ',', '.'),
                                'class' => 'danger',
                                'icon' => 'ri-arrow-up-circle-line',
                            ],
                        ];
                    @endphp

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
                        <div class="col-sm-auto">
                            <label class="form-label form-label-sm mb-1">Dibayar Oleh</label>
                            <select class="form-select form-select-sm" name="aktor">
                                <option value="">Semua</option>
                                <option value="warga" {{ request('aktor') === 'warga' ? 'selected' : '' }}>Warga</option>
                                <option value="petugas" {{ request('aktor') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                            </select>
                        </div>
                        <div class="col-sm-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                            @if(request('dari') || request('sampai') || request('aktor'))
                            <a href="{{ route('sips.pembayaran.index') }}" class="btn btn-light btn-sm">Reset</a>
                            @endif
                        </div>
                    </form>

                    {{-- Summary card --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="mb-1">Ringkasan Pembayaran</h6>
                                <p class="text-muted mb-0 fs-13">
                                    {{ $hasPembayaranFilter ? 'Menampilkan hasil filter pembayaran.' : 'Menampilkan seluruh pembayaran yang tercatat.' }}
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            @foreach($ringkasanPembayaran as $item)
                            <div class="col-md-6">
                                <div class="card border border-{{ $item['class'] }} shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fs-13 text-muted mb-1">{{ $item['label'] }}</div>
                                                <h4 class="mb-0">{{ $item['value'] }}</h4>
                                            </div>
                                            <div class="avatar-sm">
                                                <span class="avatar-title bg-{{ $item['class'] }}-subtle text-{{ $item['class'] }} rounded-circle fs-4">
                                                    <i class="{{ $item['icon'] }}"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

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
                                    <th class="text-end">Aliran Nilai</th>
                                    <th class="text-end">Dibayar (Rp)</th>
                                    <th>Status</th>
                                    <th>Dibayar Oleh</th>
                                    <th>Dicatat Oleh</th>
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
                                        <span class="{{ $p->setoran->signedAmountTextClasses() }}">
                                            {{ $p->setoran->nilaiSignedFormatted() }}
                                        </span>
                                        <div class="small text-muted">{{ $p->setoran->nilaiFormatted() }}</div>
                                    </td>
                                    <td class="text-end fw-semibold">
                                        Rp {{ number_format($p->jumlah_dibayar, 0, ',', '.') }}
                                        @if($p->jumlah_dibayar != $p->setoran->total_nilai)
                                            <br><small class="text-warning">*disesuaikan</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $p->setoran->paymentStatusBadgeClasses() }}">
                                            {{ $p->setoran->paymentStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $p->setoran->paymentActorLabel() }}</td>
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
