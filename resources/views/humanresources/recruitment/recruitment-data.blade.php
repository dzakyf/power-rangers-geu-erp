<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;



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
			<span class="active">Data Management</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="RecruitmentLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="RecruitmentLists.toExcel('./export/generate.php');" />
		<input type="button" id="delets" name="delets" value="Cancel Selected Recruitment" onclick="delsRecruitment()" class="btn btn-info mt-2 mr-2" onclick="delsRecruitment()" />

	</div>

</div>
<div id="divlistRecruitment"></div>
<script>
	function RecruitmentListsKlik() {

		RecruitmentListsID = RecruitmentLists.getSelectedId();


		if (RecruitmentListsID != '') {
			LoadM('divlistRecruitment', 'recruitment-lists-detail?ID=' + RecruitmentListsID);
		}
		return true;
	}
	RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
	RecruitmentLists.setImagePath("./dhtmlx/imgs/");
	RecruitmentLists.setHeader("No.,KTP,Name,Gender,Birth Date,Last Education,Last Education Major,Institution,Status,Remaining Time(days)");
	RecruitmentLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	RecruitmentLists.setInitWidths("70,150,100,100,200,100,100,100,100,100");
	RecruitmentLists.setColAlign("center,left,center,center,center,center,center,center,center,center");
	RecruitmentLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	RecruitmentLists.setColSorting("int,str,str,str,str,str,str,str,str,str");
	RecruitmentLists.enableKeyboardSupport(true);
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
	RecruitmentLists.attachEvent("onRowDblClicked", RecruitmentListsKlik);
	RecruitmentLists.setSkin("dhx_skyblue");
	RecruitmentLists.init();
	refreshdata();

	function refreshdata() {
		RecruitmentLists.clearAndLoad('xml-recruitment-data');

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


	function UpdateGrid_Recruitment() {
		var out = 0;
		for (var i = 0; i < RecruitmentLists.getRowsNum(); i++) {
			rowIdMov = RecruitmentLists.getRowId(i);
			RecruitmentListsProc.setUpdated(rowIdMov, true);
		}
		return out;
	}

	function SubmitGrid_Recruitment() {
		RecruitmentListsProc.sendData();
		alert('Data Successfully Updated!');
		RecruitmentLists.clearAll();
		refreshdata();
	}
</script>
@endsection