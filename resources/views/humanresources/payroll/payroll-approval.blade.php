@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<!-- Main Content Header (Adminor V3 Responsive Hero Header) -->
<div class="breadcrumb-header justify-content-between align-items-center mt-3 mb-4 pt-1 pb-2">
	<div class="mb-2 mb-md-0">
		<h4 class="content-title text-white fw-bold mb-1"
			style="color: #ffffff !important; font-size: 1.35rem; letter-spacing: 0.3px;">
			<i class="fe fe-check-square me-2"></i>Persetujuan Penggajian (Dynamic Payroll Approval)
		</h4>
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb mb-0">
				<li class="breadcrumb-item">
					<a href="{{ url('payroll-tax') }}" style="color: rgba(255, 255, 255, 0.8) !important;">
						Payroll
					</a>
				</li>
				<li class="breadcrumb-item active text-white" aria-current="page"
					style="color: #ffffff !important; font-weight: 600;">
					Batch Approval (Dynamic Policy)
				</li>
			</ol>
		</nav>
	</div>
	<div class="d-flex align-items-center flex-wrap gap-2">
		<a href="{{ url('hrd-approval-policy') }}" class="btn btn-sm rounded-pill shadow-sm"
			style="background: rgba(255, 255, 255, 0.25); border: 1px solid rgba(255, 255, 255, 0.6); color: #ffffff !important;"
			title="Atur tahapan, jabatan approver, dan kondisi nominal Policy PAYROLL secara dinamis">
			<i class="fe fe-sliders me-1"></i> Setup Policy Approval (PAYROLL)
		</a>
		<a href="{{ url('payroll-generator') }}" class="btn btn-sm rounded-pill shadow-sm"
			style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
			<i class="fe fe-play me-1"></i> Generator Payroll
		</a>
		<a href="{{ url('approved-payroll') }}" class="btn btn-sm rounded-pill shadow-sm"
			style="background: rgba(255, 255, 255, 0.2); border: 1px solid rgba(255, 255, 255, 0.5); color: #ffffff !important;">
			<i class="fe fe-archive me-1"></i> Arsip Approved
		</a>
	</div>
</div>
<!-- End Main Content Header -->

<div class="card border-0 shadow-sm rounded-4 mb-4">
	<div class="card-header bg-white border-0 pt-3 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
		<div>
			<h5 class="fw-bold mb-0 text-primary">
				<i class="fe fe-git-Pull-request me-1"></i>Daftar Batch Payroll Menunggu Approval
			</h5>
			<small class="text-muted">
				Tahapan persetujuan terintegrasi penuh dengan <strong>Dynamic Approval Engine (<code>ApprovalWorkflowService</code>)</strong>. Klik ganda pada baris batch untuk melihat alur tahapan & rincian slip karyawan.
			</small>
		</div>
		<span class="badge bg-info text-white px-3 py-2 rounded-pill">
			<i class="fe fe-shield me-1"></i>Request Type: PAYROLL
		</span>
	</div>
	<div class="card-body p-3">
		<div id="PayrollApproval" style="width:100%; height:270px; background-color:white; overflow:hidden; border: 1px solid #e2e8f0; border-radius: 8px;"></div>
		<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3" id="CekReload">
			<div class="d-flex flex-wrap gap-2">
				<button type="button" id="apprv2" class="btn btn-success rounded-pill px-4 text-white shadow-sm" onclick="approvesalary();">
					<i class="fa fa-check-circle me-1"></i> Approve Tahap Ini
				</button>
				<button type="button" class="btn btn-outline-primary rounded-pill px-3" onclick="PayrollApprovalKlik();">
					<i class="fa fa-eye me-1"></i> Lihat Tahapan & Rincian Karyawan
				</button>
				<button type="button" class="btn btn-warning rounded-pill px-3 text-dark" onclick="rejectsalary();">
					<i class="fa fa-reply me-1"></i> Reject Batch
				</button>
				<button type="button" id="ekselPODListDet2" class="btn btn-danger rounded-pill px-3 text-white" onclick="batalkansalary();">
					<i class="fa fa-times-circle me-1"></i> Batalkan Batch
				</button>
			</div>
			<div class="d-flex flex-wrap gap-2">
				<button type="button" id="ekselPODListDet" class="btn btn-outline-success rounded-pill px-3" onclick="PayrollApproval.toExcel('{{ asset('export/generate.php') }}');">
					<i class="fa fa-file-excel me-1"></i> Export Excel
				</button>
				<button type="button" class="btn btn-outline-secondary rounded-pill px-3" onclick="refreshdata();">
					<i class="fa fa-sync me-1"></i> Refresh
				</button>
			</div>
		</div>
	</div>
