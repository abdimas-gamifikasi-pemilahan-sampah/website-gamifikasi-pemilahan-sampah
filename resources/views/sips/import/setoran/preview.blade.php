@extends('partials.layouts.master')

@section('title', 'Preview Import | SIPS')
@section('title-sub', 'Import')
@section('pagetitle', 'Preview Data Import')

@section('content')
<div id="layout-wrapper">

    {{-- Summary banner --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 bg-primary-subtle">
                <div class="card-body py-3">
                    <div class="fs-24 fw-bold text-primary">{{ $totalRows }}</div>
                    <div class="text-muted fs-13">Total Baris</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 bg-success-subtle">
                <div class="card-body py-3">
                    <div class="fs-24 fw-bold text-success" id="validCount">{{ $validCount }}</div>
                    <div class="text-muted fs-13">Baris Valid</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 {{ $errorCount > 0 ? 'bg-danger-subtle' : 'bg-light' }}">
                <div class="card-body py-3">
                    <div class="fs-24 fw-bold {{ $errorCount > 0 ? 'text-danger' : 'text-muted' }}" id="errorCount">{{ $errorCount }}</div>
                    <div class="text-muted fs-13">Baris Error</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 bg-info-subtle">
                <div class="card-body py-3">
                    <div class="fs-14 fw-bold text-info text-capitalize">{{ $format }}</div>
                    <div class="text-muted fs-13">Format Terdeteksi</div>
                </div>
            </div>
        </div>
    </div>

    @if($errorCount > 0)
    <div class="alert alert-warning mb-4">
        <i class="ri-alert-line me-1"></i>
        <strong>{{ $errorCount }} baris bermasalah.</strong>
        Edit langsung di tabel untuk memperbaiki, lalu konfirmasi kembali.
    </div>
    @endif

    @if($validCount === 0)
    <div class="alert alert-danger mb-4">
        <i class="ri-error-warning-line me-1"></i>
        <strong>Tidak ada baris yang valid.</strong> Edit data di bawah atau upload ulang file.
    </div>
    @endif

    {{-- Confirm form --}}
    <form method="POST" action="{{ route('sips.import.setoran.confirm') }}" id="confirmForm">
        @csrf
        <input type="hidden" name="tanggal_setoran" value="{{ $tanggalSetoran }}">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-0">
                        Preview Data — Format: <span class="text-primary text-capitalize">{{ $format }}</span>
                        <span class="badge bg-light text-muted border fs-12 ms-2">
                            <i class="ri-edit-line me-1"></i>Klik sel untuk edit
                        </span>
                    </h5>
                    @if($format === 'detail')
                    <small class="text-muted">
                        Semua baris akan dijadikan SATU setoran — tanggal: <strong>{{ \Carbon\Carbon::parse($tanggalSetoran)->translatedFormat('d F Y') }}</strong>
                    </small>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sips.import.setoran.index') }}" class="btn btn-light btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Kembali / Upload Ulang
                    </a>
                    <button type="submit" class="btn btn-success btn-sm" id="btnConfirm">
                        <i class="ri-check-line me-1"></i> Konfirmasi Import
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" id="previewTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:36px">#</th>
                                @if($format === 'perolehan')
                                    <th>Tanggal</th><th>RW</th><th>Jumlah Sampah (kg)</th>
                                @elseif($format === 'rivan')
                                    <th>Tanggal</th><th>Jumlah Sampah (kg)</th>
                                @else
                                    <th>Nama Penyetor</th>
                                    <th style="width:70px">RW</th>
                                    <th style="width:60px">RT</th>
                                    <th style="width:110px">Berat (kg)</th>
                                    <th style="width:150px">Status Pilah</th>
                                @endif
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $i => $row)
                            @php $hasError = !empty($row['errors']); @endphp
                            <tr class="{{ $hasError ? 'table-danger row-error' : 'row-valid' }}" data-index="{{ $i }}">
                                <td class="text-muted">{{ $i + 1 }}</td>

                                @if($format === 'perolehan')
                                    <td>
                                        <input type="text" name="rows[{{ $i }}][tanggal]"
                                               class="edit-input" value="{{ $row['tanggal'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" name="rows[{{ $i }}][area_rw]"
                                               class="edit-input edit-input-sm" value="{{ $row['area_rw'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="number" name="rows[{{ $i }}][kg]"
                                               class="edit-input edit-input-sm" value="{{ $row['kg'] ?? '' }}"
                                               step="0.1" min="0" oninput="recount()">
                                    </td>

                                @elseif($format === 'rivan')
                                    <td>
                                        <input type="text" name="rows[{{ $i }}][tanggal]"
                                               class="edit-input" value="{{ $row['tanggal'] ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="number" name="rows[{{ $i }}][kg]"
                                               class="edit-input edit-input-sm" value="{{ $row['kg'] ?? '' }}"
                                               step="0.1" min="0" oninput="recount()">
                                    </td>

                                @else
                                    {{-- Detail: warga selector --}}
                                    <td style="min-width:240px;">
                                        @php $matched = ($row['warga_id'] ?? null) !== null; @endphp

                                        {{-- Nama asli dari Excel --}}
                                        <div class="fw-semibold fs-13 mb-1">{{ $row['nama_penyetor'] ?? '—' }}</div>

                                        {{-- Dropdown (tersembunyi sampai diklik) --}}
                                        <select name="rows[{{ $i }}][warga_id]"
                                                id="warga_sel_{{ $i }}"
                                                class="edit-select w-100 {{ $matched ? '' : 'd-none' }}"
                                                onchange="onWargaChange({{ $i }}, this)">
                                            <option value="">— Pilih warga terdaftar —</option>
                                            @foreach($wargaList as $w)
                                            <option value="{{ $w->id }}"
                                                    data-nama="{{ $w->nama }}"
                                                    data-rt="{{ (int)$w->rt }}"
                                                    data-rw="{{ (int)$w->rw }}"
                                                    {{ ($row['warga_id'] ?? null) == $w->id ? 'selected' : '' }}>
                                                {{ $w->nama }} (RT {{ (int)$w->rt }}/RW {{ (int)$w->rw }})
                                            </option>
                                            @endforeach
                                        </select>

                                        <input type="hidden" name="rows[{{ $i }}][nama_penyetor]"
                                               id="nama_hidden_{{ $i }}"
                                               value="{{ $row['nama_penyetor'] ?? '' }}">

                                        <div class="mt-1 d-flex align-items-center gap-2 flex-wrap" id="warga_actions_{{ $i }}">
                                            @if(!$matched)
                                                <span class="text-warning fs-11" id="warga_status_{{ $i }}">
                                                    <i class="ri-alert-line"></i> Belum terdaftar
                                                </span>
                                                <button type="button"
                                                        id="btn_pilih_{{ $i }}"
                                                        class="btn btn-link fs-11 p-0 text-secondary"
                                                        onclick="showWargaDropdown({{ $i }})">
                                                    <i class="ri-search-line"></i> Pilih dari warga terdaftar
                                                </button>
                                                <button type="button"
                                                        id="btn_daftar_{{ $i }}"
                                                        class="btn btn-link fs-11 p-0 text-primary"
                                                        onclick="openRegisterModal({{ $i }}, '{{ addslashes($row['nama_penyetor'] ?? '') }}')">
                                                    <i class="ri-user-add-line"></i> Daftar warga baru
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="rows[{{ $i }}][rw]"
                                               id="rw_{{ $i }}"
                                               class="edit-input edit-input-sm" value="{{ $row['rw'] ? (int)$row['rw'] : '' }}"
                                               placeholder="—">
                                    </td>
                                    <td>
                                        <input type="text" name="rows[{{ $i }}][rt]"
                                               id="rt_{{ $i }}"
                                               class="edit-input edit-input-sm" value="{{ $row['rt'] ? (int)$row['rt'] : '' }}"
                                               placeholder="—">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <input type="number" name="rows[{{ $i }}][berat_kg]"
                                                   class="edit-input edit-input-sm" value="{{ $row['berat_kg'] ?? '' }}"
                                                   step="0.01" min="0.01" placeholder="0.00" oninput="recount()">
                                            <span class="text-muted fs-12">kg</span>
                                        </div>
                                    </td>
                                    <td>
                                        <select name="rows[{{ $i }}][status_pemilahan]" class="edit-select">
                                            <option value="dipilah"       {{ ($row['status_pemilahan'] ?? '') === 'dipilah'       ? 'selected' : '' }}>Dipilah</option>
                                            <option value="tidak_dipilah" {{ ($row['status_pemilahan'] ?? '') === 'tidak_dipilah' ? 'selected' : '' }}>Tidak Dipilah</option>
                                        </select>
                                    </td>
                                @endif

                                <td class="status-cell">
                                    @if($hasError)
                                        @foreach($row['errors'] as $err)
                                            <div class="text-danger fs-12"><i class="ri-error-warning-line me-1"></i>{{ $err }}</div>
                                        @endforeach
                                    @else
                                        @foreach($row['warnings'] as $warn)
                                            <div class="text-warning fs-12"><i class="ri-alert-line me-1"></i>{{ $warn }}</div>
                                        @endforeach
                                        @if(empty($row['warnings']))
                                            <span class="text-success fs-12"><i class="ri-checkbox-circle-line me-1"></i>Valid</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center gap-2">
                <span class="text-muted fs-12">
                    <i class="ri-information-line me-1"></i>
                    Edit langsung di tabel jika ada data yang perlu diperbaiki, lalu klik Simpan.
                </span>
                <div class="d-flex gap-2">
                    <a href="{{ route('sips.import.setoran.index') }}" class="btn btn-light">Batal</a>
                    <button type="submit" class="btn btn-success" id="btnConfirmFooter">
                        <i class="ri-check-double-line me-1"></i>
                        Simpan ke Database
                    </button>
                </div>
            </div>
        </div>
    </form>

</div>

{{-- ── Modal Daftarkan Warga Baru ── --}}
<div class="modal fade" id="modalDaftarWarga" tabindex="-1" aria-labelledby="modalDaftarWargaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDaftarWargaLabel">
                    <i class="ri-user-add-line me-2"></i>Daftarkan Warga Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalAlert" class="alert alert-danger py-2 fs-13 d-none"></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="reg_nama" placeholder="Nama sesuai KK">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold">RT <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="reg_rt" min="1" max="99" placeholder="Contoh: 2">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">RW <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="reg_rw" min="1" max="99" placeholder="Contoh: 6">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dusun <span class="text-muted fs-12">(opsional)</span></label>
                    <input type="text" class="form-control" id="reg_dusun" placeholder="Nama dusun">
                </div>
                <div class="mb-0">
                    <label class="form-label">No. KK <span class="text-muted fs-12">(16 digit, opsional)</span></label>
                    <input type="text" class="form-control" id="reg_nokk" maxlength="16" placeholder="Kosongkan jika tidak ada">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnRegWarga" onclick="submitRegister()">
                    <i class="ri-save-line me-1"></i> Daftarkan & Pilih
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<style>
.edit-input {
    border: 1px solid transparent;
    background: transparent;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 13px;
    transition: border-color .15s, background .15s;
    width: 100%;
    min-width: 80px;
}
.edit-input:hover { border-color: #ced4da; background: #fff; }
.edit-input:focus { border-color: #86b7fe; background: #fff; outline: none; box-shadow: 0 0 0 2px rgba(13,110,253,.15); }

.edit-input-sm { min-width: 45px; max-width: 70px; }

.edit-select {
    border: 1px solid transparent;
    background: transparent;
    border-radius: 4px;
    padding: 2px 4px;
    font-size: 13px;
    transition: border-color .15s, background .15s;
    cursor: pointer;
}
.edit-select:hover { border-color: #ced4da; background: #fff; }
.edit-select:focus { border-color: #86b7fe; background: #fff; outline: none; }

/* Highlight edited cells */
.edit-input.edited, .edit-select.edited {
    border-color: #ffc107 !important;
    background: #fffdf0 !important;
}
</style>

<script>
const originalValues = {};

document.addEventListener('DOMContentLoaded', () => {
    // Snapshot original values
    document.querySelectorAll('.edit-input, .edit-select').forEach(el => {
        originalValues[el.name] = el.value;
        el.addEventListener('input', () => markEdited(el));
        el.addEventListener('change', () => markEdited(el));
    });
});

function markEdited(el) {
    if (el.value !== (originalValues[el.name] ?? '')) {
        el.classList.add('edited');
    } else {
        el.classList.remove('edited');
    }
    recount();
}

let activeRowIndex = null;

function showWargaDropdown(i) {
    const sel = document.getElementById('warga_sel_' + i);
    const btn = document.getElementById('btn_pilih_' + i);
    if (sel) sel.classList.remove('d-none');
    if (btn) btn.classList.add('d-none');
    if (sel) sel.focus();
}

function onWargaChange(i, select) {
    const opt        = select.options[select.selectedIndex];
    const actionsEl  = document.getElementById('warga_actions_' + i);
    const namaHidden = document.getElementById('nama_hidden_' + i);
    const rtEl       = document.getElementById('rt_' + i);
    const rwEl       = document.getElementById('rw_' + i);

    if (select.value) {
        // Warga terpilih — autofill, sembunyikan semua tombol
        if (namaHidden) namaHidden.value = opt.dataset.nama ?? '';
        if (rtEl && opt.dataset.rt) rtEl.value = opt.dataset.rt;
        if (rwEl && opt.dataset.rw) rwEl.value = opt.dataset.rw;
        if (actionsEl) actionsEl.innerHTML = '';
    } else {
        // Tidak ada warga dipilih — tampilkan kembali tombol
        if (actionsEl) actionsEl.innerHTML =
            `<span class="text-warning fs-11"><i class="ri-alert-line"></i> Belum terdaftar</span>
             <button type="button" id="btn_pilih_${i}" class="btn btn-link fs-11 p-0 text-secondary" onclick="showWargaDropdown(${i})">
                 <i class="ri-search-line"></i> Pilih dari warga terdaftar
             </button>
             <button type="button" id="btn_daftar_${i}" class="btn btn-link fs-11 p-0 text-primary" onclick="openRegisterModal(${i}, '')">
                 <i class="ri-user-add-line"></i> Daftar warga baru
             </button>`;
    }
    recount();
}

function openRegisterModal(rowIndex, namaSuggest) {
    activeRowIndex = rowIndex;
    document.getElementById('reg_nama').value  = namaSuggest || '';
    const rtEl = document.getElementById('rt_' + rowIndex);
    const rwEl = document.getElementById('rw_' + rowIndex);
    document.getElementById('reg_rt').value    = rtEl ? (parseInt(rtEl.value) || '') : '';
    document.getElementById('reg_rw').value    = rwEl ? (parseInt(rwEl.value) || '') : '';
    document.getElementById('reg_dusun').value = '';
    document.getElementById('reg_nokk').value  = '';
    document.getElementById('modalAlert').classList.add('d-none');
    new bootstrap.Modal(document.getElementById('modalDaftarWarga')).show();
}

async function submitRegister() {
    const btn = document.getElementById('btnRegWarga');
    const alertEl = document.getElementById('modalAlert');
    alertEl.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    try {
        const res = await fetch('{{ route("sips.warga.quick-store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                nama:  document.getElementById('reg_nama').value,
                rt:    document.getElementById('reg_rt').value,
                rw:    document.getElementById('reg_rw').value,
                dusun: document.getElementById('reg_dusun').value,
                no_kk: document.getElementById('reg_nokk').value || null,
            }),
        });

        const data = await res.json();

        if (!res.ok) {
            const msgs = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message ?? 'Gagal menyimpan.');
            alertEl.textContent = msgs;
            alertEl.classList.remove('d-none');
            return;
        }

        // Tambah option ke dropdown baris yang aktif dan pilih
        if (activeRowIndex !== null) {
            const sel = document.getElementById('warga_sel_' + activeRowIndex);
            const btn = document.getElementById('btn_pilih_' + activeRowIndex);
            if (sel) {
                const opt = new Option(data.label, data.id, true, true);
                opt.dataset.nama = data.nama;
                opt.dataset.rt   = data.rt;
                opt.dataset.rw   = data.rw;
                sel.add(opt);
                sel.value = data.id;
                sel.classList.remove('d-none');
                if (btn) btn.classList.add('d-none');
                onWargaChange(activeRowIndex, sel);
            }
        }

        bootstrap.Modal.getInstance(document.getElementById('modalDaftarWarga')).hide();
    } catch (e) {
        alertEl.textContent = 'Terjadi kesalahan. Coba lagi.';
        alertEl.classList.remove('d-none');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ri-save-line me-1"></i> Daftarkan & Pilih';
    }
}

function recount() {
    // Re-estimate valid rows: a detail row is "likely valid" if nama_penyetor not empty and berat > 0
    const format = '{{ $format }}';
    let valid = 0, error = 0;

    document.querySelectorAll('#previewTable tbody tr').forEach(tr => {
        let ok = true;
        if (format === 'detail') {
            const nama  = tr.querySelector('input[name$="[nama_penyetor]"]');
            const berat = tr.querySelector('input[name$="[berat_kg]"]');
            if (nama  && (!nama.value.trim() || nama.value.trim() === '???')) ok = false;
            if (berat && (isNaN(parseFloat(berat.value)) || parseFloat(berat.value) <= 0)) ok = false;
        } else {
            const kg = tr.querySelector('input[name$="[kg]"]');
            if (kg && (isNaN(parseFloat(kg.value)) || parseFloat(kg.value) <= 0)) ok = false;
        }
        if (ok) { valid++; tr.classList.remove('table-danger', 'row-error'); tr.classList.add('row-valid'); }
        else    { error++; tr.classList.add('table-danger', 'row-error');    tr.classList.remove('row-valid'); }
    });

    document.getElementById('validCount').textContent = valid;
    document.getElementById('errorCount').textContent = error;

    const disabled = valid === 0;
    ['btnConfirm','btnConfirmFooter'].forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.disabled = disabled;
    });
}
</script>
@endsection
