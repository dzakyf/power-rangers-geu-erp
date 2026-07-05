@extends('layout.mainlayout-mitra')

@section('content-wrapper')

<div class="main-content-header">
    <h1>INVENTORY</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="./">Unused Crude Oil (UCO)</a></li>
        <li class="breadcrumb-item active"><span class="active">INSTOCK</span></li>
    </ol>
</div>

<ul class="nav nav-tabs mb-3" id="instockTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="instock-tab" data-toggle="tab" href="#instockTabContent" role="tab">Instock</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="ampas-tab" data-toggle="tab" href="#ampasTabContent" role="tab">Ampas</a>
    </li>
</ul>

<div class="tab-content" id="instockTabsContent">
    <div class="tab-pane fade show active" id="instockTabContent" role="tabpanel">
        <!-- Instock Supplies Grid -->
        <div class="card mb-3">
            <div class="card-header m-3">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <h5>Instock Supplies</h5>
                    </div>
                    <div class="col-md-auto ml-auto">
                        <button class="btn btn-success btn-sm" id="btnShowCreateForm">+ Tambah Data</button>
                        <button class="btn btn-danger btn-sm" id="btnDeleteSupply">Hapus</button>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <div style="width:100%;overflow-x:auto;">
                    <div id="SupplyGrid" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>
                </div>
            </div>
        </div>

        @include('uco.instock.create')

        <!-- Delivery Order Grid -->
        <div class="card mb-3" style="display:none;">
            <div class="card-header m-3">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <h5>Surat Jalan</h5>
                    </div>
                    <div class="col-md-auto ml-auto">
                        <select id="doStatusFilter" class="form-control form-control-sm" style="width:150px;">
                            <option value="">-- Semua Status --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <div style="width:100%;overflow-x:auto;">
                    <div id="DeliveryOrderGrid" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>
                </div>
                <div class="mt-3">
                    <button class="btn btn-success" id="btnShowPO">Lihat PO</button>
                    <!-- <button class="btn btn-info" id="btnUpdateInstock">Update Instock</button> -->
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
                        <input type="button" value="Update Rekom" class="btn btn-info ml-2" id="btnUpdateRekom" />
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <div style="width:100%;overflow-x:auto;">
                    <div id="PickupOrderGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="ampasTabContent" role="tabpanel">
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
    </div>
</div>

