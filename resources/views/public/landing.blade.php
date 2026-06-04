@extends('layouts.main')

@section('title', 'SIPS - Sistem Informasi Pemilahan Sampah')

@section('css')
<style>
/* ---- Mobile: phone ≤ 767px ---- */
@media (max-width: 767.98px) {
    /* Hero */
    .bg-home { min-height: auto !important; padding: 90px 0 30px !important; }
    .title-heading.margin-top-100 { margin-top: 20px !important; }
    .home-dashboard { display: none; } /* hide hero image on mobile to save vertical space */
    h1.heading { font-size: 1.8rem !important; line-height: 1.3 !important; }

    /* Section padding: Landrick default is 100px */
    .section { padding: 40px 0 !important; }
    .section.mt-100 { margin-top: 0 !important; }

    /* Feature section layout */
    .section .row.align-items-center { text-align: center; }

    /* Info box */
    .d-flex.align-items-start.gap-3 { flex-direction: column !important; gap: 12px !important; }

    /* Step cards */
    h4.title { font-size: 1.4rem !important; }
    h4.title.mb-3 { font-size: 1.3rem !important; }
}

/* ---- Mobile: small phone ≤ 575px ---- */
@media (max-width: 575.98px) {
    h1.heading { font-size: 1.45rem !important; }
    .section { padding: 32px 0 !important; }

    /* Hero CTAs: stack buttons vertically */
    .list-inline-item { display: block !important; margin: 0 0 10px 0 !important; text-align: center; }
    .list-inline-item .btn { width: 100%; }

    /* Step cards: reduce inner padding */
    .card-body.px-4.py-5 { padding: 20px 16px !important; }
    .card-body.px-4.py-5 div[style*="4rem"] { font-size: 2.8rem !important; }

    /* Feature section col order: image second on mobile */
    .col-lg-5.order-1.order-md-2 { display: none; } /* hide illustrator on xs */

    /* Info box items */
    .row.g-2 .col-md-6 { margin-bottom: 6px; }
}
</style>
@endsection

@section('content')

@include('includes.landrick-sips.navbar')

<!-- Hero Start -->
<section class="bg-home d-flex align-items-center bg-light" style="height: auto;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 text-center mt-0 mt-md-5 pt-0 pt-md-5">
                <div class="title-heading margin-top-100">
                    <h1 class="heading mb-4">Sistem Informasi <span class="text-primary">Pemilahan Sampah</span> Desa Banjarsari</h1>
                    <p class="para-desc mx-auto text-muted">Bergabunglah dalam program pemilahan sampah yang terstruktur untuk lingkungan yang lebih bersih, sehat, dan berkelanjutan.</p>
                    <ul class="mt-4 list-unstyled mb-0 align-items-center">
                        <li class="list-inline-item"><a href="{{ url('/leaderboard') }}" class="btn btn-primary me-2"><i class="uil uil-trophy"></i> Lihat Papan Peringkat</a></li>
                        <li class="list-inline-item"><a href="#cara-bergabung" class="text-primary fw-bold"> Cara Bergabung <i class="uil uil-angle-right-b align-middle"></i></a></li>
                    </ul>
                </div>

                <div class="home-dashboard">
                    <img src="{{ asset('assets/images/social/hero.png') }}" alt="" class="img-fluid mover">
                </div>
            </div><!--end col-->
        </div><!--end row-->
    </div><!--end container-->
</section><!--end section-->
<div class="position-relative">
    <div class="shape overflow-hidden text-color-white">
        <svg viewBox="0 0 2880 250" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M720 125L2160 0H2880V250H0V125H720Z" fill="currentColor"></path>
        </svg>
    </div>
</div>
<!-- Hero End -->

