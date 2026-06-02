@extends('partials.layouts.master')

@section('title', 'Input Setoran | SIPS')
@section('title-sub', 'Transaksi')
@section('pagetitle', 'Input Setoran Baru')

@section('content')
<style>
/* ── Shared touch sizing ────────────────────────────────────────────── */
.penyetor-card .form-control,
.penyetor-card .form-select,
.item-row     .form-control,
.item-row     .form-select {
    min-height: 44px;
    font-size: 1rem;
}
.penyetor-card .input-group-text { min-height: 44px; }

/* Mobile only */
@media (max-width: 767.98px) {
    .penyetor-card .form-control,
    .penyetor-card .form-select,
    .item-row     .form-control,
    .item-row     .form-select {
        min-height: 52px;
        font-size: 1.05rem;
    }
    .penyetor-card .input-group-text { min-height: 52px; }
    .btn-touch { min-height: 52px; min-width: 52px; }
    .pilah-btn { font-size: 0.95rem; }
    .warga-display { border: 2px solid #0d6efd !important; }
}

/* Sticky bottom bar — mobile only */
#sticky-bar {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    background: #0d6efd;
    color: #fff;
    padding: 10px 16px;
    z-index: 1040;
    display: none;
    align-items: center;
    gap: 12px;
    box-shadow: 0 -2px 12px rgba(0,0,0,.18);
}
#sticky-bar .bar-info { flex: 1; line-height: 1.2; }
#sticky-bar .bar-submit {
    background: #fff;
    color: #0d6efd;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 600;
    font-size: 1rem;
    white-space: nowrap;
}
@media (max-width: 767.98px) {
    #sticky-bar { display: flex; }
    #sidebar-estimasi { display: none !important; }
    .form-bottom-gap { padding-bottom: 80px; }
}
</style>

<div id="layout-wrapper">

    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <strong>Terdapat kesalahan:</strong>
        <ul class="mb-0 mt-1">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('sips.setoran.store') }}" id="form-setoran">
        @csrf
        <input type="hidden" name="mode" value="penyetor">

        <div class="row g-3">

            {{-- ── Main column ─────────────────────────────────────── --}}
            <div class="col-lg-8 form-bottom-gap">

                {{-- Tanggal --}}
                <div class="card mb-3">
                    <div class="card-body py-3">
                        <label for="tanggal_setoran" class="form-label fw-semibold mb-1">
                            <i class="ri-calendar-line me-1 text-primary"></i> Tanggal Pengangkutan
                            <span class="text-danger">*</span>
                        </label>
                        <input type="date"
                               class="form-control @error('tanggal_setoran') is-invalid @enderror"
                               id="tanggal_setoran" name="tanggal_setoran"
                               value="{{ old('tanggal_setoran', date('Y-m-d')) }}"
                               max="{{ date('Y-m-d') }}" required>
                        @error('tanggal_setoran')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Penyetor container --}}
                @error('penyetor')
                    <div class="alert alert-danger py-2 fs-13 mb-2">{{ $message }}</div>
                @enderror

                <div id="penyetor-container" class="mb-2"></div>

                <button type="button" class="btn btn-outline-primary w-100 btn-touch mb-3"
                        onclick="addPenyetor()">
                    <i class="ri-user-add-line me-2"></i> Tambah Penyetor
                </button>

                {{-- Catatan --}}
                <div class="card mb-3">
                    <div class="card-body py-3">
                        <label for="catatan_kondisi" class="form-label mb-1">
                            Catatan <span class="text-muted fs-12">(opsional)</span>
                        </label>
                        <textarea class="form-control" id="catatan_kondisi" name="catatan_kondisi"
                                  rows="2" maxlength="500"
                                  placeholder="Contoh: sampah sudah ditimbang, kondisi kering...">{{ old('catatan_kondisi') }}</textarea>
                    </div>
                </div>

                {{-- Desktop submit --}}
                <div class="d-flex justify-content-end gap-2 d-none d-lg-flex">
                    <a href="{{ route('sips.setoran.index') }}" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ri-save-line me-1"></i> Simpan Setoran
                    </button>
                </div>

            </div>

            {{-- ── Sidebar — desktop only ─────────────────────────── --}}
            <div class="col-lg-4 d-none d-lg-block" id="sidebar-estimasi">

                <div class="card bg-primary text-white mb-3">
                    <div class="card-body">
                        <h6 class="card-title text-white mb-3">
                            <i class="ri-calculator-line me-1"></i> Estimasi Total
                        </h6>
                        <div id="summary-content" class="mb-3">
                            <p class="text-white-50 mb-0 fs-13">Belum ada data.</p>
                        </div>
                        <div class="border-top border-white border-opacity-25 pt-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fs-13">Total kg</span>
                                <span class="fw-semibold" id="summary-kg">0 kg</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Nilai Bersih</span>
                                <h4 class="text-white mb-0" id="summary-nilai">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-info border">
                    <div class="card-body bg-info-subtle">
                        <h6 class="text-info fw-semibold mb-2">
                            <i class="ri-information-line me-1"></i> Panduan
                        </h6>
                        <ul class="text-muted mb-0 fs-13 ps-3">
                            <li>Tarif flat: <strong>Rp {{ number_format($tarifFlat, 0, ',', '.') }}/kg</strong></li>
                            <li><strong>Dipilah</strong> = warga <span class="text-success">menerima uang</span></li>
                            <li><strong>Tidak dipilah</strong> = warga <span class="text-danger">bayar iuran</span></li>
                            <li>Pilih <em>Jenis Item</em> untuk harga spesifik, atau kosongkan untuk flat.</li>
                            <li>Gunakan <i class="ri-search-line"></i> untuk cari warga terdaftar.</li>
                        </ul>
                    </div>
                </div>

                @if($tarifItems->isEmpty())
                <div class="alert alert-warning fs-13 mt-2">
                    <i class="ri-alert-line me-1"></i>
                    Belum ada tarif item aktif.
                    <a href="{{ route('sips.tarif.index') }}">Tambah di menu Tarif</a>.
                </div>
                @endif

            </div>

        </div>
    </form>
