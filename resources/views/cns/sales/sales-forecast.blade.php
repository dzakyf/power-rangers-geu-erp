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
        font-size: 28px;
        font-weight: 700;
        line-height: 1.15;
        color: #ffffff;
        margin-top: 6px;
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
</style>

@include('cns.partials.page-header', [
    'title' => 'Sales Forecast & Pipeline Tracking',
    'breadcrumbs' => [
        ['label' => 'Sales & Commercial'],
        ['label' => 'Sales Forecast & Pipeline Tracking'],
    ],
])

<div class="card cns-card mb-3">
    <div class="card-body py-3">
        <div class="row align-items-end">
            <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                <label class="small font-weight-bold mb-1">Periode (Bulan - Tahun)</label>
                <input type="month" id="filter_month_year" class="form-control form-control-sm" value="{{ date('Y-m') }}">
            </div>
            <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                <label class="small font-weight-bold mb-1">Pencarian</label>
                <input type="text" id="filter_search" class="form-control form-control-sm" placeholder="Cari kontrak / buyer / catatan...">
            </div>
            <div class="col-md-5 d-flex justify-content-md-end flex-wrap" style="gap: 8px;">
                <button type="button" id="btn_reset_filter" class="btn btn-sm btn-outline-secondary">Reset</button>
                <button type="button" id="btn_refresh" class="btn btn-sm btn-outline-secondary">
                    <i class="fe fe-refresh-cw mr-1"></i> Refresh
                </button>
                @can('cns.sales_forecast.create')
                <button type="button" id="btn_add_forecast" class="btn btn-sm btn-primary">
                    <i class="fe fe-plus mr-1"></i> Set Plan Bulanan
                </button>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-xl-3 col-md-6 mb-2">
        <div class="metric-card bg-card-navy">
            <div class="metric-header">Total Plan Penjualan (MT)</div>
            <div class="metric-value" id="kpi_planned_mt">{{ number_format($kpis['total_planned_mt'], 2) }}</div>
            <div class="metric-icon-box"><i class="fe fe-target"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-2">
        <div class="metric-card bg-card-teal">
            <div class="metric-header">Total Stuffing Volume (MT)</div>
            <div class="metric-value" id="kpi_stuffed_mt">{{ number_format($kpis['total_stuffed_mt'], 2) }}</div>
            <div class="metric-icon-box"><i class="fe fe-package"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-2">
        <div class="metric-card bg-card-orange">
            <div class="metric-header">Overall % Achieved</div>
            <div class="metric-value" id="kpi_overall_pct">{{ number_format($kpis['overall_pct'], 1) }}%</div>
            <div class="metric-icon-box"><i class="fe fe-trending-up"></i></div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-2">
        <div class="metric-card bg-card-coral">
            <div class="metric-header">Total Booking Confirmation (BC)</div>
            <div class="metric-value" id="kpi_total_bc">{{ (int) $kpis['total_bc'] }}</div>
            <div class="metric-icon-box"><i class="fe fe-file-text"></i></div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-lg-6 mb-3 mb-lg-0">
        <div class="card cns-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Plan Penjualan vs Stuffing Volume per Bulan</h5>
            </div>
            <div class="card-body">
                <div id="forecastChart" style="min-height: 320px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card cns-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Pipeline Tracking: Persentase BC per Buyer per Bulan (Top 8)</h5>
            </div>
            <div class="card-body">
                <div id="pipelineChart" style="min-height: 320px;"></div>
            </div>
        </div>
    </div>
</div>

