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
            Surat Perintah Perjalanan Dinas (SPPD)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="#">Perjalanan Dinas</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Request SPPD</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-primary sppd-btn shadow-sm" onclick="SppdNew();">
            <i class="fa fa-plus-circle"></i> Buat Pengajuan SPPD
        </button>
    </div>
</div>

<div class="sppd-page-wrapper">
    <div class="card sppd-card">
        <div class="sppd-card-header">
            <div>
                <h3 class="sppd-card-title">
                    <i class="fa fa-list text-primary"></i> Daftar Pengajuan SPPD
                </h3>
                <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Klik ganda (double-click) pada baris data untuk membuka rincian SPPD.</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" id="SppdRefresh" class="btn btn-outline-secondary sppd-btn" onclick="SppdRefresh();">
                    <i class="fa fa-sync-alt"></i> Refresh
                </button>
                @if (App\Http\Controllers\HCGA\SppdController::isApprover())
                <button type="button" id="SppdApproval" class="btn btn-warning text-dark sppd-btn" onclick="SppdApproval();">
                    <i class="fa fa-check-double"></i> Approval SPPD
                </button>
                @endif
                <button type="button" id="SppdMaster" class="btn btn-dark sppd-btn" onclick="SppdMaster();">
                    <i class="fa fa-book"></i> Master Regulasi
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="sppd-grid-container shadow-sm">
                <div id="SppdGrid" style="width:100%;height:450px;background-color:white;overflow:hidden;"></div>
            </div>
        </div>
    </div>
</div>

<script>
    SppdGrid = new dhtmlXGridObject('SppdGrid');
    SppdGrid.setImagePath("./dhtmlx/imgs/");
    SppdGrid.setHeader("No,SPPD No,Requester,Department,Type,Destination,Route,Kegiatan,Schedule Date,Estimated Cost,Status,Doc Pengajuan,Doc Persetujuan");
    SppdGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#span,#span");
    SppdGrid.setInitWidths("45,145,145,140,110,150,170,135,115,130,115,115,135");
    SppdGrid.setColAlign("center,left,left,left,left,left,left,left,center,right,center,center,center");
    SppdGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,link,link");
    SppdGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
    SppdGrid.setSkin("dhx_skyblue");
    SppdGrid.init();
    SppdGrid.clearAndLoad('hrd-sppd-data');

    function SppdRefresh() {
        SppdGrid.clearAndLoad('hrd-sppd-data');
    }

    function SppdNew() {
        location.href = 'hrd-sppd-new';
    }

    function SppdApproval() {
        location.href = 'hrd-sppd-approval-index';
    }

    function SppdMaster() {
        location.href = 'hrd-sppd-master-index';
    }
    SppdGrid.attachEvent("onRowDblClicked", function(id) {
        location.href = 'hrd-sppd-detail?ID=' + id;
    });
</script>
@endsection