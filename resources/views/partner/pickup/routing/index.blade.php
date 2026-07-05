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
                <h5>Daftar Surat Jalan</h5>
            </div>
            <div class="col-md-auto ml-auto p-2">
                <button id="routeBtn" class="btn btn-primary">Route</button>
                <button id="btnDetailDO" class="btn btn-info"><i class="fas fa-info-circle"></i> Detail Surat Jalan</button>
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

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/@mapbox/polyline@1.2.0/src/polyline.js"></script>

<script>
    var map;
    var routingGrid;
    var selectedDoId = null;
    var osrmRouteData = null;

    function initMap() {
        map = L.map('map').setView([-6.2, 106.8], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);
    }

    function initRoutingGrid() {
        routingGrid = new dhtmlXGridObject('routingGrid');
        routingGrid.setImagePath("./dhtmlx/imgs/");
        routingGrid.setHeader("No,DO Code, DO Date, Status, PO Count, Gudang");
        routingGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        routingGrid.setInitWidths("50,*,*,*,80,120");
        routingGrid.setColAlign("center,left,left,left,center,left");
        routingGrid.setColSorting("na,str,str,str,int,str");
        routingGrid.setColTypes("ron,ro,ro,ro,ro,ro");
        routingGrid.setSkin("dhx_skyblue");
        routingGrid.attachEvent("onRowSelect", function(id) {
            selectedDoId = routingGrid.getRowAttribute(id, "ID");
        });
        routingGrid.init();
        routingGrid.enableAutoWidth(true);
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
                    $select.append('<option value="' + item.id + '" data-lat="' + (item.lat || '') + '" data-lng="' + (item.lng || '') + '">' +
                        item.warehouse_location + ' - ' + item.warehouse_task_id + '</option>');
                });
            },
            error: function() {
                console.log('Failed to load warehouse options');
            }
        });
    }

    function loadRoutingData() {
        var warehouse = $('#warehouseFilter').val();
        var dateStart = $('#dateFilterStart').val();
        var dateEnd = $('#dateFilterEnd').val();

        var url = 'partner-pickup-do-route-data?format=xml';
        if (warehouse) url += '&whid=' + encodeURIComponent(warehouse);
        if (dateStart) url += '&date_start=' + encodeURIComponent(dateStart);
        if (dateEnd) url += '&date_end=' + encodeURIComponent(dateEnd);

        routingGrid.clearAll();
        routingGrid.load(url);
    }

    // --- Step navigation (unchanged) ---
    function goToStep1() {
        $('#routeStep1').show();
        $('#routeStep2').hide();
    }

    function goToStep2() {
        $('#routeStep1').hide();
        $('#routeStep2').show();
        recalcRouteCosts();
    }

    function formatRupiah(el) {
        var val = el.value.replace(/[^0-9]/g, '');
        el.value = val ? parseInt(val).toLocaleString('id-ID') : '';
    }

    function parseRupiah(val) {
        return parseInt((val || '0').replace(/\./g, '')) || 0;
    }

    // --- Recalculate cost cards (unchanged) ---
    function recalcRouteCosts() {
        var jarak = parseFloat($('#routeTotalDistance').val()) || 0;
        var konsumsi = parseFloat($('#routeBbmConsumption').val()) || 10;
        var harga = parseRupiah($('#routeBbmPrice').val()) || 13000;
        var tol = parseRupiah($('#routeTollEstimation').val()) || 0;

        if ($('#routeCostMethod').val() === 'lumpsum') {
            var lumpSum = parseRupiah($('#routeTotalLumpSum').val()) || 0;
            var apc = parseRupiah($('#routeApc').val()) || 0;
            var grandTotal = lumpSum + apc;
            $('#cardBbm, #cardTol, #cardOperasional, #cardApc').closest('.col-md-4').hide();
            $('#cardDistance, #cardGrandTotal').closest('.col-md-4').show();
            $('#cardBbm').text('Rp 0');
            $('#cardTol').text('Rp 0');
            $('#cardOperasional').text('Rp 0');
            $('#cardTotal').text('Rp 0');
            $('#cardDistance').text(jarak.toLocaleString('id-ID') + ' km');
            $('#cardApc').text('Rp 0');
            $('#cardGrandTotal').text('Rp ' + grandTotal.toLocaleString('id-ID'));
            return;
        }

        var bbm = (jarak / konsumsi) * harga;
        var parkir = parseRupiah($('#routeParkingCost').val()) || 0;
        var alkon = parseRupiah($('#routeAlkonBbmCost').val()) || 0;
        var opDriver = parseRupiah($('#routeDriverOperational').val()) || 0;
        var opLapangan = parseRupiah($('#routeFieldOperational').val()) || 0;
        var operasional = parkir + alkon + opDriver + opLapangan;
        var apc = parseRupiah($('#routeApc').val()) || 0;
        var total = bbm + tol + operasional;
        var grandTotal = total + apc;

        $('#cardBbm, #cardTol, #cardOperasional, #cardApc, #cardDistance, #cardGrandTotal').closest('.col-md-4').show();
        $('#cardBbm').text('Rp ' + bbm.toLocaleString('id-ID'));
        $('#cardTol').text('Rp ' + tol.toLocaleString('id-ID'));
        $('#cardOperasional').text('Rp ' + operasional.toLocaleString('id-ID'));
        $('#cardTotal').text('Rp ' + total.toLocaleString('id-ID'));
        $('#cardDistance').text(jarak.toLocaleString('id-ID') + ' km');
        $('#cardApc').text('Rp ' + apc.toLocaleString('id-ID'));
        $('#cardGrandTotal').text('Rp ' + grandTotal.toLocaleString('id-ID'));
    }

    function toggleCostMethod() {
        var v = $('#routeCostMethod').val();
        $('#costVariableFields').toggle(v === 'variable');
        $('#costLumpSumFields').toggle(v === 'lumpsum');
        $('#routeApc').closest('.form-group').toggle(v !== 'lumpsum');
        recalcRouteCosts();
    }

    // --- Fetch OSRM route from selected PO IDs (unchanged) ---
    function fetchOSRMRoute(orderIds) {
        var $btn = $('#btnGetRoute');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Calculating...');
        $.ajax({
            url: 'partner-pickup-route-calculate',
            type: 'POST',
            data: {
                pickup_order_ids: orderIds,
                warehouse_id: $('#warehouseFilter').val(),
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

    // --- Submit route with step 2 form data (unchanged) ---
    function submitRouteWithDetails(orderIds) {
        var payload = {
            pickup_order_ids: orderIds,
            warehouse_id: $('#warehouseFilter').val(),
            bbm_ratio: $('#routeBbmRatio').val(),
            bbm_consumption: $('#routeBbmConsumption').val(),
            bbm_price: $('#routeBbmPrice').val(),
            fleet_id: $('#routeFleet').val(),
            total_distance: $('#routeTotalDistance').val(),
            pickup_count: $('#routePickupCount').val(),
            total_weight: $('#routeTotalWeight').val(),
            toll_estimation: $('#routeTollEstimation').val(),
            parking_cost: $('#routeParkingCost').val(),
            alkon_bbm_cost: $('#routeAlkonBbmCost').val(),
            driver_operational: $('#routeDriverOperational').val(),
            field_operational: $('#routeFieldOperational').val(),
            cost_method: $('#routeCostMethod').val(),
            total_lump_sum: $('#routeTotalLumpSum').val(),
            apc: $('#routeApc').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        var jarak = parseFloat(payload.total_distance) || 0;
        var konsumsi = parseFloat(payload.bbm_consumption) || 10;
        var harga = parseRupiah(payload.bbm_price) || 13000;
        var tol = parseRupiah(payload.toll_estimation) || 0;

        if (payload.cost_method === 'lumpsum') {
            var lumpSum = parseRupiah(payload.total_lump_sum) || 0;
            var apc = parseRupiah(payload.apc) || 0;
            payload.subtotal_bbm = 0;
            payload.subtotal_toll = 0;
            payload.subtotal_operational = lumpSum;
            payload.total_cost = lumpSum + apc;
        } else {
            var bbm = (jarak / konsumsi) * harga;
            var parkir = parseRupiah(payload.parking_cost) || 0;
            var alkon = parseRupiah(payload.alkon_bbm_cost) || 0;
            var opDriver = parseRupiah(payload.driver_operational) || 0;
            var opLapangan = parseRupiah(payload.field_operational) || 0;
            var operasional = parkir + alkon + opDriver + opLapangan;
            var apc = parseRupiah(payload.apc) || 0;
            payload.subtotal_bbm = bbm;
            payload.subtotal_toll = tol;
            payload.subtotal_operational = operasional;
            payload.total_cost = bbm + tol + operasional;
        }

        $.ajax({
            url: 'partner-pickup-route-submit',
            type: 'POST',
            data: payload,
            success: function(res) {
                if (res.status === 'success') {
                    var data = res.data;
                    var decoded = polyline.decode(data.encoded_polyline);
                    var latlngs = decoded.map(function(p) { return [p[0], p[1]]; });
                    L.polyline(latlngs, { color: 'blue', weight: 5 }).addTo(map);
                    map.fitBounds(latlngs);

                    var whId = $('#warehouseFilter').val();
                    var whOption = $('#warehouseFilter option[value="' + whId + '"]');
                    var whLat = parseFloat(whOption.data('lat'));
                    var whLng = parseFloat(whOption.data('lng'));
                    if (whLat && whLng) {
                        var whIcon = L.divIcon({
                            className: 'warehouse-marker',
                            html: '<div style="background:#2c3e50;color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);">W</div>',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14]
                        });
                        L.marker([whLat, whLng], { icon: whIcon }).addTo(map)
                            .bindPopup('<b>Warehouse</b><br>' + whOption.text());
                    }
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

    // --- Build the route modal (Step 1 + Step 2) ---
    function openRouteModal(poIds, poOrders) {
        var cardsHtml = '<div style="max-height:250px;overflow-y:auto;">';
        poOrders.forEach(function(order) {
            cardsHtml += '<div class="card mb-2 border-left-primary">' +
                '<div class="card-body py-2">' +
                '<div class="d-flex justify-content-between">' +
                '<strong>' + (order.POO_Name || '') + '</strong>' +
                '<span class="badge badge-secondary">Qty: ' + (order.Qty || '0') + '</span>' +
                '</div>' +
                '<div class="small text-muted">' + (order.POO_Full_Address || '') + '</div>' +
                '</div>' +
                '</div>';
        });
        if (poOrders.length === 0) {
            cardsHtml += '<p class="text-center text-muted">No pickup orders found</p>';
        }
        cardsHtml += '</div>';

        var step1Html = '<div id="routeStep1">' +
            '<h6 class="text-muted mb-3">Step 1: Review Pickup Orders</h6>' +
            cardsHtml +
            '<div class="text-right mt-3">' +
            '<button type="button" class="btn btn-outline-primary mr-2" id="btnGetRoute">Get Route</button>' +
            '<button type="button" class="btn btn-primary" onclick="goToStep2()">Next &rarr;</button>' +
            '</div>' +
            '</div>';

        var step2Html = '<div id="routeStep2" style="display:none;">' +
            '<h6 class="text-muted mb-3">Step 2: Route Details</h6>' +
            '<div class="mb-3">' +
            '<label class="small font-weight-bold">Metode Biaya</label>' +
            '<select id="routeCostMethod" class="form-control form-control-sm" style="width:200px;" onchange="toggleCostMethod()">' +
            '<option value="variable">Hitung Manual</option>' +
            '<option value="lumpsum">Lump Sum</option>' +
            '</select>' +
            '</div>' +
            '<div id="costVariableFields">' +
            '<div class="card mb-3 border-left-info">' +
            '<div class="card-header py-2"><strong>Kendaraan &amp; BBM</strong></div>' +
            '<div class="card-body py-2">' +
            '<div class="row">' +
            '<div class="col-md-4 form-group mb-0">' +
            '<label class="small">BBM Ratio</label>' +
            '<select id="routeBbmRatio" class="form-control form-control-sm"><option value="ev">Mobil Listrik (1:10)</option></select>' +
            '</div>' +
            '<div class="col-md-4 form-group mb-0">' +
            '<label class="small">Konsumsi BBM</label>' +
            '<div class="input-group input-group-sm"><input type="number" id="routeBbmConsumption" class="form-control form-control-sm" value="10" disabled /><div class="input-group-append"><span class="input-group-text">L</span></div></div>' +
            '</div>' +
            '<div class="col-md-4 form-group mb-0">' +
            '<label class="small">Harga BBM</label>' +
            '<div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="text" id="routeBbmPrice" class="form-control form-control-sm" value="13.000" disabled /></div>' +
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
            '<div class="input-group input-group-sm"><input type="number" id="routeTotalDistance" class="form-control form-control-sm" oninput="recalcRouteCosts()" /><div class="input-group-append"><span class="input-group-text">Km</span></div></div>' +
            '</div>' +
            '<div class="col-md-3 form-group mb-0">' +
            '<label class="small">Jumlah Pickup</label>' +
            '<input type="number" id="routePickupCount" class="form-control form-control-sm" value="15" disabled />' +
            '</div>' +
            '<div class="col-md-3 form-group mb-0">' +
            '<label class="small">Total Berat (kg)</label>' +
            '<div class="input-group input-group-sm"><input type="number" id="routeTotalWeight" class="form-control form-control-sm" value="1000" disabled /><div class="input-group-append"><span class="input-group-text">Kg</span></div></div>' +
            '</div>' +
            '<div class="col-md-3 form-group mb-0">' +
            '<label class="small">Estimasi Tol</label>' +
            '<div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="text" id="routeTollEstimation" class="form-control form-control-sm" value="300.000" oninput="formatRupiah(this); recalcRouteCosts()" /></div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="card mb-3 border-left-info">' +
            '<div class="card-header py-2"><strong>Biaya Operasional</strong></div>' +
            '<div class="card-body py-2">' +
            '<div class="row" id="opsVariableFields">' +
            '<div class="col-md-3 form-group mb-0">' +
            '<label class="small">Biaya Parkir</label>' +
            '<input type="text" id="routeParkingCost" class="form-control form-control-sm" oninput="formatRupiah(this); recalcRouteCosts()" />' +
            '</div>' +
            '<div class="col-md-3 form-group mb-0">' +
            '<label class="small">BBM Alkon</label>' +
            '<input type="text" id="routeAlkonBbmCost" class="form-control form-control-sm" oninput="formatRupiah(this); recalcRouteCosts()" />' +
            '</div>' +
            '<div class="col-md-3 form-group mb-0">' +
            '<label class="small">Operasional Driver</label>' +
            '<input type="text" id="routeDriverOperational" class="form-control form-control-sm" oninput="formatRupiah(this); recalcRouteCosts()" />' +
            '</div>' +
            '<div class="col-md-3 form-group mb-0">' +
            '<label class="small">Operasional Lapangan</label>' +
            '<input type="text" id="routeFieldOperational" class="form-control form-control-sm" oninput="formatRupiah(this); recalcRouteCosts()" />' +
            '</div>' +
            '</div>' +
            '<div class="row" id="opsLumpSumFields" style="display:none;">' +
            '<div class="col-md-12 form-group mb-0">' +
            '<label class="small">Operasional Lump Sum (Rp)</label>' +
            '<div class="input-group input-group-sm">' +
            '<div class="input-group-prepend"><span class="input-group-text">Rp</span></div>' +
            '<input type="text" id="routeOpsLumpSum" class="form-control form-control-sm" value="0" oninput="formatRupiah(this); recalcRouteCosts()" />' +
            '</div>' +
            '<small class="text-muted">Mencakup: Parkir, BBM Alkon, Driver, Lapangan</small>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div id="costLumpSumFields" style="display:none;">' +
            '<div class="card mb-3 border-left-warning">' +
            '<div class="card-header py-2"><strong>Biaya Total (Lump Sum)</strong></div>' +
            '<div class="card-body py-2">' +
            '<div class="row">' +
            '<div class="col-md-6 form-group mb-0">' +
            '<label class="small">Total Lump Sum</label>' +
            '<div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="text" id="routeTotalLumpSum" class="form-control form-control-sm" value="0" oninput="formatRupiah(this); recalcRouteCosts()" /></div>' +
            '<small class="text-muted">Mencakup: BBM + Tol + Operasional + APC</small>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="form-group row">' +
            '<div class="col-md-3 ml-4">' +
            '<label class="small">APC (Administrative Processing Cost)</label>' +
            '<div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="text" id="routeApc" class="form-control form-control-sm" value="0" oninput="formatRupiah(this); recalcRouteCosts()" /></div>' +
            '</div>' +
            '</div>' +
            '<div class="card mb-3">' +
            '<div class="card-header py-2"><strong>Summary</strong></div>' +
            '<div class="card-body">' +
            '<div class="row">' +
            '<div class="col-md-4"><div class="card border-left-primary shadow-sm mb-2"><div class="card-body py-2 text-center"><small class="text-muted">BBM</small><h6 class="mb-0" id="cardBbm">Rp 0</h6></div></div></div>' +
            '<div class="col-md-4"><div class="card border-left-success shadow-sm mb-2"><div class="card-body py-2 text-center"><small class="text-muted">Tol</small><h6 class="mb-0" id="cardTol">Rp 0</h6></div></div></div>' +
            '<div class="col-md-4"><div class="card border-left-warning shadow-sm mb-2"><div class="card-body py-2 text-center"><small class="text-muted">Operasional</small><h6 class="mb-0" id="cardOperasional">Rp 0</h6></div></div></div>' +
            '</div>' +
            '<div class="row mt-2">' +
            '<div class="col-md-4"><div class="card border-left-secondary shadow-sm mb-2"><div class="card-body py-2 text-center"><small class="text-muted">Total Jarak</small><h6 class="mb-0" id="cardDistance">0 km</h6></div></div></div>' +
            '<div class="col-md-4"><div class="card border-left-info shadow-sm mb-2"><div class="card-body py-2 text-center"><small class="text-muted">APC</small><h6 class="mb-0" id="cardApc">Rp 0</h6></div></div></div>' +
            '<div class="col-md-4"><div class="card border-left-danger shadow-sm mb-2"><div class="card-body py-2 text-center"><small class="text-muted">Total Biaya Keseluruhan</small><h6 class="mb-0" id="cardGrandTotal">Rp 0</h6></div></div></div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="text-right mt-2">' +
            '<button type="button" class="btn btn-secondary" onclick="goToStep1()">&larr; Back</button> ' +
            '<button type="button" class="btn btn-primary" id="btnSubmitRouteModal">Submit Route</button>' +
            '</div>' +
            '</div>';

        AppModal.open({
            title: 'Route - Pickup Orders',
            size: 'xl',
            body: step1Html + step2Html,
            actions: []
        });

        $(document).off('click', '#btnGetRoute').on('click', '#btnGetRoute', function() {
            fetchOSRMRoute(poIds);
        });

        $(document).off('click', '#btnSubmitRouteModal').on('click', '#btnSubmitRouteModal', function() {
            submitRouteWithDetails(poIds);
        });
    }

    $(document).ready(function() {
        initMap();
        initRoutingGrid();
        loadWarehouseOptions();

        $('#filterBtn').on('click', function() {
            loadRoutingData();
        });

        // Initial load
        loadRoutingData();

        // --- Route button: resolve POs from selected DOs ---
        $('#routeBtn').on('click', function() {
            if (!selectedDoId) {
                alert('Pilih satu Surat Jalan terlebih dahulu');
                return;
            }
            var doIds = [selectedDoId];

            // Fetch all POs belonging to selected DOs
            $.get('partner-pickup-data', { format: 'json', delivery_order_id: doIds.join(',') }, function(res) {
                var orders = res.data || [];
                if (orders.length === 0) {
                    alert('Tidak ada Pickup Order pada Surat Jalan yang dipilih');
                    return;
                }

                var poIds = orders.map(function(o) { return o.ID; });

                // --- Reroute check ---
                $.get('partner-pickup-route-waypoints-data', { po_ids: poIds.join(',') }, function(res) {
                    if (res.routed) {
                        var codes = res.data.map(function(r) { return r.Route_Code; }).join(', ');
                        alert('Beberapa PO sudah dirutekan: ' + codes + '. Silakan pilih Surat Jalan lain.');
                        return;
                    }
                    openRouteModal(poIds, orders);
                });
            }).fail(function() {
                alert('Gagal memuat data Pickup Order');
            });
        });

        // --- Detail Surat Jalan button ---
        $('#btnDetailDO').on('click', function() {
            if (!selectedDoId) {
                alert('Pilih satu Surat Jalan terlebih dahulu');
                return;
            }
            var rowId = selectedDoId;
            var rowData = routingGrid.cells(rowId, 1).getValue();

            $.get('partner-pickup-data', { format: 'json', delivery_order_id: rowId }, function(res) {
                var pos = res.data || [];
                var bodyHtml = '';
                bodyHtml += '<div class="mb-3 p-3 bg-light rounded">';
                bodyHtml += '<h6 class="font-weight-bold">Surat Jalan: ' + rowData + '</h6>';
                bodyHtml += '</div>';
                bodyHtml += '<table class="table table-sm table-bordered">';
                bodyHtml += '<thead class="thead-light"><tr><th>No</th><th>PO Code</th><th>POO Name</th><th>Qty</th></tr></thead>';
                bodyHtml += '<tbody>';
                pos.forEach(function(po, i) {
                    bodyHtml += '<tr><td>' + (i + 1) + '</td><td>' + (po.Code || '-') + '</td><td>' + (po.POO_Name || '-') + '</td><td>' + (po.Qty || '0') + '</td></tr>';
                });
                bodyHtml += '</tbody></table>';

                AppModal.open({
                    title: 'Detail Surat Jalan',
                    size: 'lg',
                    body: bodyHtml,
                    actions: [{ label: 'Close', class: 'btn-secondary', onClick: function() { AppModal.close(); } }]
                });
            });
        });

        $(window).on('resize', function() {
            if (routingGrid) routingGrid.setSizes();
        });
    });
</script>
@endsection
