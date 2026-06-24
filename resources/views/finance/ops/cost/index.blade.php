@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Finance</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Finance</a></li>
        <li class="breadcrumb-item"><a href="#">Operasional</a></li>
        <li class="breadcrumb-item active"><span class="active">Cost</span></li>
    </ol>
</div>

<div class="mt-3">

    <div class="tab-content mt-3">
        <!-- Pickup Cost Tab -->
        <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">COST</div>
        <div class="card">
            <div class="card-body">
                <div id="pickupCostGrid" style="width:100%;height:320px;background-color:white;overflow-x:auto"></div>
                <div class="mt-3">
                    <button type="button" class="btn btn-info" id="btnShowMap"><i class="fas fa-map mr-1"></i>Show Map</button>
                    <button type="button" class="btn btn-success" id="btnTopup">
                        <i class="fas fa-plus mr-1"></i>Topup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    var pickupCostGrid;
    var selectedRouteId = null;

    function initPickupCostGrid() {
        pickupCostGrid = new dhtmlXGridObject('pickupCostGrid');
        pickupCostGrid.setImagePath("./dhtmlx/imgs/");
        pickupCostGrid.setHeader("No,Route Code, PO Code, Nominal, Status, Dibuat Pada");
        pickupCostGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        pickupCostGrid.setInitWidths("60,*,*,*,*,*");
        pickupCostGrid.setColAlign("center,left,left,right,left, left");
        pickupCostGrid.setColSorting("int,str,str,str,str,str");
        pickupCostGrid.setColTypes("ron,ro,ro,ro,ro,ro");
        pickupCostGrid.setSkin("dhx_skyblue");

        pickupCostGrid.attachEvent("onRowSelect", function(id) {
            selectedRouteId = pickupCostGrid.getRowAttribute(id, "ID");
        });
        pickupCostGrid.init();
        pickupCostGrid.load('partner-pickup-route-data?format=xml');
    }

    $(document).ready(function() {
        initPickupCostGrid();

        $('#btnTopup').on('click', function() {
            var formHtml = '' +
                '<form id="topupForm" enctype="multipart/form-data">' +
                '<div class="form-group">' +
                '<label>Saldo Kas Besar</label>' +
                '<input type="text" class="form-control" value="Rp 300,000" disabled />' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Pilih Rekening</label>' +
                '<select class="form-control" id="topupRekening" name="rekening">' +
                '<option value="">-- Pilih --</option>' +
                '<option value="BCA">BCA</option>' +
                '<option value="MANDIRI">Mandiri</option>' +
                '<option value="BRI">BRI</option>' +
                '</select>' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Nominal</label>' +
                '<input type="number" class="form-control" id="topupNominal" name="nominal" placeholder="Masukkan nominal" />' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Bukti Transfer</label>' +
                '<div class="custom-file">' +
                '<input type="file" class="custom-file-input" id="topupFile" name="bukti_transfer" accept="image/*,.pdf" />' +
                '<label class="custom-file-label" for="topupFile">Choose file...</label>' +
                '</div>' +
                '</div>' +
                '</form>';

            AppModal.open({
                title: 'Topup Kas Besar',
                size: 'md',
                body: formHtml,
                actions: [{
                        label: 'Close',
                        class: 'btn-secondary'
                    },
                    {
                        label: 'Submit',
                        class: 'btn-primary',
                        id: 'btnSubmitTopup',
                        onClick: function() {
                            var formData = new FormData(document.getElementById('topupForm'));
                            formData.append('_token', '{{ csrf_token() }}');
                            formData.append('id', selectedRouteId);
                            $.ajax({
                                url: 'finance-cost-approve-route',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(res) {
                                    alert('Topup berhasil!');
                                    AppModal.close();
                                },
                                error: function(xhr) {
                                    alert('Error: ' + (xhr.responseJSON ? xhr.responseJSON.message : 'Gagal'));
                                }
                            });
                            return false;
                        }
                    }
                ]
            });

            $('#topupFile').on('change', function() {
                var fileName = this.files[0] ? this.files[0].name : 'Choose file...';
                $(this).next('.custom-file-label').text(fileName);
            });
        });
    });

        $('#btnShowMap').on('click', function() {
            if (!selectedRouteId) { alert('Pilih data route terlebih dulu'); return; }

            loadLeafletAssets(function() {
                $.get('partner-pickup-route-map-data', { route_id: selectedRouteId }, function(res) {
                    var d = res.data;
                    if (!d || !d.route) { alert('Data route tidak ditemukan'); return; }

                    var bodyHtml = '<div id="routeMapModalMap" style="height:450px;"></div>' +
                        '<div class="mt-2"><strong>Route:</strong> ' + (d.route.Code || '-') +
                        ' | <strong>Jarak:</strong> ' + (d.route.Distance_Km || '-') + ' km' +
                        ' | <strong>Durasi:</strong> ' + (d.route.Duration_Min || '-') + ' min</div>';

                    AppModal.open({
                        title: 'Route Map',
                        size: 'xl',
                        body: bodyHtml,
                        actions: [{ label: 'Close', class: 'btn-secondary' }]
                    });

                    setTimeout(function() {
                        var map = L.map('routeMapModalMap').setView([-6.2, 106.8], 12);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; OpenStreetMap contributors'
                        }).addTo(map);

                        if (d.route.Encoded_Polyline) {
                            var decoded = polyline.decode(d.route.Encoded_Polyline);
                            var latlngs = decoded.map(function(p) { return [p[0], p[1]]; });
                            var polylineLayer = L.polyline(latlngs, { color: 'blue', weight: 5 }).addTo(map);
                            map.fitBounds(polylineLayer.getBounds().pad(0.1));
                        }

                        if (d.waypoints) {
                            d.waypoints.forEach(function(wp) {
                                if (wp.Lat && wp.Lng) {
                                    L.marker([parseFloat(wp.Lat), parseFloat(wp.Lng)])
                                        .addTo(map)
                                        .bindPopup('<b>' + (wp.POO_Name || 'PO #' + wp.PO_Id) + '</b>');
                                }
                            });
                        }

                        if (d.warehouse && d.warehouse.lat && d.warehouse.lng) {
                            var whIcon = L.divIcon({
                                className: 'warehouse-marker',
                                html: '<div style="background:#2c3e50;color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:bold;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);">W</div>',
                                iconSize: [28, 28],
                                iconAnchor: [14, 14]
                            });
                            L.marker([parseFloat(d.warehouse.lat), parseFloat(d.warehouse.lng)], { icon: whIcon })
                                .addTo(map)
                                .bindPopup('<b>' + (d.warehouse.warehouse_task_id || 'Warehouse') + '</b><br>' + (d.warehouse.warehouse_location || ''));
                        }
                    }, 300);
                });
            });
        });

        function loadLeafletAssets(callback) {
            if (typeof L !== 'undefined' && typeof polyline !== 'undefined') { callback(); return; }
            var link = document.createElement('link');
            link.rel = 'stylesheet'; link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(link);
            var script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = function() {
                var poly = document.createElement('script');
                poly.src = 'https://unpkg.com/@mapbox/polyline@1.2.0/src/polyline.js';
                poly.onload = callback;
                document.head.appendChild(poly);
            };
            document.head.appendChild(script);
        }
</script>
@endsection