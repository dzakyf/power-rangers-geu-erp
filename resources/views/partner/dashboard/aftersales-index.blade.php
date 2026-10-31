@extends('layout.mainlayout-bp')
@section('content-wrapper')
@csrf
@push('head')
<link rel="stylesheet" type="text/css" href="{{ asset('assets/plugins/data-tables/css/datatables.min.css') }}">
@endpush
<!-- Main Content Header -->
<h4>Dashboard Aftersales Management</h4>

<div class="row mt-3 mb-2 align-items-end">
    <div class="col-auto">
        <label class="d-block mb-1">Pilih Gudang</label>
        <select id="filter_warehouse" class="form-control d-inline-block w-auto">
            <option value="">-- Semua Gudang --</option>
        </select>
    </div>
    <div class="col-auto">
        <label for="start" class="d-block mb-1">Pilih Periode</label>
        <input type="month" id="start" name="start_date" class="form-control" value="{{ date('Y-m') }}">
    </div>
    <div class="col-auto">
        <button type="button" class="btn btn-primary" onclick="getDashboardData()">Filter</button>
    </div>
</div>

{{-- Hub Status --}}
<p class="text-muted small font-weight-bold text-uppercase mt-3 mb-1">Hub Status</p>
<div class="row">
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Operating</h6>
                <h4 id="HubOperating" class="text-success mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Prepared</h6>
                <h4 id="HubPrepared" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Closed</h6>
                <h4 id="HubClosed" class="text-danger mb-0">-</h4>
            </div>
        </div>
    </div>
</div>

{{-- Contract Monitoring --}}
<p class="text-muted small font-weight-bold text-uppercase mt-3 mb-1">Contract Monitoring</p>
<div class="row">
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Active</h6>
                <h4 id="ContractActive" class="text-success mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Renewal Soon</h6>
                <h4 id="ContractRenewal" class="text-warning mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Expired</h6>
                <h4 id="ContractExpired" class="text-danger mb-0">-</h4>
            </div>
        </div>
    </div>
</div>

{{-- Support Performance --}}
<p class="text-muted small font-weight-bold text-uppercase mt-3 mb-1">Support Performance</p>
<div class="row">
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Open Ticket</h6>
                <h4 id="OpenTicket" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Resolved</h6>
                <h4 id="Resolved" class="text-success mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">MTTR (hrs)</h6>
                <h4 id="MTTR" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">MTTF (hrs)</h6>
                <h4 id="MTTF" class="mb-0">-</h4>
            </div>
        </div>
    </div>
</div>

{{-- Support Request by Category --}}
<p class="text-muted small font-weight-bold text-uppercase mt-3 mb-1">Support Request by Category</p>
<div class="row">
    <div class="col-sm">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">ERP</h6>
                <h4 id="SrERP" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Operational</h6>
                <h4 id="SrOperational" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Financial</h6>
                <h4 id="SrFinancial" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Partnership</h6>
                <h4 id="SrPartnership" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Other</h6>
                <h4 id="SrOther" class="mb-0">-</h4>
            </div>
        </div>
    </div>
</div>

{{-- Financial Summary --}}
<p class="text-muted small font-weight-bold text-uppercase mt-3 mb-1">Financial Summary</p>
<div class="row">
    <div class="col-sm">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Total Income</h6>
                <h4 id="TotalIncome" class="text-success mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Total Net Profit</h6>
                <h4 id="TotalNetProfit" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Mitra Royalty</h6>
                <h4 id="MitraRoyalty" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Company Total Profit</h6>
                <h4 id="CompanyTotalProfit" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">Logistik Penjemputan</h6>
                <h4 id="ManagementFee" class="mb-0">-</h4>
            </div>
        </div>
    </div>
</div>

{{-- UCO Volume --}}
<p class="text-muted small font-weight-bold text-uppercase mt-3 mb-1">UCO Volume</p>
<div class="row">
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">UCO Volume</h6>
                <h4 id="UcoVolume" class="mb-0">-</h4>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <div class="card">
            <div class="card-body">
                <h6 class="card-subtitle text-muted mb-1">UCO Volume Sold</h6>
                <h4 id="UcoVolumeSold" class="mb-0">-</h4>
            </div>
        </div>
    </div>
</div>

