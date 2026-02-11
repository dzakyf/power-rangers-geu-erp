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

		<button onclick="BankAdvance.expandAllGroups()" class="btn btn-primary">Expand </button>/<button onclick="BankAdvance.collapseAllGroups()" class="btn btn-info"> Colapse</button>


	</div>
</div>

<script>
	function refreshdata() {

		BankAdvance.clearAndLoad("xml-finance-report-bank-advance", function() {
			BankAdvance.groupBy(0, ["#title", "", "#stat_total", "#stat_total"]);
		});

	}


	BankAdvance = new dhtmlXGridObject('BankAdvance');
	BankAdvance.setImagePath("./dhtmlx/imgs/");
	BankAdvance.setHeader("Departement,Peminjam,Nilai,Count,Kode Department");
	// /BankAdvance.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#rspan,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#text_filter");
	BankAdvance.attachFooter("Total,#cspan,#stat_total,#stat_total");
	BankAdvance.setInitWidths("200,200,150,150,100");
	BankAdvance.setColAlign("left,left,right,right,left");
	BankAdvance.setColTypes("ed,ro,ron,ron,ro");
	BankAdvance.setColSorting("str,str,str,str,str");
	BankAdvance.setEditable(false);
	BankAdvance.attachEvent("onRowDblClicked", BankAdvanceDoubleClick);
	BankAdvance.setNumberFormat("0,000", 2, ".", ",");

	BankAdvance.customGroupFormat = function(text, count) {
		return "Departemen : " + text;
	};
	BankAdvance.collapseAllGroups();
	BankAdvance.setSkin("dhx_skyblue");
	BankAdvance.init();
	refreshdata();


	function BankAdvanceDoubleClick() {

		var BankAdvanceID = BankAdvance.getSelectedId();
		var nama = BankAdvance.cells(BankAdvance.getSelectedId(), 1).getValue();
		var department = BankAdvance.cells(BankAdvance.getSelectedId(), 4).getValue();
		if (BankAdvanceID != '') {
			LoadM('DivBankDetail', 'finance-report-bank-advance-detail?nama=' + encodeURI(nama) + '&Department=' + department);
		}
		return true;
	}
</script>
<div id="DivBankDetail"></div>

@endsection