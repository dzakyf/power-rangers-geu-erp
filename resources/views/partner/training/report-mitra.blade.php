<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>

@extends('layout.mainlayout-bp')

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
			<span class="active">List Training</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LIST TRAINING BASED ON TARGET</div>
	<div class="card">
		<div class="card-body">
			<div id="TrainingList" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselTrainingListDet" name="ekselTrainingListDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="TrainingList.toExcel('./export/generate.php');" />
				{{-- <input type="button" id="detailTrainingList" name="detailTrainingList" value="Detail Pelatihan" class="btn btn-info mt-2 mr-2" onclick="TrainingListKlik()" /> --}}
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	// function TrainingListKlik() {
        
	// 	let TrainingListID = TrainingList.getSelectedId()
	// 	if (TrainingListID.length != null) {
	// 		LoadM('divlistOpportunity', 'partner-training-list-detail?Task=' + TrainingListID + "&target=" + $('#target').val());
	// 	}else {
	// 		alert("Pilih baris data");
	// 	}
	// 	return true;
	// }
	TrainingList = new dhtmlXGridObject('TrainingList');
	TrainingList.setImagePath("./dhtmlx/imgs/");
	TrainingList.setHeader("No,Nama Pelatihan, Tanggal Pelatihan, Waktu Pelatihan, Instruktur, Biaya Pelatihan, Metode, Alamat/Link, Penilaian Peserta, Penilaian Manager, Waktu pembuatan");
	TrainingList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// TrainingList.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TrainingList.setInitWidths("60,100,200,150,100,100,100,200,200,200,100");
	TrainingList.setColAlign("center,left,left,left,left,right,center,center,left,left,left,left");
	TrainingList.setColSorting("int,str,str,str,str,int,str,str,str,str,str,str,str");
	TrainingList.setColTypes("ron,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro");
	TrainingList.enableSmartRendering(true, 150);
	TrainingList.setNumberFormat("0,000", 5, ".", ",");
	// TrainingList.attachEvent("onRowDblClicked", TrainingListKlik);
	TrainingList.setSkin("dhx_skyblue");
	TrainingList.init();
	refreshdata();

	function refreshdata() {
		TrainingList.clearAndLoad('xml-partner-training-report-mitra');
	}
</script>
@endsection