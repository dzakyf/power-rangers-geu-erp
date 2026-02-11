<!doctype html>
<html lang="en">
    <head>
        @include('layout.partials.head')
    </head>

    <body>
        <!-- Preloader -->
        @include('layout.partials.loader')

        <!-- Main Content Layout -->
        @yield('content-wrapper')

        <!-- Footer Scripts -->
        @include('layout.partials.footer-scripts')
    </body>
</html>