</div>

{{-- ── Sticky bottom bar (mobile) ───────────────────────────────────── --}}
<div id="sticky-bar">
    <div class="bar-info">
        <div class="fw-semibold fs-14" id="bar-nilai">Rp 0</div>
        <div class="fs-12 opacity-75" id="bar-kg">0 kg</div>
    </div>
    <button type="button" class="bar-submit" onclick="document.getElementById('form-setoran').submit()">
        <i class="ri-save-line me-1"></i> Simpan
    </button>
</div>

{{-- ── Warga search modal (shared) ──────────────────────────────────── --}}
<div class="modal fade" id="wargaModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down modal-sm">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-semibold">Cari Warga Terdaftar</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-2">
                <input type="search" class="form-control mb-2" id="warga-search-input"
                       placeholder="Ketik nama, RW, atau dusun..."
                       autocomplete="off"
                       oninput="filterWarga(this.value)">
                <div class="list-group list-group-flush" id="warga-list"></div>
            </div>
            <div class="modal-footer py-2 justify-content-start">
                <small class="text-muted">Atau ketik nama langsung di kolom penyetor</small>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════════════════
     DESKTOP TEMPLATES
     ═══════════════════════════════════════════════════════════════════ --}}

{{-- Desktop penyetor card — identical to original UI --}}
<template id="penyetor-tpl-desktop">
<div class="penyetor-card card border-primary border mb-3">
    <div class="card-header bg-primary-subtle py-2">
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary fs-13 penyetor-badge">P1</span>
            <span class="fw-semibold text-primary fs-13 flex-grow-1">Penyetor</span>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="removePenyetor(this)" title="Hapus penyetor">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>

        <input type="hidden" name="penyetor[__P__][warga_id]" class="warga-id-field" value="">

        <div class="input-group mb-2">
            <input type="text"
                   name="penyetor[__P__][nama_penyetor]"
                   class="form-control nama-field"
                   placeholder="Nama penyetor / usaha *" required
                   autocomplete="off"
                   oninput="recalc()"
                   onblur="onDesktopNamaBlur(this)">
            <button type="button" class="btn btn-primary"
                    onclick="openWargaSearch(this.closest('.penyetor-card'))"
                    title="Cari dari data warga">
                <i class="ri-search-line"></i>
            </button>
        </div>
        <div class="match-alert mb-1"></div>

        <div class="row g-2">
            <div class="col-6">
                <input type="text" name="penyetor[__P__][rw]"
                       class="form-control rw-field text-center"
                       placeholder="RW" maxlength="5">
            </div>
            <div class="col-6">
                <input type="text" name="penyetor[__P__][rt]"
                       class="form-control rt-field text-center"
                       placeholder="RT" maxlength="5">
            </div>
        </div>
    </div>

    <div class="item-container p-2 pb-0"></div>

    <div class="card-footer bg-transparent py-2">
        <button type="button" class="btn btn-sm btn-outline-secondary w-100"
                onclick="addItemRow(this.closest('.penyetor-card'))">
            <i class="ri-add-line me-1"></i> Tambah Jenis Item
        </button>
    </div>
