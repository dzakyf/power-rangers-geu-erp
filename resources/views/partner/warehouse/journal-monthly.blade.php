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
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">JOURNAL MONITORING MONTHLY</div>
	<div class="card">
		<div class="card-body">
			<div id="WarehouseJournalMonthly" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselWarehouseJournalMonthlyDet" name="ekselWarehouseJournalMonthlyDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseJournalMonthly.toExcel('./export/generate.php');" />
				<input type="button" id="detailWarehouseJournalMonthly" name="detailWarehouseJournalMonthly" value="Detail Warehouse List" class="btn btn-info mt-2 mr-2" onclick="WarehouseJournalMonthlyKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function WarehouseJournalMonthlyKlik() {
		WarehouseJournalMonthlyID = WarehouseJournalMonthly.getSelectedId();
		if (WarehouseJournalMonthlyID != null) {
			LoadM('divlistOpportunity', 'partner-warehouse-journal-monthly-detail?Task=' + WarehouseJournalMonthlyID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}

	WarehouseJournalMonthly = new dhtmlXGridObject('WarehouseJournalMonthly');
	WarehouseJournalMonthly.setImagePath("./dhtmlx/imgs/");
	WarehouseJournalMonthly.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Status Gudang");
	WarehouseJournalMonthly.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseJournalMonthly.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseJournalMonthly.setInitWidths("60,100,150,200,100");
	WarehouseJournalMonthly.setColAlign("center,left,left,left,left");
	WarehouseJournalMonthly.setColSorting("int,str,str,str,str");
	WarehouseJournalMonthly.setColTypes("ron,ro,ro,ro,ro,ro");
	WarehouseJournalMonthly.enableSmartRendering(true, 150);
	// WarehouseJournalMonthly.setNumberFormat("0,000", 7, ".", ",");
	WarehouseJournalMonthly.attachEvent("onRowDblClicked", WarehouseJournalMonthlyKlik);
	WarehouseJournalMonthly.setSkin("dhx_skyblue");
	WarehouseJournalMonthly.init();
	refreshdata();

	function refreshdata() {
		WarehouseJournalMonthly.clearAndLoad('xml-partner-warehouse-journal-monthly');
	}
</script>
@endsection