@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<div class="main-content-header">
    <h1>Partner</h1>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="#">Partner</a></li>
        <li class="breadcrumb-item active"><span class="active">Termination</span></li>
    </ol>
</div>

<div class="card mb-3">
    <div class="card-header m-3">
        <h5>EXIT TERMINATION</h5>
    </div>
    <div class="card-body">
        <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class="mb-2"></div>
        <div>
            <input type="button" value="Buat Pengajuan" class="btn btn-success" onclick="redirectToCreateApplication()" />
            <input type="button" value="Export Excel" class="btn btn-warning ml-2" onclick="PartnerGrid.toExcel('./export/generate.php');" />
        </div>
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

    function initApprovalGrid() {
        let tHeader = ['No', 'Lead ID', 'Nama', 'Status', 'Dibuat Pada']
        let tWidth = tHeader.map(() => '150')
        let headerAttachment = tHeader.map(() => '#text_filter')
        headerAttachment[0] = '#rspan'

        PartnerGrid = new dhtmlXGridObject('PartnerGrid');
        PartnerGrid.setImagePath("./dhtmlx/imgs/");
        PartnerGrid.setHeader(tHeader.join(','));
        PartnerGrid.setInitWidths(tWidth.join(','));
        PartnerGrid.attachHeader(headerAttachment);
        PartnerGrid.attachEvent("onRowSelect", selectMitra);
        PartnerGrid.setSkin("dhx_skyblue");
        PartnerGrid.init();
        PartnerGrid.clearAndLoad('partner-termination-data');
    }

    function selectMitra(id, cellIdx) {
        mitraId = PartnerGrid.cells(id, 0).getValue();
    }
</script>
@endsection