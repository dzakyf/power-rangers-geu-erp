@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="#">Partner</a>
        </li>
        <li class="breadcrumb-item active">
            <span class="active">Royalty</span>
        </li>
    </ol>
</div>


<div id="Approval" class="row m-1">
    <div class="card">
        <div class="card-body">
            @include('partner.partials.index-title', ['title' => 'Approval Royalty'])

            <!-- <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div> -->
            <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
            <div>
                <input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Log" class="btn btn-info mt-2 mr-2" onclick="doubleClickApproval()" />
                <input type="button" value="Detail" class="btn btn-primary mt-2 mr-2" onclick="showDetail()" />

                <!-- <input type="button" id="pengajuan" name="pengajuan" value="Buat Pengajuan" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" /> -->
                <!-- <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" /> -->

                <div class="btn-group mt-2 mr-2" role="group" aria-label="Button group with nested dropdown">
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Action
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            <a class="dropdown-item" href="#" onclick="approveClick('APPROVED')">Approve</a>
                            <a class="dropdown-item" href="#" onclick="approveClick('REJECTED')">Reject</a>
                            @include('approval.partials.force-approve-menu-item', ['idVar' => 'apprId'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<div id="ApprovalDetail" class="row m-1" style="display:none">
    <div class="card w-100">
        <div class="card-body" id="approvalDetailBody">
            @include('partner.partials.index-title', ['title' => 'APPROVAL ROYALTY'])

            <p class="text-muted">Loading...</p>
        </div>
    </div>
</div>

<div id="ApprovalLog" class="row m-1" style="display:none">
    <div class="card">
        <div class="card-body">
            @include('partner.partials.index-title', ['title' => 'DETAIL APPROVAL ROYALTY'])

            <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;" class=" mt-2"></div>
            <div>
            </div>
        </div>
    </div>
</div>

<!-- 
<div class="row m-1">
    <input type="button" id="pengajuan" name="pengajuan" value="Buat Pengajuan" class="btn btn-success mb-2" onclick="redirectToCreateApplication()" />
    <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
    <div class="row col-md-12" style="width:auto;height:40px">
        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
            <div class="btn-group" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                    <a class="dropdown-item" href="#" onclick="approveClick('APPROVED')">Approve</a>
                    <a class="dropdown-item" href="#" onclick="approveClick('REJECTED')">Reject</a>
                </div>
            </div>
        </div>
    </div>
    <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mt-2"></div>
</div> -->

<script>
    var apprId = null;
    $(document).ready(function() {
        initApprovalGrid()
    })

    function redirectToCreateApplication() {
        window.location.href = `partner-profit-sharing-approval-create?id=${apprId}`
    }

    function approveClick(action) {
        if (!apprId) {
            alert('Pilih data terlebih dahulu.');
            return;
        }

        let formData = new FormData();

        formData.append('id', apprId);
        formData.append('_token', '{{ csrf_token() }}')
        formData.append('action', action)

        $.ajax({
            url: "partner-profit-sharing-approve",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response) {
                alert(action === 'REJECTED' ? 'Berhasil reject' : 'Berhasil approve');
                location.reload();
            },
            error: function(xhr, status, error) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : "Telah terjadi kesalahan. Silakan coba lagi.";
                alert(msg);
            },
            complete: function() {
                // $('#submitBtn').prop('disabled', false).text('Submit');
            }
        });
    }

    function initApprovalGrid() {
        let tHeader = ['No', 'Requester', 'Warehouse', 'Periode', 'Tipe Approval', 'Status', 'Deskripsi', 'Disubmit Pada']

        let tWidth = tHeader.map((e) => '150')
        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
        ]
        PartnerGrid = new dhtmlXGridObject('PartnerGrid');
        PartnerGrid.setImagePath("./dhtmlx/imgs/");
        PartnerGrid.setHeader(tHeader.join(','));
        PartnerGrid.setInitWidths(tWidth.join(','));
        // PartnerGrid.enableAutoWidth(true);
        PartnerGrid.attachHeader(headerAttachment);
        // ApprovalLogGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ApprovalLogGrid.setColAlign("center,left,left,left,left");
        // ApprovalLogGrid.setColSorting("int,str,str,str,str");
        // ApprovalLogGrid.setColTypes("ro,ro,ro,ro,ro");
        // ApprovalLogGrid.enableSmartRendering(true, 150);
        // ApprovalLogGrid.setNumberFormat("0,000", 7, ".", ",");
        // PartnerGrid.enableAutoWidth(true);
        PartnerGrid.attachEvent("onRowDblClicked", doubleClickApproval);
        PartnerGrid.attachEvent("onRowSelect", selectMitra);
        PartnerGrid.setSkin("dhx_skyblue");
        PartnerGrid.init();
        PartnerGrid.clearAndLoad(`partner-approval-data?user_id=${apprId}&type=PROFIT_SHARING`);
    }

    function initApprovalLogGrid() {
        let tHeader = [
            'No', 'Status', 'Deskripsi', 'Tipe', 'Requester', 'Approved Oleh', 'Diapprove pada'
        ]

        let tWidth = tHeader.map((e) => '110')
        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
        ]
        ApprovalLogGrid = new dhtmlXGridObject('ProjectionGrid');
        ApprovalLogGrid.setImagePath("./dhtmlx/imgs/");
        ApprovalLogGrid.setHeader(tHeader.join(','));
        ApprovalLogGrid.setInitWidths(tWidth.join(','));
        // ApprovalLogGrid.enableAutoWidth(true);
        ApprovalLogGrid.attachHeader(headerAttachment);
        // ApprovalLogGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ApprovalLogGrid.setColAlign("center,left,left,left,left");
        // ApprovalLogGrid.setColSorting("int,str,str,str,str");
        // ApprovalLogGrid.setColTypes("ro,ro,ro,ro,ro");
        // ApprovalLogGrid.enableSmartRendering(true, 150);
        // ApprovalLogGrid.setNumberFormat("0,000", 7, ".", ",");
        // ApprovalLogGrid.attachEvent("onRowDblClicked", ProjectionGridKlik);
        ApprovalLogGrid.setSkin("dhx_skyblue");
        ApprovalLogGrid.init();
        ApprovalLogGrid.clearAndLoad(`partner-approval-data-log?appr_id=${apprId}&user_id=&type=PROFIT_SHARING`);
    }

    function selectMitra(rowId, cellIdx) {
        var row = PartnerGrid.getRowData(rowId);
        apprId = rowId;
    }

    function doubleClickApproval(rowId) {
        if (rowId) {
            apprId = rowId;
        }
        if (!apprId) {
            alert('Pilih data terlebih dahulu.');
            return;
        }
        $('#ApprovalLog').show();
        if (typeof ApprovalLogGrid === 'undefined' || !ApprovalLogGrid) {
            initApprovalLogGrid();
        } else {
            ApprovalLogGrid.clearAndLoad(`partner-approval-data-log?appr_id=${apprId}&user_id=&type=PROFIT_SHARING`);
            ApprovalLogGrid.setSizes();
        }
    }

    function showDetail() {
        if (!apprId) {
            alert("Pilih data terlebih dahulu");
            return;
        }

        var $body = $('#approvalDetailBody');
        $body.html('<p class="text-muted">Loading...</p>');
        $('#ApprovalDetail').show();

        $.get('partner-profit-sharing-approval-detail', {
            appr_id: apprId
        }, function(res) {
            var d = res.data;
            if (!d) {
                $body.html('<p class="text-danger">Data not found</p>');
                return;
            }

            var html = '';

            // Approval Info
            html += '<div class="card mb-2 border-left-primary"><div class="card-header py-2"><strong>Approval Info</strong></div><div class="card-body py-2">';
            html += '<div class="row"><div class="col-12">Status: <strong>' + (d.approval?.Status || '-') + '</strong></div>';
            html += '<div class="col-12">Type: ' + (d.approval?.Request_Type || '-') + '</div></div></div></div>';

            // Partner Info
            if (d.partner) {
                html += '<div class="card mb-2 border-left-success"><div class="card-header py-2"><strong>Partner Info</strong></div><div class="card-body py-2">';
                html += '<div class="row">';
                html += '<div class="col-12">Lead ID: <strong>' + (d.partner.lead_id || '-') + '</strong></div>';
                html += '<div class="col-12">Name: <strong>' + (d.partner.full_name || '-') + '</strong></div>';
                html += '<div class="col-12">Address: ' + (d.partner.address || '-') + '</div>';
                html += '<div class="col-12">City: ' + (d.partner.city || '-') + '</div>';
                html += '<div class="col-12">Contract Start: ' + (d.partner.contract_start_at || '-') + '</div>';
                html += '<div class="col-12">Contract End: ' + (d.partner.contract_end_at || '-') + '</div>';
                html += '</div></div></div>';
            }

            // Account Payable
            if (d.account_payable) {
                html += '<div class="card mb-2 border-left-info"><div class="card-header py-2"><strong>Account Payable</strong></div><div class="card-body py-2">';
                html += '<div class="row">';
                html += '<div class="col-12">Reference: <strong>' + (d.account_payable.Reference || '-') + '</strong></div>';
                html += '<div class="col-12">Total: Rp ' + (d.account_payable.Total || '0') + '</div>';
                html += '<div class="col-12">Status: ' + (d.account_payable.Status || '-') + '</div>';
                html += '<div class="col-12">File: ';
                if (d.account_payable.Url_Transfer) {
                    html += '<a href="' + d.account_payable.Url_Transfer + '" target="_blank">Download</a>';
                } else if (d.account_payable.File_Document) {
                    html += '<a href="' + d.account_payable.File_Document + '" target="_blank">Download</a>';
                } else {
                    html += '-';
                }
                html += '</div></div></div></div>';
            }

            // Files
            if (d.files && d.files.length > 0) {
                html += '<div class="card mb-2 border-left-warning"><div class="card-header py-2"><strong>Uploaded Files</strong></div><div class="card-body py-2">';
                d.files.forEach(function(f) {
                    html += '<div><a href="' + (f.Path || '#') + '" target="_blank">' + (f.Filename || 'File') + '</a></div>';
                });
                html += '</div></div>';
            }

            // Log History
            if (d.log && d.log.length > 0) {
                html += '<div class="card mb-2 border-left-secondary"><div class="card-header py-2"><strong>Approval Log</strong></div><div class="card-body py-2">';
                d.log.forEach(function(l) {
                    html += '<div class="mb-1"><span class="badge badge-info">' + (l.Action || '-') + '</span> ' +
                        (l.Notes || '') + ' <small class="text-muted">(' + (l.Action_Date || '') + ')</small></div>';
                });
                html += '</div></div>';
            }

            $body.html(html);
        }).fail(function() {
            $body.html('<p class="text-danger">Failed to load detail</p>');
        });
    }
</script>
@include('approval.partials.force-approve-script')
@endsection