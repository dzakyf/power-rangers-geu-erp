<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$cekakun = DB::table('cash_handler')->where('NIK_handler',$Saya)->first();
	if($cekakun->Account_No == ''){
		
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
                <span class="active">Cash Advance</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashAdvance" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
					<input type="button" id="CashAdvanceExp" NAME="CashAdvanceExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="CashAdvance.toExcel('./export/generate.php');" />
					<input type="button" id="cashdetail2" NAME="cashdetail2" value="Detail" class="btn btn-info mt-2 mr-2" />

                   
                </div>
<script>

function refreshdata(){
	
	CashAdvance.clearAndLoad("xml-finance-report-archive-cash-advance");

}

$('#cashdetail2').click(function(){
	
	CashAdvanceID = CashAdvance.getSelectedId();
			if (CashAdvanceID !=''){
			LoadM('DivCashDetail','finance-report-archive-cash-advance-detail?ID=' + CashAdvanceID);
		}
	return true;
});


CashAdvance = new dhtmlXGridObject('CashAdvance');
CashAdvance.setImagePath("./dhtmlx/imgs/");
CashAdvance.setHeader("No.,Task No,Referensi,Deskripsi,Peminjam,Nilai,Sisa,Days Open,Tanggal Buat,Status,Dibuat Oleh,Akun PUM");
CashAdvance.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#rspan,#combo_filter,#combo_filter,#combo_filter");
CashAdvance.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,,,,,");
CashAdvance.setInitWidths("70,160,150,150,100,100,100,100,100,100,100,80");
CashAdvance.setColAlign("center,center,left,left,center,right,right,center,center,center,center,center");
CashAdvance.setColTypes("ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro");
CashAdvance.setColSorting("int,str,str,str,str,str,str,str,str");
CashAdvance.setEditable(true);
CashAdvance.setNumberFormat("0,000",5,".",",");
CashAdvance.setNumberFormat("0,000",6,".",",");
CashAdvance.setSkin("dhx_skyblue");

CashAdvance.init();
refreshdata();
</script>
</div>
<div id="DivCashDetail"></div>

@endsection