@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Finance</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Finance</a></li>
        <li class="breadcrumb-item"><a href="#">Operasional</a></li>
        <li class="breadcrumb-item active"><span class="active">Acquisition Cost</span></li>
    </ol>
</div>

<div class="card mb-3">
    <div class="card-header m-3">
        <div class="row align-items-end">
            <div class="col-md-3">
                <h5>Acquisition Cost</h5>
            </div>
            <div class="col-md-auto ml-auto">
                <select id="statusFilter" class="form-control form-control-sm" style="width:200px;" onchange="applyFilter()">
                    <option value="">-- Semua Status --</option>
                    <option value="NOT_SUBMITTED">Not Submitted</option>
                    <option value="PENDING">Pending</option>
                    <option value="APPROVED">Approved</option>
                    <option value="REJECTED">Rejected</option>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body p-0 ml-3 mr-3">
        <div style="width:100%;overflow-x:auto;">
            <div id="acquisitionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        </div>
    </div>
    <div class="text-left ml-3 mt-3 mb-3 mr-3">
        <button class="btn btn-primary" onclick="openUploadBuktiTransferModal()">
            <i class="fa fa-upload"></i> Upload Bukti Transfer
        </button>
        <button class="btn btn-info" onclick="openDetailModal()">
            <i class="fa fa-info-circle"></i> Detail
        </button>
    </div>

</div>