@push('scripts')
<script>
    var DeliveryOrderGrid;
    var PickupOrderGrid;
    var DregsGrid;
    var SupplyGrid;
    var selectedSupplyId = null;
    var selectedDOId = null;
    var selectedPOId = null;

    $(document).ready(function() {
        initSupplyGrid();
        initDeliveryOrderGrid();
        initPickupOrderGrid();
        initDregsGrid();

        $('#btnShowCreateForm').on('click', function() {
            $('#createInstockCard').slideToggle();
        });

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

        $('#btnDeleteSupply').on('click', function() {
            if (!selectedSupplyId) {
                alert('Pilih data instock terlebih dulu');
                return;
            }
            if (!confirm('Yakin ingin menghapus data ini?')) return;
            $.ajax({
                url: 'partner-uco-instock-supplies-delete',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    id: selectedSupplyId
                },
                success: function(res) {
                    if (res.success) {
                        alert('Data berhasil dihapus');
                        loadInstockSuppliesData();
                    } else {
                        alert('Gagal: ' + (res.message || 'Unknown error'));
                    }
                }
            });
        });

        $('#btnUpdateRekom').on('click', function() {
            if (!selectedDOId) {
                alert('Pilih data delivery order terlebih dulu');
                return;
            }
            openUpdateRekomModal();
        });

        $('#doStatusFilter').on('change', function() {
            var url = 'partner-delivery-order-data?format=xml';
            var status = $(this).val();
            if (status) url += '&status=' + status;
            DeliveryOrderGrid.clearAll();
            DeliveryOrderGrid.load(url);
        });

        $.get('master-delivery-order-status-list', function(res) {
            var $sel = $('#doStatusFilter');
            (res.data || []).forEach(function(s) {
                $sel.append('<option value="' + s + '">' + s + '</option>');
            });
        });

        $(window).on('resize', function() {
            if (SupplyGrid) SupplyGrid.setSizes();
            if (DeliveryOrderGrid) DeliveryOrderGrid.setSizes();
            if (PickupOrderGrid) PickupOrderGrid.setSizes();
            if (DregsGrid) DregsGrid.setSizes();
        });
    });

    // === Instock Supplies Grid ===
    function initSupplyGrid() {
        SupplyGrid = new dhtmlXGridObject('SupplyGrid');
        SupplyGrid.setImagePath("./dhtmlx/imgs/");
        SupplyGrid.setHeader("No,Tanggal,Source,Warehouse,Vol Diterima,Harga,Total,Reject");
        SupplyGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        SupplyGrid.setInitWidths("50,100,100,*,100,100,100,80");
        SupplyGrid.setColAlign("center,left,left,left,right,right,right,right");
        SupplyGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
        SupplyGrid.setSkin("dhx_skyblue");
        SupplyGrid.attachEvent("onRowSelect", function(id) {
            selectedSupplyId = SupplyGrid.getRowAttribute(id, 'ID');
        });
        SupplyGrid.init();
        loadInstockSuppliesData();
    }

    function loadInstockSuppliesData() {
        SupplyGrid.clearAll();
        SupplyGrid.load('partner-uco-instock-supplies-data');
    }

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
        DeliveryOrderGrid.load('partner-delivery-order-data?format=xml');
    }

    // === Pickup Order Grid ===
    function initPickupOrderGrid() {
        PickupOrderGrid = new dhtmlXGridObject('PickupOrderGrid');
        PickupOrderGrid.setImagePath("./dhtmlx/imgs/");
        PickupOrderGrid.setHeader("No,PO Code,POO Name,Qty,Unit (L),Percentage (%),Actual Qty,Recommendation");
        PickupOrderGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan");
        PickupOrderGrid.setInitWidths("50,120,*,80,60,100,80,120");
        PickupOrderGrid.setColAlign("center,left,left,right,center,right,right,right");
        PickupOrderGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
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

    // === Update Rekom Modal ===
    function openUpdateRekomModal() {
        $.get('partner-uco-instock-po-data', {
            delivery_order_id: selectedDOId,
            format: 'json'
        }, function(res) {
            var items = res.data || [];
            if (!items.length) {
                alert('Tidak ada data PO');
                return;
            }

            var bodyHtml = '<form id="rekomForm">';
            bodyHtml += '<table class="table table-sm table-bordered">';
            bodyHtml += '<thead><tr><th>PO Code</th><th>POO Name</th><th>Qty</th><th>Rekomendasi (Kg)</th></tr></thead>';
            bodyHtml += '<tbody>';
            items.forEach(function(item, idx) {
                var rec = item.Instock_Recommendation || item.Recommendation || 0;
                bodyHtml += '<tr>';
                bodyHtml += '<td>' + (item.PO_Code || '') + '</td>';
                bodyHtml += '<td>' + (item.POO_Name || '') + '</td>';
                bodyHtml += '<td>' + (item.Qty || 0) + '</td>';
                bodyHtml += '<td><input type="number" step="0.01" class="form-control form-control-sm rekom-input" name="recommendations[' + idx + '][recommendation]" value="' + rec + '">';
                bodyHtml += '<input type="hidden" name="recommendations[' + idx + '][po_id]" value="' + item.ID + '"></td>';
                bodyHtml += '</tr>';
            });
            bodyHtml += '</tbody></table>';
            bodyHtml += '</form>';

            AppModal.open({
                title: 'Update Rekomendasi',
                size: 'lg',
                body: bodyHtml,
                actions: [{
                    label: 'Close',
                    class: 'btn-secondary'
                }, {
                    label: 'Save',
                    class: 'btn-primary',
                    id: 'btnSaveRekom',
                    onClick: function() {
                        var fd = new FormData(document.getElementById('rekomForm'));
                        fd.append('_token', '{{ csrf_token() }}');

                        $.ajax({
                            url: 'partner-uco-instock-recommendation',
                            type: 'POST',
                            data: fd,
                            processData: false,
                            contentType: false,
                            success: function(res) {
                                if (res.success) {
                                    alert('Rekomendasi berhasil disimpan');
                                    AppModal.close();
                                    loadPickupOrders(selectedDOId);
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
            '<hr>' +
            '<div class="form-group">' +
            '<label>Surat Jalan</label>' +
            '<select class="form-control" id="dregsDO" style="width:100%;"></select>' +
            '</div>' +
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
            if (!$('#dregsDO').hasClass('select2-hidden-accessible')) {
                $('#dregsDO').select2({
                    placeholder: '-- Pilih Surat Jalan --',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: 'partner-delivery-order-data',
                        dataType: 'json',
                        delay: 250,
                        data: function(p) {
                            return { format: 'json', status: 'DONE', q: p.term || '' };
                        },
                        processResults: function(res) {
                            return { results: res.data.map(function(r) {
                                return { id: r.ID, text: r.DO_Code };
                            })};
                        }
                    }
                });
            }
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