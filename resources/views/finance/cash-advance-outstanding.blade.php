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
			<a href="/dashboard1">Cash Advance</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Outstanding to Issued</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">


	<div id="CashAdvance" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="CashAdvanceExp" NAME="CashAdvanceExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="CashAdvance.toExcel('./export/generate.php');" />

		<input type="button" id="CashAdvanceDel" NAME="CashAdvanceDel" value="Batal" class="btn btn-danger mt-2 mr-2" />

	</div>
	<script>
		function refreshdata() {

			CashAdvance.clearAndLoad("xml-finance-cash-advance-outstanding");

		}

		function CashAdvanceDoubleClick() {

			CashAdvanceID = CashAdvance.getSelectedId();
			kurs = CashAdvance.cells(CashAdvance.getSelectedId(), 5).getValue();
			txacc = CashAdvance.cells(CashAdvance.getSelectedId(), 11).getValue();
			tglx = CashAdvance.cells(CashAdvance.getSelectedId(), 8).getValue();
			if (CashAdvanceID != '') {
				LoadM('DivCashDetail', 'finance-cash-advance-detail-outstanding?ID=' + CashAdvanceID);
			}
			return true;
		}


		CashAdvance = new dhtmlXGridObject('CashAdvance');
		CashAdvance.setImagePath("./dhtmlx/imgs/");
		CashAdvance.setHeader("No.,Task No,Deskripsi,Peminjam,Nilai,Sisa,Days Open,Tanggal Buat,Status,Dibuat Oleh,Akun PUM,Reference,Date Submit");
		CashAdvance.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#rspan,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#text_filter");
		CashAdvance.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,#stat_total,,,,,,");
		CashAdvance.setInitWidths("70,160,150,100,100,100,100,100,100,100,80,100,100");
		CashAdvance.setColAlign("center,center,left,center,right,right,center,center,center,center,center,center,left");
		CashAdvance.setColTypes("ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro,ro,ron");
		CashAdvance.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,date");
		CashAdvance.setEditable(true);
		CashAdvance.attachEvent("onRowDblClicked", CashAdvanceDoubleClick);
		CashAdvance.setNumberFormat("0,000", 4, ".", ",");
		CashAdvance.setNumberFormat("0,000", 5, ".", ",");
		CashAdvance.setSkin("dhx_skyblue");
		CashAdvance.enableStableSorting(true);
		CashAdvance.sortRows(12, "date", "des");
		// PCashAdvance = new dataProcessor("finance-cash-advance-update");
		// PCashAdvance.setTransactionMode({
		// 	mode: "POST",
		// 	total: true,
		// 	headers: {
		// 		'X-CSRF-TOKEN': "{{ csrf_token() }}"
		// 	}

		// })
		// PCashAdvance.init(CashAdvance);
		CashAdvance.init();
		refreshdata();
	</script>
</div>
<div id="DivCashDetail"></div>

@endsection