@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Finance</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Finance</a></li>
        <li class="breadcrumb-item"><a href="#">Operasional</a></li>
        <li class="breadcrumb-item active"><span class="active">Settlement Cost</span></li>
    </ol>
</div>

<div class="card mb-3">
    <div class="card-header m-3">
        <div class="row align-items-end">
            <div class="col-md-3">
                <h5>Manajemen Biaya & Route</h5>
            </div>
            <div class="col-md-auto ml-auto p-2">
                <button id="settleRouteBtn" class="btn btn-primary">Submit Settlement</button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div style="width:100%;overflow-x:auto;">
            <div id="costRouteGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<script>
    var costRouteGrid;
    var selectedRouteIds = [];

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

    $(document).ready(function() {
        initCostRouteGrid();

        $('#settleRouteBtn').on('click', function() {
            var checked = costRouteGrid.getCheckedRows(0);
            if (!checked || checked.length === 0) {
                alert('Pilih setidaknya satu rute di tabel');
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
                data: { id: routeIds.join(',') },
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
                                class: 'btn-secondary',
                            },
                            {
                                label: 'Submit Settlement',
                                class: 'btn-success',
                                id: 'btnSelesaikanSettlement',
                                onClick: function() {
                                    var fd = new FormData();
                                    fd.append('_token', '{{ csrf_token() }}');
                                    fd.append('action', 'submit');
                                    fd.append('route_ids', routeIds.join(','));

                                    fd.append('bbm_nominal', $('#settleBbmNominal').val());
                                    fd.append('bbm_keterangan', $('#settleBbmKeterangan').val());
                                    var bbmFiles = $('#settleBbmFile')[0].files;
                                    for (var fi = 0; fi < bbmFiles.length; fi++) fd.append('bbm_file[]', bbmFiles[fi]);

                                    fd.append('tol_nominal', $('#settleTolNominal').val());
                                    fd.append('tol_keterangan', $('#settleTolKeterangan').val());
                                    var tolFiles = $('#settleTolFile')[0].files;
                                    for (var fi = 0; fi < tolFiles.length; fi++) fd.append('tol_file[]', tolFiles[fi]);

                                    fd.append('parkir_nominal', $('#settleParkirNominal').val());
                                    fd.append('parkir_keterangan', $('#settleParkirKeterangan').val());
                                    var parkirFiles = $('#settleParkirFile')[0].files;
                                    for (var fi = 0; fi < parkirFiles.length; fi++) fd.append('parkir_file[]', parkirFiles[fi]);

                                    fd.append('op_driver_nominal', $('#settleOpDriverNominal').val());
                                    fd.append('op_driver_keterangan', $('#settleOpDriverKeterangan').val());
                                    var driverFiles = $('#settleOpDriverFile')[0].files;
                                    for (var fi = 0; fi < driverFiles.length; fi++) fd.append('op_driver_file[]', driverFiles[fi]);

                                    fd.append('op_lapangan_nominal', $('#settleOpLapanganNominal').val());
                                    fd.append('op_lapangan_keterangan', $('#settleOpLapanganKeterangan').val());
                                    var lapanganFiles = $('#settleOpLapanganFile')[0].files;
                                    for (var fi = 0; fi < lapanganFiles.length; fi++) fd.append('op_lapangan_file[]', lapanganFiles[fi]);

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
                                            alert('Settlement submitted, menunggu approval Finance');
                                            AppModal.close();
                                            costRouteGrid.load('partner-pickup-route-data?status=PENDING_SETTLEMENT&format=xml');
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
            if (costRouteGrid) costRouteGrid.setSizes();
        });
    });
</script>
@endsection
