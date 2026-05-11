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


        <div id="navigation">
            <!-- Navigation Menu-->   
            <ul class="navigation-menu">
                <li><a href="{{ url('/') }}" class="sub-menu-item">Beranda</a></li>
                <li><a href="{{ url('/leaderboard') }}" class="sub-menu-item">Papan Peringkat</a></li>
            </ul>
        </div>
    </div>
</header>
<!-- Navbar End -->
