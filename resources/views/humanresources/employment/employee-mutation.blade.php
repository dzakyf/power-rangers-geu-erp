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
        <h1>Human Resources</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Employment</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Mutation</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		
		<div id="EmployeeMutation" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
		<div><input type="button" id="ekselAttendantListDet" name="ekselAttendantListDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="EmployeeMutation.toExcel('./export/generate.php');"  />
			</div>
	</div>	
				<div id="divlistProjectOrg"></div>

<script>

function EmployeeMutationKlik() {

EmployeeMutationID = EmployeeMutation.cells(EmployeeMutation.getSelectedId(),2).getValue();

if (EmployeeMutationID !=''){
LoadM('divlistProjectOrg','employee-mutation-detail?ID=' + EmployeeMutationID);
}
return true;
}
EmployeeMutation = new dhtmlXGridObject('EmployeeMutation');
EmployeeMutation.setImagePath("./dhtmlx/imgs/");
EmployeeMutation.setHeader("No.,Name,NIK,Gender,Marital Status,Age,Location,Subsection,Last Position,Department,Directorat,Employee Status,Last Education,Last Education Major");
EmployeeMutation.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter");
EmployeeMutation.setInitWidths("70,150,100,100,100,100,100,120,100,100,100,100,100,100");
EmployeeMutation.setColAlign("center,left,center,center,center,center,center,center,center,center,center,center,center");
EmployeeMutation.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
EmployeeMutation.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
EmployeeMutation.attachEvent("onRowDblClicked", EmployeeMutationKlik);
EmployeeMutation.setSkin("dhx_skyblue");
EmployeeMutation.init();
refreshdata();

function refreshdata(){
	EmployeeMutation.clearAndLoad('xml-employee-mutation');

}
</script>
@endsection