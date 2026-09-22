@extends('layout.mainlayout-mitra')
@section('content-wrapper')
@csrf
<h4>Dashboard Compliance Gudang</h4>

{{-- Filters --}}
<div class="row mt-3 mb-2 align-items-end">
    <div class="col-auto">
        <label class="d-block mb-1">Pilih Gudang</label>
        <select id="filter_whid" class="form-control d-inline-block w-auto">
            <option value="">-- Semua Gudang --</option>
        </select>
    </div>
    <div class="col-auto">
        <label for="filter_date" class="d-block mb-1">Pilih Periode</label>
        <input type="month" id="filter_date" class="form-control d-inline-block w-auto" value="{{ date('Y-m') }}">
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" onclick="loadComplianceData()">Filter</button>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row mt-2 mb-3">
    <div class="col-sm-3">
        <div class="card border-primary">
            <div class="card-body">
                <h6 class="card-title text-muted">Total Audit</h6>
                <h3 id="card_total_audit" class="card-text text-primary">-</h3>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card border-danger">
            <div class="card-body">
                <h6 class="card-title text-muted">Total Temuan</h6>
                <h3 id="card_total_violation" class="card-text text-danger">-</h3>
            </div>
        </div>
    </div>
</div>

{{-- Compliance Rate per Warehouse --}}
<!-- <div class="row mt-2 mb-3">
    <div class="col-md">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Compliance Rate per Gudang (%)</h6>
                <canvas id="chart_compliance_rate" height="60"></canvas>
            </div>
        </div>
    </div>
</div> -->

{{-- Violation Type Bar Chart --}}
<div class="row mt-2 mb-3">
    <div class="col-md">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Temuan per Kategori</h6>
                <canvas id="chart_violation_type" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Compliance Rate per Gudang (%)</h6>
                <canvas id="chart_compliance_rate" height="120"></canvas>
            </div>
        </div>
    </div>

    {{-- Per Status Cards --}}
    <div class="col-md">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="card-title">Status Corrective</h6>
                <div class="row mt-3">
                    <div class="col-sm-4 text-center">
                        <div class="card border-secondary">
                            <div class="card-body p-2">
                                <small class="text-muted">Created</small>
                                <h4 id="card_status_created" class="mb-0">-</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 text-center">
                        <div class="card border-warning">
                            <div class="card-body p-2">
                                <small class="text-muted">On Progress</small>
                                <h4 id="card_status_on_progress" class="mb-0 text-warning">-</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 text-center">
                        <div class="card border-success">
                            <div class="card-body p-2">
                                <small class="text-muted">Closed</small>
                                <h4 id="card_status_closed" class="mb-0 text-success">-</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.js" integrity="sha512-D4pL3vNgjkHR/qq+nZywuS6Hg1gwR+UzrdBW6Yg8l26revKyQHMgPq9CLJ2+HHalepS+NuGw1ayCCsGXu9JCXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    let violationChart = null;
    let complianceRateChart = null;

    $(document).ready(function() {
        loadWarehouses();
        loadComplianceData();
    });

    function loadWarehouses() {
        fetch('partner-warehouse-data')
            .then(r => r.json())
            .then(d => {
                const sel = document.getElementById('filter_whid');
                d.data.forEach(w => {
                    const opt = document.createElement('option');
                    opt.value = w.warehouse_task_id;
                    opt.textContent = w.warehouse_task_id + ' — ' + w.warehouse_location;
                    sel.appendChild(opt);
                });
            

                $('#filter_whid').select2({
                    placeholder: '-- Semua Gudang --',
                    allowClear: true,
                    width: '100%'
                });
            });
    }

    function loadComplianceData() {
        const whid = $('#filter_whid').val();
        const date = $('#filter_date').val(); // YYYY-MM, sent as-is

        $.ajax({
            url: `partner-dashboard-compliance-data`,
            method: 'GET',
            data: {
                whid: whid,
                date: date ? date + '-01' : ''
            },
            success: function(response) {
                const d = response.data;

                // Summary cards
                $('#card_total_audit').text(d.total_audit);
                $('#card_total_violation').text(d.total_violation);

                // Status cards
                $('#card_status_created').text(d.per_status.created);
                $('#card_status_on_progress').text(d.per_status.on_progress);
                $('#card_status_closed').text(d.per_status.closed);

                // Bar chart
                renderViolationChart(d.violation_type);
                renderComplianceRateChart(d.violation_per_warehouse);
            },
            error: function(xhr) {
                console.error('Failed to load compliance data:', xhr);
            }
        });
    }

    function renderComplianceRateChart(warehouseData) {
        const ctx = document.getElementById('chart_compliance_rate').getContext('2d');

        if (complianceRateChart) {
            complianceRateChart.destroy();
        }

        const labels = warehouseData.map(w => w.warehouse_id);
        const rates = warehouseData.map(w => w.compliance_rate);
        const colors = rates.map(r => r >= 80 ? '#22c55e' : r >= 50 ? '#f59e0b' : '#ef4444');

        complianceRateChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Compliance Rate (%)',
                    data: rates,
                    backgroundColor: colors,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y}%`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: val => val + '%'
                        }
                    }
                }
            }
        });
    }

    function renderViolationChart(violationType) {
        const ctx = document.getElementById('chart_violation_type').getContext('2d');

        if (violationChart) {
            violationChart.destroy();
        }

        violationChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Minor', 'Major', 'Critical'],
                datasets: [{
                    label: 'Jumlah Temuan',
                    data: [violationType.minor, violationType.major, violationType.critical],
                    backgroundColor: ['#f59e0b', '#ef4444', '#7f1d1d'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                indexAxis: 'x',
                plugins: {
                    legend: {
                        display: false
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }
</script>
@endsection