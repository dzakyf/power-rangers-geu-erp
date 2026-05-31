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
                <label for="dateFilter">Tanggal</label>
                <input type="date" id="dateFilter" class="form-control" />
            </div>
            <div class="col-md-2">
                <label for="fleetFilter">Driver</label>
                <select id="fleetFilter" class="form-control">
                    <option value="">-- Pilih Fleet --</option>
                </select>
            </div>
            <div class="col-md-2">
                <button id="filterBtn" class="btn btn-primary">Filter</button>
                <button id="routeBtn" class="btn btn-primary">Route</button>
            </div>
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

<!-- Table Card -->
<div class="card mb-3">
    <div class="card-header m-3">
        <h5>Routing List</h5>
    </div>
    <div class="card-body p-0">
        <div style="width:100%;overflow-x:auto;">
            <div id="routingGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<!-- Manajemen Biaya & Rute Card -->
<div class="card mb-3">
    <div class="card-header m-3">
        <h5>Manajemen biaya & rute</h5>
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
        routingGrid.load(`partner-pickup-route-data?format=xml`);

    }

    function initCostRouteGrid() {
        costRouteGrid = new dhtmlXGridObject('costRouteGrid');
        costRouteGrid.setImagePath("./dhtmlx/imgs/");
        costRouteGrid.setHeader("No, Rute, Biaya, Keterangan");
        costRouteGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter");
        costRouteGrid.setInitWidths("60,*,*,*");
        costRouteGrid.setColAlign("center,left,left,left");
        costRouteGrid.setColSorting("int,str,str,str");
        costRouteGrid.setColTypes("ron,ro,ro,ro");
        costRouteGrid.setSkin("dhx_skyblue");
        costRouteGrid.init();
        costRouteGrid.loadXMLString('<rows></rows>');
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
                    $select.append('<option value="' + item.warehouse_task_id + '">' +
                        item.warehouse_location + '</option>');
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
        var date = $('#dateFilter').val();

        var params = {};
        if (warehouse) params.wh_id = warehouse;
        if (date) params.date = date;

        $.ajax({
            url: 'partner-pickup-routing-data',
            type: 'GET',
            data: params,
            success: function(res) {
                // TODO: update grid and map markers with actual data endpoint
                routingGrid.clearAll();
                routingGrid.loadXMLString(res.data || '<rows></rows>');
            },
            error: function() {
                console.log('Failed to load routing data');
            }
        });
    }


    function submitRouteSubmit(orderIds) {
        var $btn = $('#submitRouteBtn');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting...');

        $.ajax({
            url: 'partner-pickup-route-submit',
            type: 'POST',
            data: {
                pickup_order_ids: orderIds,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
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
                    alert('Route berhasil disimpan! Distance: ' + data.distance_km + ' km, Duration: ' + data.duration_min + ' min');
                } else {
                    alert('Failed: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                alert('Error: ' + ((xhr.responseJSON && xhr.responseJSON.message) || xhr.statusText));
            },
            complete: function() {
                $btn.prop('disabled', false).html('Submit');
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
            console.log(checkedRows)
            if (!checkedRows || checkedRows.length === 0) {
                alert('Pilih setidaknya satu baris di tabel routing');
                return;
            }

            // var selectedIds = checkedRows.split(',');
            var selectedIds = [1, 6, 7];

            $.ajax({
                url: 'partner-pickup-route-data?format=json',
                type: 'GET',
                success: function(res) {
                    var orders = res.data || [];
                    var filteredOrders = orders.filter(function(o) {
                        return selectedIds.indexOf(String(o.ID)) !== -1;
                    });

                    var cardsHtml = '<div style="max-height:400px;overflow-y:auto;">';
                    orders.forEach(function(order, idx) {
                        cardsHtml += '\
                        <div class="card mb-2 border-left-primary">\
                            <div class="card-body py-2">\
                                <div class="d-flex justify-content-between">\
                                    <strong>' + order.POO_Name + '</strong>\
                                    <span class="badge badge-secondary">Qty: ' + order.Qty + '</span>\
                                </div>\
                                <div class="small text-muted">' + order.POO_Full_Address + '</div>\
                            </div>\
                        </div>';
                    });

                    if (filteredOrders.length === 0) {
                        cardsHtml += '<p class="text-center text-muted">No pickup orders found</p>';
                    }
                    cardsHtml += '</div>';

                    AppModal.open({
                        title: 'Route - Pickup Orders',
                        size: 'lg',
                        body: cardsHtml,
                        actions: [{
                            label: 'Submit',
                            class: 'btn-primary',
                            id: 'submitRouteBtn',
                            onClick: function() {
                                submitRouteSubmit(selectedIds);
                                return false;
                            }
                        }]
                    });
                },
                error: function() {
                    alert('Failed to load pickup orders');
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