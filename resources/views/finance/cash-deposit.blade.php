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
                <a href="/dashboard1">Cash Deposit</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="CashDeposit" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                   <input type="button" id="CashDepositExp" NAME="CashDepositExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="CashDeposit.toExcel('./export/generate.php');" />
                    <input type="button" id="CashDepositAdd" NAME="CashDepositAdd" value="Baru"  class="btn btn-success mt-2 mr-2" />
                    <input type="button" id="CashDepositDel" NAME="CashDepositDel" value="Batal" class="btn btn-danger mt-2 mr-2"  />
                   
                </div>
<script>


function refreshdataDeposit(){
	
	CashDeposit.clearAndLoad("xml-finance-cash-deposit");

}
$('#CashDepositAdd').click(function(){

	LoadM('CashDepositDiv','finance-cash-deposit-add');

});

$('#CashDepositDel').click(function(){

	dhtmlx.confirm('Batalkan Proses?', 'Cash Deposit', function(r) {
			
		if (r==true){
				$("#CashDepositDiv").html('');
				CashDeposit.deleteSelectedRows();
				PCashDeposit.sendData();
			 	alert('Tasklog dibatalkan!');
				setTimeout(function(){
					refreshdataDeposit();
				},2000);
		
			
		}
	})
	
	
});
function CashDepositDoubleClick() {

	CashDepositMID = CashDeposit.getSelectedId();
	LoadM('CashDepositDiv','finance-cash-deposit-detail?ID=' + CashDepositMID );
	
}

CashDeposit = new dhtmlXGridObject('CashDeposit');
CashDeposit.setImagePath("./dhtmlx/imgs/");
CashDeposit.setHeader("No.,Task No,Setor Ke,Akun Bank,Nilai,Status,Referensi,Tanggal Setor,Dibuat Oleh,Keterangan");
CashDeposit.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#numeric_filter,#combo_filter,#text_filter,#text_filter,#combo_filter,#text_filter");
CashDeposit.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,,#cspan,,,");
CashDeposit.setInitWidths("70,170,130,200,100,100,120,120,100,200");
CashDeposit.setColAlign("center,center,center,center,right,center,center,center,center,left");
CashDeposit.setColTypes("ro,ro,ro,ro,ron,ro,ro,ro,ro,ro");
CashDeposit.setColSorting("int,str,str,str,str,str,str,str");
CashDeposit.setEditable(true);
PCashDeposit = new dataProcessor("finance-cash-deposit-update");
PCashDeposit.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PCashDeposit.setUpdateMode("Off");
PCashDeposit.init(CashDeposit);
CashDeposit.attachEvent("onRowDblClicked", CashDepositDoubleClick);
CashDeposit.setNumberFormat("0,000",4,".",",");
CashDeposit.setSkin("dhx_skyblue");
CashDeposit.init();
refreshdataDeposit();

</script>
</div>
<div id="CashDepositDiv"></div>

@endsection