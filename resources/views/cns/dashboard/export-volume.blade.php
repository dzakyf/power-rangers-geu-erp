@extends('layout.mainlayout-cns')

@section('content-wrapper')
<style>
    #shipmentsTable {
        font-size: 12px;
    }
    #shipmentsTable th {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.3px;
        padding: 8px 10px;
        white-space: nowrap;
        background-color: #f8fafc;
        color: #475569;
    }
    #shipmentsTable td {
        font-size: 12px;
        padding: 6px 10px;
        vertical-align: middle;
        white-space: nowrap;
        color: #334155;
    }
    #shipmentsTable .badge {
        font-size: 10.5px;
        font-weight: 500;
        padding: 3px 6px;
    }
    .pagination-sm .page-link {
        font-size: 11.5px;
        padding: 3px 8px;
        color: #475569;
    }
    .pagination-sm .page-item.active .page-link {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: #fff;
    }
</style>

<!-- Main Content Header -->
<div class="main-content-header mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="mb-1" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50;">Dashboard Export Volume</h1>
            <ol class="breadcrumb mb-0 p-0 bg-transparent">
                <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">CNS</a></li>
                <li class="breadcrumb-item">Dashboard</li>
                <li class="breadcrumb-item active" aria-current="page">Export Volume</li>
            </ol>
        </div>
        <div class="mt-2 mt-sm-0">
            <a id="btnExportExcel" href="{{ route('cns-dashboard-export-volume.export-excel', request()->all()) }}" class="btn btn-success btn-sm shadow-sm">
                <i class="fe fe-download me-1"></i> Export Data (CSV/Excel)
            </a>
            <button type="button" id="btnRefresh" class="btn btn-primary btn-sm shadow-sm ms-1">
                <i class="fe fe-refresh-cw me-1"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4 border-0 shadow-sm" style="border-radius: 8px;">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
        <i class="fe fe-filter text-primary me-2" style="font-size: 1.1rem;"></i>
        <h6 class="mb-0 font-weight-bold" style="color: #334155;">Filter Data Ekspor</h6>
    </div>
    <div class="card-body">
        <form id="filterForm" method="GET" action="{{ route('cns-dashboard-export-volume.index') }}">
            <div class="row g-3">
                <!-- Tahun -->
                <div class="col-md-2 col-sm-6">
                    <label for="filter_year" class="form-label font-weight-bold text-muted small">Tahun</label>
                    <select class="form-control form-select select2-basic" id="filter_year" name="year">
                        <option value="all" {{ $selectedYear === 'all' ? 'selected' : '' }}>-- Semua Tahun --</option>
                        @foreach ($availableYears as $yr)
                            <option value="{{ $yr }}" {{ (string)$selectedYear === (string)$yr ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Bulan -->
                <div class="col-md-2 col-sm-6">
                    <label for="filter_month" class="form-label font-weight-bold text-muted small">Bulan</label>
                    @php
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                    @endphp
                    <select class="form-control form-select select2-basic" id="filter_month" name="month">
                        <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>-- Semua Bulan --</option>
                        @foreach ($months as $num => $name)
                            <option value="{{ $num }}" {{ (string)$selectedMonth === (string)$num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Consignee -->
                <div class="col-md-3 col-sm-6">
                    <label for="filter_consignee" class="form-label font-weight-bold text-muted small">Consignee</label>
                    <select class="form-control form-select select2-basic" id="filter_consignee" name="consignee">
                        <option value="all" {{ $selectedConsignee === 'all' ? 'selected' : '' }}>-- Semua Consignee --</option>
                        @foreach ($availableConsignees as $code => $name)
                            <option value="{{ $code }}" {{ (string)$selectedConsignee === (string)$code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Country / Port -->
                <div class="col-md-3 col-sm-6">
                    <label for="filter_country" class="form-label font-weight-bold text-muted small">Negara / Pelabuhan Tujuan</label>
                    <select class="form-control form-select select2-basic" id="filter_country" name="country">
                        <option value="all" {{ $selectedCountry === 'all' ? 'selected' : '' }}>-- Semua Negara/POD --</option>
                        @foreach ($availableCountries as $cnt)
                            <option value="{{ $cnt }}" {{ (string)$selectedCountry === (string)$cnt ? 'selected' : '' }}>{{ $cnt }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Incoterm -->
                <div class="col-md-2 col-sm-6">
                    <label for="filter_incoterm" class="form-label font-weight-bold text-muted small">Incoterm</label>
                    <select class="form-control form-select select2-basic" id="filter_incoterm" name="incoterm">
                        <option value="all" {{ $selectedIncoterm === 'all' ? 'selected' : '' }}>-- Semua Incoterm --</option>
                        @foreach ($availableIncoterms as $inco)
                            <option value="{{ $inco }}" {{ (string)$selectedIncoterm === (string)$inco ? 'selected' : '' }}>{{ $inco }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12 d-flex justify-content-end gap-2">
                    <button type="button" id="btnResetFilter" class="btn btn-outline-secondary btn-sm px-3 me-2">
                        <i class="fe fe-rotate-ccw me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm">
                        <i class="fe fe-check me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="row mb-4">
    <!-- Total Volume MT -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px; border-left: 4px solid #3b82f6 !important;">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Total Export Volume</span>
                        <h3 class="mb-0 mt-1 font-weight-bold text-dark" id="kpi_volume">
                            {{ number_format($kpis['total_volume_mt'], 2, '.', ',') }} <span style="font-size: 1rem; color: #64748b;">MT</span>
                        </h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #eff6ff;">
                        <i class="fe fe-trending-up" style="font-size: 1.4rem; color: #3b82f6;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Kontainer -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px; border-left: 4px solid #10b981 !important;">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Total Kontainer</span>
                        <h3 class="mb-0 mt-1 font-weight-bold text-dark" id="kpi_containers">
                            {{ number_format($kpis['total_containers']) }} <span style="font-size: 1rem; color: #64748b;">Box</span>
                        </h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #ecfdf5;">
                        <i class="fe fe-package" style="font-size: 1.4rem; color: #10b981;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pengapalan -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px; border-left: 4px solid #f59e0b !important;">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Total Pengapalan</span>
                        <h3 class="mb-0 mt-1 font-weight-bold text-dark" id="kpi_shipments">
                            {{ number_format($kpis['total_shipments']) }} <span style="font-size: 1rem; color: #64748b;">SI</span>
                        </h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #fffbeb;">
                        <i class="fe fe-anchor" style="font-size: 1.4rem; color: #f59e0b;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Consignee Aktif -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px; border-left: 4px solid #8b5cf6 !important;">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small font-weight-bold text-uppercase">Consignee Aktif</span>
                        <h3 class="mb-0 mt-1 font-weight-bold text-dark" id="kpi_consignees">
                            {{ number_format($kpis['total_consignees']) }} <span style="font-size: 1rem; color: #64748b;">Buyer</span>
                        </h3>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background-color: #f5f3ff;">
                        <i class="fe fe-users" style="font-size: 1.4rem; color: #8b5cf6;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row mb-4">
    <!-- Trend Bulanan -->
    <div class="col-lg-8 col-12 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold" style="color: #1e293b;">
                    <i class="fe fe-bar-chart-2 text-primary me-2"></i> Tren Volume Ekspor per Bulan (MT & Kontainer)
                </h6>
                <span class="badge bg-light text-muted" id="chartYearBadge">{{ $selectedYear !== 'all' ? 'Tahun ' . $selectedYear : 'Semua Tahun' }}</span>
            </div>
            <div class="card-body">
                <div id="monthlyTrendChart" style="min-height: 320px;"></div>
            </div>
        </div>
    </div>

    <!-- Incoterm Breakdown -->
    <div class="col-lg-4 col-12 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 font-weight-bold" style="color: #1e293b;">
                    <i class="fe fe-pie-chart text-info me-2"></i> Komposisi Incoterm
                </h6>
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <div id="incotermChart" style="width: 100%; min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Top Consignee -->
    <div class="col-lg-6 col-12 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 font-weight-bold" style="color: #1e293b;">
                    <i class="fe fe-award text-warning me-2"></i> Top Consignee by Volume (MT)
                </h6>
            </div>
            <div class="card-body">
                <div id="topConsigneesChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>

    <!-- Negara / Pelabuhan Tujuan -->
    <div class="col-lg-6 col-12 mb-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius: 8px;">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 font-weight-bold" style="color: #1e293b;">
                    <i class="fe fe-map-pin text-danger me-2"></i> Distribusi Negara / Pelabuhan Tujuan
                </h6>
            </div>
            <div class="card-body">
                <div id="countryChart" style="min-height: 300px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Table Section: Detail Shipments -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 8px;">
    <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 font-weight-bold" style="color: #1e293b; font-size: 0.95rem;">
            <i class="fe fe-list text-primary me-2"></i> Rincian Pengapalan Ekspor
        </h6>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-1">
                <span class="small text-muted" style="font-size: 11.5px;">Baris:</span>
                <select id="pageSizeSelect" class="form-select form-select-sm" style="width: 70px; font-size: 12px; padding: 3px 8px;">
                    <option value="10" selected>10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <input type="text" id="tableSearchInput" class="form-control form-control-sm" placeholder="Cari SI / Consignee / Negara..." style="max-width: 230px; font-size: 12px;">
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
            <table class="table table-hover table-striped align-middle mb-0" id="shipmentsTable">
                <thead class="table-light sticky-top" style="z-index: 1;">
                    <tr class="text-uppercase text-muted">
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>No. SI</th>
                        <th>No. Final SI</th>
                        <th>No. B/L</th>
                        <th>Tgl Keberangkatan</th>
                        <th>Consignee</th>
                        <th>Negara / Port</th>
                        <th class="text-center">Incoterm</th>
                        <th class="text-end">Volume (MT)</th>
                        <th class="text-center">Kontainer</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody id="shipmentsTableBody">
                    <!-- Populated dynamically by renderPaginatedTable() -->
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="small text-muted" id="paginationInfo" style="font-size: 11.5px;">
            Menampilkan 0 sampai 0 dari 0 pengapalan
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination pagination-sm mb-0" id="paginationControls">
            </ul>
        </nav>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initial chart data passed from backend
        var monthlyTrendData = @json($monthlyTrend);
        var incotermData = @json($incotermChart);
        var topConsigneesData = @json($topConsigneesChart);
        var countryData = @json($countryChart);

        // 1. Monthly Trend Chart (MT Volume Column + Containers Line)
        var monthlyOptions = {
            series: [{
                name: 'Volume Ekspor (MT)',
                type: 'column',
                data: monthlyTrendData.volume
            }, {
                name: 'Jumlah Kontainer (Box)',
                type: 'line',
                data: monthlyTrendData.containers
            }],
            chart: {
                height: 320,
                type: 'line',
                toolbar: { show: true },
                zoom: { enabled: false }
            },
            stroke: {
                width: [0, 3],
                curve: 'smooth'
            },
            colors: ['#3b82f6', '#10b981'],
            labels: monthlyTrendData.categories,
            xaxis: {
                categories: monthlyTrendData.categories
            },
            yaxis: [{
                title: { text: 'Volume (MT)' },
                labels: {
                    formatter: function (val) { return val ? val.toLocaleString() : 0; }
                }
            }, {
                opposite: true,
                title: { text: 'Kontainer (Box)' },
                labels: {
                    formatter: function (val) { return Math.round(val); }
                }
            }],
            legend: { position: 'top' },
            dataLabels: { enabled: false },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (y, { seriesIndex }) {
                        if (seriesIndex === 0) return (y ? y.toLocaleString() : 0) + ' MT';
                        return (y ? y.toLocaleString() : 0) + ' Box';
                    }
                }
            }
        };
        var monthlyChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), monthlyOptions);
        monthlyChart.render();

        // 2. Incoterm Donut Chart
        var incotermOptions = {
            series: incotermData.series.length > 0 ? incotermData.series : [0],
            labels: incotermData.labels.length > 0 ? incotermData.labels : ['No Data'],
            chart: {
                type: 'donut',
                height: 300
            },
            colors: ['#3b82f6', '#06b6d4', '#f59e0b', '#8b5cf6', '#64748b'],
            legend: { position: 'bottom' },
            dataLabels: {
                enabled: true,
                formatter: function (val) { return Math.round(val) + "%"; }
            },
            tooltip: {
                y: {
                    formatter: function (val) { return val.toLocaleString() + ' MT'; }
                }
            }
        };
        var incotermChartInst = new ApexCharts(document.querySelector("#incotermChart"), incotermOptions);
        incotermChartInst.render();

        // 3. Top Consignees Horizontal Bar Chart
        var consigneeOptions = {
            series: [{
                name: 'Volume (MT)',
                data: topConsigneesData.series
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    dataLabels: { position: 'top' }
                }
            },
            colors: ['#f59e0b'],
            dataLabels: {
                enabled: true,
                offsetX: -6,
                style: { fontSize: '11px', colors: ['#fff'] },
                formatter: function (val) { return val ? val.toLocaleString() : ''; }
            },
            xaxis: {
                categories: topConsigneesData.categories,
                labels: {
                    formatter: function (val) { return val ? val.toLocaleString() : 0; }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) { return val.toLocaleString() + ' MT'; }
                }
            }
        };
        var topConsigneesChartInst = new ApexCharts(document.querySelector("#topConsigneesChart"), consigneeOptions);
        topConsigneesChartInst.render();

        // 4. Country / Port Breakdown Chart
        var countryOptions = {
            series: [{
                name: 'Volume (MT)',
                data: countryData.series
            }],
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '50%'
                }
            },
            colors: ['#ef4444'],
            dataLabels: { enabled: false },
            xaxis: {
                categories: countryData.categories,
                labels: {
                    rotate: -25,
                    trim: true,
                    maxHeight: 70
                }
            },
            yaxis: {
                title: { text: 'Volume (MT)' },
                labels: {
                    formatter: function (val) { return val ? val.toLocaleString() : 0; }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) { return val.toLocaleString() + ' MT'; }
                }
            }
        };
        var countryChartInst = new ApexCharts(document.querySelector("#countryChart"), countryOptions);
        countryChartInst.render();

        // 5. Live Filter via AJAX
        var filterForm = document.getElementById('filterForm');
        var btnRefresh = document.getElementById('btnRefresh');
        var btnExportExcel = document.getElementById('btnExportExcel');

        function fetchAjaxData() {
            var formData = new FormData(filterForm);
            var params = new URLSearchParams(formData).toString();

            // Update Export Excel link
            var exportUrl = "{{ route('cns-dashboard-export-volume.export-excel') }}?" + params;
            if (btnExportExcel) btnExportExcel.href = exportUrl;

            // Fetch AJAX data
            fetch("{{ route('cns-dashboard-export-volume.data') }}?" + params, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                // 1. Update KPIs
                document.getElementById('kpi_volume').innerHTML = (data.kpis.total_volume_mt || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' <span style="font-size: 1rem; color: #64748b;">MT</span>';
                document.getElementById('kpi_containers').innerHTML = (data.kpis.total_containers || 0).toLocaleString() + ' <span style="font-size: 1rem; color: #64748b;">Box</span>';
                document.getElementById('kpi_shipments').innerHTML = (data.kpis.total_shipments || 0).toLocaleString() + ' <span style="font-size: 1rem; color: #64748b;">SI</span>';
                document.getElementById('kpi_consignees').innerHTML = (data.kpis.total_consignees || 0).toLocaleString() + ' <span style="font-size: 1rem; color: #64748b;">Buyer</span>';

                // 2. Update Charts
                var yrVal = document.getElementById('filter_year').value;
                document.getElementById('chartYearBadge').textContent = yrVal !== 'all' ? 'Tahun ' + yrVal : 'Semua Tahun';

                monthlyChart.updateSeries([{
                    name: 'Volume Ekspor (MT)',
                    data: data.monthlyTrend.volume
                }, {
                    name: 'Jumlah Kontainer (Box)',
                    data: data.monthlyTrend.containers
                }]);

                incotermChartInst.updateOptions({
                    series: data.incotermChart.series.length > 0 ? data.incotermChart.series : [0],
                    labels: data.incotermChart.labels.length > 0 ? data.incotermChart.labels : ['No Data']
                });

                topConsigneesChartInst.updateOptions({
                    xaxis: { categories: data.topConsigneesChart.categories }
                });
                topConsigneesChartInst.updateSeries([{
                    name: 'Volume (MT)',
                    data: data.topConsigneesChart.series
                }]);

                countryChartInst.updateOptions({
                    xaxis: { categories: data.countryChart.categories }
                });
                countryChartInst.updateSeries([{
                    name: 'Volume (MT)',
                    data: data.countryChart.series
                }]);

                // 3. Update Table
                currentShipmentsData = data.shipments || [];
                currentPage = 1;
                renderPaginatedTable();
            })
            .catch(function (err) {
                console.error("Gagal memuat data dashboard:", err);
            });
        }

        // 6. Paginated Table Logic
        var currentShipmentsData = @json($shipments);
        var currentPage = 1;
        var pageSize = 10;
        var currentSearchTerm = '';

        function getFilteredShipments() {
            if (!currentSearchTerm) {
                return currentShipmentsData || [];
            }
            return (currentShipmentsData || []).filter(function (s) {
                var searchTarget = [
                    s.si_no,
                    s.final_si_no,
                    s.bl_no,
                    s.consignee_name,
                    s.destination,
                    s.incoterm,
                    s.status
                ].join(' ').toLowerCase();
                return searchTarget.indexOf(currentSearchTerm) !== -1;
            });
        }

        function renderPaginatedTable() {
            var activeList = getFilteredShipments();
            var totalItems = activeList.length;
            var totalPages = Math.ceil(totalItems / pageSize) || 1;

            if (currentPage > totalPages) {
                currentPage = totalPages;
            }
            if (currentPage < 1) {
                currentPage = 1;
            }

            var startIndex = (currentPage - 1) * pageSize;
            var endIndex = Math.min(startIndex + pageSize, totalItems);
            var pageItems = activeList.slice(startIndex, endIndex);

            var tbody = document.getElementById('shipmentsTableBody');
            tbody.innerHTML = '';

            if (totalItems === 0) {
                tbody.innerHTML = '<tr id="emptyTableRow"><td colspan="11" class="text-center py-4 text-muted"><i class="fe fe-inbox fs-3 d-block mb-1"></i>Tidak ada data pengapalan sesuai kriteria.</td></tr>';
                document.getElementById('paginationInfo').textContent = 'Menampilkan 0 sampai 0 dari 0 pengapalan';
                document.getElementById('paginationControls').innerHTML = '';
                return;
            }

            pageItems.forEach(function (s, idx) {
                var st = (s.status || '').toUpperCase();
                var stClass = 'bg-secondary';
                if (st === 'APPROVED' || st === 'COMPLETED') stClass = 'bg-success';
                else if (st === 'FINAL') stClass = 'bg-primary';
                else if (st === 'CREATED' || st === 'DRAFT') stClass = 'bg-warning text-dark';

                var incoClass = (s.incoterm === 'CIF') ? 'bg-info' : 'bg-secondary';

                var rowHtml = '<tr>' +
                    '<td class="text-center text-muted">' + (startIndex + idx + 1) + '</td>' +
                    '<td class="font-weight-bold text-primary">' + (s.si_no || '-') + '</td>' +
                    '<td>' + (s.final_si_no || '-') + '</td>' +
                    '<td>' + (s.bl_no || '-') + '</td>' +
                    '<td>' + (s.export_date_fmt || '-') + '</td>' +
                    '<td class="font-weight-bold text-dark">' + (s.consignee_name || '-') + '</td>' +
                    '<td><span class="badge bg-light text-dark border"><i class="fe fe-map-pin me-1 text-muted"></i>' + (s.destination || '-') + '</span></td>' +
                    '<td class="text-center"><span class="badge ' + incoClass + '">' + (s.incoterm || '-') + '</span></td>' +
                    '<td class="text-end font-weight-bold text-dark">' + (s.volume_mt || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</td>' +
                    '<td class="text-center font-weight-bold text-dark">' + (s.containers || 0) + ' Box</td>' +
                    '<td class="text-center"><span class="badge ' + stClass + '">' + st + '</span></td>' +
                    '</tr>';
                tbody.insertAdjacentHTML('beforeend', rowHtml);
            });

            // Update info
            document.getElementById('paginationInfo').textContent =
                'Menampilkan ' + (startIndex + 1) + ' sampai ' + endIndex + ' dari ' + totalItems + ' pengapalan';

            // Render Pagination Buttons
            renderPaginationButtons(totalPages);
        }

        function renderPaginationButtons(totalPages) {
            var controls = document.getElementById('paginationControls');
            controls.innerHTML = '';

            if (totalPages <= 1) {
                return;
            }

            // Prev button
            var prevLi = document.createElement('li');
            prevLi.className = 'page-item' + (currentPage === 1 ? ' disabled' : '');
            prevLi.innerHTML = '<a class="page-link" href="javascript:void(0);" aria-label="Previous">&laquo;</a>';
            if (currentPage > 1) {
                prevLi.addEventListener('click', function () {
                    currentPage--;
                    renderPaginatedTable();
                });
            }
            controls.appendChild(prevLi);

            // Page numbers
            var maxButtons = 5;
            var startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
            var endPage = Math.min(totalPages, startPage + maxButtons - 1);
            if (endPage - startPage < maxButtons - 1) {
                startPage = Math.max(1, endPage - maxButtons + 1);
            }

            if (startPage > 1) {
                var firstLi = document.createElement('li');
                firstLi.className = 'page-item';
                firstLi.innerHTML = '<a class="page-link" href="javascript:void(0);">1</a>';
                firstLi.addEventListener('click', function () {
                    currentPage = 1;
                    renderPaginatedTable();
                });
                controls.appendChild(firstLi);

                if (startPage > 2) {
                    var dotsLi = document.createElement('li');
                    dotsLi.className = 'page-item disabled';
                    dotsLi.innerHTML = '<span class="page-link">...</span>';
                    controls.appendChild(dotsLi);
                }
            }

            for (var p = startPage; p <= endPage; p++) {
                (function (pageNumber) {
                    var li = document.createElement('li');
                    li.className = 'page-item' + (pageNumber === currentPage ? ' active' : '');
                    li.innerHTML = '<a class="page-link" href="javascript:void(0);">' + pageNumber + '</a>';
                    li.addEventListener('click', function () {
                        currentPage = pageNumber;
                        renderPaginatedTable();
                    });
                    controls.appendChild(li);
                })(p);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    var dotsLi2 = document.createElement('li');
                    dotsLi2.className = 'page-item disabled';
                    dotsLi2.innerHTML = '<span class="page-link">...</span>';
                    controls.appendChild(dotsLi2);
                }
                var lastLi = document.createElement('li');
                lastLi.className = 'page-item';
                lastLi.innerHTML = '<a class="page-link" href="javascript:void(0);">' + totalPages + '</a>';
                lastLi.addEventListener('click', function () {
                    currentPage = totalPages;
                    renderPaginatedTable();
                });
                controls.appendChild(lastLi);
            }

            // Next button
            var nextLi = document.createElement('li');
            nextLi.className = 'page-item' + (currentPage === totalPages ? ' disabled' : '');
            nextLi.innerHTML = '<a class="page-link" href="javascript:void(0);" aria-label="Next">&raquo;</a>';
            if (currentPage < totalPages) {
                nextLi.addEventListener('click', function () {
                    currentPage++;
                    renderPaginatedTable();
                });
            }
            controls.appendChild(nextLi);
        }

        // Initialize table
        renderPaginatedTable();

        // Page size change
        var pageSizeSelect = document.getElementById('pageSizeSelect');
        if (pageSizeSelect) {
            pageSizeSelect.addEventListener('change', function () {
                pageSize = parseInt(this.value, 10) || 10;
                currentPage = 1;
                renderPaginatedTable();
            });
        }

        // Table search input
        var tableSearchInput = document.getElementById('tableSearchInput');
        if (tableSearchInput) {
            tableSearchInput.addEventListener('input', function () {
                currentSearchTerm = this.value.toLowerCase().trim();
                currentPage = 1;
                renderPaginatedTable();
            });
        }

        if (filterForm) {
            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                fetchAjaxData();
            });
        }

        if (btnRefresh) {
            btnRefresh.addEventListener('click', function () {
                fetchAjaxData();
            });
        }

        var btnResetFilter = document.getElementById('btnResetFilter');
        if (btnResetFilter) {
            btnResetFilter.addEventListener('click', function () {
                document.getElementById('filter_year').value = '{{ date("Y") }}';
                document.getElementById('filter_month').value = '{{ date("n") }}';
                document.getElementById('filter_consignee').value = 'all';
                document.getElementById('filter_country').value = 'all';
                document.getElementById('filter_incoterm').value = 'all';

                if (window.jQuery && jQuery.fn.select2) {
                    $('#filterForm select').trigger('change.select2');
                }

                if (tableSearchInput) {
                    tableSearchInput.value = '';
                }
                currentSearchTerm = '';
                currentPage = 1;

                fetchAjaxData();
            });
        }
    });
</script>
@endsection
