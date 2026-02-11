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
                <a href="/dashboard1">Bank Deposit</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Management</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="BankDeposit" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                    <input type="button" id="BankDepositAdd" NAME="BankDepositAdd" value="Add New" />
                    <input type="button" id="BankDepositDel" NAME="BankDepositDel" value="Cancel" />
                   
                </div>
<script>


function refreshdataDeposit(){
	
	BankDeposit.clearAndLoad("xml-finance-bank-Deposit");

}
$('#BankDepositAdd').click(function(){

	LoadM('BankDepositDiv','finance-bank-deposit-add');

});

$('#BankDepositCancel').click(function(){

	dhtmlx.confirm('Are you sure want to cancel Deposit?', 'Bank Deposit', function(r) {
			
		if (r==true){
				idx = BankDeposit.cells(BankDeposit.getSelectedId(),1).getValue();
				LoadM('BankDepositDiv','finance-bank-deposit-cancel?Task_ID=' + idx);
		
			
		}
	})
	
	
});
function BankDepositDoubleClick() {

	BankDepositMID = BankDeposit.getSelectedId();
	LoadM('BankDepositDiv','finance-bank-deposit-detail?ID=' + BankDepositMID );
	
}

BankDeposit = new dhtmlXGridObject('BankDeposit');
BankDeposit.setImagePath("./dhtmlx/imgs/");
BankDeposit.setHeader("No.,Task No,To Account No,To Account Name,Deposit Value,Last Status,Ref Deposit,Deposit Date,Created By");
BankDeposit.attachFooter("Balance,#cspan,#cspan,#cspan,#cspan,,#cspan,,");
BankDeposit.setInitWidths("70,150,130,150,100,100,120,120,100");
BankDeposit.setColAlign("center,center,center,left,right,left,center,center,center");
BankDeposit.setColTypes("ro,ro,ro,ro,ron,ro,ro,ro,ro");
BankDeposit.setColSorting("int,str,str,str,str,str,str");
BankDeposit.setEditable(true);
BankDeposit.attachEvent("onRowDblClicked", BankDepositDoubleClick);
BankDeposit.setNumberFormat("0,000",4,".",",");
BankDeposit.setSkin("dhx_skyblue");
BankDeposit.init();
refreshdataDeposit();

</script>
</div>
<div id="BankDepositDiv"></div>

@endsection