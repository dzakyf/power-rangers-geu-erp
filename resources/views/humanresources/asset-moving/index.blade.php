@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Asset Moving Management
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Data Assets</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Moving Assets</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-exchange-alt me-1"></i> Mutasi &amp; Pemindahan Aset
            </span>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <div id="AssetMoving" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" id="ASSETMOVING_New" class="btn btn-primary btn-sm" onclick="AssetMovingNew();">
                    <i class="fa fa-plus me-1"></i> Form Asset Moving
                </button>
                <button type="button" id="ASSETMOVING_Log" class="btn btn-success btn-sm" onclick="AssetMovingLog();">
                    <i class="fa fa-history me-1"></i> Log Asset Moving
                </button>
                <button type="button" id="ASSETMOVING_Excel" class="btn btn-warning btn-sm text-dark" onclick="AssetMoving.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" id="ASSETMOVING_Refresh" class="btn btn-info btn-sm text-white" onclick="AssetMovingRefresh();">
                    <i class="fa fa-sync-alt me-1"></i> Refresh Data
                </button>
                <button type="button" id="ASSETMOVING_AssetList" class="btn btn-outline-secondary btn-sm" onclick="AssetMovingAssetList();">
                    <i class="fa fa-list me-1"></i> Asset Lists
                </button>
            </div>
        </div>
    </div>

    <div id="assetList" class="card shadow-sm border-0 mt-3" style="display:none;width:100%">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fa fa-boxes me-2"></i>Daftar Master Aset</h6>
            <button type="button" class="btn-close" aria-label="Close" onclick="$('#assetList').hide()"></button>
        </div>
        <div class="card-body">
            <div id="DATAASSETS" style="width:100%;height:350px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" id="DATAASSETS_Detail" class="btn btn-primary btn-sm" onclick="DATAASSETSDBL();">
                    <i class="fa fa-eye me-1"></i> Detail Data
                </button>
                <button type="button" id="ekselDATAASSETSDet" class="btn btn-warning btn-sm text-dark" onclick="DATAASSETS.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" id="DATAASSETS_Refresh" class="btn btn-info btn-sm text-white" onclick="DATAASSETSRefresh();">
                    <i class="fa fa-sync-alt me-1"></i> Refresh Data
                </button>
            </div>
        </div>
    </div>

    <div id="DATAASSETSDTL"></div>
    <div id="DATAASSETSDTL1"></div>

<script>
function scrollToMovingPanel(id) {
    var el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function AssetMovingAssetList() {
    DATAASSETSRefresh();
    $('#assetList').toggle();
}

function AssetMovingRefresh() {
    AssetMoving.clearAndLoad('hrd-asset-moving-data');
}

function AssetMovingNew() {
    LoadM('DATAASSETSDTL1', 'hrd-asset-moving-create');
    scrollToMovingPanel('DATAASSETSDTL1');
}

function AssetMovingLog() {
    var selectedId = AssetMoving.getSelectedRowId();
    if (!selectedId) {
        alert('Pilih baris pemindahan aset terlebih dahulu.');
        return;
    }
    var reqNo = AssetMoving.cells(selectedId, 1).getValue();
    LoadM('DATAASSETSDTL1', 'hrd-asset-moving-log?ID=' + encodeURIComponent(reqNo));
    scrollToMovingPanel('DATAASSETSDTL1');
}

function NEWDATA() {
    LoadM('DATAASSETSDTL', 'hrd-asset-lists-new');
    scrollToMovingPanel('DATAASSETSDTL');
}

function DATAASSETSDBL() {
    var selectedId = DATAASSETS.getSelectedId();
    if (!selectedId) {
        alert('Pilih baris aset terlebih dahulu.');
        return;
    }
    var assetNo = DATAASSETS.cells(selectedId, 1).getValue();
    LoadM('DATAASSETSDTL', 'hrd-asset-lists-detail?Task_ID=' + encodeURIComponent(assetNo));
    scrollToMovingPanel('DATAASSETSDTL');
}

AssetMoving = new dhtmlXGridObject('AssetMoving');
AssetMoving.setImagePath("./dhtmlx/imgs/");
AssetMoving.setHeader("No,Request No, Requestor, Department, Request Date, Asset No, Asset Name, Asset Description, Moving Reason, Moving Destination, Moving Date, Receipent Name, Receipent Department, Status");
AssetMoving.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
AssetMoving.setInitWidths("60,130,140,110,100,120,180,180,140,140,100,130,130,120");
AssetMoving.setColAlign("center,center,center,center,center,center,left,left,center,center,center,center,center,center");
AssetMoving.setDateFormat("%Y-%m-%d");
AssetMoving.setColSorting("int,str,str,str,date,str,str,str,str,str,date,str,str,str");
AssetMoving.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
AssetMoving.setNumberFormat("0,000",12,".",",");
AssetMoving.enableSmartRendering(true, 150);
AssetMoving.setEditable(true);
AssetMoving.setSkin("dhx_skyblue");
AssetMoving.init();
AssetMoving.clearAndLoad('hrd-asset-moving-data');

DATAASSETS = new dhtmlXGridObject('DATAASSETS');
DATAASSETS.setImagePath("./dhtmlx/imgs/");
DATAASSETS.setHeader("No,Asset No,Asset Category,Asset Sub Category,Asset Type,Asset Code,Asset Description,Unit Name,Sub Product Type,Manufacture Date,Registration No,Buy Date,Buy Value,Asset Location,Address,City,Departement,Vendor,Contract No,Acc No");
DATAASSETS.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
DATAASSETS.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,,#cspan,#cspan,");
DATAASSETS.setInitWidths("90,120,150,120,100,100,220,200,100,100,100,100,100,100,100,100,100,100,100,100");
DATAASSETS.setColAlign("center,center,center,center,center,center,left,left,center,right,center,center,center,center,center,center,center,center,center,center");
DATAASSETS.setDateFormat("%Y-%m-%d");
DATAASSETS.setColSorting("int,str,str,str,date,str,str,str,str,str,date,str,str,str,str,str,str,str,str,str");
DATAASSETS.setColTypes("ro,ed,ro,ed,ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro");
DATAASSETS.setNumberFormat("0,000",12,".",",");
DATAASSETS.attachEvent("onRowDblClicked", DATAASSETSDBL);
DATAASSETS.enableSmartRendering(true, 150);
DATAASSETS.setEditable(true);
DATAASSETS.setSkin("dhx_skyblue");
DATAASSETS.init();

function DATAASSETSRefresh() {
    DATAASSETS.clearAndLoad('xml-asset-lists?');
}

function SimpanPenyelesaianOrder() {
    PDATAASSETS.sendData();
    alert("Data Berhasil Disimpan");
    setTimeout(function() {
        DATAASSETSRefresh();
    }, 2000);
}
</script>
@endsection