</div>
</template>

{{-- Desktop item row — original layout with dropdown for status pilah --}}
<template id="item-tpl-desktop">
<div class="item-row border rounded p-2 mb-2">
    <input type="hidden" class="pilah-hidden" name="penyetor[__P__][items][__I__][status_pemilahan]" value="">
    <div class="row g-2">

        {{-- Status Pilah --}}
        <div class="col-6">
            <label class="form-label form-label-sm text-muted mb-1">Status Pilah *</label>
            <select class="form-select desktop-pilah"
                    onchange="applyPilahUI(this.closest('.item-row'), this.value)">
                <option value="" disabled selected>-- pilih --</option>
                <option value="dipilah">✓ Dipilah</option>
                <option value="tidak_dipilah">✗ Tidak Dipilah</option>
            </select>
        </div>

        {{-- Jenis Item --}}
        <div class="col-6">
            <label class="form-label form-label-sm text-muted mb-1">Jenis Item</label>
            <div class="jenis-wrapper d-none">
                <select name="penyetor[__P__][items][__I__][tarif_item_id]"
                        class="form-select tarif-select">
                    <option value="">— flat (Rp {{ number_format($tarifFlat, 0, ',', '.') }}/kg) —</option>
                    @foreach($tarifItems as $t)
                    <option value="{{ $t->id }}" data-harga="{{ $t->activeRate->harga_per_kg }}">
                        {{ $t->nama_item }} · Rp {{ number_format($t->activeRate->harga_per_kg, 0, ',', '.') }}/kg
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="tidak-note d-none pt-2 ps-1">
                <small class="text-muted"><i class="ri-arrow-right-s-line"></i> Iuran — tarif flat</small>
            </div>
        </div>

        {{-- Berat + subnilai + delete --}}
        <div class="col-7">
            <label class="form-label form-label-sm text-muted mb-1">Berat *</label>
            <div class="input-group">
                <input type="number"
                       name="penyetor[__P__][items][__I__][berat_kg]"
                       class="form-control berat-input"
                       step="0.1" min="0.1" placeholder="0.0"
                       inputmode="decimal" required>
                <span class="input-group-text">kg</span>
            </div>
        </div>

        <div class="col-5 d-flex flex-column align-items-end justify-content-end gap-1">
            <span class="subnilai-display fw-bold fs-13 text-muted">—</span>
            <button type="button" class="btn btn-sm btn-outline-danger"
                    onclick="removeItemRow(this)" title="Hapus item">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>

    </div>
</div>
</template>

{{-- ════════════════════════════════════════════════════════════════════
     MOBILE TEMPLATES
     ═══════════════════════════════════════════════════════════════════ --}}

