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
			<span class="active">Pipeline Leads</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PIPELINE LEADS</div>
	<div class="card">
		<div class="card-body">
			<div id="SalesRatioPipelineLeads" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselSalesRatioPipelineLeadsDet" name="ekselSalesRatioPipelineLeadsDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="SalesRatioPipelineLeads.toExcel('./export/generate.php');" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	SalesRatioPipelineLeads = new dhtmlXGridObject('SalesRatioPipelineLeads');
	SalesRatioPipelineLeads.setImagePath("./dhtmlx/imgs/");
	SalesRatioPipelineLeads.setHeader("No,Sales, Ratio Response (%), Ratio No Response (%), Ratio Qualified (%), Ratio Unqualified (%), Ratio Unidentified (%), Ratio Meeting 1 (%), Ratio Meeting 2 (%), Ratio Qualified To Warm (%), Ratio Warm to Deal (%), Ratio Cold (%), Ratio Cancel (%)");
	SalesRatioPipelineLeads.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// SalesRatioPipelineLeads.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	SalesRatioPipelineLeads.setInitWidths("60,200,100,100,100,100,100,100,100,100,100,100,100,100,100");
	SalesRatioPipelineLeads.setColAlign("center,left,right,right,right,right,right,right,right,right,right,right,right,right,right");
	SalesRatioPipelineLeads.setColSorting("int,str,int,int,int,int,int,int,int,int,int,int,int,int,int");
	SalesRatioPipelineLeads.setColTypes("ron,ro,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron");
	SalesRatioPipelineLeads.setNumberFormat("0,000",2,",",".");
	SalesRatioPipelineLeads.setNumberFormat("0,000",3,",",".");
	SalesRatioPipelineLeads.setNumberFormat("0,000",4,",",".");
	SalesRatioPipelineLeads.setNumberFormat("0,000",5,",",".");
	SalesRatioPipelineLeads.setNumberFormat("0,000",5,",",".");
	SalesRatioPipelineLeads.setNumberFormat("0,000",7,",",".");
	SalesRatioPipelineLeads.setNumberFormat("0,000",8,",",".");
	SalesRatioPipelineLeads.setNumberFormat("0,000",9,",",".");
	SalesRatioPipelineLeads.setNumberFormat("0,000",10,",",".");
	SalesRatioPipelineLeads.setNumberFormat("0,000",11,",",".");
	SalesRatioPipelineLeads.setNumberFormat("0,000",12,",",".");
	SalesRatioPipelineLeads.enableSmartRendering(true, 150);
	// SalesRatioPipelineLeads.attachEvent("onRowDblClicked", SalesRatioPipelineLeadsLKManually);
	SalesRatioPipelineLeads.setSkin("dhx_skyblue");
	SalesRatioPipelineLeads.init();
	refreshdata();

	function refreshdata() {
		SalesRatioPipelineLeads.clearAndLoad('xml-partner-sales-ratio-pipeline-leads');
	}
</script>
@endsection