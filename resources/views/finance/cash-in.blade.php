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
                <a href="#">Cash IN</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashIN" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="CashOutExport" NAME="CashOutExport" value="Export" class="btn btn-warning mt-2 mr-2" onclick="CashIN.toExcel('./export/generate.php');" />
                    <input type="button" id="CashINAdd" NAME="CashINAdd" value="Add New" class="btn btn-success mt-2 mr-2"/>
                    <input type="button" id="CashINDel" NAME="CashINDel"  class="btn btn-danger mt-2 mr-2"  value="Cancel" />
					 <input type="button" id="CashINSubmit" NAME="CashINSubmit" value="Update" class="btn btn-info mt-2 mr-2" />
                  	<input type="button" id="cashdetail2" NAME="cashdetail2" value="Detail" class="btn btn-info mt-2 mr-2" />

                </div>
<script>
var akuncash = "<?php echo $cekakun->Account_No ?>";
var priv = "<?php echo $priv ?>";

var SHPREVDAY_Cal;
SHPREVDAY_Cal = new dhtmlXCalendarObject(["Tgl1","Tgl2"]);
SHPREVDAY_Cal.setDateFormat("%Y-%m-%d");

$("#SubmitCheck").click(function(){
	$.blockUI(); 
	refreshdata();
});

function itungx() {
	$.unblockUI(); 
	return true;
}



$('#SubmitCashIN').click(function(){
							   
	alert("Data Succesfully Submitted!");
	setTimeout(function(){
		
		CashIN.clearAll();
	
	},1500);
});


$('#CashINAdd').click(function(){
			var idnya = CashIN.uid();
	
			CashIN.addRow(idnya, [CashIN.getRowsNum()+1,'','<?php echo date("Y-m-d");?>',akuncash,'',''],CashIN.getRowsNum());
			
			CashIN.selectRow(idnya);
			$("#DivCashDetail").html('');

});

$('#CashINDel').click(function(){
	dhtmlx.confirm('Are you sure want to cancel this tasklog?', 'Cash Management', function(r) {
			
		if (r==true){
				$("#DivCashDetail").html('');
				CashIN.deleteSelectedRows();
				PCashIN.sendData();
			 	alert('Tasklog was canceled!');
				setTimeout(function(){
					refreshdata();
				},2000);
			
		}
	})
	
});
		
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

function editreference(rId,cInd,stage,nValue,oValue) {
				
				if (cInd==4){
					wref("Search PO","finance-window-reference",650,350);
				}
			
			
			return true;
}

function refreshdata(){
	
	CashIN.clearAndLoad("xml-finance-cash-in");

}
$("#CashINSubmit").click(function(){
	
	PCashIN.sendData();
	alert('Data Succesfully created');
	setTimeout(function(){
		refreshdata();
	},1500);

});
$("#cashdetail2").click(function(){
	
	CashINID = CashIN.getSelectedId();
	TaskCash = CashIN.cells(CashIN.getSelectedId(),1).getValue();
	kurs = CashIN.cells(CashIN.getSelectedId(),11).getValue();
	tglx = CashIN.cells(CashIN.getSelectedId(),2).getValue();
	dept = CashIN.cells(CashIN.getSelectedId(),5).getValue();
		if (TaskCash !=''){
			LoadM('DivCashDetail','finance-cash-in-detail?ID=' + CashINID + "&Tgl=" + tglx+ "&Currency=" + kurs + "&Department=" + dept);
		}
	return true;
});


CashIN = new dhtmlXGridObject('CashIN');
CashIN.setImagePath("./dhtmlx/imgs/");
CashIN.setHeader("No.,Cash No,Date,Account No,Reference No,Department,Total,Total Reval,Created Date,Created By,Status,Currency");
CashIN.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#numeric_filter,#numeric_filter,#text_filter,#text_filter,#combo_filter,#combo_filter");
CashIN.setInitWidths("70,180,100,180,100,100,100,100,100,110,100,100");
CashIN.setColTypes("ro,ro,dhxCalendar,ro,ed,combo,ron,ron,ro,ro,ro,combo");

CashIN.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
CashIN.setColSorting("int,str,str,str,str,ro");
CashIN.setDateFormat("%Y-%m-%d"); 

CashIN.setEditable(true);
CashIN.setNumberFormat("0,000",6,".",",");
CashIN.setNumberFormat("0,000",7,".",",");
CashIN.attachEvent("onRowDblClicked", editreference);

CashIN.enableKeyboardSupport(true);
CashIN.setSkin("dhx_skyblue");
CashIN.enableEditEvents(true,false,true);

PCashIN = new dataProcessor("finance-cash-in-update");
PCashIN.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PCashIN.setUpdateMode("Off");
PCashIN.init(CashIN);
CashIN.init();
refreshdata();

combocurr = CashIN.getColumnCombo(5);
combocurr.enableFilteringMode(true);
combocurr.load("xml-finance-department");
combocurr = CashIN.getColumnCombo(11);
combocurr.enableFilteringMode(true);
combocurr.load("xml-finance-currency");

</script>
<div id="DivCashDetail"></div>
</div>
@endsection