</div>

<div id="divlistProjectOrgx"></div>

<script>
	function getSelectedBatchIdOrWarn() {
		var selectedId = PayrollApproval.getSelectedId();
		if (!selectedId) {
			alert('Silakan pilih salah satu baris Batch Payroll pada tabel terlebih dahulu.');
			return null;
		}
		return selectedId;
	}

	function approvesalary(customBatchId) {
		var batchId = customBatchId || getSelectedBatchIdOrWarn();
		if (!batchId) return;

		var stageInfo = '';
		try {
			stageInfo = PayrollApproval.cells(batchId, 6).getValue();
		} catch (e) {}

		var note = prompt('Konfirmasi menyetujui (Approve) Batch Payroll [' + batchId + ']\n' + (stageInfo ? 'Tahap Aktif: ' + stageInfo + '\n\n' : '\n') + 'Masukkan catatan persetujuan (opsional):', 'Approved');
		if (note !== null) {
			LoadM('divlistProjectOrgx', '{{ url('payroll-approval-approve') }}?ID=' + encodeURIComponent(batchId) + '&notes=' + encodeURIComponent(note));
		}
	}

	function rejectsalary(customBatchId) {
		var batchId = customBatchId || getSelectedBatchIdOrWarn();
		if (!batchId) return;

		var reason = prompt('Masukkan alasan penolakan (Reject) untuk Batch Payroll [' + batchId + ']:', '');
		if (reason !== null) {
			if (reason.trim() === '') {
				alert('Alasan penolakan (Reject) wajib diisi.');
				return;
			}
			LoadM('divlistProjectOrgx', '{{ url('payroll-approval-reject') }}?ID=' + encodeURIComponent(batchId) + '&reason=' + encodeURIComponent(reason));
		}
	}

	function batalkansalary(customBatchId) {
		var batchId = customBatchId || getSelectedBatchIdOrWarn();
		if (!batchId) return;

		dhtmlx.confirm('Konfirmasi pembatalan (Cancel) Batch Payroll [' + batchId + '] ini? Seluruh penguncian lembur/KJK pada batch ini akan dilepaskan.', 'Payroll Approval', function(r) {
			if (r == true) {
				LoadM('divlistProjectOrgx', '{{ url('payroll-approval-cancel') }}?ID=' + encodeURIComponent(batchId));
				return true;
			}
		});
	}

	function PayrollApprovalKlik() {
		var batchId = getSelectedBatchIdOrWarn();
		if (batchId) {
			LoadM('divlistProjectOrgx', '{{ url('payroll-approval-detail') }}?ID=' + encodeURIComponent(batchId));
		}
		return true;
	}

	PayrollApproval = new dhtmlXGridObject('PayrollApproval');
	PayrollApproval.setImagePath("{{ asset('dhtmlx/imgs/') }}/");
	PayrollApproval.setHeader("No.,Salary ID,Period,Total (Rp),Category,Use_For,Tahap Approval (Workflow),Approver Aktif,Note,Created By,Created Date,Status");
	PayrollApproval.attachHeader("#rspan,#text_filter,#text_filter,#numeric_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter");
	PayrollApproval.setInitWidths("50,185,80,125,90,90,235,160,130,95,130,145");
	PayrollApproval.setColAlign("center,left,center,right,center,center,left,left,left,center,center,center");
	PayrollApproval.setColTypes("ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro");
	PayrollApproval.setColSorting("int,str,str,int,str,str,str,str,str,str,str,str");
	PayrollApproval.setNumberFormat("0,000", 3, ".", ",");
	PayrollApproval.attachEvent("onRowDblClicked", PayrollApprovalKlik);
	PayrollApproval.setSkin("dhx_skyblue");
	PayrollApproval.enableMultiline(true);
	PayrollApproval.init();
	refreshdata();

	function refreshdata() {
		PayrollApproval.clearAndLoad('{{ url('xml-payroll-approval') }}');
	}
</script>
@endsection