{{-- Mobile penyetor card — 3-state warga picker --}}
<template id="penyetor-tpl-mobile">
<div class="penyetor-card card border-primary border mb-3">
    <div class="card-header bg-primary-subtle py-2">

        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary fs-13 penyetor-badge">P1</span>
            <span class="fw-semibold text-primary fs-13 flex-grow-1">Penyetor</span>
            <button type="button" class="btn btn-sm btn-outline-danger btn-touch"
                    onclick="removePenyetor(this)" title="Hapus penyetor">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>

        {{-- Hidden submit fields --}}
        <input type="hidden" name="penyetor[__P__][warga_id]"       class="warga-id-field" value="">
        <input type="hidden" name="penyetor[__P__][nama_penyetor]"  class="nama-field"     value="">
        <input type="hidden" name="penyetor[__P__][rw]"             class="rw-field"       value="">
        <input type="hidden" name="penyetor[__P__][rt]"             class="rt-field"       value="">

        {{-- State 1: pick --}}
        <div class="state-pick">
            <button type="button" class="btn btn-primary w-100 btn-touch mb-2"
                    onclick="openWargaSearch(this.closest('.penyetor-card'))">
                <i class="ri-user-search-line me-2"></i> Pilih Warga Terdaftar
            </button>
            <div class="text-center">
                <button type="button" class="btn btn-link text-muted p-0 fs-13"
                        onclick="switchToManual(this.closest('.penyetor-card'))">
                    <i class="ri-edit-2-line me-1"></i> Input manual (tamu / tidak terdaftar)
                </button>
            </div>
        </div>

        {{-- State 2: warga selected --}}
        <div class="state-selected d-none">
            <div class="warga-display d-flex align-items-start gap-2 p-2 rounded bg-white border">
                <i class="ri-user-3-fill text-primary mt-1 flex-shrink-0 fs-18"></i>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-semibold warga-nama"></div>
                    <small class="text-muted warga-info"></small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary flex-shrink-0"
                        onclick="clearWarga(this.closest('.penyetor-card'))">
                    <i class="ri-close-line me-1"></i> Ganti
                </button>
            </div>
        </div>

        {{-- State 3: manual input --}}
        <div class="state-manual d-none">
            <div class="position-relative mb-2">
                <div class="d-flex gap-1">
                    <input type="text" class="form-control nama-input"
                           placeholder="Nama penyetor *" autocomplete="off"
                           oninput="onManualNamaInput(this)"
                           onblur="onManualNamaBlur(this)">
                    <button type="button" class="btn btn-outline-primary btn-touch flex-shrink-0"
                            onclick="openWargaSearch(this.closest('.penyetor-card'))"
                            title="Cari dari data warga">
                        <i class="ri-search-line"></i>
                    </button>
                </div>
                <div class="typeahead-dropdown list-group shadow position-absolute w-100 d-none"
                     style="z-index:1050;top:calc(100% + 2px);left:0;max-height:220px;overflow-y:auto;border:1px solid #dee2e6;border-radius:0 0 6px 6px;background:#fff;"></div>
            </div>
            <div class="match-alert mb-2"></div>
            <div class="row g-2">
                <div class="col-6">
                    <input type="text" class="form-control text-center rw-input"
                           placeholder="RW" maxlength="5"
                           oninput="onManualRwInput(this)">
                    <div class="rw-hint fs-12 mt-1 d-none"></div>
                </div>
                <div class="col-6">
                    <input type="text" class="form-control text-center"
                           placeholder="RT" maxlength="5"
                           oninput="syncManual(this, 'rt-field')">
                </div>
            </div>
        </div>

    </div>

    <div class="item-container p-2 pb-0"></div>

    <div class="card-footer bg-transparent py-2">
        <button type="button" class="btn btn-sm btn-outline-secondary btn-touch w-100"
                onclick="addItemRow(this.closest('.penyetor-card'))">
            <i class="ri-add-line me-1"></i> Tambah Jenis Item
        </button>
    </div>
</div>
</template>

{{-- Mobile item row — toggle buttons for status pilah --}}
<template id="item-tpl-mobile">
<div class="item-row border rounded p-2 mb-2">

    {{-- Status Pilah toggle --}}
    <div class="mb-2">
        <div class="fs-12 text-muted mb-1">Status Pilah *</div>
        <input type="hidden" name="penyetor[__P__][items][__I__][status_pemilahan]" class="pilah-hidden" value="">
        <div class="d-flex gap-1">
            <button type="button" class="btn btn-outline-success flex-fill btn-touch pilah-btn"
                    data-value="dipilah"
                    onclick="setPilah(this, 'dipilah')">
                <i class="ri-check-line me-1"></i> Dipilah
            </button>
            <button type="button" class="btn btn-outline-danger flex-fill btn-touch pilah-btn"
                    data-value="tidak_dipilah"
                    onclick="setPilah(this, 'tidak_dipilah')">
                <i class="ri-close-line me-1"></i> Tidak Dipilah
            </button>
        </div>
    </div>

    {{-- Jenis item (shown when dipilah) --}}
    <div class="jenis-wrapper mb-2 d-none">
        <div class="fs-12 text-muted mb-1">Jenis Item</div>
        <select name="penyetor[__P__][items][__I__][tarif_item_id]"
                class="form-select tarif-select">
            <option value="">— flat (Rp {{ number_format($tarifFlat, 0, ',', '.') }}/kg) —</option>
            @foreach($tarifItems as $t)
            <option value="{{ $t->id }}" data-harga="{{ $t->activeRate->harga_per_kg }}">
                {{ $t->nama_item }} · Rp {{ number_format($t->activeRate->harga_per_kg, 0, ',', '.') }}/kg
            </option>
            @endforeach
        </select>
    </div>

    {{-- Tidak dipilah note --}}
    <div class="tidak-note mb-2 d-none">
        <small class="text-danger fs-12">
            <i class="ri-information-line me-1"></i> Iuran — tarif flat dikenakan
        </small>
    </div>

    {{-- Berat + subnilai + delete --}}
    <div class="d-flex align-items-end gap-2">
        <div class="flex-grow-1">
            <div class="fs-12 text-muted mb-1">Berat *</div>
            <div class="input-group">
                <input type="number"
                       name="penyetor[__P__][items][__I__][berat_kg]"
                       class="form-control berat-input"
                       step="0.1" min="0.1" placeholder="0.0"
                       inputmode="decimal" required>
                <span class="input-group-text">kg</span>
            </div>
        </div>
        <div class="d-flex flex-column align-items-end gap-1">
            <span class="subnilai-display fw-bold fs-13 text-muted">—</span>
            <button type="button" class="btn btn-sm btn-outline-danger btn-touch"
                    onclick="removeItemRow(this)" title="Hapus item">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    </div>

