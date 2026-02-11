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
                <a href="#">Opportunity</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Active Running</span>
            </li>
        </ol>
    </div> 
	<div class="row">
		<div id="OpporFinal" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselOpporFinalDet" name="ekselOpporFinalDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OpporFinal.toExcel('./export/generate.php');"  />
		</div>

	</div>	
		<div id="divlistOpportunity"></div>
<script>

function OpporFinalKlik() {

OpporFinalID = OpporFinal.getSelectedId();

if (OpporFinalID !=''){
LoadM('divlistOpportunity','opportunity-outstanding-detail?ID=' + OpporFinalID);
}
return true;
}
OpporFinal = new dhtmlXGridObject('OpporFinal');
OpporFinal.setImagePath("./dhtmlx/imgs/");
OpporFinal.setHeader("No,Task ID,Sales Person,Subject,Company Name,Reference,Created Date,Estimated Rev,Category,Expired Date,Note");
OpporFinal.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#numeric_filter,#text_filter,#text_filter,#text_filter");
OpporFinal.setInitWidths("60,80,100,100,200,120,120,100,100,100,200");
OpporFinal.setColAlign("center,right,left,left,left,left,left,right,left,left,left");
OpporFinal.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
OpporFinal.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro");
OpporFinal.enableSmartRendering(true, 150);
OpporFinal.setNumberFormat("0,000",7,".",",");
OpporFinal.attachEvent("onRowDblClicked", OpporFinalKlik);
OpporFinal.setSkin("dhx_skyblue");
OpporFinal.init();
refreshdata();

function refreshdata(){
	OpporFinal.clearAndLoad('xml-sales-opportunity-finalize');

}
</script>
@endsection