<!-- Features Start -->
<section class="section overflow-hidden" id="features">
    <div class="container mt-md-5">
        <div class="row justify-content-center" id="counter">
            <div class="col-lg-8 col-md-10">
                <div class="mb-4 pb-2 text-center">
                    <h5 class="mb-0 fw-normal text-muted">Bergabunglah dengan <span class="text-success fw-bold"><span class="counter-value" data-target="100">1</span>+</span> warga menggunakan <span class="fw-bold text-primary">SIPS.</span> untuk lingkungan yang lebih bersih, sehat, dan berkelanjutan</h5>
                </div>
            </div><!--end col-->
        </div><!--end row-->

        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8 pb-md-4">
                <ul class="text-center mb-0 p-0">
                    @include('includes.landrick-sips.index-social-marketing.btn-icon')
                </ul>
            </div><!--end col-->
        </div><!--end row-->
    </div><!--end container-->

    <div class="container mt-100 mt-60">
        <div class="row align-items-center">
            <div class="col-lg-5 col-md-6 col-12 order-1 order-md-2">
                <img src="{{ asset('assets/images/illustrator/social.svg') }}" class="img-fluid" alt="">
            </div><!--end col-->

            <div class="col-lg-7 col-md-6 col-12 order-2 order-md-1 mt-4 pt-2 mt-sm-0 pt-sm-0">
                <div class="section-title me-lg-4">
                    <p class="text-primary h2 mb-3"><i class="uil uil-chart-line"></i></p>
                    <h4 class="title mb-3">Pantau Kontribusi <br> <span class="text-primary">Real-time</span></h4>
                    <p class="text-muted">Lihat perkembangan kontribusi pemilahan sampah Anda dan komunitas secara real-time di dasbor yang interaktif dan mudah dipahami.</p>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-1"><span class="text-primary h5 me-2"><i class="uil uil-check-circle align-middle"></i></span>Pemilahan sampah organik &amp; anorganik</li>
                        <li class="mb-1"><span class="text-primary h5 me-2"><i class="uil uil-check-circle align-middle"></i></span>Sistem poin dan papan peringkat komunitas</li>
                        <li class="mb-1"><span class="text-primary h5 me-2"><i class="uil uil-check-circle align-middle"></i></span>Laporan kontribusi dan statistik warga</li>
                    </ul>
                    <div class="mt-4">
                        <a href="{{ url('/leaderboard') }}" class="btn btn-primary">Lihat Peringkat <i class="uil uil-angle-right-b"></i></a>
                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->
    </div><!--end container-->

</section><!--end section-->

