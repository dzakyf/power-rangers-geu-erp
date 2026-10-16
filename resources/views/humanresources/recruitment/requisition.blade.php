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
			<span class="active">PTK</span>
		</li>
	</ol>
</div>

<div>
	<div id="RequisitionLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselRequisitionListsDet" name="ekselRequisitionListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="RequisitionLists.toExcel('./export/generate.php');" />
		<input type="button" id="delets" name="delets" value="Cancel PTK" onclick="delsRequisition()" class="btn btn-info mt-2 mr-2" onclick="delsRequisition()" />
		<input type="button" id="CancelReq" name="CancelReq" class="btn btn-danger mt-2 mr-2" onclick="closereq()" value="Close PTK" />
		<input type="button" id="NewReq" name="NewReq" onclick="newreq()" class="btn btn-success mt-2 mr-2" value="Form PTK" />
	</div>

</div>
<div id="divlistRequisition"></div>
<script>
	function RequisitionListsKlik() {

		RequisitionListsID = RequisitionLists.getSelectedId();

		if ((RequisitionListsID != '' && RequisitionListsID != null) && (RequisitionLists.cells(RequisitionListsID, 14).getValue() == 'REJECTED' || RequisitionLists.cells(RequisitionListsID, 14).getValue() == 'CREATED')) {
			LoadM('divlistRequisition', 'requisition-lists-detail?ID=' + RequisitionListsID);
		}
		return true;
	}
	RequisitionLists = new dhtmlXGridObject('RequisitionLists');
	RequisitionLists.setImagePath("./dhtmlx/imgs/");
	RequisitionLists.setHeader("No.,PTK No,Job Position,Description,Requirement,Department,Priority,Qty,Aging(days),Start date,End Date,Reference,PIC,Status,Note");
	RequisitionLists.setInitWidths("70,100,120,120,120,100,100,50,100,100,100,100,100,100,100");
	RequisitionLists.setColAlign("center,left,center,center,center,center,center,center,center,center,center,center,center,left");
	RequisitionLists.setColTypes("ro,ro,ro,ro,ro,ro,coro,ro,ro,ro,ro,ro,combo,ro,ro");
	RequisitionLists.setColSorting("int,str,str,str,str,str,str,str,str,str");
	RequisitionLists.setEditable(true);
	PRequisitionLists = new dataProcessor("requisition-submit");
	PRequisitionLists.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PRequisitionLists.setTransactionMode("POST", true);
	PRequisitionLists.init(RequisitionLists);
	RequisitionLists.attachEvent("onRowDblClicked", RequisitionListsKlik);
	RequisitionLists.setSkin("dhx_skyblue");
	RequisitionLists.init();
	RequisitionLists.getCombo(7).put("NORMAL", "NORMAL");
	RequisitionLists.getCombo(7).put("HIGH", "HIGH");
	RequisitionLists.getCombo(7).put("URGENT", "URGENT");
	combocurr = RequisitionLists.getColumnCombo(13);
	combocurr.enableFilteringMode(true);
	combocurr.load("xml-load-pic-production");
	refreshdata();

	function refreshdata() {
		RequisitionLists.clearAndLoad('xml-requisition-lists');

	}

	function closereq() {
		dhtmlx.confirm('Close this Requisition?', 'Requisition', function(r) {

			if (r == true) {
				RequisitionLists.cells(RequisitionLists.getSelectedId(), 12).setValue('CLOSED');

				PRequisitionLists.setUpdated(RequisitionLists.getSelectedId(), true);

				PRequisitionLists.sendData();
				alert('Data Successfully Closed!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}

	function delsRequisition() {
		dhtmlx.confirm('Cancel this Requisition?', 'Requisition', function(r) {

			if (r == true) {
				RequisitionLists.deleteSelectedRows();
				PRequisitionLists.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}


	function UpdateGrid_Requisition() {
		var out = 0;
		for (var i = 0; i < RequisitionLists.getRowsNum(); i++) {
			rowIdMov = RequisitionLists.getRowId(i);
			RequisitionListsProc.setUpdated(rowIdMov, true);
		}
		return out;
	}

	function SubmitGrid_Requisition() {
		RequisitionListsProc.sendData();
		alert('Data Successfully Updated!');
		RequisitionLists.clearAll();
		refreshdata();
	}

	function newreqpo() {
		LoadM('divlistRequisition', 'requisition-new-po');
	}

	function newreqpo2() {
		LoadM('divlistRequisition', 'requisition-new-project');
	}

	function newreq() {
		LoadM('divlistRequisition', 'requisition-new');
	}
</script>
@endsection