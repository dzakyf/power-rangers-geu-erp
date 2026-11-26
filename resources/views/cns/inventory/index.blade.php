@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
@include('cns.partials.page-header', [
    'title' => 'Inbound Movement',
    'breadcrumbs' => [
        ['label' => 'Inventory UCO'],
        ['label' => 'Inbound Movement'],
    ],
])

<style>
    /* Parameter subheader columns (FFA, MC, IMP, Link QC) */
    #ProjectionGrid .hdr tr:nth-child(2) td,
    #ProjectionGrid .hdr tr:nth-child(2) td:nth-last-child(1),
    #ProjectionGrid .hdr tr:nth-child(2) td:nth-last-child(2),
    #ProjectionGrid .hdr tr:nth-child(2) td:nth-last-child(3),
    #ProjectionGrid .hdr tr:nth-child(2) td:nth-last-child(4) {
        background-color: #FFF2CC !important;
        color: #000000 !important;
        font-weight: bold !important;
        text-align: center !important;
        border: 1px solid #b5b5b5 !important;
        font-size: 12px !important;
    }
    #ProjectionGrid .badge-light {
        font-size: 11px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 3px !important;
        padding: 2px 6px !important;
        background-color: #f8f9fa !important;
        color: #333 !important;
        text-decoration: none !important;
        display: inline-block !important;
    }
    #ProjectionGrid a {
        text-decoration: none !important;
    }
    .card-header-tabs .nav-link {
        border: none !important;
        border-bottom: 3px solid transparent !important;
        color: #6c757d;
        padding: 10px 20px;
        font-size: 13px;
        border-radius: 0;
        background: transparent;
        transition: all 0.2s ease;
    }
    .card-header-tabs .nav-link:hover {
        color: #174375;
        border-bottom: 3px solid #ced4da !important;
    }
    .card-header-tabs .nav-link.active {
        color: #174375 !important;
        background: #ffffff !important;
        border-bottom: 3px solid #174375 !important;
        font-weight: bold;
    }
    .tank-metric-card {
        border-radius: 10px;
        padding: 18px 20px;
        color: #ffffff;
        position: relative;
        min-height: 125px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
        transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .tank-metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 12px rgba(0, 0, 0, 0.08);
    }
    .tank-metric-card .metric-header {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.85);
        display: flex;
        align-items: center;
        gap: 6px;
        padding-right: 64px;
    }
    .tank-metric-card .tank-pct-badge {
        background: rgba(255, 255, 255, 0.22);
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .tank-metric-card .metric-value {
        font-size: 22px;
        font-weight: 700;
        line-height: 1.2;
        color: #ffffff;
        margin-top: 6px;
        padding-right: 64px;
    }
    .tank-metric-card .metric-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        right: 18px;
        top: 18px;
    }
    .tank-metric-card .metric-icon-box i,
    .tank-metric-card .metric-icon-box svg {
        font-size: 22px;
        color: #ffffff;
    }
    .tank-metric-card .tank-progress {
        height: 6px;
        background: rgba(255, 255, 255, 0.25);
        border-radius: 999px;
        overflow: hidden;
        margin: 10px 0 8px;
    }
    .tank-metric-card .tank-progress-bar {
        height: 100%;
        background: #ffffff;
        border-radius: 999px;
    }
    .tank-metric-card .tank-meta {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: rgba(255, 255, 255, 0.9);
        gap: 8px;
    }
    .bg-card-teal { background-color: #11a38f !important; }
    .bg-card-orange { background-color: #f5a834 !important; }
    .bg-card-coral { background-color: #ef533c !important; }
</style>

<!-- Tank Monitoring Summary Cards -->
<div id="tankMonitoringCardSection" class="mb-3" style="display:none;">
    <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
        <span class="font-weight-bold text-secondary" style="font-size: 13px;">
            <i class="fa fa-database text-info mr-1"></i> MONITORING KAPASITAS TANGKI UCO
        </span>
        <a href="cns-master-tank" class="badge badge-light p-1 text-primary" style="font-size: 11px;">
            <i class="fa fa-cog mr-1"></i>Kelola Master Tangki
        </a>
    </div>
    <div class="row g-3" id="tankCardsContainer">
        <!-- Dynamic Tank Cards -->
    </div>
</div>

<!-- Main Card Container -->
<div id="ProjectionSection" class="card cns-card">
    <div class="card-header">
        <h5 class="card-title">Inbound Movement</h5>
        <div id="monitoringHeaderActions" class="d-flex align-items-center flex-wrap" style="gap: 0.5rem;">
            <input type="button" id="CreateUCO" name="CreateUCO" value="Create" class="btn btn-sm btn-primary" onclick="createUCO()" />
            <input type="button" id="DetailQC" name="DetailQC" value="Detail/Update" class="btn btn-sm btn-info" onclick="detailQC()" />
            <input type="button" id="ApproveInbound" name="ApproveInbound" value="Approve" class="btn btn-sm btn-success" onclick="approveSelectedInbound()" />
            <input type="button" id="RejectInbound" name="RejectInbound" value="Reject" class="btn btn-sm btn-danger" onclick="rejectSelectedInbound()" />
            <input type="button" id="LogUCO" name="DetailQC" value="Log" class="btn btn-sm btn-outline-secondary" onclick="swal('Perhatian', 'under development', 'warning');" />
        </div>
        <div id="upcomingHeaderActions" class="d-none align-items-center flex-wrap" style="gap: 0.5rem;">
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnRefreshUpcomingApi" onclick="loadUpcomingGridData(true)">
                <i class="fa fa-refresh mr-1"></i> Refresh Data
            </button>
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs card-header-tabs mb-3 mx-0 border-bottom" id="inboundMovementTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active font-weight-bold" id="tab-monitoring-link" data-bs-toggle="tab" data-bs-target="#tab-monitoring" data-toggle="tab" href="#tab-monitoring" role="tab" aria-controls="tab-monitoring" aria-selected="true" onclick="switchInboundTab('monitoring'); return false;">
                    <i class="fa fa-line-chart text-primary mr-2"></i> Monitoring Stock UCO
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link font-weight-bold" id="tab-upcoming-link" data-bs-toggle="tab" data-bs-target="#tab-upcoming" data-toggle="tab" href="#tab-upcoming" role="tab" aria-controls="tab-upcoming" aria-selected="false" onclick="switchInboundTab('upcoming'); return false;">
                    <i class="fa fa-truck text-success mr-2"></i> Upcoming Inbound
                    <span class="badge badge-primary ml-1" style="font-size: 10px;">API</span>
                </a>
            </li>
        </ul>

        <div class="tab-content" id="inboundMovementTabContent">
            <!-- TAB 1: MONITORING STOCK UCO -->
            <div class="tab-pane fade show active" id="tab-monitoring" role="tabpanel" aria-labelledby="tab-monitoring-link">
                <div class="cns-filter-bar">
                    <div>
                        <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                        <input type="date" id="filterStartDate" class="text-black form-control form-control-sm" style="width:145px;" />
                    </div>
                    <div>
                        <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                        <input type="date" id="filterEndDate" class="text-black form-control form-control-sm" style="width:145px;" />
                    </div>
                    <div style="width:200px;">
                        <label class="d-block mb-1"><small>Warehouse</small></label>
                        <select id="filterWarehouse" class="text-black form-control form-control-sm" style="width:100%;">
                            <option value="">-- Semua --</option>
                        </select>
                    </div>
                    <div style="width:180px;">
                        <label class="d-block mb-1"><small>Nama Tangki</small></label>
                        <select id="filterTank" class="text-black form-control form-control-sm" style="width:100%;">
                            <option value="">-- Semua --</option>
                        </select>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                        <button type="button" class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
                    </div>
                </div>

                <div class="cns-grid-wrapper">
                    <div id="ProjectionGrid" class="cns-grid" style="width:100%;height:450px; background-color:white;overflow:hidden"></div>
                </div>
                @include('cns.inventory.create')
                @include('cns.inventory.edit')
            </div>

            <!-- TAB 2: UPCOMING INBOUND -->
            <div class="tab-pane fade" id="tab-upcoming" role="tabpanel" aria-labelledby="tab-upcoming-link">
                <div class="cns-filter-bar">
                    <div>
                        <label class="d-block mb-1"><small>Jenis Tanggal</small></label>
                        <select id="filterUpcomingDateType" class="text-black form-control form-control-sm" style="width:130px;">
                            <option value="eta" selected>Tgl ETA</option>
                            <option value="loading">Tgl Loading</option>
                        </select>
                    </div>
                    <div>
                        <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                        <input type="date" id="filterUpcomingStartDate" class="text-black form-control form-control-sm" style="width:145px;" />
                    </div>
                    <div>
                        <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                        <input type="date" id="filterUpcomingEndDate" class="text-black form-control form-control-sm" style="width:145px;" />
                    </div>
                    <div style="width:240px;">
                        <label class="d-block mb-1"><small>Pencarian</small></label>
                        <input type="text" id="filterUpcomingSearch" class="text-black form-control form-control-sm" placeholder="Kode / Asal / Tujuan / Status" onkeyup="if(event.keyCode===13) applyUpcomingFilters()" />
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="applyUpcomingFilters()">Filter</button>
                        <button type="button" class="btn btn-sm btn-secondary ml-1" onclick="resetUpcomingFilters()">Reset</button>
                    </div>
                </div>

                <div class="cns-grid-wrapper">
                    <div id="UpcomingGrid" class="cns-grid" style="width:100% !important; min-height:450px; height:450px; background-color:white; overflow:hidden;"></div>
                </div>
                <div id="upcomingEmptyNotice" class="alert alert-info mt-2 mb-0" style="display:none;"></div>
                <div id="upcomingErrorNotice" class="alert alert-danger mt-2 mb-0" style="display:none;"></div>

                <div id="upcomingPaginationBar" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                    <div class="d-flex align-items-center">
                        <span id="upcomingPaginationInfo" class="text-muted small mr-3">Menampilkan 0 data</span>
                        <div class="d-flex align-items-center">
                            <label for="upcomingPerPage" class="text-muted small mb-0 mr-2">Per halaman:</label>
                            <select id="upcomingPerPage" class="text-black form-control form-control-sm" style="width:auto;" onchange="changeUpcomingPerPage(this.value)">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <nav aria-label="Upcoming Inbound Pagination">
                        <ul id="upcomingPaginationControls" class="pagination pagination-sm mb-0"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var inventoryId = null;
    $(document).ready(function() {
        initDefaultDateRange();
        loadFilterOptions();
        loadTankMonitoringCards();
        initProjectionGrid();
    })

    function initDefaultDateRange() {
        var now = new Date();
        var yyyy = now.getFullYear();
        var mm = String(now.getMonth() + 1).padStart(2, '0');
        var dd = String(now.getDate()).padStart(2, '0');
        $('#filterStartDate').val(yyyy + '-' + mm + '-01');
        $('#filterEndDate').val(yyyy + '-' + mm + '-' + dd);
    }

    function loadFilterOptions() {
        var $wh = $('#filterWarehouse');
        var $tank = $('#filterTank');

        $wh.select2({
            placeholder: '-- Semua --',
            allowClear: true,
            width: '100%'
        });
        $tank.select2({
            placeholder: '-- Semua --',
            allowClear: true,
            width: '100%'
        });

        $.get('cns-inventory-tracking-filter-options', function(res) {
            if (res.warehouses && res.warehouses.length) {
                $.each(res.warehouses, function(_, item) {
                    $wh.append($('<option>', { value: item, text: item }));
                });
            }
            if (res.tanks && res.tanks.length) {
                $.each(res.tanks, function(_, item) {
                    $tank.append($('<option>', { value: item, text: item }));
                });
            }
        });
    }

    function createUCO() {
        $('#FormEditStock').hide();
        $('#FormQualityControl').show();
        if (typeof loadInboundMovementOptions === 'function') {
            loadInboundMovementOptions();
        }
    }

    function detailQC() {
        $('#FormQualityControl').hide();
        if (!inventoryId) {
            swal('Perhatian', 'Pilih data terlebih dahulu', 'warning');
            return;
        }
        if (inventoryId === 'row_opening') {
            swal('Perhatian', 'Opening stock tidak dapat diedit', 'warning');
            return;
        }
        prefillEditQC(inventoryId);
    }

    function resetFilters() {
        initDefaultDateRange();
        $('#filterWarehouse').val('').trigger('change');
        $('#filterTank').val('').trigger('change');
        applyFilters();
    }

    var ProjectionPagination;

    function applyFilters() {
        if (ProjectionPagination) {
            ProjectionPagination.reload(true);
        } else if (ProjectionGrid) {
            var startDate = $('#filterStartDate').val();
            var endDate = $('#filterEndDate').val();
            var warehouse = $('#filterWarehouse').val();
            var tank = $('#filterTank').val();

            var params = [];
            if (startDate) params.push('start_date=' + encodeURIComponent(startDate));
            if (endDate) params.push('end_date=' + encodeURIComponent(endDate));
            if (warehouse) params.push('warehouse=' + encodeURIComponent(warehouse));
            if (tank) params.push('tank=' + encodeURIComponent(tank));

            var url = 'cns-inventory-tracking-data' + (params.length ? '?' + params.join('&') : '');
            ProjectionGrid.clearAndLoad(url);
        }
    }

    function loadTankMonitoringCards() {
        $.get('cns-master-tank-balances', function(res) {
            if (res.success && res.data && res.data.length > 0) {
                var container = $('#tankCardsContainer');
                container.empty();
                $.each(res.data, function(_, t) {
                    var pct = t.utilization_pct || 0;
                    var cardColorClass = 'bg-card-teal';
                    if (pct >= 90) {
                        cardColorClass = 'bg-card-coral';
                    } else if (pct >= 70) {
                        cardColorClass = 'bg-card-orange';
                    }

                    var stockFmt = cnsFormatNumber(t.current_stock, 2);
                    var capFmt = cnsFormatNumber(t.capacity, 2);
                    var remFmt = cnsFormatNumber(t.remaining, 2);
                    var safeName = $('<div>').text(t.name).html();

                    var tankIconSvg = '<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' +
                        '<path d="M5 7c0-2.2 3.1-4 7-4s7 1.8 7 4v10c0 2.2-3.1 4-7 4s-7-1.8-7-4V7z"/>' +
                        '<ellipse cx="12" cy="7" rx="7" ry="2.2"/>' +
                        '<path d="M5 12c0 1.2 3.1 2.2 7 2.2s7-1 7-2.2"/>' +
                        '</svg>';

                    var cardHtml = '<div class="col-xl-3 col-lg-6 col-md-6 col-12">' +
                        '<div class="tank-metric-card ' + cardColorClass + '" data-tank="' + safeName + '" onclick="filterByTank(this.getAttribute(\'data-tank\'))" title="Klik untuk filter tabel ke ' + safeName + '">' +
                            '<div>' +
                                '<div class="metric-header">' +
                                    '<span>' + safeName + '</span>' +
                                    '<span class="tank-pct-badge">' + pct + '%</span>' +
                                '</div>' +
                                '<div class="metric-value">' + stockFmt + ' <span style="font-size:13px;font-weight:600;">KG</span></div>' +
                            '</div>' +
                            '<div class="metric-icon-box">' + tankIconSvg + '</div>' +
                            '<div>' +
                                '<div class="tank-progress">' +
                                    '<div class="tank-progress-bar" style="width: ' + Math.min(pct, 100) + '%;"></div>' +
                                '</div>' +
                                '<div class="tank-meta">' +
                                    '<span>Kapasitas: ' + capFmt + ' KG</span>' +
                                    '<span>Sisa: ' + remFmt + ' KG</span>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>';
                    container.append(cardHtml);
                });
                $('#tankMonitoringCardSection').fadeIn();
            } else {
                $('#tankMonitoringCardSection').hide();
            }
        });
    }

    function filterByTank(tankName) {
        $('#filterTank').val(tankName).trigger('change');
        applyFilters();
    }

    function approveSelectedInbound() {
        var rowId = ProjectionGrid.getSelectedRowId();
        var targetId = rowId ? (ProjectionGrid.getRowAttribute(rowId, 'ID') || rowId) : inventoryId;
        if (!targetId || String(targetId) === 'row_opening') {
            swal('Perhatian', 'Pilih data transaksi terlebih dahulu', 'warning');
            return;
        }
        swal({
            title: 'Konfirmasi',
            text: 'Approve inbound movement ini? Stok tangki akan bertambah setelah di-approve.',
            icon: 'warning',
            buttons: ['Batal', 'Ya'],
            dangerMode: false
        }).then(function (confirmed) {
            if (!confirmed) return;
            $.ajax({
                url: 'cns-inventory-tracking-approve',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    id: targetId
                },
                success: function(res) {
                    if (res && res.success) {
                        swal('Berhasil!', res.message || 'Inbound movement berhasil di-approve', 'success').then(function () {
                            applyFilters();
                            if (typeof loadTankMonitoringCards === 'function') {
                                loadTankMonitoringCards();
                            }
                        });
                    } else {
                        swal('Error', (res && res.message) ? res.message : 'Gagal approve data', 'error');
                    }
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal approve data';
                    swal('Error', msg, 'error');
                }
            });
        });
    }

    function rejectSelectedInbound() {
        var rowId = ProjectionGrid.getSelectedRowId();
        var targetId = rowId ? (ProjectionGrid.getRowAttribute(rowId, 'ID') || rowId) : inventoryId;
        if (!targetId || String(targetId) === 'row_opening') {
            swal('Perhatian', 'Pilih data transaksi terlebih dahulu', 'warning');
            return;
        }
        var reason = prompt('Masukkan alasan penolakan (wajib diisi):');
        if (reason === null) {
            return;
        }
        reason = $.trim(reason);
        if (!reason) {
            swal('Perhatian', 'Alasan penolakan wajib diisi', 'warning');
            return;
        }
        $.ajax({
            url: 'cns-inventory-tracking-reject',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: targetId,
                reason: reason
            },
            success: function(res) {
                if (res && res.success) {
                    swal('Berhasil!', res.message || 'Inbound movement ditolak', 'success').then(function () {
                        applyFilters();
                        if (typeof loadTankMonitoringCards === 'function') {
                            loadTankMonitoringCards();
                        }
                    });
                } else {
                    swal('Error', (res && res.message) ? res.message : 'Gagal reject data', 'error');
                }
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Gagal reject data';
                swal('Error', msg, 'error');
            }
        });
    }

    function deleteQC() {
        var rowId = ProjectionGrid.getSelectedRowId();
        if (!rowId) {
            swal('Perhatian', 'Pilih data terlebih dahulu', 'warning');
            return;
        }

        swal({
            title: 'Konfirmasi',
            text: 'Yakin ingin menghapus data ini?',
            icon: 'warning',
            buttons: ['Batal', 'Ya'],
            dangerMode: true
        }).then(function (confirmed) {
            if (!confirmed) return;

            var tanggal = ProjectionGrid.cells(rowId, 2).getValue();
            var date = new Date(tanggal);
            var formatted = date.getFullYear() + '-' +
                String(date.getMonth() + 1).padStart(2, '0') + '-' +
                String(date.getDate()).padStart(2, '0');

            $.ajax({
                url: 'cns-quality-delete',
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    Date: formatted
                },
                success: function(res) {
                    if (res.status === 'success') {
                        applyFilters();
                    }
                },
                error: function() {
                    swal('Error', 'Gagal menghapus data', 'error');
                }
            });
        });
    }



    if (typeof dhtmlXGridObject !== 'undefined') {
        dhtmlXGridObject.prototype._in_header_stat_total_indo = function(e, a, h) {
            var g = function() {
                var serverKey = (a === 9) ? 'total_qty_sj' : ((a === 10) ? 'total_in' : ((a === 11) ? 'total_out' : ((a === 12) ? 'total_stock' : null)));
                if (serverKey && typeof this.getUserData === 'function') {
                    var sVal = this.getUserData("", serverKey);
                    if (sVal !== null && sVal !== undefined && sVal !== '') {
                        return sVal;
                    }
                }
                var n = 0;
                this._build_m_order();
                var m = this._m_order ? this._m_order[a] : a;
                for (var l = 0; l < this.rowsBuffer.length; l++) {
                    var row = this.rowsBuffer[l];
                    if (!row || row.idd === 'row_opening') continue;
                    var val = this._get_cell_value(row, m);
                    if (val === null || val === undefined || val === '') continue;
                    var c = cnsParseNumber(val, NaN);
                    n += isNaN(c) ? 0 : c;
                }
                return n ? cnsFormatNumber(n, 2) : '0.00';
            };
            this._stat_in_header(e, g, a, h, h);
        };
    }

    function initProjectionGrid() {
        $('#ProjectionSection').show()

        let tHeader = [
            'No', 'Bulan', 'Tanggal',
            'Asal Warehouse', 'Nama Tangki', 'Reference No', 'Fleet', 'No. Polisi', 'Driver', 'QTY SJ',
            'IN (KG)', 'OUT (KG)', 'Jumlah Stok Pelindo (KG)',
            'Remarks', 'Link', 'Status Approval',
            'Parameter QC', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan'
        ];

        let tGroupHeader = [
            '#rspan', '#rspan', '#rspan',
            '#rspan', '#rspan', '#rspan', '#rspan', '#rspan', '#rspan', '#rspan',
            '#rspan', '#rspan', '#rspan',
            '#rspan', '#rspan', '#rspan',
            'FFA Palmitic', 'FFA Oleic', 'Moisture', 'Impurities', 'Unsaponifiables', 'Sulfur', 'IV', 'Chlorides', 'Phosphorus', 'Metals', 'Link'
        ];

        let tWidth = [
            '45',   // No
            '85',   // Bulan
            '95',   // Tanggal
            '130',  // Asal Warehouse
            '110',  // Nama Tangki
            '130',  // Nopol / Kontainer
            '130',  // Fleet
            '110',  // No. Polisi
            '130',  // Driver
            '85',   // QTY SJ
            '95',   // IN (KG)
            '95',   // OUT (KG)
            '150',  // Jumlah Stok Pelindo (KG)
            '100',  // Remarks
            '110',  // Link (Nota Timbang)
            '110',  // Status Approval
            '85',   // FFA Palmitic
            '85',   // FFA Oleic
            '75',   // Moisture
            '80',   // Impurities
            '100',  // Unsaponifiables
            '75',   // Sulfur
            '75',   // IV
            '75',   // Chlorides
            '80',   // Phosphorus
            '80',   // Metals
            '110'   // Link (QC)
        ];

        let tColType = [
            'ro', 'ro', 'ro',
            'ro', 'ro', 'ro', 'ro', 'ro', 'ro', 'ro',
            'ro', 'ro', 'ro',
            'ro', 'link', 'ro',
            'ro', 'ro', 'ro', 'ro', 'ro', 'ro', 'ro', 'ro', 'ro', 'ro', 'link'
        ];

        let tColAlign = [
            'center', 'center', 'center',
            'left', 'center', 'center', 'left', 'center', 'left', 'right',
            'right', 'right', 'right',
            'left', 'center', 'center',
            'right', 'right', 'right', 'right', 'right', 'right', 'right', 'right', 'right', 'right', 'center'
        ];

        let tColSort = [
            'int', 'str', 'date',
            'str', 'str', 'str', 'str', 'str', 'str', 'int',
            'int', 'int', 'int',
            'str', 'str', 'str',
            'str', 'str', 'str', 'str', 'str', 'str', 'str', 'str', 'str', 'str', 'str'
        ];

        ProjectionGrid = new dhtmlXGridObject('ProjectionGrid');
        ProjectionGrid.setImagePath("./dhtmlx/imgs/");
        ProjectionGrid.setHeader(tHeader.join(','));
        ProjectionGrid.attachHeader(tGroupHeader.join(','));
        ProjectionGrid.setInitWidths(tWidth.join(','));
        ProjectionGrid.setColAlign(tColAlign.join(','));
        ProjectionGrid.setColSorting(tColSort.join(','));
        ProjectionGrid.setColTypes(tColType.join(','));
        ProjectionGrid.setSkin("dhx_skyblue");

        let tFooter = [
            'Total',
            '#cspan',
            '#cspan',
            '#cspan',
            '#cspan',
            '#cspan',
            '#cspan',
            '#cspan',
            '#cspan',
            '#stat_total_indo',
            '#stat_total_indo',
            '#stat_total_indo',
            '#stat_total_indo',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];

        let tFooterStyle = [
            'text-align:center;font-weight:bold;',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'text-align:right;font-weight:bold;',
            'text-align:right;font-weight:bold;',
            'text-align:right;font-weight:bold;',
            'text-align:right;font-weight:bold;',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        ];
        ProjectionGrid.attachFooter(tFooter.join(','), tFooterStyle);

        ProjectionGrid.enableColSpan(true);
        ProjectionGrid.init();

        ProjectionGrid.attachEvent('onBeforeSelect', function(new_row) {
            if (new_row === 'row_opening') {
                return false;
            }
            return true;
        });

        ProjectionGrid.attachEvent('onRowSelect', function(id) {
            if (id === 'row_opening') {
                inventoryId = null;
                return;
            }
            inventoryId = ProjectionGrid.getRowAttribute(id, 'ID') || id;
        });

        ProjectionGrid.attachEvent("onXLE", function() {
            inventoryId = null;
            var rowId = ProjectionGrid.getRowId(0);
            if (rowId) {
                var cellVal = ProjectionGrid.cells(rowId, 0).getValue();
                if (rowId === 'row_opening' || (cellVal && cellVal.indexOf('OPENING STOCK') !== -1)) {
                    var cell = ProjectionGrid.cells(rowId, 0).cell;
                    if (!cell || cell.colSpan < 12) {
                        ProjectionGrid.setColspan(rowId, 0, 12);
                    }
                    ProjectionGrid.setRowColor(rowId, "#FFF2CC");
                    ProjectionGrid.setRowTextStyle(rowId, "font-weight:bold;text-align:center;cursor:default;");
                    ProjectionGrid.setCellTextStyle(rowId, 12, "font-weight:bold;text-align:right;cursor:default;");
                }
            }
        });

        ProjectionPagination = initCnsGridPagination(ProjectionGrid, {
            pageSize: 25,
            pageSizeOptions: [10, 25, 50, 100],
            getUrl: function (params) {
                var q = [];
                q.push('posStart=' + params.posStart);
                q.push('count=' + params.count);

                var startDate = $('#filterStartDate').val();
                var endDate = $('#filterEndDate').val();
                var warehouse = $('#filterWarehouse').val();
                var tank = $('#filterTank').val();

                if (startDate) q.push('start_date=' + encodeURIComponent(startDate));
                if (endDate)   q.push('end_date=' + encodeURIComponent(endDate));
                if (warehouse) q.push('warehouse=' + encodeURIComponent(warehouse));
                if (tank)      q.push('tank=' + encodeURIComponent(tank));

                if (params.sortCol !== null && params.sortCol !== undefined) {
                    q.push('sort_col=' + params.sortCol);
                    q.push('sort_dir=' + (params.sortDir || 'asc'));
                }

                return 'cns-inventory-tracking-data?' + q.join('&');
            }
        });

        applyFilters();
    }


    function selectMitra(id, cellIdx) {
        mitraId = PartnerGrid.cells(id, 0).getValue();
    }

    function doubleClickMitra() {

    }

    // Tab switching and Upcoming Grid logic
    var UpcomingGrid = null;
    var upcomingCurrentPage = 1;
    var upcomingPerPage = 25;

    function switchInboundTab(tab) {
        if (tab === 'upcoming') {
            $('#tab-monitoring-link').removeClass('active').attr('aria-selected', 'false');
            $('#tab-upcoming-link').addClass('active').attr('aria-selected', 'true');
            $('#tab-monitoring').removeClass('show active').css('display', 'none');
            $('#tab-upcoming').addClass('show active').css('display', 'block');
            $('#monitoringHeaderActions').removeClass('d-flex').addClass('d-none');
            $('#upcomingHeaderActions').removeClass('d-none').addClass('d-flex');

            initUpcomingGrid();
            setTimeout(function() {
                if (UpcomingGrid) {
                    UpcomingGrid.setSizes();
                }
            }, 60);
        } else {
            $('#tab-upcoming-link').removeClass('active').attr('aria-selected', 'false');
            $('#tab-monitoring-link').addClass('active').attr('aria-selected', 'true');
            $('#tab-upcoming').removeClass('show active').css('display', 'none');
            $('#tab-monitoring').addClass('show active').css('display', 'block');
            $('#upcomingHeaderActions').removeClass('d-flex').addClass('d-none');
            $('#monitoringHeaderActions').removeClass('d-none').addClass('d-flex');

            setTimeout(function() {
                if (ProjectionGrid) {
                    ProjectionGrid.setSizes();
                }
            }, 60);
        }
    }

    function initUpcomingGrid() {
        if (UpcomingGrid) {
            UpcomingGrid.setSizes();
            return;
        }
        var container = document.getElementById('UpcomingGrid');
        if (!container) return;

        UpcomingGrid = new dhtmlXGridObject('UpcomingGrid');
        UpcomingGrid.setImagePath("./dhtmlx/imgs/");
        UpcomingGrid.setHeader("No,Kode Movement,Tanggal,ETA,Asal Gudang,Tujuan Gudang,Total Qty (KG),Logistics Status");
        UpcomingGrid.setInitWidths("50,170,110,110,190,200,130,*");
        UpcomingGrid.setColAlign("center,left,left,left,left,left,right,center");
        UpcomingGrid.setColSorting("int,str,str,str,str,str,int,str");
        UpcomingGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,cns_badge");
        UpcomingGrid.init();

        loadUpcomingGridData(false);
    }

    $(document).ready(function() {
        if (window.location.hash === '#upcoming') {
            switchInboundTab('upcoming');
        }
    });

    function loadUpcomingGridData(forceRefresh) {
        if (!UpcomingGrid) return;
        var params = [];
        var dateType = $('#filterUpcomingDateType').val() || 'eta';
        var start    = $('#filterUpcomingStartDate').val();
        var end      = $('#filterUpcomingEndDate').val();
        var search   = $('#filterUpcomingSearch').val();

        params.push('date_type=' + encodeURIComponent(dateType));
        if (start)  params.push('start_date=' + encodeURIComponent(start));
        if (end)    params.push('end_date=' + encodeURIComponent(end));
        if (search) params.push('search=' + encodeURIComponent(search));
        params.push('page=' + encodeURIComponent(upcomingCurrentPage));
        params.push('per_page=' + encodeURIComponent(upcomingPerPage));
        if (forceRefresh === true) {
            params.push('refresh=1');
        }

        var url = 'xml-cns-upcoming-inbound?' + params.join('&');
        var $btn = $('#btnRefreshUpcomingApi');
        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Memuat...');

        $.ajax({
            url: url,
            method: 'GET',
            dataType: 'text'
        }).done(function(xml) {
            UpcomingGrid.clearAll();
            UpcomingGrid.parse(xml);

            var meta = { totalCount: 0, page: 1, perPage: upcomingPerPage, totalPages: 1, sourceCount: 0, usedExcluded: 0, doneExcluded: 0, dateExcluded: 0, searchExcluded: 0, filteredCount: 0 };
            try {
                var xmlDoc = new DOMParser().parseFromString(xml, 'text/xml');
                var rowsEl = xmlDoc.getElementsByTagName('rows')[0];
                if (rowsEl) {
                    meta.totalCount   = parseInt(rowsEl.getAttribute('total_count') || '0', 10) || 0;
                    meta.page         = parseInt(rowsEl.getAttribute('page') || '1', 10) || 1;
                    meta.perPage      = parseInt(rowsEl.getAttribute('per_page') || String(upcomingPerPage), 10) || upcomingPerPage;
                    meta.totalPages   = parseInt(rowsEl.getAttribute('total_pages') || '1', 10) || 1;
                    meta.sourceCount  = parseInt(rowsEl.getAttribute('source_count') || '0', 10) || 0;
                    meta.usedExcluded = parseInt(rowsEl.getAttribute('used_excluded') || '0', 10) || 0;
                    meta.doneExcluded = parseInt(rowsEl.getAttribute('done_excluded') || '0', 10) || 0;
                    meta.dateExcluded = parseInt(rowsEl.getAttribute('date_excluded') || '0', 10) || 0;
                    meta.searchExcluded = parseInt(rowsEl.getAttribute('search_excluded') || '0', 10) || 0;
                    meta.filteredCount = parseInt(rowsEl.getAttribute('filtered_count') || '0', 10) || 0;
                }
            } catch (e) {}

            upcomingCurrentPage = meta.page;
            upcomingPerPage = meta.perPage;
            $('#upcomingPerPage').val(String(meta.perPage));
            renderUpcomingPagination(meta);
            renderUpcomingState(meta);
        }).fail(function(xhr) {
            UpcomingGrid.clearAll();
            renderUpcomingPagination({ totalCount: 0, page: 1, perPage: upcomingPerPage, totalPages: 1 });
            $('#upcomingEmptyNotice').hide();
            var msg = 'Gagal mengambil data movements dari APIPI.';
            try {
                var parsed = JSON.parse(xhr.responseText || '{}');
                if (parsed && parsed.message) {
                    msg = parsed.message;
                }
            } catch (e) {}
            $('#upcomingErrorNotice').text(msg).show();
        }).always(function() {
            $btn.prop('disabled', false).html(originalHtml);
        });
    }

    function renderUpcomingState(meta) {
        var $empty = $('#upcomingEmptyNotice');
        var $error = $('#upcomingErrorNotice');
        $error.hide().text('');

        if (meta.totalCount > 0) {
            $empty.hide();
            return;
        }

        var msg = 'Tidak ada data upcoming inbound.';
        if (meta.sourceCount === 0) {
            msg = 'APIPI tidak mengembalikan movement apa pun. Pastikan akun APIPI memiliki data movement dan filter tanggal sudah benar.';
        } else if (meta.doneExcluded > 0 && meta.filteredCount === 0) {
            msg = 'Semua movement dari APIPI sudah berstatus done sehingga tidak ditampilkan.';
        } else if (meta.usedExcluded > 0 && meta.filteredCount === 0) {
            msg = 'Semua movement dari APIPI sudah pernah dicatat sebagai inbound.';
        } else if (meta.dateExcluded > 0) {
            msg = 'Tidak ada movement pada rentang tanggal yang dipilih.';
        } else if (meta.searchExcluded > 0) {
            msg = 'Tidak ada movement yang cocok dengan pencarian.';
        }

        $empty.text(msg).show();
    }

    function renderUpcomingPagination(meta) {
        var total = meta.totalCount || 0;
        var page = meta.page || 1;
        var perPage = meta.perPage || upcomingPerPage;
        var totalPages = meta.totalPages || 1;

        if (total === 0) {
            $('#upcomingPaginationInfo').text('Menampilkan 0 data');
        } else {
            var startRow = (page - 1) * perPage + 1;
            var endRow = Math.min(page * perPage, total);
            $('#upcomingPaginationInfo').text('Menampilkan ' + startRow + '–' + endRow + ' dari ' + total + ' data');
        }

        var $ul = $('#upcomingPaginationControls').empty();
        var addItem = function(label, targetPage, disabled, active) {
            var cls = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
            var $li = $('<li>', { 'class': cls });
            var $a = $('<a>', { 'class': 'page-link', href: '#' }).text(label);
            if (!disabled && !active && targetPage !== null) {
                $a.on('click', function(e) {
                    e.preventDefault();
                    goToUpcomingPage(targetPage);
                });
            } else {
                $a.on('click', function(e) { e.preventDefault(); });
            }
            $li.append($a);
            $ul.append($li);
        };

        addItem('Prev', page - 1, page <= 1, false);

        var pages = [];
        for (var p = 1; p <= totalPages; p++) {
            if (p === 1 || p === totalPages || (p >= page - 2 && p <= page + 2)) {
                pages.push(p);
            }
        }

        var prevP = 0;
        for (var i = 0; i < pages.length; i++) {
            var pNum = pages[i];
            if (prevP && pNum - prevP > 1) {
                addItem('...', null, true, false);
            }
            addItem(String(pNum), pNum, false, pNum === page);
            prevP = pNum;
        }

        addItem('Next', page + 1, page >= totalPages, false);
    }

    function goToUpcomingPage(page) {
        if (page < 1) return;
        upcomingCurrentPage = page;
        loadUpcomingGridData(false);
    }

    function changeUpcomingPerPage(perPage) {
        upcomingPerPage = parseInt(perPage, 10) || 25;
        upcomingCurrentPage = 1;
        loadUpcomingGridData(false);
    }

    function applyUpcomingFilters() {
        upcomingCurrentPage = 1;
        loadUpcomingGridData(false);
    }

    function resetUpcomingFilters() {
        $('#filterUpcomingDateType').val('eta');
        $('#filterUpcomingStartDate').val('');
        $('#filterUpcomingEndDate').val('');
        $('#filterUpcomingSearch').val('');
        upcomingCurrentPage = 1;
        loadUpcomingGridData(false);
    }
</script>
@endsection