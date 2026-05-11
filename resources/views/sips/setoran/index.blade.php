@extends('partials.layouts.master')

@section('title', 'Riwayat Setoran | SIPS')

@section('title-sub', 'Setoran Sampah')
@section('pagetitle', 'Riwayat Setoran')

@section('content')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"> Daftar Transaksi Setoran </h5>
                    <div>
                        <a href="/sips/setoran/create" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Setoran Baru</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-4">
                        <p class="text-muted mb-0">Daftar semua setoran warga. Anda dapat menandai setoran yang belum dibayar menjadi "Sudah Dibayar" setelah menyerahkan uang tunai.</p>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm w-auto">
                                <option value="all">Semua RW/Dusun</option>
                                <option value="1">RT 01 / Dusun Melati</option>
                                <option value="2">RT 01 / Dusun Anggrek</option>
                            </select>
                            <select class="form-select form-select-sm w-auto">
                                <option value="all">Semua Status Bayar</option>
                                <option value="belum">Belum Dibayar</option>
                                <option value="sudah">Sudah Dibayar</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle mb-0 table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">ID Transaksi</th>
                                    <th scope="col">Nama Warga</th>
                                    <th scope="col">Tanggal</th>
                                    <th scope="col">Detail Sampah</th>
                                    <th scope="col">Total (Rp)</th>
                                    <th scope="col">Status Pembayaran</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-medium">#TRX-20250505-001</td>
                                    <td>
                                        <h6 class="mb-0">Budi Santoso</h6>
                                        <small class="text-muted">RT 01 / Dusun Melati</small>
                                    </td>
                                    <td>05 Mei 2025</td>
                                    <td>
                                        <span class="d-block">2.5 kg - Organik</span>
                                        <small class="text-muted">Dipilah</small>
                                    </td>
                                    <td class="fw-semibold text-end">Rp 1.250</td>
                                    <td><span class="badge bg-danger-subtle text-danger">Belum Dibayar</span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#payModal">
                                            <i class="ri-money-dollar-circle-line me-1"></i> Catat Bayar
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">#TRX-20250504-012</td>
                                    <td>
                                        <h6 class="mb-0">Siti Aminah</h6>
                                        <small class="text-muted">RT 02 / Dusun Melati</small>
                                    </td>
                                    <td>04 Mei 2025</td>
                                    <td>
                                        <span class="d-block">1.2 kg - Anorganik</span>
                                        <small class="text-muted">Dipilah</small>
                                    </td>
                                    <td class="fw-semibold text-end">Rp 1.200</td>
                                    <td><span class="badge bg-success-subtle text-success">Sudah Dibayar</span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="tooltip" data-bs-title="Lihat Kwitansi">
                                            <i class="ri-file-text-line"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">#TRX-20250504-008</td>
                                    <td>
                                        <h6 class="mb-0">Agus Pratama</h6>
                                        <small class="text-muted">RT 01 / Dusun Anggrek</small>
                                    </td>
                                    <td>04 Mei 2025</td>
                                    <td>
                                        <span class="d-block">5.0 kg - Organik</span>
                                        <small class="text-muted">Tidak Dipilah</small>
                                    </td>
                                    <td class="fw-semibold text-end">Rp 1.000</td>
                                    <td><span class="badge bg-success-subtle text-success">Sudah Dibayar</span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="tooltip" data-bs-title="Lihat Kwitansi">
                                            <i class="ri-file-text-line"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <span class="text-muted">Menampilkan 1 sampai 3 dari 3 transaksi</span>
                        <ul class="pagination mb-0">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" aria-label="Sebelumnya">
                                    <i class="ri-arrow-left-s-line"></i>
                                </a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item disabled">
                                <a class="page-link" href="#" aria-label="Selanjutnya">
                                    <i class="ri-arrow-right-s-line"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pencatatan Bayar -->
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="ri-wallet-3-line text-success display-4 d-block mb-3"></i>
                <h4 class="mb-1">Rp 1.250</h4>
                <p class="text-muted mb-4">Setoran Budi Santoso</p>
                <div class="alert alert-info py-2 fs-13">
                    Pastikan Anda telah memberikan uang tunai kepada warga.
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label fs-13">Catatan Petugas (Opsional)</label>
                    <input type="text" class="form-control form-control-sm" placeholder="Misal: Diberikan pas">
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success">Konfirmasi Sudah Dibayar</button>
            </div>
        </div>
    </div>
</div>
@endsection
