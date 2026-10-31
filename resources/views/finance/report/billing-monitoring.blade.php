<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$priv =  Auth::user()->Priv_ID;

$tanggal1 = date("Y-m-d", strtotime("first day of this month"));

$tanggal2 = date("Y-m-d", strtotime("last day of this month"));

?>

@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Report</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Billing Monitoring</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">
<div style="font-size:12px;width:auto;height:50px;text-align:middle;vertical-align:middle" class='badan'>
		Periode iNvoice <input type="text" id="Tgl1" size="10" value="<?php echo $tanggal1 ?>" readonly="readonly" /> to
		<input type="text" id="Tgl2" size="10" value="<?php echo $tanggal2 ?>" readonly="readonly" />
		<input type="button" id="SubmitCheck" value="Submit" class="btn btn-success mt-2 mr-2" />
	</div>

	<div id="BillingMonitoring" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="BillingMonitoringExp" NAME="BillingMonitoringExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="BillingMonitoring.toExcel('./export/generate.php');" />
	</div>
</div>

<script>
	function refreshdata() {

		BillingMonitoring.clearAndLoad('xml-finance-report-billing-monitoring?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val());

	}

    var SHPREVDAY_Cal;
		SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1", "Tgl2"]);
		SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");



	BillingMonitoring = new dhtmlXGridObject('BillingMonitoring');
	BillingMonitoring.setImagePath("./dhtmlx/imgs/");
	BillingMonitoring.setHeader("No,User,No Invoice,Tanggal Inv, No. FP, Jenis Pekerjaan, Periode Pekerjaan, Jasa/Jenis Project, Recurring, OTC, Rapel, Nominal (DPP), PPN, Total");
	BillingMonitoring.attachHeader("&nbsp;,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter");
	BillingMonitoring.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
	BillingMonitoring.setInitWidths("50,150,150,100,150,200,150,100,150,150,150,150,150,150,150");
	BillingMonitoring.setColAlign("center,left,left,left,left,left,left,left,right,right,right,right,right,right");
	BillingMonitoring.setColTypes("cntr,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron");
	BillingMonitoring.setColSorting("na,str,str,str,str,str,str,str,int,int,int,int,int,int");
	BillingMonitoring.setEditable(false);
//	BillingMonitoring.attachEvent("onRowDblClicked", BillingMonitoringDoubleClick);
BillingMonitoring.setNumberFormat("0,000", 8, ".", ",");
	BillingMonitoring.setNumberFormat("0,000", 9, ".", ",");
    BillingMonitoring.setNumberFormat("0,000", 10, ".", ",");
    BillingMonitoring.setNumberFormat("0,000", 11, ".", ",");
    BillingMonitoring.setNumberFormat("0,000", 12, ".", ",");
    BillingMonitoring.setNumberFormat("0,000", 13, ".", ",");


	BillingMonitoring.setSkin("dhx_skyblue");
	BillingMonitoring.init();
	refreshdata();

    $("#SubmitCheck").click(function() {
			$.blockUI();
			BillingMonitoring.clearAndLoad('xml-finance-report-billing-monitoring?Tgl1=' + $("#Tgl1").val() + "&Tgl2=" + $("#Tgl2").val(), itungx);
		});
	function itungx() {
		$.unblockUI();
		return true;
	}


	// function BillingMonitoringDoubleClick() {

	// 	var BillingMonitoringID = BillingMonitoring.getSelectedId();
	// 	var nama = BillingMonitoring.cells(BillingMonitoring.getSelectedId(), 1).getValue();
	// 	var department = BillingMonitoring.cells(BillingMonitoring.getSelectedId(), 4).getValue();
	// 	if (BillingMonitoringID != '') {
	// 		LoadM('DivBankDetail', 'finance-report-bank-advance-detail?nama=' + encodeURI(nama) + '&Department=' + department);
	// 	}
	// 	return true;
	// }
</script>


@endsection