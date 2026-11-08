@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<style>
    .sppd-page-wrapper {
        margin-bottom: 2rem;
    }
    .sppd-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        overflow: hidden;
    }
    .sppd-card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .sppd-card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .sppd-grid-container {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .sppd-btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .sppd-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
</style>

<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Approval Surat Perintah Perjalanan Dinas (SPPD)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="hrd-sppd-index">Perjalanan Dinas</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Approval SPPD</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="hrd-sppd-index" class="btn btn-outline-secondary sppd-btn">
            <i class="fa fa-arrow-left"></i> Kembali ke Request SPPD
        </a>
    </div>
</div>

<div class="sppd-page-wrapper">
    <div class="card sppd-card">
        <div class="sppd-card-header">
            <div>
                <h3 class="sppd-card-title">
                    <i class="fa fa-tasks text-warning"></i> Antrean Approval SPPD
                </h3>
                <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Klik ganda (double-click) pada baris pengajuan untuk memproses persetujuan.</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" id="SppdApprovalRefresh" class="btn btn-outline-secondary sppd-btn" onclick="SppdApprovalRefresh();">
                    <i class="fa fa-sync-alt"></i> Refresh Data
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="sppd-grid-container shadow-sm">
                <div id="SppdApprovalGrid" style="width:100%;height:450px;background-color:white;overflow:hidden;"></div>
            </div>
        </div>
    </div>
</div>

<script>
SppdApprovalGrid = new dhtmlXGridObject('SppdApprovalGrid');
SppdApprovalGrid.setImagePath("./dhtmlx/imgs/");
SppdApprovalGrid.setHeader("No,SPPD No,Requester,Department,Type,Destination,Rute,Biaya,Status,Stage / Approver,Tanggal Pengajuan");
SppdApprovalGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
SppdApprovalGrid.setInitWidths("45,150,150,150,110,150,180,140,110,190,150");
SppdApprovalGrid.setColAlign("center,left,left,left,left,left,left,right,center,left,center");
SppdApprovalGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
SppdApprovalGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
SppdApprovalGrid.setSkin("dhx_skyblue");
SppdApprovalGrid.init();
SppdApprovalGrid.clearAndLoad('hrd-sppd-approval-data');

function SppdApprovalRefresh() {
    SppdApprovalGrid.clearAndLoad('hrd-sppd-approval-data');
}
SppdApprovalGrid.attachEvent("onRowDblClicked", function (id) {
    location.href = 'hrd-sppd-approval-detail?ID=' + id;
});
</script>
@endsection