<!-- Cara Bergabung Start -->
<section class="section" id="cara-bergabung" style="background: linear-gradient(180deg, #ffffff 0%, #f4f8ff 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-12">
                <div class="section-title text-center mb-5">
                    <h4 class="title mb-3">Cara Bergabung <span class="text-primary">Program SIPS</span></h4>
                    <p class="text-muted para-desc mx-auto mb-0">Warga tidak perlu mendaftar secara online. Cukup datang langsung ke kantor desa dan petugas akan membantu proses pendaftaran Anda.</p>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow h-100 text-center overflow-hidden" style="border-radius: 1rem;">
                    <div class="card-body px-4 py-5 d-flex flex-column align-items-center">
                        <div class="mb-3" style="font-size: 4rem; font-weight: 900; color: rgba(47,85,212,0.18); line-height: 1; user-select: none; letter-spacing: -2px;">01</div>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 68px; height: 68px; background: linear-gradient(135deg, #dde6ff, #eef2ff);">
                            <i data-feather="map-pin" style="width: 28px; height: 28px; color: #2f55d4; stroke-width: 2;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Datang ke Kantor Desa</h6>
                        <p class="text-muted small mb-0">Kunjungi Kantor Desa Banjarsari di gedung utama pada jam layanan yang tersedia.</p>
                    </div>
                    <div style="height: 4px; background: linear-gradient(90deg, #2f55d4, #7796ee);"></div>
                </div>
            </div><!--end col-->

            <!-- Step 2 -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow h-100 text-center overflow-hidden" style="border-radius: 1rem;">
                    <div class="card-body px-4 py-5 d-flex flex-column align-items-center">
                        <div class="mb-3" style="font-size: 4rem; font-weight: 900; color: rgba(47,85,212,0.18); line-height: 1; user-select: none; letter-spacing: -2px;">02</div>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 68px; height: 68px; background: linear-gradient(135deg, #d5eeff, #eef7ff);">
                            <i data-feather="message-square" style="width: 28px; height: 28px; color: #2f55d4; stroke-width: 2;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Temui Petugas Bank Sampah</h6>
                        <p class="text-muted small mb-0">Sampaikan keinginan Anda untuk bergabung program bank sampah kepada petugas yang bertugas.</p>
                    </div>
                    <div style="height: 4px; background: linear-gradient(90deg, #2f55d4, #55a0ee);"></div>
                </div>
            </div><!--end col-->

            <!-- Step 3 -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow h-100 text-center overflow-hidden" style="border-radius: 1rem;">
                    <div class="card-body px-4 py-5 d-flex flex-column align-items-center">
                        <div class="mb-3" style="font-size: 4rem; font-weight: 900; color: rgba(46,202,139,0.2); line-height: 1; user-select: none; letter-spacing: -2px;">03</div>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 68px; height: 68px; background: linear-gradient(135deg, #d0f0e4, #eefaf4);">
                            <i data-feather="file-text" style="width: 28px; height: 28px; color: #2eca8b; stroke-width: 2;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Berikan Data Diri</h6>
                        <p class="text-muted small mb-0">Siapkan nama lengkap, nomor KK, RT/RW/Dusun, dan nomor HP (opsional). Petugas yang menginput ke sistem.</p>
                    </div>
                    <div style="height: 4px; background: linear-gradient(90deg, #55a0ee, #2eca8b);"></div>
                </div>
            </div><!--end col-->

            <!-- Step 4 - Done -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow h-100 text-center overflow-hidden" style="border-radius: 1rem;">
                    <div class="card-body px-4 py-5 d-flex flex-column align-items-center">
                        <div class="mb-3" style="font-size: 4rem; font-weight: 900; color: rgba(46,202,139,0.25); line-height: 1; user-select: none;">✓</div>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 68px; height: 68px; background: linear-gradient(135deg, #a8f0d0, #d4f8ea);">
                            <i data-feather="award" style="width: 28px; height: 28px; color: #2eca8b; stroke-width: 2;"></i>
                        </div>
                        <h6 class="fw-bold mb-2">Mulai Setor &amp; Raih Poin</h6>
                        <p class="text-muted small mb-0">Setelah terdaftar, langsung setor sampah terpilah dan pantau kontribusi Anda di papan peringkat.</p>
                    </div>
                    <div style="height: 4px; background: linear-gradient(90deg, #2eca8b, #06d6a0);"></div>
                </div>
            </div><!--end col-->
        </div><!--end row-->

        <!-- Info Box -->
        <div class="row justify-content-center mt-5">
            <div class="col-lg-10">
                <div class="p-4" style="background: linear-gradient(135deg, #eef2ff 0%, #f0fbf6 100%); border-radius: 1rem; border-left: 4px solid #2f55d4;">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center shrink-0" style="width: 44px; height: 44px; background: white; box-shadow: 0 4px 12px rgba(47,85,212,0.15);">
                            <i data-feather="info" style="width: 18px; height: 18px; color: #2f55d4; stroke-width: 2;"></i>
                        </div>
                        <div class="w-100">
                            <h6 class="fw-bold mb-3">Yang perlu diketahui warga</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="uil uil-check-circle text-primary mt-1" style="font-size: 1.1rem; flex-shrink: 0;"></i>
                                        <p class="text-muted small mb-0">Warga tidak memiliki akun login — semua pencatatan dilakukan oleh petugas.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="uil uil-check-circle text-primary mt-1" style="font-size: 1.1rem; flex-shrink: 0;"></i>
                                        <p class="text-muted small mb-0">Bawa sampah yang sudah dipilah (organik terpisah dari anorganik) untuk mendapat nilai penuh.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="uil uil-check-circle text-success mt-1" style="font-size: 1.1rem; flex-shrink: 0;"></i>
                                        <p class="text-muted small mb-0">Pembayaran dilakukan tunai langsung oleh petugas setelah setoran dicatat.</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="uil uil-check-circle text-success mt-1" style="font-size: 1.1rem; flex-shrink: 0;"></i>
                                        <p class="text-muted small mb-0">Peringkat dihitung otomatis setiap akhir bulan berdasarkan total kg dan konsistensi setoran.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->
    </div><!--end container-->
</section>
<!-- Cara Bergabung End -->

<div class="position-relative">
    <div class="shape overflow-hidden text-footer">
        <svg viewBox="0 0 2880 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z" fill="currentColor"></path>
        </svg>
    </div>
</div>
<!-- Features End -->

@include('includes.landrick-sips.footer')

@endsection
