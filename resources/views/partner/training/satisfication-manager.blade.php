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
			<span class="active">Approval</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PENILAIAN KEPUASAN</div>
	<div class="card">
		<div class="card-body">
			<div id="TrainingSatisfication" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselTrainingSatisficationDet" name="ekselTrainingSatisficationDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="TrainingSatisfication.toExcel('./export/generate.php');" />
				<input type="button" id="detailTrainingSatisfication" name="detailTrainingSatisfication" value="Lihat/Lakukan Penilaian" class="btn btn-info mt-2 mr-2" onclick="TrainingSatisficationKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function TrainingSatisficationKlik() {
        
		let TrainingSatisficationID = TrainingSatisfication.getSelectedId();
		if (TrainingSatisficationID != null) {
            if (TrainingSatisfication.cells(TrainingSatisficationID, 6).getValue() != 'SUDAH') {
                LoadM('divlistOpportunity', 'partner-training-satisfication-manager-new?Task=' + TrainingSatisficationID);
            }else {
                LoadM('divlistOpportunity', 'partner-training-satisfication-manager-detail?Task=' + TrainingSatisficationID);
            }
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	TrainingSatisfication = new dhtmlXGridObject('TrainingSatisfication');
	TrainingSatisfication.setImagePath("./dhtmlx/imgs/");
	TrainingSatisfication.setHeader("No,Nama Pelatihan, Tanggal Pelatihan, Metode, Alamat, Score Kepuasan, Status Penilaian");
	TrainingSatisfication.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// TrainingSatisfication.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TrainingSatisfication.setInitWidths("60,100,150,100,200,100,100");
	TrainingSatisfication.setColAlign("center,left,left,center,right,left,center");
	TrainingSatisfication.setColSorting("int,str,str,str,str,str,str");
	TrainingSatisfication.setColTypes("ron,ro,ro,ro,ro,ro,ro");
	TrainingSatisfication.enableSmartRendering(true, 150);
	// TrainingSatisfication.setNumberFormat("0,000", 3, ".", ",");
	// TrainingSatisfication.attachEvent("onRowDblClicked", TrainingSatisficationKlik);
	TrainingSatisfication.setSkin("dhx_skyblue");
	TrainingSatisfication.init();
	refreshdata();

	function refreshdata() {
		TrainingSatisfication.clearAndLoad('xml-partner-training-satisfication-manager');
	}
</script>
@endsection