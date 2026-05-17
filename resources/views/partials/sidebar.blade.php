<aside class="pe-app-sidebar" id="sidebar">
    <div class="pe-app-sidebar-logo px-6 d-flex align-items-center position-relative">
        <a href="{{ route('sips.dashboard') }}" class="fs-18 fw-semibold">
            <img height="30" class="pe-app-sidebar-logo-default d-none" alt="Logo" src="{{ asset('assets/images/logo-dark.png') }}">
            <img height="30" class="pe-app-sidebar-logo-light d-none" alt="Logo" src="{{ asset('assets/images/logo-light.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize d-none" alt="Logo" src="{{ asset('assets/images/logo-md.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize-light d-none" alt="Logo" src="{{ asset('assets/images/logo-md-light.png') }}">
        </a>
    </div>

    <nav class="pe-app-sidebar-menu nav nav-pills" data-simplebar id="sidebar-simplebar">
        <ul class="pe-main-menu list-unstyled">
            <li class="pe-menu-title">Manajemen SIPS</li>

            {{-- Dashboard --}}
            <li class="pe-slide">
                <a href="{{ route('sips.dashboard') }}"
                   class="pe-nav-link {{ request()->routeIs('sips.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-bar-chart-line pe-nav-icon"></i>
                    <span class="pe-nav-content">Dasbor Ringkasan</span>
                </a>
            </li>

            {{-- Data Master (admin only) --}}
            @if(auth()->user()->isAdmin())
            @php $masterActive = request()->routeIs('sips.warga.*', 'sips.tarif.*', 'sips.import.*'); @endphp
            <li class="pe-slide pe-has-sub">
                <a href="#collapseMasterData"
                   class="pe-nav-link {{ $masterActive ? 'active' : '' }}"
                   data-bs-toggle="collapse"
                   aria-expanded="{{ $masterActive ? 'true' : 'false' }}"
                   aria-controls="collapseMasterData">
                    <i class="bi bi-database pe-nav-icon"></i>
                    <span class="pe-nav-content">Data Master</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse {{ $masterActive ? 'show' : '' }}" id="collapseMasterData">
                    <li class="slide pe-nav-content1">
                        <a href="javascript:void(0)">Data Master</a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.warga.index') }}"
                           class="pe-nav-link {{ request()->routeIs('sips.warga.*') ? 'active' : '' }}">
                            Data Warga
                        </a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.tarif.index') }}"
                           class="pe-nav-link {{ request()->routeIs('sips.tarif.*') ? 'active' : '' }}">
                            Manajemen Tarif
                        </a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.import.index') }}"
                           class="pe-nav-link {{ request()->routeIs('sips.import.*') ? 'active' : '' }}">
                            Import Data Warga
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            {{-- Transaksi --}}
            @php $transaksiActive = request()->routeIs('sips.setoran.*', 'sips.pembayaran.*'); @endphp
            <li class="pe-slide pe-has-sub">
                <a href="#collapseTransactions"
                   class="pe-nav-link {{ $transaksiActive ? 'active' : '' }}"
                   data-bs-toggle="collapse"
                   aria-expanded="{{ $transaksiActive ? 'true' : 'false' }}"
                   aria-controls="collapseTransactions">
                    <i class="bi bi-recycle pe-nav-icon"></i>
                    <span class="pe-nav-content">Transaksi</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse {{ $transaksiActive ? 'show' : '' }}" id="collapseTransactions">
                    <li class="slide pe-nav-content1">
                        <a href="javascript:void(0)">Transaksi</a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.setoran.create') }}"
                           class="pe-nav-link {{ request()->routeIs('sips.setoran.create') ? 'active' : '' }}">
                            Input Setoran
                        </a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.setoran.index') }}"
                           class="pe-nav-link {{ request()->routeIs('sips.setoran.index', 'sips.setoran.show', 'sips.setoran.kwitansi') ? 'active' : '' }}">
                            Riwayat Setoran
                        </a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.pembayaran.index') }}"
                           class="pe-nav-link {{ request()->routeIs('sips.pembayaran.index') ? 'active' : '' }}">
                            Laporan Pembayaran
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Analitik & Laporan --}}
            @php $analitikActive = request()->routeIs('sips.leaderboard', 'sips.analitik.*'); @endphp
            <li class="pe-slide pe-has-sub">
                <a href="#collapseAnalytics"
                   class="pe-nav-link {{ $analitikActive ? 'active' : '' }}"
                   data-bs-toggle="collapse"
                   aria-expanded="{{ $analitikActive ? 'true' : 'false' }}"
                   aria-controls="collapseAnalytics">
                    <i class="bi bi-bar-chart-line pe-nav-icon"></i>
                    <span class="pe-nav-content">Analitik & Laporan</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse {{ $analitikActive ? 'show' : '' }}" id="collapseAnalytics">
                    <li class="slide pe-nav-content1">
                        <a href="javascript:void(0)">Analitik & Laporan</a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.analitik.index') }}"
                           class="pe-nav-link {{ request()->routeIs('sips.analitik.*') ? 'active' : '' }}">
                            Analitik Data
                        </a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.leaderboard') }}"
                           class="pe-nav-link {{ request()->routeIs('sips.leaderboard') ? 'active' : '' }}">
                            Papan Peringkat
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
    </nav>
</aside>
