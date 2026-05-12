@php
    $hargaField = $hargaField ?? 'harga_per_kg';
    $tanggalField = $tanggalField ?? 'tanggal_mulai';
    $alasanField = $alasanField ?? 'alasan_perubahan';
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label for="{{ $hargaField }}" class="form-label">Harga per Kg <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">Rp</span>
            <input type="number" class="form-control @error($hargaField) is-invalid @enderror"
                   id="{{ $hargaField }}" name="{{ $hargaField }}"
                   min="0" step="1" value="{{ old($hargaField, $defaultHarga ?? '') }}" required>
            @error($hargaField)
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-4">
        <label for="{{ $tanggalField }}" class="form-label">Tanggal Efektif <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error($tanggalField) is-invalid @enderror"
               id="{{ $tanggalField }}" name="{{ $tanggalField }}"
               value="{{ old($tanggalField, $defaultTanggal ?? now()->toDateString()) }}" required>
        @error($tanggalField)
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12">
        <label for="{{ $alasanField }}" class="form-label">Alasan Perubahan</label>
        <textarea class="form-control @error($alasanField) is-invalid @enderror"
                  id="{{ $alasanField }}" name="{{ $alasanField }}" rows="3"
                  placeholder="Opsional, misalnya penyesuaian harga pasar">{{ old($alasanField, $defaultAlasan ?? '') }}</textarea>
        @error($alasanField)
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
