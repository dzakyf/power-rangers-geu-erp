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
                <a href="#">Bank WITHDRAWAL</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Archive</span>
            </li>
        </ol>
    </div> 
    <!-- End Main Content Header -->

	<!-- Stats Card -->
	<div class="row">
	

                <div id="BankWithDrawal" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
                <div id="divtom">
                 <input type="button" id="BankWithDrawalExp" NAME="BankWithDrawalExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="BankWithDrawal.toExcel('./export/generate.php');" />
				     
                </div>
<script>


function refreshdataWithDrawal(){
	
	BankWithDrawal.clearAndLoad("xml-finance-report-archive-bank-withdrawal");

}


function BankWithDrawalDoubleClick() {

	BankWithDrawalMID = BankWithDrawal.getSelectedId();
	LoadM('BankWithDrawalDiv','finance-report-archive-bank-withdrawal-detail?ID=' + BankWithDrawalMID );
	
}

BankWithDrawal = new dhtmlXGridObject('BankWithDrawal');
BankWithDrawal.setImagePath("./dhtmlx/imgs/");
BankWithDrawal.setHeader("No.,Task No,From Bank,To Account,WithDrawal Val,Last Status,Ref WithDrawal,WithDrawal Date,Created By,Department");
BankWithDrawal.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total,,,,,");
BankWithDrawal.setInitWidths("70,150,130,100,100,100,100,120,100,100");
BankWithDrawal.setColAlign("center,center,center,left,right,left,center,center,center,center");
BankWithDrawal.setColTypes("ro,ro,ro,ro,ron,ro,ro,ro,ro,ro");
BankWithDrawal.setColSorting("int,str,str,str,str,str,str,str");
BankWithDrawal.setEditable(false);
BankWithDrawal.attachEvent("onRowDblClicked", BankWithDrawalDoubleClick);
BankWithDrawal.setNumberFormat("0,000",4,".",",");
BankWithDrawal.setSkin("dhx_skyblue");
BankWithDrawal.init();
refreshdataWithDrawal();

</script>
</div>
<div id="BankWithDrawalDiv"></div>

@endsection