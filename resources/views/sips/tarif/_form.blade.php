<div class="row g-3">
    <div class="col-md-6">
        <label for="nama_item" class="form-label">Nama Item <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('nama_item') is-invalid @enderror"
               id="nama_item" name="nama_item" value="{{ old('nama_item', $tarifItem->nama_item ?? '') }}" required>
        @error('nama_item')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="tipe_sampah" class="form-label">Tipe Utama <span class="text-danger">*</span></label>
        <select class="form-select @error('tipe_sampah') is-invalid @enderror"
                id="tipe_sampah" name="tipe_sampah" required>
            <option value="organik" {{ old('tipe_sampah', $tarifItem->tipe_sampah ?? '') === 'organik' ? 'selected' : '' }}>Organik</option>
            <option value="anorganik" {{ old('tipe_sampah', $tarifItem->tipe_sampah ?? '') === 'anorganik' ? 'selected' : '' }}>Anorganik</option>
        </select>
        @error('tipe_sampah')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror"
                id="status" name="status" required>
            <option value="aktif" {{ old('status', $tarifItem->status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="arsip" {{ old('status', $tarifItem->status ?? 'aktif') === 'arsip' ? 'selected' : '' }}>Arsip</option>
        </select>
        @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
