@extends('layout.mainlayout-finance')

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
                    <button type="button" class="btn btn-primary" id="btnDetail">
                        <i class="fas fa-info-circle mr-1"></i>Detail
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
        pickupCostGrid.setHeader("No,Route Code, PO Code, Planned Nominal, Status, Dibuat Pada");
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
                        class: 'btn-secondary',
                        onClick: function() {
                            AppModal.close();
                        }
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

        $('#btnDetail').on('click', function() {
            if (!selectedRouteId) {
                alert('Pilih data route terlebih dulu');
                return;
            }

            $.get('finance-pickup-route-detail', {
                route_id: selectedRouteId
            }, function(res) {
                if (res.status !== 'success' || !res.data) {
                    alert('Data tidak ditemukan');
                    return;
                }

                var d = res.data;
                var route = d.route;
                var deliveryOrders = d.delivery_orders || [];

                var bodyHtml = '';

                bodyHtml += '<div class="mb-3 p-3 bg-light rounded">';
                bodyHtml += '<div class="row">';
                bodyHtml += '<div class="col-md-4"><strong>Route Code:</strong> ' + (route.code || '-') + '</div>';
                bodyHtml += '<div class="col-md-4"><strong>Status:</strong> <span class="badge badge-info">' + (route.status || '-') + '</span></div>';
                bodyHtml += '<div class="col-md-4"><strong>Planned Nominal:</strong> Rp ' + (route.total_cost ? Number(route.total_cost).toLocaleString() : '0') + '</div>';
                bodyHtml += '<div class="col-md-4"><strong>Actual Nominal:</strong> Rp ' + (route.transfer_nominal ? Number(route.transfer_nominal).toLocaleString() : '0') + '</div>';
                bodyHtml += '</div></div>';

                bodyHtml += '<h6 class="font-weight-bold mb-2">Delivery Order &amp; Pickup Orders</h6>';
                bodyHtml += '<table class="table table-sm table-bordered">';
                bodyHtml += '<thead class="thead-light"><tr><th style="width:30px;"></th><th>DO Code</th><th>PO Code</th><th>POO Name</th><th>Qty</th><th>Allocation Type</th></tr></thead>';
                bodyHtml += '<tbody>';

                deliveryOrders.forEach(function(doGroup, doIdx) {
                    var pos = doGroup.pickup_orders || [];
                    var rowspan = pos.length || 1;

                    pos.forEach(function(po, poIdx) {
                        bodyHtml += '<tr>';
                        if (poIdx === 0) {
                            bodyHtml += '<td rowspan="' + rowspan + '" class="align-middle text-center">' + (doIdx + 1) + '</td>';
                            bodyHtml += '<td rowspan="' + rowspan + '" class="align-middle"><strong>' + (doGroup.do_code || '-') + '</strong></td>';
                        }
                        bodyHtml += '<td>' + (po.po_code || '-') + '</td>';
                        bodyHtml += '<td>' + (po.poo_name || '-') + '</td>';
                        bodyHtml += '<td>' + (po.qty || '0') + '</td>';
                        var at = po.allocation_type || 'NOT SET';
                        var badgeClass = at === 'LUMPSUM' ? 'badge-warning' : at === 'MANUAL' ? 'badge-primary' : 'badge-secondary';
                        bodyHtml += '<td><span class="badge ' + badgeClass + '">' + at + '</span></td>';
                        bodyHtml += '</tr>';
                    });
                });

                bodyHtml += '</tbody></table>';

                AppModal.open({
                    title: 'Route Detail - ' + (route.code || ''),
                    size: 'xl',
                    body: bodyHtml,
                    actions: [{
                        label: 'Close',
                        class: 'btn-secondary',
                        onClick: function() {
                            AppModal.close();
                        }
                    }]
                });
            });
        });
    });

    $('#btnShowMap').on('click', function() {
        if (!selectedRouteId) {
            alert('Pilih data route terlebih dulu');
            return;
        }

        loadLeafletAssets(function() {
            $.get('partner-pickup-route-map-data', {
                route_id: selectedRouteId
            }, function(res) {
                var d = res.data;
                if (!d || !d.route) {
                    alert('Data route tidak ditemukan');
                    return;
                }

                var bodyHtml = '<div id="routeMapModalMap" style="height:450px;"></div>' +
                    '<div class="mt-2"><strong>Route:</strong> ' + (d.route.Code || '-') +
                    ' | <strong>Jarak:</strong> ' + (d.route.Distance_Km || '-') + ' km' +
                    ' | <strong>Durasi:</strong> ' + (d.route.Duration_Min || '-') + ' min</div>';

                AppModal.open({
                    title: 'Route Map',
                    size: 'xl',
                    body: bodyHtml,
                    actions: [{
                        label: 'Close',
                        class: 'btn-secondary',
                        onClick: function() {
                            AppModal.close();
                        }
                    }]
                });

                setTimeout(function() {
                    var map = L.map('routeMapModalMap').setView([-6.2, 106.8], 12);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);

                    if (d.route.Encoded_Polyline) {
                        var decoded = polyline.decode(d.route.Encoded_Polyline);
                        var latlngs = decoded.map(function(p) {
                            return [p[0], p[1]];
                        });
                        var polylineLayer = L.polyline(latlngs, {
                            color: 'blue',
                            weight: 5
                        }).addTo(map);
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
                        L.marker([parseFloat(d.warehouse.lat), parseFloat(d.warehouse.lng)], {
                                icon: whIcon
                            })
                            .addTo(map)
                            .bindPopup('<b>' + (d.warehouse.warehouse_task_id || 'Warehouse') + '</b><br>' + (d.warehouse.warehouse_location || ''));
                    }
                }, 300);
            });
        });
    });

    function loadLeafletAssets(callback) {
        if (typeof L !== 'undefined' && typeof polyline !== 'undefined') {
            callback();
            return;
        }
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
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