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
                        <i class="bi bi-plus-circle me-1"></i> Setoran Baru
                    </a>
                </div>
                <div class="card-body">

                    {{-- Filter --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                        <p class="text-muted mb-0 fs-13">
                            Tandai setoran <strong>Belum Dibayar</strong> menjadi sudah dibayar setelah uang diserahkan ke warga.
                        </p>
                        <form method="GET" action="{{ route('sips.setoran.index') }}" class="d-flex gap-2">
                            <select class="form-select form-select-sm w-auto" name="status" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="belum_dibayar" {{ request('status') === 'belum_dibayar' ? 'selected' : '' }}>
                                    Belum Dibayar
                                </option>
                                <option value="sudah_dibayar" {{ request('status') === 'sudah_dibayar' ? 'selected' : '' }}>
                                    Sudah Dibayar
                                </option>
                            </select>
                        </form>
                    </div>

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
                                    <th>Warga</th>
                                    <th>Tanggal</th>
                                    <th>Item</th>
                                    <th class="text-end">Total (Rp)</th>
                                    <th>Status Bayar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($setoran as $s)
                                <tr>
                                    <td class="fw-medium">#{{ str_pad($s->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $s->warga->nama }}</div>
                                        <small class="text-muted">RT {{ $s->warga->rt }} / {{ $s->warga->dusun }}</small>
                                    </td>
                                    <td>{{ $s->tanggal_setoran->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $s->items->count() }} item
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-end">
                                        Rp {{ number_format($s->total_nilai, 0, ',', '.') }}
                                    </td>
                                    <td>
                                        @if($s->status_pembayaran === 'sudah_dibayar')
                                            <span class="badge bg-success-subtle text-success">Sudah Dibayar</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Belum Dibayar</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('sips.setoran.show', $s->id) }}"
                                               class="btn btn-sm btn-light border" title="Detail">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            @if($s->status_pembayaran === 'belum_dibayar')
                                            <button type="button" class="btn btn-sm btn-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#payModal"
                                                    data-id="{{ $s->id }}"
                                                    data-nama="{{ $s->warga->nama }}"
                                                    data-total="Rp {{ number_format($s->total_nilai, 0, ',', '.') }}"
                                                    data-total-raw="{{ $s->total_nilai }}">
                                                <i class="ri-money-dollar-circle-line me-1"></i> Catat Bayar
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="text-muted fs-13">
                            Menampilkan {{ $setoran->firstItem() }}–{{ $setoran->lastItem() }}
                            dari {{ $setoran->total() }} transaksi
                        </span>
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
                    <p class="text-muted mb-4" id="pay-nama"></p>
                    <div class="alert alert-info py-2 fs-13 text-start">
                        Pastikan Anda telah menyerahkan uang tunai kepada warga sebelum konfirmasi.
                    </div>
                    <div class="mb-3 text-start">
                        <label class="form-label fs-13">Jumlah Dibayar (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" name="jumlah_dibayar"
                               id="pay-jumlah" step="100" min="0" required>
                        <small class="text-muted">Ubah jika ada pembulatan atau kebijakan lokal (BR-07).</small>
                    </div>
                    <div class="mb-3 text-start">
                        <label class="form-label fs-13">Catatan Petugas <span class="text-muted">(opsional)</span></label>
                        <input type="text" class="form-control form-control-sm" name="catatan"
                               placeholder="Misal: Diberikan pas...">
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
        const btn      = e.relatedTarget;
        const id       = btn.dataset.id;
        const nama     = btn.dataset.nama;
        const total    = btn.dataset.total;
        const totalRaw = btn.dataset.totalRaw;

        document.getElementById('pay-total').textContent  = total;
        document.getElementById('pay-nama').textContent   = 'Setoran ' + nama;
        document.getElementById('pay-jumlah').value       = totalRaw;
        document.getElementById('pay-form').action        = '/sips/setoran/' + id + '/bayar';
    });
</script>
@endsection
@endsection
