@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item">
            <a href="#">Pickup</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Routing</span>
        </li>
    </ol>
</div>

<!-- Filter Card -->
<div class="card mb-3">
    <div class="card-header m-3">
        <h5>Filter</h5>
    </div>
    <div class="card-body">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label for="warehouseFilter">Gudang</label>
                <select id="warehouseFilter" class="form-control">
                    <option value="">-- Pilih Gudang --</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="dateFilterStart">Tanggal</label>
                <div class="input-group">
                    <input type="date" id="dateFilterStart" class="form-control" />
                    <div class="input-group-append input-group-prepend">
                        <span class="input-group-text">s/d</span>
                    </div>
                    <input type="date" id="dateFilterEnd" class="form-control" />
                </div>
            </div>
            <div class="col-md-2">
                <label for="fleetFilter">Driver</label>
                <select id="fleetFilter" class="form-control">
                    <option value="">-- Pilih Fleet --</option>
                </select>
            </div>
            <div class="col-md-2">
                <button id="filterBtn" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card mb-3">
    <div class="card-header m-3">
        <div class="row align-items-end">
            <div class="col-md-1">
                <h5>Daftar Pickup</h5>
            </div>
            <div class="col-md-auto ml-auto p-2">
                <button id="routeBtn" class="btn btn-primary">Route</button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div style="width:100%;overflow-x:auto;">
            <div id="routingGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<!-- Map Card -->
<div class="card mb-3">
    <div class="card-header m-3">
        <h5>Map</h5>
    </div>
    <div class="card-body p-0">
        <div id="map" style="height: 450px;"></div>
    </div>
</div>



<!-- Manajemen Biaya & Rute Card -->
<div class="card mb-3">
    <div class="card-header m-3">
        <div class="row align-items-end">
            <div class="col-md-3">
                <h5>Manajemen Biayaa & Route</h5>
            </div>
            <div class="col-md-auto ml-auto p-2">
                <button id="settleRouteBtn" class="btn btn-primary">Settlement</button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div style="width:100%;overflow-x:auto;">
            <div id="costRouteGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/@mapbox/polyline@1.2.0/src/polyline.js"></script>

