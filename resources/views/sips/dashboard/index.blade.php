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
                        <h6 class="mb-0">Mei 2026</h6>
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
                    <div class="sips-kpi-value">3.480 kg</div>
                    <div class="mt-2 text-success fs-13"><i class="ri-arrow-up-line"></i> +12,4% dibanding bulan lalu</div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">Tingkat Pemilahan</p>
                    <div class="sips-kpi-value">78%</div>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 78%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">Total Pencairan</p>
                    <div class="sips-kpi-value">Rp 2.945.000</div>
                    <div class="mt-2 text-muted fs-13">Dari 416 transaksi setoran</div>
                </div>
            </div>
        </div>
        <div class="col-xxl-3 col-md-6">
            <div class="card card-h-100">
                <div class="card-body">
                    <p class="text-muted mb-2">Warga Aktif</p>
                    <div class="sips-kpi-value">182</div>
                    <div class="mt-2 text-danger fs-13"><i class="ri-arrow-down-line"></i> 5 warga menjadi tidak aktif bulan ini</div>
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
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span><span class="sips-stat-dot bg-success me-2"></span>Organik (Dipilah)</span>
                            <strong>1.420 kg</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: 41%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span><span class="sips-stat-dot bg-info me-2"></span>Anorganik (Dipilah)</span>
                            <strong>1.170 kg</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: 34%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span><span class="sips-stat-dot bg-warning me-2"></span>Campuran / Tidak Dipilah</span>
                            <strong>770 kg</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" style="width: 22%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><span class="sips-stat-dot bg-secondary me-2"></span>Residu / Belum Terpilah</span>
                            <strong>120 kg</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-secondary" style="width: 3%"></div>
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
                                <tr>
                                    <td><span class="badge bg-success-subtle text-success">#1</span></td>
                                    <td>RW 03</td>
                                    <td>89%</td>
                                    <td>760</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-primary-subtle text-primary">#2</span></td>
                                    <td>RW 01</td>
                                    <td>84%</td>
                                    <td>710</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-info-subtle text-info">#3</span></td>
                                    <td>RW 05</td>
                                    <td>79%</td>
                                    <td>645</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-warning-subtle text-warning">#4</span></td>
                                    <td>RW 04</td>
                                    <td>75%</td>
                                    <td>701</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-danger-subtle text-danger">#5</span></td>
                                    <td>RW 02</td>
                                    <td>68%</td>
                                    <td>664</td>
                                </tr>
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
                    <span class="text-muted fs-13">Jan - Mei 2026</span>
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
                                <tr>
                                    <td>Januari</td>
                                    <td>2.810</td>
                                    <td>70%</td>
                                    <td>2.281.000</td>
                                    <td>171</td>
                                </tr>
                                <tr>
                                    <td>Februari</td>
                                    <td>2.940</td>
                                    <td>72%</td>
                                    <td>2.394.000</td>
                                    <td>174</td>
                                </tr>
                                <tr>
                                    <td>Maret</td>
                                    <td>3.160</td>
                                    <td>75%</td>
                                    <td>2.643.000</td>
                                    <td>179</td>
                                </tr>
                                <tr>
                                    <td>April</td>
                                    <td>3.095</td>
                                    <td>74%</td>
                                    <td>2.571.000</td>
                                    <td>187</td>
                                </tr>
                                <tr>
                                    <td>Mei</td>
                                    <td>3.480</td>
                                    <td>78%</td>
                                    <td>2.945.000</td>
                                    <td>182</td>
                                </tr>
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
                        <span class="badge border border-success text-success">359</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>Setoran Belum Dibayar</span>
                        <span class="badge border border-danger text-danger">57</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span>Nilai Tertunda</span>
                        <strong>Rp 381.500</strong>
                    </div>
                </div>
            </div>

            <div class="card sips-soft-card">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0">Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <a href="/sips/setoran/create" class="btn btn-primary w-100 mb-2">+ Catat Setoran Baru</a>
                    <a href="/sips/warga" class="btn btn-light w-100 mb-2">Kelola Data Warga</a>
                    <a href="/sips/tarif" class="btn btn-light w-100">Perbarui Tarif</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
