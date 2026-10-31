@extends('layout.mainlayout-bp')
@section('content-wrapper')
@csrf
<!-- Main Content Header -->

<h4>Dashboard Kemitraan</h4>
<div class="row mt-3 mb-2 align-items-end">
    <!-- <div class="col-auto">
        <label class="d-block mb-1">Pilih Gudang</label>
        <select id="filter_warehouse" class="form-control d-inline-block w-auto">
            <option value="">-- Semua Gudang --</option>
        </select>
    </div> -->
    <div class="col-auto">
        <div class="row">
            <div class="col-auto">
                <label class="d-block mb-1">Pilih Periode Awal</label>
                <input type="month" id="filter_date_start" class="form-control d-inline-block w-auto" value="{{ date('Y-m') }}">

            </div>
            <div class="col-auto">
                <label class="d-block mb-1">Pilih Periode Akhir</label>
                <input type="month" id="filter_date_end" class="form-control d-inline-block w-auto" value="{{ date('Y-m') }}">
            </div>
        </div>
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" onclick="getDashboardData()">Filter</button>
    </div>
</div>
<div class="row mt-3">
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Down Payment</h5>
                <div class="row">
                    <div class="col-sm text-right">
                        <h4 id="DownPayment" class="card-text"></h4>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Estimated Revenue</h5>
                <div class="row">

                    <div class="col-sm text-right">
                        <h4 id="EstimatedRevenue" class="card-text"></h4>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Brand License</h5>
                <div class="row">
                    <div class="col-sm text-right">
                        <h4 id="BrandLicense" class="card-text"></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Top Product</h5>
                <div class="row">
                    <div class="col-sm text-right">
                        <h4 id="TopProduct" class="card-text"></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-3 mb-3">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Leads Source</h5>
                <div class="row">
                    <div class="col-sm">
                        <canvas id="LeadsSource" width="100" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Sales Funnel</h5>
                <div class="row">
                    <div class="col-sm">
                        <canvas id="SalesFunnel" width="100" height="100"></canvas>
                    </div>
                    <div class="col-sm">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm" id="salesFunnelTable">
                                <thead class="thead">
                                    <tr>
                                        <th>#</th>
                                        <th>Kualifikasi</th>
                                        <th>Number</th>
                                        <th>Rate Ratio</th>
                                        <!-- <th>Progress</th> -->
                                    </tr>
                                </thead>
                                <tbody id="salesFunnelBody">
                                    <tr>
                                        <td colspan="5" class="text-center">Loading...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Sales Performance <small class="text-muted" id="salesPerformanceMonth"></small></h5>
                <div class="table-responsive" style="max-height:230px;overflow:auto;">
                    <table class="table table-bordered table-hover table-sm mb-2">
                        <thead class="thead">
                            <tr>
                                <th>Sales</th>
                                <th>Rank</th>
                                <th>Leads</th>
                                <th>Deal</th>
                            </tr>
                        </thead>
                        <tbody id="salesPerformanceBody">
                            <tr><td colspan="4" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.location='partner-sales-team-performance'">Lihat Sales Performance</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.js" integrity="sha512-D4pL3vNgjkHR/qq+nZywuS6Hg1gwR+UzrdBW6Yg8l26revKyQHMgPq9CLJ2+HHalepS+NuGw1ayCCsGXu9JCXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    let leadsSourceChart = null;
    let salesFunnelChart = null;

    $(document).ready(function() {
        loadWarehouses();
        getDashboardData();
    });

    $('#filter_date_start').on('change', function() {
        $('#filter_date_end').attr('min', $(this).val());
    });

    $('#filter_date_end').on('change', function() {
        $('#filter_date_start').attr('max', $(this).val());
    });

    function loadWarehouses() {
        fetch('partner-warehouse-data')
            .then(r => r.json())
            .then(d => {
                const sel = document.getElementById('filter_warehouse');
                d.data.forEach(w => {
                    const opt = document.createElement('option');
                    opt.value = w.warehouse_task_id;
                    opt.textContent = w.warehouse_task_id + ' — ' + w.warehouse_location;
                    sel.appendChild(opt);
                });
            

                $('#filter_warehouse').select2({
                    placeholder: '-- Semua Gudang --',
                    allowClear: true,
                    width: '100%'
                });
            });
    }

    function getDashboardData() {
        const warehouse = $('#filter_warehouse').val();
        const dateStart = $('#filter_date_start').val();
        const dateEnd = $('#filter_date_end').val();

        $.ajax({
            url: 'partner-dashboard-membership-data',
            method: 'GET',
            data: {
                warehouse: warehouse,
                date_start: dateStart,
                date_end: dateEnd,
            },
            success: function(response) {
                let data = response.data
                initLeadsSourceChart(data.leads_source.labels, data.leads_source.data_arr);
                initSalesFunnelChart(data.sales_funnel.labels, data.sales_funnel.data_arr);
                renderSalesFunnelTable(data.sales_funnel.data);
                renderSalesPerformance(data.sales_performance);
                $("#DownPayment").html(data.est_dp_formatted);
                $("#EstimatedRevenue").html(data.est_revenue_formatted);
                $("#BrandLicense").html(data.brand_license_formatted);
                $("#TopProduct").html(data.top_product);
            },
            error: function(xhr) {
                console.error('Failed to load dashboard data:', xhr);
            }
        });
    }

    function renderSalesFunnelTable(data) {
        const statusColor = {
            'Total Leads': 'dark',
            'Response': 'primary',
            'Qualified': 'info',
            'Meeting 1': 'warning',
            'Meeting 2': 'warning',
            'Warm': 'warning',
            'Deal': 'success',
        };

        const rows = data.map(function(item, index) {
            const badge = statusColor[item.status] || 'secondary';
            const ratio = item.ratio;

            // Progress bar color: red if low, yellow if mid, green if high
            const barColor = ratio >= 75 ? 'success' : ratio >= 40 ? 'warning' : 'danger';

            return `
            <tr>
                <td>${index + 1}</td>
                <td><span>${item.status}</span></td>
                <td>${item.count}</td>
                <td>${item.ratio_label}</td>
            </tr>
        `;
        }).join('');

        $('#salesFunnelBody').html(rows);
    }

    function renderSalesPerformance(data) {
        if (!data) {
            $('#salesPerformanceBody').html('<tr><td colspan="4" class="text-center text-muted">No data</td></tr>');
            return;
        }

        $('#salesPerformanceMonth').text('(' + (data.month || '') + ')');

        const rows = (data.rows || []).map(function(item) {
            const dealCls = item.deal >= 5 ? 'text-success' : (item.deal >= 1 ? 'text-warning' : 'text-muted');
            return '<tr>' +
                '<td>' + item.sales + '</td>' +
                '<td class="text-center">' + item.rank + '</td>' +
                '<td class="text-right">' + item.leads + '</td>' +
                '<td class="text-right ' + dealCls + '">' + item.deal + '</td>' +
                '</tr>';
        }).join('');

        $('#salesPerformanceBody').html(rows || '<tr><td colspan="4" class="text-center text-muted">No data</td></tr>');
    }


    function initLeadsSourceChart(labels, datas) {
        if (leadsSourceChart) leadsSourceChart.destroy();
        const ctx = document.getElementById('LeadsSource').getContext('2d');

        leadsSourceChart = new Chart(ctx, {
            type: 'doughnut', // change to 'pie' for pie chart

            data: {
                labels: labels,
                datasets: [{
                    label: 'My Dataset',
                    data: datas,
                    backgroundColor: [
                        '#ef4444',
                        '#3b82f6',
                        '#f59e0b',
                        '#22c55e'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },

            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = ((value / total) * 100).toFixed(1);
                                return ` ${context.label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                },

                // Doughnut-specific: controls the hole size (0 = full pie, 0.5 = default)
                cutout: '60%' // remove this line if using type: 'pie'
            }
        });
    }

    function initSalesFunnelChart(labels, datas) {
        if (salesFunnelChart) salesFunnelChart.destroy();
        const ctx = document.getElementById('SalesFunnel').getContext('2d');

        salesFunnelChart = new Chart(ctx, {
            type: 'bar', // change to 'pie' for pie chart

            data: {
                labels: labels,
                datasets: [{
                    label: 'My Dataset',
                    data: datas,
                    backgroundColor: [
                        '#ef4444',
                        '#3b82f6',
                        '#f59e0b',
                        '#22c55e'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },

            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.x;

                                return ` ${context.label}: ${value}`;
                            }
                        }
                    }
                },
            }
        });
    }
</script>
@endsection