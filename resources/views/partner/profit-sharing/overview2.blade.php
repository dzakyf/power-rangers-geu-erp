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
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LAPORAN KEUANGAN</div>
	<div class="card">
		<div class="card-body">
			<div class="d-flex align-items-end mb-2">
				<div class="m-1">
					<label class="d-block mb-1"><small>Dari Bulan</small></label>
					<input type="month" id="filterStart" class="form-control form-control-sm" style="width:160px;" />
				</div>
				<div class="m-1">
					<label class="d-block mb-1"><small>Sampai Bulan</small></label>
					<input type="month" id="filterEnd" class="form-control form-control-sm" style="width:160px;" />
				</div>
				<div class="m-1">
					<button class="btn btn-sm btn-primary" onclick="refreshdata()">Filter</button>
				</div>
			</div>
			<div id="ProfitSharing" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselProfitSharingDet" name="ekselProfitSharingDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ProfitSharing.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	ProfitSharing = new dhtmlXGridObject('ProfitSharing');
	ProfitSharing.setImagePath("./dhtmlx/imgs/");
	ProfitSharing.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Jenis Booth, Status Gudang, Investasi, Nett Profit, Opex Mitra, EBITDA, BEP, ROI (%)");
	ProfitSharing.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// ProfitSharing.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ProfitSharing.setInitWidths("60,100,150,200,100,100,100,100,100,100,100,100");
	ProfitSharing.setColAlign("center,left,left,left,left,left,right,right,right,right,right,right");
	ProfitSharing.setColSorting("int,str,str,str,str,str,int,int,int,int,int,str");
	ProfitSharing.setColTypes("ron,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ro");
	ProfitSharing.enableSmartRendering(true, 150);
	ProfitSharing.setNumberFormat("0,000", 6, ".", ",");
	ProfitSharing.setNumberFormat("0,000", 7, ".", ",");
	ProfitSharing.setNumberFormat("0,000", 8, ".", ",");
	ProfitSharing.setNumberFormat("0,000", 9, ".", ",");
	ProfitSharing.setNumberFormat("0,000", 10, ".", ",");
	ProfitSharing.setNumberFormat("0,000", 11, ".", ",");
	// ProfitSharing.attachEvent("onRowDblClicked", ProfitSharingLKManually);
	ProfitSharing.setSkin("dhx_skyblue");
	ProfitSharing.init();
	refreshdata();

	function refreshdata() {
		var params = [];
		var start = $('#filterStart').val();
		var end = $('#filterEnd').val();
		if (start) params.push('start=' + start);
		if (end) params.push('end=' + end);
		var url = 'xml-partner-profit-sharing-overview2' + (params.length ? '?' + params.join('&') : '');
		ProfitSharing.clearAndLoad(url);
	}
</script>
@endsection