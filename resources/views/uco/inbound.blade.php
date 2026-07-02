@extends('layout.mainlayout-mitra')

@section('content-wrapper')

<div class="main-content-header">
    <h1>INVENTORY</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">Unused Crude Oil (UCO)</a></li>
        <li class="breadcrumb-item active"><span class="active">INSTOCK</span></li>
    </ol>
</div>

<!-- Delivery Order Grid -->
<div class="card mb-3">
    <div class="card-header m-3">
        <h5>Surat Jalan</h5>
    </div>
    <div class="card-body p-3">
        <div style="width:100%;overflow-x:auto;">
            <div id="DeliveryOrderGrid" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>
        </div>
        <div class="mt-3">
            <button class="btn btn-success" id="btnShowPO">Lihat PO</button>
            <button class="btn btn-info" id="btnUpdateInstock">Update Instock</button>
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
                <input type="button" value="Export Excel" class="btn btn-warning ml-2" onclick="PickupOrderGrid.toExcel('./export/generate.php');" />
            </div>
        </div>
    </div>
    <div class="card-body p-3">
        <div style="width:100%;overflow-x:auto;">
            <div id="PickupOrderGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

<!-- Dregs Grid -->
<div class="card mb-3">
    <div class="card-header m-3">
        <div class="row align-items-end">
            <div class="col-md-6">
                <h5>Ampas</h5>
            </div>
            <div class="col-md-auto ml-auto">
                <button class="btn btn-success" id="btnAddDregs">+ Add Ampas</button>
            </div>
        </div>
    </div>
    <div class="card-body p-3">
        <div style="width:100%;overflow-x:auto;">
            <div id="DregsGrid" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    var DeliveryOrderGrid;
    var PickupOrderGrid;
    var DregsGrid;
    var selectedDOId = null;
    var selectedPOId = null;

    $(document).ready(function() {
        initDeliveryOrderGrid();
        initPickupOrderGrid();
        initDregsGrid();

        $('#btnShowPO').on('click', function() {
            if (!selectedDOId) {
                alert('Pilih data delivery order terlebih dulu');
                return;
            }
            $('#poCard').show();
            loadPickupOrders(selectedDOId);
        });

        $('#btnUpdateInstock').on('click', function() {
            if (!selectedDOId) {
                alert('Pilih data delivery order terlebih dulu');
                return;
            }
            openUpdateModal();
        });

        $('#btnAddDregs').on('click', function() {
            openAddDregsModal();
        });

        $(window).on('resize', function() {
            if (DeliveryOrderGrid) DeliveryOrderGrid.setSizes();
            if (PickupOrderGrid) PickupOrderGrid.setSizes();
            if (DregsGrid) DregsGrid.setSizes();
        });
    });

    // === Delivery Order Grid ===
    function initDeliveryOrderGrid() {
        DeliveryOrderGrid = new dhtmlXGridObject('DeliveryOrderGrid');
        DeliveryOrderGrid.setImagePath("./dhtmlx/imgs/");
        DeliveryOrderGrid.setHeader("No,Code Surat Jalan,Tanggal Surat Jalan");
        DeliveryOrderGrid.attachHeader("#rspan,#text_filter,#text_filter");
        DeliveryOrderGrid.setInitWidths("60,*,*");
        DeliveryOrderGrid.setColAlign("center,left,left");
        DeliveryOrderGrid.setColTypes("ro,ro,ro");
        DeliveryOrderGrid.setSkin("dhx_skyblue");
        DeliveryOrderGrid.attachEvent("onRowSelect", function(id) {
            selectedDOId = DeliveryOrderGrid.getRowAttribute(id, 'ID');
        });
        DeliveryOrderGrid.init();
        DeliveryOrderGrid.load('partner-delivery-order-data?status=DONE&format=xml');
    }

    // === Pickup Order Grid ===
    function initPickupOrderGrid() {
        PickupOrderGrid = new dhtmlXGridObject('PickupOrderGrid');
        PickupOrderGrid.setImagePath("./dhtmlx/imgs/");
        PickupOrderGrid.setHeader("No,PO Code,POO Name,Qty,Unit (L),Percentage (%),Recommendation");
        PickupOrderGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#rspan");
        PickupOrderGrid.setInitWidths("50,120,*,80,60,100,120");
        PickupOrderGrid.setColAlign("center,left,left,right,center,right,left");
        PickupOrderGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
        PickupOrderGrid.setSkin("dhx_skyblue");
        PickupOrderGrid.attachEvent("onRowSelect", function(id) {
            selectedPOId = PickupOrderGrid.getRowAttribute(id, 'ID');
        });
        PickupOrderGrid.init();
    }

    function loadPickupOrders(doId) {
        if (!doId) return;
        selectedPOId = null;
        PickupOrderGrid.clearAll();
        PickupOrderGrid.load('partner-uco-instock-po-data?delivery_order_id=' + doId);
    }

    // === Dregs Grid ===
    function initDregsGrid() {
        DregsGrid = new dhtmlXGridObject('DregsGrid');
        DregsGrid.setImagePath("./dhtmlx/imgs/");
        DregsGrid.setHeader("No,Instock Date,Quantity,Notes,Dibuat Pada");
        DregsGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
        DregsGrid.setInitWidths("50,120,100,*,150");
        DregsGrid.setColAlign("center,left,right,left,left");
        DregsGrid.setColTypes("ro,ro,ro,ro,ro");
        DregsGrid.setSkin("dhx_skyblue");
        DregsGrid.init();
        loadDregsData();
    }

    function loadDregsData() {
        DregsGrid.clearAll();
        DregsGrid.load('partner-uco-instock-dregs-data');
    }

    // === Webcam helpers ===
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

    function appendPhotosToFormData(fd, capturedPhotos) {
        capturedPhotos.forEach(function(photo) {
            var base64 = photo.split(',')[1] || photo;
            fd.append('webcam_photos[]', base64);
        });
    }

    // === Update Instock Modal (SJ-level, multi-capture) ===
    var instockPhotos = [];

    function openUpdateModal() {
        instockPhotos = [];

        $.get('partner-delivery-order-data', {
            id: selectedDOId,
            format: 'json'
        }, function(res) {
            var doData = res.data && res.data[0] ? res.data[0] : {
                DO_Code: ''
            };

            var bodyHtml = '' +
                '<form id="instockUpdateForm">' +
                '<input type="hidden" name="do_id" value="' + selectedDOId + '">' +
                '<div class="text-center mb-3">' +
                '<div class="border rounded p-2 bg-light">' +
                '<video id="webcam" width="100%" autoplay playsinline style="max-height:200px;"></video>' +
                '<div class="mt-2">' +
                '<button type="button" class="btn btn-sm btn-primary" id="btnCapture">Capture</button>' +
                '</div>' +
                '</div>' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Captured Photos</label>' +
                '<div id="photoGallery" class="row"></div>' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Upload Image</label>' +
                '<input type="file" class="form-control" name="upload_photo" accept="image/*,.pdf">' +
                '</div>' +
                '<div class="form-group">' +
                '<label>Notes</label>' +
                '<textarea class="form-control" name="notes" rows="3"></textarea>' +
                '</div>' +
                '</form>';

            AppModal.open({
                title: 'Update Instock - ' + (doData.DO_Code || 'DO #' + selectedDOId),
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
                        appendPhotosToFormData(fd, instockPhotos);

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

            $('#appModal').one('shown.bs.modal', function() {
                startWebcam();
            });
        });
    }

    // === Add Dregs Modal (with multi-capture) ===
    var dregsPhotos = [];

    function openAddDregsModal() {
        dregsPhotos = [];
        var today = new Date().toISOString().slice(0, 10);

        var bodyHtml = '' +
            '<form id="dregsForm">' +
            '<div class="form-group">' +
            '<label>Instock Date</label>' +
            '<input type="date" class="form-control" name="Instock_Date" value="' + today + '" required>' +
            '</div>' +
            '<div class="form-group">' +
            '<label>Quantity</label>' +
            '<input type="number" step="0.01" class="form-control" name="Quantity" required>' +
            '</div>' +
            '<div class="form-group">' +
            '<label>Notes</label>' +
            '<textarea class="form-control" name="Notes" rows="3"></textarea>' +
            '</div>' +
            '<hr>' +
            '<div class="text-center mb-3">' +
            '<div class="border rounded p-2 bg-light">' +
            '<video id="webcam" width="100%" autoplay playsinline style="max-height:200px;"></video>' +
            '<div class="mt-2">' +
            '<button type="button" class="btn btn-sm btn-primary" id="btnCaptureDregs">Capture</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="form-group">' +
            '<label>Captured Photos</label>' +
            '<div id="dregsPhotoGallery" class="row"></div>' +
            '</div>' +
            '</form>';

        AppModal.open({
            title: 'Add Dregs',
            size: 'md',
            body: bodyHtml,
            actions: [{
                label: 'Cancel',
                class: 'btn-secondary'
            }, {
                label: 'Save',
                class: 'btn-primary',
                id: 'btnSaveDregs',
                onClick: function() {
                    var form = document.getElementById('dregsForm');
                    if (!form.Instock_Date.value || !form.Quantity.value) {
                        alert('Instock Date dan Quantity wajib diisi');
                        return false;
                    }
                    var fd = new FormData(form);
                    fd.append('_token', '{{ csrf_token() }}');
                    appendPhotosToFormData(fd, dregsPhotos);

                    $.ajax({
                        url: 'partner-uco-instock-dregs-submit',
                        type: 'POST',
                        data: fd,
                        processData: false,
                        contentType: false,
                        success: function(res) {
                            if (res.success) {
                                alert('Dregs berhasil disimpan');
                                AppModal.close();
                                loadDregsData();
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

        $('#appModal').one('shown.bs.modal', function() {
            startWebcam();
        });
    }

    // === Photo gallery rendering ===
    function renderGallery(containerId, photos) {
        var container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';
        photos.forEach(function(photo, index) {
            var col = document.createElement('div');
            col.className = 'col-4 col-md-3 mb-2';
            col.innerHTML = '<div class="position-relative" style="border:1px solid #ddd;border-radius:4px;overflow:hidden;">' +
                '<img src="' + photo + '" style="width:100%;height:120px;object-fit:cover;" />' +
                '<button type="button" class="btn btn-sm btn-danger position-absolute delete-photo-btn" style="top:2px;right:2px;padding:0 6px;" data-photo-index="' + index + '" data-container-id="' + containerId + '" data-arr-name="' + (containerId === 'photoGallery' ? 'instockPhotos' : 'dregsPhotos') + '">&times;</button>' +
                '</div>';
            container.appendChild(col);
        });
    }

    // Delete photo via event delegation
    $(document).on('click', '.delete-photo-btn', function() {
        var index = parseInt($(this).data('photo-index'));
        var containerId = $(this).data('container-id');
        var arrName = $(this).data('arr-name');
        var arr = window[arrName];
        if (!arr) return;
        arr.splice(index, 1);
        renderGallery(containerId, arr);
    });

    // Capture for instock modal
    $(document).on('click', '#btnCapture', function() {
        var video = document.getElementById('webcam');
        var canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        var dataUrl = canvas.toDataURL('image/png');
        instockPhotos.push(dataUrl);
        renderGallery('photoGallery', instockPhotos);
    });

    // Capture for dregs modal
    $(document).on('click', '#btnCaptureDregs', function() {
        var video = document.getElementById('webcam');
        var canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        var dataUrl = canvas.toDataURL('image/png');
        dregsPhotos.push(dataUrl);
        renderGallery('dregsPhotoGallery', dregsPhotos);
    });

    // Stop webcam when modal closes
    $(document).on('hidden.bs.modal', '#appModal', function() {
        stopWebcam();
    });
</script>
@endpush

@endsection