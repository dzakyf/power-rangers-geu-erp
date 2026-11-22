@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
    <div class="mb-2 mb-md-0">
        <h4 class="content-title text-white fw-bold mb-1"
            style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
            <i class="fe fe-gift me-2"></i>Persetujuan Batch THR (Dynamic THR Approval)
        </h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ url('payroll-thr') }}" style="color: rgba(255, 255, 255, 0.8) !important;">
                        Perhitungan THR
                    </a>
                </li>
                <li class="breadcrumb-item active text-white" aria-current="page"
                    style="color: #ffffff !important; font-weight: 600;">
                    Approval Batch THR
                </li>
            </ol>
        </nav>
    </div>
    <div class="d-flex align-items-center flex-wrap gap-2">
        <a href="{{ url('hrd-approval-policy') }}" class="btn btn-sm rounded-pill shadow-sm"
            style="background: rgba(255, 255, 255, 0.25); border: 1px solid rgba(255, 255, 255, 0.6); color: #ffffff !important;"
            title="Atur tahapan dan jabatan approver Policy PAYROLL_THR">
            <i class="fe fe-sliders me-1"></i> Setup Policy (PAYROLL_THR)
        </a>
        <a href="{{ url('payroll-thr') }}" class="btn btn-sm rounded-pill shadow-sm"
            style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
            <i class="fe fe-gift me-1"></i> Perhitungan THR
        </a>
        <a href="{{ url('hrd-approval-inbox') }}" class="btn btn-sm rounded-pill shadow-sm"
            style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
            <i class="fe fe-inbox me-1"></i> Approval Inbox
        </a>
    </div>
</div>
<!-- End Main Content Header -->

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 pt-3 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-primary">
                <i class="fe fe-git-pull-request me-1"></i> Daftar Batch THR Menunggu Approval
            </h5>
            <small class="text-muted">
                Terintegrasi dengan <strong>Dynamic Approval Engine (<code>ApprovalWorkflowService</code>)</strong>. Klik ganda pada baris batch untuk melihat rincian karyawan, stepper tahapan, dan preview Payslip THR.
            </small>
        </div>
        <span class="badge bg-success text-white px-3 py-2 rounded-pill">
            <i class="fe fe-shield me-1"></i> Request Type: PAYROLL_THR
        </span>
    </div>
    <div class="card-body p-3">
        <div id="ThrApprovalGrid" style="width:100%; height:270px; background-color:white; overflow:hidden; border: 1px solid #e2e8f0; border-radius: 8px;"></div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-success rounded-pill px-4 text-white shadow-sm" onclick="approveThrBatch();">
                    <i class="fa fa-check-circle me-1"></i> Approve Tahap Ini
                </button>
                <button type="button" class="btn btn-outline-primary rounded-pill px-3" onclick="ThrApprovalKlik();">
                    <i class="fa fa-eye me-1"></i> Lihat Tahapan &amp; Rincian Karyawan
                </button>
                <button type="button" class="btn btn-warning rounded-pill px-3 text-dark" onclick="rejectThrBatch();">
                    <i class="fa fa-reply me-1"></i> Reject Batch
                </button>
                <button type="button" class="btn btn-danger rounded-pill px-3 text-white" onclick="cancelThrBatch();">
                    <i class="fa fa-times-circle me-1"></i> Batalkan Batch
                </button>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="refreshdata();">
                    <i class="fa fa-sync me-1"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<div id="divThrApprovalDetail"></div>

