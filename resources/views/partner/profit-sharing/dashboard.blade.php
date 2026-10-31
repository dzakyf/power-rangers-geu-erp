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
			<a href="/">Warehouse</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Dashboard Profit Sharing</span>
		</li>
	</ol>
</div>

<div>
	<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DASHBOARD PROFIT SHARING</div>
    <div class="card card-body">
        <div style="width:1500px;overflow:scroll">
            <div id="chartDiv" style="width:100%;height:400px"></div>
        </div>
    </div>
	<div class="card">
		<div class="card-body">
			<div class="d-flex align-items-end mb-2">
				<div class="m-1">
					<label class="d-block mb-1"><small>Tahun</small></label>
					<select id="filterYear" class="form-control form-control-sm" style="width:120px;">
						@for($y = 2024; $y <= 2032; $y++)
							<option value="{{ $y }}"{{ $y == ($year ?? date('Y')) ? ' selected' : '' }}>{{ $y }}</option>
						@endfor
					</select>
				</div>
				<div class="m-1">
					<button class="btn btn-sm btn-primary" onclick="applyYear()">Filter</button>
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
	ProfitSharing.setHeader("No,Gudang ID, Lokasi Gudang, Nama Mitra, Jenis Booth, Status Gudang<?php for($i=1;$i<=12;$i++) {echo ',' . date('F', mktime(0, 0, 0, $i, 1, ($year ?? date('Y')))).',#cspan';} ?>");
	ProfitSharing.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter<?php for($i=1;$i<=12;$i++) {echo ',Projection,Achievement';} ?>");
	// ProfitSharing.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ProfitSharing.setInitWidths("60,100,150,200,100,100<?php for($i=1;$i<=12;$i++) {echo ',100,100';} ?>");
	ProfitSharing.setColAlign("center,left,left,left,left,left<?php for($i=1;$i<=12;$i++) {echo ',right,right';} ?>");
	ProfitSharing.setColSorting("int,str,str,str,str,str<?php for($i=1;$i<=12;$i++) {echo ',int,int';} ?>");
	ProfitSharing.setColTypes("ron,ro,ro,ro,ro,ro<?php for($i=1;$i<=12;$i++) {echo ',ron,ron';} ?>");
	ProfitSharing.enableSmartRendering(true, 150);
    @for($i=1;$i<=12;$i++)
	ProfitSharing.setNumberFormat("0,000", {{$i+5}}, ".", ",");
    @endfor
	// ProfitSharing.attachEvent("onRowDblClicked", ProfitSharingLKManually);
	ProfitSharing.setSkin("dhx_skyblue");
	ProfitSharing.init();
	refreshdata();

	function refreshdata() {
		var year = $('#filterYear').val() || '{{ $year ?? date('Y') }}';
		ProfitSharing.clearAndLoad('xml-partner-profit-sharing-dashboard?year=' + year, LoadData);
	}

	function applyYear() {
		var year = $('#filterYear').val();
		if (year) {
			window.location.href = 'partner-profit-sharing-dashboard?year=' + year;
		}
	}

    
    var barChart =  new dhtmlXChart({

    view:"bar",
        container:"chartDiv",
        value:"#data14#",
        color: "#00994c",
        //label:"<span style='font-size:8px;'>#data2#</span>",
        gradient:"rising",
        tooltip:{
            template:function(obj){
                let num = obj.data14;
                num = Intl.NumberFormat('id-ID').format(num)
                return `<span style='font-size:12px;'>TOTAL (${obj.data1}) : ${num}</span>`
            }
        },
        width:60,
        xAxis:{
            template:"<span style='font-style:italic;font-size:11px;'>#data0#</span>"
        },
        yAxis:{
            start:0,
            lines :true,
            template:function(obj){
                return "<span style='font-size:11px;'>"+(obj%20?"":obj)+"</span>"
            }
        }
    });

    
function LoadData(){
	// $.blockUI(); 
	RefreshChart();
	// $.unblockUI();
}

function RefreshChart(){
	barChart.clearAll();// clears chart dataset
	//barChart.parse(DashboardVol,"dhtmlxgrid");
	barChart.parse(ProfitSharing,"dhtmlxgrid");
}

</script>
@endsection