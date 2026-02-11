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
                <a href="/dashboard1">Cash Out</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Approval</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashOUT" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="CashOUTDel" NAME="CashOUTDel" value="Ignore to Post" class="btn btn-danger mt-2 mr-2"  />
                   
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
	
			CashOUT.addRow(idnya, [CashOUT.getRowsNum()+1,'','<?php echo date("Y-m-d");?>',akuncash,'','Rp'],CashOUT.getRowsNum());
			
			CashOUT.selectRow(idnya);

});

$('#CashOUTDel').click(function(){
	dhtmlx.confirm('Abaikan Posting?', 'Cash OUT Posting', function(r) {
			
		if (r==true){
			
				CashOUT.deleteRow(CashOUT.getSelectedId());
				PCashOUT.sendData();
			 	alert('Tasklog Diabaikan!');
				setTimeout(function(){
					refreshdata();
				},2000);
			
		}
	})
	
});


function refreshdata(){
	
	CashOUT.clearAndLoad("xml-accounting-cash-out");

}
$("#CashOUTSubmit").click(function(){
	
	PCashOUT.sendData();
	alert('Data Succesfully created');
	setTimeout(function(){
		refreshdata();
	},1500);

});
function CashOUTDoubleClick() {
	
	CashOUTID = CashOUT.getSelectedId();
	kurs = CashOUT.cells(CashOUTID,11).getValue();
	tglx = CashOUT.cells(CashOUT.getSelectedId(),2).getValue();
		if (CashOUTID !=''){
			LoadM('DivCashDetail','accounting-cash-out-detail?ID=' + CashOUTID + "&Tgl=" + tglx+ "&Currency=" + kurs);
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
PCashOUT = new dataProcessor("accounting-cash-out-update");
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

</script>
<div id="DivCashDetail"></div>
</div>
@endsection