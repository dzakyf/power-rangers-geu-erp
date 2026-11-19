@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title text-white fw-bold mb-1" style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
                Asset Fixing Approval
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Human Capital &amp; GA</a></li>
                    <li class="breadcrumb-item"><a href="#" style="color: rgba(255, 255, 255, 0.8) !important;">Data Assets</a></li>
                    <li class="breadcrumb-item active text-white" aria-current="page" style="color: #ffffff !important; font-weight: 600;">Asset Fixing Approval</li>
                </ol>
            </nav>
        </div>
        <div class="d-none d-sm-flex align-items-center gap-2">
            <span class="badge px-3 py-2 fs-12 shadow-sm" style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff !important;">
                <i class="fa fa-clipboard-check me-1"></i> Persetujuan Perbaikan
            </span>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <div id="AssetFixingApproval" style="width:100%;height:380px;background-color:white;overflow:hidden"></div>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                <button type="button" id="ASSETFIXINGAP_Detail" class="btn btn-success btn-sm" onclick="AssetFixingApprovalDetail();">
                    <i class="fa fa-check-circle me-1"></i> Detail Approval
                </button>
                <button type="button" id="ASSETFIXINGAP_Refresh" class="btn btn-outline-secondary btn-sm" onclick="AssetFixingApprovalRefresh();">
                    <i class="fa fa-sync-alt me-1"></i> Refresh Data
                </button>
            </div>
        </div>
    </div>

    <div id="ASSETFIXINGAPPROVALDTL"></div>

<script>
function AssetFixingApprovalDetail() {
    let AssetFixingApprovalID = AssetFixingApproval.getSelectedRowId();
    if (AssetFixingApprovalID == null) {
        alert("Pilih Baris terlebih dulu");
        return false;
    }
    LoadM('ASSETFIXINGAPPROVALDTL', 'hrd-asset-fixing-approval-detail?ID=' + encodeURIComponent(AssetFixingApprovalID));
    var el = document.getElementById('ASSETFIXINGAPPROVALDTL');
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function AssetFixingApprovalRefresh() {
    AssetFixingApproval.clearAndLoad('hrd-asset-fixing-approval-data');
}

AssetFixingApproval = new dhtmlXGridObject('AssetFixingApproval');
AssetFixingApproval.setImagePath("./dhtmlx/imgs/");
AssetFixingApproval.setHeader("No,Request No,Jenis Perbaikan,Requestor,Tanggal Pengajuan,Nama Aset,Estimasi Biaya,Status,Department");
AssetFixingApproval.attachHeader("&nbsp;,#text_filter,#select_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
AssetFixingApproval.setInitWidths("60,140,120,150,120,180,140,120,100");
AssetFixingApproval.setColAlign("center,center,center,center,center,center,right,center,center");
AssetFixingApproval.setDateFormat("%Y-%m-%d");
AssetFixingApproval.setColSorting("int,str,str,str,date,str,str,str,str");
AssetFixingApproval.setColTypes("ro,ed,ro,ed,ro,ro,ro,ro,ro");
AssetFixingApproval.setNumberFormat("0,000",6,".",",");
AssetFixingApproval.enableSmartRendering(true, 150);
AssetFixingApproval.setSkin("dhx_skyblue");
AssetFixingApproval.init();

function refreshdata() {
    AssetFixingApproval.clearAndLoad('hrd-asset-fixing-approval-data');
}

AssetFixingApproval.clearAndLoad('hrd-asset-fixing-approval-data');
</script>
@endsection
