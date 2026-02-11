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
                <a href="#">Other Data</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Resigned Employee</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ResignedEmployee" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
		<div id="DIVResignedEmployee"></div>
	</div>	
		
<script>

function ResignedEmployeeKlik() {

ResignedEmployeeID = ResignedEmployee.cells(ResignedEmployee.getSelectedId(),2).getValue();

if (ResignedEmployeeID !=''){
LoadM('DIVResignedEmployee','resigned-employee-detail?ID=' + ResignedEmployeeID);
}
return true;
}
ResignedEmployee = new dhtmlXGridObject('ResignedEmployee');
ResignedEmployee.setImagePath("./dhtmlx/imgs/");
ResignedEmployee.setHeader("No.,Name,NIK,Gender,Marital Status,Age,Last Position,Department,Directorat,Date Resigned,Resigned Note");
ResignedEmployee.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter");
ResignedEmployee.setInitWidths("70,150,100,100,100,100,100,120,100,100,100");
ResignedEmployee.setColAlign("center,left,center,center,center,center,center,center,center,center");
ResignedEmployee.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
ResignedEmployee.setColSorting("int,str,str,str,str,str,str,str");
ResignedEmployee.attachEvent("onRowDblClicked", ResignedEmployeeKlik);
ResignedEmployee.setSkin("dhx_skyblue");
ResignedEmployee.init();
refreshdata();

function refreshdata(){
	ResignedEmployee.clearAndLoad('xml-resigned-employee');

}
</script>
@endsection