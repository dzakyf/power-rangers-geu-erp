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
                <span class="active">Data Internal Employees</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		
		<div id="EmployeeLists" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
		<div><input type="button" id="ekselAttendantListDet" name="ekselAttendantListDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="EmployeeLists.toExcel('./export/generate.php');"  />
			</div>
	</div>	
				<div id="divlistProjectOrg"></div>

<script>

function EmployeeListsKlik() {

EmployeeListsID = EmployeeLists.cells(EmployeeLists.getSelectedId(),2).getValue();
nma = EmployeeLists.cells(EmployeeLists.getSelectedId(),1).getValue();

if (EmployeeListsID !=''){
LoadM('divlistProjectOrg','employee-lists-detail?ID=' + EmployeeListsID + "&nama=" + encodeURI(nma) );
}
return true;
}
EmployeeLists = new dhtmlXGridObject('EmployeeLists');
EmployeeLists.setImagePath("./dhtmlx/imgs/");
EmployeeLists.setHeader("No.,Name,NIK,Gender,Marital Status,Age,Last Position,Department,Directorat,Employee Status,Location,Last Education,Last Education Major");
EmployeeLists.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#text_filter,#text_filter");
EmployeeLists.setInitWidths("70,150,100,100,100,100,100,120,100,100,100,100,100");
EmployeeLists.setColAlign("center,left,center,center,center,center,center,center,center,center,center,center");
EmployeeLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
EmployeeLists.setColSorting("int,str,str,str,str,str,str,str,str,str");
EmployeeLists.attachEvent("onRowDblClicked", EmployeeListsKlik);
EmployeeLists.setSkin("dhx_skyblue");
EmployeeLists.init();
refreshdata();

function refreshdata(){
	EmployeeLists.clearAndLoad('xml-employee-lists');

}
</script>
@endsection