<script>
    var acquisitionGrid;
    var selectedPoId = null;

    function initAcquisitionGrid() {
        acquisitionGrid = new dhtmlXGridObject('acquisitionGrid');
        acquisitionGrid.setImagePath("./dhtmlx/imgs/");
        acquisitionGrid.setHeader("No,DO Code, PO Code, POO Name, Qty, Expected Cost, Actual Cost, Status, Submitted At");
        acquisitionGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan");
        acquisitionGrid.setInitWidths("50,100,120,*,80,120,120,100,150");
        acquisitionGrid.setColAlign("center,left,left,left,right,right,right,center,left");
        acquisitionGrid.setColSorting("int,str,str,str,int,str,str,str,str");
        acquisitionGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
        acquisitionGrid.setSkin("dhx_skyblue");
        acquisitionGrid.attachEvent("onRowDblClick", function(id) {
            selectedPoId = acquisitionGrid.getRowAttribute(id, 'ID');
            openAcquisitionModal(id);
        });
        acquisitionGrid.attachEvent("onRowSelect", function(id) {
            selectedPoId = acquisitionGrid.getRowAttribute(id, 'ID');
        });
        acquisitionGrid.init();
        loadAcquisitionData();
    }

    function loadAcquisitionData() {
        var url = 'finance-acquisition-cost-data?format=xml';
        var status = $('#statusFilter').val();
        if (status) url += '&status=' + status;
        acquisitionGrid.load(url);
    }

    function applyFilter() {
        loadAcquisitionData();
    }

    function openAcquisitionModal(rowId) {
        if (!selectedPoId) return;

        $.get('finance-acquisition-cost-data', {
            id: selectedPoId
        }, function(res) {
            var data = res.data && res.data[0];
            if (!data) return;

            var status = data.Acquisition_Status;

            var bodyHtml = '';

            // Info section
            bodyHtml += '<div class="mb-3 p-3 bg-light rounded">';
            bodyHtml += '<div class="row">';
            bodyHtml += '<div class="col-md-6"><strong>DO Code:</strong> ' + (data.DO_Code || '-') + '</div>';
            bodyHtml += '<div class="col-md-6"><strong>PO Code:</strong> ' + (data.PO_Code || '-') + '</div>';
            bodyHtml += '<div class="col-md-6"><strong>POO Name:</strong> ' + (data.POO_Name || '-') + '</div>';
            bodyHtml += '<div class="col-md-6"><strong>Qty:</strong> ' + (data.Qty || '-') + '</div>';
            bodyHtml += '<div class="col-md-6"><strong>Expected Cost:</strong> Rp ' + (data.Total_Cost || '0') + '</div>';
            bodyHtml += '<div class="col-md-6"><strong>Actual Cost:</strong> Rp ' + (data.Actual_Nominal_Transfer || '0') + '</div>';
            bodyHtml += '<div class="col-md-6"><strong>Status:</strong> <span class="badge ' +
                (status === 'APPROVED' ? 'badge-success' : status === 'REJECTED' ? 'badge-danger' : status === 'PENDING' ? 'badge-warning' : 'badge-secondary') +
                '">' + status + '</span></div>';
            bodyHtml += '</div></div>';

            if (status === 'NOT_SUBMITTED') {
                // Ops submission form
                bodyHtml += '<form id="acquisitionSubmitForm">';
                bodyHtml += '<input type="hidden" name="po_id" value="' + selectedPoId + '">';
                bodyHtml += '<div class="form-group">';
                bodyHtml += '<label>Actual Nominal Transfer (Rp)</label>';
                bodyHtml += '<input type="number" step="0.01" class="form-control" name="actual_nominal_transfer" required>';
                bodyHtml += '</div>';
                bodyHtml += '<div class="form-group">';
                bodyHtml += '<label>Actual Qty</label>';
                bodyHtml += '<input type="number" step="0.01" class="form-control" name="actual_qty">';
                bodyHtml += '</div>';
                bodyHtml += '<div class="form-group">';
                bodyHtml += '<label>Receipt File</label>';
                bodyHtml += '<input type="file" class="form-control" name="receipt_file" accept=".pdf,.jpg,.jpeg,.png">';
                bodyHtml += '</div>';
                bodyHtml += '</form>';
            } else if (status === 'PENDING') {
                // Finance approve/reject form
                bodyHtml += '<div class="alert alert-info">Menunggu persetujuan Finance</div>';
                bodyHtml += '<div class="form-group">';
                bodyHtml += '<label>Rejection Note</label>';
                bodyHtml += '<textarea class="form-control" id="rejectionNotes" rows="3" placeholder="Isi catatan penolakan (wajib jika menolak)"></textarea>';
                bodyHtml += '</div>';
            } else if (status === 'APPROVED') {
                bodyHtml += '<div class="alert alert-success">Telah disetujui oleh ' + (data.Acquisition_Approved_By || 'Finance') + ' pada ' + (data.Acquisition_Approved_At || '-') + '</div>';
            } else if (status === 'REJECTED') {
                bodyHtml += '<div class="alert alert-danger">Ditolak. Catatan: ' + (data.Acquisition_Notes || '-') + '</div>';
            }

            var actions = [];

            if (status === 'NOT_SUBMITTED') {
                actions.push({
                    label: 'Close',
                    class: 'btn-secondary'
                });
                actions.push({
                    label: 'Submit',
                    class: 'btn-primary',
                    id: 'btnSubmitAcquisition',
                    onClick: function() {
                        var form = document.getElementById('acquisitionSubmitForm');
                        if (!form.actual_nominal_transfer.value) {
                            alert('Actual Nominal Transfer wajib diisi');
                            return false;
                        }
                        var fd = new FormData(form);
                        fd.append('_token', '{{ csrf_token() }}');
                        $.ajax({
                            url: 'finance-acquisition-cost-submit',
                            type: 'POST',
                            data: fd,
                            processData: false,
                            contentType: false,
                            success: function(res) {
                                if (res.success) {
                                    alert('Data berhasil disubmit');
                                    AppModal.close();
                                    loadAcquisitionData();
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
                });
            } else if (status === 'PENDING') {
                actions.push({
                    label: 'Close',
                    class: 'btn-secondary'
                });
                actions.push({
                    label: 'Approve',
                    class: 'btn-success',
                    id: 'btnApproveAcquisition',
                    onClick: function() {
                        if (!confirm('Setujui acquisition cost ini?')) return false;
                        $.ajax({
                            url: 'finance-acquisition-cost-action',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                po_id: selectedPoId,
                                action: 'approve'
                            },
                            success: function(res) {
                                if (res.success) {
                                    alert('Acquisition cost disetujui');
                                    AppModal.close();
                                    loadAcquisitionData();
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
                });
                actions.push({
                    label: 'Reject',
                    class: 'btn-danger',
                    id: 'btnRejectAcquisition',
                    onClick: function() {
                        var notes = $('#rejectionNotes').val();
                        if (!notes) {
                            alert('Rejection note wajib diisi');
                            return false;
                        }
                        $.ajax({
                            url: 'finance-acquisition-cost-action',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                po_id: selectedPoId,
                                action: 'reject',
                                notes: notes
                            },
                            success: function(res) {
                                if (res.success) {
                                    alert('Acquisition cost ditolak');
                                    AppModal.close();
                                    loadAcquisitionData();
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
                });
            } else {
                actions.push({
                    label: 'Close',
                    class: 'btn-secondary'
                });
            }

            AppModal.open({
                title: 'Acquisition Cost - ' + (data.PO_Code || ''),
                size: 'md',
                body: bodyHtml,
                actions: actions
            });
        });
    }


    function openUploadBuktiTransferModal() {
        if (!selectedPoId) {
            alert('Silakan pilih baris terlebih dahulu');
            return;
        }

        $.get('finance-acquisition-cost-detail', {
            id: selectedPoId
        }, function(res) {
            if (!res.success || !res.data) {
                alert('Data tidak ditemukan');
                return;
            }

            var d = res.data;

            var bodyHtml = '';

            // Customer & Account Details section
            bodyHtml += '<h6 class="mb-3 font-weight-bold border-bottom pb-2">Customer & Account Details</h6>';
            bodyHtml += '<div class="row mb-3">';
            bodyHtml += '<div class="col-md-6">';
            bodyHtml += '<table class="table table-sm table-borderless">';
            bodyHtml += '<tr><td class="text-muted" style="width:140px;">PO Code</td><td>' + (d.PO_Code || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Customer</td><td>' + (d.Customer_Name || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">PIC</td><td>' + (d.PIC || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Phone</td><td>' + (d.Phone || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Address</td><td>' + (d.Full_Address || '-') + '</td></tr>';
            bodyHtml += '</table>';
            bodyHtml += '</div>';
            bodyHtml += '<div class="col-md-6">';
            bodyHtml += '<table class="table table-sm table-borderless">';
            // bodyHtml += '<tr><td class="text-muted" style="width:140px;">Qty</td><td>' + (d.Qty || '0') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Total Pembayaran</td><td>Rp ' + (d.Total_Cost || '0') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Total Pembayaran (Aktual)</td><td>Rp ' + (d.Actual_Nominal_Transfer || '0') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Status</td><td><span class="badge ' +
                (d.Acquisition_Status === 'APPROVED' ? 'badge-success' : d.Acquisition_Status === 'REJECTED' ? 'badge-danger' : d.Acquisition_Status === 'PENDING' ? 'badge-warning' : 'badge-secondary') +
                '">' + (d.Acquisition_Status || '-') + '</span></td></tr>';
            bodyHtml += '<tr><td class="text-muted">Bank</td><td>' + (d.Bank_Name || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Account Number</td><td>' + (d.Bank_Account_Number || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Account Name</td><td>' + (d.Bank_Account_Name || '-') + '</td></tr>';
            bodyHtml += '</table>';
            bodyHtml += '</div>';
            bodyHtml += '</div>';
            bodyHtml += '<hr>';

            // Upload form
            bodyHtml += '<form id="uploadBuktiTransferForm">';
            bodyHtml += '<div class="form-group">';
            bodyHtml += '<label>Upload Bukti Transfer</label>';
            bodyHtml += '<input type="file" class="form-control" name="receipt_file" accept="image/*,.pdf" required>';
            bodyHtml += '</div>';
            bodyHtml += '<div class="form-group">';
            bodyHtml += '<label>Nominal Transfer Aktual</label>';
            bodyHtml += '<input type="text" class="form-control" name="actual_nominal" placeholder="Masukkan nominal..."/>';
            bodyHtml += '</div>';
            bodyHtml += '<div class="form-group">';
            bodyHtml += '<label>Keterangan</label>';
            bodyHtml += '<textarea class="form-control" name="notes" rows="3" placeholder="Masukkan catatan..."></textarea>';
            bodyHtml += '</div>';
            bodyHtml += '</form>';

            AppModal.open({
                title: 'Upload Bukti Transfer - ' + (d.PO_Code || ''),
                size: 'lg',
                body: bodyHtml,
                actions: [{
                    label: 'Upload',
                    class: 'btn-primary',
                    id: 'btnUploadBuktiTransfer',
                    onClick: function() {
                        var form = document.getElementById('uploadBuktiTransferForm');
                        if (!form.receipt_file.files.length) {
                            alert('File wajib dipilih');
                            return false;
                        }
                        var fd = new FormData(form);
                        fd.append('_token', '{{ csrf_token() }}');
                        fd.append('po_id', selectedPoId);
                        $.ajax({
                            url: 'finance-acquisition-cost-upload-receipt',
                            type: 'POST',
                            data: fd,
                            processData: false,
                            contentType: false,
                            success: function(res) {
                                if (res.success) {
                                    alert('Upload berhasil');
                                    AppModal.close();
                                    loadAcquisitionData();
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
        }).fail(function() {
            alert('Gagal memuat data');
        });
    }




    function openDetailModal() {
        if (!selectedPoId) {
            alert('Silakan pilih baris terlebih dahulu');
            return;
        }

        $.get('finance-acquisition-cost-detail', {
            id: selectedPoId
        }, function(res) {
            if (!res.success || !res.data) {
                alert('Data tidak ditemukan');
                return;
            }

            var d = res.data;

            var bodyHtml = '';
            bodyHtml += '<div class="row">';
            bodyHtml += '<div class="col-md-12">';
            bodyHtml += '<table class="table table-sm table-borderless">';
            bodyHtml += '<tr><td class="text-muted" style="width:160px;">Pickup Code</td><td>' + (d.PO_Code || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Warehouse</td><td>' + (d.warehouse_location || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Driver</td><td>' + (d.Driver_Name || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Fleet</td><td>' + (d.Police_Number || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Work Order Code</td><td>' + (d.WO_Code || '-') + '</td></tr>';
            bodyHtml += '<tr><td class="text-muted">Total Cost</td><td>Rp ' + (d.Total_Cost || '0') + '</td></tr>';
            bodyHtml += '</table>';
            bodyHtml += '</div>';
            bodyHtml += '</div>';

            if (d.Receipt_Url) {
                bodyHtml += '<hr>';
                bodyHtml += '<h6 class="mb-2 font-weight-bold">Bukti Transfer</h6>';
                bodyHtml += '<a href="' + d.Receipt_Url + '" target="_blank" class="btn btn-sm btn-outline-primary">';
                bodyHtml += '<i class="fa fa-external-link"></i> Lihat Bukti Transfer';
                bodyHtml += '</a>';
            }

            AppModal.open({
                title: 'Detail - ' + (d.PO_Code || ''),
                size: 'md',
                body: bodyHtml,
                actions: []
            });
        }).fail(function() {
            alert('Gagal memuat data');
        });
    }

    $(document).ready(function() {
        initAcquisitionGrid();

        $(window).on('resize', function() {
            if (acquisitionGrid) acquisitionGrid.setSizes();
        });
    });
</script>
@endsection