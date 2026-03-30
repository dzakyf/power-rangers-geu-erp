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
			<span class="active">Library</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LIBRARY</div>
	<div class="card">
		<div class="card-body">
			<div id="TrainingLibrary" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselTrainingLibraryDet" name="ekselTrainingLibraryDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="TrainingLibrary.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
    function selectedRow(id, cInd)
    {
        if (cInd == 4 || cInd == 5) {
            if (TrainingLibrary.cells(id, cInd).getValue() != '') {
                // var selectedId = TrainingLibrary.getSelectedId();
                let split = id.split('||');
                window.open(split[1], '_blank');
            }
        }
    }
	TrainingLibrary = new dhtmlXGridObject('TrainingLibrary');
	TrainingLibrary.setImagePath("./dhtmlx/imgs/");
	TrainingLibrary.setHeader("No,No Materi, Nama Materi, Kategori, View, Download");
	TrainingLibrary.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// TrainingLibrary.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	TrainingLibrary.setInitWidths("60,100,150,200,100,100");
	TrainingLibrary.setColAlign("center,left,left,left,left");
	TrainingLibrary.setColSorting("int,str,str,str,str,str");
	TrainingLibrary.setColTypes("ron,ro,ro,ro,ro,ro,ro");
	TrainingLibrary.enableSmartRendering(true, 150);
	// TrainingLibrary.setNumberFormat("0,000", 7, ".", ",");
	TrainingLibrary.attachEvent("onRowSelect", selectedRow);
	TrainingLibrary.setSkin("dhx_skyblue");
	TrainingLibrary.init();
	refreshdata();

	function refreshdata() {
		TrainingLibrary.clearAndLoad('xml-partner-training-library');
	}
</script>
@endsection