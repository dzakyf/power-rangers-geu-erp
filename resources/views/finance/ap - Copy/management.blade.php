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
                <span class="active">Data Management</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="APManagement" style="width:auto;height:450px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselAPManagementDet" name="ekselAPManagementDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="APManagement.toExcel('./export/generate.php');"  />
		</div>
	</div>	
	<div id="divlistcustomer"></div>

<script>

function ApprovalInvKlik() {

CustomerListsID = APManagement.getSelectedId();

if (CustomerListsID !=''){
	LoadM('divlistcustomer','finance-ap-management-detail?ID=' + CustomerListsID);
}
return true;
}
APManagement = new dhtmlXGridObject('APManagement');
APManagement.setImagePath("./dhtmlx/imgs/");
APManagement.setHeader("No.,Invoice No,Invoice Date,PO No,Reference,Total AR,Remain Val,Due Date,Subject Payment,Department,Vendor No, Vendor Name");
APManagement.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter");
APManagement.setInitWidths("60,120,100,100,150,100,100,100,360,110,100,120,100");
APManagement.setColAlign("center,center,center,center,center,right,right,center,center,center,left,center,center");
APManagement.setColTypes("ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro,ro");
APManagement.setEditable(true);
APManagement.setSkin("dhx_skyblue");
APManagement.setDateFormat("%Y-%m-%d");
APManagement.setNumberFormat("0,000.00",5,".",",");
APManagement.setNumberFormat("0,000.00",6,".",",");
APManagement.attachEvent("onRowDblClicked", ApprovalInvKlik);

APManagement.init();
refreshdata();

function refreshdata(){
	APManagement.clearAndLoad('xml-finance-ap-management');

}
</script>
@endsection