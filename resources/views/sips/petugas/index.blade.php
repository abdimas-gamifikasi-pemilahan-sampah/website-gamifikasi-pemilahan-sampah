@extends('partials.layouts.master')

@section('title', 'Data Petugas | SIPS')
@section('title-sub', 'Data Master')
@section('pagetitle', 'Data Petugas')

@section('content')
<div id="layout-wrapper">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="ri-checkbox-circle-line me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="ri-error-warning-line me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if($errors->any() && !session('_edit_id'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="ri-error-warning-line me-1"></i>
        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card card-h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1">Daftar Akun Pengguna</h5>
                        <p class="text-muted mb-0 fs-13">Kelola akun admin dan petugas yang dapat mengakses sistem.</p>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Akun
                    </button>
                </div>
                <div class="card-body">

                    @php
                        $hasFilter = request('search') || request('role') || request('status');
                        $ringkasanAkun = [
                            ['label' => 'Total Akun',  'value' => (int)($allUsers->total   ?? 0), 'class' => 'primary', 'icon' => 'ri-team-line'],
                            ['label' => 'Admin',       'value' => (int)($allUsers->admin   ?? 0), 'class' => 'danger',  'icon' => 'ri-shield-user-line'],
                            ['label' => 'Petugas',     'value' => (int)($allUsers->petugas ?? 0), 'class' => 'success', 'icon' => 'ri-user-line'],
                        ];
                    @endphp

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <div>
                                <h6 class="mb-1">Ringkasan Data Petugas</h6>
                                <p class="text-muted mb-0 fs-13">
                                    {{ $hasFilter ? 'Menampilkan hasil filter akun.' : 'Menampilkan seluruh akun pengguna terdaftar.' }}
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            @foreach($ringkasanAkun as $item)
                            <div class="col-md-4">
                                <div class="card border border-{{ $item['class'] }} shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="fs-13 text-muted mb-1">{{ $item['label'] }}</div>
                                                <h4 class="mb-0">{{ number_format($item['value'], 0, ',', '.') }}</h4>
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

                    <form method="GET" action="{{ route('sips.petugas.index') }}" class="row g-2 mb-4">
                        <div class="col-md-5">
                            <label for="search" class="form-label fs-13">Cari Akun</label>
                            <input type="text" class="form-control form-control-sm" id="search" name="search"
                                   value="{{ request('search') }}" placeholder="Nama, username, atau email">
                        </div>
                        <div class="col-md-3">
                            <label for="role" class="form-label fs-13">Filter Role</label>
                            <select class="form-select form-select-sm" id="role" name="role">
                                <option value="">Semua Role</option>
                                <option value="admin"   {{ request('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                                <option value="petugas" {{ request('role') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label fs-13">Status</label>
                            <select class="form-select form-select-sm" id="status" name="status">
                                <option value="">Semua</option>
                                <option value="aktif"     {{ request('status') === 'aktif'     ? 'selected' : '' }}>Aktif</option>
                                <option value="non_aktif" {{ request('status') === 'non_aktif' ? 'selected' : '' }}>Non Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="ri-search-line me-1"></i> Filter
                            </button>
                            <a href="{{ route('sips.petugas.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                        </div>
                    </form>

                    @if($users->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="ri-team-line display-4 d-block mb-2"></i>
                        <p class="mb-0">Tidak ada akun yang cocok dengan filter.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle mb-0 table-hover">
                            <thead class="table-light sips-table-head">
                                <tr>
                                    <th>Nama Lengkap</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                <tr class="{{ $u->is_active ? '' : 'opacity-75' }}">
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $u->name }}
                                            @if($u->id === auth()->id())
                                            <span class="badge bg-secondary-subtle text-secondary ms-1" style="font-size:10px;">Anda</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-muted fs-13">{{ $u->username }}</td>
                                    <td class="fs-13">{{ $u->email }}</td>
                                    <td>
                                        @if($u->role === 'admin')
                                        <span class="badge bg-danger-subtle text-danger">Admin</span>
                                        @else
                                        <span class="badge bg-success-subtle text-success">Petugas</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($u->is_active)
                                        <span class="badge border border-success text-success">Aktif</span>
                                        @else
                                        <span class="badge border border-danger text-danger">Non Aktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button"
                                                    class="btn btn-sm btn-light border"
                                                    title="Edit akun"
                                                    onclick="openEdit({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->username }}', '{{ $u->email }}', '{{ $u->role }}')">
                                                <i class="ri-edit-2-line"></i>
                                            </button>
                                            @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('sips.petugas.status', $u->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="btn btn-sm {{ $u->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                        title="{{ $u->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}">
                                                    <i class="ri-{{ $u->is_active ? 'user-unfollow' : 'user-follow' }}-line"></i>
                                                </button>
                                            </form>
                                            <form method="POST"
                                                  action="{{ route('sips.petugas.destroy', $u->id) }}"
                                                  onsubmit="return confirm('Hapus akun {{ addslashes($u->name) }}? Tindakan ini tidak dapat dibatalkan.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light border text-danger" title="Hapus akun">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                            @else
                                            <button type="button" class="btn btn-sm btn-light border text-muted" disabled title="Akun sedang digunakan">
                                                <i class="ri-user-unfollow-line"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border text-muted" disabled title="Tidak dapat menghapus akun sendiri">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="text-muted fs-13">
                            Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} akun
                        </span>
                        {{ $users->onEachSide(1)->links('partials.pagination') }}
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Tambah Akun --}}
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahLabel">
                    <i class="bi bi-plus-circle me-2 text-primary"></i> Tambah Akun Baru
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="{{ route('sips.petugas.store') }}" id="formTambah">
                @csrf
                <div class="modal-body">

                    @if($errors->hasAny(['name', 'username', 'email', 'password']) && !session('_edit_id'))
                    <div class="alert alert-danger py-2 fs-13">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               name="name" value="{{ old('name') }}" required placeholder="Contoh: Ahmad Santoso">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror"
                               name="username" value="{{ old('username') }}" required placeholder="Contoh: ahmad.santoso">
                        <div class="form-text">Huruf, angka, titik, strip, dan garis bawah saja.</div>
                        @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" required placeholder="contoh@email.com">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="petugas" {{ old('role', 'petugas') === 'petugas' ? 'selected' : '' }}>Petugas</option>
                            <option value="admin"   {{ old('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>

                    <hr class="my-3">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" required minlength="8" placeholder="Minimal 8 karakter" id="pwdNew">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdNew', this)">
                                <i class="ri-eye-line"></i>
                            </button>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password_confirmation"
                                   required minlength="8" placeholder="Ulangi password" id="pwdNewConfirm">
                            <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdNewConfirm', this)">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Edit Akun --}}
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">
                    <i class="ri-edit-2-line me-2 text-primary"></i> Edit Akun
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form method="POST" action="" id="formEdit">
                @csrf
                @method('PUT')
                <div class="modal-body">

                    @if($errors->any() && session('_edit_id'))
                    <div class="alert alert-danger py-2 fs-13">
                        @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" id="editUsername" required>
                        <div class="form-text">Huruf, angka, titik, strip, dan garis bawah saja.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="editEmail" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                        <select name="role" class="form-select" id="editRole" required>
                            <option value="petugas">Petugas</option>
                            <option value="admin">Admin</option>
                        </select>
                        <input type="hidden" name="role" id="editRoleHidden" disabled>
                        <div class="form-text text-warning" id="editRoleHint" style="display:none;">
                            <i class="ri-information-line me-1"></i>Role akun Anda sendiri tidak dapat diubah.
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="mb-2">
                        <a href="#editPasswordSection" class="text-muted fs-13" data-bs-toggle="collapse">
                            <i class="ri-lock-line me-1"></i> Ganti Password <small>(opsional)</small>
                        </a>
                    </div>
                    <div class="collapse" id="editPasswordSection">
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-13">Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password"
                                       placeholder="Minimal 8 karakter" id="pwdEdit">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdEdit', this)">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mb-1">
                            <label class="form-label fw-semibold fs-13">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password_confirmation"
                                       placeholder="Ulangi password baru" id="pwdEditConfirm">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePwd('pwdEditConfirm', this)">
                                    <i class="ri-eye-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
const SELF_ID = {{ auth()->id() }};

function openEdit(id, name, username, email, role) {
    document.getElementById('formEdit').action = '/sips/petugas/' + id;
    document.getElementById('editName').value     = name;
    document.getElementById('editUsername').value = username;
    document.getElementById('editEmail').value    = email;
    document.getElementById('editRole').value     = role;

    const roleSelect = document.getElementById('editRole');
    const roleHidden = document.getElementById('editRoleHidden');
    const roleHint   = document.getElementById('editRoleHint');
    if (id === SELF_ID) {
        roleSelect.disabled = true;
        roleHidden.disabled = false;
        roleHidden.value    = role;
        roleHint.style.display = '';
    } else {
        roleSelect.disabled = false;
        roleHidden.disabled = true;
        roleHidden.value    = '';
        roleHint.style.display = 'none';
    }

    document.getElementById('pwdEdit').value        = '';
    document.getElementById('pwdEditConfirm').value = '';
    const collapse = bootstrap.Collapse.getInstance(document.getElementById('editPasswordSection'));
    if (collapse) collapse.hide();

    new bootstrap.Modal(document.getElementById('modalEdit')).show();
}

function togglePwd(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
    } else {
        input.type = 'password';
        icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
    }
}

@if(session('_edit_id'))
document.addEventListener('DOMContentLoaded', function () {
    const u = @json($users->getCollection()->firstWhere('id', session('_edit_id')));
    if (u) {
        openEdit(u.id, u.name, u.username, u.email, u.role);
        @if(old('name'))     document.getElementById('editName').value     = @json(old('name'));     @endif
        @if(old('username')) document.getElementById('editUsername').value = @json(old('username')); @endif
        @if(old('email'))    document.getElementById('editEmail').value    = @json(old('email'));    @endif
        @if(old('role'))     document.getElementById('editRole').value     = @json(old('role'));     @endif
    }
});
@endif
</script>
@endsection
