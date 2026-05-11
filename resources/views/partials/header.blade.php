<!-- Begin Header -->
<header class="app-header" id="appHeader">
    <div class="container-fluid w-100">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <a href="/sips/dashboard" class="fs-18 fw-semibold d-flex align-items-center">
                    <img height="30" class="header-sidebar-logo-default d-none" alt="Logo" src="{{ asset('assets/images/logo-dark.png') }}">
                    <img height="30" class="header-sidebar-logo-light d-none" alt="Logo" src="{{ asset('assets/images/logo-light.png') }}">
                    <img height="30" class="header-sidebar-logo-small d-none" alt="Logo" src="{{ asset('assets/images/logo-md.png') }}">
                    <img height="30" class="header-sidebar-logo-small-light d-none" alt="Logo" src="{{ asset('assets/images/logo-md-light.png') }}">
                </a>

                <button type="button" class="vertical-toggle btn btn-light-light text-muted icon-btn fs-5 rounded-pill" id="toggleSidebar" aria-label="Toggle Sidebar">
                    <i class="bi bi-arrow-bar-left header-icon"></i>
                </button>

                <button type="button" class="horizontal-toggle btn btn-light-light text-muted icon-btn fs-5 rounded-pill d-none" id="toggleHorizontal" aria-label="Toggle Horizontal Menu">
                    <i class="ri-menu-2-line header-icon"></i>
                </button>
            </div>

            <div class="d-flex align-items-center gap-1">
                <div class="dark-mode-btn" id="toggleMode">
                    <button class="btn header-btn active" id="lightModeBtn" aria-label="Light Mode">
                        <i class="bi bi-brightness-high"></i>
                    </button>
                    <button class="btn header-btn" id="darkModeBtn" aria-label="Dark Mode">
                        <i class="bi bi-moon-stars"></i>
                    </button>
                </div>

                <div class="dropdown pe-dropdown-mega d-none d-md-block">
                    <button class="header-profile-btn btn gap-1 text-start" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="header-btn btn position-relative">
                            <img src="{{ asset('assets/images/avatar/avatar-10.jpg') }}" alt="Avatar Image" class="img-fluid rounded-circle">
                            <span class="position-absolute translate-middle badge border border-light rounded-circle bg-success"><span class="visually-hidden">online</span></span>
                        </span>
                        <div class="d-none d-lg-block pe-2">
                            <span class="d-block mb-0 fs-13 fw-semibold">Paul Danielle</span>
                            <span class="d-block mb-0 fs-12 text-muted">Pendiri</span>
                        </div>
                    </button>
                    <div class="dropdown-menu dropdown-mega-sm header-dropdown-menu p-3">
                        <div class="border-bottom pb-2 mb-2 d-flex align-items-center gap-2">
                            <img src="{{ asset('assets/images/avatar/avatar-10.jpg') }}" alt="Avatar Image" class="avatar-md">
                            <div>
                                <a href="javascript:void(0)">
                                    <h6 class="mb-0 lh-base">Paul Danielle</h6>
                                </a>
                                <p class="mb-0 fs-13 text-muted">paul@fabkin.com</p>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li><a class="dropdown-item" href="javascript:void(0)"><i class="bi bi-box-arrow-right me-1"></i> Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- END Header -->
