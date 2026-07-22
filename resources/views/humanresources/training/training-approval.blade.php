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
	</div>
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                <label for="Notes">Notes</label>
                <textarea name="Notes" id="Notes" rows="5" class="form-control"></textarea>
            </div>
        </div>
        <div class="col-12">
            <button type="button" class="btn btn-danger" onclick="delsRequisition()">Reject Training</button>
            <button type="button" class="btn btn-success" onclick="approveTraining()">Approve Training</button>
        </div>
    </div>

</div>
<div id="divlistTraining"></div>
<script>
	TrainingLists = new dhtmlXGridObject('TrainingLists');
	TrainingLists.setImagePath("./dhtmlx/imgs/");
	TrainingLists.setHeader("Checkbox,Training Name, Category, Type, Method, Description, Objective, Competence, Start Date, End Date, Training Location, Total Cost, Currency, Status");
	TrainingLists.setInitWidths("70,100,250,120,120,120,100,100,50,100,100,100,100,100,100,100");
	TrainingLists.setColAlign("center,left,left,center,center,center,center,center,center,center,center,center,center,center,left");
	TrainingLists.setColTypes("ch,ro,ro,ro,ro,ro,ro,coro,ro,ro,ro,ro,ro,combo,ro,ro");
	TrainingLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
	TrainingLists.setEditable(true);
	PTrainingLists = new dataProcessor("hrd-training-approve");
	PTrainingLists.setTransactionMode({
		mode: "POST",
		total: true,
		headers: {
			'X-CSRF-TOKEN': "{{ csrf_token() }}"
		},

	})

	PTrainingLists.setUpdateMode("Off");
	PTrainingLists.init(TrainingLists);
	TrainingLists.setSkin("dhx_skyblue");
	TrainingLists.init();
	refreshdata();

	function refreshdata() {
		TrainingLists.clearAndLoad('xml-hrd-training-lists');

	}

	function delsRequisition() {
		dhtmlx.confirm('Cancel this Training?', 'Training', function(r) {

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

    function approveTraining() {
        let id = TrainingLists.getChangedRows().split(',');
        id.filter(n => n)
        if(id.length == 0) {
            alert('Centang data yang inigin diapprove');
            return;
        }

		dhtmlx.confirm('Approve this Training?', 'Training', function(r) {

			if (r == true) {
				PTrainingLists.sendData();
				alert('Data Successfully Approved!');
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