<script>
    function getSelectedThrBatchIdOrWarn() {
        var selectedId = ThrApprovalGrid.getSelectedId();
        if (!selectedId) {
            alert('Silakan pilih salah satu baris Batch THR pada tabel terlebih dahulu.');
            return null;
        }
        return selectedId;
    }

    function approveThrBatch(customBatchId) {
        var batchId = customBatchId || getSelectedThrBatchIdOrWarn();
        if (!batchId) return;

        var stageInfo = '';
        var batchNo = batchId;
        try {
            batchNo = ThrApprovalGrid.cells(batchId, 1).getValue() || batchId;
            stageInfo = ThrApprovalGrid.cells(batchId, 9).getValue();
        } catch (e) {}

        var note = prompt('Konfirmasi menyetujui (Approve) Batch THR [' + batchNo + ']\n' + (stageInfo ? 'Tahap Aktif: ' + stageInfo + '\n\n' : '\n') + 'Masukkan catatan persetujuan (opsional):', 'Approved');
        if (note !== null) {
            LoadM('divThrApprovalDetail', '{{ url('payroll-thr-approval-approve') }}?ID=' + encodeURIComponent(batchId) + '&notes=' + encodeURIComponent(note));
        }
    }

    function rejectThrBatch(customBatchId) {
        var batchId = customBatchId || getSelectedThrBatchIdOrWarn();
        if (!batchId) return;

        var batchNo = batchId;
        try {
            batchNo = ThrApprovalGrid.cells(batchId, 1).getValue() || batchId;
        } catch (e) {}

        var reason = prompt('Masukkan alasan penolakan (Reject) untuk Batch THR [' + batchNo + ']:', '');
        if (reason !== null) {
            if (reason.trim() === '') {
                alert('Alasan penolakan (Reject) wajib diisi.');
                return;
            }
            LoadM('divThrApprovalDetail', '{{ url('payroll-thr-approval-reject') }}?ID=' + encodeURIComponent(batchId) + '&reason=' + encodeURIComponent(reason));
        }
    }

    function cancelThrBatch(customBatchId) {
        var batchId = customBatchId || getSelectedThrBatchIdOrWarn();
        if (!batchId) return;

        var batchNo = batchId;
        try {
            batchNo = ThrApprovalGrid.cells(batchId, 1).getValue() || batchId;
        } catch (e) {}

        if (confirm('Konfirmasi pembatalan (Cancel) Batch THR [' + batchNo + ']?')) {
            LoadM('divThrApprovalDetail', '{{ url('payroll-thr-approval-cancel') }}?ID=' + encodeURIComponent(batchId));
        }
    }

    function ThrApprovalKlik() {
        var batchId = getSelectedThrBatchIdOrWarn();
        if (batchId) {
            LoadM('divThrApprovalDetail', '{{ url('payroll-thr-approval-detail') }}?ID=' + encodeURIComponent(batchId));
        }
        return true;
    }

    ThrApprovalGrid = new dhtmlXGridObject('ThrApprovalGrid');
    ThrApprovalGrid.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
    ThrApprovalGrid.setHeader("No.,Batch No,Periode,Event THR,Mode Pajak,Eligible,Bruto THR (Rp),PPh 21 (Rp),Nett THR (Rp),Tahap Approval,Approver Aktif,Created By,Status");
    ThrApprovalGrid.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#select_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#select_filter");
    ThrApprovalGrid.setInitWidths("45,165,80,180,95,70,125,110,125,220,150,95,140");
    ThrApprovalGrid.setColAlign("center,left,center,left,center,center,right,right,right,left,left,center,center");
    ThrApprovalGrid.setColTypes("ro,ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro");
    ThrApprovalGrid.setColSorting("int,str,str,str,str,int,int,int,int,str,str,str,str");
    ThrApprovalGrid.setNumberFormat("0,000", 6, ".", ",");
    ThrApprovalGrid.setNumberFormat("0,000", 7, ".", ",");
    ThrApprovalGrid.setNumberFormat("0,000", 8, ".", ",");
    ThrApprovalGrid.attachEvent("onRowDblClicked", ThrApprovalKlik);
    ThrApprovalGrid.setSkin("dhx_skyblue");
    ThrApprovalGrid.enableMultiline(true);
    ThrApprovalGrid.init();
    refreshdata();

    function refreshdata() {
        ThrApprovalGrid.clearAndLoad('{{ url('xml-payroll-thr-approval') }}');
    }
</script>
@endsection
