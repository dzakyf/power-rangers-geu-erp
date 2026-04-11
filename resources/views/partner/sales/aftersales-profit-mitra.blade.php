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
			<span class="active">Profit Mitra</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PROFIT MITRA</div>
	<div class="card">
		<div class="card-body">
			<div id="SalesPipelineLeads" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselSalesPipelineLeadsDet" name="ekselSalesPipelineLeadsDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="SalesPipelineLeads.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	SalesPipelineLeads = new dhtmlXGridObject('SalesPipelineLeads');
	SalesPipelineLeads.setImagePath("./dhtmlx/imgs/");
	SalesPipelineLeads.setHeader("No,Sales, Bulan, Tahun, Hub, Proyeksi Profit Sharing, Acv Profit Sharing, Surplus/Minus, Performance");
	SalesPipelineLeads.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	SalesPipelineLeads.attachFooter("Total,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,");
	SalesPipelineLeads.setInitWidths("60,200,100,100,100,100,100,100,100,100,100,100,100,100");
	SalesPipelineLeads.setColAlign("center,left,left,left,left,right,right,right,right,right,right,right,right,right");
	SalesPipelineLeads.setColSorting("int,str,int,int,int,int,int,int,int,int,int,int,int,int");
	SalesPipelineLeads.setColTypes("ron,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron");
	SalesPipelineLeads.setNumberFormat("0,000", 4, ",", ".");
	SalesPipelineLeads.setNumberFormat("0,000", 5, ",", ".");
	SalesPipelineLeads.setNumberFormat("0,000", 6, ",", ".");
	SalesPipelineLeads.setNumberFormat("0,000", 7, ",", ".");
	SalesPipelineLeads.enableSmartRendering(true, 150);
	// SalesPipelineLeads.attachEvent("onRowDblClicked", SalesPipelineLeadsLKManually);
	SalesPipelineLeads.setSkin("dhx_skyblue");
	SalesPipelineLeads.init();
	refreshdata();

	function refreshdata() {
		SalesPipelineLeads.clearAndLoad('xml-partner-sales-aftersales-profit-mitra');
	}
</script>
@endsection