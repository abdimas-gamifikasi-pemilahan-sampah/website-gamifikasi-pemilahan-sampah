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
                        <h3 class="counter-value mb-1" data-target="{{ $wargaAktif }}">1</h3>
                        <p class="text-muted mb-0">Warga Terdata</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mt-4 pt-2">
                <div class="card features feature-primary text-center rounded border-0 shadow">
                    <div class="card-body py-4">
                        <div class="icons rounded-circle shadow-lg d-inline-block mb-3">
                            <i data-feather="package" class="fea"></i>
                        </div>
                        <h3 class="mb-1"><span class="counter-value" data-target="{{ (int) $totalKgDipilah }}">1</span> kg</h3>
                        <p class="text-muted mb-0">Total Dipilah</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mt-4 pt-2">
                <div class="card features feature-primary text-center rounded border-0 shadow">
                    <div class="card-body py-4">
                        <div class="icons rounded-circle shadow-lg d-inline-block mb-3">
                            <i data-feather="award" class="fea"></i>
                        </div>
                        <h3 class="mb-1">
                            @if($rwTerbaik)
                                RW {{ $rwTerbaik->rw }}
                            @else
                                -
                            @endif
                        </h3>
                        <p class="text-muted mb-0">RW Terbaik Bulan Ini</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mt-4 pt-2">
                <div class="card features feature-primary text-center rounded border-0 shadow">
                    <div class="card-body py-4">
                        <div class="icons rounded-circle shadow-lg d-inline-block mb-3">
                            <i data-feather="calendar" class="fea"></i>
                        </div>
                        <h3 class="mb-1">{{ $periodeBulan }}</h3>
                        <p class="text-muted mb-0">Periode Aktif</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Stats Section End -->

@php
    $top3 = $entries->take(3);
    $rank1 = $top3->get(0);
    $rank2 = $top3->get(1);
    $rank3 = $top3->get(2);

    function initials($name) {
        $parts = explode(' ', trim($name));
        return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
    }

    $medalBg = [
        1 => 'linear-gradient(135deg,#FFD700,#FFA500)',
        2 => 'linear-gradient(135deg,#9aa5b8,#c0cce0)',
        3 => 'linear-gradient(135deg,#cd7f32,#e8964a)',
    ];
@endphp

