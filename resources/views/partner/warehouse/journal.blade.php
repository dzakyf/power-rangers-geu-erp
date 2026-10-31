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
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">JOURNAL MONITORING</div>
	<div class="card">
		<div class="card-body">
			<div id="WarehouseJournal" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselWarehouseJournalDet" name="ekselWarehouseJournalDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseJournal.toExcel('./export/generate.php');" />
				<input type="button" id="detailWarehouseJournal" name="detailWarehouseJournal" value="Detail Warehouse List" class="btn btn-info mt-2 mr-2" onclick="WarehouseJournalKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function WarehouseJournalKlik() {

		WarehouseJournalID = WarehouseJournal.getSelectedId();
		if (WarehouseJournalID != null) {
			LoadM('divlistOpportunity', 'partner-warehouse-journal-detail?Task=' + WarehouseJournalID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseJournal = new dhtmlXGridObject('WarehouseJournal');
	WarehouseJournal.setImagePath("./dhtmlx/imgs/");
	WarehouseJournal.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Status Gudang");
	WarehouseJournal.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseJournal.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseJournal.setInitWidths("60,100,150,200,100");
	WarehouseJournal.setColAlign("center,left,left,left,left");
	WarehouseJournal.setColSorting("int,str,str,str,str");
	WarehouseJournal.setColTypes("ron,ro,ro,ro,ro,ro");
	WarehouseJournal.enableSmartRendering(true, 150);
	// WarehouseJournal.setNumberFormat("0,000", 7, ".", ",");
	WarehouseJournal.attachEvent("onRowDblClicked", WarehouseJournalKlik);
	WarehouseJournal.setSkin("dhx_skyblue");
	WarehouseJournal.init();
	refreshdata();

	function refreshdata() {
		WarehouseJournal.clearAndLoad('xml-partner-warehouse-journal');
	}
</script>
@endsection