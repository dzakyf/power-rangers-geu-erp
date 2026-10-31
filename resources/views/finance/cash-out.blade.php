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
                <a href="#">Cash Out</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashOUT" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="CashOutExport" NAME="CashOutExport" value="Export" class="btn btn-warning mt-2 mr-2" onclick="CashOUT.toExcel('./export/generate.php');" />
                    <input type="button" id="CashOUTAdd" NAME="CashOUTAdd" value="Add New" class="btn btn-success mt-2 mr-2"/>
                    <input type="button" id="CashOUTDel" NAME="CashOUTDel"  class="btn btn-danger mt-2 mr-2"  value="Cancel" />
					 <input type="button" id="CashOUTSubmit" NAME="CashOUTSubmit" value="Update" class="btn btn-info mt-2 mr-2" />
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



$('#SubmitCashOUT').click(function(){
							   
	alert("Data Succesfully Submitted!");
	setTimeout(function(){
		
		CashOUT.clearAll();
	
	},1500);
});


$('#CashOUTAdd').click(function(){
			var idnya = CashOUT.uid();
	
			CashOUT.addRow(idnya, [CashOUT.getRowsNum()+1,'','<?php echo date("Y-m-d");?>',akuncash,'',''],CashOUT.getRowsNum());
			
			CashOUT.selectRow(idnya);
			$("#DivCashDetail").html('');

});

$('#CashOUTDel').click(function(){
	dhtmlx.confirm('Are you sure want to cancel this tasklog?', 'Cash Management', function(r) {
			
		if (r==true){
				$("#DivCashDetail").html('');
				CashOUT.deleteSelectedRows();
				PCashOUT.sendData();
			 	alert('Tasklog was canceled!');
				setTimeout(function(){
					refreshdata();
				},2000);
			
		}
	})
	
});


function refreshdata(){
	
	CashOUT.clearAndLoad("xml-finance-cash-out");

}
$("#CashOUTSubmit").click(function(){
	
	PCashOUT.sendData();
	alert('Data Succesfully created');
	setTimeout(function(){
		refreshdata();
	},1500);

});
$('#cashdetail2').click(function(){
	
	CashOUTID = CashOUT.getSelectedId();
	TaskCash = CashOUT.cells(CashOUT.getSelectedId(),1).getValue();
	kurs = CashOUT.cells(CashOUT.getSelectedId(),11).getValue();
	tglx = CashOUT.cells(CashOUT.getSelectedId(),2).getValue();
	dept = CashOUT.cells(CashOUT.getSelectedId(),5).getValue();
		if (TaskCash !=''){
			LoadM('DivCashDetail','finance-cash-out-detail?ID=' + CashOUTID + "&Tgl=" + tglx+ "&Currency=" + kurs + "&Department=" + dept);
		}
	return true;
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
					wref("Search PO","finance-cashout-reference",650,350);
				}
			
			
			return true;
}


CashOUT = new dhtmlXGridObject('CashOUT');
CashOUT.setImagePath("./dhtmlx/imgs/");
CashOUT.setHeader("No.,Cash No,Date,Account No,Reference No,Department,Total,Total Reval,Created Date,Created By,Status,Currency");
CashOUT.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#numeric_filter,#numeric_filter,#text_filter,#text_filter,#combo_filter,#combo_filter");
CashOUT.setInitWidths("70,180,100,180,100,100,100,100,100,110,100,100");
CashOUT.setColTypes("ro,ro,dhxCalendar,ro,ed,combo,ron,ron,ro,ro,ro,combo");

CashOUT.setColAlign("center,left,center,left,center,center,right,center,center,center,center,center");
CashOUT.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
CashOUT.setDateFormat("%Y-%m-%d"); 

CashOUT.setEditable(true);
CashOUT.setNumberFormat("0,000",6,".",",");
CashOUT.setNumberFormat("0,000",7,".",",");

CashOUT.setSkin("dhx_skyblue");
CashOUT.enableEditEvents(true,false,true);
CashOUT.attachEvent("onRowDblClicked", editreference);

PCashOUT = new dataProcessor("finance-cash-out-update");
PCashOUT.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PCashOUT.setUpdateMode("Off");
PCashOUT.init(CashOUT);
CashOUT.init();
refreshdata();

combocurr = CashOUT.getColumnCombo(5);
combocurr.enableFilteringMode(true);
combocurr.load("xml-finance-department");
combocurr = CashOUT.getColumnCombo(11);
combocurr.enableFilteringMode(true);
combocurr.load("xml-finance-currency");

</script>
<div id="DivCashDetail"></div>
</div>
@endsection