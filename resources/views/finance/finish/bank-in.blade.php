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
                <span class="active">Bank IN</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="BankIN" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="BankINExport" NAME="BankINExport" value="Export" class="btn btn-warning mt-2 mr-2" onclick="BankIN.toExcel('./export/generate.php');" />
                 
                </div>
	</div>
<div id="DivBankDetail"></div>
	
<script>

var SHPREVDAY_Cal;
SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");


function refreshdata(){
	
	BankIN.clearAndLoad("xml-finance-report-archive-bank-in");

}

function BankINDoubleClick(rId,cInd,stage,nValue,oValue) {
	
	
	BankINID = BankIN.getSelectedId();
	
		if (BankINID !=''){
			LoadM('DivBankDetail','finance-report-archive-bank-in-detail?ID=' + BankINID);
		}
	
	return true;
}


BankIN = new dhtmlXGridObject('BankIN');
BankIN.setImagePath("./dhtmlx/imgs/");
BankIN.setHeader("No.,Bank No,Date,Department,Bank Account No,Currency,Total,Total Reval,Created Date,Created By,Status,Reference");
BankIN.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,text_filter");
BankIN.setInitWidths("70,180,100,180,200,100,100,100,100,110,100,100");

	BankIN.setColTypes("ro,ro,dhxCalendar,combo,combo,combo,ron,ron,ro,ro,ro,ro,ro");

BankIN.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
BankIN.setColSorting("int,str,str,str,str,str");
BankIN.setDateFormat("%Y-%m-%d"); 

BankIN.setEditable(false);
BankIN.setNumberFormat("0,000",6,".",",");
BankIN.setNumberFormat("0,000",7,".",",");

BankIN.enableKeyboardSupport(true);
BankIN.setSkin("dhx_skyblue");
BankIN.enableEditEvents(true,false,true);

BankIN.attachEvent("onRowDblClicked", BankINDoubleClick);

BankIN.init();

refreshdata();
</script>

@endsection