</div>
</template>

@section('js')
<script>
const TARIF            = {{ $tarifFlat }};
const wargaData        = @json($wargaJson);
const isMobile         = window.innerWidth < 768;
const IS_ADMIN         = {{ auth()->user()?->isAdmin() ? 'true' : 'false' }};
const WARGA_CREATE_URL = "{{ route('sips.warga.create') }}";
let pIdx        = 0;
let activeCard  = null;

// ── Warga search ─────────────────────────────────────────────────────

function openWargaSearch(card) {
    activeCard = card;
    const input = document.getElementById('warga-search-input');
    input.value = '';
    filterWarga('');
    new bootstrap.Modal(document.getElementById('wargaModal')).show();
    setTimeout(() => input.focus(), 350);
}

function filterWarga(q) {
    q = q.toLowerCase().trim();
    const list = document.getElementById('warga-list');
    const hits  = q
        ? wargaData.filter(w =>
            w.nama.toLowerCase().includes(q) ||
            String(w.rw).includes(q) ||
            (w.dusun && w.dusun.toLowerCase().includes(q)))
        : wargaData;

    list.innerHTML = hits.slice(0, 30).map(w => `
        <button type="button"
                class="list-group-item list-group-item-action py-3"
                onclick="selectWarga(${w.id})">
            <div class="fw-semibold">${w.nama}</div>
            <small class="text-muted">RT ${w.rt} · RW ${w.rw}${w.dusun ? ' — ' + w.dusun : ''}</small>
        </button>`).join('');

    if (!hits.length) {
        list.innerHTML = '<p class="text-center text-muted py-4 mb-0 fs-13">Tidak ada warga yang cocok.</p>';
    }
}

function selectWarga(id) {
    if (!activeCard) return;
    const w = wargaData.find(w => w.id === id);
    if (!w) return;

    // warga_id is hidden in both templates
    activeCard.querySelector('.warga-id-field').value = w.id;

    // .nama-field / .rw-field / .rt-field are:
    //   desktop → visible text inputs (with name attrs) — setting value updates the UI
    //   mobile  → hidden inputs — need separate display update below
    activeCard.querySelector('.nama-field').value = w.nama;
    activeCard.querySelector('.rw-field').value   = w.rw;
    activeCard.querySelector('.rt-field').value   = w.rt;

    // Mobile-only: update display card and switch state
    const statePick = activeCard.querySelector('.state-pick');
    if (statePick) {
        activeCard.querySelector('.warga-nama').textContent = w.nama;
        activeCard.querySelector('.warga-info').textContent =
            `RT ${w.rt} · RW ${w.rw}${w.dusun ? ' — ' + w.dusun : ''}`;
        statePick.classList.add('d-none');
        activeCard.querySelector('.state-manual')?.classList.add('d-none');
        activeCard.querySelector('.state-selected').classList.remove('d-none');
    }

    bootstrap.Modal.getInstance(document.getElementById('wargaModal')).hide();
    recalc();
}

// Mobile-only: "Ganti" button clears selected warga
function clearWarga(card) {
    card.querySelector('.warga-id-field').value = '';
    card.querySelector('.nama-field').value     = '';
    card.querySelector('.rw-field').value       = '';
    card.querySelector('.rt-field').value       = '';

    card.querySelector('.state-selected').classList.add('d-none');
    card.querySelector('.state-pick').classList.remove('d-none');
    recalc();
}

