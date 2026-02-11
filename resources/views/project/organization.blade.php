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
                <a href="#">Organization</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">User Responsibility</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProjectOrgLists" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
	</div>	
		<div id="divlistProjectOrg"></div>
<script>

function ProjectOrgListsKlik() {

ProjectOrgListsID = ProjectOrgLists.getSelectedId();

if (ProjectOrgListsID !=''){
LoadM('divlistProjectOrg','project-organization-detail?ID=' + ProjectOrgListsID);
}
return true;
}
ProjectOrgLists = new dhtmlXGridObject('ProjectOrgLists');
ProjectOrgLists.setImagePath("./dhtmlx/imgs/");
ProjectOrgLists.setHeader("No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs");
ProjectOrgLists.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
ProjectOrgLists.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
ProjectOrgLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
ProjectOrgLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
ProjectOrgLists.enableSmartRendering(true, 150);
ProjectOrgLists.setNumberFormat("0,000",9,".",",");
ProjectOrgLists.setNumberFormat("0,000",10,".",",");
ProjectOrgLists.setNumberFormat("0,000",11,".",",");
ProjectOrgLists.attachEvent("onRowDblClicked", ProjectOrgListsKlik);
ProjectOrgLists.setSkin("dhx_skyblue");
ProjectOrgLists.init();
refreshdata();

function refreshdata(){
	ProjectOrgLists.clearAndLoad('xml-project-lists');

}
</script>
@endsection