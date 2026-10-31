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
                <a href="/dashboard1">Cash Transfer</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashTransfer" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
				<input type="button" id="CashTransferExp" NAME="CashTransferExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="CashTransfer.toExcel('./export/generate.php');" />
                    <input type="button" id="CashTransferAdd" NAME="CashTransferAdd" value="Baru" class="btn btn-success mt-2 mr-2" />
                    <input type="button" id="CashTransferDel" NAME="CashTransferDel" value="Batal" class="btn btn-danger mt-2 mr-2" />
                   
                </div>
<script>

$('#CashTransferDel').click(function(){
	dhtmlx.confirm('Cancel Transfer?', 'Cash Transfer', function(r) {
			
		if (r==true){
				$("#DivCashDetail").html('');
				CashTransfer.deleteSelectedRows();
				PCashTransfer.sendData();
			 	alert('Tasklog was canceled!');
				setTimeout(function(){
					refreshdatatransfer();
				},2000);
			
		}
	})
	
});
$('#CashTransferAdd').click(function(){

	LoadM('CashTransferDiv','finance-cash-transfer-add');


});


function refreshdatatransfer(){
	
	CashTransfer.clearAndLoad("xml-finance-cash-transfer");

}

function CashTransferDoubleClick() {
	
	CashTransferID = CashTransfer.getSelectedId();
	kurs = CashTransfer.cells(CashTransfer.getSelectedId(),5).getValue();
	tglx = CashTransfer.cells(CashTransfer.getSelectedId(),2).getValue();
		if (CashTransferID !=''){
			LoadM('CashTransferDiv','finance-cash-transfer-detail?ID=' + CashTransferID + "&Tgl=" + tglx+ "&Currency=" + kurs);
		}
	return true;
}


CashTransfer = new dhtmlXGridObject('CashTransfer');
CashTransfer.setImagePath("./dhtmlx/imgs/");
CashTransfer.setHeader("No.,Task No,From,To,Cash Value,Last Status,Ref Trf,Transfer Date,Created By");
CashTransfer.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
CashTransfer.setInitWidths("70,150,100,100,150,100,200,120,100");
CashTransfer.setColAlign("center,center,center,left,right,left,center,center,center");
CashTransfer.setColTypes("ro,ro,ro,ro,ron,ro,ro,ro,ro");
CashTransfer.setColSorting("int,str,str,str,str,str,str");
CashTransfer.setEditable(false);
CashTransfer.attachEvent("onRowDblClicked", CashTransferDoubleClick);
PCashTransfer = new dataProcessor("finance-cash-transfer-update");
PCashTransfer.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PCashTransfer.setUpdateMode("Off");
PCashTransfer.init(CashTransfer);
CashTransfer.setNumberFormat("0,000",4,".",",");
CashTransfer.setSkin("dhx_skyblue");
CashTransfer.init();
refreshdatatransfer();
</script>
</div>
<div id="CashTransferDiv"></div>

@endsection