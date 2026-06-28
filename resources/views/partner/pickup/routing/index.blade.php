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





<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/@mapbox/polyline@1.2.0/src/polyline.js"></script>

<script>
    var map;
    var routingGrid;

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
        recalcRouteCosts();
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

        // BBM (always variable)
        var bbm = (jarak / konsumsi) * harga;

        // Operasional (variable or lump sum)
        var operasional;
        if ($('#routeOpsMethod').val() === 'lumpsum') {
            operasional = parseFloat($('#routeOpsLumpSum').val()) || 0;
        } else {
            var parkir = parseFloat($('#routeParkingCost').val()) || 0;
            var alkon = parseFloat($('#routeAlkonBbmCost').val()) || 0;
            var opDriver = parseFloat($('#routeDriverOperational').val()) || 0;
            var opLapangan = parseFloat($('#routeFieldOperational').val()) || 0;
            operasional = parkir + alkon + opDriver + opLapangan;
        }

        var apc = parseFloat($('#routeApc').val()) || 0;
        var total = bbm + tol + operasional;
        var grandTotal = total + apc;

        // Existing cards
        $('#cardBbm').text('Rp ' + bbm.toLocaleString());
        $('#cardTol').text('Rp ' + tol.toLocaleString());
        $('#cardOperasional').text('Rp ' + operasional.toLocaleString());
        $('#cardTotal').text('Rp ' + total.toLocaleString());

        // New cards
        $('#cardDistance').text(jarak.toLocaleString() + ' km');
        $('#cardApc').text('Rp ' + apc.toLocaleString());
        $('#cardGrandTotal').text('Rp ' + grandTotal.toLocaleString());
    }

    function toggleOpsMethod() {
        var v = $('#routeOpsMethod').val();
        $('#opsVariableFields').toggle(v === 'variable');
        $('#opsLumpSumFields').toggle(v === 'lumpsum');
        recalcRouteCosts();
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

    // --- Submit route with step 2 form data ---
    function submitRouteWithDetails(orderIds) {
        var payload = {
            pickup_order_ids: orderIds,
            warehouse_id: $('#warehouseFilter').val(),
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
            ops_method: $('#routeOpsMethod').val(),
            ops_lump_sum: $('#routeOpsLumpSum').val(),
            apc: $('#routeApc').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        var jarak = parseFloat(payload.total_distance) || 0;
        var konsumsi = parseFloat(payload.bbm_consumption) || 10;
        var harga = parseFloat(payload.bbm_price) || 13000;
        var tol = parseFloat(payload.toll_estimation) || 0;

        // BBM (always variable)
        var bbm = (jarak / konsumsi) * harga;

        // Operasional (variable or lump sum)
        var operasional;
        if (payload.ops_method === 'lumpsum') {
            operasional = parseFloat(payload.ops_lump_sum) || 0;
        } else {
            var parkir = parseFloat(payload.parking_cost) || 0;
            var alkon = parseFloat(payload.alkon_bbm_cost) || 0;
            var opDriver = parseFloat(payload.driver_operational) || 0;
            var opLapangan = parseFloat(payload.field_operational) || 0;
            operasional = parkir + alkon + opDriver + opLapangan;
        }

        var apc = parseFloat(payload.apc) || 0;
        var total = bbm + tol + operasional;

        payload.subtotal_bbm = bbm;
        payload.subtotal_toll = tol;
        payload.subtotal_operational = operasional;
        payload.total_cost = total;

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

                    // Add warehouse start/end markers
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
                        L.marker([whLat, whLng], {
                                icon: whIcon
                            }).addTo(map)
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
    $(document).ready(function() {
        initMap();
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
                        '<div class="input-group input-group-sm"><input type="number" id="routeBbmConsumption" class="form-control form-control-sm" value="10" readonly /><div class="input-group-append"><span class="input-group-text">L</span></div></div>' +
                        '</div>' +
                        '<div class="col-md-4 form-group mb-0">' +
                        '<label class="small">Harga BBM</label>' +
                        '<div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" id="routeBbmPrice" class="form-control form-control-sm" value="13000" readonly /></div>' +
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
                        '<input type="number" id="routePickupCount" class="form-control form-control-sm" value="15" readonly />' +
                        '</div>' +
                        '<div class="col-md-3 form-group mb-0">' +
                        '<label class="small">Total Berat (kg)</label>' +
                        '<div class="input-group input-group-sm"><input type="number" id="routeTotalWeight" class="form-control form-control-sm" value="1000" readonly /><div class="input-group-append"><span class="input-group-text">Kg</span></div></div>' +
                        '</div>' +
                        '<div class="col-md-3 form-group mb-0">' +
                        '<label class="small">Estimasi Tol</label>' +
                        '<div class="input-group input-group-sm"><div class="input-group-prepend"><span class="input-group-text">Rp</span></div><input type="number" id="routeTollEstimation" class="form-control form-control-sm" value="300000" oninput="recalcRouteCosts()" /></div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        '<div class="card mb-3 border-left-info">' +
                        '<div class="card-header py-2 d-flex justify-content-between align-items-center">' +
                        '<strong>Biaya Operasional</strong>' +
                        '<select id="routeOpsMethod" class="form-control form-control-sm" style="width:auto;" onchange="toggleOpsMethod()">' +
                        '<option value="variable">Hitung Manual</option>' +
                        '<option value="lumpsum">Lump Sum</option>' +
                        '</select>' +
                        '</div>' +
                        '<div class="card-body py-2">' +
                        '<div class="row" id="opsVariableFields">' +
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
                        '<div class="row" id="opsLumpSumFields" style="display:none;">' +
                        '<div class="col-md-12 form-group mb-0">' +
                        '<label class="small">Operasional Lump Sum (Rp)</label>' +
                        '<div class="input-group input-group-sm">' +
                        '<div class="input-group-prepend"><span class="input-group-text">Rp</span></div>' +
                        '<input type="number" id="routeOpsLumpSum" class="form-control form-control-sm" value="0" oninput="recalcRouteCosts()" />' +
                        '</div>' +
                        '<small class="text-muted">Mencakup: Parkir, BBM Alkon, Driver, Lapangan</small>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        '<div class="form-group row">' +
                        '<div class="col-md-3 ml-4">' +
                        '<label class="small">APC (Administrative Processing Cost)</label>' +
                        '<div class="input-group input-group-sm">' +
                        '<div class="input-group-prepend"><span class="input-group-text">Rp</span></div>' +
                        '<input type="number" id="routeApc" class="form-control form-control-sm" value="0" oninput="recalcRouteCosts()" />' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        '<div class="card mb-3">' +
                        '<div class="card-header py-2"><strong>Summary</strong></div>' +
                        '<div class="card-body">' +
                        '<div class="row">' +
                        '<div class="col-md-4">' +
                        '<div class="card border-left-primary shadow-sm mb-2">' +
                        '<div class="card-body py-2 text-center">' +
                        '<small class="text-muted">BBM</small>' +
                        '<h6 class="mb-0" id="cardBbm">Rp 0</h6>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                        '<div class="card border-left-success shadow-sm mb-2">' +
                        '<div class="card-body py-2 text-center">' +
                        '<small class="text-muted">Tol</small>' +
                        '<h6 class="mb-0" id="cardTol">Rp 0</h6>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                        '<div class="card border-left-warning shadow-sm mb-2">' +
                        '<div class="card-body py-2 text-center">' +
                        '<small class="text-muted">Operasional</small>' +
                        '<h6 class="mb-0" id="cardOperasional">Rp 0</h6>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +

                        '<div class="row mt-2">' +
                        '<div class="col-md-4">' +
                        '<div class="card border-left-secondary shadow-sm mb-2">' +
                        '<div class="card-body py-2 text-center">' +
                        '<small class="text-muted">Total Jarak</small>' +
                        '<h6 class="mb-0" id="cardDistance">0 km</h6>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                        '<div class="card border-left-info shadow-sm mb-2">' +
                        '<div class="card-body py-2 text-center">' +
                        '<small class="text-muted">APC</small>' +
                        '<h6 class="mb-0" id="cardApc">Rp 0</h6>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '<div class="col-md-4">' +
                        '<div class="card border-left-danger shadow-sm mb-2">' +
                        '<div class="card-body py-2 text-center">' +
                        '<small class="text-muted">Total Biaya Keseluruhan</small>' +
                        '<h6 class="mb-0" id="cardGrandTotal">Rp 0</h6>' +
                        '</div>' +
                        '</div>' +
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

        $(window).on('resize', function() {
            if (routingGrid) routingGrid.setSizes();
        });
    });
</script>
@endsection