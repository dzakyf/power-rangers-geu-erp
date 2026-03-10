@extends('layout.mainlayout-mitra')

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
            <span class="active">Proyeksi</span>
        </li>
    </ol>
</div>

<div class="row m-1">

    <input type="button" id="detailBP" name="detailBP" value="Buat Pengajuan" class="btn btn-success mb-2" onclick="redirectToCreateApplication()" />
    <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
    <div id="ExpenditureGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
    <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mt-2"></div>
    <div>
        <!-- <input type="button" id="ekselProjectionGridDet" name="ekselProjectionGridDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProjectionGrid.toExcel('./export/generate.php');" /> -->
        <!-- <input type="button" id="createBroadcast" name="createBroadcast" value="Create Panduan" class="btn btn-info mt-2 mr-2" onclick="createPanduan()" /> -->
        <div class="btn-group" role="group">
        </div>
    </div>
</div>

<script>
    var mitraId = null;
    $(document).ready(function() {
        initApprovalGrid()
    })

    function redirectToCreateApplication() {
        window.location.href = `partner-profit-sharing-approval-create?id=${mitraId}`
    }

    function initApprovalGrid() {
        let tHeader = [
            'No', 'Nama', 'Tipe Approval', 'Status'
        ]

        let tWidth = tHeader.map((e) => '110')
        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
        ]
        PartnerGrid = new dhtmlXGridObject('PartnerGrid');
        PartnerGrid.setImagePath("./dhtmlx/imgs/");
        PartnerGrid.setHeader(tHeader.join(','));
        PartnerGrid.setInitWidths(tWidth.join(','));
        // PartnerGrid.enableAutoWidth(true);
        PartnerGrid.attachHeader(headerAttachment);
        // ProjectionGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ProjectionGrid.setColAlign("center,left,left,left,left");
        // ProjectionGrid.setColSorting("int,str,str,str,str");
        // ProjectionGrid.setColTypes("ro,ro,ro,ro,ro");
        // ProjectionGrid.enableSmartRendering(true, 150);
        // ProjectionGrid.setNumberFormat("0,000", 7, ".", ",");
        // PartnerGrid.enableAutoWidth(true);
        PartnerGrid.attachEvent("onRowDblClicked", doubleClickMitra);
        PartnerGrid.attachEvent("onRowSelect", selectMitra);
        PartnerGrid.setSkin("dhx_skyblue");
        PartnerGrid.init();
        PartnerGrid.clearAndLoad('partner-profit-sharing-data?user_id=2345&type=PROFIT_SHARING');
    }

    function initApprovalLogGrid() {
        let tHeader = [
            'No', 'Status', 'Deskripsi', 'Tipe', 'Requester', 'Approved Oleh', 'Diapprove pada'
        ]

        let tWidth = tHeader.map((e) => '110')
        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
        ]
        ProjectionGrid = new dhtmlXGridObject('ProjectionGrid');
        ProjectionGrid.setImagePath("./dhtmlx/imgs/");
        ProjectionGrid.setHeader(tHeader.join(','));
        ProjectionGrid.setInitWidths(tWidth.join(','));
        ProjectionGrid.enableAutoWidth(true);
        ProjectionGrid.attachHeader(headerAttachment);
        // ProjectionGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ProjectionGrid.setColAlign("center,left,left,left,left");
        // ProjectionGrid.setColSorting("int,str,str,str,str");
        // ProjectionGrid.setColTypes("ro,ro,ro,ro,ro");
        // ProjectionGrid.enableSmartRendering(true, 150);
        // ProjectionGrid.setNumberFormat("0,000", 7, ".", ",");
        // ProjectionGrid.attachEvent("onRowDblClicked", ProjectionGridKlik);
        ProjectionGrid.setSkin("dhx_skyblue");
        ProjectionGrid.init();
        ProjectionGrid.clearAndLoad('partner-profit-sharing-data-log?request_id=28&user_id=2345&type=PROFIT_SHARING');
    }


    function initExpenditureGrid() {
        let tHeader = [
            'No', 'Tipe Hub', 'Tipe Proyeksi', 'Uraian', ' Standard',
            ' %', 'Tahun Ke-', ' Month 1 ',
            ' Month 2',
            ' Month 3',
            ' Month 4',
            ' Month 5',
            ' Month 6',
            'Month 7',
            'Month 8',
            'Month 9',
            'Month 10',
            'Month 11',
            ' Month 12',
        ]
        let tWidth = tHeader.map((e) => '140')

        let headerAttachment = [
            '#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
        ]

        ExpenditureGrid = new dhtmlXGridObject('ExpenditureGrid');
        ExpenditureGrid.setImagePath("./dhtmlx/imgs/");
        ExpenditureGrid.setHeader(tHeader.join(','));
        ExpenditureGrid.setInitWidths(tWidth.join(','));
        ExpenditureGrid.enableAutoWidth(true);
        ExpenditureGrid.attachHeader(headerAttachment);
        // ProjectionGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
        // ProjectionGrid.setColAlign("center,left,left,left,left");
        // ProjectionGrid.setColSorting("int,str,str,str,str");
        // ProjectionGrid.setColTypes("ro,ro,ro,ro,ro");
        // ProjectionGrid.enableSmartRendering(true, 150);
        // ProjectionGrid.setNumberFormat("0,000", 7, ".", ",");
        // ProjectionGrid.attachEvent("onRowDblClicked", ProjectionGridKlik);
        ExpenditureGrid.setSkin("dhx_skyblue");
        ExpenditureGrid.init();
        ExpenditureGrid.clearAndLoad(`expenditure-data-expenditure?user_id=${mitraId}&hub_type=&projection_type=&year=`);

    }

    function selectMitra(id, cellIdx) {
        mitraId = PartnerGrid.cells(id, 0).getValue();
    }

    function doubleClickMitra() {
        initApprovalLogGrid()
    }
</script>
@endsection