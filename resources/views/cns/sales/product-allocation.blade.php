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
    #AllocationGrid table.obj tr { height: 42px; }
    #AllocationGrid table.obj td,
    #UpcomingGrid table.obj td {
        vertical-align: middle;
        padding-top: 6px;
        padding-bottom: 6px;
    }
    .alloc-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eef2ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
        border-radius: 6px;
        padding: 2px 7px;
        font-size: 11.5px;
        margin: 2px 4px 2px 0;
    }
    .alloc-badge button {
        border: none;
        background: transparent;
        padding: 0 2px;
        cursor: pointer;
        line-height: 1;
    }
</style>

@include('cns.partials.page-header', [
    'title' => 'Product Allocation & Volume Planning',
    'breadcrumbs' => [
        ['label' => 'Sales & Commercial'],
        ['label' => 'Product Allocation & Volume Planning'],
    ],
])

<ul class="nav nav-tabs mb-3" id="allocationTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link active font-weight-bold" id="tab-allocation-link" data-bs-toggle="tab" data-bs-target="#tab-allocation" data-toggle="tab" href="#tab-allocation" role="tab" aria-controls="tab-allocation" aria-selected="true">
            Contract Allocation & Volume Planning
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link font-weight-bold" id="tab-upcoming-link" data-bs-toggle="tab" data-bs-target="#tab-upcoming" data-toggle="tab" href="#tab-upcoming" role="tab" aria-controls="tab-upcoming" aria-selected="false">
            Upcoming Export Volume
        </a>
    </li>
</ul>

