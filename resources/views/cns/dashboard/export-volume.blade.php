@extends('layout.mainlayout-cns')

@section('content-wrapper')
<style>
    /* Tetapkan warna ungu gradient langsung pada topbar */
    .main-header {
        background: linear-gradient(45deg, var(--primary-bg-color, #623AA2), #9853af) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.15) !important;
    }
    .main-header .header-icons,
    .main-header .open-toggle,
    .main-header .close-toggle,
    .main-header .fe-bell,
    .main-header .fe-more-vertical {
        color: #ffffff !important;
    }

    /* Hilangkan tumpahan ungu 190px di bawah topbar */
    .main-content:after {
        display: none !important;
    }
    .main-content.app-content {
        background-color: #f8fafc;
    }

    /* Metric & Card Aesthetic matching reference image */
    .metric-card {
        border-radius: 10px;
        padding: 20px 86px 20px 22px;
        color: #ffffff;
        position: relative;
        height: 100%;
        min-height: 125px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(0, 0, 0, 0.08);
    }
    .metric-card .metric-header {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.8);
    }
    .metric-card .metric-value {
        font-size: 32px;
        font-weight: 700;
        line-height: 1.15;
        color: #ffffff;
        margin-top: 6px;
    }
    .metric-card .metric-value-currency {
        font-size: 24px;
    }
    .metric-card .metric-link {
        font-size: 11.5px;
        color: #ffffff;
        text-decoration: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        margin-top: 8px;
    }
    .metric-card .metric-link:hover {
        color: #ffffff;
        text-decoration: underline;
    }
    .metric-card .metric-icon-box {
        width: 58px;
        height: 58px;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
    }
    .metric-card .metric-icon-box i {
        font-size: 26px;
        color: #ffffff;
    }

    /* Card background colors matching image */
    .bg-card-navy {
        background-color: #3e4f7a !important;
    }
    .bg-card-teal {
        background-color: #11a38f !important;
    }
    .bg-card-orange {
        background-color: #f5a834 !important;
    }
    .bg-card-coral {
        background-color: #ef533c !important;
    }

    /* Table styling */
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

    .top-summary-cards {
        margin-top: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }
</style>

@include('cns.partials.page-header', [
    'title' => 'Dashboard Export Performance',
    'breadcrumbs' => [
        ['label' => 'Dashboard'],
        ['label' => 'Export Performance'],
    ],
])

<!-- 1. Top Row: 4 Solid Color Cards (Style matching reference image) -->
<div class="row g-3 mb-4 top-summary-cards">
    <!-- Card 1: Total Surat Jalan / SI (Navy) -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="metric-card bg-card-navy">
            <div>
                <div class="metric-header">TOTAL SI (PROGRESS)</div>
                <div class="metric-value" id="kpi_shipments">{{ number_format($kpis['total_shipments']) }}</div>
            </div>
            <div>
                <a class="metric-link" onclick="scrollToTable()">Lihat detail</a>
            </div>
            <div class="metric-icon-box">
                <i class="fe fe-file-text"></i>
            </div>
        </div>
    </div>

    <!-- Card 2: Total Volume (Teal) -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="metric-card bg-card-teal">
            <div>
                <div class="metric-header">TOTAL VOLUME (MT)</div>
                <div class="metric-value" id="kpi_volume">{{ number_format($kpis['total_volume_mt'], 2, '.', ',') }}</div>
            </div>
            <div>
                <a class="metric-link" onclick="scrollToTable()">Lihat detail</a>
            </div>
            <div class="metric-icon-box">
                <i class="fe fe-trending-up"></i>
            </div>
        </div>
    </div>

    <!-- Card 3: Total Value USD (Warm Orange) -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="metric-card bg-card-orange">
            <div>
                <div class="metric-header">TOTAL VALUE (USD)</div>
                <div class="metric-value metric-value-currency" id="kpi_value">$ {{ number_format($kpis['total_value_usd'] ?? 0, 2, '.', ',') }}</div>
            </div>
            <div>
                <a class="metric-link" onclick="scrollToTable()">Lihat detail</a>
            </div>
            <div class="metric-icon-box">
                <i class="fe fe-dollar-sign"></i>
            </div>
        </div>
    </div>

    <!-- Card 4: Total Margin USD & % (Coral Red) -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-12">
        <div class="metric-card bg-card-coral">
            <div>
                <div class="metric-header">TOTAL MARGIN (USD &amp; %)</div>
                <div class="metric-value metric-value-currency">
                    <span id="kpi_margin">$ {{ number_format($kpis['total_margin_usd'] ?? 0, 2, '.', ',') }}</span>
                    <span id="kpi_margin_pct" style="font-size: 14px; font-weight: 600; opacity: 0.92;">({{ number_format($kpis['avg_margin_pct'] ?? 0, 1, '.', ',') }}%)</span>
                </div>
            </div>
            <div>
                <a class="metric-link" onclick="scrollToTable()">Lihat detail</a>
            </div>
            <div class="metric-icon-box">
                <i class="fe fe-bar-chart-2"></i>
            </div>
        </div>
    </div>
</div>

<!-- 2. Second Row: Ringkasan & Aktivitas Hari Ini (Horizontal Summary Strip) -->
<div class="card cns-card mb-4">
    <div class="card-header">
        <div class="d-flex align-items-center flex-wrap gap-2">
            <i class="fe fe-trending-up text-dark me-2" style="font-size: 15px;"></i>
            <h6 class="card-title">Ringkasan &amp; Aktivitas Hari Ini</h6>
            <span class="badge rounded-pill bg-success ms-2 px-2 py-1" style="font-size: 11px; font-weight: 500;">
                Last Update: <span id="todayLastUpdate">{{ $todayStats['last_update'] ?? date('H:i:s') }}</span>
            </span>
        </div>
        <div class="d-flex align-items-center flex-wrap gap-2">
            <a id="btnExportExcel" href="{{ route('cns-dashboard-export-volume.export-excel', request()->all()) }}" class="btn btn-success btn-sm">
                <i class="fe fe-download me-1"></i> Export Data (CSV/Excel)
            </a>
            <button type="button" id="btnRefresh" class="btn btn-primary btn-sm ms-1">
                <i class="fe fe-refresh-cw me-1"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body py-4">
        <div class="row text-center align-items-center">
            <!-- Total Kontainer (Box) -->
            <div class="col-lg-2 col-md-4 col-6 border-end mb-3 mb-lg-0">
                <h2 class="fw-bold mb-1" id="kpi_containers" style="color: #f5a834; font-size: 26px;">
                    {{ number_format($kpis['total_containers']) }}
                </h2>
                <div class="text-muted small">Total Kontainer (Box)</div>
            </div>

            <!-- Total Draft / Pending -->
            <div class="col-lg-2 col-md-4 col-6 border-end mb-3 mb-lg-0">
                <h2 class="fw-bold mb-1" id="kpi_pending" style="color: #ef533c; font-size: 26px;">
                    {{ number_format($kpis['total_pending'] ?? 0) }}
                </h2>
                <div class="text-muted small">Total Draft / Pending</div>
            </div>

            <!-- Total Kirim Hari Ini -->
            <div class="col-lg-2 col-md-4 col-6 border-end mb-3 mb-lg-0">
                <h2 class="fw-bold mb-1" id="today_shipments" style="color: #3e4f7a; font-size: 26px;">
                    {{ $todayStats['shipments'] ?? 0 }}
                </h2>
                <div class="text-muted small">Total Kirim Hari Ini</div>
            </div>

            <!-- Kontainer Hari Ini -->
            <div class="col-lg-2 col-md-4 col-6 border-end mb-2 mb-md-0">
                <h2 class="fw-bold mb-1" id="today_containers" style="color: #11a38f; font-size: 26px;">
                    {{ $todayStats['containers'] ?? 0 }}
                </h2>
                <div class="text-muted small">Kontainer Hari Ini</div>
            </div>

            <!-- Total Volume MT Hari Ini -->
            <div class="col-lg-2 col-md-4 col-6 border-end mb-2 mb-md-0">
                <h2 class="fw-bold mb-1" id="today_volume" style="color: #d97706; font-size: 26px;">
                    {{ number_format($todayStats['volume_mt'] ?? 0, 2, '.', ',') }}
                </h2>
                <div class="text-muted small">
                    Volume Hari Ini (MT) <i class="fe fe-info text-muted ms-1" style="font-size: 11px;" title="Volume Netto MT"></i>
                </div>
            </div>

            <!-- Dokumen Siap / Approved -->
            <div class="col-lg-2 col-md-4 col-6">
                <h2 class="fw-bold mb-1" id="today_approved" style="color: #3b82f6; font-size: 26px;">
                    {{ $todayStats['approved'] ?? 0 }}
                </h2>
                <div class="text-muted small">SI Siap / Approved</div>
            </div>
        </div>
    </div>
</div>

<!-- 3. Third Row: Visual Charts in 8:4 Grid -->
<div class="row g-3 mb-4">
    <!-- Left Column (8): Multi-line Trend Chart -->
    <div class="col-lg-8 col-12">
        <div class="card cns-card h-100 mb-0">
            <div class="card-header">
                <div class="d-flex align-items-center">
                    <i class="fe fe-activity text-dark me-2"></i>
                    <h6 class="card-title">Tren Volume &amp; Kinerja Ekspor (MT, Kontainer, Value &amp; Margin)</h6>
                </div>
                <span class="badge bg-light text-muted border" id="chartYearBadge">
                    {{ $selectedYear !== 'all' ? 'Tahun ' . $selectedYear : 'Semua Tahun' }}
                </span>
            </div>
            <div class="card-body">
                <div id="monthlyTrendChart" style="min-height: 380px;"></div>
            </div>
        </div>
    </div>

    <!-- Right Column (4): Two Stacked Donut Charts -->
    <div class="col-lg-4 col-12">
        <div class="d-flex flex-column gap-3 h-100">
            <!-- Top Donut: Status Pickup / Incoterm MTD -->
            <div class="card cns-card flex-fill mb-0">
                <div class="card-header py-2 px-3">
                    <div class="d-flex align-items-center">
                        <i class="fe fe-truck text-dark me-2" style="font-size: 13px;"></i>
                        <h6 class="card-title" style="font-size: 12.5px;">Status Incoterm - Month To Date (MTD)</h6>
                    </div>
                </div>
                <div class="card-body p-2 d-flex flex-column justify-content-center align-items-center">
                    <div id="incotermChart" style="width: 100%; min-height: 180px;"></div>
                </div>
            </div>

            <!-- Bottom Donut: Hasil QC / Status Pengapalan MTD -->
            <div class="card cns-card flex-fill mb-0">
                <div class="card-header py-2 px-3">
                    <div class="d-flex align-items-center">
                        <i class="fe fe-check-circle text-dark me-2" style="font-size: 13px;"></i>
                        <h6 class="card-title" style="font-size: 12.5px;">Status Pengapalan - Month To Date (MTD)</h6>
                    </div>
                </div>
                <div class="card-body p-2 d-flex flex-column justify-content-center align-items-center">
                    <div id="statusDonutChart" style="width: 100%; min-height: 180px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. Filter Section -->
<div class="card cns-card mb-4">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <i class="fe fe-filter text-primary me-2"></i>
            <h6 class="card-title">Filter Parameter Ekspor</h6>
        </div>
    </div>
    <div class="card-body">
        <form id="filterForm" method="GET" action="{{ route('cns-dashboard-export-volume.index') }}">
            <div class="row g-3">
                <!-- Tahun -->
                <div class="col-md-2 col-sm-6">
                    <label for="filter_year" class="form-label font-weight-bold text-muted small">Tahun</label>
                    <select class="text-black form-control form-select select2-basic" id="filter_year" name="year">
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
                    <select class="text-black form-control form-select select2-basic" id="filter_month" name="month">
                        <option value="all" {{ $selectedMonth === 'all' ? 'selected' : '' }}>-- Semua Bulan --</option>
                        @foreach ($months as $num => $name)
                            <option value="{{ $num }}" {{ (string)$selectedMonth === (string)$num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Consignee -->
                <div class="col-md-2 col-sm-6">
                    <label for="filter_consignee" class="form-label font-weight-bold text-muted small">Consignee</label>
                    <select class="text-black form-control form-select select2-basic" id="filter_consignee" name="consignee">
                        <option value="all" {{ $selectedConsignee === 'all' ? 'selected' : '' }}>-- Semua Consignee --</option>
                        @foreach ($availableConsignees as $code => $name)
                            <option value="{{ $code }}" {{ (string)$selectedConsignee === (string)$code ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Country / Port -->
                <div class="col-md-2 col-sm-6">
                    <label for="filter_country" class="form-label font-weight-bold text-muted small">Negara / Pelabuhan Tujuan</label>
                    <select class="text-black form-control form-select select2-basic" id="filter_country" name="country">
                        <option value="all" {{ $selectedCountry === 'all' ? 'selected' : '' }}>-- Semua Negara/POD --</option>
                        @foreach ($availableCountries as $cnt)
                            <option value="{{ $cnt }}" {{ (string)$selectedCountry === (string)$cnt ? 'selected' : '' }}>{{ $cnt }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Incoterm -->
                <div class="col-md-2 col-sm-6">
                    <label for="filter_incoterm" class="form-label font-weight-bold text-muted small">Incoterm</label>
                    <select class="text-black form-control form-select select2-basic" id="filter_incoterm" name="incoterm">
                        <option value="all" {{ $selectedIncoterm === 'all' ? 'selected' : '' }}>-- Semua Incoterm --</option>
                        @foreach ($availableIncoterms as $inco)
                            <option value="{{ $inco }}" {{ (string)$selectedIncoterm === (string)$inco ? 'selected' : '' }}>{{ $inco }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Checklist -->
                <div class="col-md-2 col-sm-6">
                    <label for="filter_status" class="form-label font-weight-bold text-muted small">Status Checklist</label>
                    <select class="text-black form-control form-select select2-basic" id="filter_status" name="status">
                        <option value="all" {{ ($selectedStatus ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                        <option value="completed" {{ ($selectedStatus ?? 'all') === 'completed' ? 'selected' : '' }}>Completed 100%</option>
                        <option value="in_progress" {{ ($selectedStatus ?? 'all') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
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

<!-- 5. Table Section: Detail Shipments -->
<div class="card cns-card mb-4" id="shipmentsTableCard">
    <div class="card-header">
        <h6 class="card-title">
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
            <input type="text" id="tableSearchInput" class="text-black form-control form-control-sm" placeholder="Cari SI / Consignee / Negara..." style="max-width: 230px; font-size: 12px;">
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
                        <th class="text-center">Checklist</th>
                        <th class="text-end">Value (USD)</th>
                        <th class="text-end">Cost (USD)</th>
                        <th class="text-end">Margin (USD &amp; %)</th>
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
    function scrollToTable() {
        var el = document.getElementById('shipmentsTableCard');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Initial chart data from backend
        var monthlyTrendData = @json($monthlyTrend);
        var incotermData = @json($incotermChart);
        var statusData = @json($statusChart);

        // 1. Multi-line Trend Chart (Teal for Containers, Warm Orange for Volume MT, Navy for Value USD, Emerald for Margin USD)
        var monthlyOptions = {
            series: [{
                name: 'Kontainer (Box)',
                data: monthlyTrendData.containers
            }, {
                name: 'Volume Ekspor (MT)',
                data: monthlyTrendData.volume
            }, {
                name: 'Value (USD)',
                data: monthlyTrendData.value_usd || []
            }, {
                name: 'Margin (USD)',
                data: monthlyTrendData.margin_usd || []
            }],
            chart: {
                height: 380,
                type: 'line',
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            stroke: {
                width: [2.5, 2.5, 2.5, 2.5],
                curve: 'smooth'
            },
            markers: {
                size: 5,
                strokeColors: '#ffffff',
                strokeWidth: 2,
                hover: { size: 7 }
            },
            colors: ['#14b8a6', '#f59e0b', '#3e4f7a', '#10b981'],
            xaxis: {
                categories: monthlyTrendData.categories,
                labels: {
                    style: { fontSize: '11px', colors: '#64748b' }
                }
            },
            yaxis: [{
                seriesName: 'Kontainer (Box)',
                title: { text: 'Kontainer (Box)', style: { fontSize: '11px', color: '#14b8a6' } },
                labels: {
                    formatter: function (val) { return Math.round(val); },
                    style: { colors: '#64748b' }
                }
            }, {
                seriesName: 'Volume Ekspor (MT)',
                opposite: true,
                title: { text: 'Volume (MT)', style: { fontSize: '11px', color: '#f59e0b' } },
                labels: {
                    formatter: function (val) { return (val ? val.toLocaleString() : 0); },
                    style: { colors: '#64748b' }
                }
            }, {
                seriesName: 'Value (USD)',
                opposite: true,
                title: { text: 'Value & Margin (USD)', style: { fontSize: '11px', color: '#3e4f7a' } },
                labels: {
                    formatter: function (val) { return '$ ' + (val ? Number(val).toLocaleString() : 0); },
                    style: { colors: '#64748b' }
                }
            }, {
                seriesName: 'Value (USD)',
                show: false
            }],
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 3
            },
            legend: {
                position: 'top',
                horizontalAlign: 'center',
                markers: { radius: 12 }
            },
            dataLabels: { enabled: false },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (y, { seriesIndex }) {
                        if (seriesIndex === 0) return (y ? Math.round(y) : 0) + ' Box';
                        if (seriesIndex === 1) return (y ? y.toLocaleString() : 0) + ' MT';
                        return '$ ' + Number(y || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        };
        var monthlyChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), monthlyOptions);
        monthlyChart.render();

        // 2. Top Donut Chart: Status Incoterm MTD (Gold/Yellow, Green, Red, Blue)
        var incotermColors = ['#f59e0b', '#10b981', '#ef4444', '#3b82f6', '#64748b'];
        var incotermOptions = {
            series: incotermData.series.length > 0 ? incotermData.series : [0],
            labels: incotermData.labels.length > 0 ? incotermData.labels : ['No Data'],
            chart: {
                type: 'donut',
                height: 180
            },
            colors: incotermColors,
            legend: {
                position: 'bottom',
                fontSize: '11px',
                markers: { radius: 12 }
            },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%'
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) { return val.toLocaleString() + ' MT'; }
                }
            }
        };
        var incotermChartInst = new ApexCharts(document.querySelector("#incotermChart"), incotermOptions);
        incotermChartInst.render();

        // 3. Bottom Donut Chart: Status Pengapalan MTD (Deep Forest Green, Coral Red, Amber, Slate)
        var statusColors = ['#0f766e', '#dc2626', '#f59e0b', '#64748b', '#3b82f6'];
        var statusOptions = {
            series: statusData.series.length > 0 ? statusData.series : [0],
            labels: statusData.labels.length > 0 ? statusData.labels : ['No Data'],
            chart: {
                type: 'donut',
                height: 180
            },
            colors: statusColors,
            legend: {
                position: 'bottom',
                fontSize: '11px',
                markers: { radius: 12 }
            },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%'
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) { return val + ' SI'; }
                }
            }
        };
        var statusChartInst = new ApexCharts(document.querySelector("#statusDonutChart"), statusOptions);
        statusChartInst.render();

        // 4. Live Filter via AJAX
        var filterForm = document.getElementById('filterForm');
        var btnRefresh = document.getElementById('btnRefresh');
        var btnExportExcel = document.getElementById('btnExportExcel');

        function escHtml(val) {
            return String(val == null ? '' : val)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function fmtNum(val, decimals) {
            return Number(val || 0).toLocaleString('en-US', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
        }

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
                // 1. Update Top Metric Cards
                document.getElementById('kpi_shipments').textContent = (data.kpis.total_shipments || 0).toLocaleString();
                document.getElementById('kpi_volume').textContent = fmtNum(data.kpis.total_volume_mt || 0, 2);
                document.getElementById('kpi_value').textContent = '$ ' + fmtNum(data.kpis.total_value_usd || 0, 2);
                document.getElementById('kpi_margin').textContent = '$ ' + fmtNum(data.kpis.total_margin_usd || 0, 2);
                document.getElementById('kpi_margin_pct').textContent = '(' + fmtNum(data.kpis.avg_margin_pct || 0, 1) + '%)';
                document.getElementById('kpi_cost').textContent = '$ ' + fmtNum(data.kpis.total_cost_usd || 0, 2);
                document.getElementById('kpi_containers').textContent = (data.kpis.total_containers || 0).toLocaleString();
                document.getElementById('kpi_pending').textContent = (data.kpis.total_pending || 0).toLocaleString();

                // 2. Update Today's Activity Strip
                if (data.todayStats) {
                    document.getElementById('today_shipments').textContent = data.todayStats.shipments || 0;
                    document.getElementById('today_containers').textContent = data.todayStats.containers || 0;
                    document.getElementById('today_volume').textContent = fmtNum(data.todayStats.volume_mt || 0, 2);
                    document.getElementById('today_approved').textContent = data.todayStats.approved || 0;
                    document.getElementById('todayLastUpdate').textContent = data.todayStats.last_update || '';
                }

                // 3. Update Chart Badge & Series
                var yrVal = document.getElementById('filter_year').value;
                document.getElementById('chartYearBadge').textContent = yrVal !== 'all' ? 'Tahun ' + yrVal : 'Semua Tahun';

                monthlyChart.updateSeries([{
                    name: 'Kontainer (Box)',
                    data: data.monthlyTrend.containers
                }, {
                    name: 'Volume Ekspor (MT)',
                    data: data.monthlyTrend.volume
                }, {
                    name: 'Value (USD)',
                    data: data.monthlyTrend.value_usd || []
                }, {
                    name: 'Margin (USD)',
                    data: data.monthlyTrend.margin_usd || []
                }]);

                incotermChartInst.updateOptions({
                    series: data.incotermChart.series.length > 0 ? data.incotermChart.series : [0],
                    labels: data.incotermChart.labels.length > 0 ? data.incotermChart.labels : ['No Data']
                });

                if (data.statusChart) {
                    statusChartInst.updateOptions({
                        series: data.statusChart.series.length > 0 ? data.statusChart.series : [0],
                        labels: data.statusChart.labels.length > 0 ? data.statusChart.labels : ['No Data']
                    });
                }

                // 4. Update Table
                currentShipmentsData = data.shipments || [];
                currentPage = 1;
                renderPaginatedTable();
            })
            .catch(function (err) {
                console.error("Gagal memuat data dashboard:", err);
            });
        }

        // 5. Paginated Table Logic
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
                    s.bc_no,
                    s.contract_no,
                    s.consignee_name,
                    s.destination,
                    s.incoterm,
                    s.status,
                    s.checklist_status
                ].join(' ').toLowerCase();
                return searchTarget.indexOf(currentSearchTerm) !== -1;
            });
        }

        function renderTable() {
            renderPaginatedTable();
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
                tbody.innerHTML = '<tr id="emptyTableRow"><td colspan="15" class="text-center py-4 text-muted"><i class="fe fe-inbox fs-3 d-block mb-1"></i>Tidak ada data pengapalan sesuai kriteria.</td></tr>';
                document.getElementById('paginationInfo').textContent = 'Menampilkan 0 sampai 0 dari 0 pengapalan';
                renderPaginationButtons(1);
                return;
            }

            pageItems.forEach(function (s, idx) {
                var st = (s.status || '').toUpperCase();
                var stClass = 'bg-secondary';
                if (st === 'APPROVED' || st === 'COMPLETED') stClass = 'bg-success';
                else if (st === 'FINAL') stClass = 'bg-primary';
                else if (st === 'CREATED' || st === 'DRAFT') stClass = 'bg-warning text-dark';

                var incoClass = (s.incoterm === 'CIF') ? 'bg-info' : 'bg-secondary';

                var checklistHtml = '<span class="text-muted">-</span>';
                if (s.checklist_status === 'completed') {
                    checklistHtml = '<span class="badge bg-success">Completed (' + escHtml(s.completed_stages) + '/' + escHtml(s.total_stages) + ')</span>';
                } else if (s.checklist_status === 'in_progress') {
                    checklistHtml = '<span class="badge bg-warning text-dark">In Progress (' + escHtml(s.completed_stages) + '/' + escHtml(s.total_stages) + ')</span>';
                }

                var marginClass = Number(s.margin_usd || 0) < 0 ? 'text-danger' : 'text-success';

                var rowHtml = '<tr>' +
                    '<td class="text-center text-muted">' + (startIndex + idx + 1) + '</td>' +
                    '<td class="font-weight-bold text-primary">' + escHtml(s.si_no || '-') + '</td>' +
                    '<td>' + escHtml(s.final_si_no || '-') + '</td>' +
                    '<td>' + escHtml(s.bl_no || '-') + '</td>' +
                    '<td>' + escHtml(s.export_date_fmt || '-') + '</td>' +
                    '<td class="font-weight-bold text-dark">' + escHtml(s.consignee_name || '-') + '</td>' +
                    '<td><span class="badge bg-light text-dark border"><i class="fe fe-map-pin me-1 text-muted"></i>' + escHtml(s.destination || '-') + '</span></td>' +
                    '<td class="text-center"><span class="badge ' + incoClass + '">' + escHtml(s.incoterm || '-') + '</span></td>' +
                    '<td class="text-end font-weight-bold text-dark">' + fmtNum(s.volume_mt || 0, 2) + '</td>' +
                    '<td class="text-center font-weight-bold text-dark">' + escHtml(s.containers || 0) + ' Box</td>' +
                    '<td class="text-center"><span class="badge ' + stClass + '">' + escHtml(st) + '</span></td>' +
                    '<td class="text-center">' + checklistHtml + '</td>' +
                    '<td class="text-end">$ ' + fmtNum(s.value_usd || 0, 2) + '</td>' +
                    '<td class="text-end">$ ' + fmtNum(s.cost_usd || 0, 2) + '</td>' +
                    '<td class="text-end font-weight-bold ' + marginClass + '">$ ' + fmtNum(s.margin_usd || 0, 2) + ' (' + fmtNum(s.margin_pct || 0, 1) + '%)</td>' +
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

        function applyFilters() {
            fetchAjaxData();
        }

        function resetFilters() {
            document.getElementById('filter_year').value = '{{ date("Y") }}';
            document.getElementById('filter_month').value = '{{ date("n") }}';
            document.getElementById('filter_consignee').value = 'all';
            document.getElementById('filter_country').value = 'all';
            document.getElementById('filter_incoterm').value = 'all';
            document.getElementById('filter_status').value = 'all';

            if (window.jQuery && jQuery.fn.select2) {
                $('#filterForm select').trigger('change.select2');
            }

            if (tableSearchInput) {
                tableSearchInput.value = '';
            }
            currentSearchTerm = '';
            currentPage = 1;

            fetchAjaxData();
        }

        // Submit filter
        if (filterForm) {
            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                applyFilters();
            });
        }

        // Refresh button
        if (btnRefresh) {
            btnRefresh.addEventListener('click', function () {
                fetchAjaxData();
            });
        }

        // Reset filter
        var btnResetFilter = document.getElementById('btnResetFilter');
        if (btnResetFilter) {
            btnResetFilter.addEventListener('click', function () {
                resetFilters();
            });
        }
    });
</script>
@endsection
