@extends('partials.layouts.master')

@section('title', 'Data Master Warga | SIPS')

@section('title-sub', 'Sistem Informasi Pemilahan Sampah')
@section('pagetitle', 'Data Master Warga')

@section('content')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"> Daftar Warga Terdaftar </h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWargaModal">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Warga Baru
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Warga yang terdaftar tidak memiliki akun login. Data ini digunakan sebagai referensi saat warga menyetorkan sampah.</p>
                    
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle mb-0 table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Nama Lengkap</th>
                                    <th scope="col">Nomor KK</th>
                                    <th scope="col">RT / RW / Dusun</th>
                                    <th scope="col">Nomor HP</th>
                                    <th scope="col">Tanggal Terdaftar</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-medium">Budi Santoso</td>
                                    <td>3271123456789012</td>
                                    <td>RT 01 / RW 03 / Dusun Melati</td>
                                    <td>081234567890</td>
                                    <td>01 Mei 2025</td>
                                    <td><span class="badge border border-success text-success">Aktif</span></td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <button type="button" class="btn btn-light-primary border-primary icon-btn-sm" data-bs-toggle="tooltip" data-bs-title="Edit"><i class="ri-edit-2-line"></i></button>
                                            <button type="button" class="btn btn-light-danger border-danger icon-btn-sm" data-bs-toggle="tooltip" data-bs-title="Nonaktifkan"><i class="ri-user-unfollow-line"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">Siti Aminah</td>
                                    <td>3271123456789099</td>
                                    <td>RT 02 / RW 03 / Dusun Melati</td>
                                    <td>085678901234</td>
                                    <td>15 April 2025</td>
                                    <td><span class="badge border border-success text-success">Aktif</span></td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <button type="button" class="btn btn-light-primary border-primary icon-btn-sm" data-bs-toggle="tooltip" data-bs-title="Edit"><i class="ri-edit-2-line"></i></button>
                                            <button type="button" class="btn btn-light-danger border-danger icon-btn-sm" data-bs-toggle="tooltip" data-bs-title="Nonaktifkan"><i class="ri-user-unfollow-line"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-medium">Agus Pratama</td>
                                    <td>3271123456789055</td>
                                    <td>RT 01 / RW 04 / Dusun Anggrek</td>
                                    <td>-</td>
                                    <td>10 Maret 2025</td>
                                    <td><span class="badge border border-danger text-danger">Tidak Aktif</span></td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <button type="button" class="btn btn-light-primary border-primary icon-btn-sm" data-bs-toggle="tooltip" data-bs-title="Edit"><i class="ri-edit-2-line"></i></button>
                                            <button type="button" class="btn btn-light-success border-success icon-btn-sm" data-bs-toggle="tooltip" data-bs-title="Aktifkan"><i class="ri-user-follow-line"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Warga -->
<div class="modal fade" id="addWargaModal" tabindex="-1" aria-labelledby="addWargaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWargaModalLabel">Tambah Warga Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" placeholder="Masukkan nama lengkap">
                    </div>
                    <div class="mb-3">
                        <label for="no_kk" class="form-label">Nomor Kartu Keluarga (KK)</label>
                        <input type="text" class="form-control" id="no_kk" placeholder="Masukkan 16 digit No KK">
                    </div>
                    <div class="mb-3">
                        <label for="rt_rw" class="form-label">RT / RW / Dusun</label>
                        <input type="text" class="form-control" id="rt_rw" placeholder="Contoh: RT 01 / RW 03 / Dusun Melati">
                    </div>
                    <div class="mb-3">
                        <label for="no_hp" class="form-label">Nomor HP (Opsional)</label>
                        <input type="text" class="form-control" id="no_hp" placeholder="Contoh: 081234567890">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary">Simpan Warga</button>
            </div>
        </div>
    </div>
</div>
@endsection
