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
        <h1>Sales</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Opportunity</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Job Requisition Lists</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="OpportunityLists" style="width:auto;height:420px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselOpportunityListsDet" name="ekselOpportunityListsDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OpportunityLists.toExcel('./export/generate.php');"  />
			<input type="button" id="delets" name="delets" value="Cancel Opportunity" class="btn btn-danger mt-2 mr-2" onclick="delsOpportunity()"  />
		</div>

	</div>	
		<div id="divlistOpportunity"></div>
<script>

function OpportunityListsKlik() {

	OpportunityListsID = OpportunityLists.getSelectedId();

	if (OpportunityListsID !=''){
		LoadM('divlistOpportunity','opportunity-job-requisition-detail?ID=' + OpportunityListsID);
	}
	return true;
}
OpportunityLists = new dhtmlXGridObject('OpportunityLists');
OpportunityLists.setImagePath("./dhtmlx/imgs/");
OpportunityLists.setHeader("No,Task ID,Sales Person,Subject,Job Position,Qty,PO No,Date to Close,Current Num of Candidates,Num Already Employed,Num of Rejected Candidates");
OpportunityLists.setInitWidths("60,190,100,200,200,70,120,100,100,100,100,100,100");
OpportunityLists.setColAlign("center,center,center,left,left,right,center,right,right,left,left,center,center");
OpportunityLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
OpportunityLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
OpportunityLists.attachEvent("onRowDblClicked", OpportunityListsKlik);
OpportunityLists.setSkin("dhx_skyblue");
OpportunityLists.enableMultiline(true);
OpportunityLists.init();
refreshdata();

function refreshdata(){
	OpportunityLists.clearAndLoad('xml-opportunity-job-requisition');

}


</script>
@endsection