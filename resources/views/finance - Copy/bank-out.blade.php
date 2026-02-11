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
                    <input type="button" id="BankOUTAdd" NAME="BankOUTAdd" value="Add New" />
                    <input type="button" id="BankOUTDel" NAME="BankOUTDel" value="Cancel" />
					 <input type="button" id="BankOUTSubmit" NAME="BankOUTSubmit" value="Update" />
                   
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



$('#SubmitBankOUT').click(function(){
							   
	alert("Data Succesfully Submitted!");
	setTimeout(function(){
		
		BankOUT.clearAll();
	
	},1500);
});


$('#BankOUTAdd').click(function(){
			var idnya = BankOUT.uid();
	
			BankOUT.addRow(idnya, [BankOUT.getRowsNum()+1,'','<?php echo date("Y-m-d");?>',akunbank,'','Rp'],BankOUT.getRowsNum());
			
			BankOUT.selectRow(idnya);

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
function BankOUTDoubleClick() {
	
	BankOUTID = BankOUT.getSelectedId();
	kurs = BankOUT.cells(BankOUT.getSelectedId(),5).getValue();
	tglx = BankOUT.cells(BankOUT.getSelectedId(),2).getValue();
		if (BankOUTID !=''){
			LoadM('DivBankDetail','finance-bank-out-detail?ID=' + BankOUTID + "&Tgl=" + tglx+ "&Currency=" + kurs);
		}
	return true;
}


BankOUT = new dhtmlXGridObject('BankOUT');
BankOUT.setImagePath("./dhtmlx/imgs/");
BankOUT.setHeader("No.,Bank No,Date,Account No,Voucher No,Currency,Total,Total Reval,Created Date,Created By,Status");
BankOUT.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
BankOUT.setInitWidths("70,180,100,180,100,100,100,100,100,110,100");

if (priv != 'ADM'){
	BankOUT.setColTypes("ro,ro,dhxCalendar,combo,ed,combo,ron,ron,ro,ro,ro,ro");

}else{
	BankOUT.setColTypes("ro,ro,dhxCalendar,ro,ed,ro,ron,ron,ro,ro,ro,ro");
	
}
BankOUT.setColAlign("center,left,center,left,center,center,right,center,center,center,center");
BankOUT.setColSorting("int,str,str,str,str,ro");
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
refreshdata();

if (priv != 'ADM'){

	combotopo = BankOUT.getColumnCombo(3);
	combotopo.enableFilteringMode(true);
	combotopo.loadXML("xml-finance-combo-petty-bank");
	combocurr = BankOUT.getColumnCombo(5);
	combocurr.enableFilteringMode(true);
	combocurr.loadXML("xml-finance-currency");
}
</script>
<div id="DivBankDetail"></div>
</div>
@endsection