// Mobile-only: switch to manual text input
function switchToManual(card) {
    card.querySelector('.state-pick').classList.add('d-none');
    card.querySelector('.state-manual').classList.remove('d-none');
    setTimeout(() => card.querySelector('.nama-input').focus(), 100);
}

// Mobile-only: sync visible manual inputs → hidden submit fields
function syncManual(input, fieldClass) {
    input.closest('.penyetor-card').querySelector('.' + fieldClass).value = input.value;
    recalc();
}

// ── Penyetor management ─────────────────────────────────────────────

function addPenyetor() {
    const tplId = isMobile ? 'penyetor-tpl-mobile' : 'penyetor-tpl-desktop';
    const tpl   = document.getElementById(tplId);
    const html  = tpl.innerHTML.replaceAll('__P__', pIdx);
    const temp  = document.createElement('div');
    temp.innerHTML = html;
    const card = temp.firstElementChild;

    card.dataset.pidx = pIdx;
    card.dataset.iidx = 0;

    document.getElementById('penyetor-container').appendChild(card);
    pIdx++;
    renumberCards();
    addItemRow(card);
}

function removePenyetor(btn) {
    if (document.querySelectorAll('.penyetor-card').length <= 1) {
        alert('Minimal satu penyetor harus ada.');
        return;
    }
    btn.closest('.penyetor-card').remove();
    renumberCards();
    recalc();
}

function renumberCards() {
    document.querySelectorAll('.penyetor-card').forEach((card, i) => {
        card.querySelector('.penyetor-badge').textContent = `P${i + 1}`;
    });
}

// ── Item management ─────────────────────────────────────────────────

function addItemRow(card) {
    const pidx  = card.dataset.pidx;
    const iidx  = parseInt(card.dataset.iidx || 0);
    const tplId = isMobile ? 'item-tpl-mobile' : 'item-tpl-desktop';

    const tpl  = document.getElementById(tplId);
    const html = tpl.innerHTML
        .replaceAll('__P__', pidx)
        .replaceAll('__I__', iidx);

    const temp = document.createElement('div');
    temp.innerHTML = html;
    const row = temp.firstElementChild;

    row.querySelector('.berat-input').addEventListener('input', recalc);
    row.querySelector('.tarif-select').addEventListener('change', recalc);

    card.querySelector('.item-container').appendChild(row);
    card.dataset.iidx = iidx + 1;
    recalc();
}

function removeItemRow(btn) {
    const card = btn.closest('.penyetor-card');
    if (card.querySelectorAll('.item-row').length <= 1) {
        alert('Minimal satu item per penyetor.');
        return;
    }
    btn.closest('.item-row').remove();
    recalc();
}

// ── Pilah selection ──────────────────────────────────────────────────

// Mobile toggle button handler
function setPilah(btn, value) {
    const row = btn.closest('.item-row');

    row.querySelectorAll('.pilah-btn').forEach(b => {
        const active = b.dataset.value === value;
        b.className = b.dataset.value === 'dipilah'
            ? `btn flex-fill btn-touch pilah-btn ${active ? 'btn-success' : 'btn-outline-success'}`
            : `btn flex-fill btn-touch pilah-btn ${active ? 'btn-danger'  : 'btn-outline-danger'}`;
    });

    applyPilahUI(row, value);
}

// Shared: sync pilah value + show/hide jenis & note (works for both templates)
function applyPilahUI(row, value) {
    row.querySelector('.pilah-hidden').value = value;

    const jenisWrapper = row.querySelector('.jenis-wrapper');
    const tidakNote    = row.querySelector('.tidak-note');
    const tarifSel     = row.querySelector('.tarif-select');

    if (value === 'dipilah') {
        jenisWrapper?.classList.remove('d-none');
        tidakNote?.classList.add('d-none');
    } else {
        jenisWrapper?.classList.add('d-none');
        tidakNote?.classList.remove('d-none');
        if (tarifSel) tarifSel.value = '';
    }

    recalc();
}

// ── Recalc ──────────────────────────────────────────────────────────

