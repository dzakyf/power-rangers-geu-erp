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
                <a href="#">Master</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Master Template</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="MasterTemplate" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselMasterTemplateDet" name="ekselMasterTemplateDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="MasterTemplate.toExcel('./export/generate.php');"  />
				<input type="button" id="delets" name="delets" value="Delete Template" onclick="delsRequisition()"  class="btn btn-info mt-2 mr-2" onclick="delsRequisition()"  /> 
			<input type="button" id="NewReq" name="NewReq" onclick="newreq()"class="btn btn-success mt-2 mr-2"  value="New Template"/>
		
		</div>

	</div>	
		<div id="divlistRecruitment"></div>
<script>

function delsRequisition(){
	dhtmlx.confirm('Disable this Template?', 'Template', function(r) {
			
			if (r==true){
				MasterTemplate.deleteSelectedRows();
				PMasterTemplate.sendData();
					alert('Template Disabled!');
					setTimeout(function(){
						refreshdata();
					},1000);
			}
	})
}

function newreq() {
	LoadM('divlistRecruitment','hrd-master-template-new');
}


function MasterTemplateKlik() {

MasterTemplateID = MasterTemplate.getSelectedId();

if (MasterTemplateID !=''){
	LoadM('divlistRecruitment','hrd-master-template-detail?ID=' + MasterTemplateID);
}
return true;
}
MasterTemplate = new dhtmlXGridObject('MasterTemplate');
MasterTemplate.setImagePath("./dhtmlx/imgs/");
MasterTemplate.setHeader("No.,Template ID,Message Type,Status,Created By,Created Date");
MasterTemplate.setInitWidths("70,150,100,100,100,100,100,120,100,100,100");
MasterTemplate.setColAlign("center,left,center,center,center,center,center,center,center,center");
MasterTemplate.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
MasterTemplate.setColSorting("int,str,str,str,str,str,str,str");
MasterTemplate.enableKeyboardSupport(true);
MasterTemplate.attachEvent("onRowDblClicked", MasterTemplateKlik);
PMasterTemplate = new dataProcessor("hrd-master-template-submit");
PMasterTemplate.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PMasterTemplate.setTransactionMode("POST", true);
PMasterTemplate.init(MasterTemplate);
MasterTemplate.setSkin("dhx_skyblue");
MasterTemplate.init();
refreshdata();

function refreshdata(){
	MasterTemplate.clearAndLoad('xml-hrd-master-template');

}


</script>
@endsection