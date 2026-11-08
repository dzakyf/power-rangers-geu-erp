@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<style>
    .settlement-page-wrapper {
        margin-bottom: 2rem;
    }
    .settlement-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        overflow: hidden;
    }
    .settlement-card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .settlement-card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .settlement-grid-container {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .settlement-btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .settlement-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
</style>

<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            Travel, Expense & Settlement
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="#">Perjalanan Dinas</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Request Settlement</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-primary settlement-btn shadow-sm" onclick="SettlementNew();">
            <i class="fa fa-plus-circle"></i> Buat Pengajuan Settlement
        </button>
    </div>
</div>

<div class="settlement-page-wrapper">
    <div class="card settlement-card">
        <div class="settlement-card-header">
            <div>
                <h3 class="settlement-card-title">
                    <i class="fa fa-file-invoice-dollar text-primary"></i> Daftar Pengajuan Settlement SPPD & Expense
                </h3>
                <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Klik ganda (double-click) pada baris data untuk melihat rincian detail settlement.</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" id="SettlementRefresh" class="btn btn-outline-secondary settlement-btn" onclick="SettlementRefresh();">
                    <i class="fa fa-sync-alt"></i> Refresh Data
                </button>
                @if (App\Http\Controllers\HCGA\SettlementController::isHcgaVerifier())
                <button type="button" id="SettlementApproval" class="btn btn-warning text-dark settlement-btn" onclick="SettlementApproval();">
                    <i class="fa fa-clipboard-check"></i> Verifikasi Settlement
                </button>
                @endif
            </div>
        </div>
        <div class="card-body p-3">
            <div class="settlement-grid-container shadow-sm">
                <div id="SettlementGrid" style="width:100%;height:450px;background-color:white;overflow:hidden;"></div>
            </div>
        </div>
    </div>
</div>

<script>
SettlementGrid = new dhtmlXGridObject('SettlementGrid');
SettlementGrid.setImagePath("./dhtmlx/imgs/");
SettlementGrid.setHeader("No,No Settlement,Requester,Department,Ref SPPD,Jml Rincian,Total Biaya,Status,Invoice,Doc");
SettlementGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,&nbsp;,&nbsp;");
SettlementGrid.setInitWidths("45,170,160,150,150,90,140,110,110,120");
SettlementGrid.setColAlign("center,left,left,left,left,center,right,center,center,center");
SettlementGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,link,link");
SettlementGrid.setColSorting("int,str,str,str,str,str,str,str,str,str");
SettlementGrid.setSkin("dhx_skyblue");
SettlementGrid.init();
SettlementGrid.clearAndLoad('hrd-settlement-data');

function SettlementRefresh() {
    SettlementGrid.clearAndLoad('hrd-settlement-data');
}
function SettlementNew() {
    location.href = 'hrd-settlement-new';
}
function SettlementApproval() {
    location.href = 'hrd-settlement-approval-index';
}
SettlementGrid.attachEvent("onRowDblClicked", function (id) {
    location.href = 'hrd-settlement-detail?ID=' + id;
});
</script>
@endsection
