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
                <a href="/dashboard1">Bank Transfer</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Approval</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="BankTransfer" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="BankTransferDel" NAME="BankTransferDel" value="Ignore To Post" class="btn btn-danger mt-2 mr-2"/>
                   
                </div>
<script>

$('#BankTransferAdd').click(function(){

	LoadM('BankTransferDiv','accounting-bank-transfer-add');


});


function refreshdatatransfer(){
	
	BankTransfer.clearAndLoad("xml-accounting-bank-transfer");

}

function BankTransferDoubleClick() {
	
	BankTransferID = BankTransfer.getSelectedId();
	kurs = BankTransfer.cells(BankTransfer.getSelectedId(),5).getValue();
	tglx = BankTransfer.cells(BankTransfer.getSelectedId(),2).getValue();
		if (BankTransferID !=''){
			LoadM('BankTransferDiv','accounting-bank-transfer-detail?ID=' + BankTransferID + "&Tgl=" + tglx+ "&Currency=" + kurs);
		}
	return true;
}


$('#BankTransferDel').click(function(){
	dhtmlx.confirm('Are you sure want to ignore to post this tasklog?', 'Bank Management', function(r) {
			
		if (r==true){
			
				BankTransfer.deleteRow(BankTransfer.getSelectedId());
				PBankTransfer.sendData();
			 	alert('Tasklog was Ignored!');
				setTimeout(function(){
					refreshdatatransfer();
					$("#BankTransferDiv").html('');
				},2000);
			
		}
	})
	
});
BankTransfer = new dhtmlXGridObject('BankTransfer');
BankTransfer.setImagePath("./dhtmlx/imgs/");
BankTransfer.setHeader("No.,Task No,From,To,Bank Value,Last Status,Ref Trf,Transfer Date,Created By,Department");
BankTransfer.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
BankTransfer.setInitWidths("70,150,100,100,150,100,200,120,100,100");
BankTransfer.setColAlign("center,center,center,left,right,left,center,center,center,center");
BankTransfer.setColTypes("ro,ro,ro,ro,ron,ro,ro,ro,ro,ro");
BankTransfer.setColSorting("int,str,str,str,str,str,str,str");
BankTransfer.setEditable(false);
BankTransfer.attachEvent("onRowDblClicked", BankTransferDoubleClick);
PBankTransfer = new dataProcessor("accounting-bank-transfer-update");
PBankTransfer.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PBankTransfer.setUpdateMode("Off");
PBankTransfer.init(BankTransfer);
BankTransfer.setNumberFormat("0,000",4,".",",");
BankTransfer.setSkin("dhx_skyblue");
BankTransfer.init();
refreshdatatransfer();
</script>
</div>
<div id="BankTransferDiv"></div>

@endsection