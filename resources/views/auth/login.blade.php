@extends('partials.layouts.master_auth')

@section('title', 'Masuk · SIPS Desa Banjarsari')

@section('css')
<style>
    .auth-login-shell {
        min-height: 100vh;
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(circle at top left, rgba(255, 126, 61, 0.18), transparent 30%),
            radial-gradient(circle at bottom right, rgba(13, 110, 253, 0.14), transparent 32%),
            linear-gradient(180deg, #fff9f4 0%, #f4f7fb 100%);
    }

    [data-bs-theme="dark"] .auth-login-shell {
        background:
            radial-gradient(circle at top left, rgba(255, 126, 61, 0.14), transparent 28%),
            radial-gradient(circle at bottom right, rgba(13, 110, 253, 0.08), transparent 30%),
            linear-gradient(180deg, #0f172a 0%, #111827 100%);
    }

    .auth-login-panel {
        border-radius: 1.5rem;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: rgba(255, 255, 255, 0.84);
        backdrop-filter: blur(12px);
        box-shadow: 0 22px 60px rgba(15, 23, 42, 0.12);
    }

    [data-bs-theme="dark"] .auth-login-panel {
        background: rgba(15, 23, 42, 0.82);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 24px 70px rgba(0, 0, 0, 0.28);
    }

    .auth-eyebrow {
        letter-spacing: 0.18em;
        text-transform: uppercase;
        font-size: 0.76rem;
        font-weight: 700;
        color: #f97316;
    }

    .auth-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid rgba(148, 163, 184, 0.18);
        color: #475569;
        font-size: 0.875rem;
    }

    [data-bs-theme="dark"] .auth-badge {
        background: rgba(15, 23, 42, 0.72);
        color: #cbd5e1;
        border-color: rgba(255,255,255,0.08);
    }

    .auth-info-card {
        border-radius: 1.2rem;
        background: linear-gradient(135deg, rgba(255, 126, 61, 0.14), rgba(59, 130, 246, 0.08));
        border: 1px solid rgba(148, 163, 184, 0.18);
    }

    [data-bs-theme="dark"] .auth-info-card {
        background: linear-gradient(135deg, rgba(255, 126, 61, 0.12), rgba(59, 130, 246, 0.06));
        border-color: rgba(255,255,255,0.08);
    }
</style>
@endsection

@section('content')
<div class="auth-login-shell">
    <div class="container py-4 py-lg-5">
        <div class="row justify-content-center align-items-center min-vh-100 py-4">
            <div class="col-12 col-lg-11 col-xl-10">
                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-5">
                        <div class="auth-login-panel h-100 p-4 p-lg-5">
                            <span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle mb-3">Admin Login</span>
                            <h1 class="display-6 fw-semibold mb-3">Masuk ke SIPS Desa Banjarsari</h1>
                            <p class="text-muted mb-4">
                                Gunakan halaman ini untuk masuk ke dashboard petugas dan admin desa. Semua pencatatan setoran, tarif, dan laporan dikelola di sini.
                            </p>
                            <div class="d-flex flex-wrap gap-2 mb-4">
                                <span class="auth-badge"><i class="bi bi-shield-lock"></i> Akses aman</span>
                                <span class="auth-badge"><i class="bi bi-recycle"></i> Untuk petugas</span>
                                <span class="auth-badge"><i class="bi bi-building"></i> Admin desa</span>
                            </div>
                            <div class="auth-info-card p-4">
                                <h2 class="h6 fw-semibold mb-2">Petunjuk singkat</h2>
                                <p class="text-muted mb-3">Masuk menggunakan akun petugas atau admin yang sudah disiapkan. Jika Anda warga, gunakan halaman publik leaderboard untuk melihat peringkat desa.</p>
                                <a href="/leaderboard" class="btn btn-outline-secondary">Lihat halaman publik</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="auth-login-panel h-100 p-4 p-lg-5">
                            <div class="mb-4 text-center text-lg-start">
                                <span class="auth-eyebrow">Sistem Informasi Pemilahan Sampah</span>
                                <h2 class="h3 fw-semibold mt-2 mb-2">Silakan masuk</h2>
                                <p class="text-muted mb-0">Kelola setoran, pembayaran, dan laporan dari dashboard admin.</p>
                            </div>

                            <form>
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label for="username" class="form-label">Nama pengguna</label>
                                        <input type="text" class="form-control form-control-lg" id="username" placeholder="Masukkan nama pengguna">
                                    </div>
                                    <div class="col-12">
                                        <label for="password" class="form-label">Kata sandi</label>
                                        <input type="password" class="form-control form-control-lg" id="password" placeholder="Masukkan kata sandi">
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="rememberMe">
                                                <label class="form-check-label" for="rememberMe">Ingat saya</label>
                                            </div>
                                            <a href="/" class="link-primary text-decoration-underline small">Kembali ke beranda publik</a>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <button type="submit" class="btn btn-primary btn-lg w-100">
                                            Masuk ke Dashboard
                                            <i class="bi bi-box-arrow-in-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
@endsection
