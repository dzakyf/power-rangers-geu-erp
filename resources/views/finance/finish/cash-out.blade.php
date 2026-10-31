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
                <span class="active">Cash OUT</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashOUT" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="CashOutExport" NAME="CashOutExport" value="Export" class="btn btn-warning mt-2 mr-2" onclick="CashOUT.toExcel('./export/generate.php');" />
                   	<input type="button" id="cashdetail2" NAME="cashdetail2" value="Detail" class="btn btn-info mt-2 mr-2" />

                </div>
<script>




function refreshdata(){
	
	CashOUT.clearAndLoad("xml-finance-report-archive-cash-out");

}

$('#cashdetail2').click(function(){
	
	CashOUTID = CashOUT.getSelectedId();
		if (CashOUTID !=''){
			LoadM('DivCashDetail','finance-report-archive-cash-out-detail?ID=' + CashOUTID);
		}
	return true;
});



CashOUT = new dhtmlXGridObject('CashOUT');
CashOUT.setImagePath("./dhtmlx/imgs/");
CashOUT.setHeader("No.,Cash No,Date,Account No,Reference No,Department,Total,Total Reval,Created Date,Created By,Status,Currency");
CashOUT.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#numeric_filter,#numeric_filter,#text_filter,#text_filter,#combo_filter,#combo_filter");
CashOUT.setInitWidths("70,180,100,180,100,100,100,100,100,110,100,100");
CashOUT.setColTypes("ro,ro,dhxCalendar,ro,ed,combo,ron,ron,ro,ro,ro,combo");

CashOUT.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
CashOUT.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
CashOUT.setDateFormat("%Y-%m-%d"); 

CashOUT.setEditable(false);
CashOUT.setNumberFormat("0,000",6,".",",");
CashOUT.setNumberFormat("0,000",7,".",",");

CashOUT.setSkin("dhx_skyblue");
CashOUT.enableEditEvents(true,false,true);

CashOUT.init();
refreshdata();

</script>
<div id="DivCashDetail"></div>
</div>
@endsection