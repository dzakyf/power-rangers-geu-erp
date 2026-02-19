<!doctype html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layout.partials.head')
</head>

<body>
    <!-- Preloader -->
    @include('layout.partials.loader')

    <!-- Top Navbar -->
    @include('layout.partials.top-nav-mitra')

    <!-- Side Menu -->
    @include('layout.partials.sidemenu')
    @include('layout.plugins')

    <!-- Main Content Wrapper -->
    <div class="main-content d-flex flex-column hide-sidemenu" id="PagesContent">
        <!-- Main Content Layout -->
        @yield('content-wrapper')

        <!-- Footer -->
        @include('layout.partials.footer')
    </div>
    <!-- End Main Content Wrapper -->

    <!-- Theme Color customizer Right Modal -->
    @include('layout.partials.theme-color-customizer')

    <!-- Footer Scripts -->
    @include('layout.partials.footer-scripts')
</body>

</html>
<link href="{{ asset('ajaxloader/ajaxloader.css') }}" type="text/css" rel="stylesheet" />

<script src="{{ asset('jquery.ajaxloader.1.5.1.js') }}"></script>
<script src="{{ asset('blockui.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('jquery.coolautosuggest.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('jquery.coolfieldset.css') }}" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>