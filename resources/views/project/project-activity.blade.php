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
                <span class="active">Activity Lists</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProjectActivity" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselProjectActivityDet" name="ekselProjectActivityDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProjectActivity.toExcel('./export/generate.php');"  />
		</div>
		<div id="divlistProjectList"></div>

</div>	
<script>

function ProjectActivityKlik() {

ProjectActivityID = ProjectActivity.getSelectedId();
projecttask = ProjectActivity.cells(ProjectActivity.getSelectedId(),1).getValue();

if (ProjectActivityID !=''){
LoadM('divlistProjectList','project-activity2?Task_ID=' + ProjectActivityID + "&Task_No=" + projecttask);

}
return true;
}
ProjectActivity = new dhtmlXGridObject('ProjectActivity');
ProjectActivity.setImagePath("./dhtmlx/imgs/");
ProjectActivity.setHeader("No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs");
ProjectActivity.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ProjectActivity.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
ProjectActivity.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
ProjectActivity.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
ProjectActivity.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
ProjectActivity.enableSmartRendering(true, 150);
ProjectActivity.setNumberFormat("0,000",9,".",",");
ProjectActivity.setNumberFormat("0,000",10,".",",");
ProjectActivity.setNumberFormat("0,000",11,".",",");
ProjectActivity.attachEvent("onRowDblClicked", ProjectActivityKlik);
ProjectActivity.setSkin("dhx_skyblue");
ProjectActivity.init();
refreshdata();

function refreshdata(){
	ProjectActivity.clearAndLoad('xml-project-activity');

}



</script>
@endsection