function recalc() {
    let grandKg = 0, grandNilai = 0;
    const lines = [];

    document.querySelectorAll('.penyetor-card').forEach(card => {
        // .nama-field is visible on desktop, hidden on mobile — value is always set
        const nama = (card.querySelector('.nama-field')?.value || '').trim() || '(belum diisi)';
        let cardKg = 0, cardNilai = 0;

        card.querySelectorAll('.item-row').forEach(row => {
            const pilah    = row.querySelector('.pilah-hidden').value;
            const tarifSel = row.querySelector('.tarif-select');
            const berat    = parseFloat(row.querySelector('.berat-input').value || 0);
            const subEl    = row.querySelector('.subnilai-display');

            if (!pilah || berat <= 0) {
                subEl.textContent = '—';
                subEl.className   = 'subnilai-display fw-bold fs-13 text-muted';
                return;
            }

            const dipilah = pilah === 'dipilah';
            let harga     = TARIF;

            if (dipilah && tarifSel.value) {
                harga = parseFloat(tarifSel.options[tarifSel.selectedIndex]?.dataset.harga || TARIF);
            }

            const sub  = berat * harga * (dipilah ? 1 : -1);
            cardKg    += berat;
            cardNilai += sub;

            subEl.textContent = (sub >= 0 ? '+' : '−') + 'Rp ' + fmt(Math.abs(sub));
            subEl.className   = 'subnilai-display fw-bold fs-13 ' + (dipilah ? 'text-success' : 'text-danger');
        });

        grandKg    += cardKg;
        grandNilai += cardNilai;

        if (cardKg > 0) {
            lines.push(
                `<div class="d-flex justify-content-between mb-1">
                    <span class="text-white-50 small">${nama} (${round1(cardKg)} kg)</span>
                    <span class="small ${cardNilai >= 0 ? 'text-success' : 'text-warning'}">
                        ${cardNilai >= 0 ? '+' : '−'}Rp ${fmt(Math.abs(cardNilai))}
                    </span>
                </div>`
            );
        }
    });

    const summaryEl = document.getElementById('summary-content');
    if (summaryEl) {
        summaryEl.innerHTML = lines.length
            ? lines.join('')
            : '<p class="text-white-50 mb-0 fs-13">Belum ada data.</p>';
    }
    const kgEl = document.getElementById('summary-kg');
    if (kgEl) kgEl.textContent = grandKg > 0 ? `${round1(grandKg)} kg` : '0 kg';
    const nilaiEl = document.getElementById('summary-nilai');
    if (nilaiEl) {
        nilaiEl.textContent = (grandNilai >= 0 ? '+' : '−') + `Rp ${fmt(Math.abs(grandNilai))}`;
        nilaiEl.className   = 'mb-0 ' + (grandNilai >= 0 ? 'text-white' : 'text-warning');
    }

    document.getElementById('bar-kg').textContent    = `${round1(grandKg)} kg`;
    document.getElementById('bar-nilai').textContent = (grandNilai >= 0 ? '+' : '−') + `Rp ${fmt(Math.abs(grandNilai))}`;
}

function round1(n) { return Math.round(n * 10) / 10; }
function fmt(n)    { return Math.round(n).toLocaleString('id-ID'); }

// ── HTML escape utility ──────────────────────────────────────────────

