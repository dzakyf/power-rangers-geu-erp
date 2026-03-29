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
	ProfitSharing.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Jenis Booth, Status Gudang, Management Fee, Bagi Hasil Perusahaan, Total");
	ProfitSharing.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// ProfitSharing.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ProfitSharing.setInitWidths("60,100,150,200,100,100,100,100,100");
	ProfitSharing.setColAlign("center,left,left,left,left,left,left,left,left");
	ProfitSharing.setColSorting("int,str,str,str,str,str,str,str,str,str");
	ProfitSharing.setColTypes("ron,ro,ro,ro,ro,ro,ro,ro,ro");
	ProfitSharing.enableSmartRendering(true, 150);
	// ProfitSharing.setNumberFormat("0,000", 7, ".", ",");
	// ProfitSharing.attachEvent("onRowDblClicked", ProfitSharingLKManually);
	ProfitSharing.setSkin("dhx_skyblue");
	ProfitSharing.init();
	refreshdata();

	function refreshdata() {
		ProfitSharing.clearAndLoad('xml-partner-profit-sharing-management-fee');
	}
</script>
@endsection