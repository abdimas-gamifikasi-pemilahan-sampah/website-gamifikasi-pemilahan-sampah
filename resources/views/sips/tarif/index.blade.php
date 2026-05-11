@extends('partials.layouts.master')

@section('title', 'Manajemen Tarif | SIPS')

@section('title-sub', 'Pengaturan')
@section('pagetitle', 'Manajemen Tarif Sampah')

@section('content')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"> Daftar Tarif Berlaku </h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tarifModal">
                        <i class="bi bi-tags me-1"></i> Perbarui Tarif
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="ri-error-warning-line fs-4 me-2"></i>
                        <div>
                            <strong>Penting:</strong> Perubahan tarif hanya berlaku untuk setoran pada atau setelah Tanggal Efektif. Nilai setoran historis yang sudah diinput tidak akan berubah.
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <!-- Organik -->
                        <div class="col-md-4 mb-4">
                            <div class="card border border-success h-100 shadow-sm">
                                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-white"><i class="ri-leaf-line me-2"></i> Organik</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                        <div>
                                            <span class="d-block fw-semibold text-body">Dipilah</span>
                                            <small class="text-muted">Bersih, terpisah</small>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="text-success mb-0">Rp 500 <span class="fs-12 text-muted fw-normal">/ kg</span></h5>
                                            <small class="text-muted fs-11">Sejak 01 Jan 2025</small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="d-block fw-semibold text-body">Tidak Dipilah</span>
                                            <small class="text-muted">Campur</small>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="text-warning mb-0">Rp 200 <span class="fs-12 text-muted fw-normal">/ kg</span></h5>
                                            <small class="text-muted fs-11">Sejak 01 Jan 2025</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Anorganik -->
                        <div class="col-md-4 mb-4">
                            <div class="card border border-info h-100 shadow-sm">
                                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-white"><i class="ri-recycle-line me-2"></i> Anorganik</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                        <div>
                                            <span class="d-block fw-semibold text-body">Dipilah</span>
                                            <small class="text-muted">Plastik, Kertas, dll</small>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="text-success mb-0">Rp 1.000 <span class="fs-12 text-muted fw-normal">/ kg</span></h5>
                                            <small class="text-muted fs-11">Sejak 15 Mar 2025</small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="d-block fw-semibold text-body">Tidak Dipilah</span>
                                            <small class="text-muted">Campur</small>
                                        </div>
                                        <div class="text-end">
                                            <h5 class="text-warning mb-0">Rp 300 <span class="fs-12 text-muted fw-normal">/ kg</span></h5>
                                            <small class="text-muted fs-11">Sejak 01 Jan 2025</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Riwayat Perubahan Tarif Table -->
                    <h6 class="mt-4 mb-3 fw-bold">Riwayat Pembaruan Tarif Terakhir</h6>
                    <div class="table-responsive">
                        <table class="table table-sm text-nowrap align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal Efektif</th>
                                    <th>Jenis Sampah</th>
                                    <th>Status Pemilahan</th>
                                    <th>Harga Lama</th>
                                    <th>Harga Baru</th>
                                    <th>Diperbarui Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>15 Mar 2025</td>
                                    <td>Anorganik</td>
                                    <td>Dipilah</td>
                                    <td><span class="text-muted text-decoration-line-through">Rp 800</span></td>
                                    <td class="fw-semibold text-success">Rp 1.000</td>
                                    <td>Admin Desa</td>
                                </tr>
                                <tr>
                                    <td>01 Jan 2025</td>
                                    <td>Semua Jenis</td>
                                    <td>Semua Status</td>
                                    <td><span class="text-muted">-</span></td>
                                    <td class="fw-semibold text-success">Tarif Awal</td>
                                    <td>System</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Perbarui Tarif -->
<div class="modal fade" id="tarifModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perbarui Harga Tarif</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jenis Sampah</label>
                            <select class="form-select">
                                <option value="organik">Organik</option>
                                <option value="anorganik">Anorganik</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status Pemilahan</label>
                            <select class="form-select">
                                <option value="dipilah">Dipilah</option>
                                <option value="tidak_dipilah">Tidak Dipilah</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Harga Baru per Kg (Rp)</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" class="form-control" placeholder="Contoh: 1500" min="0" step="100">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Efektif Berlaku</label>
                        <input type="date" class="form-control" value="2025-05-06">
                        <small class="text-muted mt-1 d-block">Setoran sebelum tanggal ini tetap menggunakan harga lama.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary">Simpan Tarif Baru</button>
            </div>
        </div>
    </div>
</div>
@endsection
