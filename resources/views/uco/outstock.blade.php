@extends('layout.mainlayout-bp')

@section('content-wrapper')

<div class="main-content-header">
    <h1>INVENTORY</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">Unused Crude Oil (UCO)</a></li>
        <li class="breadcrumb-item active"><span class="active">OUTSTOCK</span></li>
    </ol>
</div>

<!-- Delivery Order Grid -->
<div class="card mb-3">
    <div class="card-header m-3">
        <h5>Outstock</h5>
    </div>
    <div class="card-body p-3">
        <div style="width:100%;overflow-x:auto;">
            <div id="DeliveryOrderGrid" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>

        </div>
        <div class="mt-3">
            <input type="button" value="Export Excel" class="btn btn-warning ml-2" onclick="DeliveryOrderGrid.toExcel('./export/generate.php');" />
        </div>
    </div>
</div>

<!-- Pickup Order Grid -->
<div class="card mb-3" id="poCard" style="display:none;">
    <div class="card-header m-3">
        <div class="row align-items-end">
            <div class="col-md-6">
                <h5>Pickup Order</h5>
            </div>
            <div class="col-md-auto ml-auto">
                <input type="button" id="btnUpdateInstock" value="Update" class="btn btn-info" />
            </div>
        </div>
    </div>
    <div class="card-body p-3">
        <div style="width:100%;overflow-x:auto;">
            <div id="PickupOrderGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    var DeliveryOrderGrid;
    var PickupOrderGrid;
    var selectedDOId = null;
    var selectedPOId = null;

    $(document).ready(function() {
        initDeliveryOrderGrid();
        initPickupOrderGrid();

        $('#btnShowPO').on('click', function() {
            if (!selectedDOId) {
                alert('Pilih data delivery order terlebih dulu');
                return;
            }
            $('#poCard').show();
            loadPickupOrders(selectedDOId);
        });

        $('#btnUpdateInstock').on('click', function() {
            if (!selectedPOId) {
                alert('Pilih data pickup order terlebih dulu');
                return;
            }
            openUpdateModal();
        });

        $(window).on('resize', function() {
            if (DeliveryOrderGrid) DeliveryOrderGrid.setSizes();
            if (PickupOrderGrid) PickupOrderGrid.setSizes();
        });
    });

    // === Delivery Order Grid ===
    function initDeliveryOrderGrid() {
        DeliveryOrderGrid = new dhtmlXGridObject('DeliveryOrderGrid');
        DeliveryOrderGrid.setImagePath("./dhtmlx/imgs/");
        DeliveryOrderGrid.setHeader("No,Code ,Tanggal, Qty Out, Qty Order, Dibuat Pada");
        DeliveryOrderGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        DeliveryOrderGrid.setInitWidths("60,*,*");
        DeliveryOrderGrid.setColAlign("center,left,left");
        DeliveryOrderGrid.setColTypes("ro,ro,ro");
        DeliveryOrderGrid.setSkin("dhx_skyblue");
        DeliveryOrderGrid.attachEvent("onRowSelect", function(id) {
            selectedDOId = DeliveryOrderGrid.getRowAttribute(id, 'ID');
        });
        DeliveryOrderGrid.init();
        // DeliveryOrderGrid.load('partner-delivery-order-data?status=DONE&format=xml');
    }

    // === Pickup Order Grid ===
    function initPickupOrderGrid() {
        PickupOrderGrid = new dhtmlXGridObject('PickupOrderGrid');
        PickupOrderGrid.setImagePath("./dhtmlx/imgs/");
        PickupOrderGrid.setHeader("No,Code,POO Name,Weight-in,Unit,Picker,DO Date");
        PickupOrderGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter");
        PickupOrderGrid.setInitWidths("60,120,*,100,60,100,120");
        PickupOrderGrid.setColAlign("center,center,left,center,center,center,left");
        PickupOrderGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
        PickupOrderGrid.setSkin("dhx_skyblue");
        PickupOrderGrid.attachEvent("onRowSelect", function(id) {
            selectedPOId = PickupOrderGrid.getRowAttribute(id, 'ID');
        });
        PickupOrderGrid.init();
        // PickupOrderGrid.load('partner-pickup-order-data?status=DONE');
    }

    function loadPickupOrders(doId) {
        if (!doId) return;
        selectedPOId = null;
        PickupOrderGrid.clearAll();
        PickupOrderGrid.load('partner-pickup-order-data?status=DONE&delivery_order_id=' + doId);
    }

    // === Update Modal ===
    function openUpdateModal() {
        $.get('partner-pickup-order-data', {
            id: selectedPOId
        }, function(res) {
            var data = res.data && res.data[0] ? res.data[0] : {
                PO_Code: ''
            };

            var bodyHtml = '' +
                '<form id="instockUpdateForm">' +
                '<input type="hidden" name="po_id" value="' + selectedPOId + '">' +
                '<div class="text-center mb-3">' +
                '<div class="border rounded p-2 bg-light">' +
                '<video id="webcam" width="100%" autoplay playsinline style="max-height:200px;"></video>' +
                '<div class="mt-2">' +
                '<button type="button" class="btn btn-sm btn-primary" id="btnCapture">Capture</button>' +
                '</div>' +
                '<canvas id="webcamCanvas" style="display:none;max-width:100%;max-height:150px;" class="mt-2"></canvas>' +
                '<input type="hidden" name="webcam_photo" id="webcamPhoto">' +
                '</div>' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Upload Image</label>' +
                '<input type="file" class="form-control" name="upload_photo" accept="image/*,.pdf">' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Notes</label>' +
                '<textarea class="form-control" name="notes" rows="3">' + (data.Description || '') + '</textarea>' +
                '</div>' +
                '</form>';

            AppModal.open({
                title: 'Update Instock - ' + (data.PO_Code || 'PO #' + selectedPOId),
                size: 'md',
                body: bodyHtml,
                actions: [{
                    label: 'Close',
                    class: 'btn-secondary'
                }, {
                    label: 'Submit',
                    class: 'btn-primary',
                    id: 'btnSubmitInstock',
                    onClick: function() {
                        var form = document.getElementById('instockUpdateForm');
                        var fd = new FormData(form);
                        fd.append('_token', '{{ csrf_token() }}');

                        $.ajax({
                            url: 'partner-uco-instock-update',
                            type: 'POST',
                            data: fd,
                            processData: false,
                            contentType: false,
                            success: function(res) {
                                if (res.success) {
                                    alert('Data berhasil diupdate');
                                    AppModal.close();
                                    if (selectedDOId) {
                                        loadPickupOrders(selectedDOId);
                                    } else {
                                        PickupOrderGrid.load('partner-pickup-order-data?status=DONE');
                                    }
                                } else {
                                    alert('Gagal: ' + (res.message || 'Unknown error'));
                                }
                            },
                            error: function(xhr) {
                                alert(xhr.responseJSON ? xhr.responseJSON.message : 'Error Internal!');
                            }
                        });
                        return false;
                    }
                }]
            });

            // Start webcam after modal opens
            $('#appModal').one('shown.bs.modal', function() {
                startWebcam();
            });
        });
    }

    // === Webcam ===
    var webcamStream = null;

    function startWebcam() {
        var video = document.getElementById('webcam');
        if (!video) return;

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({
                    video: true
                })
                .then(function(stream) {
                    webcamStream = stream;
                    video.srcObject = stream;
                })
                .catch(function(err) {
                    console.log('Webcam error:', err);
                    video.style.display = 'none';
                });
        }
    }

    function stopWebcam() {
        if (webcamStream) {
            webcamStream.getTracks().forEach(function(track) {
                track.stop();
            });
            webcamStream = null;
        }
    }

    $(document).on('click', '#btnCapture', function() {
        var video = document.getElementById('webcam');
        var canvas = document.getElementById('webcamCanvas');
        var ctx = canvas.getContext('2d');

        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        var dataUrl = canvas.toDataURL('image/png');
        var base64 = dataUrl.split(',')[1];

        $('#webcamPhoto').val(base64);
        canvas.style.display = 'block';
        $('#btnCapture').text('Retake');
    });

    // Stop webcam when modal closes
    $(document).on('hidden.bs.modal', '#appModal', function() {
        stopWebcam();
    });
</script>
@endpush

@endsection