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
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">JOURNAL MONITORING ANNUALLY</div>
	<div class="card">
		<div class="card-body">
			<div id="WarehouseJournalAnnually" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselWarehouseJournalAnnuallyDet" name="ekselWarehouseJournalAnnuallyDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseJournalAnnually.toExcel('./export/generate.php');" />
				<input type="button" id="detailWarehouseJournalAnnually" name="detailWarehouseJournalAnnually" value="Detail Warehouse List" class="btn btn-info mt-2 mr-2" onclick="WarehouseJournalAnnuallyKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function WarehouseJournalAnnuallyKlik() {
		WarehouseJournalAnnuallyID = WarehouseJournalAnnually.getSelectedId();
		if (WarehouseJournalAnnuallyID != null) {
			LoadM('divlistOpportunity', 'partner-warehouse-journal-annually-detail?Task=' + WarehouseJournalAnnuallyID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}

	WarehouseJournalAnnually = new dhtmlXGridObject('WarehouseJournalAnnually');
	WarehouseJournalAnnually.setImagePath("./dhtmlx/imgs/");
	WarehouseJournalAnnually.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Status Gudang");
	WarehouseJournalAnnually.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseJournalAnnually.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseJournalAnnually.setInitWidths("60,100,150,200,100");
	WarehouseJournalAnnually.setColAlign("center,left,left,left,left");
	WarehouseJournalAnnually.setColSorting("int,str,str,str,str");
	WarehouseJournalAnnually.setColTypes("ron,ro,ro,ro,ro,ro");
	WarehouseJournalAnnually.enableSmartRendering(true, 150);
	// WarehouseJournalAnnually.setNumberFormat("0,000", 7, ".", ",");
	WarehouseJournalAnnually.attachEvent("onRowDblClicked", WarehouseJournalAnnuallyKlik);
	WarehouseJournalAnnually.setSkin("dhx_skyblue");
	WarehouseJournalAnnually.init();
	refreshdata();

	function refreshdata() {
		WarehouseJournalAnnually.clearAndLoad('xml-partner-warehouse-journal-annually');
	}
</script>
@endsection