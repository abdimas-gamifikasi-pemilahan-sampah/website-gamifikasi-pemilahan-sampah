<!DOCTYPE html>
<html lang="en">

<meta charset="utf-8" />
<title>@yield('title', 'SIPS | Sistem Informasi Pemilahan Sampah')</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta content="Admin & Dashboards Template" name="description" />
<meta content="Pixeleyez" name="author" />

<!-- layout setup -->
<script type="module" src="{{ asset('assets/js/layout-setup.js') }}"></script>

<!-- App favicon -->
<link rel="shortcut icon" href="{{ asset('assets/images/k_favicon_32x.png') }}">

@yield('css')
@include('partials.head-css')

<body>

    <div id="sidebar-backdrop"
         style="display:none; position:fixed; inset:0; background:transparent; z-index:1004; cursor:pointer;"
         aria-hidden="true"></div>

    @include('partials.header')
    @include('partials.sidebar')

    <main class="app-wrapper">
        <div class="container-fluid">

            @include('partials.page-title')

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="ri-error-warning-line me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
            @include('partials.switcher')
            @include('partials.scroll-to-top')

        </div>
    </main>

    @include('partials.footer')

    @include('partials.vendor-scripts')

    @yield('js')

</body>

</html>
