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
			<a href="/dashboard1">Bank Advance</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Outstanding to Issued</span>
		</li>
	</ol>
</div>
<!-- End Main Content Header -->

<!-- Stats Card -->
<div class="row">


	<div id="BankAdvance" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="BankAdvanceExp" NAME="BankAdvanceExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="BankAdvance.toExcel('./export/generate.php');" />

		<input type="button" id="BankAdvanceDel" NAME="BankAdvanceDel" value="Batal" onclick="delsAdvanced()" class="btn btn-danger mt-2 mr-2" />

	</div>
	<script>
		function refreshdata() {

			BankAdvance.clearAndLoad("xml-finance-bank-advance-outstanding");

		}

		function BankAdvanceDoubleClick() {

			BankAdvanceID = BankAdvance.getSelectedId();
			kurs = BankAdvance.cells(BankAdvance.getSelectedId(), 5).getValue();
			txacc = BankAdvance.cells(BankAdvance.getSelectedId(), 11).getValue();
			tglx = BankAdvance.cells(BankAdvance.getSelectedId(), 8).getValue();
			if (BankAdvanceID != '') {
				LoadM('DivBankDetail', 'finance-bank-advance-detail-outstanding?ID=' + BankAdvanceID);
			}
			return true;
		}


		BankAdvance = new dhtmlXGridObject('BankAdvance');
		BankAdvance.setImagePath("./dhtmlx/imgs/");
		BankAdvance.setHeader("No.,Task No,From Bank,Deskripsi,Peminjam,Nilai,Sisa,Days Open,Tanggal Buat,Status,Dibuat Oleh,Akun PUM,Reference,Date Submit");
		BankAdvance.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#rspan,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#text_filter");
		BankAdvance.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,,,,,,");
		BankAdvance.setInitWidths("70,160,150,150,100,100,100,100,100,100,100,80,100,100");
		BankAdvance.setColAlign("center,center,left,left,center,right,right,center,center,center,center,center,center,left");
		BankAdvance.setColTypes("ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro,ro,ron");
		BankAdvance.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,date");
		BankAdvance.setEditable(true);
		BankAdvance.attachEvent("onRowDblClicked", BankAdvanceDoubleClick);
		BankAdvance.setNumberFormat("0,000", 5, ".", ",");
		BankAdvance.setNumberFormat("0,000", 6, ".", ",");
		BankAdvance.setSkin("dhx_skyblue");
		BankAdvance.enableStableSorting(true);
		BankAdvance.sortRows(13, "date", "des");
		PBankAdvance = new dataProcessor("finance-bank-advance-update");
		PBankAdvance.setTransactionMode({
			mode: "POST",
			total: true,
			headers: {
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			}

		})
		PBankAdvance.init(BankAdvance);
		BankAdvance.init();
		refreshdata();


		function delsAdvanced() {
			dhtmlx.confirm('Remove this Bank Advanced?', 'Ho', function(r) {

				if (r == true) {
					BankAdvance.deleteSelectedRows();
					PBankAdvance.sendData();
					alert('Data Successfully Deleted!');
					setTimeout(function() {
						// 	refreshdata();
					}, 1000);
				}
			})
		}
	</script>
</div>
<div id="DivBankDetail"></div>

@endsection