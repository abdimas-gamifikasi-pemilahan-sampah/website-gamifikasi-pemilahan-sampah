@extends('partials.layouts.master')

@section('title', 'Input Setoran Sampah | SIPS')
@section('title-sub', 'Transaksi')
@section('pagetitle', 'Input Setoran Baru')

@section('content')
<div id="layout-wrapper">

    {{-- Validation errors --}}
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

    <form method="POST" action="{{ route('sips.setoran.store') }}" id="form-setoran">
        @csrf

        <div class="row">

            {{-- ── Left Column ── --}}
            <div class="col-xl-8 col-lg-7">
                <div class="card card-h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Form Setoran Sampah</h5>
                    </div>
                    <div class="card-body">

                        {{-- Warga & Tanggal --}}
                        <div class="row mb-3">
                            <div class="col-md-7">
                                <label for="warga_id" class="form-label">Warga <span class="text-danger">*</span></label>
                                <select class="form-select @error('warga_id') is-invalid @enderror"
                                        id="warga_id" name="warga_id" required>
                                    <option value="" disabled {{ old('warga_id') ? '' : 'selected' }}>-- Cari Warga --</option>
                                    @foreach($warga as $w)
                                        <option value="{{ $w->id }}" {{ old('warga_id') == $w->id ? 'selected' : '' }}>
                                            {{ $w->nama }} (RT {{ $w->rt }} / {{ $w->dusun }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('warga_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-5">
                                <label for="tanggal_setoran" class="form-label">Tanggal Setoran <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('tanggal_setoran') is-invalid @enderror"
                                       id="tanggal_setoran" name="tanggal_setoran"
                                       value="{{ old('tanggal_setoran', date('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}" required>
                                @error('tanggal_setoran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Item Setoran --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Item Setoran <span class="text-danger">*</span></label>
                            @error('items')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            <div id="items-container"></div>

                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addItem()">
                                <i class="ri-add-line me-1"></i> Tambah Item Sampah
                            </button>
                        </div>

                        {{-- Catatan --}}
                        <div class="mb-4">
                            <label for="catatan_kondisi" class="form-label">Catatan Kondisi <span class="text-muted">(opsional)</span></label>
                            <textarea class="form-control" id="catatan_kondisi" name="catatan_kondisi"
                                      rows="2" maxlength="500"
                                      placeholder="Contoh: plastik sudah diikat, sampah kering...">{{ old('catatan_kondisi') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('sips.setoran.index') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line me-1"></i> Simpan Setoran
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ── Right Column ── --}}
            <div class="col-xl-4 col-lg-5">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title text-white mb-4">
                            <i class="ri-calculator-line me-2"></i> Estimasi Total
                        </h5>
                        <div id="summary-items" class="mb-3">
                            <p class="text-white-50 mb-0 fs-13">Belum ada item ditambahkan.</p>
                        </div>
                        <div class="border-top border-white border-opacity-25 pt-3 d-flex justify-content-between align-items-center">
                            <span class="fs-15">Total Dibayarkan</span>
                            <h3 class="text-white mb-0" id="total-display">Rp 0</h3>
                        </div>
                    </div>
                </div>

                <div class="card border-info border">
                    <div class="card-body bg-info-subtle">
                        <div class="d-flex align-items-start">
                            <div class="shrink-0 me-3">
                                <i class="ri-information-line fs-1 text-info"></i>
                            </div>
                            <div>
                                <h6 class="text-info fw-semibold mb-1">Panduan Petugas</h6>
                                <ul class="text-muted mb-0 fs-13 ps-3">
                                    <li>Pastikan sampah sudah ditimbang dengan benar.</li>
                                    <li>Pembayaran dilakukan secara tunai setelah dicatat.</li>
                                    <li>Status pemilahan boleh diisi jika ingin dicatat sebagai informasi transaksi.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                @if($tarifItems->isEmpty())
                <div class="alert alert-warning">
                    <i class="ri-alert-line me-1"></i>
                    Belum ada tarif aktif. Minta Dev A untuk menambahkan tarif terlebih dahulu.
                </div>
                @endif
            </div>

        </div>
    </form>
</div>

{{-- Item row template (rendered by Blade, used by JS) --}}
<template id="item-row-template">
    <div class="item-row card border mb-2">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label form-label-sm mb-1">Item / Kategori</label>
                    <select name="items[__INDEX__][tarif_item_id]"
                            class="form-select form-select-sm tarif-select" required>
                        <option value="" disabled selected>-- Pilih jenis --</option>
                        @foreach($tarifItems as $t)
                        <option value="{{ $t->id }}"
                                data-harga="{{ $t->activeRate->harga_per_kg }}">
                            {{ $t->nama_item }}
                            (Rp {{ number_format($t->activeRate->harga_per_kg, 0, ',', '.') }}/kg)
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Berat (kg)</label>
                    <div class="input-group input-group-sm">
                        <input type="number" name="items[__INDEX__][berat_kg]"
                               class="form-control berat-input"
                               step="0.1" min="0.1" placeholder="0.0" required>
                        <span class="input-group-text">kg</span>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label form-label-sm mb-1">Status Pemilahan <span class="text-muted">(opsional)</span></label>
                    <select name="items[__INDEX__][status_pemilahan]" class="form-select form-select-sm pemilahan-select">
                        <option value="" selected>Tidak dicatat</option>
                        <option value="dipilah">Dipilah</option>
                        <option value="tidak_dipilah">Tidak dipilah</option>
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)" title="Hapus">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </div>
            </div>
            <div class="mt-2 text-end">
                <small class="text-muted">Subtotal: <span class="subtotal-display fw-semibold text-dark">Rp 0</span></small>
            </div>
        </div>
    </div>
</template>

@section('js')
<script>
    let itemCount = 0;
    const tarifData = @json($tarifJson);

    function addItem() {
        const template = document.getElementById('item-row-template');
        const html = template.innerHTML.replaceAll('__INDEX__', itemCount);

        const temp = document.createElement('div');
        temp.innerHTML = html;
        const row = temp.firstElementChild;

        row.querySelector('.tarif-select').addEventListener('change', recalc);
        row.querySelector('.berat-input').addEventListener('input', recalc);
        row.querySelector('.pemilahan-select').addEventListener('change', recalc);

        document.getElementById('items-container').appendChild(row);
        itemCount++;
        recalc();
    }

    function removeItem(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('Minimal satu item harus ada.');
            return;
        }
        btn.closest('.item-row').remove();
        recalc();
    }

    function recalc() {
        let total = 0;
        const summaryEl = document.getElementById('summary-items');
        const summaryLines = [];

        document.querySelectorAll('.item-row').forEach(row => {
            const select = row.querySelector('.tarif-select');
            const beratInput = row.querySelector('.berat-input');
            const subtotalEl = row.querySelector('.subtotal-display');

            const harga = select.value ? parseFloat(select.options[select.selectedIndex].dataset.harga || 0) : 0;
            const berat = parseFloat(beratInput.value || 0);
            const subtotal = harga * berat;

            total += subtotal;
            subtotalEl.textContent = 'Rp ' + formatRp(subtotal);

            if (select.value && berat > 0) {
                const nama = select.options[select.selectedIndex].text.split('(')[0].trim();
                summaryLines.push(`<div class="d-flex justify-content-between mb-1"><span class="text-white-50 small">${nama} ${berat} kg</span><span class="small">Rp ${formatRp(subtotal)}</span></div>`);
            }
        });

        document.getElementById('total-display').textContent = 'Rp ' + formatRp(total);
        summaryEl.innerHTML = summaryLines.length
            ? summaryLines.join('')
            : '<p class="text-white-50 mb-0 fs-13">Belum ada item ditambahkan.</p>';
    }

    function formatRp(n) {
        return Math.round(n).toLocaleString('id-ID');
    }

    // Add first item automatically on load
    addItem();
</script>
@endsection
@endsection
