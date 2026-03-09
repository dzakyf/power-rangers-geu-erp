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
			<span class="active">List</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LIST WAREHOUSE</div>
	<div class="card">
		<div class="card-body">
			<div id="WarehouseList" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselWarehouseListDet" name="ekselWarehouseListDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseList.toExcel('./export/generate.php');" />
				<input type="button" id="detailWarehouseList" name="detailWarehouseList" value="Detail Warehouse List" class="btn btn-info mt-2 mr-2" onclick="WarehouseListKlik()" />
				@if (auth()->user()->Priv_ID != 'PARTNER')
				<input type="button" id="newWarehouseList" name="newWarehouseList" value="New Warehouse List" class="btn btn-success mt-2 mr-2" onclick="WarehouseListNew()" />
				<input type="button" id="deactivateWarehouseList" name="deactivateWarehouseList" value="Change Warehouse Status" class="btn btn-danger mt-2 mr-2" onclick="NonAktifkanWarehouse()" />
				@endif
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function NonAktifkanWarehouse() {

		WarehouseListID = WarehouseList.getSelectedId();
		if (WarehouseListID != null) {
			dhtmlx.confirm('Yakin ingin mengubah status gudang?', 'Change Status', function(r) {
				if (r == true) {
					LoadM('divlistOpportunity', 'partner-warehouse-list-nonaktifkan?Task=' + WarehouseListID);
				}
			})
		}else {
			dhtmlx.alert("Pilih baris data");
		}
		return true;
	}
	function WarehouseListNew() {

		LoadM('divlistOpportunity', 'partner-warehouse-list-new');
		return true;
	}
	function WarehouseListKlik() {

		WarehouseListID = WarehouseList.getSelectedId();
		if (WarehouseListID != null) {
			LoadM('divlistOpportunity', 'partner-warehouse-list-detail?Task=' + WarehouseListID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseList = new dhtmlXGridObject('WarehouseList');
	WarehouseList.setImagePath("./dhtmlx/imgs/");
	WarehouseList.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Status Gudang");
	WarehouseList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseList.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseList.setInitWidths("60,100,150,200,100");
	WarehouseList.setColAlign("center,left,left,left,left");
	WarehouseList.setColSorting("int,str,str,str,str");
	WarehouseList.setColTypes("ron,ro,ro,ro,ro,ro");
	WarehouseList.enableSmartRendering(true, 150);
	// WarehouseList.setNumberFormat("0,000", 7, ".", ",");
	WarehouseList.attachEvent("onRowDblClicked", WarehouseListKlik);
	WarehouseList.setSkin("dhx_skyblue");
	WarehouseList.init();
	refreshdata();

	function refreshdata() {
		WarehouseList.clearAndLoad('xml-partner-warehouse-list');

	}
</script>
@endsection