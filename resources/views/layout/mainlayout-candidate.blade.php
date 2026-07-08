<!doctype html>
<html lang="en">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('layout.partials.head')
</head>

<body>
    @include('layout.partials.loader')
    @include('ui-components.modal')
    @include('layout.partials.top-nav-candidate')
    @include('layout.partials.sidemenu')
    @include('layout.plugins')

    <div class="main-content d-flex flex-column hide-sidemenu" id="PagesContent">
        @yield('content-wrapper')
        @include('layout.partials.footer')
    </div>

    @include('layout.partials.theme-color-customizer')
    @include('layout.partials.footer-scripts')

</body>
</html>
<link href="{{ asset('ajaxloader/ajaxloader.css') }}" type="text/css" rel="stylesheet" />
<script src="{{ asset('jquery.ajaxloader.1.5.1.js') }}"></script>
<script src="{{ asset('blockui.js') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('blockui.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('jquery.coolautosuggest.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('jquery.coolfieldset.css') }}" />
@stack('rightmenu-script')
@stack('modals-script')
@stack('scripts')
