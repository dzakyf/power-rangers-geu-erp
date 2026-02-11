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
                <a href="/dashboard1">Bank Out</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="BankOUT" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="BankOUTExport" NAME="BankOUTExport" value="Export" class="btn btn-warning mt-2 mr-2" onclick="BankOUT.toExcel('./export/generate.php');" />
                  <input type="button" id="BankOUTAdd" NAME="BankOUTAdd" value="Add New" class="btn btn-success mt-2 mr-2" />
                    <input type="button" id="BankOUTDel" NAME="BankOUTDel" value="Cancel"   class="btn btn-danger mt-2 mr-2" />
					 <input type="button" id="BankOUTSubmit" NAME="BankOUTSubmit" value="Update" class="btn btn-info mt-2 mr-2" />
                   
                </div>
<script>

var SHPREVDAY_Cal;
SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

$('#SubmitBankOUT').click(function(){
							   
	alert("Data Succesfully Submitted!");
	setTimeout(function(){
		
		BankOUT.clearAll();
	
	},1500);
});


$('#BankOUTAdd').click(function(){
			var idnya = BankOUT.uid();
	
			BankOUT.addRow(idnya, [BankOUT.getRowsNum() + 1,'','<?php echo date("Y-m-d");?>','','','Rp'],BankOUT.getRowsNum());
			
			BankOUT.selectRow(idnya);
			$("#DivBankDetail").html('');

});

$('#BankOUTDel').click(function(){
	dhtmlx.confirm('Are you sure want to cancel this tasklog?', 'Bank Management', function(r) {
			
		if (r==true){
			
				BankOUT.deleteRow(BankOUT.getSelectedId());
				PBankOUT.sendData();
			 	alert('Tasklog was canceled!');
				setTimeout(function(){
					refreshdata();
				},2000);
			
		}
	})
	
});


function refreshdata(){
	
	BankOUT.clearAndLoad("xml-finance-bank-out");

}
$("#BankOUTSubmit").click(function(){
	
	PBankOUT.sendData();
	alert('Data Succesfully created');
	setTimeout(function(){
		refreshdata();
	},1500);

});
function BankOUTDoubleClick(rId,cInd,stage,nValue,oValue) {
	
	if (cInd==11){
					wref("Search PO","finance-bankout-reference",650,350);
	}else{
			
					
		BankOUTID = BankOUT.getSelectedId();
		tasks = BankOUT.cells(BankOUTID,1).getValue();
		kurs = BankOUT.cells(BankOUTID,5).getValue();
		tglx = BankOUT.cells(BankOUTID,2).getValue();
			dept = BankOUT.cells(BankOUT.getSelectedId(),3).getValue();

			if (tasks !=''){
				LoadM('DivBankDetail','finance-bank-out-detail?ID=' + BankOUTID + "&Tgl=" + tglx+ "&Currency=" + kurs + "&Department=" + dept);
			}
	}
	return true;
}
	
var winreference;
var Wreference;
function wref(text,url,width,height) {
    winreference = new dhtmlXWindows();

	winreference.attachViewportTo('PagesContent');
    Wreference = winreference.createWindow("Wreference", 0, 0, width, height);
    Wreference.keepInViewport(false);
	Wreference.setText(text);
    Wreference.attachURL(url, true);
	Wreference.button("minmax1").hide();
    Wreference.button("park").hide();
	Wreference.centerOnScreen();
	Wreference.allowMove();	
}



BankOUT = new dhtmlXGridObject('BankOUT');
BankOUT.setImagePath("./dhtmlx/imgs/");
BankOUT.setHeader("No.,Bank No,Date,Department,Bank Account No,Currency,Total,Total Reval,Created Date,Created By,Status,Reference");
BankOUT.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
BankOUT.setInitWidths("70,180,100,180,200,100,100,100,100,110,100,120");

BankOUT.setColTypes("ro,ro,dhxCalendar,combo,combo,combo,ron,ron,ro,ro,ro,ro,ro");

BankOUT.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
BankOUT.setColSorting("int,str,str,str,str,str");
BankOUT.setDateFormat("%Y-%m-%d"); 

BankOUT.setEditable(true);
BankOUT.setNumberFormat("0,000",6,".",",");
BankOUT.setNumberFormat("0,000",7,".",",");

BankOUT.enableKeyboardSupport(true);
BankOUT.setSkin("dhx_skyblue");
BankOUT.enableEditEvents(true,false,true);

BankOUT.attachEvent("onRowDblClicked", BankOUTDoubleClick);
PBankOUT = new dataProcessor("finance-bank-out-update");
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

combocurr = BankOUT.getColumnCombo(3);
combocurr.enableFilteringMode(true);
combocurr.load("xml-finance-department");

combotopo2 = BankOUT.getColumnCombo(5);
combotopo2.enableFilteringMode(true);
combotopo2.load("xml-finance-currency");
combotopo = BankOUT.getColumnCombo(4);
combotopo.enableFilteringMode(true);
combotopo.load("xml-finance-bank");
refreshdata();
</script>
<div id="DivBankDetail"></div>
</div>
@endsection