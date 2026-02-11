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
                <span class="active">Issue Lists</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProjectIssue" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselProjectIssueDet" name="ekselProjectIssueDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProjectIssue.toExcel('./export/generate.php');"  />
		</div>
		<div id="divlistProjectList"></div>

</div>	
<script>

function ProjectIssueKlik() {

ProjectIssueID = ProjectIssue.getSelectedId();

if (ProjectIssueID !=''){
LoadM('divlistProjectList','project-issues-detail?Task_ID=' + ProjectIssueID);
}
return true;
}
ProjectIssue = new dhtmlXGridObject('ProjectIssue');
ProjectIssue.setImagePath("./dhtmlx/imgs/");
ProjectIssue.setHeader("No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs");
ProjectIssue.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ProjectIssue.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
ProjectIssue.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
ProjectIssue.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
ProjectIssue.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
ProjectIssue.enableSmartRendering(true, 150);
ProjectIssue.setNumberFormat("0,000",9,".",",");
ProjectIssue.setNumberFormat("0,000",10,".",",");
ProjectIssue.setNumberFormat("0,000",11,".",",");
ProjectIssue.attachEvent("onRowDblClicked", ProjectIssueKlik);
ProjectIssue.setSkin("dhx_skyblue");
ProjectIssue.init();
refreshdata();

function refreshdata(){
	ProjectIssue.clearAndLoad('xml-project-lists');

}



</script>
@endsection