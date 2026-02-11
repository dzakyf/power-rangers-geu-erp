<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	
	
?>

@extends('layout.mainlayout')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Assets And Inventory</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Inventory</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Request Management</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="RequestManagement" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselRequestManagementDet" name="ekselRequestManagementDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="RequestManagement.toExcel('./export/generate.php');"  />

		</div>
	</div>	
	<div id='RequestManagementDiv' ></div>
<script>
RequestManagement = new dhtmlXGridObject('RequestManagement');
RequestManagement.setImagePath("./dhtmlx/imgs/");
RequestManagement.setHeader("No.,Request No,Date Request,Department,Request By,FR No,Category,Pickup Date,Note,Status");
RequestManagement.setInitWidths("70,120,150,200,100,100,150,100,100,100");
RequestManagement.setColAlign("center,center,center,center,center,center,center,center");
RequestManagement.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
RequestManagement.setColSorting("int,str,str,str,str,str,str,str");
RequestManagement.setEditable(true);
RequestManagement.attachEvent("onRowDblClicked", RequestManagementDoubleClick);
RequestManagement.setSkin("dhx_skyblue");
RequestManagement.init();
refreshdata();

function refreshdata(){
	RequestManagement.clearAndLoad('xml-inventory-request-management');

}

function RequestManagementDoubleClick() {

	RequestManagementMIDStatus = RequestManagement.cells(RequestManagement.getSelectedId(),9).getValue();
	
	RequestManagementMID = RequestManagement.cells(RequestManagement.getSelectedId(),1).getValue();
	if (RequestManagementMIDStatus == 'PROPOSED'){
		LoadM('RequestManagementDiv','inventory-request-management-detail2?ID=' + encodeURI(RequestManagementMID));
	}else{
		LoadM('RequestManagementDiv','inventory-request-management-detail?ID=' + encodeURI(RequestManagementMID));
	}
	
}

</script>
@endsection
