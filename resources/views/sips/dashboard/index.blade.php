@extends('partials.layouts.master')

@section('title', 'Dasbor Analitik SIPS')
@section('title-sub', 'Sistem Informasi Pemilahan Sampah')
@section('pagetitle', 'Dasbor Analitik Desa Banjarsari')

@section('css')
<style>
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

    .badge-bronze {
        background-color: rgba(205,127,50,0.15);
        color: #A0522D;
        border: 1px solid rgba(205,127,50,0.3);
    }
</style>
@endsection

@section('content')
<div id="layout-wrapper" class="sips-page-shell">

    {{-- Welcome banner --}}
    <div class="row g-4 mb-1">
        <div class="col-12">
            <div class="card sips-soft-card">
                <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3"
                     style="background: linear-gradient(135deg, rgba(255,126,61,0.08), rgba(59,130,246,0.05)); border-radius: inherit;">
                    <div>
                        <h4 class="mb-1">Selamat datang kembali, Tim SIPS Desa Banjarsari</h4>
                        <p class="text-muted mb-0">Berikut ringkasan performa pemilahan sampah terbaru untuk Desa Banjarsari.</p>
                    </div>
                    <div class="text-md-end">
                        <p class="mb-1 text-muted fs-13">Periode Laporan</p>
                        <h6 class="mb-0 fw-semibold">{{ $periodeBulan }}</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-4 mb-1">
        <div class="col-xxl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted mb-0 fs-13">Total Sampah Terkumpul</p>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Bulan ini</span>
                    </div>
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
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted mb-0 fs-13">Tingkat Pemilahan</p>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Dipilah</span>
                    </div>
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
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted mb-0 fs-13">Total Pencairan</p>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $kpi['total_setoran'] }} setoran</span>
                    </div>
                    <div class="sips-kpi-value">Rp {{ number_format($kpi['pencairan'], 0, ',', '.') }}</div>
                    <div class="mt-2 text-muted fs-13">Dari {{ $kpi['total_setoran'] }} transaksi setoran</div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <p class="text-muted mb-0 fs-13">Warga Aktif</p>
                        <span class="badge bg-info-subtle text-info border border-info-subtle">Terdaftar</span>
                    </div>
                    <div class="sips-kpi-value">{{ $wargaAktif }}</div>
                    <div class="mt-2 text-muted fs-13">
                        {{ $topWarga->count() }} berkontribusi bulan ini
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Komposisi + Peringkat RW --}}
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

        {{-- Peringkat RW: progress bar style (consistent with leaderboard) --}}
        <div class="col-xl-5">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Peringkat RW (5 Teratas)</h5>
                    <a href="{{ route('sips.leaderboard') }}" class="btn btn-outline-secondary btn-sm fs-12">
                        Lihat Semua <i class="ri-arrow-right-line ms-1"></i>
                    </a>
                </div>
                <div class="card-body">
                    @php
                    $rwColors  = ['bg-success', 'bg-primary', 'bg-info', 'bg-warning', 'bg-secondary'];
                    $barColors = ['bg-success', 'bg-primary', 'bg-info', 'bg-warning', 'bg-secondary'];
                    @endphp
                    @forelse($rwRanking->take(5) as $i => $rw)
                    <div class="{{ !$loop->last ? 'mb-4' : '' }}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <strong class="fs-13">{{ $rw->rw_display }}</strong>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $rw->persen_dipilah }}% dipilah
                                    · {{ number_format($rw->total_kg, 0, ',', '.') }} kg total
                                </div>
                            </div>
                            <span class="badge {{ $rwColors[$i] ?? 'bg-secondary' }}">#{{ $i + 1 }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $barColors[$i] ?? 'bg-secondary' }}"
                                 style="width: {{ $rw->persen_dipilah }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">Belum ada data periode ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Top Warga + Poin Formula --}}
    <div class="row g-4 mb-1">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Top Warga Bulan Ini</h5>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                            {{ $topWarga->count() }} warga berkontribusi
                        </span>
                        <a href="{{ route('sips.leaderboard') }}" class="btn btn-outline-secondary btn-sm fs-12">
                            Papan Peringkat <i class="ri-arrow-right-line ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @forelse($topWarga as $i => $entry)
                    @php
                        $rank = $i + 1;
                        $badgeClass = match(true) {
                            $rank === 1 => 'bg-warning-subtle text-warning',
                            $rank === 2 => 'bg-secondary-subtle text-secondary',
                            $rank === 3 => 'badge-bronze',
                            default     => 'bg-primary-subtle text-primary',
                        };
                    @endphp
                    <div class="d-flex align-items-center px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="me-3" style="width: 36px; text-align: center; flex-shrink: 0;">
                            <span class="badge rounded-pill {{ $badgeClass }}">#{{ $rank }}</span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium">{{ $entry->nama }}</div>
                            <div class="text-muted fs-12">
                                RW {{ $entry->rw }} / {{ $entry->dusun }}
                                &nbsp;·&nbsp;{{ number_format($entry->kg_dipilah, 1, ',', '.') }} kg dipilah
                                &nbsp;·&nbsp;{{ $entry->persen_dipilah }}% pilah
                            </div>
                        </div>
                        <div class="text-end ms-3">
                            <span class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle fw-semibold px-3 py-2">
                                {{ (int) $entry->poin }} poin
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-5 text-center text-muted">
                        <i class="ri-trophy-line fs-2 d-block mb-2 text-muted opacity-50"></i>
                        Belum ada data setoran warga bulan ini.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Poin Formula (consistent with leaderboard admin sidebar) --}}
        <div class="col-xl-4">
            <div class="card border-primary-subtle">
                <div class="card-header bg-primary-subtle border-primary-subtle">
                    <h5 class="card-title mb-0 text-primary fs-13">
                        <i class="ri-calculator-line me-1"></i> Cara Perhitungan Poin
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3 p-3 rounded" style="background: rgba(13,110,253,0.06);">
                        <div class="fw-bold fs-14" style="font-family: monospace; letter-spacing: 0.02em;">
                            Poin = (kg dipilah × 10) + (% pilah × 2)
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-2 fs-13">
                        <span class="text-muted">Bobot berat dipilah</span>
                        <span class="fw-semibold">× 10 / kg</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 fs-13">
                        <span class="text-muted">Bobot persentase pilah</span>
                        <span class="fw-semibold">× 2 / %</span>
                    </div>
                    <div class="border-top pt-3">
                        <p class="text-muted fs-12 mb-1">Contoh:</p>
                        <p class="fs-12 mb-0">
                            10 kg dipilah, 80% pilah<br>
                            = (10 × 10) + (80 × 2) = <strong class="text-primary">260 poin</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tren Bulanan + Status Pembayaran + Aksi Cepat --}}
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
                            <thead class="table-light">
                                <tr>
                                    <th>Bulan</th>
                                    <th class="text-end">Total Sampah (kg)</th>
                                    <th class="text-end">Dipilah (%)</th>
                                    <th class="text-end">Pencairan (Rp)</th>
                                    <th class="text-end">Warga Aktif</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tren as $t)
                                <tr>
                                    <td class="fw-medium">{{ $t['bulan'] }}</td>
                                    <td class="text-end">{{ number_format($t['total_kg'], 1, ',', '.') }}</td>
                                    <td class="text-end">
                                        <span class="{{ $t['persen_dipilah'] >= 50 ? 'text-success' : 'text-warning' }} fw-medium">
                                            {{ $t['persen_dipilah'] }}%
                                        </span>
                                    </td>
                                    <td class="text-end">{{ number_format($t['pencairan'], 0, ',', '.') }}</td>
                                    <td class="text-end">{{ $t['warga_aktif'] }}</td>
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
                        <span class="fs-13">Setoran Sudah Dibayar</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fs-12 px-3">
                            {{ $statusBayar['sudah_dibayar'] ?? 0 }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fs-13">Setoran Belum Dibayar</span>
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-12 px-3">
                            {{ $statusBayar['belum_dibayar'] ?? 0 }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <span class="fs-13 fw-medium">Nilai Tertunda</span>
                        <strong class="{{ ($statusBayar['belum_dibayar'] ?? 0) > 0 ? 'text-danger' : 'text-muted' }}">
                            Rp {{ number_format($nilaiTertunda, 0, ',', '.') }}
                        </strong>
                    </div>
                </div>
            </div>

            <div class="card sips-soft-card">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('sips.setoran.create') }}" class="btn btn-primary w-100 mb-2">
                        <i class="ri-add-line me-1"></i> Catat Setoran Baru
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('sips.warga.index') }}" class="btn btn-light w-100 mb-2">Kelola Data Warga</a>
                    <a href="{{ route('sips.tarif.index') }}" class="btn btn-light w-100 mb-2">Perbarui Tarif</a>
                    <a href="{{ route('sips.import.index') }}" class="btn btn-light w-100 mb-2">Import Data Warga</a>
                    <a href="{{ route('sips.ekspor.index') }}" class="btn btn-outline-success w-100">
                        <i class="ri-file-excel-2-line me-1"></i> Ekspor Laporan Excel
                    </a>
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
