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
                <span class="active">OS Conversion</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		
		<div id="osconversion" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
		<div><input type="button" id="ekselAttendantListDet" name="ekselAttendantListDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="osconversion.toExcel('./export/generate.php');"  />
			</div>
	</div>	
				<div id="divlistProjectOrg"></div>

<script>

function osconversionKlik() {

osconversionID = osconversion.cells(osconversion.getSelectedId(),2).getValue();

if (osconversionID !=''){
LoadM('divlistProjectOrg','os-conversion-detail?ID=' + osconversionID);
}
return true;
}
osconversion = new dhtmlXGridObject('osconversion');
osconversion.setImagePath("./dhtmlx/imgs/");
osconversion.setHeader("No.,Name,NIK,Gender,Marital Status,Age,Location,Subsection,Last Position,Department,Directorat,Employee Status,Last Education,Last Education Major");
osconversion.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#text_filter,#text_filter,#text_filter");
osconversion.setInitWidths("70,150,100,100,100,100,100,120,100,100,100,100,100,100");
osconversion.setColAlign("center,left,center,center,center,center,center,center,center,center,center,center,center");
osconversion.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
osconversion.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
osconversion.attachEvent("onRowDblClicked", osconversionKlik);
osconversion.setSkin("dhx_skyblue");
osconversion.init();
refreshdata();

function refreshdata(){
	osconversion.clearAndLoad('xml-os-conversion');

}
</script>
@endsection