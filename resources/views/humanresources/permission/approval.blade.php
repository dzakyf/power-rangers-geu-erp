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
                <a href="#">Permission</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">APPROVAL</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="PermissionApproval" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
		<div id="CekReload">
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="PermissionApproval.toExcel('./export/generate.php');"  />
			<input type="button" id="ekselPODListDet2" name="ekselPODListDet2" value="Cancel" class="btn btn-danger mt-2 mr-2" onclick="batalkansalary();"  />
			<input type="button" id="apprv2" name="apprv2" value="APPROVE" class="btn btn-success mt-2 mr-2" onclick="approvesalary();"  />
		</div>
		<div id="PermissionApprovalDet"></div>
	</div>	
		
<script>

function batalkansalary() {
	dhtmlx.confirm('Confirm Cancel this Permission?', 'Generator', function(r) {
			
			if (r==true){
									
					PermissionApprovalID = PermissionApproval.getSelectedId();

					if (PermissionApprovalID !=''){
						
						LoadM('PermissionApprovalDet','permission-approval-cancel?ID=' + PermissionApprovalID);
						
					}
					return true;
			}
	});
}
function approvesalary() {
	dhtmlx.confirm('Confirm Approve this Permission?', 'Generator', function(r) {
			
			if (r==true){
				$.blockUI();
				PPermissionApproval.sendData();
				setTimeout(function(){
					
				refreshdata();
				$.unblockUI();
				},2000);
				return true;
			}
	});
}
function PermissionApprovalKlik() {

PermissionApprovalID = PermissionApproval.getSelectedId();

if (PermissionApprovalID !=''){
	
	LoadM('PermissionApprovalDet','permission-approval-detail?ID=' + PermissionApprovalID);
	
}
return true;
}

PermissionApproval = new dhtmlXGridObject('PermissionApproval');
PermissionApproval.setImagePath("./dhtmlx/imgs/");
PermissionApproval.setHeader("Approve,Permission ID,NIK,Name,Employee Category,Permission Category,Sub Category,Location,Note,Date");
PermissionApproval.attachHeader("#master_checkbox,#combo_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#text_filter");
PermissionApproval.setInitWidths("70,220,100,200,100,100,200,150,220,100,100,100,100,100,100");
PermissionApproval.setColAlign("center,left,center,center,center,center,center,center,center,center");
PermissionApproval.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro,ro,ro");
PermissionApproval.setColSorting("int,str,str,str,str,str,str,str");
PermissionApproval.attachEvent("onRowDblClicked", PermissionApprovalKlik);
PermissionApproval.setSkin("dhx_skyblue");
PPermissionApproval = new dataProcessor("permission-approval-update");
	PPermissionApproval.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }		  
    })
PPermissionApproval.setUpdateMode("Off");
PPermissionApproval.init(PermissionApproval);
PermissionApproval.setEditable(true);
PermissionApproval.enableMultiline(true);
PermissionApproval.init();
refreshdata();

function refreshdata(){
	PermissionApproval.clearAndLoad('xml-permission-approval');

}
</script>
@endsection