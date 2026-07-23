@extends('partials.layouts.master')

@section('title', 'Papan Peringkat | SIPS')
@section('title-sub', 'Ranking Warga')
@section('pagetitle', 'Papan Peringkat')

@section('css')
<style>
    .badge-bronze {
        background-color: rgba(205,127,50,0.15);
        color: #A0522D;
        border: 1px solid rgba(205,127,50,0.3);
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 sips-page-shell">

    {{-- Header --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card overflow-hidden">
                <div class="card-body p-4" style="background: linear-gradient(135deg, rgba(255,126,61,0.10), rgba(59,130,246,0.06));">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle mb-2">Admin · Monitor Kinerja</span>
                            <h4 class="fw-semibold mb-1">Papan Peringkat Pemilahan Sampah</h4>
                            <p class="text-muted mb-0 fs-13">
                                Pantau warga dan RW paling aktif —
                                <strong>{{ $bulanDipilih->translatedFormat('F Y') }}</strong>
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            {{-- Month filter --}}
                            <form method="GET" action="{{ route('sips.leaderboard') }}" class="d-flex gap-2">
                                <select name="bulan" class="form-select form-select-sm" onchange="this.form.submit()">
                                    @foreach($availableMonths as $m)
                                    <option value="{{ $m->format('Y-m') }}"
                                        {{ $m->format('Y-m') === $bulanDipilih->format('Y-m') ? 'selected' : '' }}>
                                        {{ $m->translatedFormat('F Y') }}
                                    </option>
                                    @endforeach
                                </select>
                            </form>
                            <a href="{{ route('public.leaderboard') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                                Lihat versi publik
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-4 mb-4">
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted mb-0 fs-13">Warga Aktif</p>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                    </div>
                    <h3 class="mb-1">{{ $wargaAktif }}</h3>
                    <small class="text-muted">Terdaftar dalam sistem</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted mb-0 fs-13">Total Dipilah</p>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Kg</span>
                    </div>
                    <h3 class="mb-1">{{ number_format($totalKgDipilah, 1, ',', '.') }} kg</h3>
                    <small class="text-muted">Bulan {{ $bulanDipilih->translatedFormat('F Y') }}</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted mb-0 fs-13">RW Teraktif</p>
                        <span class="badge bg-info-subtle text-info border border-info-subtle">Top RW</span>
                    </div>
                    @if($rwTerbaik)
                    <h3 class="mb-1">RW {{ $rwTerbaik->rw ?? '-' }}</h3>
                    <small class="text-muted">{{ $rwTerbaik->dusun ?? '' }}{{ $rwTerbaik->dusun ? ' · ' : '' }}{{ $rwTerbaik->persen_dipilah }}% pemilahan</small>
                    @else
                    <h3 class="mb-1">-</h3>
                    <small class="text-muted">Belum ada data</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-md-6 col-xl-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted mb-0 fs-13">Rata-rata Poin</p>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Top 10</span>
                    </div>
                    <h3 class="mb-1">{{ $rataRataPoin }}</h3>
                    <small class="text-muted">10 warga teratas bulan ini</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="row g-4">

        {{-- Left column: ranking tables stacked --}}
        <div class="col-xl-8">

            {{-- Ranking Warga Terdaftar --}}
            <div class="card {{ $unregistered->isNotEmpty() ? 'mb-4' : '' }}">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Ranking Warga Terdaftar</h5>
                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                        {{ $entries->count() }} warga berkontribusi
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">Rank</th>
                                    <th>Nama Warga</th>
                                    <th class="d-none d-md-table-cell">RW / Dusun</th>
                                    <th class="d-none d-lg-table-cell text-end">Dipilah (kg)</th>
                                    <th class="d-none d-lg-table-cell text-end">% Pilah</th>
                                    <th class="text-end">Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $i => $entry)
                                @php
                                    $rank = $i + 1;
                                    $badgeClass = match(true) {
                                        $rank === 1 => 'bg-warning-subtle text-warning',
                                        $rank === 2 => 'bg-secondary-subtle text-secondary',
                                        $rank === 3 => 'badge-bronze',
                                        $rank <= 10 => 'bg-primary-subtle text-primary',
                                        default     => 'bg-light text-muted',
                                    };
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <span class="badge rounded-pill {{ $badgeClass }}">#{{ $rank }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{{ $entry->nama }}</div>
                                        <small class="text-muted">{{ Str::limit($entry->alamat ?? '', 42) ?: '-' }}</small>
                                    </td>
                                    <td class="d-none d-md-table-cell text-muted fs-13">
                                        @if($entry->rw)RW {{ $entry->rw }}@endif{{ ($entry->rw && $entry->dusun) ? ' / ' : '' }}{{ $entry->dusun ?? '' }}
                                        @if(!$entry->rw && !$entry->dusun)<span class="text-muted">—</span>@endif
                                    </td>
                                    <td class="d-none d-lg-table-cell text-end">{{ number_format($entry->kg_dipilah, 1, ',', '.') }}</td>
                                    <td class="d-none d-lg-table-cell text-end">{{ $entry->persen_dipilah }}%</td>
                                    <td class="text-end fw-semibold">{{ (int) $entry->poin }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Belum ada data setoran untuk periode ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Penyetor Belum Terdaftar --}}
            @if($unregistered->isNotEmpty())
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0">Penyetor Belum Terdaftar</h5>
                        <small class="text-muted">Setoran tercatat tapi tidak terhubung ke akun warga</small>
                    </div>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                        {{ $unregistered->count() }} penyetor
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px">No.</th>
                                    <th>Nama Penyetor</th>
                                    <th class="d-none d-lg-table-cell text-end">Total (kg)</th>
                                    <th class="d-none d-lg-table-cell text-end">Dipilah (kg)</th>
                                    <th class="d-none d-md-table-cell text-end">% Pilah</th>
                                    <th class="text-end">Poin</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($unregistered as $i => $u)
                                <tr>
                                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                                    <td class="fw-medium">{{ $u->nama }}</td>
                                    <td class="d-none d-lg-table-cell text-end">{{ number_format($u->total_kg, 1, ',', '.') }}</td>
                                    <td class="d-none d-lg-table-cell text-end">{{ number_format($u->kg_dipilah, 1, ',', '.') }}</td>
                                    <td class="d-none d-md-table-cell text-end">{{ $u->persen_dipilah }}%</td>
                                    <td class="text-end fw-semibold text-warning">{{ (int) $u->poin }}</td>
                                    <td class="text-end">
                                        @php
                                            $prefillParams = array_filter([
                                                'nama'      => $u->nama,
                                                'rw'        => $u->rw ? (int) ltrim($u->rw, '0') ?: null : null,
                                                'rt'        => $u->rt ? (int) ltrim($u->rt, '0') ?: null : null,
                                                'link_nama' => $u->nama,
                                            ]);
                                        @endphp
                                        <a href="{{ route('sips.warga.create', $prefillParams) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Buka form daftar warga dengan nama, RW, RT sudah terisi">
                                            <i class="ri-user-add-line me-1"></i> Daftarkan
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-warning-subtle border-warning-subtle fs-12 text-warning-emphasis">
                    <i class="ri-information-line me-1"></i>
                    Klik <strong>Daftarkan</strong> di tiap baris untuk membuka form pendaftaran dengan nama sudah terisi otomatis.
                    Lengkapi <strong>Alamat</strong> untuk menyimpan (RT/RW dan Dusun opsional).
                </div>
            </div>
            @endif

        </div>

        {{-- Sidebar --}}
        <div class="col-xl-4">

            {{-- RW ranking --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ranking RW</h5>
                </div>
                <div class="card-body">
                    @php
                    $rwColors = ['bg-success', 'bg-primary', 'bg-info', 'bg-warning', 'bg-secondary'];
                    $barColors = ['bg-success', 'bg-primary', 'bg-info', 'bg-warning', 'bg-secondary'];
                    @endphp
                    @forelse($rws->take(5) as $i => $rw)
                    <div class="{{ !$loop->last ? 'mb-4' : '' }}">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div>
                                <strong class="fs-13">RW {{ $rw->rw ?? '-' }}{{ $rw->dusun ? ' / '.$rw->dusun : '' }}</strong>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    {{ $rw->persen_dipilah }}% pemilahan · {{ $rw->jumlah_warga }} warga
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

            {{-- Poin formula --}}
            <div class="card mb-4 border-primary-subtle">
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

            {{-- Quick stats --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ringkasan Periode</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted fs-13">Warga berkontribusi</span>
                        <span class="fw-semibold">{{ $entries->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted fs-13">Total berat dipilah</span>
                        <span class="fw-semibold">{{ number_format($totalKgDipilah, 1, ',', '.') }} kg</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted fs-13">Jumlah RW aktif</span>
                        <span class="fw-semibold">{{ $rws->count() }} RW</span>
                    </div>
                    @if($entries->isNotEmpty())
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted fs-13">Poin tertinggi</span>
                        <span class="fw-semibold text-warning">{{ (int) $entries->first()->poin }} poin</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted fs-13">Juara bulan ini</span>
                        <span class="fw-semibold text-truncate ms-2" style="max-width:130px;">
                            {{ $entries->first()->nama }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
