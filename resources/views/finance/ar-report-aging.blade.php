<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;


?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Fast Reports</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">AR Aging</span>
		</li>
	</ol>
</div>

<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Periode <=  <input type="text" id="Tgl2" size="10" value="<?php echo date('Y-m-d') ?>" readonly="readonly" />
		Tampilan <select id="tipe">
			<option value="QTY">Quantity</option>
			<option value="total">Total</option>
		</select>
		<input type="button" id="SubmitCheck" value="Submit" onclick="refreshdata()" class="btn btn-success mt-2 mr-2" />

	</div>
	<div id="ARAging" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselARAgingDet" name="ekselARAgingDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ARAging.toExcel('./export/generate.php');" />
	</div>
</div>
<div id="ARCareDetail"></div>

<script>
	var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

	ARAging = new dhtmlXGridObject('ARAging');
	ARAging.setImagePath("./dhtmlx/imgs/");
	ARAging.setHeader("No.,Account No,Account Name,On Process,<=14 Days,<= 30 days,31-60 days,61-90 days,91-150 days,>151 days,Grand Total");
	ARAging.attachHeader("#rspan");
	ARAging.attachFooter("Total,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
	ARAging.setInitWidths("60,100,300,120,120,120,120,120,120,120,100");
	ARAging.setColAlign("center,center,left,right,right,right,right,right,right,right,right");
	ARAging.setColSorting("int,str,str,str,int,int,int,int,int,int,int");
	ARAging.setColTypes("sub_grid,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron");
	ARAging.attachEvent("onCellMarked", function(rid, ind) {
		data = ARAging.cellById(rid, ind).getValue();

		alert($data);


	});
	ARAging.setNumberFormat("0,000.00", 2, ".", ",");
	ARAging.setNumberFormat("0,000.00", 3, ".", ",");
	ARAging.setNumberFormat("0,000.00", 4, ".", ",");
	ARAging.setNumberFormat("0,000.00", 5, ".", ",");
	ARAging.setNumberFormat("0,000.00", 6, ".", ",");
	ARAging.setNumberFormat("0,000.00", 7, ".", ",");
	ARAging.setNumberFormat("0,000.00", 8, ".", ",");
	ARAging.setNumberFormat("0,000.00", 9, ".", ",");
	ARAging.setNumberFormat("0,000.00", 10, ".", ",");
	ARAging.setNumberFormat("0,000.00", 11, ".", ",");
	ARAging.init();
	refreshdata();

	function refreshdata() {
		ARAging.clearAndLoad('xml-finance-ar-report-aging?tgl1=' + $("#Tgl1").val() + "&tgl2=" + $("#Tgl2").val() + "&Metode=" + $("#tipe").val());

	}
</script>
@endsection