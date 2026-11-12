@extends('layout.mainlayout-bp')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Partner</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Survey</span>
		</li>
	</ol>
</div>


<div id="Approval" class="row m-1">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">APPROVAL SURVEY</div>
	<div class="card">
		<div class="card-body">
			<!-- <div id="MembershipGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div> -->
			<div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
			<div>
				<input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Log" class="btn btn-info mt-2 mr-2" onclick="doubleClickApproval()" />
				<input type="button" value="Detail Survey" class="btn btn-primary mt-2 mr-2" onclick="openSurveyDetail()" />
				<!-- <input type="button" id="showProgress" name="showProgress" value="Show Progress (Mitra)" class="btn btn-info mt-2 mr-2" onclick="redirectToOnboardingApprovalProgress()" /> -->

				<!-- <input type="button" id="pengajuan" name="pengajuan" value="Buat Data" class="btn btn-success mt-2 mr-2" onclick="redirectToCreateApplication()" /> -->
				<!-- <input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" /> -->

				<div class="btn-group mt-2 mr-2" role="group" aria-label="Button group with nested dropdown">
					<div class="btn-group" role="group">
						<button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Action
						</button>
						<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
							<a class="dropdown-item" href="#" onclick="approveClick('APPROVED')">Approve</a>
							<a class="dropdown-item" href="#" onclick="approveClick('REJECTED')">Reject</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>

<div id="divSurveyEdit" style="display:none;">
	@include('partner.survey.edit')
</div>

<div id="ApprovalLog" class="row m-1" style="display:none">
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LOG APPROVAL SURVEY</div>
	<div class="card">
		<div class="card-body">
			<div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;" class=" mt-2"></div>
			<div>
			</div>
		</div>
	</div>
</div>

<!-- 
<div class="row m-1">
    <input type="button" id="pengajuan" name="pengajuan" value="Buat Pengajuan" class="btn btn-success mb-2" onclick="redirectToCreateApplication()" />
    <div id="PartnerGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mb-2"></div>
    <div class="row col-md-12" style="width:auto;height:40px">
        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
            <div class="btn-group" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Action
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                    <a class="dropdown-item" href="#" onclick="approveClick('APPROVED')">Approve</a>
                    <a class="dropdown-item" href="#" onclick="approveClick('REJECTED')">Reject</a>
                </div>
            </div>
        </div>
    </div>
    <div id="ProjectionGrid" style="width:auto;height:320px; background-color:white;overflow:hidden" class=" mt-2"></div>
</div> -->

