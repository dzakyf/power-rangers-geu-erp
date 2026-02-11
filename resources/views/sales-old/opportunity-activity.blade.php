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
                <a href="#">Activity</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Lists</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ActivityLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselActivityListsDet" name="ekselActivityListsDet" value="Export Excel" onclick="ActivityLists.toExcel('./export/generate.php');"  />
			<input type="button" id="addactivity" name="addactivity" value="Add Activity" onclick="addactivity()"  />
			<input type="button" id="delets" name="delets" value="Delete Activity" onclick="delsActivity()"  />
			<input type="button" id="edtss" name="edtss" value="Edit Activity" onclick="EditActivity()"  />
		</div>
		
	</div>	
	<div id = 'divlistActivity'></div>

<script>

			
ActivityLists = new dhtmlXGridObject('ActivityLists');
ActivityLists.setImagePath("./dhtmlx/imgs/");
ActivityLists.setHeader("No,Task ID,PO No,Sales Person,Date,Activity Type,Priority,Activity Desc,Company Name,Activity Subject,Note");
ActivityLists.setInitWidths("60,100,100,100,100,100,100,200,200,200,200");
ActivityLists.setColAlign("center,center,center,left,center,center,center,center,center,center,left");
ActivityLists.setColSorting("int,str,str,str,str,str,str,str,str");
ActivityLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
ActivityLists.enableSmartRendering(true, 150);
PActivityLists = new dataProcessor("opportunity-activity-submit");
PActivityLists.setTransactionMode("POST", true);
PActivityLists.setUpdateMode("Off");
PActivityLists.init(ActivityLists);
ActivityLists.setSkin("dhx_skyblue");
ActivityLists.init();
ActivityListsRefresh();
function ActivityListsRefresh(){
	ActivityLists.clearAndLoad('xml-sales-opportunity-activity');
	
}

function UpdateGrid_Activity() {
	var out = 0;
    for (var i = 0; i < ActivityLists.getRowsNum(); i++) {
		rowIdMov=ActivityLists.getRowId(i);
        ActivityListsProc.setUpdated(rowIdMov,true);
    }
	return out;
}

function addactivity() {
	taskid = ActivityLists.getSelectedId();
	var nama = ActivityLists.cells(taskid,8).getValue();
	LoadM('divlistActivity','sales-opportunity-activity-add?ID=' + taskid + "&Customer=" + encodeURI(nama));
}
function EditActivity() {
	taskid = ActivityLists.getSelectedId();
	LoadM('divlistActivity','opportunity-activity-detail?ID='+taskid);
}

function delsActivity(){
	dhtmlx.confirm('Delete this Activity?', 'Activity', function(r) {
			
			if (r==true){
				ActivityLists.deleteSelectedRows();
				PActivityLists.sendData();
					alert('Data Successfully Deleted!');
					setTimeout(function(){
						ActivityListsRefresh();
					},1000);
			}
	})
}

function SubmitGrid_Activity(){
	ActivityListsProc.sendData();
	alert('Data Successfully Updated!');
	ActivityLists.clearAll();
	ActivityListsRefresh();	
}


</script>
@endsection