<div class="card cns-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Rincian Volume Bulanan & Progress (% Achieved)</h5>
    </div>
    <div class="card-body p-2">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm forecast-table mb-0" id="forecastTable">
                <thead class="thead-light">
                    <tr>
                        <th>Bulan</th>
                        <th class="text-right">Plan Volume (MT)</th>
                        <th class="text-right">Stuffing Volume (MT)</th>
                        <th class="text-right">Sisa Target (MT)</th>
                        <th style="min-width: 180px;">Progress (% Achieved)</th>
                        <th class="text-center">Jumlah BC</th>
                        <th>Catatan</th>
                        <th class="text-center" style="width: 1%; white-space: nowrap;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="forecastTableBody">
                    {{-- Rendered via JS --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card cns-card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Rincian Pipeline BC per Kontrak</h5>
    </div>
    <div class="card-body p-2">
        <div class="table-responsive" style="max-height: 360px; overflow-y: auto;">
            <table class="table table-bordered table-hover table-sm forecast-table mb-0" id="pipelineDetailTable">
                <thead class="thead-light">
                    <tr>
                        <th>Bulan</th>
                        <th>No. Kontrak</th>
                        <th>Buyer</th>
                        <th class="text-center">Jumlah BC</th>
                        <th class="text-center">Total BC Bulan</th>
                        <th class="text-right">Persentase (%)</th>
                    </tr>
                </thead>
                <tbody id="pipelineDetailBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="forecastModal" tabindex="-1" role="dialog" aria-labelledby="forecastModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="forecastForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="forecastModalLabel">Set Plan Volume Penjualan Bulanan</h5>
                    <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="fc_id" value="">
                    <div class="form-group">
                        <label class="font-weight-bold small">Periode Bulan (YYYY-MM) <span class="text-danger">*</span></label>
                        <input type="month" id="fc_period_month" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Plan Volume Penjualan (MT) <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" min="0" id="fc_planned_mt" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold small">Catatan</label>
                        <input type="text" maxlength="255" id="fc_notes" class="form-control form-control-sm" placeholder="Opsional">
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
    var initialData = {!! json_encode(['year' => $year, 'monthly' => $monthly, 'pipeline' => $pipeline, 'kpis' => $kpis]) !!};
    var csrfToken = '{{ csrf_token() }}';
    var canEdit = {!! json_encode(auth()->user() && auth()->user()->can('cns.sales_forecast.edit')) !!};
    var canDelete = {!! json_encode(auth()->user() && auth()->user()->can('cns.sales_forecast.delete')) !!};
    var forecastChart = null;
    var pipelineChart = null;
    var currentMonthlyRows = initialData.monthly || [];
    var currentPipelineRows = (initialData.pipeline && initialData.pipeline.breakdown_rows) ? initialData.pipeline.breakdown_rows : [];
    var loadedYear = String(initialData.year || new Date().getFullYear());

    function getSelectedMonth() {
        return (document.getElementById('filter_month_year').value || '').trim();
    }

    function getSelectedYear() {
        var m = getSelectedMonth();
        return /^\d{4}-\d{2}$/.test(m) ? m.slice(0, 4) : String(new Date().getFullYear());
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

    function renderKpis(kpis) {
        document.getElementById('kpi_planned_mt').textContent = fmtNum(kpis.total_planned_mt, 2);
        document.getElementById('kpi_stuffed_mt').textContent = fmtNum(kpis.total_stuffed_mt, 2);
        document.getElementById('kpi_overall_pct').textContent = fmtNum(kpis.overall_pct, 1) + '%';
        document.getElementById('kpi_total_bc').textContent = Number(kpis.total_bc || 0);
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
            tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data untuk filter ini.</td></tr>';
            return;
        }

        var html = '';

        rows.forEach(function (row) {
            var pct = Number(row.achieved_pct || 0);
            var barWidth = Math.min(100, Math.max(0, pct));
            var barClass = pct >= 100 ? 'bg-success' : (pct >= 60 ? 'bg-info' : 'bg-warning');

            var actions = '';
            if (canEdit || !row.id) {
                actions += '<button type="button" class="btn btn-sm btn-outline-primary btn-edit-fc" title="Set / Edit Plan" '
                    + 'data-id="' + (row.id || '') + '" '
                    + 'data-period="' + escHtml(row.period_month) + '" '
                    + 'data-planned="' + row.planned_mt + '" '
                    + 'data-notes="' + escHtml(row.notes) + '"><i class="fe fe-edit-2 mr-1"></i></button>';
            }
            if (canDelete && row.id) {
                actions += '<button type="button" class="btn btn-sm btn-outline-danger btn-del-fc" title="Hapus Plan" data-id="' + row.id + '"><i class="fe fe-trash-2"></i></button>';
            }

            html += '<tr>'
                + '<td><strong>' + escHtml(row.month_label) + '</strong> <span class="text-muted small">(' + escHtml(row.period_month) + ')</span></td>'
                + '<td class="text-right">' + fmtNum(row.planned_mt, 2) + '</td>'
                + '<td class="text-right">' + fmtNum(row.stuffed_mt, 2) + '</td>'
                + '<td class="text-right">' + fmtNum(row.remaining_mt, 2) + '</td>'
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
        });

        tbody.innerHTML = html;
    }

    function renderForecastChart(monthly) {
        var categories = monthly.map(function (r) { return r.month_label; });
        var planned = monthly.map(function (r) { return r.planned_mt; });
        var stuffed = monthly.map(function (r) { return r.stuffed_mt; });
        var pcts = monthly.map(function (r) { return r.achieved_pct; });

        var options = {
            chart: { type: 'bar', height: 320, toolbar: { show: false } },
            series: [
                { name: 'Plan Volume (MT)', data: planned },
                { name: 'Stuffing Volume (MT)', data: stuffed }
            ],
            colors: ['#3e4f7a', '#11a38f'],
            plotOptions: { bar: { horizontal: false, columnWidth: '55%', borderRadius: 4 } },
            dataLabels: { enabled: false },
            xaxis: { categories: categories },
            yaxis: { title: { text: 'Metric Ton (MT)' } },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (val, opts) {
                        if (opts.seriesIndex === 1) {
                            var pct = pcts[opts.dataPointIndex] || 0;
                            return fmtNum(val, 2) + ' MT (' + fmtNum(pct, 1) + '% achieved)';
                        }
                        return fmtNum(val, 2) + ' MT';
                    }
                }
            }
        };

        if (forecastChart) {
            forecastChart.destroy();
        }
        forecastChart = new ApexCharts(document.querySelector('#forecastChart'), options);
        forecastChart.render();
    }

    function renderPipelineChart(pipeline) {
        var container = document.querySelector('#pipelineChart');
        if (!pipeline.series || pipeline.series.length === 0) {
            if (pipelineChart) {
                pipelineChart.destroy();
                pipelineChart = null;
            }
            container.innerHTML = '<div class="text-center text-muted py-5">Belum ada data Booking Confirmation (BC) pada tahun ini.</div>';
            return;
        }
        container.innerHTML = '';

        var series = pipeline.series.map(function (s) {
            return { name: s.name, data: s.data };
        });

        var options = {
            chart: {
                type: 'bar',
                height: 320,
                stacked: true,
                stackType: '100%',
                toolbar: { show: false }
            },
            series: series,
            plotOptions: { bar: { horizontal: false, columnWidth: '55%' } },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val >= 5 ? Math.round(val) + '%' : '';
                }
            },
            xaxis: { categories: pipeline.categories },
            yaxis: { max: 100, title: { text: 'Persentase BC (%)' } },
            tooltip: {
                y: {
                    formatter: function (val, opts) {
                        var s = pipeline.series[opts.seriesIndex];
                        var pct = s && s.pcts ? s.pcts[opts.dataPointIndex] : 0;
                        return val + ' BC (' + fmtNum(pct, 1) + '%)';
                    }
                }
            },
            legend: { position: 'bottom' }
        };

        if (pipelineChart) {
            pipelineChart.destroy();
        }
        pipelineChart = new ApexCharts(container, options);
        pipelineChart.render();
    }

    function renderPipelineTable() {
        var tbody = document.getElementById('pipelineDetailBody');
        var selectedMonth = getSelectedMonth();
        var q = (document.getElementById('filter_search').value || '').trim().toLowerCase();

        var rows = currentPipelineRows.filter(function (r) {
            if (selectedMonth !== '' && String(r.period_month || '') !== selectedMonth) {
                return false;
            }
            if (q !== '') {
                var hay = (String(r.contract_no || '') + ' ' + String(r.buyer_name || '')).toLowerCase();
                if (hay.indexOf(q) === -1) return false;
            }
            return true;
        });

        if (rows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data BC untuk filter ini.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(function (r) {
            return '<tr>'
                + '<td><strong>' + escHtml(r.month_label) + '</strong> <span class="text-muted small">(' + escHtml(r.period_month) + ')</span></td>'
                + '<td>' + escHtml(r.contract_no) + '</td>'
                + '<td>' + escHtml(r.buyer_name) + '</td>'
                + '<td class="text-center"><span class="badge bg-primary text-white border">' + (r.bc_count || 0) + ' BC</span></td>'
                + '<td class="text-center">' + (r.total_month_bc || 0) + '</td>'
                + '<td class="text-right font-weight-bold">' + fmtNum(r.pct, 1) + '%</td>'
                + '</tr>';
        }).join('');
    }

    function renderAll(data) {
        loadedYear = String(data.year || getSelectedYear());
        currentMonthlyRows = data.monthly || [];
        currentPipelineRows = (data.pipeline && data.pipeline.breakdown_rows) ? data.pipeline.breakdown_rows : [];
        renderKpis(data.kpis);
        renderTable(currentMonthlyRows);
        renderForecastChart(currentMonthlyRows);
        renderPipelineChart(data.pipeline);
        renderPipelineTable();
    }

    function reloadData() {
        var year = getSelectedYear();
        fetch('{{ route("cns-sales-forecast.data") }}?year=' + encodeURIComponent(year), {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(renderAll)
        .catch(function () {
            swal('Error', 'Gagal memuat data forecast.', 'error');
        });
    }

    function openModal(id, period, planned, notes) {
        document.getElementById('fc_id').value = id || '';
        document.getElementById('fc_period_month').value = period || getSelectedMonth() || (getSelectedYear() + '-01');
        document.getElementById('fc_planned_mt').value = planned !== undefined && planned !== '' ? planned : '';
        document.getElementById('fc_notes').value = notes || '';
        $('#forecastModal').modal('show');
    }

    document.getElementById('filter_month_year').addEventListener('change', function () {
        if (getSelectedYear() !== loadedYear) {
            reloadData();
        } else {
            renderTable(currentMonthlyRows);
            renderPipelineTable();
        }
    });
    document.getElementById('filter_search').addEventListener('input', function () {
        renderTable(currentMonthlyRows);
        renderPipelineTable();
    });
    document.getElementById('btn_reset_filter').addEventListener('click', function () {
        document.getElementById('filter_month_year').value = '';
        document.getElementById('filter_search').value = '';
        if (getSelectedYear() !== loadedYear) {
            reloadData();
        } else {
            renderTable(currentMonthlyRows);
            renderPipelineTable();
        }
    });
    document.getElementById('btn_refresh').addEventListener('click', reloadData);

    var addBtn = document.getElementById('btn_add_forecast');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            openModal('', '', '', '');
        });
    }

    document.getElementById('forecastTableBody').addEventListener('click', function (e) {
        var editBtn = e.target.closest('.btn-edit-fc');
        if (editBtn) {
            openModal(
                editBtn.getAttribute('data-id'),
                editBtn.getAttribute('data-period'),
                editBtn.getAttribute('data-planned'),
                editBtn.getAttribute('data-notes')
            );
            return;
        }

        var delBtn = e.target.closest('.btn-del-fc');
        if (delBtn) {
            var id = delBtn.getAttribute('data-id');
            swal({
                title: 'Hapus Plan?',
                text: 'Data plan bulanan ini akan dihapus.',
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
                    swal('Error', 'Gagal menghapus plan.', 'error');
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
            $('#forecastModal').modal('hide');
            swal('Berhasil', res.message || 'Tersimpan.', 'success');
            reloadData();
        })
        .catch(function (err) {
            swal('Error', (err && err.message) ? err.message : 'Gagal menyimpan plan.', 'error');
        });
    });

    renderAll(initialData);
})();
</script>
@endsection
