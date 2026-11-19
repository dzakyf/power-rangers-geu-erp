@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Asset Fixing Management
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Data Assets</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Asset Fixing</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-tools me-1"></i> Perbaikan &amp; Perawatan Aset
            </span>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <div id="AssetFixing" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" id="ASSETFIXING_New" class="btn btn-primary btn-sm" onclick="AssetFixingNew();">
                    <i class="fa fa-plus me-1"></i> Form Perbaikan Aset
                </button>
                <button type="button" id="ASSETFIXING_Detail" class="btn btn-info btn-sm text-white" onclick="AssetFixingDetail();">
                    <i class="fa fa-eye me-1"></i> Detail Perbaikan
                </button>
                <button type="button" id="ASSETFIXING_Log" class="btn btn-success btn-sm" onclick="AssetFixingLog();">
                    <i class="fa fa-history me-1"></i> Log Perbaikan Aset
                </button>
                <button type="button" id="ekselASSETFIXING" class="btn btn-warning btn-sm text-dark" onclick="AssetFixing.toExcel('./export/generate.php');">
                    <i class="fa fa-file-excel me-1"></i> Export Excel
                </button>
                <button type="button" id="ASSETFIXING_Refresh" class="btn btn-outline-secondary btn-sm" onclick="AssetFixingRefresh();">
                    <i class="fa fa-sync-alt me-1"></i> Refresh Data
                </button>
                <a href="hrd-asset-fixing-schedule" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-calendar-check me-1"></i> Jadwal Perawatan Aset (Schedule)
                </a>
            </div>
        </div>
    </div>

    <div id="ASSETFIXINGDTL"></div>
    <div id="ASSETFIXINGDTL1"></div>

<script>
function scrollToFixingPanel(id) {
    var el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function AssetFixingNew() {
    LoadM('ASSETFIXINGDTL1', 'hrd-asset-fixing-create');
    scrollToFixingPanel('ASSETFIXINGDTL1');
}

function AssetFixingDetail() {
    let AssetFixingID = AssetFixing.getSelectedRowId();
    if (AssetFixingID == null) {
        alert("Pilih Baris terlebih dulu");
        return false;
    }
    var url = 'hrd-asset-fixing-detail?ID=' + encodeURIComponent(AssetFixingID);
    LoadM('ASSETFIXINGDTL', url);
    scrollToFixingPanel('ASSETFIXINGDTL');
}

function AssetFixingLog() {
    let AssetFixingID = AssetFixing.getSelectedRowId();
    if (AssetFixingID == null) {
        alert("Pilih Baris terlebih dulu");
        return false;
    }
    var url = 'hrd-asset-fixing-log?ID=' + encodeURIComponent(AssetFixingID);
    LoadM('ASSETFIXINGDTL', url);
    scrollToFixingPanel('ASSETFIXINGDTL');
}

function AssetFixingRefresh() {
    AssetFixing.clearAndLoad('hrd-asset-fixing-data');
}

AssetFixing = new dhtmlXGridObject('AssetFixing');
AssetFixing.setImagePath("./dhtmlx/imgs/");
AssetFixing.setHeader("No,Request No,Jenis Perbaikan,Requestor,Tanggal Pengajuan,Nama Aset,Product Code,Asset No,SN,Estimasi Biaya,Status");
AssetFixing.attachHeader("&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
AssetFixing.setInitWidths("60,140,120,150,120,180,100,100,100,120,100");
AssetFixing.setColAlign("center,center,center,center,center,center,right,center,center,right,center");
AssetFixing.setDateFormat("%Y-%m-%d");
AssetFixing.setColSorting("int,str,str,str,date,str,str,str,str,str,str");
AssetFixing.setColTypes("ro,ed,ro,ed,ro,ro,ro,ro,ro,ro,ro");
AssetFixing.setNumberFormat("0,000",9,".",",");
AssetFixing.enableSmartRendering(true, 150);
AssetFixing.setSkin("dhx_skyblue");
AssetFixing.init();
AssetFixing.clearAndLoad('hrd-asset-fixing-data');

function refreshdata() {
    AssetFixing.clearAndLoad('hrd-asset-fixing-data');
}
</script>
@endsection
