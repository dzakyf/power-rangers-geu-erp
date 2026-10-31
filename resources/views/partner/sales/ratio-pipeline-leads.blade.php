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
<div id="gridTip" style="position:fixed;display:none;z-index:9999;background:#333;color:#fff;padding:4px 8px;border-radius:4px;font-size:12px;pointer-events:none;"></div>
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="/">Sales & Marketing</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Ratio Pipeline Leads</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">RATIO PIPELINE LEADS</div>
	<div class="card">
		<div class="card-body">
			<div id="SalesRatioPipelineLeads" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
			<div>
				<input type="button" id="ekselSalesRatioPipelineLeadsDet" name="ekselSalesRatioPipelineLeadsDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="SalesRatioPipelineLeads.toExcel('./export/generate.php');" />
				<input type="button" value="Pipeline Leads" class="btn btn-primary mt-2 mr-2" onclick="window.location='partner-sales-pipeline-leads'" />
			</div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	SalesRatioPipelineLeads = new dhtmlXGridObject('SalesRatioPipelineLeads');
	SalesRatioPipelineLeads.setImagePath("./dhtmlx/imgs/");
	SalesRatioPipelineLeads.setHeader("No,Sales, Ratio Response (%), Ratio No Response (%), Ratio Qualified (%), Ratio Unqualified (%), Ratio Unidentified (%), Ratio Cold (%), Ratio Meeting 1 (%), Ratio Meeting 2 (%), Ratio Warm (%), Ratio Deal (%), Ratio Cancel (%)");
	SalesRatioPipelineLeads.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// SalesRatioPipelineLeads.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	SalesRatioPipelineLeads.setInitWidths("60,200,100,100,100,100,100,100,100,100,100,100,100");
	SalesRatioPipelineLeads.setColAlign("center,left,right,right,right,right,right,right,right,right,right,right,right");
	SalesRatioPipelineLeads.setColSorting("int,str,int,int,int,int,int,int,int,int,int,int,int");
	SalesRatioPipelineLeads.setColTypes("ron,ro,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron,ron");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 2, ",", ".");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 3, ",", ".");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 4, ",", ".");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 5, ",", ".");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 6, ",", ".");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 7, ",", ".");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 8, ",", ".");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 9, ",", ".");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 10, ",", ".");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 11, ",", ".");
	SalesRatioPipelineLeads.setNumberFormat("0,000", 12, ",", ".");
	SalesRatioPipelineLeads.enableSmartRendering(true, 150);
	// SalesRatioPipelineLeads.attachEvent("onRowDblClicked", SalesRatioPipelineLeadsLKManually);
	SalesRatioPipelineLeads.setSkin("dhx_skyblue");
	SalesRatioPipelineLeads.init();
	refreshdata();

	function refreshdata() {
		SalesRatioPipelineLeads.clearAndLoad('xml-partner-sales-ratio-pipeline-leads');
	}

	var TIP_COLS = {
		2: 'response',
		3: 'no_response',
		4: 'qualified',
		5: 'unqualified',
		6: 'unidentified',
		7: 'cold',
		8: 'meeting_1',
		9: 'meeting_2',
		10: 'warm',
		11: 'deal',
		12: 'cancel_total'
	};
	var TIP_LABELS = {
		total_leads: 'Total Leads',
		response: 'Response',
		no_response: 'No Response',
		qualified: 'Qualified',
		unqualified: 'Unqualified',
		unidentified: 'Unidentified',
		cold: 'Cold',
		meeting_1: 'Meeting 1',
		meeting_2: 'Meeting 2',
		warm: 'Warm',
		deal: 'Deal',
		cancel_total: 'Cancel'
	};

	function showGridTip(grid, rowId, colIndex) {
		var key = TIP_COLS[colIndex];
		if (!key) {
			hideGridTip();
			return;
		}
		var val = grid.getRowAttribute(rowId, 't_' + key);
		if (!val) {
			hideGridTip();
			return;
		}
		var $tip = $('#gridTip');
		$tip.text(TIP_LABELS[key] + ': ' + val).show();
		try {
			var cell = grid.cells(rowId, colIndex).cell;
			if (cell) {
				var rect = cell.getBoundingClientRect();
				$tip.css({
					top: (rect.top - $tip.outerHeight() - 6) + 'px',
					left: rect.left + 'px'
				});
			}
		} catch (e) {}
	}

	function hideGridTip() {
		$('#gridTip').hide();
	}
	SalesRatioPipelineLeads.attachEvent('onMouseOver', function(rowId, colIndex) {
		showGridTip(SalesRatioPipelineLeads, rowId, colIndex);
	});
	SalesRatioPipelineLeads.attachEvent('onMouseOut', function() {
		hideGridTip();
	});
	$('#SalesRatioPipelineLeads').on('mouseleave', hideGridTip);
</script>
@endsection