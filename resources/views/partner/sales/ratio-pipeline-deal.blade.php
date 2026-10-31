<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

$getUsers = DB::table('users')->where('Priv_ID', 'SALES')->get();

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
			<span class="active">Master Target</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">MASTER TARGET</div>
	<div class="card">
		<div class="card-body">
            <div class="form-inline mb-3">
                <label class="mr-2" for="year">Year</label>
                <select id="year" name="year" class="form-control mr-3" onchange="refreshdata()">
                    @for ($y = date('Y'); $y >= date('Y') - 4; $y--)
                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
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
	SalesTiering.setHeader("No,Sales<?php for($i=1;$i<=12;$i++) {echo ',' . date('F', mktime(0, 0, 0, $i, 1, date('Y'))).',#cspan';} ?>");
	SalesTiering.attachHeader("#rspan,#rspan<?php for($i=1;$i<=12;$i++) {echo ',target,achievement';} ?>");
	SalesTiering.attachHeader("#rspan,#text_filter<?php for($i=1;$i<=12;$i++) {echo ',' . '#text_filter,#text_filter';} ?>");
	// SalesTiering.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	SalesTiering.setInitWidths("60,100<?php for($i=1;$i<=12;$i++) {echo ',100,100';} ?>");
	SalesTiering.setColAlign("center,left<?php for($i=1;$i<=12;$i++) {echo ',right,right';} ?>");
	SalesTiering.setColSorting("int,str<?php for($i=1;$i<=12;$i++) {echo ',int,int';} ?>");
	SalesTiering.setColTypes("ron,ro<?php for($i=1;$i<=12;$i++) {echo ',ron,ron';} ?>");
    @for ($i = 0; $i < 12; $i++)
        SalesTiering.setNumberFormat("0,000",{{$i+2}},",",".");
    @endfor
	SalesTiering.enableSmartRendering(true, 150);
	// SalesTiering.attachEvent("onRowDblClicked", SalesTieringLKManually);
	SalesTiering.setSkin("dhx_skyblue");
	SalesTiering.init();

	combotopo = SalesTiering.getColumnCombo(1);
	combotopo.enableFilteringMode(true);
	refreshdata();

	function refreshdata() {
		SalesTiering.clearAndLoad('xml-partner-sales-ratio-pipeline-deal?year=' + document.getElementById('year').value);
	}
</script>
@endsection