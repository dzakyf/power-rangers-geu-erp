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
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">CORRECTIVE ACTION</div>
	<div class="card">
		<div class="card-body">
			<div id="WarehouseCorrective" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselWarehouseCorrectiveDet" name="ekselWarehouseCorrectiveDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseCorrective.toExcel('./export/generate.php');" />
				<input type="button" id="detailWarehouseCorrective" name="detailWarehouseCorrective" value="Detail Corrective" class="btn btn-info mt-2 mr-2" onclick="WarehouseCorrectiveKlik()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function WarehouseCorrectiveKlik() {

		WarehouseCorrectiveID = WarehouseCorrective.getSelectedId();
		if (WarehouseCorrectiveID != null) {
			LoadM('divlistOpportunity', 'partner-warehouse-corrective-action-detail?Task=' + WarehouseCorrective.cells(WarehouseCorrectiveID, 1).getValue());
		} else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseCorrective = new dhtmlXGridObject('WarehouseCorrective');
	WarehouseCorrective.setImagePath("./dhtmlx/imgs/");
	WarehouseCorrective.setHeader("No,Nomor Audit, Tanggal Audit, Due Date Corrective, No Gudang, Lokasi Gudang, Auditor, Jenis Audit, Ringkasan Temuan, Catatan Audit, Corrective Score, Created By, Corrective Action");
	WarehouseCorrective.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseCorrective.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseCorrective.setInitWidths("60,*");
	WarehouseCorrective.setColAlign("center,left,left,left,left,left,left,left,left,left");
	WarehouseCorrective.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
	WarehouseCorrective.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	WarehouseCorrective.enableSmartRendering(true, 150);
	// WarehouseCorrective.setNumberFormat("0,000", 7, ".", ",");
	WarehouseCorrective.attachEvent("onRowDblClicked", WarehouseCorrectiveKlik);
	WarehouseCorrective.setSkin("dhx_skyblue");
	WarehouseCorrective.init();
	refreshdata();

	function refreshdata() {
		WarehouseCorrective.clearAndLoad('xml-partner-warehouse-corrective-action');
	}
</script>
@endsection