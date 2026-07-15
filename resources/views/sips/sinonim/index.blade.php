@extends('partials.layouts.master')

@section('title', 'Kamus Sinonim Sampah | SIPS')
@section('title-sub', 'Pengaturan')
@section('pagetitle', 'Kamus Sinonim Jenis Sampah')

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

    <div class="row">

        {{-- ── Table ── --}}
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <h5 class="card-title mb-0">
                        <i class="ri-book-2-line me-2" style="color:#7030a0;"></i>
                        Daftar Sinonim
                    </h5>
                    <form method="GET" class="d-flex gap-2">
                        <input type="text" name="q" class="form-control form-control-sm" style="width:180px;"
                               placeholder="Cari kata kunci..." value="{{ $search }}">
                        <select name="tipe" class="form-select form-select-sm" style="width:160px;">
                            <option value="">Semua status</option>
                            <option value="dipilah"       {{ request('tipe') === 'dipilah'       ? 'selected' : '' }}>Dipilah</option>
                            <option value="tidak_dipilah" {{ request('tipe') === 'tidak_dipilah' ? 'selected' : '' }}>Tidak Dipilah</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
                        @if(request('q') || request('tipe'))
                        <a href="{{ route('sips.sinonim.index') }}" class="btn btn-sm btn-outline-danger">Reset</a>
                        @endif
                    </form>
                </div>
                <div class="card-body p-0">
                    @if($sinonims->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="ri-inbox-line d-block fs-1 mb-2"></i>
                        Belum ada sinonim yang terdaftar.
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 fs-13">
                            <thead class="table-light">
                                <tr>
                                    <th>Kata Kunci (dari lapangan)</th>
                                    <th>Status Pemilahan</th>
                                    <th>Dibuat oleh</th>
                                    <th class="text-center" style="width:100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($sinonims as $s)
                            <tr>
                                <td>
                                    <code>{{ $s->kata_kunci }}</code>
                                </td>
                                <td>
                                    @if($s->tipe_sampah === 'dipilah')
                                        <span class="badge bg-success">Dipilah</span>
                                    @elseif($s->tipe_sampah === 'tidak_dipilah')
                                        <span class="badge bg-danger">Tidak Dipilah</span>
                                    @else
                                        <span class="text-muted">–</span>
                                    @endif
                                </td>
                                <td class="text-muted fs-12">{{ $s->dibuatOleh?->name ?? '–' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary py-0 px-2 me-1"
                                            onclick="openEdit({{ $s->id }}, '{{ addslashes($s->kata_kunci) }}', '{{ $s->tipe_sampah }}')"
                                            title="Edit">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <form method="POST" action="{{ route('sips.sinonim.destroy', $s) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Hapus sinonim «{{ $s->kata_kunci }}»?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3">{{ $sinonims->links() }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Add form ── --}}
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-add-circle-line me-2 text-success"></i> Tambah Sinonim
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('sips.sinonim.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kata Kunci <span class="text-danger">*</span></label>
                            <input type="text" name="kata_kunci" class="form-control @error('kata_kunci') is-invalid @enderror"
                                   value="{{ old('kata_kunci') }}"
                                   placeholder="contoh: plastik hitam"
                                   required>
                            <div class="form-text">Akan disimpan lowercase. Ini yang ditulis petugas/AI di lapangan.</div>
                            @error('kata_kunci')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Status Pemilahan <span class="text-danger">*</span></label>
                            <select name="tipe_sampah" class="form-select @error('tipe_sampah') is-invalid @enderror" required>
                                <option value="">– Pilih status –</option>
                                <option value="dipilah"       {{ old('tipe_sampah') === 'dipilah'       ? 'selected' : '' }}>Dipilah</option>
                                <option value="tidak_dipilah" {{ old('tipe_sampah') === 'tidak_dipilah' ? 'selected' : '' }}>Tidak Dipilah</option>
                            </select>
                            <div class="form-text">Pilih "Dipilah" jika kata ini merujuk sampah yang sudah dipilah warga.</div>
                            @error('tipe_sampah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="ri-save-line me-1"></i> Simpan Sinonim
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body py-3 fs-13 text-muted">
                    <div class="fw-semibold text-dark mb-2"><i class="ri-information-line me-1"></i> Cara kerja kamus sinonim</div>
                    <p class="mb-2">Saat file Excel dari AI diupload, sistem mencari kata kunci ini di kolom <em>jenis_sampah</em> dan memetakannya ke status <strong>Dipilah</strong> atau <strong>Tidak Dipilah</strong> secara otomatis.</p>
                    <p class="mb-0">Semakin banyak sinonim, semakin sedikit item yang perlu review manual.</p>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Edit Modal ── --}}
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editForm">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Sinonim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kata Kunci</label>
                        <input type="text" name="kata_kunci" id="editKataKunci" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status Pemilahan</label>
                        <select name="tipe_sampah" id="editTipeSampah" class="form-select" required>
                            <option value="">– Pilih status –</option>
                            <option value="dipilah">Dipilah</option>
                            <option value="tidak_dipilah">Tidak Dipilah</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
const routes = { update: (id) => `/sips/sinonim/${id}` };

function openEdit(id, kunci, tipe) {
    document.getElementById('editForm').action       = routes.update(id);
    document.getElementById('editKataKunci').value   = kunci;
    document.getElementById('editTipeSampah').value  = tipe || '';
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
@endsection
