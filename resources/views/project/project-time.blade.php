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
                <a href="#">Project</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Time Tracking</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProjectTime" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselProjectTimeDet" name="ekselProjectTimeDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProjectTime.toExcel('./export/generate.php');"  />
		</div>

	</div>		<div id="divlistProjectList"></div>
	
<script>

function ProjectTimeKlik() {

ProjectTimeID = ProjectTime.cells(ProjectTime.getSelectedId(),1).getValue();

if (ProjectTimeID !=''){
LoadM('divlistProjectList','project-times-detail?Task_ID=' + ProjectTimeID);
}
return true;
}
ProjectTime = new dhtmlXGridObject('ProjectTime');
ProjectTime.setImagePath("./dhtmlx/imgs/");
ProjectTime.setHeader("No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs");
ProjectTime.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ProjectTime.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
ProjectTime.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
ProjectTime.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
ProjectTime.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
ProjectTime.enableSmartRendering(true, 150);
ProjectTime.setNumberFormat("0,000",9,".",",");
ProjectTime.setNumberFormat("0,000",10,".",",");
ProjectTime.setNumberFormat("0,000",11,".",",");
ProjectTime.attachEvent("onRowDblClicked", ProjectTimeKlik);
ProjectTime.setSkin("dhx_skyblue");
ProjectTime.init();
refreshdata();

function refreshdata(){
	ProjectTime.clearAndLoad('xml-project-lists');

}



</script>
@endsection