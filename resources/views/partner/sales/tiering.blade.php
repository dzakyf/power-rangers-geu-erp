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
			<a href="/">Sales & Marketing</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Tiering</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TIERING</div>
	<div class="card">
		<div class="card-body">
			<div id="SalesTiering" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselSalesTieringDet" name="ekselSalesTieringDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="SalesTiering.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	SalesTiering = new dhtmlXGridObject('SalesTiering');
	SalesTiering.setImagePath("./dhtmlx/imgs/");
	SalesTiering.setHeader("No,Tiering, Member, Current Streak");
	SalesTiering.attachHeader("#rspan,#text_filter,#text_filter,#text_filter");
	// SalesTiering.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	SalesTiering.setInitWidths("60,100,200,200");
	SalesTiering.setColAlign("center,left,left,left");
	SalesTiering.setColSorting("int,str,str,str");
	SalesTiering.setColTypes("ron,ro,ro,ro");
	SalesTiering.enableSmartRendering(true, 150);
	// SalesTiering.attachEvent("onRowDblClicked", SalesTieringLKManually);
	SalesTiering.setSkin("dhx_skyblue");
	SalesTiering.init();
	refreshdata();

	function refreshdata() {
		SalesTiering.clearAndLoad('xml-partner-sales-tiering');
	}
</script>
@endsection