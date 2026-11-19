@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Asset Moving Approval
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Data Assets</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Moving Assets Approval</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-clipboard-check me-1"></i> Persetujuan Mutasi Aset
            </span>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <div id="AssetMoving" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" id="ASSETMOVINGAP_Detail" class="btn btn-success btn-sm" onclick="AssetMovingDetail();">
                    <i class="fa fa-check-circle me-1"></i> Detail Form Asset Moving
                </button>
                <button type="button" id="ekselDATAASSETSDet" class="btn btn-warning btn-sm text-dark" onclick="AssetMoving.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" id="DATAASSETS_Refresh" class="btn btn-info btn-sm text-white" onclick="refreshdata();">
                    <i class="fa fa-sync-alt me-1"></i> Refresh Data
                </button>
            </div>
        </div>
    </div>

    <div id="DATAASSETSDTL"></div>

<script>
function AssetMovingDetail() {
    let AssetMovingID = AssetMoving.getSelectedRowId();
    if (AssetMovingID == null) {
        alert("Pilih Baris terlebih dulu");
        return false;
    }
    LoadM('DATAASSETSDTL', 'hrd-asset-moving-approval-detail?ID=' + encodeURIComponent(AssetMovingID));
    var el = document.getElementById('DATAASSETSDTL');
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

AssetMoving = new dhtmlXGridObject('AssetMoving');
AssetMoving.setImagePath("./dhtmlx/imgs/");
AssetMoving.setHeader("No,Request No, Requestor, Department, Request Date, Asset No, Asset Name, Asset Description, Moving Reason, Moving Destination, Moving Date, Receipent Name, Receipent Department, Status");
AssetMoving.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
AssetMoving.setInitWidths("90,120,150,120,100,100,220,200,100,100,100,100,100,100,100,100,100,100,100,100");
AssetMoving.setColAlign("center,center,center,center,center,center,left,left,center,right,center,center,center,center,center,center,center,center,center,center");
AssetMoving.setDateFormat("%Y-%m-%d");
AssetMoving.setColSorting("int,str,str,str,date,str,str,str,str,str,date,str,str,str,str,str,str,str,str,str");
AssetMoving.setColTypes("ro,ed,ro,ed,ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro");
AssetMoving.setNumberFormat("0,000",12,".",",");
AssetMoving.enableSmartRendering(true, 150);
AssetMoving.setEditable(true);
AssetMoving.setSkin("dhx_skyblue");
AssetMoving.init();

function refreshdata() {
    AssetMoving.clearAndLoad('hrd-asset-moving-approval-data');
}

refreshdata();
</script>
@endsection
