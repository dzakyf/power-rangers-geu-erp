<!doctype html>
<html lang="en">
<head>
    @php
        $adminorMenuModule = 'CNS';
        $adminorHome = 'home-cns';
    @endphp
    @include('layout.adminor.head')
</head>
<body class="main-body app sidebar-mini ltr">
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
    <script>
        window.cnsParseNumber = function (val, fallback) {
            if (typeof val === 'number') {
                return isFinite(val) ? val : (fallback !== undefined ? fallback : 0);
            }
            var str = String(val === null || val === undefined ? '' : val).trim().replace(/,/g, '');
            if (str === '') {
                return fallback !== undefined ? fallback : 0;
            }
            var n = parseFloat(str);
            return isFinite(n) ? n : (fallback !== undefined ? fallback : 0);
        };

        window.cnsFormatNumber = function (val, decimals) {
            var d = decimals !== undefined ? decimals : 2;
            var n = window.cnsParseNumber(val, NaN);
            if (!isFinite(n)) {
                return '';
            }
            return n.toLocaleString('en-US', {
                minimumFractionDigits: d,
                maximumFractionDigits: d
            });
        };

        window.cnsInitDecimalInputs = function (root) {
            $(root || document).find('input.cns-decimal').each(function () {
                var $el = $(this);
                if ($el.is(':focus')) {
                    return;
                }
                var raw = String($el.val() === null || $el.val() === undefined ? '' : $el.val()).trim();
                if (raw !== '') {
                    var dec = $el.data('decimals');
                    $el.val(window.cnsFormatNumber(raw, dec !== undefined ? parseInt(dec, 10) : 2));
                }
            });
        };

        $(document).on('focus', 'input.cns-decimal', function () {
            var raw = String($(this).val() === null || $(this).val() === undefined ? '' : $(this).val()).trim();
            if (raw !== '') {
                $(this).val(raw.replace(/,/g, ''));
            }
        });

        $(document).on('blur', 'input.cns-decimal', function () {
            var $el = $(this);
            var raw = String($el.val() === null || $el.val() === undefined ? '' : $el.val()).trim();
            if (raw !== '') {
                var dec = $el.data('decimals');
                var formatted = window.cnsFormatNumber(raw, dec !== undefined ? parseInt(dec, 10) : 2);
                if (formatted !== '') {
                    $el.val(formatted);
                }
            }
        });

        $(document).on('submit', 'form', function () {
            $(this).find('input.cns-decimal').each(function () {
                var raw = String($(this).val() === null || $(this).val() === undefined ? '' : $(this).val()).trim();
                if (raw !== '') {
                    $(this).val(raw.replace(/,/g, ''));
                }
            });
        });

        $(function () {
            window.cnsInitDecimalInputs(document);
        });
        $(document).ajaxComplete(function () {
            window.cnsInitDecimalInputs(document);
        });
    </script>
</body>
</html>