<script>
    var map;
    var routingGrid;
    var costRouteGrid;

    function initMap() {
        map = L.map('map').setView([-6.2, 106.8], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
    }

    function initRoutingGrid() {
        routingGrid = new dhtmlXGridObject('routingGrid');
        routingGrid.setImagePath("./dhtmlx/imgs/");
        routingGrid.setHeader("#master_checkbox,Nama POO, Alamat, PO Code, Qty");
        routingGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
        routingGrid.setInitWidths("40,*,*,*,*");
        routingGrid.setColAlign("center,left,left,left,left");
        routingGrid.setColSorting("na,str,str,str,str");
        routingGrid.setColTypes("ch,ro,ro,ro,ro");
        routingGrid.setSkin("dhx_skyblue");
        routingGrid.enableMultiselect(true);

        routingGrid.init();
        routingGrid.enableAutoWidth(true);

        // Load zero data: empty XML response
        // routingGrid.loadXMLString('<rows></rows>');
        routingGrid.load(`partner-pickup-data?format=xml`);

    }

    function initCostRouteGrid() {
        costRouteGrid = new dhtmlXGridObject('costRouteGrid');
        costRouteGrid.setImagePath("./dhtmlx/imgs/");
        costRouteGrid.setHeader("#master_checkbox,Route Code, No. PO, Total Biaya,  Status, Dibuat, Jarak (km)");
        costRouteGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        costRouteGrid.setInitWidths("40,*,120,100,130,120,120");
        costRouteGrid.setColAlign("center,left,left,right,right,left,left");
        costRouteGrid.setColSorting("na,str,str,int,int,str,str");
        costRouteGrid.setColTypes("ch,ro,ro,ro,ro,ro,ro");
        costRouteGrid.setSkin("dhx_skyblue");
        costRouteGrid.enableMultiselect(true);
        costRouteGrid.init();
        costRouteGrid.load('partner-pickup-route-data?status=PENDING_SETTLEMENT&format=xml');
    }

    function loadWarehouseOptions() {
        $.ajax({
            url: 'partner-warehouse-data',
            type: 'GET',
            success: function(res) {
                var data = res.data || [];
                var $select = $('#warehouseFilter');
                $select.empty();
                $select.append('<option value="">-- Pilih Gudang --</option>');
                $.each(data, function(i, item) {
                    $select.append('<option value="' + item.id + '">' +
                        item.warehouse_location + ' - ' + item.warehouse_task_id + '</option>');
                });
            },
            error: function() {
                console.log('Failed to load warehouse options');
            }
        });
    }

    function initFleetSelect() {
        $('#fleetFilter').select2({
            placeholder: '-- Pilih Fleet --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: 'master-users',
                dataType: 'json',
                delay: 250,
                data: function(p) {
                    return {
                        q: p.term || ''
                    };
                },
                processResults: function(res) {
                    return {
                        results: res.data.map(function(row) {
                            return {
                                id: row.ID,
                                text: row.Name
                            };
                        })
                    };
                },
                cache: true
            }
        });
    }

    function loadRoutingData() {
        var warehouse = $('#warehouseFilter').val();
        var dateStart = $('#dateFilterStart').val();
        var dateEnd = $('#dateFilterEnd').val();

        var url = 'partner-pickup-data?format=xml';
        if (warehouse) url += '&whid=' + encodeURIComponent(warehouse);
        if (dateStart) url += '&date_start=' + encodeURIComponent(dateStart);
        if (dateEnd) url += '&date_end=' + encodeURIComponent(dateEnd);

        routingGrid.clearAll();
        routingGrid.load(url);
    }



    var osrmRouteData = null;
    // --- Step navigation ---
    function goToStep1() {
        $('#routeStep1').show();
        $('#routeStep2').hide();
    }

    function goToStep2() {
        $('#routeStep1').hide();
        $('#routeStep2').show();
        initFleetSelectInModal();
    }

    // --- Fleet select2 inside modal ---
    function initFleetSelectInModal() {
        $('#routeFleet').select2({
            placeholder: '-- Pilih Driver --',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#appModal'),
            ajax: {
                url: 'master-users',
                dataType: 'json',
                delay: 250,
                data: function(p) {
                    return {
                        q: p.term || ''
                    };
                },
                processResults: function(res) {
                    return {
                        results: res.data.map(function(row) {
                            return {
                                id: row.ID,
                                text: row.Name
                            };
                        })
                    };
                },
                cache: true
            }
        });
    }

    // --- Recalculate cost cards ---
    function recalcRouteCosts() {
        var jarak = parseFloat($('#routeTotalDistance').val()) || 0;
        var konsumsi = parseFloat($('#routeBbmConsumption').val()) || 10;
        var harga = parseFloat($('#routeBbmPrice').val()) || 13000;
        var tol = parseFloat($('#routeTollEstimation').val()) || 0;
        var parkir = parseFloat($('#routeParkingCost').val()) || 0;
        var alkon = parseFloat($('#routeAlkonBbmCost').val()) || 0;
        var opDriver = parseFloat($('#routeDriverOperational').val()) || 0;
        var opLapangan = parseFloat($('#routeFieldOperational').val()) || 0;

        var bbm = (jarak / konsumsi) * harga;
        var operasional = parkir + alkon + opDriver + opLapangan;
        var total = bbm + tol + operasional;

        $('#cardBbm').text(bbm);
        $('#cardTol').text(tol);
        $('#cardOperasional').text(operasional);
        $('#cardTotal').text(total);


    }

    // --- Fetch OSRM route from selected orders ---
    function fetchOSRMRoute(orderIds) {
        var $btn = $('#btnGetRoute');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Calculating...');

        $.ajax({
            url: 'partner-pickup-route-calculate',
            type: 'POST',
            data: {
                pickup_order_ids: orderIds,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                if (res.status === 'success') {
                    osrmRouteData = res.data;
                    $('#routeTotalDistance').val(res.data.distance_km);
                    recalcRouteCosts();
                    $btn.prop('disabled', false).html('<i class="fas fa-check"></i> Route Ready').removeClass('btn-outline-primary').addClass('btn-success');
                } else {
                    alert('Failed to get route: ' + (res.message || 'Unknown error'));
                    $btn.prop('disabled', false).html('Get Route');
                }
            },
            error: function(xhr) {
                alert('Error: ' + ((xhr.responseJSON && xhr.responseJSON.message) || xhr.statusText));
                $btn.prop('disabled', false).html('Get Route');
            }
        });
    }

    // --- Submit route with step 2 form data ---
    function submitRouteWithDetails(orderIds) {
        var payload = {
            pickup_order_ids: orderIds,
            bbm_ratio: $('#routeBbmRatio').val(),
            bbm_consumption: $('#routeBbmConsumption').val(),
            bbm_price: $('#routeBbmPrice').val(),
            fleet_id: $('#routeFleet').val(),
            pickup_date: $('#routePickupDate').val(),
            total_distance: $('#routeTotalDistance').val(),
            pickup_count: $('#routePickupCount').val(),
            total_weight: $('#routeTotalWeight').val(),
            toll_estimation: $('#routeTollEstimation').val(),
            parking_cost: $('#routeParkingCost').val(),
            alkon_bbm_cost: $('#routeAlkonBbmCost').val(),
            driver_operational: $('#routeDriverOperational').val(),
            field_operational: $('#routeFieldOperational').val(),
            subtotal_bbm: '0',
            subtotal_toll: '0',
            subtotal_operational: '0',
            total_cost: '0',
            apc: 0,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        var jarak = parseFloat(payload.total_distance) || 0;
        var konsumsi = parseFloat(payload.bbm_consumption) || 10;
        var harga = parseFloat(payload.bbm_price) || 13000;
        var tol = parseFloat(payload.toll_estimation) || 0;
        var parkir = parseFloat(payload.parking_cost) || 0;
        var alkon = parseFloat(payload.alkon_bbm_cost) || 0;
        var opDriver = parseFloat(payload.driver_operational) || 0;
        var opLapangan = parseFloat(payload.field_operational) || 0;

        payload.subtotal_bbm = (jarak / konsumsi) * harga;
        payload.subtotal_toll = tol;
        payload.subtotal_operational = parkir + alkon + opDriver + opLapangan;
        payload.total_cost = payload.subtotal_bbm + payload.subtotal_toll + payload.subtotal_operational;

        $.ajax({
            url: 'partner-pickup-route-submit',
            type: 'POST',
            data: payload,
            success: function(res) {
                if (res.status === 'success') {
                    var data = res.data;
                    var decoded = polyline.decode(data.encoded_polyline);
                    var latlngs = decoded.map(function(p) {
                        return [p[0], p[1]];
                    });
                    L.polyline(latlngs, {
                        color: 'blue',
                        weight: 5
                    }).addTo(map);
                    map.fitBounds(latlngs);
                    AppModal.close();
                    alert('Route berhasil disimpan! Distance: ' + data.distance_km + ' km');
                } else {
                    alert('Failed: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Error: ' + ((xhr.responseJSON && xhr.responseJSON.message) || xhr.statusText));
            }
        });
    }
    $(document).ready(function() {
        initMap();
        initCostRouteGrid();
        initRoutingGrid();
        loadWarehouseOptions();
        initFleetSelect();

        $('#filterBtn').on('click', function() {
            loadRoutingData();
        });

        $('#routeBtn').on('click', function() {
            var checkedRows = routingGrid.getCheckedRows(0);
            if (!checkedRows || checkedRows.length === 0) {
                alert('Pilih setidaknya satu baris di tabel routing');
                return;
            }

            var rowIndices = checkedRows.split(',');
            var selectedIds = [];
            for (var i = 0; i < rowIndices.length; i++) {
                selectedIds.push(routingGrid.getRowAttribute(rowIndices[i], 'ID'));
            }


            $.ajax({
                url: 'partner-pickup-data?format=json',
                type: 'GET',
                success: function(res) {
                    var orders = res.data || [];
                    console.log(orders, selectedIds)
                    var filteredOrders = orders.filter(function(o) {
                        return selectedIds.indexOf(String(o.ID)) !== -1;
                    });

                    var cardsHtml = '<div style="max-height:250px;overflow-y:auto;">';
                    filteredOrders.forEach(function(order) {
                        cardsHtml += '' +
                            '<div class="card mb-2 border-left-primary">' +
                            '<div class="card-body py-2">' +
                            '<div class="d-flex justify-content-between">' +
                            '<strong>' + order.POO_Name + '</strong>' +
                            '<span class="badge badge-secondary">Qty: ' + order.Qty + '</span>' +
                            '</div>' +
                            '<div class="small text-muted">' + order.POO_Full_Address + '</div>' +
                            '</div>' +
                            '</div>';
                    });

                    if (filteredOrders.length === 0) {
                        cardsHtml += '<p class="text-center text-muted">No pickup orders found</p>';
                    }
                    cardsHtml += '</div>';

                    // Step 1 body
                    var step1Html = '<div id="routeStep1">' +
                        '<h6 class="text-muted mb-3">Step 1: Review Pickup Orders</h6>' +
                        cardsHtml +
                        '<div class="text-right mt-3">' +
                        '<button type="button" class="btn btn-outline-primary mr-2" id="btnGetRoute">Get Route</button>' +
                        '<button type="button" class="btn btn-primary" onclick="goToStep2()">Next &rarr;</button>' +
                        '</div>' +
                        '</div>';

                    // Step 2 body
                    var step2Html = '<div id="routeStep2" style="display:none;">' +
                        '<h6 class="text-muted mb-3">Step 2: Route Details</h6>' +

                        '<div class="card mb-3 border-left-info">' +
                        '<div class="card-header py-2"><strong>Kendaraan &amp; BBM</strong></div>' +
                        '<div class="card-body py-2">' +
                        '<div class="row">' +
                        '<div class="col-md-4 form-group mb-0">' +
                        '<label class="small">BBM Ratio</label>' +
                        '<select id="routeBbmRatio" class="form-control form-control-sm">' +
                        '<option value="ev">Mobil Listrik (1:10)</option>' +
                        '</select>' +
                        '</div>' +
                        '<div class="col-md-4 form-group mb-0">' +
                        '<label class="small">Konsumsi BBM</label>' +
                        '<input type="number" id="routeBbmConsumption" class="form-control form-control-sm" value="10" readonly />' +
                        '</div>' +
                        '<div class="col-md-4 form-group mb-0">' +
                        '<label class="small">Harga BBM</label>' +
                        '<input type="number" id="routeBbmPrice" class="form-control form-control-sm" value="13000" readonly />' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +


                        '<div class="card mb-3 border-left-info">' +
                        '<div class="card-header py-2"><strong>Data Route</strong></div>' +
                        '<div class="card-body py-2">' +
                        '<div class="row">' +
                        '<div class="col-md-3 form-group mb-0">' +
                        '<label class="small">Total Jarak (km)</label>' +
                        '<input type="number" id="routeTotalDistance" class="form-control form-control-sm" oninput="recalcRouteCosts()" />' +
                        '</div>' +
                        '<div class="col-md-3 form-group mb-0">' +
                        '<label class="small">Jumlah Pickup</label>' +
                        '<input type="number" id="routePickupCount" class="form-control form-control-sm" value="15" readonly />' +
                        '</div>' +
                        '<div class="col-md-3 form-group mb-0">' +
                        '<label class="small">Total Berat (kg)</label>' +
                        '<input type="number" id="routeTotalWeight" class="form-control form-control-sm" value="1000" readonly />' +
                        '</div>' +
                        '<div class="col-md-3 form-group mb-0">' +
                        '<label class="small">Estimasi Tol</label>' +
                        '<input type="number" id="routeTollEstimation" class="form-control form-control-sm" value="300000" oninput="recalcRouteCosts()" />' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        '<div class="card mb-3 border-left-info">' +
                        '<div class="card-header py-2"><strong>Biaya Operasional</strong></div>' +
                        '<div class="card-body py-2">' +
                        '<div class="row">' +
                        '<div class="col-md-3 form-group mb-0">' +
                        '<label class="small">Biaya Parkir</label>' +
                        '<input type="number" id="routeParkingCost" class="form-control form-control-sm" oninput="recalcRouteCosts()" />' +
                        '</div>' +
                        '<div class="col-md-3 form-group mb-0">' +
                        '<label class="small">BBM Alkon</label>' +
                        '<input type="number" id="routeAlkonBbmCost" class="form-control form-control-sm" oninput="recalcRouteCosts()" />' +
                        '</div>' +
                        '<div class="col-md-3 form-group mb-0">' +
                        '<label class="small">Operasional Driver</label>' +
                        '<input type="number" id="routeDriverOperational" class="form-control form-control-sm" oninput="recalcRouteCosts()" />' +
                        '</div>' +
                        '<div class="col-md-3 form-group mb-0">' +
                        '<label class="small">Operasional Lapangan</label>' +
                        '<input type="number" id="routeFieldOperational" class="form-control form-control-sm" oninput="recalcRouteCosts()" />' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        // Summary Cards
                        '<div class="row">' +
                        '<div class="col-md-3">' +
                        '<div class="card border-left-primary shadow-sm mb-2">' +
                        '<div class="card-body py-2 text-center">' +
                        '<small class="text-muted">BBM</small>' +
                        '<h6 class="mb-0" id="cardBbm">Rp 0</h6>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-md-3">' +
                        '<div class="card border-left-success shadow-sm mb-2">' +
                        '<div class="card-body py-2 text-center">' +
                        '<small class="text-muted">Tol</small>' +
                        '<h6 class="mb-0" id="cardTol">Rp 0</h6>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-md-3">' +
                        '<div class="card border-left-warning shadow-sm mb-2">' +
                        '<div class="card-body py-2 text-center">' +
                        '<small class="text-muted">Operasional</small>' +
                        '<h6 class="mb-0" id="cardOperasional">Rp 0</h6>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-md-3">' +
                        '<div class="card border-left-danger shadow-sm mb-2">' +
                        '<div class="card-body py-2 text-center">' +
                        '<small class="text-muted">Total Biaya</small>' +
                        '<h6 class="mb-0" id="cardTotal">Rp 0</h6>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        // Navigation
                        '<div class="text-right mt-2">' +
                        '<button type="button" class="btn btn-secondary" onclick="goToStep1()">&larr; Back</button> ' +
                        '<button type="button" class="btn btn-primary" id="btnSubmitRouteModal">Submit Route</button>' +
                        '</div>' +
                        '</div>';

                    var bodyHtml = step1Html + step2Html;

                    AppModal.open({
                        title: 'Route - Pickup Orders',
                        size: 'xl',
                        body: bodyHtml,
                        actions: []
                    });

                    $(document).off('click', '#btnGetRoute').on('click', '#btnGetRoute', function() {
                        fetchOSRMRoute(selectedIds);
                    });

                    $(document).off('click', '#btnSubmitRouteModal').on('click', '#btnSubmitRouteModal', function() {
                        submitRouteWithDetails(selectedIds);
                    });
                },
                error: function() {
                    alert('Failed to load pickup orders');
                }
            });
        });

        $('#settleRouteBtn').on('click', function() {
            var checked = costRouteGrid.getCheckedRows(0);
            if (!checked || checked.length === 0) {
                alert('Pilih setidaknya satu rute di tabel Manajemen Biaya');
                return;
            }

            var rowIndices = checked.split(',');
            var routeIds = [];
            for (var i = 0; i < rowIndices.length; i++) {
                routeIds.push(costRouteGrid.getRowAttribute(rowIndices[i], 'ID'));
            }

            $.ajax({
                url: 'partner-pickup-route-data?format=json',
                type: 'GET',
                data: {
                    id: routeIds.join(',')
                },
                success: function(res) {
                    var routes = res.data || [];
                    var summaryHtml = '<div style="max-height:200px;overflow-y:auto;" class="mb-3">';
                    routes.forEach(function(r) {
                        summaryHtml += '\
                        <div class="card mb-1 border-left-info">\
                            <div class="card-body py-1 px-3">\
                                <div class="d-flex justify-content-between">\
                                    <small><strong>' + r.Route_Code + '</strong></small>\
                                    <small>Jarak: ' + r.Distance_Km + ' km | Biaya: Rp ' + r.Total_Cost + '</small>\
                                </div>\
                            </div>\
                        </div>';
                    });
                    summaryHtml += '</div>';

                    var formHtml = summaryHtml +
                        '<h6 class="text-muted mb-3">Rincian Realisasi</h6>' +

                        '<div class="card mb-2 border-left-primary">' +
                        '<div class="card-header py-1"><strong>BBM</strong></div>' +
                        '<div class="card-body py-2">' +
                        '<div class="row">' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Nominal (Rp)</label><input type="number" class="form-control form-control-sm" id="settleBbmNominal" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Keterangan</label><input type="text" class="form-control form-control-sm" id="settleBbmKeterangan" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Upload File</label><div class="custom-file"><input type="file" class="custom-file-input" id="settleBbmFile" multiple /><label class="custom-file-label" for="settleBbmFile">Pilih file</label></div></div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        '<div class="card mb-2 border-left-success">' +
                        '<div class="card-header py-1"><strong>TOL</strong></div>' +
                        '<div class="card-body py-2">' +
                        '<div class="row">' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Nominal (Rp)</label><input type="number" class="form-control form-control-sm" id="settleTolNominal" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Keterangan</label><input type="text" class="form-control form-control-sm" id="settleTolKeterangan" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Upload File</label><div class="custom-file"><input type="file" class="custom-file-input" id="settleTolFile" multiple /><label class="custom-file-label" for="settleTolFile">Pilih file</label></div></div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        '<div class="card mb-2 border-left-warning">' +
                        '<div class="card-header py-1"><strong>Parkir</strong></div>' +
                        '<div class="card-body py-2">' +
                        '<div class="row">' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Nominal (Rp)</label><input type="number" class="form-control form-control-sm" id="settleParkirNominal" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Keterangan</label><input type="text" class="form-control form-control-sm" id="settleParkirKeterangan" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Upload File</label><div class="custom-file"><input type="file" class="custom-file-input" id="settleParkirFile" multiple /><label class="custom-file-label" for="settleParkirFile">Pilih file</label></div></div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        '<div class="card mb-2 border-left-info">' +
                        '<div class="card-header py-1"><strong>Operasional Driver</strong></div>' +
                        '<div class="card-body py-2">' +
                        '<div class="row">' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Nominal (Rp)</label><input type="number" class="form-control form-control-sm" id="settleOpDriverNominal" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Keterangan</label><input type="text" class="form-control form-control-sm" id="settleOpDriverKeterangan" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Upload File</label><div class="custom-file"><input type="file" class="custom-file-input" id="settleOpDriverFile" multiple /><label class="custom-file-label" for="settleOpDriverFile">Pilih file</label></div></div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        '<div class="card mb-3 border-left-secondary">' +
                        '<div class="card-header py-1"><strong>Operasional Lapangan</strong></div>' +
                        '<div class="card-body py-2">' +
                        '<div class="row">' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Nominal (Rp)</label><input type="number" class="form-control form-control-sm" id="settleOpLapanganNominal" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Keterangan</label><input type="text" class="form-control form-control-sm" id="settleOpLapanganKeterangan" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Upload File</label><div class="custom-file"><input type="file" class="custom-file-input" id="settleOpLapanganFile" multiple /><label class="custom-file-label" for="settleOpLapanganFile">Pilih file</label></div></div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        '<h6 class="text-muted mb-3 mt-4">Biaya non receipt / Tanpa nota lainnya</h6>' +
                        '<div class="card mb-3 border-left-danger">' +
                        '<div class="card-body py-2">' +
                        '<div class="row">' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Keterangan Biaya</label><input type="text" class="form-control form-control-sm" id="settleNonReceiptKeterangan" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Nominal (Rp)</label><input type="number" class="form-control form-control-sm" id="settleNonReceiptNominal" /></div>' +
                        '<div class="col-md-4 form-group mb-0"><label class="small">Upload File</label><div class="custom-file"><input type="file" class="custom-file-input" id="settleNonReceiptFile" multiple /><label class="custom-file-label" for="settleNonReceiptFile">Pilih file</label></div></div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                    AppModal.open({
                        title: 'Settlement Route',
                        size: 'xl',
                        body: formHtml,
                        actions: [{
                                label: 'Close',
                                class: 'btn-secondary'
                            },
                            {
                                label: 'Selesaikan Settlement',
                                class: 'btn-success',
                                id: 'btnSelesaikanSettlement',
                                onClick: function() {
                                    var fd = new FormData();
                                    fd.append('_token', '{{ csrf_token() }}');
                                    fd.append('route_ids', routeIds.join(','));

                                    // BBM
                                    fd.append('bbm_nominal', $('#settleBbmNominal').val());
                                    fd.append('bbm_keterangan', $('#settleBbmKeterangan').val());
                                    var bbmFiles = $('#settleBbmFile')[0].files;
                                    for (var fi = 0; fi < bbmFiles.length; fi++) fd.append('bbm_file[]', bbmFiles[fi]);

                                    // TOL
                                    fd.append('tol_nominal', $('#settleTolNominal').val());
                                    fd.append('tol_keterangan', $('#settleTolKeterangan').val());
                                    var tolFiles = $('#settleTolFile')[0].files;
                                    for (var fi = 0; fi < tolFiles.length; fi++) fd.append('tol_file[]', tolFiles[fi]);

                                    // Parkir
                                    fd.append('parkir_nominal', $('#settleParkirNominal').val());
                                    fd.append('parkir_keterangan', $('#settleParkirKeterangan').val());
                                    var parkirFiles = $('#settleParkirFile')[0].files;
                                    for (var fi = 0; fi < parkirFiles.length; fi++) fd.append('parkir_file[]', parkirFiles[fi]);

                                    // Operasional Driver
                                    fd.append('op_driver_nominal', $('#settleOpDriverNominal').val());
                                    fd.append('op_driver_keterangan', $('#settleOpDriverKeterangan').val());
                                    var driverFiles = $('#settleOpDriverFile')[0].files;
                                    for (var fi = 0; fi < driverFiles.length; fi++) fd.append('op_driver_file[]', driverFiles[fi]);

                                    // Operasional Lapangan
                                    fd.append('op_lapangan_nominal', $('#settleOpLapanganNominal').val());
                                    fd.append('op_lapangan_keterangan', $('#settleOpLapanganKeterangan').val());
                                    var lapanganFiles = $('#settleOpLapanganFile')[0].files;
                                    for (var fi = 0; fi < lapanganFiles.length; fi++) fd.append('op_lapangan_file[]', lapanganFiles[fi]);

                                    // Non receipt
                                    fd.append('non_receipt_keterangan', $('#settleNonReceiptKeterangan').val());
                                    fd.append('non_receipt_nominal', $('#settleNonReceiptNominal').val());
                                    var nonReceiptFiles = $('#settleNonReceiptFile')[0].files;
                                    for (var fi = 0; fi < nonReceiptFiles.length; fi++) fd.append('non_receipt_file[]', nonReceiptFiles[fi]);

                                    $.ajax({
                                        url: 'partner-pickup-finish-settlement',
                                        type: 'POST',
                                        data: fd,
                                        processData: false,
                                        contentType: false,
                                        success: function(res) {
                                            alert('Settlement berhasil disimpan!');
                                            AppModal.close();
                                        },
                                        error: function(xhr) {
                                            alert('Error: ' + ((xhr.responseJSON && xhr.responseJSON.message) || xhr.statusText));
                                        }
                                    });
                                    return false;
                                }
                            }
                        ]
                    });
                },
                error: function() {
                    alert('Failed to load route detail');
                }
            });
        });

        $(window).on('resize', function() {
            if (routingGrid) routingGrid.setSizes();
            if (costRouteGrid) costRouteGrid.setSizes();
        });
    });
</script>
@endsection