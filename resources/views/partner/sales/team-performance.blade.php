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
			<span class="active">Team Performance</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TEAM PERFORMANCE</div>
	<div class="card">
		<div class="card-body">
			<div id="SalesTTeamPerformance" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselSalesTTeamPerformanceDet" name="ekselSalesTTeamPerformanceDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="SalesTTeamPerformance.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	SalesTTeamPerformance = new dhtmlXGridObject('SalesTTeamPerformance');
	SalesTTeamPerformance.setImagePath("./dhtmlx/imgs/");
	SalesTTeamPerformance.setHeader("No,PIC, Bulan, Tahun, Acv Deal, Acv After Sales Sales Mitra, Acv After Sales Handling Compliance, Perfomrance, Standard, Status, Rank");
	SalesTTeamPerformance.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// SalesTTeamPerformance.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	SalesTTeamPerformance.setInitWidths("60,100,100,100,100,150,150,100,100,100,100");
	SalesTTeamPerformance.setColAlign("center,left,left,left,right,right,right,right,right,center,center");
	SalesTTeamPerformance.setColSorting("int,str,str,int,int,int,int,int,int,str,str");
	SalesTTeamPerformance.setColTypes("ron,ro,ro,ro,ron,ron,ron,ron,ron,ro,ro");
	SalesTTeamPerformance.enableSmartRendering(true, 150);
	// SalesTTeamPerformance.attachEvent("onRowDblClicked", SalesTTeamPerformanceLKManually);
	SalesTTeamPerformance.setSkin("dhx_skyblue");
	SalesTTeamPerformance.init();
	refreshdata();

	function refreshdata() {
		SalesTTeamPerformance.clearAndLoad('xml-partner-sales-team-performance');
	}
</script>
@endsection