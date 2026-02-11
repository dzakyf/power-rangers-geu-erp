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
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="/dashboard1">Recurring</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Cash Out</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashOUT" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                   
                </div>
<script>





function refreshdata(){
	
	CashOUT.clearAndLoad("xml-accounting-recurring-cash-out");

}

function CashOUTDoubleClick() {
	
	CashOUTID = CashOUT.getSelectedId();
	kurs = CashOUT.cells(CashOUTID,11).getValue();
	tglx = CashOUT.cells(CashOUT.getSelectedId(),2).getValue();
		if (CashOUTID !=''){
			LoadM('DivCashDetail','accounting-recurring-cash-out-detail?ID=' + CashOUTID + "&Tgl=" + tglx+ "&Currency=" + kurs);
		}
	return true;
}


CashOUT = new dhtmlXGridObject('CashOUT');
CashOUT.setImagePath("./dhtmlx/imgs/");
CashOUT.setHeader("No.,Cash No,Date,Account No,Voucher No,Department,Total,Total Reval,Created Date,Created By,Status,Currency");
CashOUT.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
CashOUT.setInitWidths("70,100,100,100,100,100,100,100,100,110,100,100");
CashOUT.setColTypes("ro,ro,dhxCalendar,ro,ed,ro,ron,ron,ro,ro,ro,ro,ro");
CashOUT.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
CashOUT.setColSorting("int,str,str,str,str,ro");
CashOUT.setDateFormat("%Y-%m-%d"); 

CashOUT.setEditable(false);
CashOUT.setNumberFormat("0,000",6,".",",");
CashOUT.setNumberFormat("0,000",7,".",",");

CashOUT.enableKeyboardSupport(true);
CashOUT.setSkin("dhx_skyblue");
CashOUT.enableEditEvents(true,false,true);

CashOUT.attachEvent("onRowDblClicked", CashOUTDoubleClick);
CashOUT.init();
refreshdata();

</script>
<div id="DivCashDetail"></div>
</div>
@endsection