{{-- Net Profit per Warehouse --}}
<p class="text-muted small font-weight-bold text-uppercase mt-3 mb-1">Net Profit per Warehouse</p>
<div class="row ">
    <div class="col-sm-8">
        <div class="card p-3">
            <div class="card-body p-0">
                <table class="table table-sm table-bordered mb-0" id="netProfitTable">
                    <thead class="thead-light">
                        <tr>
                            <th>Warehouse</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="netProfitBody">
                        <tr>
                            <td colspan="2" class="text-center">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        loadWarehouses();
        initNetProfitTable();
        getDashboardData();

        $('#start').on('change', function() {
            $('#end').attr('min', $(this).val());
        });
        $('#end').on('change', function() {
            $('#start').attr('max', $(this).val());
        });
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

    function formatRp(amount) {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }

    function monthEnd(ym) {
        var d = new Date(ym + '-01T00:00:00');
        d.setMonth(d.getMonth() + 1);
        d.setDate(0);
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    var netProfitTable = null;

    function initNetProfitTable() {
        if (netProfitTable) return netProfitTable;
        netProfitTable = $('#netProfitTable').DataTable({
            data: [],
            columns: [{
                    data: 'warehouse'
                },
                {
                    data: 'amount',
                    className: 'text-right',
                    render: function(val) {
                        var cls = Number(val) < 0 ? 'text-danger' : 'text-success';
                        return '<span class="' + cls + '">' + formatRp(val) + '</span>';
                    }
                }
            ],
            scrollY: '300px',
            scrollCollapse: true,
            paging: true,
            pageLength: 10,
            order: [
                [1, 'desc']
            ],
            language: {
                emptyTable: 'No data',
                zeroRecords: 'No data'
            }
        });
        return netProfitTable;
    }

    function getDashboardData() {
        var ym = $('#start').val();
        $.ajax({
            url: 'partner-dashboard-aftersales-data',
            method: 'GET',
            data: {
                warehouse: $('#filter_warehouse').val(),
                date_start: ym ? ym + '-01' : null,
                date_end: ym ? monthEnd(ym) : null
            },
            success: function(response) {
                let d = response.data;

                // Hub stats
                $('#HubOperating').text(d.hub_stats.operating);
                $('#HubPrepared').text(d.hub_stats.prepared);
                $('#HubClosed').text(d.hub_stats.closed);

                // Contract monitoring
                $('#ContractActive').text(d.contract_monitoring.active);
                $('#ContractRenewal').text(d.contract_monitoring.renewal_soon);
                $('#ContractExpired').text(d.contract_monitoring.expired);

                // Support performance
                $('#OpenTicket').text(d.support_performance.open_ticket);
                $('#Resolved').text(d.support_performance.resolved);
                $('#MTTR').text(d.support_performance.mttr);
                $('#MTTF').text(d.support_performance.mttf);

                // Support request count
                $('#SrERP').text(d.support_request_count.erp);
                $('#SrOperational').text(d.support_request_count.operational);
                $('#SrFinancial').text(d.support_request_count.financial);
                $('#SrPartnership').text(d.support_request_count.partnership);
                $('#SrOther').text(d.support_request_count.other);

                // Financial
                $('#TotalIncome').text(formatRp(d.total_income));
                $('#TotalNetProfit').text(formatRp(d.total_net_profit)).toggleClass('text-danger', d.total_net_profit < 0).toggleClass('text-success', d.total_net_profit >= 0);
                $('#MitraRoyalty').text(formatRp(d.mitra_royalty)).toggleClass('text-danger', d.mitra_royalty < 0).toggleClass('text-success', d.mitra_royalty >= 0);
                $('#CompanyTotalProfit').text(formatRp(d.company_total_profit));
                $('#ManagementFee').text(formatRp(d.management_fee));

                // UCO
                $('#UcoVolume').text(d.uco_volume);
                $('#UcoVolumeSold').text(d.uco_volume_sold);

                // Net profit table (DataTables)
                initNetProfitTable().clear().rows.add(d.net_profit || []).draw();
            },
            error: function(xhr) {
                console.error('Failed to load dashboard data:', xhr);
            }
        });
    }
</script>

@push('scripts')
<script src="{{ asset('assets/plugins/data-tables/js/datatables.min.js') }}"></script>
@endpush
@endsection