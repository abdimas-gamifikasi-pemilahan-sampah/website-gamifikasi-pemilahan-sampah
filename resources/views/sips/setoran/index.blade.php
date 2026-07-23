@extends('partials.layouts.master')

@section('title', 'Riwayat Setoran | SIPS')
@section('title-sub', 'Setoran Sampah')
@section('pagetitle', 'Riwayat Setoran')

@section('content')
<div id="layout-wrapper">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Transaksi Setoran</h5>
                    <a href="{{ route('sips.setoran.create') }}" class="btn btn-primary btn-sm">
                        <i class="ri-add-circle-line me-1"></i> Setoran Baru
                    </a>
                </div>
                <div class="card-body">
                    @php
                        $hasSetoranFilter = filled(request('status')) || filled(request('dari')) || filled(request('sampai'));
                        $ringkasanSetoran = [
                            [
                                'label' => 'Jumlah Setoran',
                                'value' => number_format((int) ($ringkasan->jumlah_setoran ?? 0), 0, ',', '.'),
                                'class' => 'primary',
                                'icon' => 'ri-inbox-archive-line',
                            ],
                            [
                                'label' => 'Belum Dibayar Warga',
                                'value' => number_format((int) ($ringkasan->belum_dibayar_warga ?? 0), 0, ',', '.'),
                                'class' => 'danger',
                                'icon' => 'ri-alert-line',
                            ],
                            [
                                'label' => 'Belum Dibayar Petugas',
                                'value' => number_format((int) ($ringkasan->belum_dibayar_petugas ?? 0), 0, ',', '.'),
                                'class' => 'warning',
                                'icon' => 'ri-time-line',
                            ],
                            [
                                'label' => 'Sudah Dibayar',
                                'value' => number_format((int) ($ringkasan->sudah_dibayar ?? 0), 0, ',', '.'),
                                'class' => 'success',
                                'icon' => 'ri-checkbox-circle-line',
                            ],
                            [
                                'label' => 'Pemasukan',
                                'value' => \App\Support\SignedMoney::formatCurrency((float) ($ringkasan->total_nilai_minus ?? 0)),
                                'class' => 'success',
                                'icon' => 'ri-arrow-down-circle-line',
                            ],
                            [
                                'label' => 'Pengeluaran',
                                'value' => \App\Support\SignedMoney::formatCurrency((float) ($ringkasan->total_nilai_plus ?? 0)),
                                'class' => 'danger',
                                'icon' => 'ri-arrow-up-circle-line',
                            ],
                        ];
                    @endphp

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="mb-1">Ringkasan Setoran</h6>
                                <p class="text-muted mb-0 fs-13">
                                    {{ $hasSetoranFilter ? 'Menampilkan hasil filter setoran.' : 'Menampilkan seluruh data setoran.' }}
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            @foreach($ringkasanSetoran as $item)
                            <div class="col-6 col-md-6 col-xl-4">
                                <div class="card border border-{{ $item['class'] }} shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fs-13 text-muted mb-1">{{ $item['label'] }}</div>
                                                <h4 class="mb-0">{{ $item['value'] }}</h4>
                                            </div>
                                            <div class="avatar-sm">
                                                <span class="avatar-title bg-{{ $item['class'] }}-subtle text-{{ $item['class'] }} rounded-circle fs-4">
                                                    <i class="{{ $item['icon'] }}"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('sips.setoran.index') }}"
                          class="row g-2 align-items-end mb-4">
                        <div class="col-sm-auto">
                            <label class="form-label form-label-sm mb-1">Dari Tanggal</label>
                            <input type="date" class="form-control form-control-sm"
                                   name="dari" value="{{ request('dari') }}">
                        </div>
                        <div class="col-sm-auto">
                            <label class="form-label form-label-sm mb-1">Sampai Tanggal</label>
                            <input type="date" class="form-control form-control-sm"
                                   name="sampai" value="{{ request('sampai') }}">
                        </div>
                        <div class="col-sm-auto">
                            <label class="form-label form-label-sm mb-1">Status Bayar</label>
                            <select class="form-select form-select-sm" name="status">
                                <option value="">Semua Status</option>
                                <option value="belum_dibayar_warga" {{ request('status') === 'belum_dibayar_warga' ? 'selected' : '' }}>Belum Dibayar Warga</option>
                                <option value="belum_dibayar_petugas" {{ request('status') === 'belum_dibayar_petugas' ? 'selected' : '' }}>Belum Dibayar Petugas</option>
                                <option value="sudah_dibayar_warga" {{ request('status') === 'sudah_dibayar_warga' ? 'selected' : '' }}>Sudah Dibayar Warga</option>
                                <option value="sudah_dibayar_petugas" {{ request('status') === 'sudah_dibayar_petugas' ? 'selected' : '' }}>Sudah Dibayar Petugas</option>
                            </select>
                        </div>
                        <div class="col-sm-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ri-filter-line me-1"></i> Filter
                            </button>
                            @if($hasSetoranFilter)
                            <a href="{{ route('sips.setoran.index') }}" class="btn btn-light btn-sm">Reset</a>
                            @endif
                        </div>
                    </form>

                    @if($setoran->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="ri-inbox-line display-4 d-block mb-2"></i>
                        <p>Belum ada data setoran.
                            <a href="{{ route('sips.setoran.create') }}">Catat setoran pertama</a>.
                        </p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th class="d-none d-md-table-cell">ID</th>
                                    <th>Penyetor / Area</th>
                                    <th>Tanggal</th>
                                    <th class="d-none d-md-table-cell">Mode</th>
                                    <th class="text-end">Total (Rp)</th>
                                    <th>Status</th>
                                    <th class="d-none d-lg-table-cell">Selesai</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($setoran as $s)
                                <tr class="{{ $s->is_selesai ? 'table-success' : '' }}">
                                    <td class="fw-medium d-none d-md-table-cell">#{{ str_pad($s->id, 5, '0', STR_PAD_LEFT) }}</td>

                                    <td>
                                        @if($s->warga)
                                            <div class="fw-semibold">{{ $s->warga->nama }}</div>
                                            <small class="text-muted">{{ Str::limit($s->warga->alamat ?? (($s->warga->rt ? 'RT '.$s->warga->rt : '').($s->warga->dusun ? ' / '.$s->warga->dusun : '')), 40) ?: '-' }}</small>
                                        @else
                                            @php
                                                $namaList = $s->items->pluck('nama_penyetor')->filter()->unique()->values();
                                            @endphp
                                            @if($namaList->isNotEmpty())
                                                <div class="fw-semibold">
                                                    {{ $namaList->take(2)->implode(', ') }}
                                                    @if($namaList->count() > 2)
                                                        <span class="text-muted fw-normal fs-12">+{{ $namaList->count() - 2 }} lainnya</span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">
                                                    {{ $s->items->count() }} item{{ $s->area_rw ? ' · RW '.$s->area_rw : '' }}
                                                </small>
                                            @elseif($s->area_rw)
                                                <div class="fw-semibold text-muted">Area RW {{ $s->area_rw }}</div>
                                                <small class="text-muted">{{ $s->items->count() ?: '—' }} item</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        @endif
                                    </td>

                                    <td>
                                        <div>{{ $s->tanggal_setoran->format('d M Y') }}</div>
                                        @if($s->tanggal_setoran->format('H:i') !== '00:00')
                                        <small class="text-muted">{{ $s->tanggal_setoran->format('H:i') }}</small>
                                        @endif
                                    </td>

                                    <td class="d-none d-md-table-cell">
                                        @if($s->mode === 'agregat')
                                            <span class="badge bg-primary-subtle text-primary">Agregat</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Detail</span>
                                        @endif
                                        @if($s->sumber_input === 'excel')
                                            <span class="badge bg-info-subtle text-info ms-1">Excel</span>
                                        @elseif($s->sumber_input === 'ocr')
                                            <span class="badge bg-warning-subtle text-warning ms-1">OCR</span>
                                        @endif
                                    </td>

                                    <td class="fw-semibold text-end">
                                        <span class="{{ $s->signedAmountTextClasses() }}">
                                            {{ $s->nilaiSignedFormatted() }}
                                        </span>
                                        <div class="small text-muted">{{ $s->nilaiFormatted() }}</div>
                                    </td>

                                    <td>
                                        <span class="badge payment-status-badge {{ $s->paymentStatusBadgeClasses() }}">
                                            {{ $s->paymentStatusLabel() }}
                                        </span>
                                    </td>

                                    <td class="d-none d-lg-table-cell">
                                        @php
                                            $btnLabel = $s->is_selesai
                                                ? 'Selesai'
                                                : ($s->hasPaymentFlow() ? 'Konfirmasi Pembayaran' : 'Tandai Selesai');
                                            $btnClass = $s->is_selesai
                                                ? 'btn-success'
                                                : ($s->hasPaymentFlow() ? 'btn-outline-primary' : 'btn-outline-secondary');
                                            $btnTitle = $s->is_selesai
                                                ? 'Klik untuk batalkan konfirmasi'
                                                : ($s->hasPaymentFlow() ? 'Konfirmasi pembayaran sekaligus tandai selesai' : 'Tandai Selesai');
                                        @endphp
                                        <button type="button"
                                                class="btn btn-sm selesai-btn {{ $btnClass }}"
                                                data-id="{{ $s->id }}"
                                                data-has-payment="{{ $s->hasPaymentFlow() ? '1' : '0' }}"
                                                title="{{ $btnTitle }}">
                                            <i class="ri-checkbox-circle-{{ $s->is_selesai ? 'fill' : 'line' }}"></i>
                                            {{ $btnLabel }}
                                        </button>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('sips.setoran.show', $s->id) }}"
                                               class="btn btn-sm btn-light border" title="Detail">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $setoran->links() }}
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script>
document.querySelectorAll('.selesai-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id    = this.dataset.id;
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const self  = this;
        self.disabled = true;

        fetch(`/sips/setoran/${id}/selesai`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            const selesai = data.is_selesai;
            const row     = self.closest('tr');

            // Update button
            self.className = `btn btn-sm selesai-btn ${data.btn_class}`;
            self.innerHTML = `<i class="ri-checkbox-circle-${selesai ? 'fill' : 'line'}"></i> ${data.btn_label}`;
            self.title     = data.btn_class.includes('btn-success') ? 'Klik untuk batalkan konfirmasi' : self.dataset.title || '';
            row.className  = selesai ? 'table-success' : '';

            // Update payment status badge
            const badge = row.querySelector('.payment-status-badge');
            if (badge && data.status_badge_class) {
                badge.className   = `badge payment-status-badge ${data.status_badge_class}`;
                badge.textContent = data.status_label;
            }
        })
        .catch(() => {})
        .finally(() => { self.disabled = false; });
    });
});
</script>
@endsection
@endsection
