<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$tanggal1 = isset($_GET['Tgl1']) ? $_GET['Tgl1'] : date("Y-m-d", strtotime("first day of january this year"));
$tanggal2 = isset($_GET['Tgl2']) ? $_GET['Tgl2'] : date("Y-m-d");

?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Account Receive</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">History</span>
		</li>
	</ol>
</div>
<div class="row">
	<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Periode <input type="text" id="Tgl1" size="10" value="<?php echo $tanggal1 ?>" readonly="readonly" /> to
		<input type="text" id="Tgl2" size="10" value="<?php echo $tanggal2 ?>" readonly="readonly" />

		<input type="button" id="SubmitFilter" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>
</div>

<div class="row">

	<div id="ARMonitoring" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselARMonitoringDet" name="ekselARMonitoringDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ARMonitoring.toExcel('./export/generate.php');" />
	</div>
</div>
<div id="ARCareDetail"></div>

<script>
	var SHPREVDAY_Cal;
	SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1", "Tgl2"]);
	SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

	function itungx() {
		$.unblockUI();
		return true;
	}
	$("#SubmitFilter").click(function() {
		$.blockUI();
		ARMonitoring.clearAndLoad('xml-finance-ar-history?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val(), itungx);
	});

	function ARMonitoringDetail() {

		ARMonitoringID = encodeURI(ARMonitoring.cells(ARMonitoring.getSelectedId(), 1).getValue());
		remaininv = ARMonitoring.cells(ARMonitoring.getSelectedId(), 6).getValue();
		totalinv = ARMonitoring.cells(ARMonitoring.getSelectedId(), 4).getValue();
		LoadM('ARCareDetail', 'ar-history-detail?CID=' + ARMonitoringID + "&remain=" + remaininv + "&totalinv=" + totalinv + '&Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());
	}
	ARMonitoring = new dhtmlXGridObject('ARMonitoring');
	ARMonitoring.setImagePath("./dhtmlx/imgs/");
	ARMonitoring.setHeader("No.,Account No,Account Name,Total Invoice,Total,Paid,Remain,CC User, PIC Invoice,Location");
	ARMonitoring.attachHeader("#rspan,#text_filter,#text_filter,#rspan,#rspan,#rspan,#rspan,#text_filter,#text_filter,#combo_filter");
	ARMonitoring.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,,,");
	ARMonitoring.setInitWidths("60,100,300,100,120,120,120,120,120,100");
	ARMonitoring.setColAlign("center,center,left,right,right,right,right,center,center,center");
	ARMonitoring.setColSorting("int,str,str,str,int,int,int,str,str,str");
	ARMonitoring.setColTypes("dyn,ro,ro,ro,ron,ron,ron,ro,ro,ro");
	ARMonitoring.enableSmartRendering(true, 350);
	ARMonitoring.setNumberFormat("0,000.00", 4, ".", ",");
	ARMonitoring.setNumberFormat("0,000.00", 5, ".", ",");
	ARMonitoring.setNumberFormat("0,000.00", 6, ".", ",");
	ARMonitoring.attachEvent("onRowDblClicked", ARMonitoringDetail);
	ARMonitoring.init();
	refreshdata();

	function refreshdata() {
		ARMonitoring.clearAndLoad('xml-finance-ar-history?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());

	}
</script>
@endsection