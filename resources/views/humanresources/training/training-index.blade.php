<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;



?>

@extends('layout.mainlayout-hrd')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Human Resources</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Training</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">List</span>
		</li>
	</ol>
</div>

<div>
	<div id="TrainingLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselTrainingListsDet" name="ekselTrainingListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="TrainingLists.toExcel('./export/generate.php');" />
		<input type="button" id="delets" name="delets" value="Cancel Training" onclick="delsRequisition()" class="btn btn-info mt-2 mr-2" onclick="delsRequisition()" />
		<input type="button" id="CancelReq" name="CancelReq" class="btn btn-danger mt-2 mr-2" onclick="closereq()" value="Close Training" />
		<input type="button" id="NewReq" name="NewReq" onclick="newreq()" class="btn btn-success mt-2 mr-2" value="New Training" />
	</div>

</div>
<div id="divlistTraining"></div>
<script>
	function TrainingListsKlik() {

		TrainingListsID = TrainingLists.getSelectedId();

		if ((TrainingListsID != '' && TrainingListsID != null) && (TrainingLists.cells(TrainingListsID, 14).getValue() == 'REJECTED' || TrainingLists.cells(TrainingListsID, 14).getValue() == 'CREATED')) {
			LoadM('divlistTraining', 'hrd-training-lists-detail?ID=' + TrainingListsID);
		}
		return true;
	}
	TrainingLists = new dhtmlXGridObject('TrainingLists');
	TrainingLists.setImagePath("./dhtmlx/imgs/");
	TrainingLists.setHeader("No.,Training Name, Category, Type, Method, Description, Objective, Competence, Start Date, End Date, Training Location, Total Cost, Currency, Status");
	TrainingLists.setInitWidths("70,100,250,120,120,120,100,100,50,100,100,100,100,100,100,100");
	TrainingLists.setColAlign("center,left,left,center,center,center,center,center,center,center,center,center,center,center,left");
	TrainingLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,coro,ro,ro,ro,ro,ro,combo,ro,ro");
	TrainingLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
	TrainingLists.setEditable(true);
	PTrainingLists = new dataProcessor("hrd-training-submit");
	PTrainingLists.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})
	PTrainingLists.init(TrainingLists);
	TrainingLists.attachEvent("onRowDblClicked", TrainingListsKlik);
	TrainingLists.setSkin("dhx_skyblue");
	TrainingLists.init();
	refreshdata();

	function refreshdata() {
		TrainingLists.clearAndLoad('xml-hrd-training-lists');

	}

	function closereq() {
		dhtmlx.confirm('Close this Requisition?', 'Requisition', function(r) {

			if (r == true) {
				TrainingLists.cells(TrainingLists.getSelectedId(), 12).setValue('CLOSED');

				PTrainingLists.setUpdated(TrainingLists.getSelectedId(), true);

				PTrainingLists.sendData();
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
				TrainingLists.deleteSelectedRows();
				PTrainingLists.sendData();
				alert('Data Successfully Deleted!');
				setTimeout(function() {
					refreshdata();
				}, 1000);
			}
		})
	}


	function UpdateGrid_Requisition() {
		var out = 0;
		for (var i = 0; i < TrainingLists.getRowsNum(); i++) {
			rowIdMov = TrainingLists.getRowId(i);
			TrainingListsProc.setUpdated(rowIdMov, true);
		}
		return out;
	}

	function SubmitGrid_Requisition() {
		TrainingListsProc.sendData();
		alert('Data Successfully Updated!');
		TrainingLists.clearAll();
		refreshdata();
	}

	function newreq() {
		LoadM('divlistTraining', 'hrd-training-new');
	}
</script>
@endsection