function escHtml(s) {
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ── Manual input: typeahead ──────────────────────────────────────────

function onManualNamaInput(input) {
    syncManual(input, 'nama-field');
    showTypeahead(input);
}

function showTypeahead(input) {
    const card     = input.closest('.penyetor-card');
    const dropdown = card.querySelector('.typeahead-dropdown');
    if (!dropdown) return;

    const q = input.value.trim().toLowerCase();
    if (q.length < 2) { dropdown.classList.add('d-none'); return; }

    const hits = wargaData.filter(w => w.nama.toLowerCase().includes(q)).slice(0, 6);
    if (!hits.length) { dropdown.classList.add('d-none'); return; }

    dropdown.innerHTML = hits.map(w => `
        <button type="button"
                class="list-group-item list-group-item-action py-2 px-3"
                onmousedown="event.preventDefault()"
                onclick="selectWargaInline(this, ${w.id})">
            <div class="fw-semibold fs-13">${escHtml(w.nama)}</div>
            <small class="text-muted">RT ${escHtml(String(w.rt))} · RW ${escHtml(String(w.rw))}${w.dusun ? ' — ' + escHtml(w.dusun) : ''}</small>
        </button>`).join('');
    dropdown.classList.remove('d-none');
}

function selectWargaInline(btn, wargaId) {
    activeCard = btn.closest('.penyetor-card');
    selectWarga(wargaId);
    activeCard.querySelector('.typeahead-dropdown')?.classList.add('d-none');
}

// ── Manual input: blur → match warning / register hint ───────────────

function onManualNamaBlur(input) {
    setTimeout(() => {
        const card = input.closest('.penyetor-card');
        card.querySelector('.typeahead-dropdown')?.classList.add('d-none');
        showMatchWarning(card, input.value.trim());
    }, 200);
}

function onDesktopNamaBlur(input) {
    setTimeout(() => showMatchWarning(input.closest('.penyetor-card'), input.value.trim()), 200);
}

function showMatchWarning(card, nama) {
    const alertEl = card.querySelector('.match-alert');
    if (!alertEl) return;

    // Already linked to a warga — clear any warning
    if (card.querySelector('.warga-id-field')?.value) {
        alertEl.innerHTML = ''; return;
    }
    if (!nama || nama.length < 2) { alertEl.innerHTML = ''; return; }

    const nameLower = nama.toLowerCase();
    const match = wargaData.find(w =>
        w.nama.toLowerCase().includes(nameLower) ||
        nameLower.includes(w.nama.toLowerCase())
    );

    if (match) {
        alertEl.innerHTML = `
            <div class="alert alert-warning py-2 px-3 fs-13 mb-0">
                <i class="ri-user-search-line me-1"></i>
                Warga serupa: <strong>${escHtml(match.nama)}</strong>
                <span class="text-muted">(RT ${match.rt} · RW ${match.rw})</span>
                <div class="mt-2 d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-warning btn-sm"
                            onclick="selectWargaFromAlert(this, ${match.id})">
                        <i class="ri-check-line me-1"></i> Gunakan data ini
                    </button>
                    <button type="button" class="btn btn-link btn-sm text-muted px-0"
                            onclick="this.closest('.match-alert').innerHTML=''">
                        Abaikan
                    </button>
                </div>
            </div>`;
    } else if (IS_ADMIN) {
        const params = new URLSearchParams({
            nama,
            rt: card.querySelector('.rt-field')?.value || '',
            rw: card.querySelector('.rw-field')?.value || '',
        });
        alertEl.innerHTML = `
            <div class="d-flex align-items-center gap-2 py-1 px-2 fs-12 text-muted border rounded">
                <i class="ri-information-line text-secondary flex-shrink-0"></i>
                <span>Tidak ditemukan di data warga.</span>
                <a href="${WARGA_CREATE_URL}?${params}" target="_blank"
                   class="ms-auto text-primary fw-semibold text-decoration-none text-nowrap">
                    Daftarkan ↗
                </a>
            </div>`;
    } else {
        alertEl.innerHTML = '';
    }
}

function selectWargaFromAlert(btn, wargaId) {
    activeCard = btn.closest('.penyetor-card');
    selectWarga(wargaId);
    activeCard.querySelector('.match-alert').innerHTML = '';
}

// ── Manual input: RW hint ────────────────────────────────────────────

function onManualRwInput(input) {
    syncManual(input, 'rw-field');

    const rw     = input.value.trim();
    const hintEl = input.closest('.col-6')?.querySelector('.rw-hint');
    if (!hintEl) return;

    if (!rw) { hintEl.classList.add('d-none'); return; }

    const rwNorm = rw.padStart(2, '0');
    const count  = wargaData.filter(w =>
        String(w.rw) === rw || String(w.rw).padStart(2, '0') === rwNorm
    ).length;

    hintEl.textContent = count > 0
        ? `${count} warga terdaftar di RW ini`
        : 'RW ini belum ada di data warga';
    hintEl.className = `fs-12 mt-1 ${count > 0 ? 'text-success' : 'text-warning'}`;
}

// ── Form submit validation ───────────────────────────────────────────

document.getElementById('form-setoran').addEventListener('submit', function(e) {
    for (const card of document.querySelectorAll('.penyetor-card')) {
        if (!(card.querySelector('.nama-field')?.value || '').trim()) {
            e.preventDefault();
            alert('Pilih warga atau isi nama penyetor untuk setiap kartu penyetor.');
            return;
        }
        for (const row of card.querySelectorAll('.item-row')) {
            if (!row.querySelector('.pilah-hidden').value) {
                e.preventDefault();
                alert('Pilih status Dipilah atau Tidak Dipilah untuk semua item.');
                return;
            }
        }
    }
});

// ── Init ────────────────────────────────────────────────────────────
addPenyetor();
</script>
@endsection
@endsection
