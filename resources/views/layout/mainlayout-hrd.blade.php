<!doctype html>
<html lang="en">
<head>
    @include('layout.adminor.head')
</head>
<body class="main-body app sidebar-mini ltr">
@php
    $adminorMenuModule = 'HRD';
    $adminorHome = 'home-hrd';
@endphp
    @include('layout.adminor.loader')

    <div class="page">
        @include('layout.adminor.header')

        <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
        <div class="sticky">
            <aside class="app-sidebar sidebar-scroll">
                @include('layout.adminor.sidebar')
            </aside>
        </div>

        @include('layout.adminor.vendor-scripts')

        <div class="main-content app-content">
            <div class="main-container container-fluid">
                @yield('content-wrapper')
            </div>
        </div>

        @include('layout.adminor.footer')
    </div>

    @include('ui-components.modal-bp')
    @include('layout.adminor.scripts')
</body>
</html>
