<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	$cekakun = DB::table('bank_handler')->where('NIK_handler',$Saya)->first();
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
                    <input type="button" id="BankINAdd" NAME="BankINAdd" value="Add New" />
                    <input type="button" id="BankINDel" NAME="BankINDel" value="Cancel" />
					 <input type="button" id="BankINSubmit" NAME="BankINSubmit" value="Update" />
                   
                </div>
<script>
var akunbank = "<?php echo $cekakun->Account_No ?>";
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



$('#SubmitBankIN').click(function(){
							   
	alert("Data Succesfully Submitted!");
	setTimeout(function(){
		
		BankIN.clearAll();
	
	},1500);
});


$('#BankINAdd').click(function(){
			var idnya = BankIN.uid();
	
			BankIN.addRow(idnya, [BankIN.getRowsNum()+1,'','<?php echo date("Y-m-d");?>',akunbank,'','Rp'],BankIN.getRowsNum());
			
			BankIN.selectRow(idnya);

});

$('#BankINDel').click(function(){
	dhtmlx.confirm('Are you sure want to cancel this tasklog?', 'Bank Management', function(r) {
			
		if (r==true){
			
				BankIN.deleteRow(BankIN.getSelectedId());
				PBankIN.sendData();
			 	alert('Tasklog was canceled!');
				setTimeout(function(){
					refreshdata();
				},2000);
			
		}
	})
	
});


function refreshdata(){
	
	BankIN.clearAndLoad("xml-finance-bank-in");

}
$("#BankINSubmit").click(function(){
	
	PBankIN.sendData();
	alert('Data Succesfully created');
	setTimeout(function(){
		refreshdata();
	},1500);

});
function BankINDoubleClick() {
	
	BankINID = BankIN.getSelectedId();
	kurs = BankIN.cells(BankIN.getSelectedId(),5).getValue();
	tglx = BankIN.cells(BankIN.getSelectedId(),2).getValue();
		if (BankINID !=''){
			LoadM('DivBankDetail','finance-bank-in-detail?ID=' + BankINID + "&Tgl=" + tglx+ "&Currency=" + kurs);
		}
	return true;
}


BankIN = new dhtmlXGridObject('BankIN');
BankIN.setImagePath("./dhtmlx/imgs/");
BankIN.setHeader("No.,Bank No,Date,Account No,Voucher No,Currency,Total,Total Reval,Created Date,Created By,Status");
BankIN.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
BankIN.setInitWidths("70,180,100,180,100,100,100,100,100,110,100");

if (priv != 'ADM'){
	BankIN.setColTypes("ro,ro,dhxCalendar,combo,ed,combo,ron,ron,ro,ro,ro,ro");

}else{
	BankIN.setColTypes("ro,ro,dhxCalendar,ro,ed,ro,ron,ron,ro,ro,ro,ro");
	
}
BankIN.setColAlign("center,left,center,left,center,center,right,center,center,center,center");
BankIN.setColSorting("int,str,str,str,str,ro");
BankIN.setDateFormat("%Y-%m-%d"); 

BankIN.setEditable(true);
BankIN.setNumberFormat("0,000",6,".",",");
BankIN.setNumberFormat("0,000",7,".",",");

BankIN.enableKeyboardSupport(true);
BankIN.setSkin("dhx_skyblue");
BankIN.enableEditEvents(true,false,true);

BankIN.attachEvent("onRowDblClicked", BankINDoubleClick);
PBankIN = new dataProcessor("finance-bank-in-update");
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

if (priv != 'ADM'){

	combotopo = BankIN.getColumnCombo(3);
	combotopo.enableFilteringMode(true);
	combotopo.loadXML("xml-finance-combo-petty-bank");
	combocurr = BankIN.getColumnCombo(5);
	combocurr.enableFilteringMode(true);
	combocurr.loadXML("xml-finance-currency");
}
</script>
<div id="DivBankDetail"></div>
</div>
@endsection