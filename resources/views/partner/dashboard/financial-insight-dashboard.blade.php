@extends('layout.mainlayout-mitra')
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
        <label class="d-block mb-1">Pilih Periode</label>
        <input type="month" id="filter_date" class="form-control d-inline-block w-auto" value="{{ date('Y-m') }}">
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
    <!-- <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title">Projection vs BEP</h6>
                <canvas id="chart_projection_bep" height="120"></canvas>
            </div>
        </div>
    </div> -->
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title">Projection vs Profit Sharing</h6>
                <canvas id="chart_projection_profit_sharing" height="120"></canvas>
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
            });
    }

    function loadData() {
        const date = document.getElementById('filter_date').value;
        const whid = document.getElementById('filter_warehouse').value;
        const params = new URLSearchParams();
        if (date) params.set('date_start', date + '-01');
        if (whid) params.set('Task', whid);
        const qs = params.toString() ? '?' + params.toString() : '';

        fetch(`partner-dashboard-financial-insight-data${qs}`)
            .then(r => r.json())
            .then(d => {
                console.log(d)
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
        const projection = data.map(r => r.projection);
        const bep = data.map(r => r.bep);

        if (chartBep) chartBep.destroy();
        chartBep = new Chart(document.getElementById('chart_projection_bep'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                        label: 'Projection',
                        data: projection,
                        backgroundColor: 'rgba(54,162,235,0.7)'
                    },
                    {
                        label: 'BEP',
                        data: bep,
                        backgroundColor: 'rgba(255,99,132,0.7)'
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

    function renderProfitSharingChart(data) {
        const labels = data.map(r => r.month);
        const projection = data.map(r => r.projection);
        const profitSharing = data.map(r => r.profit_sharing);

        if (chartProfitSharing) chartProfitSharing.destroy();
        chartProfitSharing = new Chart(document.getElementById('chart_projection_profit_sharing'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                        label: 'Projection',
                        data: projection,
                        backgroundColor: 'rgba(75,192,192,0.7)'
                    },
                    {
                        label: 'Profit Sharing',
                        data: profitSharing,
                        backgroundColor: 'rgba(255,159,64,0.7)'
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