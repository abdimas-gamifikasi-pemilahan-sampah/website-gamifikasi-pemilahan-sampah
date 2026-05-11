<!-- Navbar Start -->
<header id="topnav" class="defaultscroll sticky">
    <div class="container">
        <!-- Logo container-->
        <a class="logo" href="{{ url('/') }}">
            <img src="{{ asset('assets/images/logo-dark.png') }}" height="24" class="logo-light-mode" alt="">
            <img src="{{ asset('assets/images/logo-light.png') }}" height="24" class="logo-dark-mode" alt="">
        </a>                
        <!-- Logo End -->

        <!-- End Logo container-->
        <div class="menu-extras">
            <div class="menu-item">
                <!-- Mobile menu toggle-->
                <a class="navbar-toggle" id="isToggle" onclick="toggleMenu()">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
                <!-- End mobile menu toggle-->
            </div>
        </div>

        <!--Login button Start-->
        <ul class="buy-button list-inline mb-0">
            <li class="list-inline-item mb-0">
                <a href="javascript:void(0)" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                    <div class="btn btn-icon btn-pills btn-soft-primary"><i data-feather="settings" class="fea icon-sm"></i></div>
                </a>
            </li>
    
            <li class="list-inline-item ps-1 mb-0">
                <a href="https://1.envato.market/landrick-laravel" target="_blank">
                    <div class="btn btn-icon btn-pills btn-primary"><i data-feather="shopping-cart" class="fea icon-sm"></i></div>
                </a>
            </li>
        </ul>
        <!--Login button End-->

        <div id="navigation">
            <!-- Navigation Menu-->   
            <ul class="navigation-menu">
                <li><a href="{{ url('/') }}" class="sub-menu-item">Home</a></li>
                <li class="has-submenu parent-parent-menu-item">
                    <a href="javascript:void(0)">Landing</a><span class="menu-arrow"></span>
                    <ul class="submenu megamenu">
                        <li>
                            <ul>
                                <li class="megamenu-head"><i class="uil uil-book-open fs-6 align-middle"></i> Landing Pages</li>
                                <li><a href="{{ url('/index-saas') }}" class="sub-menu-item">Saas</a></li>
                                <li><a href="{{ url('/index-classic-saas') }}" class="sub-menu-item">Classic Saas</a></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li><a href="{{ url('/page-contact-one') }}" class="sub-menu-item">Contact us</a></li>
            </ul><!--end navigation menu-->
        </div><!--end navigation-->
    </div><!--end container-->
</header><!--end header-->
<!-- Navbar End -->
