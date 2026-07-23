@extends('partials.layouts.master')

@section('title', 'Analitik & Laporan | SIPS')
@section('title-sub', 'Analitik')
@section('pagetitle', 'Analitik & Laporan')

@section('css')
<style>
    .chart-container { position: relative; height: 280px; }
    .kpi-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 1.4rem; flex-shrink: 0; }
    @media (max-width: 575.98px) {
        .kpi-icon { width: 34px; height: 34px; border-radius: 8px; font-size: 1rem; }
        .chart-container { height: 220px; }
    }
    @media print {
        .no-print { display: none !important; }
        .card { break-inside: avoid; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="row g-3 mb-4 no-print">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4"
                     style="background: linear-gradient(135deg, rgba(59,130,246,0.08), rgba(16,185,129,0.06));">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle mb-2">
                                Analitik Real-time
                            </span>
                            <h4 class="fw-semibold mb-1">Analitik & Laporan Pemilahan Sampah</h4>
                            <p class="text-muted mb-0 fs-13">
                                Visualisasi data dan laporan kinerja —
                                <strong>{{ $bulanDipilih->translatedFormat('F Y') }}</strong>
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <form method="GET" action="{{ route('sips.analitik.index') }}" class="d-flex gap-2">
                                <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach($availableMonths as $m)
                                    <option value="{{ $m->format('Y-m') }}"
                                        {{ $m->format('Y-m') === $bulanDipilih->format('Y-m') ? 'selected' : '' }}>
                                        {{ $m->translatedFormat('F Y') }}
                                    </option>
                                    @endforeach
                                </select>
                            </form>
                            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
                                <i class="ri-printer-line me-1"></i> Cetak Laporan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-primary-subtle text-primary">
                            <i class="ri-scales-3-line"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-12 mb-1">Total Sampah</div>
                            <div class="fw-bold fs-5">{{ number_format($kpi['total_kg'], 1, ',', '.') }} kg</div>
                            <div class="fs-12 text-muted">{{ $bulanDipilih->translatedFormat('F Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-success-subtle text-success">
                            <i class="ri-recycle-line"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-12 mb-1">Berhasil Dipilah</div>
                            <div class="fw-bold fs-5">{{ number_format($kpi['kg_dipilah'], 1, ',', '.') }} kg</div>
                            <div class="fs-12 text-success">{{ $kpi['persen_dipilah'] }}% dari total</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-warning-subtle text-warning">
                            <i class="ri-group-line"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-12 mb-1">Partisipasi Warga</div>
                            <div class="fw-bold fs-5">{{ $wargaBerkontribusi }} / {{ $wargaAktif }}</div>
                            <div class="fs-12 text-muted">
                                {{ $wargaAktif > 0 ? round(($wargaBerkontribusi / $wargaAktif) * 100) : 0 }}% warga aktif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kpi-icon bg-info-subtle text-info">
                            <i class="ri-money-dollar-circle-line"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-12 mb-1">Pencairan</div>
                            <div class="fw-bold fs-5">Rp {{ number_format($kpi['pencairan'], 0, ',', '.') }}</div>
                            <div class="fs-12 text-muted">{{ $kpi['total_setoran'] }} transaksi setoran</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tren 12 Bulan --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Tren 12 Bulan Terakhir</h5>
                        <small class="text-muted">Berat sampah dan tingkat pemilahan per bulan</small>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                        <i class="ri-line-chart-line me-1"></i> Tren
                    </span>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 320px;">
                        <canvas id="chartTren"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Komposisi + RW Breakdown --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Komposisi Sampah</h5>
                    <small class="text-muted">{{ $bulanDipilih->translatedFormat('F Y') }}</small>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div class="chart-container w-100" style="height: 240px;">
                        <canvas id="chartKomposisi"></canvas>
                    </div>
                    <div class="d-flex flex-wrap gap-4 mt-3 justify-content-center">
                        <div class="text-center">
                            <div class="fw-semibold text-success fs-5">{{ number_format($kgDipilah, 1, ',', '.') }} kg</div>
                            <div class="text-muted fs-12">Dipilah</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-semibold text-danger fs-5">{{ number_format($kgTidakDipilah, 1, ',', '.') }} kg</div>
                            <div class="text-muted fs-12">Tidak Dipilah</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Kontribusi per RW</h5>
                    <small class="text-muted">Berat dipilah vs total — {{ $bulanDipilih->translatedFormat('F Y') }}</small>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 280px;">
                        <canvas id="chartRw"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Tingkat Pemilahan per RW + Payment Donut --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Tingkat Pemilahan per RW</h5>
                    <small class="text-muted">Persentase sampah yang berhasil dipilah</small>
                </div>
                <div class="card-body">
                    @if($rwData->isEmpty())
                    <p class="text-muted text-center py-4">Belum ada data untuk periode ini.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>RW / Dusun</th>
                                    <th class="text-end">Total (kg)</th>
                                    <th class="d-none d-lg-table-cell text-end">Dipilah (kg)</th>
                                    <th>% Pilah</th>
                                    <th class="d-none d-lg-table-cell text-end">Warga</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rwData as $i => $rw)
                                @php
                                    $barColors = ['bg-success', 'bg-primary', 'bg-info', 'bg-warning', 'bg-secondary'];
                                    $barColor  = $barColors[$i % count($barColors)];
                                @endphp
                                <tr>
                                    <td class="fw-medium">{{ $rw->label }}</td>
                                    <td class="text-end">{{ number_format($rw->total_kg, 1, ',', '.') }}</td>
                                    <td class="d-none d-lg-table-cell text-end">{{ number_format($rw->kg_dipilah, 1, ',', '.') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:8px;">
                                                <div class="progress-bar {{ $barColor }}"
                                                     style="width:{{ $rw->persen }}%"></div>
                                            </div>
                                            <span class="fs-12 fw-semibold text-nowrap">{{ $rw->persen }}%</span>
                                        </div>
                                    </td>
                                    <td class="d-none d-lg-table-cell text-end">{{ $rw->jumlah_warga }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Status Pembayaran</h5>
                    <small class="text-muted">{{ $bulanDipilih->translatedFormat('F Y') }}</small>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center gap-3">
                    <div class="chart-container w-100" style="height: 200px;">
                        <canvas id="chartPayment"></canvas>
                    </div>
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="d-flex align-items-center gap-2 text-muted fs-13">
                                <span class="rounded-circle bg-success d-inline-block" style="width:10px;height:10px;"></span>
                                Sudah Dibayar
                            </span>
                            <div class="text-end">
                                <div class="fw-semibold fs-13">{{ $jumlahSudah }} transaksi</div>
                                <div class="text-muted fs-12">Rp {{ number_format($nilaiTerbayar, 0, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center py-2">
                            <span class="d-flex align-items-center gap-2 text-muted fs-13">
                                <span class="rounded-circle bg-danger d-inline-block" style="width:10px;height:10px;"></span>
                                Belum Dibayar
                            </span>
                            <div class="text-end">
                                <div class="fw-semibold fs-13">{{ $jumlahBelum }} transaksi</div>
                                <div class="text-muted fs-12">Rp {{ number_format($nilaiTertunda, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 10 Warga --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Top 10 Warga Berkontribusi</h5>
                        <small class="text-muted">Berdasarkan berat sampah dipilah — {{ $bulanDipilih->translatedFormat('F Y') }}</small>
                    </div>
                    <a href="{{ route('sips.leaderboard') }}?bulan={{ $bulanDipilih->format('Y-m') }}"
                       class="btn btn-outline-primary btn-sm no-print">
                        <i class="ri-trophy-line me-1"></i> Papan Peringkat Lengkap
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($topWarga->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="ri-user-search-line display-4 d-block mb-2"></i>
                        <p>Belum ada data warga untuk periode ini.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">No.</th>
                                    <th>Nama Warga</th>
                                    <th class="d-none d-md-table-cell text-end">Total (kg)</th>
                                    <th class="d-none d-lg-table-cell text-end">Dipilah (kg)</th>
                                    <th>% Pilah</th>
                                    <th class="d-none d-md-table-cell text-end">Setoran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topWarga as $i => $w)
                                @php
                                    $rank = $i + 1;
                                    $medal = match($rank) {
                                        1 => '<i class="ri-medal-line text-warning fs-5" title="Juara 1"></i>',
                                        2 => '<i class="ri-medal-line text-secondary fs-5" title="Juara 2"></i>',
                                        3 => '<i class="ri-medal-line fs-5" style="color:#A0522D;" title="Juara 3"></i>',
                                        default => "<span class=\"text-muted fw-medium\">#$rank</span>",
                                    };
                                @endphp
                                <tr>
                                    <td class="text-center">{!! $medal !!}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $w->nama }}</div>
                                        <small class="text-muted">{{ Str::limit($w->alamat ?? (($w->rt ? 'RT '.$w->rt : '').($w->rw ? ' RW '.$w->rw : '').($w->dusun ? ' / '.$w->dusun : '')), 42) ?: '-' }}</small>
                                    </td>
                                    <td class="d-none d-md-table-cell text-end">{{ number_format($w->total_kg, 1, ',', '.') }}</td>
                                    <td class="d-none d-lg-table-cell text-end">{{ number_format($w->kg_dipilah, 1, ',', '.') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height:6px;">
                                                <div class="progress-bar bg-success" style="width:{{ $w->persen }}%"></div>
                                            </div>
                                            <span class="fs-12">{{ $w->persen }}%</span>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell text-end">{{ $w->jumlah_setoran }}x</td>
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

    {{-- Partisipasi Warga Gauge --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Tingkat Partisipasi Warga</h5>
                    <small class="text-muted">Warga menyetor setidaknya 1x di {{ $bulanDipilih->translatedFormat('F Y') }}</small>
                </div>
                <div class="card-body">
                    @php
                        $pctPartisipasi = $wargaAktif > 0 ? round(($wargaBerkontribusi / $wargaAktif) * 100) : 0;
                        $partisipasiClass = $pctPartisipasi >= 70 ? 'bg-success' : ($pctPartisipasi >= 40 ? 'bg-warning' : 'bg-danger');
                    @endphp
                    <div class="text-center mb-4">
                        <div class="display-4 fw-bold {{ $pctPartisipasi >= 70 ? 'text-success' : ($pctPartisipasi >= 40 ? 'text-warning' : 'text-danger') }}">
                            {{ $pctPartisipasi }}%
                        </div>
                        <p class="text-muted mb-0">{{ $wargaBerkontribusi }} dari {{ $wargaAktif }} warga aktif berkontribusi</p>
                    </div>
                    <div class="progress mb-3" style="height: 16px; border-radius: 8px;">
                        <div class="progress-bar {{ $partisipasiClass }}"
                             style="width: {{ $pctPartisipasi }}%; border-radius: 8px; transition: width 1s ease;">
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-4 text-center">
                            <div class="bg-success-subtle rounded p-2">
                                <div class="fw-bold text-success fs-5">{{ $wargaBerkontribusi }}</div>
                                <div class="text-muted fs-12">Berkontribusi</div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="bg-danger-subtle rounded p-2">
                                <div class="fw-bold text-danger fs-5">{{ $wargaAktif - $wargaBerkontribusi }}</div>
                                <div class="text-muted fs-12">Tidak Menyetor</div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="bg-primary-subtle rounded p-2">
                                <div class="fw-bold text-primary fs-5">{{ $wargaAktif }}</div>
                                <div class="text-muted fs-12">Total Aktif</div>
                            </div>
                        </div>
                    </div>
                    @if($pengangkutanAgregat > 0)
                    <div class="mt-3 p-2 bg-light rounded d-flex align-items-center gap-2 fs-12 text-muted">
                        <i class="ri-truck-line text-warning"></i>
                        <span>
                            + <strong>{{ $pengangkutanAgregat }}</strong> pengangkutan per-area
                            (input agregat, tanpa data warga individual)
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">Tren Persentase Pemilahan</h5>
                    <small class="text-muted">12 bulan terakhir</small>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 220px;">
                        <canvas id="chartPersen"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Ringkasan Cetak --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Ringkasan Laporan</h5>
                    <button onclick="window.print()" class="btn btn-primary btn-sm no-print">
                        <i class="ri-printer-line me-1"></i> Cetak / Simpan PDF
                    </button>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">Periode</td>
                                        <td class="fw-semibold">{{ $bulanDipilih->translatedFormat('F Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Total Sampah Masuk</td>
                                        <td class="fw-semibold">{{ number_format($kpi['total_kg'], 1, ',', '.') }} kg</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Berhasil Dipilah</td>
                                        <td class="fw-semibold text-success">{{ number_format($kpi['kg_dipilah'], 1, ',', '.') }} kg ({{ $kpi['persen_dipilah'] }}%)</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tidak Dipilah</td>
                                        <td class="fw-semibold text-danger">{{ number_format($kgTidakDipilah, 1, ',', '.') }} kg</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted">Total Setoran</td>
                                        <td class="fw-semibold">{{ $kpi['total_setoran'] }} transaksi</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Pencairan Dana</td>
                                        <td class="fw-semibold">Rp {{ number_format($kpi['pencairan'], 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tagihan Terbayar</td>
                                        <td class="fw-semibold text-success">{{ $jumlahSudah }} setoran · Rp {{ number_format($nilaiTerbayar, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tagihan Tertunda</td>
                                        <td class="fw-semibold text-danger">{{ $jumlahBelum }} setoran · Rp {{ number_format($nilaiTertunda, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Warga Aktif</td>
                                        <td class="fw-semibold">{{ $wargaAktif }} orang</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Partisipasi</td>
                                        <td class="fw-semibold">{{ $wargaBerkontribusi }} warga ({{ $pctPartisipasi }}%)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    // ── Shared defaults ───────────────────────────────────────
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.color       = '#6c757d';

    const labels12    = @json($trenLabels);
    const totalKg12   = @json($trenTotalKg);
    const dipilah12   = @json($trenDipilah);
    const pencairan12 = @json($trenPencairan);
    const persen12    = @json($trenPersen);

    // ── 1. Tren 12 bulan (line + bar) ────────────────────────
    try {
        new Chart(document.getElementById('chartTren'), {
            type: 'bar',
            data: {
                labels: labels12,
                datasets: [
                    {
                        type: 'bar',
                        label: 'Total Sampah (kg)',
                        data: totalKg12,
                        backgroundColor: 'rgba(59,130,246,0.15)',
                        borderColor: 'rgba(59,130,246,0.4)',
                        borderWidth: 1,
                        yAxisID: 'yKg',
                        order: 2,
                    },
                    {
                        type: 'bar',
                        label: 'Dipilah (kg)',
                        data: dipilah12,
                        backgroundColor: 'rgba(16,185,129,0.55)',
                        borderColor: 'rgba(16,185,129,0.8)',
                        borderWidth: 1,
                        yAxisID: 'yKg',
                        order: 1,
                    },
                    {
                        type: 'line',
                        label: 'Pencairan (Rp ribu)',
                        data: pencairan12,
                        borderColor: 'rgba(245,158,11,1)',
                        backgroundColor: 'rgba(245,158,11,0.08)',
                        pointBackgroundColor: 'rgba(245,158,11,1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'yRp',
                        order: 0,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const val = ctx.parsed.y;
                                if (ctx.dataset.label.includes('Pencairan'))
                                    return ` Pencairan: Rp ${(val * 1000).toLocaleString('id-ID')}`;
                                return ` ${ctx.dataset.label}: ${val.toLocaleString('id-ID')} kg`;
                            },
                        },
                    },
                },
                scales: {
                    yKg: {
                        position: 'left',
                        title: { display: true, text: 'Berat (kg)' },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                    },
                    yRp: {
                        position: 'right',
                        title: { display: true, text: 'Pencairan (Rp ribu)' },
                        grid: { drawOnChartArea: false },
                    },
                },
            },
        });
    } catch (e) { console.error('chartTren:', e); }

    // ── 2. Komposisi donut ────────────────────────────────────
    const kgDipilah      = @json($kgDipilah);
    const kgTidakDipilah = @json($kgTidakDipilah);
    const isEmpty        = kgDipilah === 0 && kgTidakDipilah === 0;

    try {
        new Chart(document.getElementById('chartKomposisi'), {
            type: 'doughnut',
            data: {
                labels: isEmpty ? ['Belum ada data'] : ['Dipilah', 'Tidak Dipilah'],
                datasets: [{
                    data:            isEmpty ? [1] : [kgDipilah, kgTidakDipilah],
                    backgroundColor: isEmpty ? ['rgba(200,200,200,0.4)'] : ['rgba(16,185,129,0.75)', 'rgba(239,68,68,0.75)'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => isEmpty ? '' : ` ${ctx.label}: ${ctx.parsed.toLocaleString('id-ID', { maximumFractionDigits: 1 })} kg`,
                        },
                    },
                },
            },
        });
    } catch (e) { console.error('chartKomposisi:', e); }

    // ── 3. Per-RW bar chart ───────────────────────────────────
    const rwLabels  = @json($rwData->pluck('label'));
    const rwTotal   = @json($rwData->pluck('total_kg'));
    const rwDipilah = @json($rwData->pluck('kg_dipilah'));

    try {
        new Chart(document.getElementById('chartRw'), {
            type: 'bar',
            data: {
                labels: rwLabels,
                datasets: [
                    {
                        label: 'Total Sampah (kg)',
                        data: rwTotal,
                        backgroundColor: 'rgba(59,130,246,0.2)',
                        borderColor: 'rgba(59,130,246,0.6)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Dipilah (kg)',
                        data: rwDipilah,
                        backgroundColor: 'rgba(16,185,129,0.6)',
                        borderColor: 'rgba(16,185,129,0.9)',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top' } },
                scales: {
                    x: { grid: { display: false } },
                    y: {
                        title: { display: true, text: 'Berat (kg)' },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                    },
                },
            },
        });
    } catch (e) { console.error('chartRw:', e); }

    // ── 4. Payment status donut ───────────────────────────────
    const sudah = @json($jumlahSudah);
    const belum = @json($jumlahBelum);

    try {
        new Chart(document.getElementById('chartPayment'), {
            type: 'doughnut',
            data: {
                labels: ['Sudah Dibayar', 'Belum Dibayar'],
                datasets: [{
                    data: [sudah, belum],
                    backgroundColor: ['rgba(16,185,129,0.75)', 'rgba(239,68,68,0.75)'],
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.label}: ${ctx.parsed} transaksi`,
                        },
                    },
                },
            },
        });
    } catch (e) { console.error('chartPayment:', e); }

    // ── 5. Tren persen pemilahan (sparkline-style line) ───────
    try {
        new Chart(document.getElementById('chartPersen'), {
            type: 'line',
            data: {
                labels: labels12,
                datasets: [{
                    label: '% Pemilahan',
                    data: persen12,
                    borderColor: 'rgba(16,185,129,0.9)',
                    backgroundColor: 'rgba(16,185,129,0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: 'rgba(16,185,129,1)',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ` ${ctx.parsed.y}% dipilah`,
                        },
                    },
                },
                scales: {
                    y: {
                        min: 0,
                        max: 100,
                        title: { display: true, text: '%' },
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { callback: (v) => v + '%' },
                    },
                    x: { grid: { display: false } },
                },
            },
        });
    } catch (e) { console.error('chartPersen:', e); }

})();
</script>
@endsection
