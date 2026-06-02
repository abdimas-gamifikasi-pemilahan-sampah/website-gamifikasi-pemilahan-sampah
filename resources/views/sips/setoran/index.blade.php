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
                        $hasSetoranFilter = filled(request('status'));
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
                            <div class="col-md-6 col-xl-4">
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
                          class="d-flex flex-wrap gap-2 align-items-center mb-4">
                        <select class="form-select form-select-sm w-auto" name="status" onchange="this.form.submit()">
                            <option value="">Semua Status Bayar</option>
                            <option value="belum_dibayar_warga" {{ request('status') === 'belum_dibayar_warga' ? 'selected' : '' }}>Belum Dibayar Warga</option>
                            <option value="belum_dibayar_petugas" {{ request('status') === 'belum_dibayar_petugas' ? 'selected' : '' }}>Belum Dibayar Petugas</option>
                            <option value="sudah_dibayar_warga" {{ request('status') === 'sudah_dibayar_warga' ? 'selected' : '' }}>Sudah Dibayar Warga</option>
                            <option value="sudah_dibayar_petugas" {{ request('status') === 'sudah_dibayar_petugas' ? 'selected' : '' }}>Sudah Dibayar Petugas</option>
                        </select>
                        @if(request('status'))
                            <a href="{{ route('sips.setoran.index') }}" class="btn btn-sm btn-light">Reset</a>
                        @endif
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
                        <table class="table text-nowrap align-middle mb-0 table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Penyetor / Area</th>
                                    <th>Tanggal</th>
                                    <th>Mode</th>
                                    <th class="text-end">Total (Rp)</th>
                                    <th>Status</th>
                                    <th>Selesai</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($setoran as $s)
                                <tr class="{{ $s->is_selesai ? 'table-success' : '' }}">
                                    <td class="fw-medium">#{{ str_pad($s->id, 5, '0', STR_PAD_LEFT) }}</td>

                                    <td>
                                        @if($s->warga)
                                            <div class="fw-semibold">{{ $s->warga->nama }}</div>
                                            <small class="text-muted">RT {{ $s->warga->rt }} / {{ $s->warga->dusun }}</small>
                                        @elseif($s->area_rw)
                                            <div class="fw-semibold">RW {{ $s->area_rw }}</div>
                                            <small class="text-muted">{{ $s->items->count() }} penyetor</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    <td>{{ $s->tanggal_setoran->format('d M Y') }}</td>

                                    <td>
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
                                        <span class="badge {{ $s->paymentStatusBadgeClasses() }}">
                                            {{ $s->paymentStatusLabel() }}
                                        </span>
                                    </td>

                                    <td>
                                        <button type="button"
                                                class="btn btn-sm selesai-btn {{ $s->is_selesai ? 'btn-success' : 'btn-outline-secondary' }}"
                                                data-id="{{ $s->id }}"
                                                title="{{ $s->is_selesai ? 'Tandai Belum Selesai' : 'Tandai Selesai' }}">
                                            <i class="ri-checkbox-circle-{{ $s->is_selesai ? 'fill' : 'line' }}"></i>
                                            {{ $s->statusSederhanaLabel() }}
                                        </button>
                                    </td>

                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('sips.setoran.show', $s->id) }}"
                                               class="btn btn-sm btn-light border" title="Detail">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            @if($s->status_pembayaran === 'belum_dibayar' && $s->warga && $s->hasPaymentFlow())
                                            <button type="button" class="btn btn-sm btn-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#payModal"
                                                    data-id="{{ $s->id }}"
                                                    data-nama="{{ $s->warga->nama }}"
                                                    data-total="{{ $s->nilaiSignedFormatted() }}"
                                                    data-flow="{{ $s->isPaymentByWarga() ? 'pemasukan' : 'pengeluaran' }}"
                                                    data-total-raw="{{ $s->total_nilai }}">
                                                <i class="ri-money-dollar-circle-line"></i>
                                            </button>
                                            @endif
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

{{-- Modal Konfirmasi Pembayaran --}}
<div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="pay-form" method="POST" action="">
                @csrf
                <div class="modal-body text-center">
                    <i class="ri-wallet-3-line text-success display-4 d-block mb-3"></i>
                    <h4 class="mb-1" id="pay-total"></h4>
                    <p class="text-muted mb-2" id="pay-nama"></p>
                    <div class="alert alert-info py-2 fs-13 text-start mb-4" id="pay-hint"></div>
                    <div class="mb-3 text-start">
                        <label class="form-label fs-13">Jumlah Dibayar (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" name="jumlah_dibayar"
                               id="pay-jumlah" step="100" min="0" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="form-label fs-13">Catatan <span class="text-muted">(opsional)</span></label>
                        <input type="text" class="form-control form-control-sm" name="catatan" placeholder="Misal: Diberikan pas...">
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-checkbox-circle-line me-1"></i> Konfirmasi Sudah Dibayar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('js')
<script>
document.getElementById('payModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('pay-total').textContent  = btn.dataset.total;
    document.getElementById('pay-nama').textContent   = 'Setoran ' + btn.dataset.nama;
    document.getElementById('pay-hint').textContent   = btn.dataset.flow === 'pemasukan'
        ? 'Catat saat pemasukan dari warga sudah diterima oleh sistem.'
        : 'Catat saat pengeluaran dari sistem kepada warga sudah dibayarkan.';
    document.getElementById('pay-jumlah').value       = btn.dataset.totalRaw;
    document.getElementById('pay-form').action        = '/sips/setoran/' + btn.dataset.id + '/bayar';
});

// Selesai toggle (AJAX)
document.querySelectorAll('.selesai-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        fetch(`/sips/setoran/${id}/selesai`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            const selesai = data.is_selesai;
            this.className = 'btn btn-sm selesai-btn ' + (selesai ? 'btn-success' : 'btn-outline-secondary');
            this.innerHTML = `<i class="ri-checkbox-circle-${selesai ? 'fill' : 'line'}"></i> ${selesai ? 'Selesai' : 'Draft'}`;
            this.title     = selesai ? 'Tandai Belum Selesai' : 'Tandai Selesai';
            this.closest('tr').className = selesai ? 'table-success' : '';
        });
    });
});
</script>
@endsection
@endsection
