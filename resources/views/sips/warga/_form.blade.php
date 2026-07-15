@php
    $isEdit  = isset($warga);
    $prefill ??= [];
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('nama') is-invalid @enderror"
               id="nama" name="nama" value="{{ old('nama', $warga->nama ?? $prefill['nama'] ?? '') }}" required>
        @error('nama')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="no_kk" class="form-label">Nomor KK <span class="text-muted fw-normal fs-12">(opsional)</span></label>
        <input type="text" class="form-control @error('no_kk') is-invalid @enderror"
               id="no_kk" name="no_kk" maxlength="16"
               inputmode="numeric"
               value="{{ old('no_kk', $warga->no_kk ?? '') }}"
               oninput="this.value = this.value.replace(/\D/g, '').slice(0, 16)"
               placeholder="16 digit angka">
        <div class="form-text">Jika diisi, harus tepat 16 digit angka. Digunakan sebagai kunci deduplikasi saat import.</div>
        @error('no_kk')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
        <textarea class="form-control @error('alamat') is-invalid @enderror"
                  id="alamat" name="alamat" rows="2" maxlength="500"
                  placeholder="Jalan, nomor rumah, RT/RW, dusun, dll."
                  required>{{ old('alamat', $warga->alamat ?? '') }}</textarea>
        @error('alamat')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2">
        <label for="rt" class="form-label">RT <span class="text-muted fw-normal fs-12">(opsional)</span></label>
        <input type="number" class="form-control @error('rt') is-invalid @enderror"
               id="rt" name="rt" min="1" max="999" value="{{ old('rt', $warga->rt ?? $prefill['rt'] ?? '') }}">
        @error('rt')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-2">
        <label for="rw" class="form-label">RW <span class="text-muted fw-normal fs-12">(opsional)</span></label>
        <input type="number" class="form-control @error('rw') is-invalid @enderror"
               id="rw" name="rw" min="1" max="999" value="{{ old('rw', $warga->rw ?? $prefill['rw'] ?? '') }}">
        @error('rw')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="dusun" class="form-label">Dusun <span class="text-muted fw-normal fs-12">(opsional)</span></label>
        <input type="text" class="form-control @error('dusun') is-invalid @enderror"
               id="dusun" name="dusun" value="{{ old('dusun', $warga->dusun ?? '') }}">
        @error('dusun')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="no_hp" class="form-label">Nomor HP</label>
        <input type="text" class="form-control @error('no_hp') is-invalid @enderror"
               id="no_hp" name="no_hp" maxlength="20" value="{{ old('no_hp', $warga->no_hp ?? '') }}">
        @error('no_hp')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="tanggal_terdaftar" class="form-label">Tanggal Terdaftar <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('tanggal_terdaftar') is-invalid @enderror"
               id="tanggal_terdaftar" name="tanggal_terdaftar"
               value="{{ old('tanggal_terdaftar', isset($warga) ? $warga->tanggal_terdaftar?->toDateString() : now()->toDateString()) }}" required>
        @error('tanggal_terdaftar')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="status_keanggotaan" class="form-label">Status Keanggotaan <span class="text-danger">*</span></label>
        <select class="form-select @error('status_keanggotaan') is-invalid @enderror"
                id="status_keanggotaan" name="status_keanggotaan" required>
            <option value="aktif" {{ old('status_keanggotaan', $warga->status_keanggotaan ?? 'aktif') === 'aktif' ? 'selected' : '' }}>
                Aktif
            </option>
            <option value="non_aktif" {{ old('status_keanggotaan', $warga->status_keanggotaan ?? 'aktif') === 'non_aktif' ? 'selected' : '' }}>
                Non Aktif
            </option>
        </select>
        @error('status_keanggotaan')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('sips.warga.index') }}" class="btn btn-light">Batal</a>
    <button type="submit" class="btn btn-primary">
        <i class="ri-save-line me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Warga' }}
    </button>
</div>
