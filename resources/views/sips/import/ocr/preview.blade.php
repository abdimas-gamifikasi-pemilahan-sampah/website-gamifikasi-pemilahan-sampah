@extends('partials.layouts.master')

@section('title', 'Preview OCR | SIPS')
@section('title-sub', 'Import')
@section('pagetitle', 'Preview Import AI / OCR')

@section('content')
<div id="layout-wrapper">

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- ── Summary Stats ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-2">
            <div class="card text-center py-2 px-2">
                <div class="fs-4 fw-bold text-dark">{{ $summary['total'] }}</div>
                <div class="text-muted fs-12">Total Baris</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center py-2 px-2 border-success">
                <div class="fs-4 fw-bold text-success">{{ $summary['auto_matched'] }}</div>
                <div class="text-muted fs-12">Otomatis Cocok</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center py-2 px-2 border-warning">
                <div class="fs-4 fw-bold text-warning">{{ $summary['needs_review'] }}</div>
                <div class="text-muted fs-12">Perlu Review</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center py-2 px-2 border-secondary">
                <div class="fs-4 fw-bold text-secondary">{{ $summary['unregistered'] }}</div>
                <div class="text-muted fs-12">Belum Terdaftar</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center py-2 px-2 border-danger">
                <div class="fs-4 fw-bold text-danger">{{ $summary['blocked'] }}</div>
                <div class="text-muted fs-12">Error / Blokir</div>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="card text-center py-2 px-2" style="border-color: #7030a0;">
                <div class="fs-4 fw-bold" style="color: #7030a0;">{{ $summary['flat_rate'] }}</div>
                <div class="text-muted fs-12">Flat Rate</div>
            </div>
        </div>
    </div>

    {{-- ── Main Form ── --}}
    <form method="POST" action="{{ route('sips.import.ocr.confirm') }}" id="confirmForm">
        @csrf

        {{-- Global date ──────────────────────────────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-body d-flex align-items-center gap-3 flex-wrap">
                <label class="fw-semibold mb-0 text-nowrap">Tanggal Setoran:</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" style="max-width: 180px;"
                       value="{{ $defaultTanggal }}" max="{{ date('Y-m-d') }}" required>
                <span class="text-muted fs-12">
                    <i class="ri-information-line me-1"></i>
                    Tanggal ini digunakan untuk semua setoran dari file ini.
                </span>
                @if($summary['blocked'] > 0)
                <span class="ms-auto badge bg-danger">
                    {{ $summary['blocked'] }} baris diblokir — isi nama atau centang "Lewati"
                </span>
                @endif
            </div>
        </div>

        {{-- Legend ──────────────────────────────────────────────────────────── --}}
        <div class="d-flex gap-3 mb-3 flex-wrap fs-12">
            <div class="d-flex align-items-center gap-1">
                <span class="badge bg-success">Otomatis</span> Nama cocok sempurna
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge bg-warning text-dark">Review</span> Kecocokan di bawah 80%
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge bg-secondary">Baru</span> Tidak ada kecocokan
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge bg-danger">Error</span> Nama tidak terbaca
            </div>
            <div class="d-flex align-items-center gap-1">
                <span class="badge text-white" style="background-color: #7030a0;">Flat</span> Jenis tidak dikenal
            </div>
        </div>

        {{-- Table ──────────────────────────────────────────────────────────── --}}
        <div class="card mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle mb-0 fs-13">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width:40px;">#</th>
                                <th style="min-width:80px;">Status</th>
                                <th style="min-width:200px;">Penyetor</th>
                                <th style="min-width:200px;">Jenis Sampah</th>
                                <th style="min-width:110px;">Status Pilah</th>
                                <th style="min-width:90px;">Berat (kg)</th>
                                <th style="min-width:110px;">Nilai Est.</th>
                                <th class="text-center" style="width:60px;" title="Simpan sebagai sinonim">Sinonim</th>
                                <th class="text-center" style="width:60px;">Lewati</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($previewRows as $i => $row)
                        @php
                            $wm          = $row['warga_match'];
                            $wsm         = $row['waste_match'];
                            $isDipilah   = $row['status_pemilahan'] === 'dipilah';
                            $isBlocked   = $row['is_blocked'];
                            $autoMatched = $wm['auto_matched'];
                            $wConf       = $wm['confidence'];
                            $wFlat       = $wsm['gunakan_flat'] && $isDipilah;

                            if ($isBlocked)              $rowClass = 'table-danger';
                            elseif (!$autoMatched &&
                                    $wConf < \App\Services\WargaMatcher::THRESHOLD_AUTO) $rowClass = 'table-warning';
                            elseif ($wFlat)               $rowClass = 'table-light';
                            else                          $rowClass = '';

                            $preSelectedWarga = ($autoMatched && $wm['warga']) ? $wm['warga']->id : 0;
                            $preSelectedTarif = $wsm['tarif_item'] ? $wsm['tarif_item']->id : '';
                        @endphp
                        <tr class="{{ $rowClass }}" id="preview-row-{{ $i }}">
                            {{-- # --}}
                            <td class="text-center text-muted">{{ $row['_row_num'] }}</td>

                            {{-- Status badge --}}
                            <td>
                                @if($isBlocked)
                                    <span class="badge bg-danger">Error</span>
                                @elseif($autoMatched)
                                    <span class="badge bg-success">Otomatis</span>
                                @elseif($wConf >= \App\Services\WargaMatcher::THRESHOLD_SUGGEST)
                                    <span class="badge bg-warning text-dark">Review</span>
                                @else
                                    <span class="badge bg-secondary">Baru</span>
                                @endif
                                @if($wFlat)
                                    <span class="badge mt-1 d-block text-white" style="background-color:#7030a0;">Flat</span>
                                @endif
                            </td>

                            {{-- Penyetor column --}}
                            <td>
                                <div class="text-muted fs-11 mb-1">
                                    <i class="ri-pencil-line"></i> "{{ $row['nama_raw'] }}"
                                    @if($row['rw_raw']) <span class="ms-1">RW{{ str_pad($row['rw_raw'],2,'0',STR_PAD_LEFT) }}</span> @endif
                                    @if($row['rt_raw']) <span>/RT{{ str_pad($row['rt_raw'],2,'0',STR_PAD_LEFT) }}</span> @endif
                                </div>

                                <input type="hidden" name="rows[{{ $i }}][rw_raw]" value="{{ $row['rw_raw'] }}">
                                <input type="hidden" name="rows[{{ $i }}][rt_raw]" value="{{ $row['rt_raw'] }}">

                                <select name="rows[{{ $i }}][warga_id]"
                                        class="form-select form-select-sm mb-1"
                                        onchange="toggleNama({{ $i }}, this.value); validateForm();">
                                    <option value="0" {{ $preSelectedWarga === 0 ? 'selected' : '' }}>
                                        — Penyetor Baru (tidak terdaftar) —
                                    </option>
                                    @foreach($allWarga as $w)
                                    <option value="{{ $w->id }}"
                                            data-nama="{{ $w->nama }}"
                                            {{ (int)$preSelectedWarga === $w->id ? 'selected' : '' }}>
                                        {{ $w->nama }}
                                        @if($w->rw) (RW{{ str_pad($w->rw,2,'0',STR_PAD_LEFT) }}) @endif
                                    </option>
                                    @endforeach
                                </select>

                                {{-- Candidate hints (only when needs review) --}}
                                @if(!$autoMatched && !empty($wm['candidates']) && !$isBlocked)
                                <div class="text-muted fs-11 mb-1">
                                    Kandidat:
                                    @foreach($wm['candidates'] as $c)
                                    <a href="#" class="text-decoration-none me-1"
                                       onclick="event.preventDefault(); selectWarga({{ $i }}, {{ $c['warga']->id }})">
                                        {{ $c['warga']->nama }}
                                        <span class="badge bg-light text-secondary">{{ $c['confidence'] }}%</span>
                                    </a>
                                    @endforeach
                                </div>
                                @endif

                                {{-- Name input for penyetor baru --}}
                                <div id="nama-group-{{ $i }}"
                                     style="{{ $preSelectedWarga !== 0 ? 'display:none' : '' }}">
                                    <input type="text" name="rows[{{ $i }}][nama_penyetor]"
                                           class="form-control form-control-sm"
                                           placeholder="Nama penyetor baru..."
                                           value="{{ $row['nama_raw'] !== '???' ? $row['nama_raw'] : '' }}">
                                </div>
                                {{-- Hidden for warga case (nama will be resolved in controller) --}}
                                @if($preSelectedWarga !== 0)
                                <input type="hidden" id="nama-hidden-{{ $i }}"
                                       name="rows[{{ $i }}][nama_penyetor]"
                                       value="{{ $wm['warga']?->nama ?? $row['nama_raw'] }}">
                                @endif
                            </td>

                            {{-- Jenis Sampah column --}}
                            <td>
                                <input type="hidden" name="rows[{{ $i }}][jenis_raw]" value="{{ $row['jenis_raw'] }}">

                                @if($isDipilah)
                                <div class="text-muted fs-11 mb-1">
                                    <i class="ri-leaf-line"></i> "{{ $row['jenis_raw'] ?: '–' }}"
                                    @if($wsm['method'] !== 'unknown' && $wsm['confidence'] > 0)
                                        <span class="badge bg-light text-secondary ms-1">{{ $wsm['confidence'] }}%</span>
                                    @endif
                                </div>
                                <select name="rows[{{ $i }}][tarif_item_id]" class="form-select form-select-sm"
                                        onchange="updateNilai({{ $i }})">
                                    <option value="">— Flat Rate (tidak dikenal) —</option>
                                    @foreach($allTarifItems as $t)
                                    <option value="{{ $t->id }}"
                                            {{ (string)$preSelectedTarif === (string)$t->id ? 'selected' : '' }}>
                                        {{ $t->nama_item }}
                                        <span class="text-muted">({{ $t->tipe_sampah }})</span>
                                    </option>
                                    @endforeach
                                </select>
                                @else
                                <span class="text-muted fs-13">Tidak dipilah — flat rate</span>
                                <input type="hidden" name="rows[{{ $i }}][tarif_item_id]" value="">
                                @endif
                            </td>

                            {{-- Status Pemilahan --}}
                            <td>
                                <select name="rows[{{ $i }}][status_pemilahan]" class="form-select form-select-sm"
                                        onchange="updateNilai({{ $i }})">
                                    <option value="dipilah" {{ $row['status_pemilahan'] === 'dipilah' ? 'selected' : '' }}>
                                        Dipilah
                                    </option>
                                    <option value="tidak_dipilah" {{ $row['status_pemilahan'] === 'tidak_dipilah' ? 'selected' : '' }}>
                                        Tidak Dipilah
                                    </option>
                                </select>
                            </td>

                            {{-- Berat --}}
                            <td>
                                <input type="number" name="rows[{{ $i }}][berat_kg]"
                                       class="form-control form-control-sm text-end"
                                       value="{{ $row['berat_kg'] }}"
                                       min="0.01" step="0.01" required
                                       oninput="updateNilai({{ $i }})">
                            </td>

                            {{-- Nilai estimasi --}}
                            <td class="text-end" id="nilai-cell-{{ $i }}">
                                @if($row['nilai_estimasi'] !== null)
                                    @php $n = $row['nilai_estimasi']; @endphp
                                    @if($n > 0)
                                        <span class="text-success fw-semibold">+Rp {{ number_format($n, 0, ',', '.') }}</span>
                                    @elseif($n < 0)
                                        <span class="text-danger fw-semibold">–Rp {{ number_format(abs($n), 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">Rp 0</span>
                                    @endif
                                    @if($wFlat)
                                        <div class="text-muted fs-11">est. flat</div>
                                    @endif
                                @else
                                    <span class="text-muted">–</span>
                                @endif
                            </td>

                            {{-- Simpan sinonim checkbox --}}
                            <td class="text-center">
                                @if($isDipilah && !empty($row['jenis_raw']) && $wsm['method'] !== 'sinonim' && $wsm['method'] !== 'exact')
                                <input type="checkbox" class="form-check-input"
                                       name="rows[{{ $i }}][simpan_sinonim_jenis]"
                                       value="1"
                                       title="Simpan '{{ $row['jenis_raw'] }}' sebagai sinonim untuk tarif yang dipilih">
                                @else
                                <span class="text-muted">–</span>
                                @endif
                            </td>

                            {{-- Lewati (skip) --}}
                            <td class="text-center">
                                <input type="checkbox" class="form-check-input"
                                       name="rows[{{ $i }}][skip]"
                                       value="1"
                                       onchange="toggleSkip({{ $i }}, this.checked); validateForm();"
                                       {{ $isBlocked ? 'checked' : '' }}>
                            </td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ── Action bar ── --}}
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="text-muted fs-13">
                    <i class="ri-information-line me-1"></i>
                    Centang <strong>Lewati</strong> untuk mengabaikan baris. Centang <strong>Sinonim</strong>
                    untuk menyimpan nama jenis sampah agar dikenali di import berikutnya.
                </div>
                <div class="d-flex flex-column align-items-end gap-2">
                    <div id="confirmWarning" class="text-danger fs-12" style="display:none;"></div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('sips.import.ocr.index') }}" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Batalkan
                        </a>
                        <button type="submit" id="confirmBtn" class="btn text-white" style="background-color: #7030a0;">
                            <i class="ri-save-line me-1"></i> Konfirmasi &amp; Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>
@endsection

@section('js')
<script>
// ── Server-side data passed to JS ────────────────────────────────────────────
const hargaMap    = @json($hargaMap);       // tarif_item_id → harga_per_kg (null if no active tarif)
const tarifDipilah = {{ $tarifDipilah }};   // flat rate for dipilah
const tarifTidak   = {{ $tarifTidak }};     // flat rate for tidak_dipilah
const rowCount     = {{ count($previewRows) }};

// ── Formatting ────────────────────────────────────────────────────────────────
function formatRp(value) {
    return Math.round(value).toLocaleString('id-ID');
}

// ── Nilai recalculation ───────────────────────────────────────────────────────
function updateNilai(idx) {
    const beratInput  = document.querySelector(`input[name="rows[${idx}][berat_kg]"]`);
    const tarifSelect = document.querySelector(`select[name="rows[${idx}][tarif_item_id]"]`);
    const pilahSelect = document.querySelector(`select[name="rows[${idx}][status_pemilahan]"]`);
    const nilaiCell   = document.getElementById('nilai-cell-' + idx);

    if (!beratInput || !nilaiCell) return;

    const berat   = parseFloat(beratInput.value) || 0;
    const dipilah = pilahSelect ? pilahSelect.value === 'dipilah' : true;
    const tarifId = tarifSelect ? tarifSelect.value : '';

    let nilai, isFlat = false;

    if (!dipilah) {
        nilai = -(berat * tarifTidak);
    } else if (tarifId && hargaMap[tarifId] != null) {
        nilai = berat * hargaMap[tarifId];
    } else {
        nilai  = berat * tarifDipilah;
        isFlat = true;
    }

    if (berat <= 0) {
        nilaiCell.innerHTML = '<span class="text-muted">–</span>';
        return;
    }

    let html;
    if (nilai > 0) {
        html = `<span class="text-success fw-semibold">+Rp ${formatRp(nilai)}</span>`;
    } else if (nilai < 0) {
        html = `<span class="text-danger fw-semibold">–Rp ${formatRp(Math.abs(nilai))}</span>`;
    } else {
        html = '<span class="text-muted">Rp 0</span>';
    }
    if (isFlat) {
        html += '<div class="text-muted fs-11">est. flat</div>';
    }
    nilaiCell.innerHTML = html;
}

// ── Warga name toggling ───────────────────────────────────────────────────────
function toggleNama(idx, wargaId) {
    const namaGroup  = document.getElementById('nama-group-' + idx);
    const namaHidden = document.getElementById('nama-hidden-' + idx);

    if (parseInt(wargaId) === 0) {
        if (namaGroup)  namaGroup.style.display  = 'block';
        if (namaHidden) namaHidden.disabled = true;
    } else {
        if (namaGroup)  namaGroup.style.display  = 'none';
        if (namaHidden) {
            namaHidden.disabled = false;
            const select = document.querySelector(`select[name="rows[${idx}][warga_id]"]`);
            const option = select ? select.options[select.selectedIndex] : null;
            if (option) namaHidden.value = option.dataset.nama || '';
        }
    }
}

function selectWarga(idx, wargaId) {
    const select = document.querySelector(`select[name="rows[${idx}][warga_id]"]`);
    if (select) {
        select.value = wargaId;
        toggleNama(idx, wargaId);
        validateForm();
    }
}

// ── Skip row opacity ──────────────────────────────────────────────────────────
function toggleSkip(idx, checked) {
    const row = document.getElementById('preview-row-' + idx);
    if (row) row.style.opacity = checked ? '0.4' : '';
}

// ── Form validation: disable confirm if any non-skipped row has no name ───────
function validateForm() {
    let unresolved = 0;

    for (let i = 0; i < rowCount; i++) {
        const skipCb = document.querySelector(`input[name="rows[${i}][skip]"]`);
        if (skipCb && skipCb.checked) continue;

        const wargaSelect = document.querySelector(`select[name="rows[${i}][warga_id]"]`);
        if (!wargaSelect || parseInt(wargaSelect.value) > 0) continue;

        const namaInput  = document.querySelector(`#nama-group-${i} input`);
        const namaHidden = document.getElementById('nama-hidden-' + i);
        const nama = ((namaInput ? namaInput.value : '') || (namaHidden ? namaHidden.value : '')).trim();

        if (!nama) unresolved++;
    }

    const confirmBtn   = document.getElementById('confirmBtn');
    const warningEl    = document.getElementById('confirmWarning');

    if (unresolved > 0) {
        confirmBtn.disabled = true;
        if (warningEl) {
            warningEl.style.display = '';
            warningEl.textContent   = `${unresolved} baris belum memiliki nama penyetor. Isi nama atau centang "Lewati".`;
        }
    } else {
        confirmBtn.disabled = false;
        if (warningEl) warningEl.style.display = 'none';
    }
}

// ── Init on page load ─────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    // Apply skip opacity to pre-checked rows
    document.querySelectorAll('input[name$="[skip]"]:checked').forEach(function (cb) {
        const m = cb.name.match(/rows\[(\d+)\]/);
        if (m) toggleSkip(m[1], true);
    });

    // Run initial validation (blocked rows start checked but user may have unblocked)
    validateForm();
});
</script>
@endsection
