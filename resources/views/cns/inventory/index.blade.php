@extends('layout.mainlayout-cns')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Inventory</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home-cns') }}">Home</a></li>
        <li class="breadcrumb-item">Inventory UCO</li>
        <li class="breadcrumb-item active">
            <span class="active">Inbound Movement</span>
        </li>
    </ol>
</div>

<style>
    /* Table styling to match Pelindo Inventory design */
    #ProjectionGrid .hdr tr:first-child td {
        background-color: #174375 !important;
        color: #000000 !important;
        font-weight: bold !important;
        text-align: center !important;
        vertical-align: middle !important;
        border-right: 1px solid #ffffff !important;
        font-size: 12px !important;
    }
    #ProjectionGrid .hdr tr:nth-child(2) td {
        background-color: #FFF2CC !important;
        color: #000000 !important;
        font-weight: bold !important;
        text-align: center !important;
        vertical-align: middle !important;
        border-right: 1px solid #b5b5b5 !important;
        font-size: 12px !important;
    }
    /* Parameter subheader columns (FFA, MC, IMP, Link QC) */
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
    #ProjectionGrid td {
        font-size: 12px !important;
        vertical-align: middle !important;
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
    #UpcomingGrid .hdr tr:first-child td {
        background-color: #174375 !important;
        color: #ffffff !important;
        font-weight: bold !important;
        text-align: center !important;
        vertical-align: middle !important;
        border-right: 1px solid #ffffff !important;
        font-size: 12px !important;
    }
    #UpcomingGrid td {
        font-size: 12px !important;
        vertical-align: middle !important;
    }
    .card-header-tabs .nav-link {
        border: none !important;
        border-bottom: 3px solid transparent !important;
        color: #6c757d;
        padding: 12px 24px;
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
</style>

<div id="ProjectionSection" class="row m-1">
    <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">INBOUND MOVEMENT</div>

    <!-- Main Card Container with Header Tabs -->
    <div class="card w-100 shadow-sm" style="border: 1px solid #ced4da; border-radius: 4px;">
        <div class="card-header bg-white border-bottom p-0">
            <ul class="nav nav-tabs card-header-tabs m-0 border-bottom-0" id="inboundMovementTabs" role="tablist">
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
        </div>
        <div class="card-body p-3">
            <div class="tab-content" id="inboundMovementTabContent">
                <!-- TAB 1: MONITORING STOCK UCO -->
                <div class="tab-pane fade show active" id="tab-monitoring" role="tabpanel" aria-labelledby="tab-monitoring-link">
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
                        <div class="row" id="tankCardsContainer">
                            <!-- Dynamic Tank Cards -->
                        </div>
                    </div>

                    <div class="d-flex align-items-end flex-wrap gap-2 mb-2">
                        <div class="m-1">
                            <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                            <input type="date" id="filterStartDate" class="form-control form-control-sm" style="width:145px;" />
                        </div>
                        <div class="m-1">
                            <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                            <input type="date" id="filterEndDate" class="form-control form-control-sm" style="width:145px;" />
                        </div>
                        <div class="m-1" style="width:200px;">
                            <label class="d-block mb-1"><small>Warehouse</small></label>
                            <select id="filterWarehouse" class="form-control form-control-sm" style="width:100%;">
                                <option value="">-- Semua --</option>
                            </select>
                        </div>
                        <div class="m-1" style="width:180px;">
                            <label class="d-block mb-1"><small>Nama Tangki</small></label>
                            <select id="filterTank" class="form-control form-control-sm" style="width:100%;">
                                <option value="">-- Semua --</option>
                            </select>
                        </div>
                        <div class="m-1">
                            <button class="btn btn-sm btn-primary" onclick="applyFilters()">Filter</button>
                            <button class="btn btn-sm btn-secondary ml-1" onclick="resetFilters()">Reset</button>
                        </div>
                    </div>
                    <div id="ProjectionGrid" style="width:100%;height:450px; background-color:white;overflow:hidden" class="mt-2 border"></div>
                    <div>
                        <input type="button" id="CreateUCO" name="CreateUCO" value="Create" class="btn btn-info mt-2 mr-2" onclick="createUCO()" />
                        <input type="button" id="DetailQC" name="DetailQC" value="Detail/Update" class="btn btn-info mt-2 mr-2" onclick="detailQC()" />
                        <input type="button" id="DetailQC" name="DetailQC" value="Log" class="btn btn-info mt-2 mr-2" onclick="alert('under development');" />
                    </div>
                </div>

                <!-- TAB 2: UPCOMING INBOUND -->
                <div class="tab-pane fade" id="tab-upcoming" role="tabpanel" aria-labelledby="tab-upcoming-link">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div class="d-flex align-items-end flex-wrap gap-2">
                            <div class="m-1">
                                <label class="d-block mb-1"><small>Dari Tanggal</small></label>
                                <input type="date" id="filterUpcomingStartDate" class="form-control form-control-sm" style="width:145px;" />
                            </div>
                            <div class="m-1">
                                <label class="d-block mb-1"><small>Sampai Tanggal</small></label>
                                <input type="date" id="filterUpcomingEndDate" class="form-control form-control-sm" style="width:145px;" />
                            </div>
                            <div class="m-1" style="width:240px;">
                                <label class="d-block mb-1"><small>Pencarian</small></label>
                                <input type="text" id="filterUpcomingSearch" class="form-control form-control-sm" placeholder="Kode / Asal / Tujuan / Status" onkeyup="if(event.keyCode===13) applyUpcomingFilters()" />
                            </div>
                            <div class="m-1">
                                <button class="btn btn-sm btn-primary" onclick="applyUpcomingFilters()">Filter</button>
                                <button class="btn btn-sm btn-secondary ml-1" onclick="resetUpcomingFilters()">Reset</button>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnRefreshUpcomingApi" onclick="loadUpcomingGridData(true)">
                                <i class="fa fa-refresh mr-1"></i> Refresh Data
                            </button>
                        </div>
                    </div>

                    <div id="UpcomingGrid" style="width:100% !important; min-height:450px; height:450px; background-color:white; overflow:hidden;" class="mt-2 border"></div>

                    <div id="upcomingPaginationBar" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2">
                        <div class="d-flex align-items-center">
                            <span id="upcomingPaginationInfo" class="text-muted small mr-3">Menampilkan 0 data</span>
                            <div class="d-flex align-items-center">
                                <label for="upcomingPerPage" class="text-muted small mb-0 mr-2">Per halaman:</label>
                                <select id="upcomingPerPage" class="form-control form-control-sm" style="width:auto;" onchange="changeUpcomingPerPage(this.value)">
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
</div>
@include('cns.inventory.create')
@include('cns.inventory.edit')
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
            alert('Pilih data terlebih dahulu');
            return;
        }
        if (inventoryId === 'row_opening') {
            alert('Opening stock tidak dapat diedit');
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

    function applyFilters() {
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

    function loadTankMonitoringCards() {
        $.get('cns-master-tank-balances', function(res) {
            if (res.success && res.data && res.data.length > 0) {
                var container = $('#tankCardsContainer');
                container.empty();
                $.each(res.data, function(_, t) {
                    var pct = t.utilization_pct || 0;
                    var barClass = 'bg-success';
                    var badgeClass = 'badge-success';
                    if (pct >= 90) {
                        barClass = 'bg-danger';
                        badgeClass = 'badge-danger';
                    } else if (pct >= 70) {
                        barClass = 'bg-warning';
                        badgeClass = 'badge-warning';
                    }

                    var stockFmt = cnsFormatNumber(t.current_stock, 2);
                    var capFmt = cnsFormatNumber(t.capacity, 2);
                    var remFmt = cnsFormatNumber(t.remaining, 2);

                    var cardHtml = '<div class="col-md-3 col-sm-6 mb-2">' +
                        '<div class="card border shadow-sm h-100" style="cursor: pointer;" onclick="filterByTank(\'' + t.name + '\')" title="Klik untuk filter tabel ke ' + t.name + '">' +
                            '<div class="card-body p-2">' +
                                '<div class="d-flex justify-content-between align-items-center mb-1">' +
                                    '<span class="font-weight-bold text-dark" style="font-size: 13px;">' +
                                        '<i class="fa fa-cube text-primary mr-1"></i>' + t.name +
                                    '</span>' +
                                    '<span class="badge ' + badgeClass + '">' + pct + '%</span>' +
                                '</div>' +
                                '<div class="progress mb-2" style="height: 8px;">' +
                                    '<div class="progress-bar ' + barClass + '" role="progressbar" style="width: ' + Math.min(pct, 100) + '%;"></div>' +
                                '</div>' +
                                '<div class="d-flex justify-content-between text-muted" style="font-size: 11px;">' +
                                    '<span>Stok: <strong>' + stockFmt + '</strong> KG</span>' +
                                    '<span>Kapasitas: ' + capFmt + ' KG</span>' +
                                '</div>' +
                                '<div class="text-right text-muted mt-1" style="font-size: 10px;">' +
                                    'Sisa: ' + remFmt + ' KG' +
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

    function deleteQC() {
        var rowId = ProjectionGrid.getSelectedRowId();
        if (!rowId) {
            alert('Pilih data terlebih dahulu');
            return;
        }

        if (!confirm('Yakin ingin menghapus data ini?')) {
            return;
        }

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
                alert('Gagal menghapus data');
            }
        });
    }



    if (typeof dhtmlXGridObject !== 'undefined') {
        dhtmlXGridObject.prototype._in_header_stat_total_indo = function(e, a, h) {
            var g = function() {
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
            'Asal Warehouse', 'Nama Tangki', 'Nopol / Kontainer', 'QTY SJ',
            'IN (KG)', 'OUT (KG)', 'Jumlah Stok Pelindo (KG)',
            'Remarks', 'Link',
            'Parameter QC', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan', '#cspan'
        ];

        let tGroupHeader = [
            '#rspan', '#rspan', '#rspan',
            '#rspan', '#rspan', '#rspan', '#rspan',
            '#rspan', '#rspan', '#rspan',
            '#rspan', '#rspan',
            'FFA Palmitic', 'FFA Oleic', 'MIU', 'Sulfur', 'IV', 'Chlorides', 'Phosphorus', 'Metals', 'Link'
        ];

        let tWidth = [
            '45',   // No
            '85',   // Bulan
            '95',   // Tanggal
            '130',  // Asal Warehouse
            '110',  // Nama Tangki
            '130',  // Nopol / Kontainer
            '85',   // QTY SJ
            '95',   // IN (KG)
            '95',   // OUT (KG)
            '150',  // Jumlah Stok Pelindo (KG)
            '100',  // Remarks
            '110',  // Link (Nota Timbang)
            '85',   // FFA Palmitic
            '85',   // FFA Oleic
            '75',   // MIU
            '75',   // Sulfur
            '75',   // IV
            '75',   // Chlorides
            '80',   // Phosphorus
            '80',   // Metals
            '110'   // Link (QC)
        ];

        let tColType = [
            'ro', 'ro', 'ro',
            'ro', 'ro', 'ro', 'ro',
            'ro', 'ro', 'ro',
            'ro', 'link',
            'ro', 'ro', 'ro', 'ro', 'ro', 'ro', 'ro', 'ro', 'link'
        ];

        let tColAlign = [
            'center', 'center', 'center',
            'left', 'center', 'center', 'right',
            'right', 'right', 'right',
            'left', 'center',
            'right', 'right', 'right', 'right', 'right', 'right', 'right', 'right', 'center'
        ];

        let tColSort = [
            'int', 'str', 'date',
            'str', 'str', 'str', 'int',
            'int', 'int', 'int',
            'str', 'str',
            'str', 'str', 'str', 'str', 'str', 'str', 'str', 'str', 'str'
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
            ''
        ];

        let tFooterStyle = [
            'text-align:center;font-weight:bold;',
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
                    if (!cell || cell.colSpan < 9) {
                        ProjectionGrid.setColspan(rowId, 0, 9);
                    }
                    ProjectionGrid.setRowColor(rowId, "#FFF2CC");
                    ProjectionGrid.setRowTextStyle(rowId, "font-weight:bold;text-align:center;cursor:default;");
                    ProjectionGrid.setCellTextStyle(rowId, 9, "font-weight:bold;text-align:right;cursor:default;");
                }
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
        UpcomingGrid.setHeader("No,Kode Movement,Tanggal,Asal Gudang,Tujuan Gudang,Total Qty (KG),Logistics Status");
        UpcomingGrid.setInitWidths("50,180,120,200,220,140,*");
        UpcomingGrid.setColAlign("center,left,center,left,left,right,center");
        UpcomingGrid.setColSorting("int,str,str,str,str,int,str");
        UpcomingGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
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
        var start  = $('#filterUpcomingStartDate').val();
        var end    = $('#filterUpcomingEndDate').val();
        var search = $('#filterUpcomingSearch').val();

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

            var meta = { totalCount: 0, page: 1, perPage: upcomingPerPage, totalPages: 1 };
            try {
                var xmlDoc = new DOMParser().parseFromString(xml, 'text/xml');
                var rowsEl = xmlDoc.getElementsByTagName('rows')[0];
                if (rowsEl) {
                    meta.totalCount = parseInt(rowsEl.getAttribute('total_count') || '0', 10) || 0;
                    meta.page       = parseInt(rowsEl.getAttribute('page') || '1', 10) || 1;
                    meta.perPage    = parseInt(rowsEl.getAttribute('per_page') || String(upcomingPerPage), 10) || upcomingPerPage;
                    meta.totalPages = parseInt(rowsEl.getAttribute('total_pages') || '1', 10) || 1;
                }
            } catch (e) {}

            upcomingCurrentPage = meta.page;
            upcomingPerPage = meta.perPage;
            $('#upcomingPerPage').val(String(meta.perPage));
            renderUpcomingPagination(meta);
        }).fail(function(xhr) {
            UpcomingGrid.clearAll();
            renderUpcomingPagination({ totalCount: 0, page: 1, perPage: upcomingPerPage, totalPages: 1 });
            var msg = 'Gagal mengambil data movements dari APIPI.';
            try {
                var parsed = JSON.parse(xhr.responseText || '{}');
                if (parsed && parsed.message) {
                    msg = parsed.message;
                }
            } catch (e) {}
            alert(msg);
        }).always(function() {
            $btn.prop('disabled', false).html(originalHtml);
        });
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
        $('#filterUpcomingStartDate').val('');
        $('#filterUpcomingEndDate').val('');
        $('#filterUpcomingSearch').val('');
        upcomingCurrentPage = 1;
        loadUpcomingGridData(false);
    }
</script>
@endsection