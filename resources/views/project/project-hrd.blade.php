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
                <span class="active">Human Resources</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProjectHRD" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
		<div id="divlistProjectOrg"></div>
	</div>	
		
<script>

function ProjectHRDKlik() {

ProjectHRDID = ProjectHRD.cells(ProjectHRD.getSelectedId(),1).getValue();

if (ProjectHRDID !=''){
LoadM('divlistProjectOrg','project-hrd-detail?ID=' + ProjectHRDID);
}
return true;
}
ProjectHRD = new dhtmlXGridObject('ProjectHRD');
ProjectHRD.setImagePath("./dhtmlx/imgs/");
ProjectHRD.setHeader("No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs");
ProjectHRD.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ProjectHRD.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
ProjectHRD.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
ProjectHRD.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
ProjectHRD.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
ProjectHRD.enableSmartRendering(true, 150);
ProjectHRD.setNumberFormat("0,000",9,".",",");
ProjectHRD.setNumberFormat("0,000",10,".",",");
ProjectHRD.setNumberFormat("0,000",11,".",",");
ProjectHRD.attachEvent("onRowDblClicked", ProjectHRDKlik);
ProjectHRD.setSkin("dhx_skyblue");
ProjectHRD.init();
refreshdata();

function refreshdata(){
	ProjectHRD.clearAndLoad('xml-project-lists');

}
</script>
@endsection