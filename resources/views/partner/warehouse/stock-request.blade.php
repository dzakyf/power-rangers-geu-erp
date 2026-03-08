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
			<a href="/">Warehouse</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Monitoring</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">STOCK REQUEST WAREHOUSE</div>
	<div class="card">
		<div class="card-body">
			<div id="WarehouseRequest" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselWarehouseRequestDet" name="ekselWarehouseRequestDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseRequest.toExcel('./export/generate.php');" />
				<input type="button" id="detailWarehouseRequest" name="detailWarehouseRequest" value="Detail Warehouse List" class="btn btn-info mt-2 mr-2" onclick="WarehouseRequestKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function WarehouseRequestKlik() {

		WarehouseRequestID = WarehouseRequest.getSelectedId();
		if (WarehouseRequestID != null) {
			LoadM('divlistOpportunity', 'partner-warehouse-request-detail?Task=' + WarehouseRequestID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseRequest = new dhtmlXGridObject('WarehouseRequest');
	WarehouseRequest.setImagePath("./dhtmlx/imgs/");
	WarehouseRequest.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Status Gudang");
	WarehouseRequest.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseRequest.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseRequest.setInitWidths("60,100,150,200,100");
	WarehouseRequest.setColAlign("center,left,left,left,left");
	WarehouseRequest.setColSorting("int,str,str,str,str");
	WarehouseRequest.setColTypes("ron,ro,ro,ro,ro,ro");
	WarehouseRequest.enableSmartRendering(true, 150);
	// WarehouseRequest.setNumberFormat("0,000", 7, ".", ",");
	WarehouseRequest.attachEvent("onRowDblClicked", WarehouseRequestKlik);
	WarehouseRequest.setSkin("dhx_skyblue");
	WarehouseRequest.init();
	refreshdata();

	function refreshdata() {
		WarehouseRequest.clearAndLoad('xml-partner-warehouse-request');
	}
</script>
@endsection