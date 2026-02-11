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
                <span class="active">Bank Out</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="BankOUT" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="BankOUTExport" NAME="BankOUTExport" value="Export" class="btn btn-warning mt-2 mr-2" onclick="BankOUT.toExcel('./export/generate.php');" /> 
					
                </div>
<script>

var SHPREVDAY_Cal;
SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");


$('#BankOUTDel').click(function(){
	dhtmlx.confirm('Ignore Posting?', 'Bank Management', function(r) {
			
		if (r==true){
			
				BankOUT.deleteRow(BankOUT.getSelectedId());
				PBankOUT.sendData();
			 	alert('Tasklog was Ignored to Post!');
				setTimeout(function(){
					refreshdata();
				},2000);
			
		}
	})
	
});
function BankOUTDoubleClick() {
	
	BankOUTID = BankOUT.getSelectedId();
	tasks = BankOUT.cells(BankOUTID,1).getValue();
	kurs = BankOUT.cells(BankOUTID,5).getValue();
	tglx = BankOUT.cells(BankOUTID,2).getValue();
	
		if (tasks !=''){
			LoadM('DivBankDetail','accounting-recurring-bank-out-detail?ID=' + BankOUTID + "&Tgl=" + tglx+ "&Currency=" + kurs);
		}
	return true;
}

function refreshdata(){
	
	BankOUT.clearAndLoad("xml-accounting-recurring-bank-out");

}
$("#BankOUTSubmit").click(function(){
	
	PBankOUT.sendData();
	alert('Data Succesfully Posting');
	setTimeout(function(){
		refreshdata();
	},1500);

});


BankOUT = new dhtmlXGridObject('BankOUT');
BankOUT.setImagePath("./dhtmlx/imgs/");
BankOUT.setHeader("No.,Bank No,Date,Bank Name,Bank Account No,Currency,Total,Total Reval,Created Date,Created By,Department");
BankOUT.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
BankOUT.setInitWidths("70,180,100,180,200,100,100,100,100,110,100");
BankOUT.setColTypes("ro,ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro");
BankOUT.setColAlign("center,left,center,left,center,center,right,center,center,center,center");
BankOUT.setColSorting("int,str,str,str,str,str");
BankOUT.setDateFormat("%Y-%m-%d"); 

BankOUT.setEditable(false);
BankOUT.setNumberFormat("0,000",6,".",",");
BankOUT.setNumberFormat("0,000",7,".",",");

BankOUT.enableKeyboardSupport(true);
BankOUT.setSkin("dhx_skyblue");
BankOUT.enableEditEvents(true,false,true);
BankOUT.attachEvent("onRowDblClicked", BankOUTDoubleClick);

PBankOUT = new dataProcessor("accounting-recurring-bank-out-update");
PBankOUT.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PBankOUT.setUpdateMode("Off");
PBankOUT.init(BankOUT);
BankOUT.init();

refreshdata();
</script>
<div id="DivBankDetail"></div>
</div>
@endsection