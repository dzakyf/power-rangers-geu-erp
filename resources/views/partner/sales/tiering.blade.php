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
			<div class="form-inline mb-3">
				<label class="mr-2">Year</label>
				<select id="filter_year" class="form-control mr-3">
					@for ($y = date('Y'); $y >= date('Y') - 4; $y--)
					<option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
					@endfor
				</select>
				<label class="mr-2">Quarter</label>
				<select id="filter_quarter" class="form-control mr-3">
					<option value="">All</option>
					<option value="1">Q1 (Jan – Mar)</option>
					<option value="2">Q2 (Apr – Jun)</option>
					<option value="3">Q3 (Jul – Sep)</option>
					<option value="4">Q4 (Oct – Dec)</option>
				</select>
				<button type="button" class="btn btn-primary" onclick="refreshdata()">Filter</button>
			</div>
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
	SalesTiering.setHeader("No,Tiering, Member, Last Update");
	SalesTiering.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
	// SalesTiering.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	SalesTiering.setInitWidths("60,100,200,200,200");
	SalesTiering.setColAlign("center,left,left,left,left");
	SalesTiering.setColSorting("int,str,str,str,str");
	SalesTiering.setColTypes("ron,ro,ro,ro,ro");
	SalesTiering.enableSmartRendering(true, 150);
	// SalesTiering.attachEvent("onRowDblClicked", SalesTieringLKManually);
	SalesTiering.setSkin("dhx_skyblue");
	const quarterRange = {
		1: { start: '01-01', end: '03-31' },
		2: { start: '04-01', end: '06-30' },
		3: { start: '07-01', end: '09-30' },
		4: { start: '10-01', end: '12-31' },
	};

	function refreshdata() {
		const year = $('#filter_year').val();
		const quarter = $('#filter_quarter').val();

		let url = 'xml-partner-sales-tiering';

		if (year) {
			const range = quarterRange[quarter];
			const start = range ? `${year}-${range.start}` : `${year}-01-01`;
			const end = range ? `${year}-${range.end}` : `${year}-12-31`;
			url += `?start=${start}&end=${end}`;
		}

		SalesTiering.clearAndLoad(url);
	}

	SalesTiering.init();
	refreshdata();
</script>
@endsection