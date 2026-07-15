<!-- resources/views/layouts/landrick/main.blade.php -->

<!doctype html>
<html lang="en" dir="ltr">

    <head>
        <meta charset="utf-8">
        <title>@yield('title', 'SIPS - Sistem Informasi Pemilahan Sampah')</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Sistem Informasi Pemilahan Sampah Desa Banjarsari">
        <meta name="keywords" content="Pemilahan, Sampah, Organik, Anorganik, Desa">
        <meta name="author" content="Desa Banjarsari">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- favicon -->
        <link rel="shortcut icon" href="{{ asset('assets/images/k_favicon_32x.png') }}">

        <!-- Css -->
        <link href="{{ asset('assets/libs/simplebar/simplebar.min.css') }}" rel="stylesheet">
        <!-- Css -->
        <link href="{{ asset('assets/libs/tiny-slider/tiny-slider.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/libs/tobii/css/tobii.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/libs/js-datepicker/datepicker.min.css') }}" rel="stylesheet">
        <link href="{{ asset('assets/libs/animate.css/animate.min.css') }}" rel="stylesheet">
        <!-- Bootstrap Css -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" class="theme-opt" rel="stylesheet" type="text/css">
        <!-- Icons Css -->
        <link href="{{ asset('assets/libs/@mdi/font/css/materialdesignicons.min.css') }}" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="{{ asset('assets/libs/@iconscout/unicons/css/line.css') }}" type="text/css" rel="stylesheet">
        <!-- Style Css-->
        <link href="{{ asset('assets/css/style.css') }}" id="color-opt" class="theme-opt" rel="stylesheet" type="text/css">

        @yield('css')
    </head>

    <body>

        <!-- Main Content -->
        <div>
            @yield('content')
        </div>


        <!-- Back to top -->
        <a href="#" onclick="topFunction()" id="back-to-top" class="back-to-top fs-5"><i data-feather="arrow-up" class="fea icon-sm icons align-middle"></i></a>
        <!-- Back to top -->

        <!-- Javascript -->
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
        <script src="{{ asset('assets/libs/gumshoejs/gumshoe.min.js') }}"></script>
        <script src="{{ asset('assets/libs/tiny-slider/min/tiny-slider.js') }}"></script>
        <script src="{{ asset('assets/js/easy_background.js') }}"></script>
        @stack('scripts')
        <script src="{{ asset('assets/libs/swiper/swiper-bundle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/jarallax/jarallax.min.js') }}"></script>
        <script src="{{ asset('assets/libs/shufflejs/shuffle.min.js') }}"></script>
        <script src="{{ asset('assets/libs/tobii/js/tobii.min.js') }}"></script>
        <script src="{{ asset('assets/libs/js-datepicker/datepicker.min.js') }}"></script>
        <script src="{{ asset('assets/libs/wow.js/wow.min.js') }}"></script>
        <script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins.init.js') }}"></script>
        <script src="{{ asset('assets/js/landrick-app.js') }}"></script>

    </body>

</html>
