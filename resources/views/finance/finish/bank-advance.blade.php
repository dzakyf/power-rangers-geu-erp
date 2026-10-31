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

@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Archives</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Bank Advance</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">


	<div id="BankAdvance" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="BankAdvanceExp" NAME="BankAdvanceExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="BankAdvance.toExcel('./export/generate.php');" />


	</div>
	<script>
		function refreshdata() {

			BankAdvance.clearAndLoad("xml-finance-finish-bank-advance");

		}

		function BankAdvanceDoubleClick() {

			BankAdvanceID = BankAdvance.getSelectedId();
			if (BankAdvanceID != '') {
				LoadM('DivBankDetail', 'finance-finish-bank-advance-detail?ID=' + BankAdvanceID);
			}
			return true;
		}


		BankAdvance = new dhtmlXGridObject('BankAdvance');
		BankAdvance.setImagePath("./dhtmlx/imgs/");
		BankAdvance.setHeader("No.,Task No,From Bank,Deskripsi,Peminjam,Nilai,Days Open,Tanggal Buat,Submit Date, Payment Date, PTJB Date,Reference");
		BankAdvance.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#text_filter,#text_filter,#text_filter,#combo_filter");
		BankAdvance.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,,,,,,");
		BankAdvance.setInitWidths("70,160,150,150,100,100,100,100,100,100,100,80,100,100");
		BankAdvance.setColAlign("center,center,left,left,center,right,right,center,center,center,center,center,center,left");
		BankAdvance.setColTypes("ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro,ro,ro");
		BankAdvance.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
		BankAdvance.setEditable(false);
		BankAdvance.attachEvent("onRowDblClicked", BankAdvanceDoubleClick);
		BankAdvance.setNumberFormat("0,000", 5, ".", ",");
		BankAdvance.setNumberFormat("0,000", 6, ".", ",");
		BankAdvance.setSkin("dhx_skyblue");
		BankAdvance.init();
		refreshdata();
	</script>
</div>
<div id="DivBankDetail"></div>

@endsection