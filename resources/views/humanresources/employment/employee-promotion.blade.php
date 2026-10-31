<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	
	?>

@extends('layout.mainlayout-hrd')

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
                <span class="active">Promotion</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		
		<div id="EmployeePromotion" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
		<div><input type="button" id="ekselAttendantListDet" name="ekselAttendantListDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="EmployeePromotion.toExcel('./export/generate.php');"  />
			</div>
	</div>	
				<div id="divlistProjectOrg"></div>

<script>

function EmployeePromotionKlik() {

EmployeePromotionID = EmployeePromotion.cells(EmployeePromotion.getSelectedId(),2).getValue();

if (EmployeePromotionID !=''){
LoadM('divlistProjectOrg','employee-promotion-detail?ID=' + EmployeePromotionID);
}
return true;
}
EmployeePromotion = new dhtmlXGridObject('EmployeePromotion');
EmployeePromotion.setImagePath("./dhtmlx/imgs/");
EmployeePromotion.setHeader("No.,Name,NIK,Gender,Marital Status,Age,Location,Subsection,Last Position,Department,Directorat,Employee Status,Last Education,Last Education Major");
EmployeePromotion.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter");
EmployeePromotion.setInitWidths("70,150,100,100,100,100,100,120,100,100,100,100,100,100");
EmployeePromotion.setColAlign("center,left,center,center,center,center,center,center,center,center,center,center,center");
EmployeePromotion.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
EmployeePromotion.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
EmployeePromotion.attachEvent("onRowDblClicked", EmployeePromotionKlik);
EmployeePromotion.setSkin("dhx_skyblue");
EmployeePromotion.init();
refreshdata();

function refreshdata(){
	EmployeePromotion.clearAndLoad('xml-employee-promotion');

}
</script>
@endsection