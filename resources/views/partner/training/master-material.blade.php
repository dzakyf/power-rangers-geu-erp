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
			<span class="active">Materi</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MASTER MATERI</div>
	<div class="card">
		<div class="card-body">
			<div id="MasterMaterial" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselMasterMaterialDet" name="ekselMasterMaterialDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="MasterMaterial.toExcel('./export/generate.php');" />
				<input type="button" id="detailMasterMaterial" name="detailMasterMaterial" value="Detail Materi" class="btn btn-info mt-2 mr-2" onclick="MasterMaterialKlik()" />
				<input type="button" id="detailMasterMaterial" name="detailMasterMaterial" value="New Materi" class="btn btn-success mt-2 mr-2" onclick="MasterMaterialNew()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function MasterMaterialNew() {

        LoadM('divlistOpportunity', 'partner-master-material-new');
		return true;
	}
	function MasterMaterialKlik() {

		MasterMaterialID = MasterMaterial.getSelectedId();
		if (MasterMaterialID != null) {
			LoadM('divlistOpportunity', 'partner-master-material-detail?Task=' + MasterMaterialID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	MasterMaterial = new dhtmlXGridObject('MasterMaterial');
	MasterMaterial.setImagePath("./dhtmlx/imgs/");
	MasterMaterial.setHeader("No,No Materi, Nama Materi, Kategori, Dapat Didownload, Dapat dilihat, Attachment, Status, Dibuat oleh");
	MasterMaterial.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// MasterMaterial.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	MasterMaterial.setInitWidths("60,100,150,200,100,100,100,100,auto");
	MasterMaterial.setColAlign("center,left,left,left,left,left,left,left");
	MasterMaterial.setColSorting("int,str,str,str,str,str,str,str,str");
	MasterMaterial.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	MasterMaterial.enableSmartRendering(true, 150);
	// MasterMaterial.setNumberFormat("0,000", 7, ".", ",");
	MasterMaterial.attachEvent("onRowDblClicked", MasterMaterialKlik);
	MasterMaterial.setSkin("dhx_skyblue");
	MasterMaterial.init();
	refreshdata();

	function refreshdata() {
		MasterMaterial.clearAndLoad('xml-partner-master-material');
	}
</script>
@endsection