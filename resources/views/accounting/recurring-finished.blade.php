<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;

	
	
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
                <span class="active">Finish Processed</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	    <div id="CashOUT" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
				    
	
<script>



function refreshdata(){
	
	CashOUT.clearAndLoad("xml-accounting-recurring-finished");

}

function CashOUTDoubleClick() {
	
	CashOUTID = CashOUT.getSelectedId();
		if (CashOUTID !=''){
			LoadM('DivCashDetail2','accounting-recurring-data-detail?ID=' + CashOUTID);
		}
	return true;
}


CashOUT = new dhtmlXGridObject('CashOUT');
CashOUT.setImagePath("./dhtmlx/imgs/");
CashOUT.setHeader("No.,Recurring ID,Transaction Type,Task No,Description,Date,Times,Method,Created Date,Created By,Processed,Last Processed");
CashOUT.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
CashOUT.setInitWidths("70,100,100,100,100,100,100,100,100,110,100,100");
CashOUT.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
CashOUT.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
CashOUT.setColSorting("int,str,str,str,str,ro");
CashOUT.setDateFormat("%Y-%m-%d"); 

CashOUT.setEditable(false);
CashOUT.setSkin("dhx_skyblue");

CashOUT.attachEvent("onRowDblClicked", CashOUTDoubleClick);
CashOUT.init();
refreshdata();

</script>
<div id="DivCashDetail2"></div>
</div>
@endsection