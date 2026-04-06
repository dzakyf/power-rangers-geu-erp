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
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">AUDIT</div>
	<div class="card">
		<div class="card-body">
			<div id="WarehouseAudit" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselWarehouseAuditDet" name="ekselWarehouseAuditDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseAudit.toExcel('./export/generate.php');" />
				<input type="button" id="detailWarehouseAudit" name="detailWarehouseAudit" value="Detail Audit" class="btn btn-info mt-2 mr-2" onclick="WarehouseAuditKlik()" />
				<input type="button" id="detailWarehouseAudit" name="detailWarehouseAudit" value="New Audit" class="btn btn-success mt-2 mr-2" onclick="WarehouseAuditNew()" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	function WarehouseAuditNew() {

        LoadM('divlistOpportunity', 'partner-warehouse-audit-new');
		return true;
	}
	function WarehouseAuditKlik() {

		WarehouseAuditID = WarehouseAudit.getSelectedId();
		if (WarehouseAuditID != null) {
			LoadM('divlistOpportunity', 'partner-warehouse-audit-detail?Task=' + WarehouseAuditID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseAudit = new dhtmlXGridObject('WarehouseAudit');
	WarehouseAudit.setImagePath("./dhtmlx/imgs/");
	WarehouseAudit.setHeader("No,Nomor Audit, Tanggal Audit, No Gudang, Lokasi Gudang, Auditor, Jenis Audit, Kategori Temuan, Ringkasan Temuan, Total Score, Deskripsi Score");
	WarehouseAudit.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseAudit.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseAudit.setInitWidths("60,100,150,200,100,100,100,100,100,100,auto");
	WarehouseAudit.setColAlign("center,left,left,left,left,left,left,left,left,left");
	WarehouseAudit.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
	WarehouseAudit.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	WarehouseAudit.enableSmartRendering(true, 150);
	// WarehouseAudit.setNumberFormat("0,000", 7, ".", ",");
	WarehouseAudit.attachEvent("onRowDblClicked", WarehouseAuditKlik);
	WarehouseAudit.setSkin("dhx_skyblue");
	WarehouseAudit.init();
	refreshdata();

	function refreshdata() {
		WarehouseAudit.clearAndLoad('xml-partner-warehouse-audit');
	}
</script>
@endsection