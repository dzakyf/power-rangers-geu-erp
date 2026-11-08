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
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        .stats-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .stats-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
    </style>

    <div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
        <div>
            <h4 class="content-title fw-bold mb-1" style="font-size: 1.35rem; letter-spacing: 0.3px;">
                <i class="fa fa-receipt text-white me-2"></i> Klaim & Reimbursement
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#">Human Capital</a></li>
                    <li class="breadcrumb-item"><a href="#">Kompensasi & Benefit</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="font-weight: 600;">Reimbursement
                        Management</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary rmb-btn shadow-sm" onclick="ReimbursementNew();">
                <i class="fa fa-plus-circle"></i> Buat Request Reimbursement
            </button>
        </div>
    </div>

    <div class="rmb-page-wrapper">
        <div class="card rmb-card">
            <div class="rmb-card-header">
                <div>
                    <h3 class="rmb-card-title">
                        <i class="fa fa-list-alt text-primary"></i> Daftar Riwayat Pengajuan Reimbursement
                    </h3>
                    <small class="text-muted"><i class="fa fa-info-circle me-1"></i> Klik ganda (double-click) pada baris
                        data untuk membuka rincian dokumen reimbursement.</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="ReimbursementRefresh" class="btn btn-outline-secondary rmb-btn"
                        onclick="ReimbursementRefresh();">
                        <i class="fa fa-sync-alt"></i> Refresh Data
                    </button>
                    @if (\App\Http\Controllers\HCGA\ReimbursementApprovalController::isApprover())
                    <button type="button" id="ReimbursementApproval" class="btn btn-warning text-dark rmb-btn"
                        onclick="ReimbursementApproval();">
                        <i class="fa fa-check-double"></i> Approval Reimbursement
                    </button>
                    @endif
                </div>
            </div>
            <div class="card-body p-3">
                <div class="rmb-grid-container shadow-sm">
                    <div id="ReimbursementGrid" style="width:100%;height:450px;background-color:white;overflow:hidden;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        ReimbursementGrid = new dhtmlXGridObject('ReimbursementGrid');
        ReimbursementGrid.setImagePath("./dhtmlx/imgs/");
        ReimbursementGrid.setHeader(
            "No,No Reimbursement,Tanggal,Departemen,Keterangan / Keperluan,Total Biaya (Rp),Status,Tanggal Submit,Dibuat Oleh"
        );
        ReimbursementGrid.attachHeader(
            "#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter"
        );
        ReimbursementGrid.setInitWidths("45,180,105,150,260,140,130,140,110");
        ReimbursementGrid.setColAlign("center,left,center,left,left,right,center,center,center");
        ReimbursementGrid.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
        ReimbursementGrid.setColSorting("int,str,str,str,str,int,str,str,str");
        ReimbursementGrid.setSkin("dhx_skyblue");
        ReimbursementGrid.init();
        ReimbursementGrid.clearAndLoad('hrd-reimbursement-data');

        function ReimbursementRefresh() {
            ReimbursementGrid.clearAndLoad('hrd-reimbursement-data');
        }

        function ReimbursementNew() {
            location.href = 'hrd-reimbursement-new';
        }

        function ReimbursementApproval() {
            location.href = 'hrd-reimbursement-approval-index';
        }
        ReimbursementGrid.attachEvent("onRowDblClicked", function(id) {
            location.href = 'hrd-reimbursement-detail?ID=' + id;
        });
    </script>
@endsection
