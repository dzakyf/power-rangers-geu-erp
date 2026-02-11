<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Finance</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Account Payable</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data History</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="APHistory" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselAPHistoryDet" name="ekselAPHistoryDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="APHistory.toExcel('./export/generate.php');"  />
		</div>
	</div>	
	<div id="divlistcustomer"></div>

<script>

function ApprovalInvKlik() {

CustomerListsID = APHistory.getSelectedId();

if (CustomerListsID !=''){
	LoadM('divlistcustomer','finance-ap-history-detail?ID=' + CustomerListsID);
}
return true;
}
APHistory = new dhtmlXGridObject('APHistory');
APHistory.setImagePath("./dhtmlx/imgs/");
APHistory.setHeader("No.,Invoice No,Invoice Date,PO No,Reference,Total AR,Remain Val,Due Date,Subject Payment,Department,Vendor No, Vendor Name,Status");
APHistory.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#combo_filter");
APHistory.setInitWidths("60,120,100,100,150,100,100,100,360,110,100,120,100,100");
APHistory.setColAlign("center,center,center,center,center,right,right,center,center,center,left,center,center,center");
APHistory.setColTypes("ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro,ro,ro");
APHistory.setEditable(true);
APHistory.setSkin("dhx_skyblue");
APHistory.setDateFormat("%Y-%m-%d");
APHistory.setNumberFormat("0,000.00",5,".",",");
APHistory.setNumberFormat("0,000.00",6,".",",");
APHistory.attachEvent("onRowDblClicked", ApprovalInvKlik);

APHistory.init();
refreshdata();

function refreshdata(){
	APHistory.clearAndLoad('xml-finance-ap-history');

}
</script>
@endsection