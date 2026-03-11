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
	<div class="card">
		<div class="card-body">
		<div>
			<input type="text" id="date_filter" name="date_filter" value="{{ date('Y') }}" size="10" readonly>
			<select name="type" id="type">
				<option value="booth">booth</option>
				<option value="mobil">mobil</option>
			</select>
			<button class="btn btn-success" onclick="refreshdata()">Refresh</button>
		</div>
        <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DASHBOARD PROFIT SHARING</div>
        <div id="ProfitSharingDashboard" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        <div>
            <input type="button" id="ekselProfitSharingDashboardDet" name="ekselProfitSharingDashboardDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ProfitSharingDashboard.toExcel('./export/generate.php');" />
            <input type="button" id="detailProfitSharingDashboard" name="detailProfitSharingDashboard" value="Detail Warehouse Profit Sharing LK Monthly" class="btn btn-info mt-2 mr-2" onclick="ProfitSharingDashboardLKManually()" />
        </div>
		</div>
	</div>
	<div id="divlistOpportunity"></div>
	<div id="DivProcess"></div>

</div>

<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["date_filter"]);
	myCalendar.setDateFormat("%Y-%m");
	function ProfitSharingDashboardLKManually() {

		ProfitSharingDashboardID = ProfitSharingDashboard.getSelectedId();
		if (ProfitSharingDashboardID != null) {
			// LoadM('divlistOpportunity', 'partner-profit-sharing-dashboard?Task=' + ProfitSharingDashboardID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	ProfitSharingDashboard = new dhtmlXGridObject('ProfitSharingDashboard');
	ProfitSharingDashboard.setImagePath("./dhtmlx/imgs/");
	ProfitSharingDashboard.setHeader("No,Bulan, Target Penjualan, Acv Penjualan, Biaya Operasional, Target Nett Profit, Acv Net Profit, GAP Penjualan, Gap Net Profit, Kesesuaian BEP, Status");
	ProfitSharingDashboard.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// ProfitSharingDashboard.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ProfitSharingDashboard.setInitWidths("60,100,150,150,80,150,150,150,150,150,150");
	ProfitSharingDashboard.setColAlign("center,left,right,right,right,right,right,right,right,right,left,left");
	ProfitSharingDashboard.setColSorting("int,str,int,int,int,int,int,int,int,int,str,str");
	ProfitSharingDashboard.setColTypes("ron,ro,ron,ron,ron,ron,ron,ron,ron,ron,ro,ro");
	ProfitSharingDashboard.enableSmartRendering(true, 150);
	ProfitSharingDashboard.setNumberFormat("0,000", 2, ".", ",");
	ProfitSharingDashboard.setNumberFormat("0,000", 3, ".", ",");
	ProfitSharingDashboard.setNumberFormat("0,000", 4, ".", ",");
	ProfitSharingDashboard.setNumberFormat("0,000", 5, ".", ",");
	ProfitSharingDashboard.setNumberFormat("0,000", 6, ".", ",");
	ProfitSharingDashboard.setNumberFormat("0,000", 7, ".", ",");
	ProfitSharingDashboard.setNumberFormat("0,000", 8, ".", ",");
	ProfitSharingDashboard.attachEvent("onRowDblClicked", ProfitSharingDashboardLKManually);
	ProfitSharingDashboard.setSkin("dhx_skyblue");
	ProfitSharingDashboard.init();
	refreshdata();

	function refreshdata() {
		ProfitSharingDashboard.clearAndLoad('xml-partner-profit-sharing-dashboard-user?date=' + $('#date_filter').val() + "&type=" + $('#type').val() + '&mitra={{auth()->user()->id}}');
	}
</script>
@endsection