@extends('layouts.main')

@section('title', 'SIPS - Papan Peringkat Desa Banjarsari')

@section('content')

@include('includes.landrick-sips.navbar')

<!-- Page Header Start -->
<section class="bg-half-170 bg-light d-table w-100">
    <div class="container">
        <div class="row mt-5 justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="pages-heading">
                    <h4 class="title mb-3">Papan Peringkat <span class="text-primary">Pemilahan Sampah</span></h4>
                    <p class="text-muted para-desc mx-auto mb-0">Lihat daftar warga dan RW Desa Banjarsari yang aktif berkontribusi dalam program pemilahan sampah. Data diperbarui setiap bulan.</p>
                    <div class="mt-4">
                        <a href="#leaderboard" class="btn btn-primary me-2"><i class="uil uil-trophy"></i> Lihat Peringkat</a>
                        <a href="#cara-ikut" class="btn btn-outline-primary"><i class="uil uil-info-circle"></i> Cara Ikut Serta</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="position-relative">
    <div class="shape overflow-hidden text-color-white">
        <svg viewBox="0 0 2880 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z" fill="currentColor"></path>
        </svg>
    </div>
</div>
<!-- Page Header End -->

<!-- Stats Section Start -->
<section class="section pt-5" id="stats">
    <div class="container">
        <div class="row justify-content-center" id="counter">
            <div class="col-lg-3 col-md-6 mt-4 pt-2">
                <div class="card features feature-primary text-center rounded border-0 shadow">
                    <div class="card-body py-4">
                        <div class="icons rounded-circle shadow-lg d-inline-block mb-3">
                            <i data-feather="users" class="fea"></i>
                        </div>
                        <h3 class="counter-value mb-1" data-target="182">1</h3>
                        <p class="text-muted mb-0">Warga Terdata</p>
                    </div>
                </div>
            </div><!--end col-->

            <div class="col-lg-3 col-md-6 mt-4 pt-2">
                <div class="card features feature-primary text-center rounded border-0 shadow">
                    <div class="card-body py-4">
                        <div class="icons rounded-circle shadow-lg d-inline-block mb-3">
                            <i data-feather="package" class="fea"></i>
                        </div>
                        <h3 class="mb-1"><span class="counter-value" data-target="1420">1</span> kg</h3>
                        <p class="text-muted mb-0">Total Dipilah</p>
                    </div>
                </div>
            </div><!--end col-->

            <div class="col-lg-3 col-md-6 mt-4 pt-2">
                <div class="card features feature-primary text-center rounded border-0 shadow">
                    <div class="card-body py-4">
                        <div class="icons rounded-circle shadow-lg d-inline-block mb-3">
                            <i data-feather="award" class="fea"></i>
                        </div>
                        <h3 class="mb-1">RW 03</h3>
                        <p class="text-muted mb-0">RW Terbaik Bulan Ini</p>
                    </div>
                </div>
            </div><!--end col-->

            <div class="col-lg-3 col-md-6 mt-4 pt-2">
                <div class="card features feature-primary text-center rounded border-0 shadow">
                    <div class="card-body py-4">
                        <div class="icons rounded-circle shadow-lg d-inline-block mb-3">
                            <i data-feather="calendar" class="fea"></i>
                        </div>
                        <h3 class="mb-1">Mei 2026</h3>
                        <p class="text-muted mb-0">Periode Aktif</p>
                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->
    </div><!--end container-->
</section>
<!-- Stats Section End -->