<!-- Top 3 Podium Start -->
@if($rank1)
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
                @if($rank2)
                <div class="card border-0 text-center overflow-hidden" style="border-radius: 1.25rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                    <div class="card-body px-4 py-4 d-flex flex-column align-items-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white mb-3" style="width: 34px; height: 34px; background: linear-gradient(135deg, #9aa5b8, #b8c4d4); font-size: 0.8rem;">#2</div>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white mb-3" style="width: 62px; height: 62px; background: linear-gradient(135deg, #9aa5b8, #c0cce0); font-size: 1.3rem; box-shadow: 0 6px 16px rgba(154,165,184,0.4);">{{ initials($rank2->nama) }}</div>
                        <h6 class="fw-bold mb-1">{{ $rank2->nama }}</h6>
                        <p class="text-muted small mb-3">RW {{ $rank2->rw }} / {{ $rank2->dusun }}</p>
                        <h4 class="text-primary fw-bold mb-0">{{ (int) $rank2->poin }}</h4>
                        <small class="text-muted">poin · {{ number_format($rank2->total_kg, 1, ',', '.') }} kg</small>
                    </div>
                    <div style="height: 4px; background: linear-gradient(90deg, #9aa5b8, #c0cce0);"></div>
                </div>
                @endif
            </div>

            <!-- Rank 1 -->
            <div class="col-lg-4 col-md-6 order-md-2 order-1">
                <div class="text-center overflow-hidden" style="border-radius: 1.25rem; background: linear-gradient(155deg, #2f55d4 0%, #3d67e8 60%, #2eca8b 100%); box-shadow: 0 20px 60px rgba(47,85,212,0.35);">
                    <div class="px-4 pt-4 pb-2">
                        <i class="uil uil-trophy" style="font-size: 2.2rem; color: #FFD700;"></i>
                    </div>
                    <div class="px-4 pb-5 d-flex flex-column align-items-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold mb-2" style="width: 72px; height: 72px; background: white; color: #2f55d4; font-size: 1.4rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15);">{{ initials($rank1->nama) }}</div>
                        <span class="badge mb-3 px-3 py-1" style="background: rgba(255,255,255,0.2); color: white; border-radius: 50px; font-size: 0.7rem; letter-spacing: 0.5px;">JUARA 1</span>
                        <h5 class="text-white fw-bold mb-1">{{ $rank1->nama }}</h5>
                        <p class="mb-3" style="color: rgba(255,255,255,0.65); font-size: 0.875rem;">RW {{ $rank1->rw }} / {{ $rank1->dusun }}</p>
                        <h3 class="text-white fw-bold mb-0">{{ (int) $rank1->poin }}</h3>
                        <small style="color: rgba(255,255,255,0.65);">poin · {{ number_format($rank1->total_kg, 1, ',', '.') }} kg</small>
                    </div>
                </div>
            </div>

            <!-- Rank 3 -->
            <div class="col-lg-4 col-md-6 order-md-3 order-3">
                @if($rank3)
                <div class="card border-0 text-center overflow-hidden" style="border-radius: 1.25rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                    <div class="card-body px-4 py-4 d-flex flex-column align-items-center">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white mb-3" style="width: 34px; height: 34px; background: linear-gradient(135deg, #cd7f32, #e8964a); font-size: 0.8rem;">#3</div>
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white mb-3" style="width: 62px; height: 62px; background: linear-gradient(135deg, #cd7f32, #e8964a); font-size: 1.3rem; box-shadow: 0 6px 16px rgba(205,127,50,0.35);">{{ initials($rank3->nama) }}</div>
                        <h6 class="fw-bold mb-1">{{ $rank3->nama }}</h6>
                        <p class="text-muted small mb-3">RW {{ $rank3->rw }} / {{ $rank3->dusun }}</p>
                        <h4 class="text-info fw-bold mb-0">{{ (int) $rank3->poin }}</h4>
                        <small class="text-muted">poin · {{ number_format($rank3->total_kg, 1, ',', '.') }} kg</small>
                    </div>
                    <div style="height: 4px; background: linear-gradient(90deg, #cd7f32, #e8964a);"></div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif
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
                    @forelse($entries->take(10) as $i => $entry)
                    @php $rank = $i + 1; @endphp
                    <div class="d-flex align-items-center px-4 py-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="me-4" style="width: 34px; text-align: center;">
                            @if($rank <= 3)
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white" style="width: 30px; height: 30px; background: {{ $medalBg[$rank] }}; font-size: 0.72rem;">{{ $rank }}</div>
                            @else
                                <span class="text-muted fw-semibold" style="font-size: 0.85rem;">#{{ $rank }}</span>
                            @endif
                        </div>
                        <div class="grow">
                            <div class="fw-semibold" style="font-size: 0.9rem; line-height: 1.3;">{{ $entry->nama }}</div>
                            <div class="text-muted" style="font-size: 0.78rem;">RW {{ $entry->rw }} / {{ $entry->dusun }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge rounded-pill px-3 py-2 d-block mb-1" style="background: linear-gradient(135deg, #eef2ff, #dde6ff); color: #2f55d4; font-size: 0.82rem; font-weight: 700;">{{ (int) $entry->poin }} poin</span>
                            <span class="text-muted" style="font-size: 0.74rem;">{{ number_format($entry->total_kg, 1, ',', '.') }} kg</span>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-5 text-center text-muted">
                        Belum ada data setoran bulan ini.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- RW Rankings -->
            <div class="col-lg-5">
                <div class="mb-5">
                    <h5 class="title mb-1">Peringkat RW / Dusun</h5>
                    <p class="text-muted small mb-0">RW dengan tingkat pemilahan tertinggi bulan ini.</p>
                </div>

                @php
                $rwColors = [
                    ['linear-gradient(90deg,#2eca8b,#06d6a0)', '#2eca8b'],
                    ['linear-gradient(90deg,#2f55d4,#7796ee)', '#2f55d4'],
                    ['linear-gradient(90deg,#17a2b8,#36c2da)', '#17a2b8'],
                    ['linear-gradient(90deg,#fd7e14,#ffa040)', '#fd7e14'],
                    ['linear-gradient(90deg,#6c757d,#9aa5b4)', '#6c757d'],
                ];
                @endphp

                <div class="card border-0 p-4 mb-4" style="border-radius: 1rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                    @forelse($rws->take(5) as $i => $rw)
                    @php [$gradient, $color] = $rwColors[$i] ?? $rwColors[4]; @endphp
                    <div class="{{ !$loop->last ? 'mb-4' : '' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-semibold" style="color: {{ $color }}; font-size: 0.9rem;">RW {{ $rw->rw }} / {{ $rw->dusun }}</span>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $rw->persen_dipilah }}% pemilahan · {{ $rw->jumlah_warga }} warga aktif</div>
                            </div>
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white shrink-0 ms-2" style="width: 28px; height: 28px; background: {{ $gradient }}; font-size: 0.68rem;">#{{ $i + 1 }}</div>
                        </div>
                        <div class="rounded-pill overflow-hidden" style="height: 8px; background: #f0f2f5;">
                            <div class="h-100 rounded-pill" style="width: {{ $rw->persen_dipilah }}%; background: {{ $gradient }};"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center mb-0">Belum ada data bulan ini.</p>
                    @endforelse
                </div>

                <!-- Spotlight card -->
                @if($rwTerbaik)
                <div class="card border-0 overflow-hidden" style="border-radius: 1rem; box-shadow: 0 8px 30px rgba(0,0,0,0.08);">
                    <div style="height: 4px; background: linear-gradient(90deg, #2f55d4, #2eca8b);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center me-3 shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #fff8e1, #fff3cd);">
                                <i data-feather="trending-up" style="width: 20px; height: 20px; color: #fd7e14; stroke-width: 2;"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">Sorotan Bulan Ini</h6>
                        </div>
                        <h6 class="mb-1 fw-bold" style="color: #2eca8b;">RW {{ $rwTerbaik->rw }} / {{ $rwTerbaik->dusun }}</h6>
                        <p class="text-muted small mb-3">{{ $rwTerbaik->persen_dipilah }}% tingkat pemilahan · {{ number_format($rwTerbaik->total_kg, 0, ',', '.') }} kg total</p>
                        <div class="rounded-pill overflow-hidden" style="height: 10px; background: #f0f2f5;">
                            <div class="h-100 rounded-pill" style="width: {{ $rwTerbaik->persen_dipilah }}%; background: linear-gradient(90deg, #2eca8b, #06d6a0);"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">Data diperbarui otomatis setiap bulan.</small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
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
            </div>

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
            </div>

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
            </div>

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
            </div>
        </div>
    </div>
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
