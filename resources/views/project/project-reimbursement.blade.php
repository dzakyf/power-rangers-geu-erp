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
                <span class="active">Reimbursement</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProjectReimbursementLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div id="divlistProjectReimbursement"></div>

	</div>	
<script>

function ProjectReimbursementListsKlik() {

ProjectReimbursementListsID = ProjectReimbursementLists.getSelectedId();

if (ProjectReimbursementListsID !=''){
LoadM('divlistProjectReimbursement','project-reimbursement-detail?ID=' + ProjectReimbursementListsID);
}
return true;
}
ProjectReimbursementLists = new dhtmlXGridObject('ProjectReimbursementLists');
ProjectReimbursementLists.setImagePath("./dhtmlx/imgs/");
ProjectReimbursementLists.setHeader("No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs");
ProjectReimbursementLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ProjectReimbursementLists.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
ProjectReimbursementLists.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
ProjectReimbursementLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
ProjectReimbursementLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
ProjectReimbursementLists.enableSmartRendering(true, 150);
ProjectReimbursementLists.setNumberFormat("0,000",9,".",",");
ProjectReimbursementLists.setNumberFormat("0,000",10,".",",");
ProjectReimbursementLists.setNumberFormat("0,000",11,".",",");
ProjectReimbursementLists.attachEvent("onRowDblClicked", ProjectReimbursementListsKlik);
ProjectReimbursementLists.setSkin("dhx_skyblue");
ProjectReimbursementLists.init();
refreshdata();

function refreshdata(){
	ProjectReimbursementLists.clearAndLoad('xml-project-lists');

}


</script>
@endsection