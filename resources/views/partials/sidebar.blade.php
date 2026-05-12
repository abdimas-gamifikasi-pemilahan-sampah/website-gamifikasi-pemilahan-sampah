<aside class="pe-app-sidebar" id="sidebar">
    <div class="pe-app-sidebar-logo px-6 d-flex align-items-center position-relative">
        <!--begin::Brand Image-->
        <a href="{{ route('sips.dashboard') }}" class="fs-18 fw-semibold">
            <img height="30" class="pe-app-sidebar-logo-default d-none" alt="Logo" src="{{ asset('assets/images/logo-dark.png') }}">
            <img height="30" class="pe-app-sidebar-logo-light d-none" alt="Logo" src="{{ asset('assets/images/logo-light.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize d-none" alt="Logo" src="{{ asset('assets/images/logo-md.png') }}">
            <img height="30" class="pe-app-sidebar-logo-minimize-light d-none" alt="Logo" src="{{ asset('assets/images/logo-md-light.png') }}">
            <!-- FabKin -->
        </a>
        <!--end::Brand Image-->
    </div> 
    <nav class="pe-app-sidebar-menu nav nav-pills" data-simplebar id="sidebar-simplebar">
        <ul class="pe-main-menu list-unstyled">
            <li class="pe-menu-title">
                Manajemen SIPS
            </li>
            <li class="pe-slide">
                <a href="{{ route('sips.dashboard') }}" class="pe-nav-link">
                    <i class="bi bi-bar-chart-line pe-nav-icon"></i>
                    <span class="pe-nav-content">Dasbor Ringkasan</span>
                </a>
            </li>
            @if(auth()->user()->isAdmin())
            <li class="pe-slide pe-has-sub">
                <a href="#collapseMasterData" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseMasterData">
                    <i class="bi bi-database pe-nav-icon"></i>
                    <span class="pe-nav-content">Data Master</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseMasterData">
                    <li class="slide pe-nav-content1">
                        <a href="javascript:void(0)">Data Master</a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.warga.index') }}" class="pe-nav-link">
                            Data Warga
                        </a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.tarif.index') }}" class="pe-nav-link">
                            Manajemen Tarif
                        </a>
                    </li>
                </ul>
            </li>
            @endif
            <li class="pe-slide pe-has-sub">
                <a href="#collapseTransactions" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseTransactions">
                    <i class="bi bi-recycle pe-nav-icon"></i>
                    <span class="pe-nav-content">Transaksi</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseTransactions">
                    <li class="slide pe-nav-content1">
                        <a href="javascript:void(0)">Transaksi</a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.setoran.create') }}" class="pe-nav-link">
                            Input Setoran
                        </a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.setoran.index') }}" class="pe-nav-link">
                            Riwayat Setoran
                        </a>
                    </li>
                </ul>
            </li>
            <li class="pe-slide pe-has-sub">
                <a href="#collapseAnalytics" class="pe-nav-link" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseAnalytics">
                    <i class="bi bi-bar-chart-line pe-nav-icon"></i>
                    <span class="pe-nav-content">Analitik & Laporan</span>
                    <i class="ri-arrow-down-s-line pe-nav-arrow"></i>
                </a>
                <ul class="pe-slide-menu collapse" id="collapseAnalytics">
                    <li class="slide pe-nav-content1">
                        <a href="javascript:void(0)">Analitik & Laporan</a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="javascript:void(0)" class="pe-nav-link">
                            Tren Partisipasi (Segera Hadir)
                        </a>
                    </li>
                    <li class="pe-slide-item">
                        <a href="{{ route('sips.leaderboard') }}" class="pe-nav-link">
                            Papan Peringkat
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</aside>