<script>
	var apprId = null;
	var opportunityId = null;
	$(document).ready(function() {
		initApprovalGrid()
	})


	function approveClick(action) {
		if (!apprId) {
			alert('Pilih data terlebih dahulu.');
			return;
		}

		let formData = new FormData();

		formData.append('id', apprId);
		formData.append('_token', '{{ csrf_token() }}')
		formData.append('action', action)

		$.ajax({
			url: "partner-survey-approval-submit",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			cache: false,
			success: function(response) {
				alert(action === 'REJECTED' ? 'Berhasil reject' : 'Berhasil approve');
				location.reload();
			},
			error: function(xhr, status, error) {
				// Surface the server's actual reason (wewenang, duplicate entry,
				// already processed, account creation failure, ...)
				var msg = (xhr.responseJSON && xhr.responseJSON.message)
					? xhr.responseJSON.message
					: "Telah terjadi kesalahan. Silakan coba lagi.";
				alert(msg);
			},
			complete: function() {
				// $('#submitBtn').prop('disabled', false).text('Submit');
			}
		});
	}

	function initApprovalGrid() {
		let tHeader = ['No', 'Nama', 'Task ID', 'Tipe Approval', 'Status', 'Disubmit Pada']

		let tWidth = tHeader.map((e) => '200')
		console.log()
		let headerAttachment = [
			'#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
		]
		PartnerGrid = new dhtmlXGridObject('PartnerGrid');
		PartnerGrid.setImagePath("./dhtmlx/imgs/");
		PartnerGrid.setHeader(tHeader.join(','));
		PartnerGrid.setInitWidths(tWidth.join(","));
		// PartnerGrid.enableAutoWidth(true);
		PartnerGrid.attachHeader(headerAttachment);
		// ApprovalLogGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
		// ApprovalLogGrid.setColAlign("center,left,left,left,left");
		// ApprovalLogGrid.setColSorting("int,str,str,str,str");
		// ApprovalLogGrid.setColTypes("ro,ro,ro,ro,ro");
		// ApprovalLogGrid.enableSmartRendering(true, 150);
		// ApprovalLogGrid.setNumberFormat("0,000", 7, ".", ",");
		// PartnerGrid.enableAutoWidth(true);
		PartnerGrid.attachEvent("onRowDblClicked", doubleClickApproval);
		PartnerGrid.attachEvent("onRowSelect", selectMitra);
		PartnerGrid.setSkin("dhx_skyblue");
		PartnerGrid.init();
		PartnerGrid.clearAndLoad(`partner-approval-data?user_id=${apprId}&type=SURVEY`);
	}

	function initApprovalLogGrid() {
		let tHeader = [
			'No', 'Status', 'Deskripsi', 'Tipe', 'Requester', 'Approved Oleh', 'Diapprove pada'
		]

		let tWidth = tHeader.map((e) => '110')
		let headerAttachment = [
			'#rspan', '#text_filter', '#text_filter', '#text_filter', '#text_filter', '#text_filter'
		]
		ApprovalLogGrid = new dhtmlXGridObject('ProjectionGrid');
		ApprovalLogGrid.setImagePath("./dhtmlx/imgs/");
		ApprovalLogGrid.setHeader(tHeader.join(','));
		ApprovalLogGrid.setInitWidths(tWidth.join(','));
		// ApprovalLogGrid.enableAutoWidth(true);
		ApprovalLogGrid.attachHeader(headerAttachment);
		// ApprovalLogGrid.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
		// ApprovalLogGrid.setColAlign("center,left,left,left,left");
		// ApprovalLogGrid.setColSorting("int,str,str,str,str");
		// ApprovalLogGrid.setColTypes("ro,ro,ro,ro,ro");
		// ApprovalLogGrid.enableSmartRendering(true, 150);
		// ApprovalLogGrid.setNumberFormat("0,000", 7, ".", ",");
		// ApprovalLogGrid.attachEvent("onRowDblClicked", ProjectionGridKlik);
		ApprovalLogGrid.setSkin("dhx_skyblue");
		ApprovalLogGrid.init();
		ApprovalLogGrid.clearAndLoad(`partner-approval-data-log?appr_id=${apprId}&user_id=&type=SURVEY`);
	}

	function selectMitra(rowId, cellIdx) {

		var mapperId = PartnerGrid.getRowAttribute(rowId, 'Mapper_Id');
		var opptId = PartnerGrid.getRowAttribute(rowId, 'Oppt_Id');

		apprId = rowId;
		opportunityId = opptId
	}

	function doubleClickApproval(rowId) {
		if (rowId) {
			apprId = rowId;
		}
		if (!apprId) {
			alert('Pilih data terlebih dahulu.');
			return;
		}
		$('#ApprovalLog').show();
		if (typeof ApprovalLogGrid === 'undefined' || !ApprovalLogGrid) {
			initApprovalLogGrid();
		} else {
			ApprovalLogGrid.clearAndLoad(`partner-approval-data-log?appr_id=${apprId}&user_id=&type=SURVEY`);
			ApprovalLogGrid.setSizes();
		}
	}

	function openSurveyDetail() {
		var selectedId = PartnerGrid.getSelectedId();
		if (!selectedId) {
			alert('Pilih data terlebih dahulu.');
			return;
		}
		var mapperId = PartnerGrid.getRowAttribute(selectedId, 'Mapper_Id');
		if (!mapperId) {
			alert('Data survey tidak ditemukan.');
			return;
		}
		$('#divSurveyEdit').show();
		$('html, body').animate({ scrollTop: $('#divSurveyEdit').offset().top }, 400);
		getSurveyData(mapperId);
	}
</script>
@endsection