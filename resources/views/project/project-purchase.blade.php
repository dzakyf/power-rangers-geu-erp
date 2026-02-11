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
        <h1>Project Management</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Job Costing</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Purchase Request</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProjectHRDPurchase" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
		<div id="divlistProjectOrg"></div>
	</div>	
		
<script>

function ProjectHRDPurchaseKlik() {

ProjectHRDPurchaseID = ProjectHRDPurchase.cells(ProjectHRDPurchase.getSelectedId(),1).getValue();

if (ProjectHRDPurchaseID !=''){
LoadM('divlistProjectOrg','project-purchase-detail?ID=' + ProjectHRDPurchaseID);
}
return true;
}
ProjectHRDPurchase = new dhtmlXGridObject('ProjectHRDPurchase');
ProjectHRDPurchase.setImagePath("./dhtmlx/imgs/");
ProjectHRDPurchase.setHeader("No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs");
ProjectHRDPurchase.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ProjectHRDPurchase.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
ProjectHRDPurchase.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
ProjectHRDPurchase.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
ProjectHRDPurchase.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
ProjectHRDPurchase.enableSmartRendering(true, 150);
ProjectHRDPurchase.setNumberFormat("0,000",9,".",",");
ProjectHRDPurchase.setNumberFormat("0,000",10,".",",");
ProjectHRDPurchase.setNumberFormat("0,000",11,".",",");
ProjectHRDPurchase.attachEvent("onRowDblClicked", ProjectHRDPurchaseKlik);
ProjectHRDPurchase.setSkin("dhx_skyblue");
ProjectHRDPurchase.init();
refreshdata();

function refreshdata(){
	ProjectHRDPurchase.clearAndLoad('xml-project-lists');

}
</script>
@endsection