<div class="tab-content">
    {{-- TAB 1: Contract Allocation & Volume Planning --}}
    <div class="tab-pane fade show active" id="tab-allocation" role="tabpanel" aria-labelledby="tab-allocation-link">
        <div class="card cns-card mb-3">
            <div class="card-body py-3">
                <div class="row align-items-end">
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Status Kontrak</label>
                        <select id="filter_status" class="form-control form-control-sm">
                            <option value="all">Semua Status</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ ($filters['status'] ?? 'all') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Buyer</label>
                        <select id="filter_buyer" class="form-control form-control-sm">
                            <option value="all">Semua Buyer</option>
                            @foreach($filter_options['buyers'] as $buyer)
                                <option value="{{ $buyer }}" {{ ($filters['buyer'] ?? 'all') === $buyer ? 'selected' : '' }}>{{ $buyer }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Periode (Bulan)</label>
                        <input type="month" id="filter_period" class="form-control form-control-sm" value="{{ $filters['period'] ?? '' }}">
                    </div>
                    <div class="col-md-2 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Produk</label>
                        <select id="filter_product" class="form-control form-control-sm">
                            <option value="all">Semua Produk</option>
                            @foreach($filter_options['products'] as $product)
                                <option value="{{ $product }}" {{ ($filters['product'] ?? 'all') === $product ? 'selected' : '' }}>{{ $product }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex justify-content-md-end flex-wrap" style="gap: 8px;">
                        <button type="button" id="btn_reset_filter" class="btn btn-sm btn-outline-secondary">Reset</button>
                        <button type="button" id="btn_export_excel" class="btn btn-sm btn-warning">
                            <i class="fe fe-download mr-1 text-white"></i> Export Excel
                        </button>
                        <!-- @can('cns.product_allocation.create')
                        <button type="button" id="btn_add_allocation" class="btn btn-sm btn-primary">
                            <i class="fe fe-plus mr-1"></i> Tambah Plan
                        </button>
                        @endcan -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-navy">
                    <div class="metric-header">Total Contract (MT)</div>
                    <div class="metric-value" id="kpi_contract_mt">{{ number_format($kpis['total_contract_mt'], 2) }}</div>
                    <div class="metric-icon-box"><i class="fe fe-file-text"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-teal">
                    <div class="metric-header">Total Planned (MT)</div>
                    <div class="metric-value" id="kpi_planned_mt">{{ number_format($kpis['total_planned_mt'], 2) }}</div>
                    <div class="metric-icon-box"><i class="fe fe-calendar"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-orange">
                    <div class="metric-header">Total Shipped (MT)</div>
                    <div class="metric-value" id="kpi_shipped_mt">{{ number_format($kpis['total_shipped_mt'], 2) }}</div>
                    <div class="metric-icon-box"><i class="fe fe-anchor"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-coral">
                    <div class="metric-header">Remaining Unfulfilled (MT)</div>
                    <div class="metric-value" id="kpi_remaining_mt">{{ number_format($kpis['total_remaining_mt'], 2) }}</div>
                    <div class="metric-icon-box"><i class="fe fe-alert-circle"></i></div>
                </div>
            </div>
        </div>

        <div class="card cns-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Contract Allocation & Volume Planning</h5>
                <div class="d-flex align-items-center" style="gap: 0.5rem;">
                    @can('cns.product_allocation.create')
                    <button type="button" id="btn_plan_selected" class="btn btn-sm btn-outline-primary">+ Plan Kontrak Terpilih</button>
                    @endcan
                    <button type="button" onclick="reloadAllocation()" class="btn btn-sm btn-outline-secondary">Refresh</button>
                </div>
            </div>
            <div class="card-body">
                <div class="cns-grid-wrapper">
                    <div id="AllocationGrid" class="cns-grid" style="width:100%;max-width:100%;height:380px;background-color:white;overflow:hidden"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: Upcoming Export Volume --}}
    <div class="tab-pane fade" id="tab-upcoming" role="tabpanel" aria-labelledby="tab-upcoming-link">
        <div class="card cns-card mb-3">
            <div class="card-body py-3">
                <div class="row align-items-end">
                    <div class="col-md-4 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Buyer</label>
                        <select id="filter_upcoming_buyer" class="form-control form-control-sm">
                            <option value="all">Semua Buyer</option>
                            @foreach($filter_options['buyers'] as $buyer)
                                <option value="{{ $buyer }}" {{ ($filters['buyer'] ?? 'all') === $buyer ? 'selected' : '' }}>{{ $buyer }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-2 mb-md-0">
                        <label class="small font-weight-bold mb-1">Periode (Bulan ETD)</label>
                        <input type="month" id="filter_upcoming_period" class="form-control form-control-sm" value="{{ $filters['period'] ?? '' }}">
                    </div>
                    <div class="col-md-5 d-flex justify-content-md-end flex-wrap" style="gap: 8px;">
                        <button type="button" id="btn_reset_upcoming_filter" class="btn btn-sm btn-outline-secondary">Reset</button>
                        <button type="button" id="btn_export_upcoming_excel" class="btn btn-sm btn-warning">
                            <i class="fe fe-download mr-1 text-white"></i> Export Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-navy">
                    <div class="metric-header">Upcoming Scheduled (MT)</div>
                    <div class="metric-value" id="kpi_upcoming_mt">{{ number_format($kpis['upcoming_mt'], 2) }}</div>
                    <div class="metric-icon-box"><i class="fe fe-trending-up"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-teal">
                    <div class="metric-header">Upcoming Containers</div>
                    <div class="metric-value" id="kpi_upcoming_containers">{{ number_format($kpis['upcoming_containers']) }}</div>
                    <div class="metric-icon-box"><i class="fe fe-box"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-orange">
                    <div class="metric-header">Scheduled Shipments</div>
                    <div class="metric-value" id="kpi_upcoming_count">{{ number_format($kpis['upcoming_count']) }}</div>
                    <div class="metric-icon-box"><i class="fe fe-navigation"></i></div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="metric-card bg-card-coral">
                    <div class="metric-header">Active Contract Backlog (MT)</div>
                    <div class="metric-value" id="kpi_backlog_mt">{{ number_format($kpis['unscheduled_backlog_mt'], 2) }}</div>
                    <div class="metric-icon-box"><i class="fe fe-layers"></i></div>
                </div>
            </div>
        </div>

        <div class="card cns-card">
            <div class="card-header">
                <h5 class="card-title mb-0">Upcoming Export Volume</h5>
                <button type="button" onclick="reloadUpcoming()" class="btn btn-sm btn-outline-secondary">Refresh</button>
            </div>
            <div class="card-body">
                <div class="cns-grid-wrapper">
                    <div id="UpcomingGrid" class="cns-grid" style="width:100%;max-width:100%;height:380px;background-color:white;overflow:hidden"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Add / Edit Allocation --}}
