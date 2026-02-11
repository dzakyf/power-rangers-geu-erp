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
                <span class="active">Team Lists</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProjectTeam" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselProjectTeamDet" name="ekselProjectTeamDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProjectTeam.toExcel('./export/generate.php');"  />
		</div>
		<div id="divlistProjectList"></div>

</div>	
<script>

function ProjectTeamKlik() {

ProjectTeamID = ProjectTeam.getSelectedId();

if (ProjectTeamID !=''){
LoadM('divlistProjectList','project-team-detail?Task_ID=' + ProjectTeamID);
}
return true;
}
ProjectTeam = new dhtmlXGridObject('ProjectTeam');
ProjectTeam.setImagePath("./dhtmlx/imgs/");
ProjectTeam.setHeader("No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs");
ProjectTeam.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
ProjectTeam.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
ProjectTeam.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
ProjectTeam.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
ProjectTeam.enableSmartRendering(true, 150);
ProjectTeam.setNumberFormat("0,000",9,".",",");
ProjectTeam.setNumberFormat("0,000",10,".",",");
ProjectTeam.setNumberFormat("0,000",11,".",",");
ProjectTeam.attachEvent("onRowDblClicked", ProjectTeamKlik);
ProjectTeam.setSkin("dhx_skyblue");
ProjectTeam.init();
refreshdata();

function refreshdata(){
	ProjectTeam.clearAndLoad('xml-project-team');

}



</script>
@endsection