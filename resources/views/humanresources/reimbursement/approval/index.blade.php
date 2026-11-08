@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<style>
    .rmb-page-wrapper {
        margin-bottom: 2rem;
    }
    .rmb-card {
        border: none;
        border-radius: 14px;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.04);
        background: #ffffff;
        overflow: hidden;
    }
    .rmb-card-header {
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .rmb-card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .rmb-grid-container {
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .rmb-btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.5rem 1.1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        transition: all 0.2s ease;
    }
    .rmb-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }
</style>

<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div>
        <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
            <i class="fa fa-tasks text-warning me-2"></i> Approval Klaim & Reimbursement
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                <li class="breadcrumb-item"><a href="hrd-reimbursement-index">Reimbursement</a></li>
                <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Approval Reimbursement</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="hrd-reimbursement-index" class="btn btn-outline-secondary rmb-btn">
            <i class="fa fa-arrow-left"></i> Kembali ke Request
        </a>
    </div>
</div>

<div class="rmb-page-wrapper">
    <div class="card rmb-card">
        <div class="rmb-card-header">
            <div>
                <h3 class="rmb-card-title">
                    <i class="fa fa-inbox text-warning"></i> Antrean Approval Reimbursement
                </h3>
                <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Klik ganda (double-click) pada baris data untuk membuka formulir persetujuan.</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" id="ReimbursementApprovalRefresh" class="btn btn-outline-secondary rmb-btn" onclick="ReimbursementApprovalRefresh();">
                    <i class="fa fa-sync-alt"></i> Refresh Data
                </button>
            </div>
        </div>
        <div class="card-body p-3">
            <div class="rmb-grid-container shadow-sm">
                <div id="ReimbursementApprovalGrid" style="width:100%;height:450px;background-color:white;overflow:hidden;"></div>
            </div>
        </div>
    </div>
</div>

<script>
ReimbursementApprovalGrid = new dhtmlXGridObject('ReimbursementApprovalGrid');
ReimbursementApprovalGrid.setImagePath("./dhtmlx/imgs/");
ReimbursementApprovalGrid.setHeader("No,No Reimbursement,Pemohon,Departemen,Keterangan / Keperluan,Total (Rp),Status,Stage / Approver,Tanggal Pengajuan");
ReimbursementApprovalGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ReimbursementApprovalGrid.setInitWidths("45,170,150,140,240,130,120,180,130");
ReimbursementApprovalGrid.setColAlign("center,left,left,left,left,right,center,left,center");
ReimbursementApprovalGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
ReimbursementApprovalGrid.setColSorting("int,str,str,str,str,int,str,str,str");
ReimbursementApprovalGrid.setSkin("dhx_skyblue");
ReimbursementApprovalGrid.init();
ReimbursementApprovalGrid.clearAndLoad('hrd-reimbursement-approval-data');

function ReimbursementApprovalRefresh() {
    ReimbursementApprovalGrid.clearAndLoad('hrd-reimbursement-approval-data');
}

ReimbursementApprovalGrid.attachEvent("onRowDblClicked", function (id) {
    location.href = 'hrd-reimbursement-approval-detail?ID=' + id;
});
</script>
@endsection

