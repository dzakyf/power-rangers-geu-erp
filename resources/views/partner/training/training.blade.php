<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Training</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">AUDIT</div>
	<div class="card">
		<div class="card-body">
			<div id="Training" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselTrainingDet" name="ekselTrainingDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="Training.toExcel('./export/generate.php');" />
				<input type="button" id="detailTraining" name="detailTraining" value="Detail Training" class="btn btn-info mt-2 mr-2" onclick="TrainingKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function TrainingKlik() {

		TrainingID = Training.getSelectedId();
		if (TrainingID != null) {
			LoadM('divlistOpportunity', 'partner-training-detail?Task=' + TrainingID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	Training = new dhtmlXGridObject('Training');
	Training.setImagePath("./dhtmlx/imgs/");
    @if(!auth()->user()->Training_Approval)
	Training.setHeader("No,Training Name, Request Date, Request Fee, Keterangan tambahan, Status, Waktu pembuatan");
	Training.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// Training.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	Training.setInitWidths("60,100,150,200,auto,100,100");
	Training.setColAlign("center,left,left,right,left,left");
	Training.setColSorting("int,str,str,int,str,str,str");
	Training.setColTypes("ron,ro,ro,ron,ro,ro,ro,ro");
	Training.enableSmartRendering(true, 150);
	Training.setNumberFormat("0,000", 3, ".", ",");
    @else
	Training.setHeader("No,Training Name, Participant Name, Participant Position, Participant Department, Participant Email,Request Date, Request Fee, Keterangan Tambahan, Status, Waktu pembuatan");
	Training.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// Training.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	Training.setInitWidths("60,100,100,100,100,100,150,200,auto,100,100");
	Training.setColAlign("center,left,left,left,left,left,left,right,left,left");
	Training.setColSorting("int,str,str,str,str,str,str,int,str,str,str");
	Training.setColTypes("ron,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro");
	Training.enableSmartRendering(true, 150);
	Training.setNumberFormat("0,000", 7, ".", ",");
    @endif
	Training.attachEvent("onRowDblClicked", TrainingKlik);
	Training.setSkin("dhx_skyblue");
	Training.init();
	refreshdata();

	function refreshdata() {
		Training.clearAndLoad('xml-partner-training');
	}
</script>
@endsection