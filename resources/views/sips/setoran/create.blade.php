@extends('partials.layouts.master')

@section('title', 'Input Setoran Sampah | SIPS')

@section('title-sub', 'Transaksi')
@section('pagetitle', 'Input Setoran Baru')

@section('content')
<div id="layout-wrapper">
    <div class="row">
        <!-- Left Column: Form -->
        <div class="col-xl-8 col-lg-7">
            <div class="card card-h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0"> Form Setoran Sampah </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Pilih warga yang menyetorkan sampah dan isi detail setoran dengan benar. Sistem akan menghitung estimasi nilai rupiah secara otomatis.</p>
                    
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="wargaSelect" class="form-label">Pilih Warga</label>
                                <select class="form-select" id="wargaSelect">
                                    <option selected disabled>-- Cari Warga --</option>
                                    <option value="1">Budi Santoso (RT 01 / Dusun Melati)</option>
                                    <option value="2">Siti Aminah (RT 02 / Dusun Melati)</option>
                                    <option value="3">Agus Pratama (RT 01 / Dusun Anggrek)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="tanggalSetoran" class="form-label">Tanggal Setoran</label>
                                <input type="date" class="form-control" id="tanggalSetoran" value="2025-05-05">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="beratSampah" class="form-label">Berat Sampah (kg)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="beratSampah" placeholder="0.00" step="0.1" min="0.1">
                                    <span class="input-group-text">kg</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jenis Sampah</label>
                                <select class="form-select" id="jenisSampah">
                                    <option value="organik">Organik</option>
                                    <option value="anorganik">Anorganik</option>
                                </select>
                                <small class="text-muted d-block mt-1">Kategori yang diterima saat ini: Organik dan Anorganik.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label d-block">Status Pemilahan</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="statusPemilahan" id="dipilah" value="dipilah" checked>
                                    <label class="form-check-label" for="dipilah">Sudah Dipilah (Nilai lebih tinggi)</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="statusPemilahan" id="tidakDipilah" value="tidak_dipilah">
                                    <label class="form-check-label" for="tidakDipilah">Belum Dipilah (Campur)</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="catatan" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="catatan" rows="3" placeholder="Contoh: Plastik bersih sudah diikat rapi..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light">Reset</button>
                            <button type="button" class="btn btn-primary"><i class="ri-save-line me-1"></i> Simpan Setoran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column: Summary/Estimator -->
        <div class="col-xl-4 col-lg-5">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title text-white mb-4"><i class="ri-calculator-line me-2"></i> Estimasi Nilai</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Jenis Sampah</span>
                        <span class="fw-semibold">Organik</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Status</span>
                        <span class="fw-semibold">Dipilah</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tarif Berlaku</span>
                        <span class="fw-semibold">Rp 500 / kg</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-bottom border-white border-opacity-25 pb-3">
                        <span>Berat Input</span>
                        <span class="fw-semibold">2.5 kg</span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-16">Total Dibayarkan</span>
                        <h3 class="text-white mb-0">Rp 1.250</h3>
                    </div>
                </div>
            </div>

            <div class="card mt-4 border-info border">
                <div class="card-body bg-info-subtle">
                    <div class="d-flex align-items-center">
                        <div class="shrink-0 me-3">
                            <i class="ri-information-line fs-1 text-info"></i>
                        </div>
                        <div>
                            <h6 class="text-info fw-semibold mb-1">Panduan Petugas</h6>
                            <p class="text-muted mb-0 fs-13">Pastikan sampah sudah ditimbang dengan benar. Pembayaran dilakukan secara tunai di luar sistem. Setelah dibayar, Anda bisa mencatatnya di halaman riwayat.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