<!-- Top 3 Podium Start -->
<section class="section pt-4" id="leaderboard" style="background: linear-gradient(180deg, #ffffff 0%, #f4f8ff 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="section-title text-center mb-5">
                    <h4 class="title mb-3">Peringkat Tiga Teratas</h4>
                    <p class="text-muted para-desc mx-auto mb-0">Warga paling aktif dalam program pemilahan sampah Desa Banjarsari bulan ini.</p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center align-items-end g-4">
            <!-- Rank 2 -->
            <div class="col-lg-4 col-md-6 order-md-1 order-2">
                <div class="card border-0 text-center overflow-hidden" style="border-radius: 1.25rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                    <div class="card-body px-4 py-4 d-flex flex-column align-items-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white mb-3" style="width: 34px; height: 34px; background: linear-gradient(135deg, #9aa5b8, #b8c4d4); font-size: 0.8rem;">#2</div>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white mb-3" style="width: 62px; height: 62px; background: linear-gradient(135deg, #9aa5b8, #c0cce0); font-size: 1.3rem; box-shadow: 0 6px 16px rgba(154,165,184,0.4);">BS</div>
                        <h6 class="fw-bold mb-1">Budi Santoso</h6>
                        <p class="text-muted small mb-3">RW 01 / Melati</p>
                        <h4 class="text-primary fw-bold mb-0">502</h4>
                        <small class="text-muted">poin · 22.2 kg</small>
                    </div>
                    <div style="height: 4px; background: linear-gradient(90deg, #9aa5b8, #c0cce0);"></div>
                </div>
            </div><!--end col-->

            <!-- Rank 1 -->
            <div class="col-lg-4 col-md-6 order-md-2 order-1">
                <div class="text-center overflow-hidden" style="border-radius: 1.25rem; background: linear-gradient(155deg, #2f55d4 0%, #3d67e8 60%, #2eca8b 100%); box-shadow: 0 20px 60px rgba(47,85,212,0.35);">
                    <div class="px-4 pt-4 pb-2">
                        <i class="uil uil-trophy" style="font-size: 2.2rem; color: #FFD700;"></i>
                    </div>
                    <div class="px-4 pb-5 d-flex flex-column align-items-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold mb-2" style="width: 72px; height: 72px; background: white; color: #2f55d4; font-size: 1.4rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">SA</div>
                        <span class="badge mb-3 px-3 py-1" style="background: rgba(255,255,255,0.2); color: white; border-radius: 50px; font-size: 0.7rem; letter-spacing: 0.5px;">JUARA 1</span>
                        <h5 class="text-white fw-bold mb-1">Siti Aminah</h5>
                        <p class="mb-3" style="color: rgba(255,255,255,0.65); font-size: 0.875rem;">RW 03 / Melati</p>
                        <h3 class="text-white fw-bold mb-0">528</h3>
                        <small style="color: rgba(255,255,255,0.65);">poin · 24.6 kg</small>
                    </div>
                </div>
            </div><!--end col-->

            <!-- Rank 3 -->
            <div class="col-lg-4 col-md-6 order-md-3 order-3">
                <div class="card border-0 text-center overflow-hidden" style="border-radius: 1.25rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                    <div class="card-body px-4 py-4 d-flex flex-column align-items-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white mb-3" style="width: 34px; height: 34px; background: linear-gradient(135deg, #cd7f32, #e8964a); font-size: 0.8rem;">#3</div>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white mb-3" style="width: 62px; height: 62px; background: linear-gradient(135deg, #cd7f32, #e8964a); font-size: 1.3rem; box-shadow: 0 6px 16px rgba(205,127,50,0.35);">AP</div>
                        <h6 class="fw-bold mb-1">Agus Pratama</h6>
                        <p class="text-muted small mb-3">RW 04 / Anggrek</p>
                        <h4 class="text-info fw-bold mb-0">451</h4>
                        <small class="text-muted">poin · 19.8 kg</small>
                    </div>
                    <div style="height: 4px; background: linear-gradient(90deg, #cd7f32, #e8964a);"></div>
                </div>
            </div><!--end col-->
        </div><!--end row-->
    </div><!--end container-->
</section>
<!-- Top 3 Podium End -->

<!-- Table + RW Rankings Start -->
<section class="section pt-5">
    <div class="container">
        <div class="row g-4">
            <!-- Leaderboard Table -->
            <div class="col-lg-7">
                <div class="mb-5">
                    <h5 class="title mb-1">10 Besar Warga</h5>
                    <p class="text-muted small mb-0">Daftar warga dengan kontribusi pemilahan terbanyak bulan ini.</p>
                </div>
                <div class="card border-0 overflow-hidden" style="border-radius: 1rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                    @php
                    $entries = [
                        [1,  'Siti Aminah',    'RW 03 / Melati',  '24.6', 528],
                        [2,  'Budi Santoso',   'RW 01 / Melati',  '22.2', 502],
                        [3,  'Agus Pratama',   'RW 04 / Anggrek', '19.8', 451],
                        [4,  'Rahmawati',      'RW 05 / Kenanga', '18.9', 430],
                        [5,  'Deni Saputra',   'RW 02 / Anggrek', '17.1', 388],
                        [6,  'Lina Sari',      'RW 03 / Melati',  '16.4', 374],
                        [7,  'Yudi Hartono',   'RW 01 / Melati',  '15.8', 361],
                        [8,  'Nur Aini',       'RW 05 / Kenanga', '15.3', 350],
                        [9,  'Rudi Kurniawan', 'RW 04 / Anggrek', '14.8', 339],
                        [10, 'Sri Wahyuni',    'RW 02 / Anggrek', '14.1', 325],
                    ];
                    $medalBg = [
                        1 => 'linear-gradient(135deg,#FFD700,#FFA500)',
                        2 => 'linear-gradient(135deg,#9aa5b8,#c0cce0)',
                        3 => 'linear-gradient(135deg,#cd7f32,#e8964a)',
                    ];
                    @endphp
                    @foreach($entries as [$rank, $name, $rw, $kg, $pts])
                    <div class="d-flex align-items-center px-4 py-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="me-4" style="width: 34px; text-align: center;">
                            @if(isset($medalBg[$rank]))
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white" style="width: 30px; height: 30px; background: {{ $medalBg[$rank] }}; font-size: 0.72rem;">{{ $rank }}</div>
                            @else
                                <span class="text-muted fw-semibold" style="font-size: 0.85rem;">#{{ $rank }}</span>
                            @endif
                        </div>
                        <div class="grow">
                            <div class="fw-semibold" style="font-size: 0.9rem; line-height: 1.3;">{{ $name }}</div>
                            <div class="text-muted" style="font-size: 0.78rem;">{{ $rw }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge rounded-pill px-3 py-2 d-block mb-1" style="background: linear-gradient(135deg, #eef2ff, #dde6ff); color: #2f55d4; font-size: 0.82rem; font-weight: 700;">{{ $pts }} poin</span>
                            <span class="text-muted" style="font-size: 0.74rem;">{{ $kg }} kg</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div><!--end col-->

            <!-- RW Rankings -->
            <div class="col-lg-5">
                <div class="mb-5">
                    <h5 class="title mb-1">Peringkat RW / Dusun</h5>
                    <p class="text-muted small mb-0">RW dengan tingkat pemilahan tertinggi bulan ini.</p>
                </div>
                <div class="card border-0 p-4 mb-4" style="border-radius: 1rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                    @php
                    $rws = [
                        ['RW 03 / Melati',  '89% pemilahan · 44 warga aktif', 89, 'linear-gradient(90deg,#2eca8b,#06d6a0)', '#2eca8b'],
                        ['RW 01 / Melati',  '84% pemilahan · 41 warga aktif', 84, 'linear-gradient(90deg,#2f55d4,#7796ee)', '#2f55d4'],
                        ['RW 05 / Kenanga', '79% pemilahan · 38 warga aktif', 79, 'linear-gradient(90deg,#17a2b8,#36c2da)', '#17a2b8'],
                        ['RW 04 / Anggrek', '73% pemilahan · 36 warga aktif', 73, 'linear-gradient(90deg,#fd7e14,#ffa040)', '#fd7e14'],
                        ['RW 02 / Anggrek', '68% pemilahan · 33 warga aktif', 68, 'linear-gradient(90deg,#6c757d,#9aa5b4)', '#6c757d'],
                    ];
                    @endphp
                    @foreach($rws as $i => [$rw, $desc, $pct, $gradient, $color])
                    <div class="{{ $i < count($rws) - 1 ? 'mb-4' : '' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-semibold" style="color: {{ $color }}; font-size: 0.9rem;">{{ $rw }}</span>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $desc }}</div>
                            </div>
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white shrink-0 ms-2" style="width: 28px; height: 28px; background: {{ $gradient }}; font-size: 0.68rem;">#{{ $i+1 }}</div>
                        </div>
                        <div class="rounded-pill overflow-hidden" style="height: 8px; background: #f0f2f5;">
                            <div class="h-100 rounded-pill" style="width: {{ $pct }}%; background: {{ $gradient }};"></div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Spotlight card -->
                <div class="card border-0 overflow-hidden" style="border-radius: 1rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                    <div style="height: 4px; background: linear-gradient(90deg, #2f55d4, #2eca8b);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center me-3 shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #fff8e1, #fff3cd);">
                                <i data-feather="trending-up" style="width: 20px; height: 20px; color: #fd7e14; stroke-width: 2;"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">Sorotan Bulan Ini</h6>
                        </div>
                        <h6 class="mb-1 fw-bold" style="color: #2eca8b;">RW 03 / Melati</h6>
                        <p class="text-muted small mb-3">89% tingkat pemilahan · 760 kg total</p>
                        <div class="rounded-pill overflow-hidden" style="height: 10px; background: #f0f2f5;">
                            <div class="h-100 rounded-pill" style="width: 89%; background: linear-gradient(90deg, #2eca8b, #06d6a0);"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Data diperbarui otomatis setiap bulan.</small>
                    </div>
                </div>
            </div><!--end col-->
        </div><!--end row-->
    </div><!--end container-->
</section>
<!-- Table + RW Rankings End -->

<!-- How to Join Start -->
<section class="section" id="cara-ikut" style="background: linear-gradient(180deg, #ffffff 0%, #f4f8ff 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-12">
                <div class="section-title text-center mb-5">
                    <h4 class="title mb-3">Cara Ikut Serta</h4>
                    <p class="text-muted para-desc mx-auto mb-0">Langkah sederhana untuk warga baru bergabung dalam program pemilahan sampah Desa Banjarsari.</p>
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
    </div><!--end container-->
</section>
<!-- How to Join End -->


<div class="position-relative">
    <div class="shape overflow-hidden text-footer">
        <svg viewBox="0 0 2880 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 48H1437.5H2880V0H2160C1442.5 52 720 0 720 0H0V48Z" fill="currentColor"></path>
        </svg>
    </div>
</div>

@include('includes.landrick-sips.footer')

@endsection
