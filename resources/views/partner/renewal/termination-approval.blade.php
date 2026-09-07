@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Terminasi</span></li>
    </ol>
</div>

<div class="card mb-3">
    <div class="card-header m-3">
        <h5>APPROVAL TERMINATION</h5>
    </div>
    <div class="card-body">
        <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class="mb-2"></div>
        <div>
            <input type="button" value="Buat Pengajuan" class="btn btn-success" onclick="redirectToCreateApplication()" />
            <input type="button" value="Log" class="btn btn-info ml-2" onclick="doubleClickMitra()" />
            <input type="button" value="Detail" class="btn btn-primary ml-2" onclick="showDetail()" />

            <div class="btn-group ml-2" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
                    Action
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                    <a class="dropdown-item" href="#" onclick="approveClick('APPROVED')">Approve</a>
                    <a class="dropdown-item" href="#" onclick="approveClick('REJECTED')">Reject</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ApprovalLog" class="card mb-3" style="display:none">
    <div class="card-header m-3">
        <h5>LOG APPROVAL TERMINASI</h5>
    </div>
    <div class="card-body">
        <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class="mt-2"></div>
    </div>
</div>

<div id="ApprovalDetail" class="card mb-3" style="display:none">
    <div class="card-header m-3">
        <h5>DETAIL TERMINASI</h5>
    </div>
    <div class="card-body" id="approvalDetailBody">
        <p class="text-muted">Loading...</p>
    </div>
</div>

<script>
    var mitraId = null;
    $(document).ready(function() {
        initApprovalGrid()
    })

    function redirectToCreateApplication() {
        window.location.href = `partner-termination-approval-create?id=${mitraId}`
    }

    function approveClick(action) {
        let formData = new FormData();
        formData.append('id', mitraId);
        formData.append('_token', '{{ csrf_token() }}')
        formData.append('action', action)

        $.ajax({
            url: "partner-termination-approval-approve",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            success: function(response) {
                alert("Berhasil approve");
            },
            error: function() {
                alert("Telah terjadi kesalahan. Silakan coba lagi.");
            }
        });
    }

    function initApprovalGrid() {
        let tHeader = ['No', 'Nama', 'Lead ID', 'Tipe Approval', 'Status', 'Dibuat Pada']
        let tWidth = ['60', '*', '100', '120', '100', '150']
        let headerAttachment = ['#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter']

        PartnerGrid = new dhtmlXGridObject('PartnerGrid');
        PartnerGrid.setImagePath("./dhtmlx/imgs/");
        PartnerGrid.setHeader(tHeader.join(','));
        PartnerGrid.setInitWidths(tWidth.join(','));
        PartnerGrid.attachHeader(headerAttachment);
        PartnerGrid.attachEvent("onRowDblClicked", doubleClickMitra);
        PartnerGrid.attachEvent("onRowSelect", selectMitra);
        PartnerGrid.setSkin("dhx_skyblue");
        PartnerGrid.init();
        PartnerGrid.clearAndLoad(`partner-termination-approval-data?user_id=${mitraId}&type=TERMINATION`);
    }

    function initApprovalLogGrid() {
        let tHeader = ['No', 'Status', 'Deskripsi', 'Tipe', 'Requester', 'Approved Oleh', 'Diapprove pada']
        let tWidth = ['60', '*', '100', '120', '100', '150']
        let headerAttachment = ['#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter']

        ProjectionGrid = new dhtmlXGridObject('ProjectionGrid');
        ProjectionGrid.setImagePath("./dhtmlx/imgs/");
        ProjectionGrid.setHeader(tHeader.join(','));
        ProjectionGrid.setInitWidths(tWidth.join(','));
        ProjectionGrid.enableAutoWidth(true);
        ProjectionGrid.attachHeader(headerAttachment);
        ProjectionGrid.setSkin("dhx_skyblue");
        ProjectionGrid.init();
        ProjectionGrid.clearAndLoad(`partner-termination-approval-data-log?request_id=${mitraId}&user_id=&type=TERMINATION`);
    }

    function selectMitra(id, cellIdx) {
        // The grid row id IS the approval_request.ID (cell 0 is only the row
        // counter) - reading cell 0 here targeted the wrong request.
        mitraId = id;
    }

    function doubleClickMitra() {
        if (!mitraId) {
            alert('Pilih data terlebih dahulu');
            return;
        }

        $('#ApprovalLog').show();
        initApprovalLogGrid()
    }

    function showDetail() {
        if (!mitraId) {
            alert('Pilih data terlebih dahulu');
            return;
        }

        var rowId = PartnerGrid.getSelectedId();
        var opptId = PartnerGrid.getRowAttribute(rowId, 'Oppt_Id');
        if (!opptId) {
            alert('Data tidak ditemukan');
            return;
        }

        $('#ApprovalDetail').show();
        $('#approvalDetailBody').html('<p class="text-muted">Loading...</p>');

        $.get('partner-termination-approval-detail', {
            oppt_id: opptId
        }, function(res) {
            var d = res.data;
            if (!d) {
                $('#approvalDetailBody').html('<p class="text-danger">Data not found</p>');
                return;
            }

            var html = '<table class="table table-bordered table-sm">' +
                '<tr><td style="width:200px;"><strong>Lead ID</strong></td><td>' + (d.lead_id || '-') + '</td></tr>' +
                '<tr><td><strong>Nama</strong></td><td>' + (d.full_name || '-') + '</td></tr>' +
                '<tr><td><strong>Status</strong></td><td>' + (d.Status || '-') + '</td></tr>' +
                '<tr><td><strong>Dibuat Pada</strong></td><td>' + (d.Created_At || '-') + '</td></tr>' +
                '<tr><td><strong>Approved At</strong></td><td>' + (d.Approved_At || '-') + '</td></tr>' +
                '<tr><td><strong>Resolution</strong></td><td>' + (d.Resolution || '-') + '</td></tr>' +
                '</table>';

            $('#approvalDetailBody').html(html);
        }).fail(function() {
            $('#approvalDetailBody').html('<p class="text-danger">Failed to load detail</p>');
        });
    }
</script>
@endsection