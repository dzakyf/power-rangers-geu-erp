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
                <span class="active">Data Lists</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProjectListLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselProjectListListsDet" name="ekselProjectListListsDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ProjectListLists.toExcel('./export/generate.php');"  />
			<input type="button" id="delets" name="delets" value="Cancel Project" class="btn btn-danger mt-2 mr-2" onclick="delsProjectList()"  />
		</div>

	</div>	
		<div id="divlistProjectList"></div>
<script>

function ProjectListListsKlik() {

ProjectListListsID = ProjectListLists.getSelectedId();

if (ProjectListListsID !=''){
LoadM('divlistProjectList','project-lists-detail?ID=' + ProjectListListsID);
}
return true;
}
ProjectListLists = new dhtmlXGridObject('ProjectListLists');
ProjectListLists.setImagePath("./dhtmlx/imgs/");
ProjectListLists.setHeader("No,Project ID,Subject,Date,Description,Source Reference,Reference,Customer,Vendor,Created Date,Estimated Rev,Estimated Costs,Running Costs");
ProjectListLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ProjectListLists.setInitWidths("60,150,250,100,250,120,100,100,120,120,100,100,100,100");
ProjectListLists.setColAlign("center,left,left,left,left,center,left,left,left,center,right,right,right");
ProjectListLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str");
ProjectListLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ron,ron");
ProjectListLists.enableSmartRendering(true, 150);
ProjectListLists.setNumberFormat("0,000",9,".",",");
ProjectListLists.setNumberFormat("0,000",10,".",",");
ProjectListLists.setNumberFormat("0,000",11,".",",");
PProjectListLists = new dataProcessor("project-lists-submit");
PProjectListLists.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PProjectListLists.setTransactionMode("POST", true);
PProjectListLists.setUpdateMode("Off");
PProjectListLists.init(ProjectListLists);
ProjectListLists.attachEvent("onRowDblClicked", ProjectListListsKlik);
ProjectListLists.setSkin("dhx_skyblue");
ProjectListLists.init();
refreshdata();

function refreshdata(){
	ProjectListLists.clearAndLoad('xml-project-lists');

}

function delsProjectList(){
	dhtmlx.confirm('Cancel this Project?', 'Cancel Project', function(r) {
			
			if (r==true){
				ProjectListLists.deleteSelectedRows();
				PProjectListLists.sendData();
					alert('Project Successfully Canceled!');
					setTimeout(function(){
						refreshdata();
					},1000);
			}
	})
}


</script>
@endsection