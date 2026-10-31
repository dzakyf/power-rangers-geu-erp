@extends('layout.mainlayout-bp')
@section('content-wrapper')
@csrf
<h4>Dashboard Financial Insight</h4>

{{-- Filters --}}
<div class="row mt-3 mb-2 align-items-end">
    <div class="col-auto">
        <label class="d-block mb-1">Pilih Gudang</label>
        <select id="filter_warehouse" class="form-control d-inline-block w-auto">
            <option value="">-- Semua Gudang --</option>
        </select>
    </div>
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
        <button type="button" class="btn btn-primary" onclick="loadData()">Filter</button>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mt-2 mb-3">
    <div class="col-sm-4 col-md-2 mb-3">
        <div class="card border-success h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Total Profit Sharing</h6>
                <h4 id="card_total_profit_sharing" class="card-text text-success">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2 mb-3">
        <div class="card border-primary h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Total Revenue</h6>
                <h4 id="card_total_revenue" class="card-text text-primary">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2 mb-3">
        <div class="card border-danger h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Total Operational Cost</h6>
                <h4 id="card_total_operational_cost" class="card-text text-danger">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2 mb-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Total Qty</h6>
                <h4 id="card_total_qty" class="card-text text-info">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2 mb-3">
        <div class="card border-warning h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Total Investasi</h6>
                <h4 id="card_total_investation" class="card-text text-warning">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2 mb-3">
        <div class="card border-secondary h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Total ROI</h6>
                <h4 id="card_total_roi" class="card-text text-secondary">-</h4>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-sm-6 col-md-3 mb-3">
        <div class="card border-dark h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">Margin Profit</h6>
                <h4 id="card_margin_profit" class="card-text text-dark">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3 mb-3">
        <div class="card border-info h-100">
            <div class="card-body">
                <h6 class="card-title text-muted">UCO Transaction</h6>
                <h4 id="card_uco_transaction" class="card-text text-info">-</h4>
            </div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row mb-3">

    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="card-title mb-0">Proyeksi vs Profit Sharing</h6>
                    <div class="d-flex gap-2 align-items-center">

                    </div>
                </div>
                <canvas id="chart_projection_profit_sharing" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title">Proyeksi vs BEP </h6>
                <canvas id="chart_projection_bep" height="120"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let chartBep = null;
    let chartProfitSharing = null;

    function fmt(n) {
        return Number(n).toLocaleString('id-ID');
    }

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

    function loadData() {
        const dateStart = document.getElementById('filter_date_start').value;
        const dateEnd = document.getElementById('filter_date_end').value;
        const whid = document.getElementById('filter_warehouse').value;
        const params = new URLSearchParams();
        if (dateStart) params.set('date_start', dateStart + '-01');
        if (dateEnd) params.set('date_end', dateEnd + '-01');
        if (whid) params.set('Task', whid);
        const qs = params.toString() ? '?' + params.toString() : '';

        fetch(`partner-dashboard-financial-insight-data${qs}`)
            .then(r => r.json())
            .then(d => {
                document.getElementById('card_total_profit_sharing').textContent = 'Rp ' + fmt(d.data.total_profit_sharing);
                document.getElementById('card_total_revenue').textContent = 'Rp ' + fmt(d.data.total_revenue);
                document.getElementById('card_total_operational_cost').textContent = 'Rp ' + fmt(d.data.total_operational_cost);
                document.getElementById('card_total_qty').textContent = fmt(d.data.total_qty) + ' kg';
                document.getElementById('card_total_investation').textContent = 'Rp ' + fmt(d.data.total_investation);
                document.getElementById('card_total_roi').textContent = fmt(d.data.total_roi) + ' %';
                document.getElementById('card_margin_profit').textContent = fmt(d.data.margin_profit) + ' %';
                document.getElementById('card_uco_transaction').textContent = fmt(d.data.uco_transaction) + ' kg';

                renderBepChart(d.data.projection_against_bep);
                renderProfitSharingChart(d.data.projection_against_profit_sharing);
            });
    }

    function renderBepChart(data) {
        const labels = data.map(r => r.month);
        const totalInvestasi = data.map(r => r.total_investation);
        const projectionNetMarginHigh = data.map(r => r.projection_net_margin_high);
        const projectionNetMarginLow = data.map(r => r.projection_net_margin_low);
        const netMargin = data.map(r => r.net_margin);

        if (chartBep) chartBep.destroy();
        chartBep = new Chart(document.getElementById('chart_projection_bep'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                        label: 'Total Investasi',
                        data: totalInvestasi,
                        borderColor: 'rgba(54,162,235,1)',
                        backgroundColor: 'rgba(54,162,235,0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                    },
                    {
                        label: 'Projection Net Margin (High)',
                        data: projectionNetMarginHigh,
                        borderColor: 'rgba(75,192,192,1)',
                        backgroundColor: 'rgba(75,192,192,0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                    },
                    {
                        label: 'Projection Net Margin (Low)',
                        data: projectionNetMarginLow,
                        borderColor: 'rgba(153,102,255,1)',
                        backgroundColor: 'rgba(153,102,255,0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                    },
                    {
                        label: 'Net Margin',
                        data: netMargin,
                        borderColor: 'rgba(255,99,132,1)',
                        backgroundColor: 'rgba(255,99,132,0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                    },
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }

    function renderProfitSharingChart(data) {
        const labels = data.map(r => r.month);
        const projectionHigh = data.map(r => r.projection_net_profit_high);
        const projectionLow = data.map(r => r.projection_net_profit_low);
        const profitSharing = data.map(r => r.profit_sharing);

        if (chartProfitSharing) chartProfitSharing.destroy();
        chartProfitSharing = new Chart(document.getElementById('chart_projection_profit_sharing'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                        label: 'Projection Net Profit (High)',
                        data: projectionHigh,
                        borderColor: 'rgba(75,192,192,1)',
                        backgroundColor: 'rgba(75,192,192,0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                    },
                    {
                        label: 'Projection Net Profit (Low)',
                        data: projectionLow,
                        borderColor: 'rgba(54,162,235,1)',
                        backgroundColor: 'rgba(54,162,235,0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                    },
                    {
                        label: 'Profit Sharing',
                        data: profitSharing,
                        borderColor: 'rgba(255,159,64,1)',
                        backgroundColor: 'rgba(255,159,64,0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }

    loadWarehouses();
    loadData();
</script>
@endsection