@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Renewal</span></li>
    </ol>
</div>

<div class="card mb-3">
    <div class="card-header m-3">
        <h5>APPROVAL RENEWAL</h5>
    </div>
    <div class="card-body">
        <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class="mb-2"></div>
        <div>
            <!-- <input type="button" value="Buat Pengajuan" class="btn btn-success" onclick="redirectToCreateApplication()" /> -->
            <input type="button" value="Log" class="btn btn-info" onclick="doubleClickMitra()" />
            <input type="button" id="detailBtn" value="Detail" class="btn btn-primary" onclick="showDetail()" />
            <div class="btn-group " role="group">
                <div class="btn-group" role="group">
                    <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
</div>

<div id="ApprovalLog" class="card mb-3" style="display:none">
    <div class="card-header m-3">
        <h5>LOG APPROVAL RENEWAL</h5>
    </div>
    <div class="card-body">
        <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class="mt-2"></div>
    </div>
</div>

<div id="ApprovalDetail" class="card mb-3" style="display:none">
    <div class="card-header m-3">
        <h5>DETAIL RENEWAL</h5>
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
        window.location.href = `partner-renewal-approval-create?id=${mitraId}`
    }

    function approveClick(action) {
        let formData = new FormData();
        formData.append('id', mitraId);
        formData.append('_token', '{{ csrf_token() }}')
        formData.append('action', action)

        $.ajax({
            url: "partner-renewal-approval-approve",
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
        let tHeader = ['No', 'Lead ID', 'Nama', 'Status Renewal', 'Tipe Approval', 'Dibuat Pada']

        let tWidth = tHeader.map(() => '120')
        let headerAttachment = tHeader.map(() => '#text_filter')
        headerAttachment[0] = '#rspan'

        PartnerGrid = new dhtmlXGridObject('PartnerGrid');
        PartnerGrid.setImagePath("./dhtmlx/imgs/");
        PartnerGrid.setHeader(tHeader.join(','));
        PartnerGrid.setInitWidths(tWidth.join(','));
        PartnerGrid.attachHeader(headerAttachment);
        PartnerGrid.attachEvent("onRowDblClicked", doubleClickMitra);
        PartnerGrid.attachEvent("onRowSelect", selectMitra);
        PartnerGrid.setSkin("dhx_skyblue");
        PartnerGrid.init();
        PartnerGrid.clearAndLoad(`partner-profit-sharing-data?user_id=${mitraId}&type=RENEWAL`);
    }

    function initApprovalLogGrid() {
        let tHeader = ['No', 'Status', 'Deskripsi', 'Tipe', 'Requester', 'Approved Oleh', 'Diapprove pada']
        let tWidth = tHeader.map(() => '110')
        let headerAttachment = ['#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter']

        ProjectionGrid = new dhtmlXGridObject('ProjectionGrid');
        ProjectionGrid.setImagePath("./dhtmlx/imgs/");
        ProjectionGrid.setHeader(tHeader.join(','));
        ProjectionGrid.setInitWidths(tWidth.join(','));
        ProjectionGrid.enableAutoWidth(true);
        ProjectionGrid.attachHeader(headerAttachment);
        ProjectionGrid.setSkin("dhx_skyblue");
        ProjectionGrid.init();
        ProjectionGrid.clearAndLoad(`partner-profit-sharing-data-log?request_id=${mitraId}&user_id=&type=RENEWAL`);
    }

    function selectMitra(id, cellIdx) {
        mitraId = PartnerGrid.cells(id, 0).getValue();
    }

    function doubleClickMitra() {
        $('#ApprovalLog').show();
        initApprovalLogGrid()
    }

    function showDetail() {
        if (!mitraId) {
            alert('Pilih data terlebih dahulu');
            return;
        }

        $('#ApprovalDetail').show();
        $('#approvalDetailBody').html('<p class="text-muted">Loading...</p>');

        $.get('partner-renewal-approval-detail', {
            appr_id: mitraId
        }, function(res) {
            var d = res.data;
            if (!d) {
                $('#approvalDetailBody').html('<p class="text-danger">Data not found</p>');
                return;
            }

            var html = '<table class="table table-bordered table-sm">' +
                '<tr><td style="width:200px;"><strong>Lead ID</strong></td><td>' + (d.lead_id || '-') + '</td></tr>' +
                '<tr><td><strong>Nama</strong></td><td>' + (d.full_name || '-') + '</td></tr>' +
                '<tr><td><strong>Alamat</strong></td><td>' + (d.address || '-') + '</td></tr>' +
                '<tr><td><strong>Status Renewal</strong></td><td>' + (d.Status || '-') + '</td></tr>' +
                '<tr><td><strong>Kontrak Berakhir</strong></td><td>' + (d.Contract_End_At || '-') + '</td></tr>' +
                '<tr><td><strong>Biaya Renewal</strong></td><td>' + (d.Renewal_Fee || '-') + '</td></tr>' +
                '<tr><td><strong>Durasi Perpanjangan</strong></td><td>' + (d.Renewed_Length || '-') + ' bln</td></tr>' +
                '</table>';

            $('#approvalDetailBody').html(html);
        }).fail(function() {
            $('#approvalDetailBody').html('<p class="text-danger">Failed to load detail</p>');
        });
    }
</script>
@endsection