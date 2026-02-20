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
			<a href="#">Opportunity</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Outstanding Leads</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="OpportunityLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
        <input type="button" id="ekselOpportunityListsDet" name="ekselOpportunityListsDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OpportunityLists.toExcel('./export/generate.php');" />
		<input type="button" id="detailLeads" name="detailLeads" value="Detail Leads" class="btn btn-success mt-2 mr-2" onclick="detailLeads()" />
				<!-- <input type="button" id="delets" name="delets" value="Cancel Opportunity" class="btn btn-danger mt-2 mr-2" onclick="delsOpportunity()" /> -->
		
		<div class="btn-group" role="group">
			<button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<i class="fas fa-download"></i> Action
			</button>
			<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
				<a class="dropdown-item" href="#" onclick="document.getElementById('csvFileInput').click(); return false;">
					<i class="fas fa-file-csv"></i> Import .csv
				</a>
				<a class="dropdown-item" href="#" onclick="downloadCSVTemplate()">
					<i class="fas fa-file-csv"></i> Download template (.csv)
				</a>
			</div>
		</div>

		<input type="file" id="csvFileInput" accept=".csv" style="display: none;">
	</div>
</div>
<div id="divlistOpportunity"></div>
<div id="divlistProjectList"></div>
<script>

	$('#csvFileInput').on('change', function(){
		let file = this.files[0]

		ImportCSV(file)
	})

	function downloadCSVTemplate(){
		$.ajax({
			url: "download-leads-template",
			type: "GET",
			xhrFields:{
				responseType: 'blob'
			},
			success: function(response, status, xhr) {
				var blob = new Blob([response]);
				var link = document.createElement('a');
				link.href = window.URL.createObjectURL(blob);
				link.download = 'import-leads-template.csv'; 
				link.click();
				
				window.URL.revokeObjectURL(link.href);
			},
			error: function(err){
				alert("Telah terjadi kesalahan.");
			}
		})
	}

	function ImportCSV(file) {
		let fd = new FormData()

		fd.append('csv_file', file)
		fd.append('_token', '{{ csrf_token() }}')

		$.ajax({
			url: "upload-csv",
			data: fd, 
			type: "POST",
			processData: false,
			contentType: false,
			success: function(res){
				alert("Berhasil mengunggah file!")
				$('#csvFileInput').val(null)
			},
			error: function(){
				alert("Gagal mengunggah file!")
			}
		})
	}

	function OpportunityListsKlik() {

		OpportunityListsID = OpportunityLists.getSelectedId();
		$("#divlistOpportunity").show();
		if (OpportunityListsID != '') {
			$("#divlistProjectList").hide();
			LoadM('divlistOpportunity', 'partner-outstanding-leads-detail?ID=' + OpportunityListsID);
		}
		return true;
	}
	OpportunityLists = new dhtmlXGridObject('OpportunityLists');
	OpportunityLists.setImagePath("./dhtmlx/imgs/");
	OpportunityLists.setHeader("No,Task ID,Created Person,Full Name, Date, Active Contact, Partnership Interest, Capital Estimation (Modal/RP), Created Date, Source, Response, Kualifikasi, Klasifikasi, Status, Note Cancellation");
	OpportunityLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// OpportunityLists.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	OpportunityLists.setInitWidths("60,150,100,200,100,120,120,200,100,100,100,100,100,100");
	OpportunityLists.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
	OpportunityLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	OpportunityLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	OpportunityLists.enableSmartRendering(true, 150);
	// OpportunityLists.setNumberFormat("0,000", 7, ".", ",");
	POpportunityLists = new dataProcessor("partner-outstanding-leads-update");
	POpportunityLists.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	POpportunityLists.setTransactionMode("POST", true);
	POpportunityLists.setUpdateMode("Off");
	POpportunityLists.init(OpportunityLists);
	OpportunityLists.attachEvent("onRowDblClicked", OpportunityListsKlik);
	OpportunityLists.setSkin("dhx_skyblue");
	OpportunityLists.init();
	refreshdata();

	function refreshdata() {
		OpportunityLists.clearAndLoad('xml-partner-outstanding-leads');

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


	function detailLeads() {
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