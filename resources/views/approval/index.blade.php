<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
// $LSP = "GEU-HO";
// $Saya = "admin";
?>

@extends('layout.mainlayout-mitra')

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
			<span class="active">Approval</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="OpportunityLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
        <input type="button" id="ekselOpportunityListsDet" name="ekselOpportunityListsDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OpportunityLists.toExcel('./export/generate.php');" />
		<input type="button" id="detailBP" name="detailBP" value="Detail Leads" class="btn btn-success mt-2 mr-2" onclick="detailBP()" />
		<div class="btn-group" role="group">
    <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Action
    </button>
    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
      <a class="dropdown-item" href="#" onclick="submitApproval('APPROVED')">Approve</a>
      <a class="dropdown-item" href="#" onclick="submitApproval('REJECTED')">Reject</a>
    </div>
  </div>
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script>
	function submitApproval(type){
		let fd = new FormData()
		fd.append('id', OpportunityLists.getSelectedId());
		fd.append('type', type)
		fd.append('_token', '{{ csrf_token() }}')
		
		$.ajax({
			url: "approval/submit",
			type: "POST",
			processData: false,
			contentType: false,
			data: fd,
			success: function(response, status, xhr) {
				alert("Berhasil melakukan aksi");
			},
			error: function(err){
				alert("Telah terjadi kesalahan.");
			}
		})
	}

	function generateMOU(){
		$.ajax({
			url: "approval/generate-mou",
			type: "GET",
			xhrFields:{
				responseType: 'blob'
			},
			success: function(response, status, xhr) {
				var blob = new Blob([response]);
				var link = document.createElement('a');
				link.href = window.URL.createObjectURL(blob);
				link.download = 'mou.docx'; 
				link.click();
				
				window.URL.revokeObjectURL(link.href);
			},
			error: function(err){
				alert("Telah terjadi kesalahan.");
			}
		})
	}

	function OpportunityListsKlik() {

		OpportunityListsID = OpportunityLists.getSelectedId();
		console.log(OpportunityListsID)
		$("#divlistOpportunity").show();
		if (OpportunityListsID != '') {
			$("#divlistProjectList").hide();
			LoadM('divlistOpportunity', 'partner-approval-detail?ID=' + OpportunityListsID);
		}
		return true;
	}

	OpportunityLists = new dhtmlXGridObject('OpportunityLists');
	OpportunityLists.setImagePath("./dhtmlx/imgs/");
	OpportunityLists.setHeader("No,Daftar Sebagai, Nama, Alamat, Jumlah Mitra/Bisnis Gudang, Motivasi Bergabung, Pertanyaan 1, Pertanyaan 2, Pertanyaan 3, Pertanyaan 4, Pertanyaan 5, Pertanyaan 6, Paket");
	OpportunityLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// OpportunityLists.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	OpportunityLists.setInitWidths("60,100,150,300,100,120,120,100,100,100,100,100,100");
	OpportunityLists.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
	OpportunityLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str");
	OpportunityLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	OpportunityLists.enableSmartRendering(true, 150);
	// OpportunityLists.setNumberFormat("0,000", 7, ".", ",");
	OpportunityLists.attachEvent("onRowDblClicked", OpportunityListsKlik);
	OpportunityLists.setSkin("dhx_skyblue");
	OpportunityLists.init();
	refreshdata();

	function refreshdata() {
		OpportunityLists.clearAndLoad('xml-partner-business-lists');

	}

	function delsOpportunity() {
		dhtmlx.confirm('Cancel this Opportunity?', 'Opportunity', function(r) {

			if (r == true) {
				OpportunityLists.deleteSelectedRows();
				POpportunityLists.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}


	function detailBP() {
		OpportunityListsKlik();
		// dhtmlx.confirm('Submit this Opportunity FU?', 'Opportunity', function(r) {

		// 	if (r == true) {
		// 		POpportunityLists.sendData();
		// 		alert('Data Successfully Submitted!');
		// 		setTimeout(function() {
		// 			refreshdata();
		// 		}, 1000);
		// 	}
		// })
	}


	function UpdateGrid_Opportunity() {
		var out = 0;
		for (var i = 0; i < OpportunityLists.getRowsNum(); i++) {
			rowIdMov = OpportunityLists.getRowId(i);
			OpportunityListsProc.setUpdated(rowIdMov, true);
		}
		return out;
	}

	function SubmitGrid_Opportunity() {
		OpportunityListsProc.sendData();
		alert('Data Successfully Updated!');
		OpportunityLists.clearAll();
		refreshdata();
	}

	function EditOpportunity() {
		taskid = OpportunityLists.cells(OpportunityLists.getSelectedId(), 1).getValue();
		LoadM('divlistOpportunity', 'sales-opportunity-lists-detail?ID=' + taskid);
	}

	function LihatDetailOpportunityLists() {
		taskid = OpportunityLists.cells(OpportunityLists.getSelectedId(), 1).getValue();
		LoadM('divlistOpportunity', 'sales-opportunity-activity?taskid=' + taskid);
	}

	function listDoc() {

		DocumentListsDetailID = OpportunityLists.cells(OpportunityLists.getSelectedId(),12).getValue();
		
			$("#divlistProjectList").show();

		if (DocumentListsDetailID !=''){
			$("#divlistOpportunity").hide();
			LoadM('divlistProjectList','project-documents-detail?Task_ID=' + DocumentListsDetailID);
		}
		return true;
	}
</script>
@endsection