<div class="modal fade" id="allocationModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="allocationForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allocationModalTitle">Tambah Product Allocation & Volume Plan</h5>
                <button type="button" id="btn_close_modal_x" class="close btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="alloc_id" value="">
                <div class="form-group mb-2">
                    <label class="small font-weight-bold">Kontrak <span class="text-danger">*</span></label>
                    <select id="alloc_contract_id" class="form-control form-control-sm" required>
                        <option value="">-- Pilih Kontrak --</option>
                        @foreach($contract_options as $opt)
                            <option value="{{ $opt['id'] }}">{{ $opt['contract_no'] }} — {{ $opt['buyer_name'] }} ({{ number_format($opt['quantity_mt'], 2) }} MT)</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-2">
                        <label class="small font-weight-bold">Produk / Grade <span class="text-danger">*</span></label>
                        <input type="text" id="alloc_product_name" class="form-control form-control-sm" placeholder="Contoh: UCO, PFAD, POME" required maxlength="100">
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label class="small font-weight-bold">Periode (Bulan) <span class="text-danger">*</span></label>
                        <input type="month" id="alloc_period_month" class="form-control form-control-sm" value="{{ date('Y-m') }}" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group mb-2">
                        <label class="small font-weight-bold">Planned Volume (MT) <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" min="0" id="alloc_planned_mt" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-6 form-group mb-2">
                        <label class="small font-weight-bold">Planned Containers</label>
                        <input type="number" min="0" id="alloc_planned_containers" class="form-control form-control-sm" value="0">
                    </div>
                </div>
                <div class="form-group mb-0">
                    <label class="small font-weight-bold">Catatan</label>
                    <input type="text" id="alloc_notes" class="form-control form-control-sm" maxlength="255">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="btn_cancel_modal" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-sm btn-primary" id="btn_save_allocation">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var csrfToken = '{{ csrf_token() }}';

    function allocationFilters() {
        return {
            status: document.getElementById('filter_status').value,
            buyer: document.getElementById('filter_buyer').value,
            period: document.getElementById('filter_period').value,
            product: document.getElementById('filter_product').value
        };
    }

    function upcomingFilters() {
        return {
            buyer: document.getElementById('filter_upcoming_buyer').value,
            period: document.getElementById('filter_upcoming_period').value
        };
    }

    function filterQuery(params, f) {
        var q = [
            'posStart=' + params.posStart,
            'count=' + params.count
        ];
        Object.keys(f).forEach(function (key) {
            q.push(key + '=' + encodeURIComponent(f[key]));
        });
        if (params.sortCol !== null && params.sortCol !== undefined) {
            q.push('sort_col=' + params.sortCol);
            q.push('sort_dir=' + (params.sortDir || 'asc'));
        }
        return q.join('&');
    }

    function fmtNum(val, dec) {
        return Number(val || 0).toLocaleString('en-US', {
            minimumFractionDigits: dec,
            maximumFractionDigits: dec
        });
    }

    // DHTMLX Grid 1: Contract Allocation & Volume Planning
    window.AllocationGrid = new dhtmlXGridObject('AllocationGrid');
    AllocationGrid.setImagePath('./dhtmlx/imgs/');
    AllocationGrid.setHeader('No,Contract No,Buyer,Status,Loading Period,Contract (MT),Planned (MT),Shipped (MT),Remaining (MT),Fulfillment,Product Allocation & Plan');
    AllocationGrid.attachHeader('#rspan,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter');
    AllocationGrid.setInitWidths('45,140,160,130,155,95,95,95,100,90,*');
    AllocationGrid.setColAlign('center,left,left,left,left,right,right,right,right,center,left');
    AllocationGrid.setColSorting('int,str,str,str,str,int,int,int,int,str,na');
    AllocationGrid.setColTypes('ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro');
    AllocationGrid.enableMultiline(true);
    AllocationGrid.enableAutoHeight(true, 420);
    AllocationGrid.setSkin('dhx_skyblue');
    AllocationGrid.init();

    var AllocationPagination = initCnsGridPagination(AllocationGrid, {
        pageSize: 25,
        pageSizeOptions: [10, 25, 50, 100],
        serverFilter: false,
        getUrl: function (params) {
            return 'xml-cns-product-allocation?' + filterQuery(params, allocationFilters());
        },
        onLoaded: function () {
            AllocationGrid.setSizes();
        }
    });

    // DHTMLX Grid 2: Upcoming Export Volume
    window.UpcomingGrid = new dhtmlXGridObject('UpcomingGrid');
    UpcomingGrid.setImagePath('./dhtmlx/imgs/');
    UpcomingGrid.setHeader('No,Contract No,Buyer,Vessel / Order No,ETD,POD,Incoterm,Volume (MT),Containers,Status');
    UpcomingGrid.attachHeader('#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#select_filter,#text_filter,#text_filter,#select_filter');
    UpcomingGrid.setInitWidths('45,140,170,*,110,140,85,105,90,110');
    UpcomingGrid.setColAlign('center,left,left,left,center,left,center,right,right,center');
    UpcomingGrid.setColSorting('int,str,str,str,str,str,str,int,int,str');
    UpcomingGrid.setColTypes('ron,ro,ro,ro,ro,ro,ro,ro,ron,ro');
    UpcomingGrid.enableAutoHeight(true, 420);
    UpcomingGrid.setSkin('dhx_skyblue');
    UpcomingGrid.init();

    var UpcomingPagination = initCnsGridPagination(UpcomingGrid, {
        pageSize: 25,
        pageSizeOptions: [10, 25, 50, 100],
        serverFilter: false,
        getUrl: function (params) {
            return 'xml-cns-upcoming-export?' + filterQuery(params, upcomingFilters());
        },
        onLoaded: function () {
            UpcomingGrid.setSizes();
        }
    });

    function fetchKpis(filters, callback) {
        var params = new URLSearchParams(filters).toString();
        fetch('{{ route("cns-product-allocation.data") }}?' + params, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function (r) { return r.json(); })
        .then(callback);
    }

    window.reloadAllocation = function () {
        AllocationPagination.reload(1);
        fetchKpis(allocationFilters(), function (data) {
            document.getElementById('kpi_contract_mt').textContent = fmtNum(data.kpis.total_contract_mt, 2);
            document.getElementById('kpi_planned_mt').textContent = fmtNum(data.kpis.total_planned_mt, 2);
            document.getElementById('kpi_shipped_mt').textContent = fmtNum(data.kpis.total_shipped_mt, 2);
            document.getElementById('kpi_remaining_mt').textContent = fmtNum(data.kpis.total_remaining_mt, 2);
        });
    };

    window.reloadUpcoming = function () {
        UpcomingPagination.reload(1);
        fetchKpis(upcomingFilters(), function (data) {
            document.getElementById('kpi_upcoming_mt').textContent = fmtNum(data.kpis.upcoming_mt, 2);
            document.getElementById('kpi_upcoming_containers').textContent = fmtNum(data.kpis.upcoming_containers, 0);
            document.getElementById('kpi_upcoming_count').textContent = fmtNum(data.kpis.upcoming_count, 0);
            document.getElementById('kpi_backlog_mt').textContent = fmtNum(data.kpis.unscheduled_backlog_mt, 2);
        });
    };

    window.refreshAll = function () {
        reloadAllocation();
        reloadUpcoming();
    };

    refreshAll();

    // Tab switching + DHTMLX resize fix (supports BS5, BS4, or direct click)
    document.querySelectorAll('#allocationTabs a.nav-link').forEach(function (tabLink) {
        tabLink.addEventListener('click', function (e) {
            e.preventDefault();
            var targetId = this.getAttribute('href');
            document.querySelectorAll('#allocationTabs a.nav-link').forEach(function (l) {
                l.classList.remove('active');
                l.setAttribute('aria-selected', 'false');
            });
            document.querySelectorAll('.tab-content > .tab-pane').forEach(function (pane) {
                pane.classList.remove('show', 'active');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            var pane = document.querySelector(targetId);
            if (pane) {
                pane.classList.add('show', 'active');
            }
            setTimeout(function () {
                AllocationGrid.setSizes();
                UpcomingGrid.setSizes();
            }, 50);
        });
    });

    window.addEventListener('resize', function () {
        AllocationGrid.setSizes();
        UpcomingGrid.setSizes();
    });

    function showModal() {
        var el = document.getElementById('allocationModal');
        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(el).show();
        } else if (window.jQuery && window.jQuery.fn.modal) {
            window.jQuery(el).modal('show');
        } else {
            el.classList.add('show');
            el.style.display = 'block';
        }
    }

    function hideModal() {
        var el = document.getElementById('allocationModal');
        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(el).hide();
        } else if (window.jQuery && window.jQuery.fn.modal) {
            window.jQuery(el).modal('hide');
        } else {
            el.classList.remove('show');
            el.style.display = 'none';
        }
    }

    document.getElementById('btn_close_modal_x').addEventListener('click', hideModal);
    document.getElementById('btn_cancel_modal').addEventListener('click', hideModal);

    function openModal(alloc, presetContractId) {
        document.getElementById('alloc_id').value = alloc ? alloc.id : '';
        document.getElementById('allocationModalTitle').textContent = alloc
            ? 'Edit Product Allocation & Volume Plan'
            : 'Tambah Product Allocation & Volume Plan';
        document.getElementById('alloc_contract_id').value = alloc ? alloc.contract_id : (presetContractId || '');
        document.getElementById('alloc_product_name').value = alloc ? alloc.product_name : '';
        document.getElementById('alloc_period_month').value = alloc ? alloc.period_month : '{{ date("Y-m") }}';
        document.getElementById('alloc_planned_mt').value = alloc ? alloc.planned_mt : '';
        document.getElementById('alloc_planned_containers').value = alloc ? alloc.planned_containers : 0;
        document.getElementById('alloc_notes').value = alloc ? (alloc.notes || '') : '';
        showModal();
    }

    ['filter_status', 'filter_buyer', 'filter_period', 'filter_product'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', reloadAllocation);
    });

    document.getElementById('btn_reset_filter').addEventListener('click', function () {
        document.getElementById('filter_status').value = 'all';
        document.getElementById('filter_buyer').value = 'all';
        document.getElementById('filter_period').value = '';
        document.getElementById('filter_product').value = 'all';
        reloadAllocation();
    });

    ['filter_upcoming_buyer', 'filter_upcoming_period'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', reloadUpcoming);
    });

    document.getElementById('btn_reset_upcoming_filter').addEventListener('click', function () {
        document.getElementById('filter_upcoming_buyer').value = 'all';
        document.getElementById('filter_upcoming_period').value = '';
        reloadUpcoming();
    });

    document.getElementById('btn_export_excel').addEventListener('click', function () {
        AllocationGrid.toExcel('./export/generate.php');
    });

    document.getElementById('btn_export_upcoming_excel').addEventListener('click', function () {
        UpcomingGrid.toExcel('./export/generate.php');
    });

    var addBtn = document.getElementById('btn_add_allocation');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            openModal(null, AllocationGrid.getSelectedRowId() || '');
        });
    }

    var planSelectedBtn = document.getElementById('btn_plan_selected');
    if (planSelectedBtn) {
        planSelectedBtn.addEventListener('click', function () {
            var selectedId = AllocationGrid.getSelectedRowId();
            if (!selectedId) {
                alert('Pilih baris kontrak pada grid terlebih dahulu.');
                return;
            }
            openModal(null, selectedId);
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-edit-alloc')) {
            var editBtn = e.target.closest('.btn-edit-alloc');
            openModal(JSON.parse(editBtn.getAttribute('data-alloc')), '');
        } else if (e.target.closest('.btn-delete-alloc')) {
            var delBtn = e.target.closest('.btn-delete-alloc');
            if (!confirm('Hapus alokasi produk ini?')) return;
            fetch('{{ url("cns-product-allocation") }}/' + delBtn.getAttribute('data-id') + '/delete', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            }).then(function () { refreshAll(); });
        }
    });

    document.getElementById('allocationForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var id = document.getElementById('alloc_id').value;
        var url = id
            ? '{{ url("cns-product-allocation") }}/' + id + '/update'
            : '{{ route("cns-product-allocation.store") }}';

        var payload = {
            contract_id: document.getElementById('alloc_contract_id').value,
            product_name: document.getElementById('alloc_product_name').value,
            period_month: document.getElementById('alloc_period_month').value,
            planned_mt: document.getElementById('alloc_planned_mt').value,
            planned_containers: document.getElementById('alloc_planned_containers').value || 0,
            notes: document.getElementById('alloc_notes').value
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
        .then(function () {
            hideModal();
            refreshAll();
        })
        .catch(function (err) {
            alert((err && err.message) ? err.message : 'Gagal menyimpan data.');
        });
    });
})();
</script>
@endsection
