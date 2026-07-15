@extends('partials.layouts.master')

@section('title', 'Review Flat Rate OCR | SIPS')
@section('title-sub', 'Import')
@section('pagetitle', 'Review Item Flat Rate dari OCR')

@section('content')
<div id="layout-wrapper">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Info banner --}}
    <div class="card mb-4">
        <div class="card-body py-3 d-flex align-items-center gap-3 flex-wrap">
            <i class="ri-sparkling-line fs-4" style="color:#7030a0;"></i>
            <div>
                <div class="fw-semibold">Item jenis sampah yang belum teridentifikasi</div>
                <div class="text-muted fs-13">
                    Baris di bawah ini berasal dari import OCR dan dihitung sementara dengan flat rate
                    karena jenis sampahnya tidak cocok dengan tarif yang terdaftar.
                    Tetapkan tarif yang sesuai agar nilai tercatat dengan benar.
                </div>
            </div>
            <div class="ms-auto d-flex gap-2">
                <a href="{{ route('sips.sinonim.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-book-2-line me-1"></i> Kelola Sinonim
                </a>
                <a href="{{ route('sips.import.ocr.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ri-upload-line me-1"></i> Import Baru
                </a>
            </div>
        </div>
    </div>

    @if($grouped->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="ri-checkbox-circle-line d-block fs-1 mb-2 text-success"></i>
            <div class="fw-semibold">Semua item OCR sudah memiliki tarif yang ditetapkan.</div>
            <p class="fs-13 mt-1">Tidak ada item flat rate yang perlu direview saat ini.</p>
        </div>
    </div>
    @else
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                {{ $grouped->count() }} kelompok jenis sampah perlu ditinjau
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-sm align-middle mb-0 fs-13">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width:160px;">Jenis (dari lapangan)</th>
                            <th class="text-center" style="width:80px;">Item</th>
                            <th class="text-center" style="width:90px;">Total (kg)</th>
                            <th style="min-width:200px;">Tetapkan Tarif</th>
                            <th class="text-center" style="width:80px;">Sinonim</th>
                            <th class="text-center" style="width:80px;">Simpan</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($grouped as $g)
                    <tr>
                        <td>
                            @if($g['jenis_raw'])
                                <code>{{ $g['jenis_raw'] }}</code>
                            @else
                                <span class="text-muted fst-italic">tidak disebutkan</span>
                            @endif
                            <div class="text-muted fs-11 mt-1">
                                Terakhir: {{ $g['tanggal'] ? \Carbon\Carbon::parse($g['tanggal'])->format('d M Y') : '–' }}
                            </div>
                        </td>
                        <td class="text-center fw-semibold">{{ $g['jumlah'] }}</td>
                        <td class="text-center">{{ number_format($g['total_kg'], 2, ',', '.') }} kg</td>
                        <td>
                            <form method="POST" action="{{ route('sips.import.ocr.assign-tarif') }}">
                                @csrf
                                @foreach($g['item_ids'] as $id)
                                    <input type="hidden" name="item_ids[]" value="{{ $id }}">
                                @endforeach
                                <input type="hidden" name="jenis_raw" value="{{ $g['jenis_raw'] }}">
                                <div class="d-flex gap-2 align-items-center">
                                    <select name="tarif_item_id" class="form-select form-select-sm" required>
                                        <option value="">— Pilih tarif —</option>
                                        @foreach($tarifItems as $t)
                                        <option value="{{ $t->id }}">
                                            {{ $t->nama_item }} ({{ $t->tipe_sampah }})
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-2 d-flex align-items-center gap-2">
                                    @if($g['jenis_raw'])
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="checkbox"
                                               name="simpan_sinonim" value="1" id="sinonim-{{ $loop->index }}"
                                               checked>
                                        <label class="form-check-label fs-12" for="sinonim-{{ $loop->index }}">
                                            Simpan sbg sinonim
                                        </label>
                                    </div>
                                    @endif
                                    <button type="submit" class="btn btn-sm btn-primary ms-auto">
                                        <i class="ri-save-line me-1"></i> Tetapkan
                                    </button>
                                </div>
                            </form>
                        </td>
                        <td class="text-center">
                            @if($g['jenis_raw'])
                            <a href="{{ route('sips.sinonim.index') }}?q={{ urlencode($g['jenis_raw']) }}"
                               class="btn btn-sm btn-outline-secondary py-0 px-2"
                               title="Lihat/tambah di kamus sinonim">
                                <i class="ri-book-2-line"></i>
                            </a>
                            @else
                            <span class="text-muted">–</span>
                            @endif
                        </td>
                        <td class="text-center text-muted fs-12">
                            {{ $g['jumlah'] }} item
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
