<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;
$cekakun = DB::table('cash_handler')->where('NIK_handler', $Saya)->first();
if ($cekakun->Account_No == '') {

	echo "You Don't have access to this menu!";
	exit;
}

$priv =  Auth::user()->Priv_ID;



?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a>Archives</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Bank OUT</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">


	<div id="BankOUT" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="BankOUTExp" NAME="BankOUTExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="BankOUT.toExcel('./export/generate.php');" />


	</div>

	<script>
		var SHPREVDAY_Cal;
		SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1", "Tgl2"]);
		SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");



		function refreshdata() {

			BankOUT.clearAndLoad("xml-finance-report-archive-bank-out");

		}

		function BankOUTDoubleClick(rId, cInd, stage, nValue, oValue) {

			BankOUTID = BankOUT.getSelectedId();

			if (BankOUTID != '') {
				LoadM('DivBankDetail', 'finance-report-archive-bank-out-detail?ID=' + BankOUTID);
			}

			return true;
		}



		BankOUT = new dhtmlXGridObject('BankOUT');
		BankOUT.setImagePath("./dhtmlx/imgs/");
		BankOUT.setHeader("No.,Bank No,Date,Department,Bank Account No,Currency,Total,Total Reval,Created Date,Created By,Status,Reference");
		BankOUT.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
		BankOUT.setInitWidths("70,180,100,180,200,100,100,100,100,110,100,120");

		BankOUT.setColTypes("ro,ro,dhxCalendar,combo,combo,combo,ron,ron,ro,ro,ro,ro,ro");

		BankOUT.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
		BankOUT.setColSorting("int,str,str,str,str,str");
		BankOUT.setDateFormat("%Y-%m-%d");

		BankOUT.setEditable(false);
		BankOUT.setNumberFormat("0,000", 6, ".", ",");
		BankOUT.setNumberFormat("0,000", 7, ".", ",");

		BankOUT.enableKeyboardSupport(true);
		BankOUT.setSkin("dhx_skyblue");
		BankOUT.enableEditEvents(true, false, true);

		BankOUT.attachEvent("onRowDblClicked", BankOUTDoubleClick);

		BankOUT.init();

		refreshdata();
	</script>
</div>
<div id="DivBankDetail"></div>

@endsection