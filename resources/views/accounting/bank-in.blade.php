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
                <a href="/dashboard1">Bank In</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="BankIN" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="BankINExport" NAME="BankINExport" value="Export" class="btn btn-warning mt-2 mr-2" onclick="BankIN.toExcel('./export/generate.php');" /> 
                    <input type="button" id="BankINDel" NAME="BankINDel" value="Ignore to Post"   class="btn btn-danger mt-2 mr-2" />
					
                </div>
<script>

var SHPREVDAY_Cal;
SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

$('#SubmitBankIN').click(function(){
							   
	alert("Data Succesfully Submitted!");
	setTimeout(function(){
		
		BankIN.clearAll();
	
	},1500);
});


$('#BankINAdd').click(function(){
			var idnya = BankIN.uid();
	
			BankIN.addRow(idnya, [BankIN.getRowsNum() + 1,'','<?php echo date("Y-m-d");?>','','','Rp'],BankIN.getRowsNum());
			
			BankIN.selectRow(idnya);
			$("#DivBankDetail").html('');

});

$('#BankINDel').click(function(){
	dhtmlx.confirm('Ignore Posting?', 'Bank Management', function(r) {
			
		if (r==true){
			
				BankIN.deleteRow(BankIN.getSelectedId());
				PBankIN.sendData();
			 	alert('Tasklog was Ignored to Post!');
				setTimeout(function(){
					refreshdata();
				},2000);
			
		}
	})
	
});
function BankINDoubleClick() {
	
	BankINID = BankIN.getSelectedId();
	tasks = BankIN.cells(BankINID,1).getValue();
	kurs = BankIN.cells(BankINID,5).getValue();
	tglx = BankIN.cells(BankINID,2).getValue();
	
		if (tasks !=''){
			LoadM('DivBankDetail','accounting-bank-in-detail?ID=' + BankINID + "&Tgl=" + tglx+ "&Currency=" + kurs);
		}
	return true;
}

function refreshdata(){
	
	BankIN.clearAndLoad("xml-accounting-bank-in");

}
$("#BankINSubmit").click(function(){
	
	PBankIN.sendData();
	alert('Data Succesfully Posting');
	setTimeout(function(){
		refreshdata();
	},1500);

});


BankIN = new dhtmlXGridObject('BankIN');
BankIN.setImagePath("./dhtmlx/imgs/");
BankIN.setHeader("No.,Bank No,Date,Bank Name,Bank Account No,Currency,Total,Total Reval,Created Date,Created By,Department");
BankIN.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
BankIN.setInitWidths("70,180,100,180,200,100,100,100,100,110,100");
BankIN.setColTypes("ro,ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro");
BankIN.setColAlign("center,left,center,left,center,center,right,center,center,center,center");
BankIN.setColSorting("int,str,str,str,str,str");
BankIN.setDateFormat("%Y-%m-%d"); 

BankIN.setEditable(true);
BankIN.setNumberFormat("0,000",6,".",",");
BankIN.setNumberFormat("0,000",7,".",",");

BankIN.enableKeyboardSupport(true);
BankIN.setSkin("dhx_skyblue");
BankIN.enableEditEvents(true,false,true);
BankIN.attachEvent("onRowDblClicked", BankINDoubleClick);

PBankIN = new dataProcessor("accounting-bank-in-update");
PBankIN.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PBankIN.setUpdateMode("Off");
PBankIN.init(BankIN);
BankIN.init();

refreshdata();
</script>
<div id="DivBankDetail"></div>
</div>
@endsection