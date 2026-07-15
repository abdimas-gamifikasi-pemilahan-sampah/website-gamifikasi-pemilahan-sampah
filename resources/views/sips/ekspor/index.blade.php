@extends('partials.layouts.master')

@section('title', 'Ekspor Laporan | SIPS')
@section('title-sub', 'Ekspor')
@section('pagetitle', 'Ekspor Laporan')

@section('css')
<style>
    /* ── Base (light mode) ─────────────────────────────────── */
    .format-card {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        cursor: pointer;
        transition: border-color .15s, box-shadow .15s, background-color .15s;
    }
    .format-card:hover   { border-color: #86b7fe; background-color: #f8fbff; }
    .format-card.selected { border-color: #0d6efd; background-color: #f0f5ff; box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
    .format-card input[type="radio"] { display: none; }

    .format-badge-rivan     { background-color: #d1e7dd; color: #0a3622; }
    .format-badge-perolehan { background-color: #cff4fc; color: #055160; }

    .preview-stat {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px 16px;
    }

    .eksport-icon-wrapper {
        width: 64px; height: 64px;
        background: #e8f5e9;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 12px;
    }
    .eksport-tips { background: #f8f9fa; }

    /* ── Dark mode ─────────────────────────────────────────── */
    @media (prefers-color-scheme: dark) {
        .format-card                { border-color: rgba(255,255,255,0.12); }
        .format-card:hover          { border-color: #86b7fe; background-color: rgba(13,110,253,0.10); }
        .format-card.selected       { border-color: #4d94ff; background-color: rgba(13,110,253,0.18); box-shadow: 0 0 0 3px rgba(77,148,255,.2); }
        .format-badge-rivan         { background-color: rgba(25,135,84,0.25); color: #75d5a4; }
        .format-badge-perolehan     { background-color: rgba(13,202,240,0.18); color: #79ddf6; }
        .preview-stat               { background: rgba(255,255,255,0.06); }
        .eksport-icon-wrapper       { background: rgba(25,135,84,0.20); }
        .eksport-tips               { background: rgba(255,255,255,0.05); }
    }
    :root[data-theme="dark"] .format-card            { border-color: rgba(255,255,255,0.12); }
    :root[data-theme="dark"] .format-card:hover      { border-color: #86b7fe; background-color: rgba(13,110,253,0.10); }
    :root[data-theme="dark"] .format-card.selected   { border-color: #4d94ff; background-color: rgba(13,110,253,0.18); box-shadow: 0 0 0 3px rgba(77,148,255,.2); }
    :root[data-theme="dark"] .format-badge-rivan     { background-color: rgba(25,135,84,0.25); color: #75d5a4; }
    :root[data-theme="dark"] .format-badge-perolehan { background-color: rgba(13,202,240,0.18); color: #79ddf6; }
    :root[data-theme="dark"] .preview-stat           { background: rgba(255,255,255,0.06); }
    :root[data-theme="dark"] .eksport-icon-wrapper   { background: rgba(25,135,84,0.20); }
    :root[data-theme="dark"] .eksport-tips           { background: rgba(255,255,255,0.05); }
</style>
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body p-4"
                     style="background: linear-gradient(135deg, rgba(13,110,253,0.07), rgba(16,185,129,0.05));">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                        <div>
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle mb-2">
                                Ekspor Data
                            </span>
                            <h4 class="fw-semibold mb-1">Ekspor Laporan ke Excel</h4>
                            <p class="text-muted mb-0 fs-13">
                                Unduh data setoran dalam format Excel sesuai kebutuhan pelaporan desa.
                            </p>
                        </div>
                        <div class="text-muted fs-13 d-flex align-items-center gap-2">
                            <i class="ri-information-line"></i>
                            Hanya Admin yang dapat mengekspor laporan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form action="{{ route('sips.ekspor.download') }}" method="GET" id="eksporForm">
    <div class="row g-4">

        {{-- Column 1: Format + Period --}}
        <div class="col-xl-7">

            {{-- Format selection --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ri-file-excel-2-line me-2 text-success"></i>
                        Pilih Format Laporan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        {{-- Perolehan --}}
                        <div class="col-md-6">
                            <label class="format-card d-block p-3 w-100" id="card-perolehan">
                                <input type="radio" name="format" value="perolehan" required
                                       onchange="selectFormat(this)">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="mt-1">
                                        <span class="badge format-badge-perolehan rounded-pill px-3 py-2 fs-13">
                                            <i class="ri-money-dollar-circle-line me-1"></i> Perolehan
                                        </span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold mb-1">DATA PEROLEHAN SAMPAH</div>
                                        <div class="text-muted fs-12">
                                            Format rekap harian: Tanggal, RW, Kg, Tarif, Jumlah.
                                            Digunakan untuk pencatatan perolehan dana.
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted fw-medium">Kolom:</small>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach(['No','Hari/Tanggal','RW','Jumlah Sampah','Rp','Jumlah','Pengeluaran*'] as $col)
                                                <span class="badge bg-secondary-subtle text-secondary border fs-11">{{ $col }}</span>
                                                @endforeach
                                            </div>
                                            <div class="text-muted fs-11 mt-1">* kolom kosong untuk diisi admin</div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                        {{-- Pengelolaan --}}
                        <div class="col-md-6">
                            <label class="format-card d-block p-3 w-100" id="card-rivan">
                                <input type="radio" name="format" value="rivan" required
                                       onchange="selectFormat(this)">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="mt-1">
                                        <span class="badge format-badge-rivan rounded-pill px-3 py-2 fs-13">
                                            <i class="ri-recycle-line me-1"></i> Pengelolaan
                                        </span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold mb-1">DATA PENGELOLAAN</div>
                                        <div class="text-muted fs-12">
                                            Format laporan 2-mingguan pengelolaan sampah.
                                            Kolom olahan dikosongkan untuk diisi pengelola.
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted fw-medium">Kolom:</small>
                                            <div class="d-flex flex-wrap gap-1 mt-1">
                                                @foreach(['No','Tanggal','Kompos*','Pakan Magot*','Residu*','Daur Ulang*','Jumlah Sampah','Iuran'] as $col)
                                                <span class="badge {{ str_contains($col,'*') ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-secondary-subtle text-secondary border' }} fs-11">{{ $col }}</span>
                                                @endforeach
                                            </div>
                                            <div class="text-muted fs-11 mt-1">* dikosongkan — diisi pengelola</div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Date range --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ri-calendar-line me-2 text-primary"></i>
                        Rentang Periode
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label for="dari" class="form-label fw-medium">Dari Tanggal</label>
                            <input type="date" id="dari" name="dari"
                                   class="form-control @error('dari') is-invalid @enderror"
                                   value="{{ old('dari', $dari) }}"
                                   onchange="updatePreview()">
                            @error('dari')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <label for="sampai" class="form-label fw-medium">Sampai Tanggal</label>
                            <input type="date" id="sampai" name="sampai"
                                   class="form-control @error('sampai') is-invalid @enderror"
                                   value="{{ old('sampai', $sampai) }}"
                                   onchange="updatePreview()">
                            @error('sampai')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Quick select buttons --}}
                    <div class="mt-3">
                        <small class="text-muted fw-medium d-block mb-2">Pilih cepat:</small>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="setRange(14)">2 Minggu Terakhir</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="setRange(30)">30 Hari Terakhir</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="setCurrentMonth()">Bulan Ini</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="setLastMonth()">Bulan Lalu</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="setMonthsAgo(2)">2 Bulan Terakhir</button>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-muted fw-medium text-nowrap">Atau pilih bulan:</small>
                            <input type="month" id="monthPicker" class="form-control form-control-sm" style="width:160px;"
                                   max="{{ now()->format('Y-m') }}"
                                   onchange="setFromMonthPicker(this.value)">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Column 2: Preview + Download --}}
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="card-title mb-0">
                        <i class="ri-eye-line me-2 text-info"></i>
                        Ringkasan Data
                    </h5>
                </div>
                <div class="card-body">
                    <div id="previewContainer">
                        @if($preview['jumlah'] > 0)
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="preview-stat text-center">
                                    <div class="fw-bold fs-4 text-primary" id="statJumlah">{{ $preview['jumlah'] }}</div>
                                    <div class="text-muted fs-12">Setoran</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="preview-stat text-center">
                                    <div class="fw-bold fs-5 text-success" id="statKg">{{ number_format($preview['total_kg'], 1, ',', '.') }} kg</div>
                                    <div class="text-muted fs-12">Total Sampah</div>
                                </div>
                            </div>
                        </div>
                        <div class="preview-stat text-center mb-3">
                            <div class="fw-bold fs-5 text-warning" id="statNilai">Rp {{ number_format($preview['total_nilai'], 0, ',', '.') }}</div>
                            <div class="text-muted fs-12">Total Iuran / Nilai</div>
                        </div>
                        @else
                        <div class="text-center text-muted py-3" id="previewEmpty">
                            <i class="ri-inbox-line display-5 d-block mb-2 opacity-50"></i>
                            <p class="mb-0 fs-13">Tidak ada data setoran<br>dalam periode ini.</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Download card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="eksport-icon-wrapper">
                            <i class="ri-file-excel-2-line text-success" style="font-size:2rem;"></i>
                        </div>
                        <p class="text-muted fs-13 mb-0">
                            File .xlsx akan langsung diunduh setelah klik tombol di bawah.
                        </p>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100" id="downloadBtn">
                        <i class="ri-download-line me-2"></i>
                        Unduh Excel
                    </button>

                    <div class="mt-3 p-3 rounded fs-12 text-muted eksport-tips">
                        <i class="ri-lightbulb-line me-1 text-warning"></i>
                        <strong>Tips:</strong> Format <em>Pengelolaan</em> sebaiknya diekspor setiap 2 minggu
                        sesuai siklus laporan pengelolaan. Kolom berwarna kuning dikosongkan
                        untuk diisi pengelola.
                    </div>
                </div>
            </div>

        </div>

    </div>
    </form>

    {{-- ── Arsip Laporan Bulanan ── --}}
    <div class="row g-4 mt-1">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center gap-2">
                    <i class="ri-archive-line text-primary fs-5"></i>
                    <h5 class="card-title mb-0">Arsip Laporan Bulanan</h5>
                    <span class="badge bg-secondary-subtle text-secondary ms-1 fs-12">6 bulan terakhir</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 fs-13">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:160px;">Bulan</th>
                                    <th class="text-center" style="width:100px;">Setoran</th>
                                    <th class="text-center" style="width:120px;">Total Sampah</th>
                                    <th class="text-center" style="width:130px;">Total Nilai</th>
                                    <th class="text-center" colspan="2">Unduh Langsung</th>
                                </tr>
                                <tr class="table-light border-top-0">
                                    <th colspan="4"></th>
                                    <th class="text-center fs-11 text-muted fw-normal py-1">
                                        <i class="ri-file-excel-2-line text-info me-1"></i>Perolehan
                                    </th>
                                    <th class="text-center fs-11 text-muted fw-normal py-1">
                                        <i class="ri-recycle-line text-success me-1"></i>Pengelolaan
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($arsipBulanan as $bulan)
                            <tr>
                                <td class="fw-medium">{{ $bulan['label'] }}</td>
                                <td class="text-center">
                                    @if($bulan['jumlah'] > 0)
                                        <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $bulan['jumlah'] }}</span>
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($bulan['total_kg'] > 0)
                                        <span>{{ number_format($bulan['total_kg'], 1, ',', '.') }} kg</span>
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($bulan['total_nilai'] != 0)
                                        <span>Rp {{ number_format(abs($bulan['total_nilai']), 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($bulan['jumlah'] > 0)
                                    <a href="{{ route('sips.ekspor.download', ['format' => 'perolehan', 'dari' => $bulan['dari'], 'sampai' => $bulan['sampai']]) }}"
                                       class="btn btn-sm btn-outline-info py-0 px-2" title="Unduh Perolehan {{ $bulan['label'] }}">
                                        <i class="ri-download-line"></i>
                                    </a>
                                    @else
                                        <span class="text-muted fs-12">kosong</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($bulan['jumlah'] > 0)
                                    <a href="{{ route('sips.ekspor.download', ['format' => 'rivan', 'dari' => $bulan['dari'], 'sampai' => $bulan['sampai']]) }}"
                                       class="btn btn-sm btn-outline-success py-0 px-2" title="Unduh Pengelolaan {{ $bulan['label'] }}">
                                        <i class="ri-download-line"></i>
                                    </a>
                                    @else
                                        <span class="text-muted fs-12">kosong</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-2">
                    <small class="text-muted">
                        <i class="ri-information-line me-1"></i>
                        Klik tombol unduh untuk langsung mengunduh laporan per bulan tanpa perlu memilih tanggal.
                    </small>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
<script>
(function () {
    'use strict';

    // ── Format card selection ─────────────────────────────────────────────
    window.selectFormat = function (radio) {
        document.querySelectorAll('.format-card').forEach(c => c.classList.remove('selected'));
        const card = document.getElementById('card-' + radio.value);
        if (card) card.classList.add('selected');
    };

    // Pre-select if previously chosen (e.g. after validation error)
    @if(old('format'))
    const prevFormat = document.querySelector('input[name="format"][value="{{ old('format') }}"]');
    if (prevFormat) { prevFormat.checked = true; selectFormat(prevFormat); }
    @endif

    // ── Quick date range setters ──────────────────────────────────────────
    window.setRange = function (days) {
        const today  = new Date();
        const from   = new Date();
        from.setDate(today.getDate() - (days - 1));
        document.getElementById('dari').value   = toInput(from);
        document.getElementById('sampai').value = toInput(today);
        updatePreview();
    };

    window.setCurrentMonth = function () {
        const now = new Date();
        const from = new Date(now.getFullYear(), now.getMonth(), 1);
        document.getElementById('dari').value   = toInput(from);
        document.getElementById('sampai').value = toInput(now);
        updatePreview();
    };

    window.setLastMonth = function () {
        const now   = new Date();
        const from  = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const until = new Date(now.getFullYear(), now.getMonth(), 0);
        document.getElementById('dari').value   = toInput(from);
        document.getElementById('sampai').value = toInput(until);
        updatePreview();
    };

    window.setMonthsAgo = function (n) {
        const now   = new Date();
        const from  = new Date(now.getFullYear(), now.getMonth() - n, 1);
        const until = new Date(now.getFullYear(), now.getMonth(), 0);
        document.getElementById('dari').value   = toInput(from);
        document.getElementById('sampai').value = toInput(until);
        updatePreview();
    };

    window.setFromMonthPicker = function (val) {
        if (!val) return;
        const [year, month] = val.split('-').map(Number);
        const from  = new Date(year, month - 1, 1);
        const until = new Date(year, month, 0);
        document.getElementById('dari').value   = toInput(from);
        document.getElementById('sampai').value = toInput(until);
        updatePreview();
    };

    function toInput(d) {
        return d.toISOString().slice(0, 10);
    }

    // ── Preview stats via fetch ───────────────────────────────────────────
    window.updatePreview = function () {
        const dari   = document.getElementById('dari').value;
        const sampai = document.getElementById('sampai').value;
        if (!dari || !sampai || dari > sampai) return;

        fetch('{{ route('sips.ekspor.index') }}?dari=' + dari + '&sampai=' + sampai, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            const tmp   = document.createElement('div');
            tmp.innerHTML = html;
            const fresh = tmp.querySelector('#previewContainer');
            if (fresh) {
                document.getElementById('previewContainer').innerHTML = fresh.innerHTML;
            }
        })
        .catch(() => {});
    };

    // ── Download button feedback ──────────────────────────────────────────
    document.getElementById('eksporForm').addEventListener('submit', function () {
        const btn = document.getElementById('downloadBtn');
        btn.disabled  = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Menyiapkan file...';
        setTimeout(() => {
            btn.disabled  = false;
            btn.innerHTML = '<i class="ri-download-line me-2"></i>Unduh Excel';
        }, 4000);
    });

})();
</script>
@endsection
