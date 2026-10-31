<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$currentYear  = (int) date('Y');
$currentMonth = (int) date('n');

$years = DB::table('opportunity_target')->select('Year')->distinct()->orderByDesc('Year')->pluck('Year');
if (!$years->contains($currentYear)) {
	$years->push($currentYear);
}
$years = $years->map(fn($y) => (int) $y)->unique()->sortByDesc(function ($y) {
	return $y;
})->values();

?>

@extends('layout.mainlayout-bp')

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
			<div class="form-inline mb-3">
				<label class="mr-2">Tahun</label>
				<select id="filter_year" class="form-control mr-3">
					@foreach($years as $y)
					<option value="{{ $y }}" {{ (int) $y === $currentYear ? 'selected' : '' }}>{{ $y }}</option>
					@endforeach
				</select>
				<label class="mr-2">Bulan Dari</label>
				<select id="filter_month_from" class="form-control mr-3">
					<option value="All">All</option>
					@for($m = 1; $m <= 12; $m++)
					<option value="{{ $m }}" {{ $m === $currentMonth ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
					@endfor
				</select>
				<label class="mr-2">Bulan Sampai</label>
				<select id="filter_month_to" class="form-control mr-3">
					@for($m = 1; $m <= 12; $m++)
					<option value="{{ $m }}" {{ $m === $currentMonth ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
					@endfor
				</select>
				<button type="button" class="btn btn-primary" onclick="refreshdata()">Filter</button>
			</div>
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

	$('#filter_month_from').on('change', function() {
		$('#filter_month_to').prop('disabled', this.value === 'All');
	});
	$('#filter_month_from').trigger('change');

	refreshdata();

	function refreshdata() {
		var params = '?year=' + encodeURIComponent($('#filter_year').val())
			+ '&month_from=' + encodeURIComponent($('#filter_month_from').val())
			+ '&month_to=' + encodeURIComponent($('#filter_month_to').val());
		SalesTTeamPerformance.clearAndLoad('xml-partner-sales-team-performance' + params);
	}
</script>
@endsection