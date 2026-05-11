@extends('partials.layouts.master')

@section('title', 'Papan Peringkat Admin')

@section('content')
<div class="container-fluid py-4 sips-page-shell">
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card leaderboard-outline-card overflow-hidden">
                <div class="card-body p-4 p-lg-5" style="background: linear-gradient(135deg, rgba(255, 126, 61, 0.14), rgba(59, 130, 246, 0.08));">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle mb-3">Admin · Monitor Kinerja</span>
                            <h1 class="h2 fw-semibold mb-2">Papan Peringkat Pemilahan Sampah</h1>
                            <p class="text-muted mb-0">
                                Pantau warga dan RW paling aktif, cek peringkat, lalu gunakan data ini untuk evaluasi program.
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="/leaderboard" class="btn btn-outline-secondary">Lihat versi publik</a>
                            <button type="button" class="btn btn-primary">Atur periode</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card sips-summary-card leaderboard-outline-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted mb-0">Warga aktif</p>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                    </div>
                    <h3 class="mb-1">182</h3>
                    <small class="text-success">+12 dari bulan lalu</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card sips-summary-card leaderboard-outline-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted mb-0">Total dipilah</p>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Kg</span>
                    </div>
                    <h3 class="mb-1">1.420 kg</h3>
                    <small class="text-muted">Data per periode berjalan</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card sips-summary-card leaderboard-outline-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted mb-0">RW teraktif</p>
                        <span class="badge bg-info-subtle text-info border border-info-subtle">Top RW</span>
                    </div>
                    <h3 class="mb-1">RW 03</h3>
                    <small class="text-muted">Melati · 89% pemilahan</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card sips-summary-card leaderboard-outline-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="text-muted mb-0">Rata-rata poin</p>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Terbaru</span>
                    </div>
                    <h3 class="mb-1">478</h3>
                    <small class="text-muted">Top 10 warga bulan ini</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card leaderboard-outline-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0">Ranking Warga</h5>
                    <span class="badge bg-info-subtle text-info border border-info-subtle">Siap untuk evaluasi mingguan</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Peringkat</th>
                                    <th>Nama Warga</th>
                                    <th>RW / Dusun</th>
                                    <th>Dipilah</th>
                                    <th>Poin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge rounded-pill bg-warning-subtle text-warning">1</span></td>
                                    <td>Siti Aminah</td>
                                    <td>RW 03 / Melati</td>
                                    <td>24.6 kg</td>
                                    <td class="fw-semibold">528</td>
                                </tr>
                                <tr>
                                    <td><span class="badge rounded-pill bg-primary-subtle text-primary">2</span></td>
                                    <td>Budi Santoso</td>
                                    <td>RW 01 / Melati</td>
                                    <td>22.2 kg</td>
                                    <td class="fw-semibold">502</td>
                                </tr>
                                <tr>
                                    <td><span class="badge rounded-pill bg-info-subtle text-info">3</span></td>
                                    <td>Agus Pratama</td>
                                    <td>RW 04 / Anggrek</td>
                                    <td>19.8 kg</td>
                                    <td class="fw-semibold">451</td>
                                </tr>
                                <tr>
                                    <td><span class="badge rounded-pill bg-success-subtle text-success">4</span></td>
                                    <td>Rahmawati</td>
                                    <td>RW 05 / Kenanga</td>
                                    <td>18.9 kg</td>
                                    <td class="fw-semibold">430</td>
                                </tr>
                                <tr>
                                    <td><span class="badge rounded-pill bg-danger-subtle text-danger">5</span></td>
                                    <td>Deni Saputra</td>
                                    <td>RW 02 / Anggrek</td>
                                    <td>17.1 kg</td>
                                    <td class="fw-semibold">388</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card leaderboard-outline-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ranking RW</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>RW 03 / Melati</strong>
                            <span class="badge bg-success">#1</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: 89%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>RW 01 / Melati</strong>
                            <span class="badge bg-primary">#2</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: 84%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>RW 05 / Kenanga</strong>
                            <span class="badge bg-info">#3</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: 79%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card leaderboard-outline-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Aksi Admin</h5>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Cepat</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary">Unduh laporan</button>
                        <button type="button" class="btn btn-outline-secondary">Filter bulan</button>
                        <button type="button" class="btn btn-outline-success">Sorot RW terbaik</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
