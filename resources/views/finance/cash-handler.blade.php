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
                <a href="/dashboard1">Cash Handler</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashHandler" style="width:auto;height:400px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="CashHandlerAdd" NAME="CashHandlerAdd" value="Baru" class="btn btn-success mt-2 mr-2" />           <input type="button" id="CashHandlerDel" NAME="CashHandlerDel" value="Rilis" class="btn btn-warning mt-2 mr-2" />                   
                </div>
<script>

$('#SubmitCashHandler').click(function(){
							   
PCashHandler.sendData();
alert("Data Succesfully Submitted!");
setTimeout(function(){
	CashHandler.clearAll();
},1500);
							   
							   });


$('#CashHandlerAdd').click(function(){
		LoadM('CashHandlerDiv','finance-cash-handle-add');

	});


function refreshdataHandler(){
	
	CashHandler.clearAndLoad("xml-finance-cash-handler");

}

$('#CashHandlerDel').click(function(){
	dhtmlx.confirm('Hapus Pemegang Kas Terpilih?', 'Cash Handler', function(r) {
			
		if (r==true){
				$("#CashHandlerDiv").html('');
				CashHandler.deleteSelectedRows();
				PCashHandler.sendData();
			 	alert('Pemegang Kas telah dihapus!');
				setTimeout(function(){
					refreshdataHandler();
				},2000);
			
		}
	})
	
});

CashHandler = new dhtmlXGridObject('CashHandler');
CashHandler.setImagePath("./dhtmlx/imgs/");
CashHandler.setHeader("No.,Name,Handler NIK,Start Balance,Current Balance,Status,Created Date,Created By");
CashHandler.setInitWidths("70,100,150,100,100,100,120,100");
CashHandler.setColAlign("center,center,center,left,center,center,center,center");
CashHandler.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
CashHandler.setColSorting("int,str,str,str,str,str");
CashHandler.setEditable(false);
PCashHandler = new dataProcessor("finance-cash-handle-update");
PCashHandler.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PCashHandler.setUpdateMode("Off");
PCashHandler.init(CashHandler);
CashHandler.setSkin("dhx_skyblue");
CashHandler.init();
CashHandler.loadXML("xml-finance-cash-handler");
refreshdataHandler();
</script>
</div>
<div id="CashHandlerDiv"></div>

@endsection