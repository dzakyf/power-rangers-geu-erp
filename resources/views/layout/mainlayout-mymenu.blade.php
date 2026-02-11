<!doctype html>
<html lang="en">

<head>
    @include('layout.partials.head')
</head>

<body>
    <!-- Preloader -->
    @include('layout.partials.loader')

    <!-- Top Navbar -->
    @include('layout.partials.top-nav-mymenu')

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