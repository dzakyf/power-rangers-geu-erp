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

@extends('layout.mainlayout-finance')

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
			<span class="active">History Invoice</span>
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

	<div id="ARDetail" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div>
		<input type="button" id="ekselARMonitoringDet" name="ekselARMonitoringDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ARDetail.toExcel('./export/generate.php');" />
	</div>
</div>

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
		ARDetail.clearAndLoad('xml-ar-history-invoice?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val(), itungx);
	});

	ARDetail = new dhtmlXGridObject('ARDetail');
	ARDetail.setImagePath("./dhtmlx/imgs/");
	ARDetail.setHeader("No.,Invoice No,Invoice Date,Invoice Type,Subject,Payment Date,Subtotal,PPN,Total AR,Remain,Paid,Confirm,After PPH,Action,SubAction,PaymentVal,DebtColl,PIC,PIC No,Aging(days),Note,Claim,Claim Type,Claim Note,Collector,Rek No,Customer Name,Remain,PIC Receive");
	ARDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	ARDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,,,,,,,,,,,,");
	ARDetail.setInitWidths("70,100,100,100,100,150,100,100,110,110,110,100,100,0,0,0,0,0,100,100,120,180,200,140,280,100,140,120");
	ARDetail.setColAlign("center,center,center,left,center,center,right,right,right,center,right,center,center,left,left,center");
	ARDetail.setColTypes("ro,ro,ro,ro,ro,ro,ron,ron,edn,ron,ron,ro,ro,ro,ro,ro,ro,ro,ro,edn,coro,txt,ro,ro,ro,ro,ron,ro,ro");
	ARDetail.setColSorting("int,str,str,str,str,int,int,int,int,str,int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,int,str,str");
	ARDetail.setEditable(false);
	ARDetail.setSkin("dhx_skyblue");
	ARDetail.setNumberFormat("0,000", 10, ".", ",");
	ARDetail.setNumberFormat("0,000", 9, ".", ",");
	ARDetail.setNumberFormat("0,000", 8, ".", ",");
	ARDetail.setNumberFormat("0,000", 7, ".", ",");
	ARDetail.setNumberFormat("0,000", 6, ".", ",");
	ARDetail.setNumberFormat("0,000", 19, ".", ",");
	ARDetail.init();
	refreshdata();

	function refreshdata() {
		ARDetail.clearAndLoad('xml-ar-history-invoice?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());

	}
</script>
@endsection