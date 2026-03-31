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
			<span class="active">Approval</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">AUDIT</div>
	<div class="card">
		<div class="card-body">
			<div id="TrainingApproval" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselTrainingApprovalDet" name="ekselTrainingApprovalDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="TrainingApproval.toExcel('./export/generate.php');" />
				<input type="button" id="detailTrainingApproval" name="detailTrainingApproval" value="Cetak Certificate" class="btn btn-info mt-2 mr-2" onclick="TrainingApprovalKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function TrainingApprovalKlik() {
        
		let TrainingApprovalID = TrainingApproval.getSelectedId();
		if (TrainingApprovalID != null) {
            window.open('partner-training-certificate-mentor-print?Task=' + TrainingApprovalID, '_blank');
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	TrainingApproval = new dhtmlXGridObject('TrainingApproval');
	TrainingApproval.setImagePath("./dhtmlx/imgs/");
	TrainingApproval.setHeader("No,Training Name, Participant Name,Training Date, Waktu pembuatan");
	TrainingApproval.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
	// TrainingApproval.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TrainingApproval.setInitWidths("60,100,100,150,200");
	TrainingApproval.setColAlign("center,left,left,left");
	TrainingApproval.setColSorting("int,str,str,str,str");
	TrainingApproval.setColTypes("ron,ro,ro,ro,ro,ro");
	TrainingApproval.enableSmartRendering(true, 150);
	TrainingApproval.setNumberFormat("0,000", 4, ".", ",");
	// TrainingApproval.attachEvent("onRowDblClicked", TrainingApprovalKlik);
	TrainingApproval.setSkin("dhx_skyblue");
	TrainingApproval.init();
	refreshdata();

	function refreshdata() {
		TrainingApproval.clearAndLoad('xml-partner-training-certificate-mentor');
	}
</script>
@endsection