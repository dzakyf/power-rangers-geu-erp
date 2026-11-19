@extends('layout.mainlayout-hrd')

@section('content-wrapper')
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Asset Handover Returned Confirmation
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="/dashboard1" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">General Affairs</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Handover Returned Confirmation</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-clipboard-check me-1"></i> Persetujuan Pengembalian
            </span>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <div id="AssetHandover" style="width:100%;height:500px;background-color:white;overflow:hidden"></div>
            <div id="divtom2" class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" id="DetailAssetHandover" name="DetailAssetHandover" class="btn btn-info btn-sm text-white">
                    <i class="fa fa-check-circle me-1"></i> Detail &amp; Approval Pengembalian
                </button>
                <button type="button" id="ekselAssetHandover" name="ekselAssetHandover" class="btn btn-warning btn-sm text-dark" onclick="AssetHandover.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshData();">
                    <i class="fa fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <div id="AssetHandoverDiv"></div>

    <script>
        AssetHandover = new dhtmlXGridObject('AssetHandover');
        AssetHandover.setImagePath("dhtmlx/imgs/");
        AssetHandover.setHeader("No.,Handover No, Handover Date, User Name, User NIK, PIC Name, PIC Department, PIC Phone, Reference, Remark, Products");
        AssetHandover.setInitWidths("70,170,100,100,100,100,100,100,120,100");
        AssetHandover.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
        AssetHandover.setColAlign("center,center,center,center,center,center,center,left,center,center");
        AssetHandover.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
        AssetHandover.setColSorting("int,str,str,str,str,str,str,str,str");
        AssetHandover.setEditable(true);
        AssetHandover.setSkin("dhx_skyblue");
        AssetHandover.init();
        refreshData();

        function refreshData() {
            AssetHandover.clearAndLoad("hrd-asset-handover-returned-approval-data");
        }

        $('#DetailAssetHandover').on('click', function() {
            let pmid = AssetHandover.getSelectedRowId();
            if (pmid == null) {
                alert('Mohon pilih baris');
                return;
            }
            let Handover_No = AssetHandover.cells(pmid, 1).getValue();
            LoadM('AssetHandoverDiv', 'hrd-asset-handover-returned-approval-detail?Handover_No=' + encodeURIComponent(Handover_No));
            var el = document.getElementById('AssetHandoverDiv');
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    </script>
@endsection
