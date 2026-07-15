@extends('partials.layouts.master')

@section('title', 'Pengaturan Tarif | SIPS')
@section('title-sub', 'Pengaturan')
@section('pagetitle', 'Pengaturan Tarif')

@section('content')
<div id="layout-wrapper">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <strong>Terdapat kesalahan pada form:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Info cards --}}
            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="card border-success border h-100">
                        <div class="card-body text-center py-4">
                            <div class="avatar-sm mx-auto mb-3">
                                <span class="avatar-title bg-success-subtle text-success rounded-circle fs-3">
                                    <i class="ri-check-double-line"></i>
                                </span>
                            </div>
                            <div class="text-muted fs-13 mb-1">Sampah Dipilah</div>
                            <h3 class="text-success mb-0">
                                Rp {{ number_format((float)$settings['nilai_dipilah_per_kg'], 0, ',', '.') }}/kg
                            </h3>
                            <small class="text-muted">Warga <strong>menerima</strong> uang</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card border-danger border h-100">
                        <div class="card-body text-center py-4">
                            <div class="avatar-sm mx-auto mb-3">
                                <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-3">
                                    <i class="ri-close-circle-line"></i>
                                </span>
                            </div>
                            <div class="text-muted fs-13 mb-1">Tidak Dipilah (Iuran)</div>
                            <h3 class="text-danger mb-0">
                                Rp {{ number_format((float)$settings['tarif_flat_per_kg'], 0, ',', '.') }}/kg
                            </h3>
                            <small class="text-muted">Warga <strong>membayar</strong> iuran</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Edit form --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-edit-line me-2 text-primary"></i> Ubah Tarif
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sips.pengaturan.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="nilai_dipilah_per_kg" class="form-label fw-semibold">
                                    <span class="text-success me-1">✓</span> Tarif Dipilah (Rp/kg)
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number"
                                           class="form-control @error('nilai_dipilah_per_kg') is-invalid @enderror"
                                           id="nilai_dipilah_per_kg"
                                           name="nilai_dipilah_per_kg"
                                           min="0" step="50"
                                           value="{{ old('nilai_dipilah_per_kg', $settings['nilai_dipilah_per_kg']) }}"
                                           required>
                                    <span class="input-group-text">/kg</span>
                                    @error('nilai_dipilah_per_kg')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Jumlah yang warga <em>terima</em> per kg sampah dipilah.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="tarif_flat_per_kg" class="form-label fw-semibold">
                                    <span class="text-danger me-1">✗</span> Iuran Tidak Dipilah (Rp/kg)
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number"
                                           class="form-control @error('tarif_flat_per_kg') is-invalid @enderror"
                                           id="tarif_flat_per_kg"
                                           name="tarif_flat_per_kg"
                                           min="0" step="50"
                                           value="{{ old('tarif_flat_per_kg', $settings['tarif_flat_per_kg']) }}"
                                           required>
                                    <span class="input-group-text">/kg</span>
                                    @error('tarif_flat_per_kg')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text">Iuran yang warga <em>bayar</em> per kg sampah tidak dipilah.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="ri-save-line me-1"></i> Simpan Tarif
                            </button>
                        </div>

                        {{-- hidden: preserve other settings --}}
                        <input type="hidden" name="nama_tps"
                               value="{{ $settings['nama_tps'] ?? '' }}">
                    </form>
                </div>
            </div>

            <div class="alert alert-info fs-13 mt-3">
                <i class="ri-information-line me-1"></i>
                Tarif berlaku untuk <strong>semua setoran baru</strong> setelah disimpan.
                Setoran yang sudah tercatat tidak terpengaruh.
            </div>

        </div>
    </div>
</div>
@endsection
