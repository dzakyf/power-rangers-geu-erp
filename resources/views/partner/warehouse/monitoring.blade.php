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
			<span class="active">Monitoring</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">STOCK MONITORING</div>
	<div class="card">
		<div class="card-body">
			<div id="WarehouseMonitoring" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselWarehouseMonitoringDet" name="ekselWarehouseMonitoringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoring.toExcel('./export/generate.php');" />
				<input type="button" id="detailWarehouseMonitoring" name="detailWarehouseMonitoring" value="Detail Warehouse List" class="btn btn-info mt-2 mr-2" onclick="WarehouseMonitoringKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function WarehouseMonitoringKlik() {

		WarehouseMonitoringID = WarehouseMonitoring.getSelectedId();
		if (WarehouseMonitoringID != null) {
			LoadM('divlistOpportunity', 'partner-warehouse-monitoring-detail?Task=' + WarehouseMonitoringID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseMonitoring = new dhtmlXGridObject('WarehouseMonitoring');
	WarehouseMonitoring.setImagePath("./dhtmlx/imgs/");
	WarehouseMonitoring.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Status Gudang");
	WarehouseMonitoring.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseMonitoring.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseMonitoring.setInitWidths("60,100,150,200,100");
	WarehouseMonitoring.setColAlign("center,left,left,left,left");
	WarehouseMonitoring.setColSorting("int,str,str,str,str");
	WarehouseMonitoring.setColTypes("ron,ro,ro,ro,ro,ro");
	WarehouseMonitoring.enableSmartRendering(true, 150);
	// WarehouseMonitoring.setNumberFormat("0,000", 7, ".", ",");
	WarehouseMonitoring.attachEvent("onRowDblClicked", WarehouseMonitoringKlik);
	WarehouseMonitoring.setSkin("dhx_skyblue");
	WarehouseMonitoring.init();
	refreshdata();

	function refreshdata() {
		WarehouseMonitoring.clearAndLoad('xml-partner-warehouse-monitoring');
	}
</script>
@endsection