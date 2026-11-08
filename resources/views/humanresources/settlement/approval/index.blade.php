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
            Verifikasi Request Settlement
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('hrd-settlement-index') }}">Settlement</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Verifikasi HCGA Dept Head</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="hrd-settlement-index" class="btn btn-outline-secondary settlement-btn">
            <i class="fa fa-arrow-left"></i> Kembali ke List
        </a>
    </div>
</div>

<div class="settlement-page-wrapper">
    <div class="card settlement-card">
        <div class="settlement-card-header">
            <div>
                <h3 class="settlement-card-title">
                    <i class="fa fa-clipboard-check text-warning"></i> Antrian Verifikasi Settlement
                </h3>
                <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Klik ganda (double-click) pada baris data untuk memproses verifikasi atau penolakan pengajuan.</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" id="SettlementApprovalRefresh" class="btn btn-outline-secondary settlement-btn" onclick="SettlementApprovalRefresh();">
                    <i class="fa fa-sync-alt"></i> Refresh Data
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="settlement-grid-container shadow-sm">
                <div id="SettlementApprovalGrid" style="width:100%;height:450px;background-color:white;overflow:hidden;"></div>
            </div>
        </div>
    </div>
</div>

<script>
SettlementApprovalGrid = new dhtmlXGridObject('SettlementApprovalGrid');
SettlementApprovalGrid.setImagePath("./dhtmlx/imgs/");
SettlementApprovalGrid.setHeader("No,No Settlement,Requester,Department,Jml Rincian,Total Biaya,Tanggal Pengajuan");
SettlementApprovalGrid.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
SettlementApprovalGrid.setInitWidths("45,180,180,170,110,160,180");
SettlementApprovalGrid.setColAlign("center,left,left,left,center,right,center");
SettlementApprovalGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro");
SettlementApprovalGrid.setColSorting("int,str,str,str,str,str,str");
SettlementApprovalGrid.setSkin("dhx_skyblue");
SettlementApprovalGrid.init();
SettlementApprovalGrid.clearAndLoad('hrd-settlement-approval-data');

function SettlementApprovalRefresh() {
    SettlementApprovalGrid.clearAndLoad('hrd-settlement-approval-data');
}
SettlementApprovalGrid.attachEvent("onRowDblClicked", function (id) {
    location.href = 'hrd-settlement-approval-detail?ID=' + id;
});
</script>
@endsection
