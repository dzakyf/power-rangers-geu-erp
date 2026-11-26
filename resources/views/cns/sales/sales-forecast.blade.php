@extends('layout.mainlayout-cns')

@section('content-wrapper')
<style>
    .metric-card {
        border-radius: 10px;
        padding: 18px 20px;
        color: #ffffff;
        position: relative;
        min-height: 110px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }
    .metric-card .metric-header {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.85);
    }
    .metric-card .metric-value {
        font-size: 26px;
        font-weight: 700;
        line-height: 1.15;
        color: #ffffff;
        margin-top: 6px;
    }
    .metric-card .metric-sub {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.9);
        margin-top: 4px;
    }
    .metric-card .metric-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
    }
    .metric-card .metric-icon-box i {
        font-size: 22px;
        color: #ffffff;
    }
    .bg-card-navy { background-color: #3e4f7a !important; }
    .bg-card-teal { background-color: #11a38f !important; }
    .bg-card-orange { background-color: #f5a834 !important; }
    .bg-card-coral { background-color: #ef533c !important; }
    .forecast-progress {
        height: 18px;
        border-radius: 6px;
        background-color: #e9ecef;
        overflow: hidden;
    }
    .forecast-table th,
    .forecast-table td {
        vertical-align: middle !important;
        padding: 8px 12px !important;
    }
    .forecast-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #f8f9fa;
        white-space: nowrap;
    }
    .forecast-actions {
        display: inline-flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: 4px;
    }
    .contract-row {
        cursor: pointer;
    }
    .bc-accordion-row > td {
        background-color: #f8fafc !important;
        padding: 10px 16px !important;
    }
</style>

@include('cns.partials.page-header', [
    'title' => 'Sales Forecast & Pipeline Tracking',
    'breadcrumbs' => [
        ['label' => 'Sales & Commercial'],
        ['label' => 'Sales Forecast & Pipeline Tracking'],
    ],
])

<ul class="nav nav-tabs mb-3" id="salesForecastTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active font-weight-bold" id="tab-sales-forecast-link" data-bs-toggle="tab" data-bs-target="#tab-sales-forecast" data-toggle="tab" href="#tab-sales-forecast" role="tab" aria-controls="tab-sales-forecast" aria-selected="true">
            Sales Forecast
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link font-weight-bold" id="tab-pipeline-tracking-link" data-bs-toggle="tab" data-bs-target="#tab-pipeline-tracking" data-toggle="tab" href="#tab-pipeline-tracking" role="tab" aria-controls="tab-pipeline-tracking" aria-selected="false">
            Pipeline Tracking
        </a>
    </li>
</ul>

<div class="tab-content" id="salesForecastTabsContent">
    {{-- TAB 1: Sales Forecast --}}
    <div class="tab-pane fade show active" id="tab-sales-forecast" role="tabpanel" aria-labelledby="tab-sales-forecast-link">
        <div class="card cns-card mb-3">
            <div class="card-body py-3">
                <div class="row align-items-end">
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Periode (Bulan - Tahun)</label>
                        <input type="month" id="filter_month_year" class="text-black form-control form-control-sm" value="{{ date('Y-m') }}">
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Pencarian</label>
                        <input type="text" id="filter_search" class="text-black form-control form-control-sm" placeholder="Cari bulan / catatan...">
                    </div>
                    <div class="col-md-5 d-flex justify-content-md-end flex-wrap" style="gap: 8px;">
                        <button type="button" id="btn_reset_filter" class="btn btn-sm btn-outline-secondary">Semua Bulan</button>
                        <button type="button" id="btn_refresh" class="btn btn-sm btn-outline-secondary">
                            <i class="fe fe-refresh-cw mr-1"></i> Refresh
                        </button>
                        @can('cns.sales_forecast.create')
                        <button type="button" id="btn_add_forecast" class="btn btn-sm btn-primary">
                            <i class="fe fe-plus mr-1"></i> Set Target Bulanan
                        </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-navy">
                    <div class="metric-header">Target &amp; Realisasi Volume (MT)</div>
                    <div class="metric-value" id="kpi_planned_mt">{{ number_format($kpis['total_planned_mt'], 2) }}</div>
                    <div class="metric-sub">Stuffing: <strong id="kpi_stuffed_mt">{{ number_format($kpis['total_stuffed_mt'], 2) }}</strong> MT</div>
                    <div class="metric-icon-box"><i class="fe fe-package"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-teal">
                    <div class="metric-header">Target &amp; Realisasi Sales Value (USD)</div>
                    <div class="metric-value" id="kpi_planned_value">${{ number_format($kpis['total_planned_value'], 0) }}</div>
                    <div class="metric-sub">Realisasi: <strong id="kpi_stuffed_value">${{ number_format($kpis['total_stuffed_value'], 0) }}</strong> (<span id="kpi_value_pct">{{ number_format($kpis['overall_value_pct'], 1) }}%</span>)</div>
                    <div class="metric-icon-box"><i class="fe fe-dollar-sign"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-orange">
                    <div class="metric-header">Pencapaian Target Volume</div>
                    <div class="metric-value" id="kpi_overall_pct">{{ number_format($kpis['overall_pct'], 1) }}%</div>
                    <div class="metric-sub">Stuffing aktual vs Target MT</div>
                    <div class="metric-icon-box"><i class="fe fe-trending-up"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-coral">
                    <div class="metric-header">Total Booking Confirmation (BC)</div>
                    <div class="metric-value" id="kpi_total_bc">{{ (int) $kpis['total_bc'] }}</div>
                    <div class="metric-sub">Tahun <span id="kpi_year_label">{{ $year }}</span></div>
                    <div class="metric-icon-box"><i class="fe fe-file-text"></i></div>
                </div>
            </div>
        </div>

        <div class="card cns-card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Pencapaian Target Penjualan per Bulan</h5>
                    <small class="text-muted">Klik batang bulan pada diagram untuk memfilter rincian target bulan terkait.</small>
                </div>
            </div>
            <div class="card-body">
                <div id="pipelineProgressChart" style="min-height: 300px;"></div>
            </div>
        </div>

        <div class="card cns-card mb-3" id="forecastTableSection">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Rincian Target &amp; Realisasi Bulanan</h5>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm forecast-table mb-0" id="forecastTable">
                        <thead class="thead-light">
                            <tr>
                                <th>Bulan</th>
                                <th class="text-right">Target Vol (MT)</th>
                                <th class="text-right">Stuffing (MT)</th>
                                <th class="text-right">Target Value (USD)</th>
                                <th class="text-right">Realisasi Value (USD)</th>
                                <th style="min-width: 170px;">Progress (% Achieved)</th>
                                <th class="text-center">Jumlah BC</th>
                                <th>Catatan</th>
                                <th class="text-center" style="width: 1%; white-space: nowrap;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.8rem;" id="forecastTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: Pipeline Tracking --}}
    <div class="tab-pane fade" id="tab-pipeline-tracking" role="tabpanel" aria-labelledby="tab-pipeline-tracking-link">
        <div class="card cns-card mb-3">
            <div class="card-body py-3">
                <div class="row align-items-end">
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Tahun Kontrak</label>
                        @php
                            $yearOptions = range(date('Y') - 2, date('Y') + 2);
                            if (!in_array((int) $year, $yearOptions, true)) {
                                $yearOptions[] = (int) $year;
                                sort($yearOptions);
                            }
                        @endphp
                        <select id="pipeline_year" class="text-black form-control form-control-sm">
                            @foreach($yearOptions as $yOpt)
                                <option value="{{ $yOpt }}" {{ (int) $year === (int) $yOpt ? 'selected' : '' }}>{{ $yOpt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Pencarian</label>
                        <input type="text" id="pipeline_search" class="text-black form-control form-control-sm" placeholder="Cari no. kontrak / buyer / no. BC...">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Status Kontrak</label>
                        <select id="pipeline_status" class="text-black form-control form-control-sm">
                            <option value="">Semua Status</option>
                            @foreach(\App\BuyerContract::STATUSES as $stKey => $stLabel)
                                <option value="{{ $stKey }}">{{ $stLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex justify-content-md-end flex-wrap" style="gap: 8px;">
                        <button type="button" id="btn_reset_pipeline_filter" class="btn btn-sm btn-outline-secondary">Reset</button>
                        <button type="button" id="btn_refresh_pipeline" class="btn btn-sm btn-outline-secondary">
                            <i class="fe fe-refresh-cw mr-1"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card cns-card mb-3" id="pipelineDetailSection">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0">Monitoring Progress Ekspor per Kontrak &amp; Booking Confirmation (BC)</h5>
                    <small class="text-muted" id="pipelineSummaryLabel">Klik baris kontrak untuk melihat bobot dan progress shipment masing-masing BC</small>
                </div>
            </div>
            <div class="card-body p-2">
                <div class="table-responsive" style="max-height: 540px; overflow-y: auto;">
                    <table class="table table-bordered table-hover table-sm forecast-table mb-0" id="pipelineDetailTable">
                        <thead class="thead-light font-weight-bold">
                            <tr>
                                <th style="width: 36px;"></th>
                                <th>No. Kontrak</th>
                                <th>Buyer</th>
                                <th class="text-right">Qty Kontrak (MT)</th>
                                <th class="text-center">Jumlah BC</th>
                                <th style="min-width: 210px;">Total Progress Ekspor (%)</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.8rem;" id="pipelineDetailBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="forecastModal" tabindex="-1" role="dialog" aria-labelledby="forecastModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="forecastForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="forecastModalLabel">Set Target Penjualan Bulanan</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="fc_id" value="">
                    <div class="form-group">
                        <label class="font-weight-bold small">Periode Bulan (YYYY-MM) <span class="text-danger">*</span></label>
                        <input type="month" id="fc_period_month" class="text-black form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Target Volume Penjualan (MT) <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" min="0" id="fc_planned_mt" class="text-black form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Target Sales Value (USD) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" id="fc_planned_value" class="text-black form-control form-control-sm" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Catatan</label>
                        <input type="text" maxlength="255" id="fc_notes" class="text-black form-control form-control-sm" placeholder="Opsional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btn_save_forecast">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var initialData = {!! json_encode(['year' => $year, 'monthly' => $monthly, 'pipeline' => $pipeline, 'contract_pipeline' => $contract_pipeline, 'kpis' => $kpis]) !!};
    var csrfToken = '{{ csrf_token() }}';
    var canEdit = {!! json_encode(auth()->user() && auth()->user()->can('cns.sales_forecast.edit')) !!};
    var canDelete = {!! json_encode(auth()->user() && auth()->user()->can('cns.sales_forecast.delete')) !!};
    var progressChart = null;
    var currentMonthlyRows = initialData.monthly || [];
    var currentContractPipeline = initialData.contract_pipeline || [];
    var loadedYear = String(initialData.year || new Date().getFullYear());
    var expandedContracts = {};

    function getSelectedMonth() {
        return (document.getElementById('filter_month_year').value || '').trim();
    }

    function getSelectedYear() {
        var m = getSelectedMonth();
        return /^\d{4}-\d{2}$/.test(m) ? m.slice(0, 4) : loadedYear;
    }

    function fmtNum(val, dec) {
        return Number(val || 0).toLocaleString('en-US', {
            minimumFractionDigits: dec,
            maximumFractionDigits: dec
        });
    }

    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function progressBarColor(pct) {
        if (pct >= 100) return 'bg-success';
        if (pct >= 60) return 'bg-info';
        return 'bg-warning';
    }

    function renderKpis(kpis, year) {
        document.getElementById('kpi_planned_mt').textContent = fmtNum(kpis.total_planned_mt, 2);
        document.getElementById('kpi_stuffed_mt').textContent = fmtNum(kpis.total_stuffed_mt, 2);
        document.getElementById('kpi_planned_value').textContent = '$' + fmtNum(kpis.total_planned_value, 0);
        document.getElementById('kpi_stuffed_value').textContent = '$' + fmtNum(kpis.total_stuffed_value, 0);
        document.getElementById('kpi_value_pct').textContent = fmtNum(kpis.overall_value_pct, 1) + '%';
        document.getElementById('kpi_overall_pct').textContent = fmtNum(kpis.overall_pct, 1) + '%';
        document.getElementById('kpi_total_bc').textContent = Number(kpis.total_bc || 0);
        document.getElementById('kpi_year_label').textContent = String(year || loadedYear);
    }

    function renderTable(monthly) {
        var tbody = document.getElementById('forecastTableBody');
        var selectedMonth = getSelectedMonth();
        var q = (document.getElementById('filter_search').value || '').trim().toLowerCase();

        var rows = (monthly || []).filter(function (row) {
            if (selectedMonth !== '' && row.period_month !== selectedMonth) {
                return false;
            }
            if (q !== '') {
                var hay = (String(row.month_label || '') + ' ' + String(row.period_month || '') + ' ' + String(row.notes || '')).toLowerCase();
                if (hay.indexOf(q) === -1) return false;
            }
            return true;
        });

        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-3">Tidak ada data untuk filter ini.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(function (row) {
            var pct = Number(row.achieved_pct || 0);
            var barWidth = Math.min(100, Math.max(0, pct));
            var barClass = progressBarColor(pct);

            var actions = '';
            if (canEdit || !row.id) {
                actions += '<button type="button" class="btn btn-sm btn-outline-primary btn-edit-fc" title="Set / Edit Target" '
                    + 'data-id="' + (row.id || '') + '" '
                    + 'data-period="' + escHtml(row.period_month) + '" '
                    + 'data-planned="' + row.planned_mt + '" '
                    + 'data-planned-value="' + (row.planned_value || 0) + '" '
                    + 'data-notes="' + escHtml(row.notes) + '"><i class="fe fe-edit-2"></i></button>';
            }
            if (canDelete && row.id) {
                actions += '<button type="button" class="btn btn-sm btn-outline-danger btn-del-fc" title="Hapus Target" data-id="' + row.id + '"><i class="fe fe-trash-2"></i></button>';
            }

            return '<tr>'
                + '<td><strong>' + escHtml(row.month_label) + '</strong> <span class="text-muted small">(' + escHtml(row.period_month) + ')</span></td>'
                + '<td class="text-right">' + fmtNum(row.planned_mt, 2) + '</td>'
                + '<td class="text-right">' + fmtNum(row.stuffed_mt, 2) + '</td>'
                + '<td class="text-right">$' + fmtNum(row.planned_value, 2) + '</td>'
                + '<td class="text-right">$' + fmtNum(row.stuffed_value, 2) + '</td>'
                + '<td>'
                +   '<div class="d-flex align-items-center" style="gap: 8px;">'
                +     '<div class="progress forecast-progress flex-grow-1">'
                +       '<div class="progress-bar ' + barClass + '" role="progressbar" style="width: ' + barWidth + '%" aria-valuenow="' + pct + '" aria-valuemin="0" aria-valuemax="100"></div>'
                +     '</div>'
                +     '<span class="small font-weight-bold" style="min-width:48px;text-align:right;">' + fmtNum(pct, 1) + '%</span>'
                +   '</div>'
                + '</td>'
                + '<td class="text-center"><span class="badge badge-light border">' + (row.bc_count || 0) + ' BC</span></td>'
                + '<td class="small text-muted">' + escHtml(row.notes || '-') + '</td>'
                + '<td class="text-center">' + (actions ? '<div class="forecast-actions">' + actions + '</div>' : '-') + '</td>'
                + '</tr>';
        }).join('');
    }

    function renderProgressChart(monthly) {
        var categories = monthly.map(function (r) { return r.month_label; });
        var pcts = monthly.map(function (r) { return Number(r.achieved_pct || 0); });
        var selectedMonth = getSelectedMonth();
        var colors = monthly.map(function (r) {
            if (selectedMonth && r.period_month === selectedMonth) {
                return '#3e4f7a';
            }
            return Number(r.achieved_pct || 0) >= 100 ? '#11a38f' : '#f5a834';
        });

        var options = {
            chart: {
                type: 'bar',
                height: 300,
                toolbar: { show: false },
                events: {
                    dataPointSelection: function (event, chartContext, config) {
                        var row = monthly[config.dataPointIndex];
                        if (!row) return;
                        document.getElementById('filter_month_year').value = row.period_month;
                        renderTable(currentMonthlyRows);
                        renderProgressChart(currentMonthlyRows);
                        var tableEl = document.getElementById('forecastTableSection');
                        if (tableEl) {
                            tableEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }
                }
            },
            series: [{ name: 'Pencapaian Target (%)', data: pcts }],
            colors: colors,
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '52%',
                    borderRadius: 4,
                    distributed: true
                }
            },
            legend: { show: false },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return fmtNum(val, 1) + '%';
                }
            },
            xaxis: { categories: categories },
            yaxis: {
                min: 0,
                max: function (max) { return Math.max(100, Math.ceil(max / 10) * 10); },
                title: { text: 'Persentase Pencapaian (%)' },
                labels: {
                    formatter: function (val) { return Math.round(val) + '%'; }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return fmtNum(val, 1) + '%';
                    }
                }
            }
        };

        if (progressChart) {
            progressChart.destroy();
        }
        progressChart = new ApexCharts(document.querySelector('#pipelineProgressChart'), options);
        progressChart.render();
    }

    function renderBcSubtable(bcs) {
        if (!bcs || bcs.length === 0) {
            return '<div class="text-muted small">Belum ada Booking Confirmation pada kontrak ini.</div>';
        }

        var rowsHtml = bcs.map(function (bc) {
            var bcPct = Math.round(Number(bc.progress_pct || 0));
            var barWidth = Math.min(100, Math.max(0, bcPct));
            var barClass = progressBarColor(bcPct);
            var labelText = bc.label
                ? ('<strong>' + escHtml(bc.label) + '</strong> <span class="text-muted">/ ' + escHtml(bc.booking_no || '-') + '</span>')
                : ('<strong>' + escHtml(bc.booking_no || '-') + '</strong>');

            return '<tr>'
                + '<td>' + labelText + '</td>'
                + '<td class="text-right font-weight-bold">' + fmtNum(bc.weight_pct, 1) + '%</td>'
                + '<td>'
                +   '<div class="d-flex align-items-center" style="gap: 8px;">'
                +     '<div class="progress forecast-progress flex-grow-1" style="height: 14px;">'
                +       '<div class="progress-bar ' + barClass + '" role="progressbar" style="width: ' + barWidth + '%" aria-valuenow="' + bcPct + '" aria-valuemin="0" aria-valuemax="100"></div>'
                +     '</div>'
                +     '<span class="small font-weight-bold" style="min-width: 42px; text-align: right;">' + bcPct + '%</span>'
                +   '</div>'
                + '</td>'
                + '</tr>';
        }).join('');

        return '<table class="table table-sm table-bordered mb-0 bg-white">'
            + '<thead class="thead-light">'
            +   '<tr>'
            +     '<th>Label / No. BC</th>'
            +     '<th class="text-right" style="width: 140px;">Bobot (%)</th>'
            +     '<th style="min-width: 220px;">Progress Shipment (%)</th>'
            +   '</tr>'
            + '</thead>'
            + '<tbody>' + rowsHtml + '</tbody>'
            + '</table>';
    }

    function renderPipelineTable() {
        var tbody = document.getElementById('pipelineDetailBody');
        var q = (document.getElementById('pipeline_search').value || '').trim().toLowerCase();
        var statusFilter = (document.getElementById('pipeline_status').value || '').trim();

        var rows = (currentContractPipeline || []).filter(function (r) {
            if (statusFilter !== '' && String(r.status || '') !== statusFilter) {
                return false;
            }
            if (q !== '') {
                var bcText = (r.bcs || []).map(function (b) {
                    return String(b.label || '') + ' ' + String(b.booking_no || '');
                }).join(' ');
                var hay = (String(r.contract_no || '') + ' ' + String(r.buyer_name || '') + ' ' + bcText).toLowerCase();
                if (hay.indexOf(q) === -1) return false;
            }
            return true;
        });

        document.getElementById('pipelineSummaryLabel').textContent =
            'Menampilkan ' + rows.length + ' kontrak pada tahun ' + loadedYear + ' (klik baris kontrak untuk rincian bobot & progress BC)';

        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada kontrak untuk filter ini.</td></tr>';
            return;
        }

        var html = '';
        rows.forEach(function (r) {
            var rowKey = String(r.contract_id);
            var isOpen = !!expandedContracts[rowKey];
            var pct = Math.round(Number(r.progress_pct || 0));
            var barWidth = Math.min(100, Math.max(0, pct));
            var barClass = progressBarColor(pct);
            var statusHtml = r.status_label
                ? '<div class="small text-muted">' + escHtml(r.status_label) + '</div>'
                : '';

            html += '<tr class="contract-row" data-key="' + escHtml(rowKey) + '">'
                + '<td class="text-center">'
                +   '<button type="button" class="btn btn-xs btn-outline-secondary btn-toggle-bc" data-key="' + escHtml(rowKey) + '">'
                +     '<i class="fe ' + (isOpen ? 'fe-chevron-down' : 'fe-chevron-right') + '"></i>'
                +   '</button>'
                + '</td>'
                + '<td><strong>' + escHtml(r.contract_no) + '</strong>' + statusHtml + '</td>'
                + '<td>' + escHtml(r.buyer_name) + '</td>'
                + '<td class="text-right">' + fmtNum(r.contract_qty_mt, 2) + '</td>'
                + '<td class="text-center"><span class="badge badge-primary">' + (r.bc_count || 0) + ' BC</span></td>'
                + '<td>'
                +   '<div class="d-flex align-items-center" style="gap: 8px;">'
                +     '<div class="progress forecast-progress flex-grow-1" style="height: 16px;">'
                +       '<div class="progress-bar ' + barClass + '" role="progressbar" style="width: ' + barWidth + '%" aria-valuenow="' + pct + '" aria-valuemin="0" aria-valuemax="100"></div>'
                +     '</div>'
                +     '<span class="small font-weight-bold" style="min-width: 42px; text-align: right;">' + pct + '%</span>'
                +   '</div>'
                + '</td>'
                + '</tr>';

            if (isOpen) {
                html += '<tr class="bc-accordion-row"><td colspan="6">' + renderBcSubtable(r.bcs) + '</td></tr>';
            }
        });

        tbody.innerHTML = html;
    }

    function renderAll(data) {
        loadedYear = String(data.year || getSelectedYear());
        var pipelineYearEl = document.getElementById('pipeline_year');
        if (pipelineYearEl && pipelineYearEl.value !== loadedYear) {
            pipelineYearEl.value = loadedYear;
        }
        currentMonthlyRows = data.monthly || [];
        currentContractPipeline = data.contract_pipeline || [];
        renderKpis(data.kpis, loadedYear);
        renderTable(currentMonthlyRows);
        renderProgressChart(currentMonthlyRows);
        renderPipelineTable();
    }

    function reloadData(yearOverride) {
        var year = yearOverride || getSelectedYear();
        fetch('{{ route("cns-sales-forecast.data") }}?year=' + encodeURIComponent(year), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(renderAll)
        .catch(function () {
            swal('Error', 'Gagal memuat data forecast.', 'error');
        });
    }

    function showForecastModal() {
        var el = document.getElementById('forecastModal');
        if (window.jQuery && window.jQuery.fn.modal) {
            window.jQuery(el).modal('show');
        } else if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    function hideForecastModal() {
        var el = document.getElementById('forecastModal');
        if (window.jQuery && window.jQuery.fn.modal) {
            window.jQuery(el).modal('hide');
        } else if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(el).hide();
        }
    }

    function openModal(id, period, planned, plannedValue, notes) {
        document.getElementById('fc_id').value = id || '';
        document.getElementById('fc_period_month').value = period || getSelectedMonth() || (getSelectedYear() + '-01');
        document.getElementById('fc_planned_mt').value = planned !== undefined && planned !== '' ? planned : '';
        document.getElementById('fc_planned_value').value = plannedValue !== undefined && plannedValue !== '' ? plannedValue : '';
        document.getElementById('fc_notes').value = notes || '';
        showForecastModal();
    }

    document.querySelectorAll('#salesForecastTabs a.nav-link').forEach(function (tabLink) {
        tabLink.addEventListener('click', function (e) {
            e.preventDefault();
            var targetId = this.getAttribute('href');
            document.querySelectorAll('#salesForecastTabs a.nav-link').forEach(function (l) {
                l.classList.remove('active');
                l.setAttribute('aria-selected', 'false');
            });
            document.querySelectorAll('#salesForecastTabsContent > .tab-pane').forEach(function (pane) {
                pane.classList.remove('show', 'active');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            var pane = document.querySelector(targetId);
            if (pane) {
                pane.classList.add('show', 'active');
            }
            if (targetId === '#tab-sales-forecast') {
                setTimeout(function () {
                    window.dispatchEvent(new Event('resize'));
                }, 50);
            }
        });
    });

    document.getElementById('filter_month_year').addEventListener('change', function () {
        var m = getSelectedMonth();
        var nextYear = /^\d{4}-\d{2}$/.test(m) ? m.slice(0, 4) : loadedYear;
        if (nextYear !== loadedYear) {
            reloadData(nextYear);
        } else {
            renderTable(currentMonthlyRows);
            renderProgressChart(currentMonthlyRows);
        }
    });

    document.getElementById('filter_search').addEventListener('input', function () {
        renderTable(currentMonthlyRows);
    });

    document.getElementById('btn_reset_filter').addEventListener('click', function () {
        document.getElementById('filter_month_year').value = '';
        document.getElementById('filter_search').value = '';
        renderTable(currentMonthlyRows);
        renderProgressChart(currentMonthlyRows);
    });

    document.getElementById('btn_refresh').addEventListener('click', function () {
        reloadData();
    });

    document.getElementById('pipeline_year').addEventListener('change', function () {
        var yr = this.value || loadedYear;
        if (yr !== loadedYear) {
            reloadData(yr);
        }
    });

    document.getElementById('pipeline_search').addEventListener('input', renderPipelineTable);
    document.getElementById('pipeline_status').addEventListener('change', renderPipelineTable);

    document.getElementById('btn_reset_pipeline_filter').addEventListener('click', function () {
        document.getElementById('pipeline_search').value = '';
        document.getElementById('pipeline_status').value = '';
        renderPipelineTable();
    });

    document.getElementById('btn_refresh_pipeline').addEventListener('click', function () {
        reloadData(document.getElementById('pipeline_year').value || loadedYear);
    });

    var addBtn = document.getElementById('btn_add_forecast');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            openModal('', '', '', '', '');
        });
    }

    document.getElementById('pipelineDetailBody').addEventListener('click', function (e) {
        var row = e.target.closest('.contract-row');
        if (!row) return;
        var key = row.getAttribute('data-key');
        if (!key) return;
        expandedContracts[key] = !expandedContracts[key];
        renderPipelineTable();
    });

    document.getElementById('forecastTableBody').addEventListener('click', function (e) {
        var editBtn = e.target.closest('.btn-edit-fc');
        if (editBtn) {
            openModal(
                editBtn.getAttribute('data-id'),
                editBtn.getAttribute('data-period'),
                editBtn.getAttribute('data-planned'),
                editBtn.getAttribute('data-planned-value'),
                editBtn.getAttribute('data-notes')
            );
            return;
        }

        var delBtn = e.target.closest('.btn-del-fc');
        if (delBtn) {
            var id = delBtn.getAttribute('data-id');
            swal({
                title: 'Hapus Target?',
                text: 'Data target bulanan ini akan dihapus.',
                type: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }, function (confirmed) {
                if (!confirmed) return;
                fetch('{{ url("cns-sales-forecast") }}/' + id + '/delete', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    swal('Berhasil', res.message || 'Dihapus.', 'success');
                    reloadData();
                })
                .catch(function () {
                    swal('Error', 'Gagal menghapus target.', 'error');
                });
            });
        }
    });

    document.getElementById('forecastForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var id = document.getElementById('fc_id').value;
        var url = id
            ? '{{ url("cns-sales-forecast") }}/' + id + '/update'
            : '{{ route("cns-sales-forecast.store") }}';

        var payload = {
            period_month: document.getElementById('fc_period_month').value,
            planned_mt: document.getElementById('fc_planned_mt').value,
            planned_value: document.getElementById('fc_planned_value').value,
            notes: document.getElementById('fc_notes').value
        };

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(function (r) {
            if (!r.ok) return r.json().then(function (err) { throw err; });
            return r.json();
        })
        .then(function (res) {
            hideForecastModal();
            swal('Berhasil', res.message || 'Tersimpan.', 'success');
            reloadData();
        })
        .catch(function (err) {
            swal('Error', (err && err.message) ? err.message : 'Gagal menyimpan target.', 'error');
        });
    });

    renderAll(initialData);
})();
</script>
@endsection
