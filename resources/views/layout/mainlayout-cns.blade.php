<!doctype html>
<html lang="en">
<head>
    @php
        $adminorMenuModule = 'CNS';
        $adminorHome = 'home-cns';
    @endphp
    @include('layout.adminor.head')
    <style>
        /* CNS Standardized Layout Styles */
        .main-content .main-content-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem;
            height: auto;
            min-height: 0;
            margin: 1.25rem 0 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e6e8f0;
        }
        @media (min-width: 992px) {
            .main-content .main-content-header {
                display: flex;
            }
        }
        .main-content .main-content-header h1 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.3;
        }
        .main-content .main-content-header .breadcrumb {
            margin: 0;
            padding: 0;
            background-color: transparent;
            font-size: 0.8125rem;
        }

        .cns-card {
            border-radius: 12px !important;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            border: 2px solid #e6e8f0;
            background-color: #ffffff;
            margin-bottom: 1.25rem;
        }
        .cns-card .card-header {
            border-radius: 12px 12px 0 0 !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding-top: 0.875rem;
            padding-bottom: 0.875rem;
            background-color: #ffffff;
            border-bottom: 1px solid #eef0f6;
        }
        .cns-card .card-header .card-title {
            border-radius: 12px !important;
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
        }

        .cns-filter-bar {
            margin-bottom: 1rem;
        }
        .cns-filter-bar:not(.row) {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .cns-grid-wrapper {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }
        .cns-grid-wrapper .gridbox,
        .cns-grid-wrapper .cns-grid {
            box-sizing: content-box !important;
            max-width: 100% !important;
        }
        .cns-grid-wrapper .gridbox .xhdr,
        .cns-grid-wrapper .cns-grid .xhdr {
            height: auto !important;
            min-height: 0 !important;
        }
        .cns-grid-wrapper .gridbox table.hdr td,
        .cns-grid-wrapper .cns-grid table.hdr td {
            color: #000000 !important;
            font-weight: bold !important;
            text-align: center !important;
            vertical-align: middle !important;
            font-size: 12px !important;
            padding: 5px 6px !important;
            line-height: 1.2 !important;
            height: auto !important;
        }
        .cns-grid-wrapper .gridbox table.hdr td.filter,
        .cns-grid-wrapper .cns-grid table.hdr td.filter {
            padding: 3px 4px !important;
            font-weight: normal !important;
        }
        .cns-grid-wrapper .gridbox table.hdr td div.hdrcell,
        .cns-grid-wrapper .cns-grid table.hdr td div.hdrcell {
            padding: 0 4px !important;
            line-height: 1.2 !important;
            white-space: nowrap !important;
        }
        .cns-grid-wrapper .gridbox table.hdr td.filter input,
        .cns-grid-wrapper .gridbox table.hdr td.filter select,
        .cns-grid-wrapper .cns-grid table.hdr td.filter input,
        .cns-grid-wrapper .cns-grid table.hdr td.filter select {
            width: 100% !important;
            height: 22px !important;
            padding: 1px 4px !important;
            font-size: 11px !important;
            line-height: 1.2 !important;
            box-sizing: border-box !important;
        }
        .cns-grid-wrapper .gridbox td,
        .cns-grid-wrapper .cns-grid td {
            font-size: 12px !important;
            vertical-align: middle !important;
        }

        .cns-section-header {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e293b;
            background-color: #ffffff;
            border-left: 3px solid var(--primary-bg-color, #623AA2);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
            padding: 0.5rem 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            letter-spacing: 0.02em;
        }

        /* Top bar solid ungu pekat (#623AA2) dengan ikon/teks putih; area isi bersih #f8fafc */
        body.main-body,
        .page,
        .main-content.app-content {
            background-color: #f8fafc !important;
        }
        .main-header.side-header,
        .main-header.side-header.fixed-header {
            background-color: var(--primary-bg-color, #623AA2) !important;
            border-block-end: 1px solid rgba(255, 255, 255, 0.14) !important;
        }
        .main-header.side-header .nav-link,
        .main-header.side-header .nav-link i,
        .main-header.side-header .header-icons,
        .main-header.side-header .app-sidebar__toggle a,
        .main-header.side-header .app-sidebar__toggle svg,
        .main-header.side-header .main-profile-menu .profile-user span,
        .main-header.side-header .navbar-toggler-icon {
            color: #ffffff !important;
            fill: #ffffff !important;
        }
        .main-header.side-header .nav-link:hover,
        .main-header.side-header .app-sidebar__toggle a:hover {
            background-color: rgba(255, 255, 255, 0.12);
            border-radius: 6px;
        }

        .cns-grid-wrapper .gridbox td span.badge.cns-status-badge,
        .cns-status-badge {
            display: inline-block;
            font-size: 0.6rem !important;
            font-weight: 800 !important;
            line-height: 1.2 !important;
            padding: 0.25rem 0.55rem !important;
            border-radius: 50rem !important;
            letter-spacing: 0.02em;
        }
    </style>
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
        <script>
            window.cnsStatusBadge = function (val) {
                var raw = String(val === null || val === undefined ? '' : val).replace(/<[^>]*>/g, '').trim();
                if (!raw || raw === '-') {
                    return raw;
                }
                var upper = raw.toUpperCase();
                var cls = 'bg-secondary text-white';
                if (/\b(REJECTED|REJECT|CANCELLED|CANCELED|INACTIVE|CLOSED|VOID|FAILED|FAIL|DECLINED)\b/.test(upper)) {
                    cls = 'bg-danger text-white';
                } else if (/\b(DRAFT|CREATED|PENDING|SUBMITTED|IN_PROGRESS|IN PROGRESS|WAITING|PARTIAL|ONGOING|PROCESS|REVIEW)\b/.test(upper)) {
                    cls = 'bg-warning text-dark';
                } else if (/\b(APPROVED|ACTIVE|FINAL|COMPLETED|COMPLETE|OPEN|VERIFIED|DONE|RELEASED|CONFIRMED|PASSED|PASS|OK)\b/.test(upper)) {
                    cls = 'bg-success text-white';
                } else if (/\b(POSTED|SENT|ISSUED|SHIPPED|DELIVERED)\b/.test(upper)) {
                    cls = 'bg-info text-white';
                }
                var safe = raw.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                return '<span class="badge rounded-pill cns-status-badge ' + cls + '">' + safe + '</span>';
            };

            if (typeof window.eXcell === 'function') {
                window.eXcell_cns_badge = function (cell) {
                    if (cell) {
                        this.cell = cell;
                        this.grid = this.cell.parentNode.grid;
                    }
                    this.edit = function () {};
                    this.isDisabled = function () { return true; };
                    this.setValue = function (val) {
                        var plain = String(val === null || val === undefined ? '' : val).replace(/<[^>]*>/g, '').trim();
                        this.cell._cnsRawValue = plain;
                        this.setCValue(window.cnsStatusBadge(plain), plain);
                    };
                    this.getValue = function () {
                        return this.cell._cnsRawValue !== undefined
                            ? this.cell._cnsRawValue
                            : String(this.cell.textContent || this.cell.innerText || '').trim();
                    };
                };
                window.eXcell_cns_badge.prototype = new window.eXcell();
            }
        </script>

        <div class="main-content app-content">
            <div class="main-container container-fluid">
                @yield('content-wrapper')
            </div>
        </div>

        @include('layout.adminor.footer')
    </div>

    @include('ui-components.modal-bp')
    @include('ui-components.modal-doc-preview')
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
            @if(session('success'))
                swal('Berhasil!', @json(session('success')), 'success');
            @endif
            @if(session('error'))
                swal('Error', @json(session('error')), 'error');
            @endif
        });
        $(document).ajaxComplete(function () {
            window.cnsInitDecimalInputs(document);
        });
    </script>
</body>
</html>
