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
                <span class="active">Outstanding</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="OpportunityLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselOpportunityListsDet" name="ekselOpportunityListsDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OpportunityLists.toExcel('./export/generate.php');"  />
		</div>

	</div>	
		<div id="divlistOpportunity"></div>
<script>

function OpportunityListsKlik() {

OpportunityListsID = OpportunityLists.getSelectedId();

if (OpportunityListsID !=''){
LoadM('divlistOpportunity','opportunity-outstanding-detail?ID=' + OpportunityListsID);
}
return true;
}
OpportunityLists = new dhtmlXGridObject('OpportunityLists');
OpportunityLists.setImagePath("./dhtmlx/imgs/");
OpportunityLists.setHeader("No,Aging(days),Sales Person,Subject,Company Name,Reference,Created Date,Estimated Rev,Category,Expected Closing,Note,Status");
OpportunityLists.attachHeader("#rspan,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
OpportunityLists.setInitWidths("60,80,100,100,200,120,120,100,100,100,200,100");
OpportunityLists.setColAlign("center,right,left,left,left,left,left,right,left,left,left,center");
OpportunityLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
OpportunityLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro");
OpportunityLists.enableSmartRendering(true, 150);
OpportunityLists.setNumberFormat("0,000",7,".",",");
OpportunityLists.attachEvent("onRowDblClicked", OpportunityListsKlik);
OpportunityLists.setSkin("dhx_skyblue");
OpportunityLists.init();
refreshdata();

function refreshdata(){
	OpportunityLists.clearAndLoad('xml-opportunity-outstanding');

}
</script>
@endsection