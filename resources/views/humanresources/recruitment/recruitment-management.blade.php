@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Human Resources</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Recruitment</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Dashboard Progress</span>
		</li>
	</ol>
</div>

<div class="row">
	<div class="col-12 mb-3">
		<div class="card">
			<div class="card-header m-2">
				<h5 class="card-title mb-0">Dashboard Progress</h5>
			</div>
			<div class="card-body">
				<div class="w-100" id="pivottrial"></div>
				<div id="RecruitmentPivotGrid" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			</div>
		</div>
	</div>
	<div class="col-12 mb-3">
		<div class="card" id="detailPTKCard" style="display:none">
			<div class="card-header m-2">
				<h5 class="card-title mb-0">Detail PTK</h5>
			</div>
			<div class="card-body">
				<div id="RecruitmentLists" style="width:auto;height:520px; background-color:white;overflow:hidden"></div>
				<div>
					<input type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="RecruitmentLists.toExcel('./export/generate.php');" />
					<input type="button" id="delets" name="delets" value="Cancel Selected Recruitment (Declined Invitation)" onclick="delsRecruitment()" class="btn btn-info mt-2 mr-2" />
				</div>
				<div id="divlistRecruitment"></div>
			</div>
		</div>
	</div>
</div>
<script>
	function RecruitmentListsKlik() {

		RecruitmentListsID = RecruitmentLists.getSelectedId();

		if (RecruitmentListsID != '' && RecruitmentListsID != null) {
			let splitID = RecruitmentListsID.split("|");
			let getFirst = RecruitmentLists.cells(RecruitmentListsID, 9).getValue();
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-document-approval-detail?ID=' + splitID[0]);
			}
			getFirst = RecruitmentLists.cells(RecruitmentListsID, 10).getValue();
			console.log(getFirst);
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-test-result-detail?ID=' + splitID[0]);
			}
			getFirst = RecruitmentLists.cells(RecruitmentListsID, 11).getValue();
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-lists-detail?ID=' + splitID[0]);
			}
			getFirst = RecruitmentLists.cells(RecruitmentListsID, 12).getValue();
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-interview-detail?ID=' + splitID[0]);
			}
			getFirst = RecruitmentLists.cells(RecruitmentListsID, 13).getValue();
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-candidate-analysis-detail?ID=' + splitID[0]);
			}
			getFirst = RecruitmentLists.cells(RecruitmentListsID, 14).getValue();
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-lists-detail?ID=' + splitID[0]);
			}
			getFirst = RecruitmentLists.cells(RecruitmentListsID, 15).getValue();
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-lists-detail?ID=' + splitID[0]);
			}
			getFirst = RecruitmentLists.cells(RecruitmentListsID, 16).getValue();
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-lists-detail?ID=' + splitID[0]);
			}
			getFirst = RecruitmentLists.cells(RecruitmentListsID, 17).getValue();
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-lists-detail?ID=' + splitID[0]);
			}
			// LoadM('divlistRecruitment','recruitment-lists-detail?ID=' + RecruitmentListsID);
		}
		return true;
	}

	function delsRecruitment() {
		dhtmlx.confirm('Cancel this Recruitment?', 'Recruitment', function(r) {

			if (r == true) {
				RecruitmentLists.deleteSelectedRows();
				PRecruitmentLists.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}
	RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
	RecruitmentLists.setImagePath("./dhtmlx/imgs/");
	RecruitmentLists.setHeader("No.,Recruitment Type,Name,Gender,PO No,Requisition,Last Education,Last Education Major,Recruiter,Seleksi Administrasi,Psikotest,Decline Invitation,Interview HR,Pending,Rejected,Interview User,Candidate Analysis,Decline Offered,Hired");
	RecruitmentLists.setInitWidths("70,100,100,100,100,200,100,100,100,120,60,60,60,60,60,60,60,60");
	RecruitmentLists.setColAlign("center,left,center,center,center,center,center,center,center,center,center,center");
	RecruitmentLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str");
	RecruitmentLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
	RecruitmentLists.enableKeyboardSupport(true);
	RecruitmentLists.setEditable('false');
	RecruitmentLists.attachEvent("onRowDblClicked", RecruitmentListsKlik);
	RecruitmentLists.setSkin("dhx_skyblue");
	PRecruitmentLists = new dataProcessor("recruitment-data-submit");
	PRecruitmentLists.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PRecruitmentLists.setTransactionMode("POST", true);
	PRecruitmentLists.setUpdateMode("Off");
	PRecruitmentLists.init(RecruitmentLists);
	RecruitmentLists.init();
	refreshdata();

	function refreshdata() {
		RecruitmentLists.clearAndLoad('xml-hrd-recruitment-management');
		RecruitmentLists.sortRows(0, "str", "des");

	}

	RecruitmentPivot = new dhtmlXGridObject('RecruitmentPivotGrid');
	RecruitmentPivot.setImagePath("./dhtmlx/imgs/");
	RecruitmentPivot.setHeader("PTK Number,Current Approver By,Seleksi Administrasi,Psikotes,Interview HR,Interview User,Tes Teknik,MCU,Offering Letter,Keterangan");
	RecruitmentPivot.setInitWidths("150,150,100,100,100,100,100,100,100,250");
	RecruitmentPivot.setColAlign("left,left,center,center,center,center,center,center,center,left");
	RecruitmentPivot.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	RecruitmentPivot.setColSorting("str,str,int,int,int,int,int,int,int,str");
	RecruitmentPivot.enableKeyboardSupport(true);
	RecruitmentPivot.setEditable('false');
	RecruitmentPivot.setSkin("dhx_skyblue");
	RecruitmentPivot.attachEvent("onRowDblClicked", function(id, ind) {
		document.getElementById('detailPTKCard').style.display = '';
		setTimeout(function() {
			RecruitmentLists.setSizes();
			RecruitmentLists.clearAndLoad('xml-hrd-recruitment-management?Requisition_ID=' + encodeURIComponent(id));
			RecruitmentLists.sortRows(0, "str", "des");
		}, 50);
	});
	RecruitmentPivot.init();
	RecruitmentPivot.load('xml-hrd-recruitment-pivot');
</script>
<script>
	var derivers = $.pivotUtilities.derivers;
	let json = `{!! json_encode($rows->values()) !!}`;
	$('#pivottrial').pivotUI(JSON.parse(json), {
		rows: ["no_ptk", "Date"], // Date nested under no_ptk gives you the multi-date-per-ACC behavior
		cols: ["Stage"], // <-- Stage now becomes actual table columns
		aggregatorName: "Sum",
		vals: ["Count"],
		rendererName: "Table"
	});
</script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/tips_data.min.js"></script>
@endsection