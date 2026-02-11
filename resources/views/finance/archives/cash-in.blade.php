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
                <span class="active">Cash IN</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashIN" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="CashOutExport" NAME="CashOutExport" value="Export" class="btn btn-warning mt-2 mr-2" onclick="CashIN.toExcel('./export/generate.php');" />
                   	<input type="button" id="cashdetail2" NAME="cashdetail2" value="Detail" class="btn btn-info mt-2 mr-2" />

                </div>
<script>

function refreshdata(){
	
	CashIN.clearAndLoad("xml-finance-report-archive-cash-in");

}

$("#cashdetail2").click(function(){
	
	CashINID = CashIN.getSelectedId();
	TaskCash = CashIN.cells(CashIN.getSelectedId(),1).getValue();
	kurs = CashIN.cells(CashIN.getSelectedId(),11).getValue();
	tglx = CashIN.cells(CashIN.getSelectedId(),2).getValue();
	dept = CashIN.cells(CashIN.getSelectedId(),5).getValue();
		if (TaskCash !=''){
			LoadM('DivCashDetail','finance-report-archive-cash-in-detail?ID=' + CashINID);
		}
	return true;
});

CashIN = new dhtmlXGridObject('CashIN');
CashIN.setImagePath("./dhtmlx/imgs/");
CashIN.setHeader("No.,Cash No,Date,Account No,Reference No,Department,Total,Total Reval,Created Date,Created By,Status,Currency");
CashIN.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#numeric_filter,#numeric_filter,#text_filter,#text_filter,#combo_filter,#combo_filter");
CashIN.setInitWidths("70,180,100,180,100,100,100,100,100,110,100,100");
CashIN.setColTypes("ro,ro,ro,ro,ed,ro,ron,ron,ro,ro,ro,combo");

CashIN.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
CashIN.setColSorting("int,str,str,str,str,ro");
CashIN.setDateFormat("%Y-%m-%d"); 

CashIN.setEditable(false);
CashIN.setNumberFormat("0,000",6,".",",");
CashIN.setNumberFormat("0,000",7,".",",");

CashIN.enableKeyboardSupport(true);
CashIN.setSkin("dhx_skyblue");

CashIN.init();
refreshdata();


</script>
<div id="DivCashDetail"></div>
</div>
@endsection