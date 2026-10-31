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
			<a href="/">Warehouse</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Open</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">OPEN WAREHOUSE</div>
	<div class="card">
		<div class="card-body">
			<div id="WarehouseOpen" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselWarehouseOpenDet" name="ekselWarehouseOpenDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseOpen.toExcel('./export/generate.php');" />
				<input type="button" id="newWarehouseOpen" name="newWarehouseOpen" value="Update Warehouse Detail" class="btn btn-info mt-2 mr-2" onclick="WarehouseOpenKlik()" />
				<input type="button" id="detailWarehouseOpen" name="newWarehouseOpen" value="New Warehouse Detail" class="btn btn-success mt-2 mr-2" onclick="WarehouseOpenNew()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function WarehouseOpenNew() {

		LoadM('divlistOpportunity', 'partner-warehouse-open-new');
		return true;
	}
	function WarehouseOpenKlik() {

		WarehouseOpenID = WarehouseOpen.getSelectedId();
		if (WarehouseOpenID != null) {
			LoadM('divlistOpportunity', 'partner-warehouse-open-detail?Task=' + WarehouseOpenID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseOpen = new dhtmlXGridObject('WarehouseOpen');
	WarehouseOpen.setImagePath("./dhtmlx/imgs/");
	WarehouseOpen.setHeader("No,Tanggal Penerimaan, Tipe Penjemputan, Nama POO, Sumber, Coverage, Volume Diterima, Harga / Liter, Total Diterima, Volume Reject (Jika Ada), Bukti Timbangan / Barang");
	WarehouseOpen.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseOpen.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseOpen.setInitWidths("60,100,150,200,100,120,100,150,100,100,100,100");
	WarehouseOpen.setColAlign("center,left,left,left,left,left,left,left,left,left,left");
	WarehouseOpen.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
	WarehouseOpen.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	WarehouseOpen.enableSmartRendering(true, 150);
	// WarehouseOpen.setNumberFormat("0,000", 7, ".", ",");
	WarehouseOpen.attachEvent("onRowDblClicked", WarehouseOpenKlik);
	WarehouseOpen.setSkin("dhx_skyblue");
	WarehouseOpen.init();
	refreshdata();

	function refreshdata() {
		WarehouseOpen.clearAndLoad('xml-partner-warehouse-open');

	}
</script>
@endsection