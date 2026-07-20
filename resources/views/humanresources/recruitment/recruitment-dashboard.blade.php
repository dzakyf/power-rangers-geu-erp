<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$getRecruit = DB::table('recruitment')
	->select("requisition.Department", "requisition.Job_Position as Position", "recruitment.Status", "recruitment.Name")
	->leftJoin("requisition", "recruitment.Requisition_ID", "requisition.Task_ID")
	->where('recruitment.Status', '!=', "CLOSED")
	->where('recruitment.Location', $LSP)
	->get();

?>

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
			<span class="active">Dashboard</span>
		</li>
	</ol>
</div>

<div class="card">
	<div class="card-body">
		<div class="row no-gutters">
			<div class="col-12">
				<div class="w-100" id="pivottrial"></div>
			</div>
		</div>
	</div>
</div>
<div id="RecruitmentLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
<div id="divlistRecruitment"></div>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot.min.js"></script> --}}
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/c3_renderers.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/d3_renderers.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/gchart_renderers.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/plotly_renderers.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/pivot_spec.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pivottable/2.23.0/tips_data.min.js"></script>
<script>
	// $(function() {
	// })
	var derivers = $.pivotUtilities.derivers;
	let json = `<?= json_encode($getRecruit) ?>`;


	$('#pivottrial').pivotUI(JSON.parse(json));

	function RecruitmentListsKlik() {

		RecruitmentListsID = RecruitmentLists.getSelectedId();

		if (RecruitmentListsID != '' && RecruitmentListsID != null) {
			let splitID = RecruitmentListsID.split("|");
			let getFirst = RecruitmentLists.cells(RecruitmentListsID, 9).getValue();
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-document-detail?ID=' + splitID[0]);
			}
			getFirst = RecruitmentLists.cells(RecruitmentListsID, 10).getValue();
			console.log(getFirst);
			if (getFirst == '1') {
				LoadM('divlistRecruitment', 'recruitment-test-results-detail?ID=' + splitID[0]);
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
	RecruitmentLists.setHeader("No.,Recruitment Type,Name,Gender,PO No,Requisition,Last Education,Last Education Major,Recruiter,Document Completion,Test,Decline Invitation,Interviewed,Pending,Rejected,Business Approval,Candidate Analysis,Decline Offered,Hired");

	RecruitmentLists.setInitWidths("70,100,100,100,100,200,100,100,100,120,60,60,60,60,60,60,0,60,60");
	RecruitmentLists.setColAlign("center,left,center,center,center,center,center,center,center,center,center,center,center,center,center,center,center,center,center");
	RecruitmentLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str");
	RecruitmentLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
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
</script>
@endsection