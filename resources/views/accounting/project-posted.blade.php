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
        <h1>Accounting</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Project Opportunity</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Posted PO</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="OpporActive" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselOpporActiveDet" name="ekselOpporActiveDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="OpporActive.toExcel('./export/generate.php');"  />
		</div>

	</div>	
		<div id="divlistOpportunity"></div>
<script>

function OpporActiveKlik() {

OpporActiveID = OpporActive.getSelectedId();

if (OpporActiveID !=''){
LoadM('divlistOpportunity','accounting-project-posted-detail?ID=' + OpporActive.cells(OpporActiveID,1).getValue());
}
return true;
}
OpporActive = new dhtmlXGridObject('OpporActive');
OpporActive.setImagePath("./dhtmlx/imgs/");
OpporActive.setHeader("No,Task No,Sales Person,Subject,Company Name,Reference,Created Date,Estimated Rev,Category,Expired Date,Note,Posted Date");
OpporActive.attachHeader("#rspan,#numeric_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
OpporActive.setInitWidths("60,120,100,100,200,120,120,100,100,100,200,100");
OpporActive.setColAlign("center,right,left,left,left,left,left,right,left,left,left,center");
OpporActive.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
OpporActive.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro");
OpporActive.enableSmartRendering(true, 150);
OpporActive.setNumberFormat("0,000",7,".",",");
OpporActive.attachEvent("onRowDblClicked", OpporActiveKlik);
OpporActive.setSkin("dhx_skyblue");
OpporActive.init();
refreshdata();

function refreshdata(){
	OpporActive.clearAndLoad('xml-accounting-project-posted');

}
</script>
@endsection