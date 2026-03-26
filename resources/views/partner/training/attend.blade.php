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
			<div id="TrainingAttend" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselTrainingAttendDet" name="ekselTrainingAttendDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="TrainingAttend.toExcel('./export/generate.php');" />
				<input type="button" id="detailTrainingAttend" name="detailTrainingAttend" value="Lakukan Presensi" class="btn btn-info mt-2 mr-2" onclick="TrainingAttendKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function TrainingAttendKlik() {
        
		let TrainingAttendID = TrainingAttend.getSelectedId()
		if (TrainingAttendID.length != null) {
			LoadM('divlistOpportunity', 'partner-training-attend-presence?Task=' + TrainingAttendID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	TrainingAttend = new dhtmlXGridObject('TrainingAttend');
	TrainingAttend.setImagePath("./dhtmlx/imgs/");
	TrainingAttend.setHeader("No,Nama Pelatihan, Tanggal Pelatihan, Metode, Alamat, Presensi");
	TrainingAttend.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// TrainingAttend.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TrainingAttend.setInitWidths("60,100,150,100,200,100");
	TrainingAttend.setColAlign("center,left,left,center,right,left");
	TrainingAttend.setColSorting("int,str,str,str,str,str");
	TrainingAttend.setColTypes("ron,ro,ro,ro,ro,ro");
	TrainingAttend.enableSmartRendering(true, 150);
	// TrainingAttend.setNumberFormat("0,000", 3, ".", ",");
	// TrainingAttend.attachEvent("onRowDblClicked", TrainingAttendKlik);
	TrainingAttend.setSkin("dhx_skyblue");
	TrainingAttend.init();
	refreshdata();

	function refreshdata() {
		TrainingAttend.clearAndLoad('xml-partner-training-attend');
	}
</script>
@endsection