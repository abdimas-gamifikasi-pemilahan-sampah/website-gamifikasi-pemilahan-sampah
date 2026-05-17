@extends('partials.layouts.master')

@section('title', 'Dasbor Analitik SIPS')
@section('title-sub', 'Sistem Informasi Pemilahan Sampah')
@section('pagetitle', 'Dasbor Analitik Desa Banjarsari')

@section('css')
<style>
    .sips-soft-card {
        background: linear-gradient(135deg, #f8fafc 0%, #eef6ff 100%);
        border: 1px solid #e7eef9;
    }

    .sips-kpi-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .sips-stat-dot {
        width: 10px;
        height: 10px;
        border-radius: 999px;
        display: inline-block;
    }
</style>
@endsection

@section('content')
<div id="layout-wrapper" class="sips-page-shell">
    <div class="row g-4 mb-1">
        <div class="col-12">
            <div class="card sips-soft-card card-h-100">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h4 class="mb-1">Selamat datang kembali, Tim SIPS Desa Banjarsari</h4>
                        <p class="text-muted mb-0">Berikut ringkasan performa pemilahan sampah terbaru untuk Desa Banjarsari.</p>
                    </div>
                    <div class="text-md-end">
                        <p class="mb-1 text-muted">Periode Laporan</p>
                        <h6 class="mb-0">{{ $periodeBulan }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-1">
        <div class="col-xxl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">Total Sampah Terkumpul</p>
                    <div class="sips-kpi-value">{{ number_format($kpi['total_kg'], 1, ',', '.') }} kg</div>
                    @if($totalKgDelta !== null)
                    <div class="mt-2 fs-13 {{ $totalKgDelta >= 0 ? 'text-success' : 'text-danger' }}">
                        <i class="ri-arrow-{{ $totalKgDelta >= 0 ? 'up' : 'down' }}-line"></i>
                        {{ $totalKgDelta >= 0 ? '+' : '' }}{{ $totalKgDelta }}% dibanding bulan lalu
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">Tingkat Pemilahan</p>
                    <div class="sips-kpi-value">{{ $kpi['persen_dipilah'] }}%</div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: {{ $kpi['persen_dipilah'] }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">Total Pencairan</p>
                    <div class="sips-kpi-value">Rp {{ number_format($kpi['pencairan'], 0, ',', '.') }}</div>
                    <div class="mt-2 text-muted fs-13">Dari {{ $kpi['total_setoran'] }} transaksi setoran</div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">Warga Aktif</p>
                    <div class="sips-kpi-value">{{ $wargaAktif }}</div>
                    <div class="mt-2 text-muted fs-13">Terdaftar dalam sistem</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-1">
        <div class="col-xl-7">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Komposisi Sampah Masuk</h5>
                    <span class="badge border border-primary text-primary">Bulanan</span>
                </div>
                <div class="card-body">
                    @php
                        $pctOrganik      = $totalKgAll > 0 ? round(($kgOrganik / $totalKgAll) * 100) : 0;
                        $pctAnorganik    = $totalKgAll > 0 ? round(($kgAnorganik / $totalKgAll) * 100) : 0;
                        $pctTidakDipilah = $totalKgAll > 0 ? round(($kgTidakDipilah / $totalKgAll) * 100) : 0;
                        $pctTidakDicatat = $totalKgAll > 0 ? round(($kgTidakDicatat / $totalKgAll) * 100) : 0;
                    @endphp
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span><span class="sips-stat-dot bg-success me-2"></span>Organik</span>
                            <strong>{{ number_format($kgOrganik, 1, ',', '.') }} kg</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: {{ $pctOrganik }}%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span><span class="sips-stat-dot bg-info me-2"></span>Anorganik</span>
                            <strong>{{ number_format($kgAnorganik, 1, ',', '.') }} kg</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: {{ $pctAnorganik }}%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span><span class="sips-stat-dot bg-warning me-2"></span>Tidak Dipilah</span>
                            <strong>{{ number_format($kgTidakDipilah, 1, ',', '.') }} kg</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" style="width: {{ $pctTidakDipilah }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><span class="sips-stat-dot bg-secondary me-2"></span>Status Tidak Dicatat</span>
                            <strong>{{ number_format($kgTidakDicatat, 1, ',', '.') }} kg</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-secondary" style="width: {{ max($pctTidakDicatat, 1) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card card-h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Peringkat RW (5 Teratas)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Peringkat</th>
                                    <th>RW</th>
                                    <th>Tingkat Pemilahan</th>
                                    <th>Total (kg)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rwRanking as $i => $rw)
                                <tr>
                                    <td>
                                        @php
                                            $badgeClass = match($i) {
                                                0 => 'bg-success-subtle text-success',
                                                1 => 'bg-primary-subtle text-primary',
                                                2 => 'bg-info-subtle text-info',
                                                3 => 'bg-warning-subtle text-warning',
                                                default => 'bg-danger-subtle text-danger',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">#{{ $i + 1 }}</span>
                                    </td>
                                    <td>RW {{ $rw->rw }}</td>
                                    <td>{{ $rw->persen_dipilah }}%</td>
                                    <td>{{ number_format($rw->total_kg, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Belum ada data bulan ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Ringkasan Tren Bulanan</h5>
                    <span class="text-muted fs-13">5 Bulan Terakhir</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Bulan</th>
                                    <th>Total Sampah (kg)</th>
                                    <th>Dipilah (%)</th>
                                    <th>Pencairan (Rp)</th>
                                    <th>Warga Aktif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tren as $t)
                                <tr>
                                    <td>{{ $t['bulan'] }}</td>
                                    <td>{{ number_format($t['total_kg'], 1, ',', '.') }}</td>
                                    <td>{{ $t['persen_dipilah'] }}%</td>
                                    <td>{{ number_format($t['pencairan'], 0, ',', '.') }}</td>
                                    <td>{{ $t['warga_aktif'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Setoran Sudah Dibayar</span>
                        <span class="badge border border-success text-success">
                            {{ $statusBayar['sudah_dibayar'] ?? 0 }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Setoran Belum Dibayar</span>
                        <span class="badge border border-danger text-danger">
                            {{ $statusBayar['belum_dibayar'] ?? 0 }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Nilai Tertunda</span>
                        <strong>Rp {{ number_format($nilaiTertunda, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <div class="card sips-soft-card">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('sips.setoran.create') }}" class="btn btn-primary w-100 mb-2">+ Catat Setoran Baru</a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('sips.warga.index') }}" class="btn btn-light w-100 mb-2">Kelola Data Warga</a>
                    <a href="{{ route('sips.tarif.index') }}" class="btn btn-light w-100 mb-2">Perbarui Tarif</a>
                    <a href="{{ route('sips.import.index') }}" class="btn btn-light w-100">Import Data Warga</a>
                    @else
                    <a href="{{ route('sips.setoran.index') }}" class="btn btn-light w-100 mb-2">Lihat Riwayat Setoran</a>
                    <a href="{{ route('sips.pembayaran.index') }}" class="btn btn-light w-100">Lihat Pembayaran</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
