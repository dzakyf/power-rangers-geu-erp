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
                <span class="active">OS CONTRACT MANAGEMENT</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="OSContract" style="width:auto;height:350px; background-color:white;overflow:hidden"></div>
		<div>
		<input type="button" id="DeleteBM" NAME="DeleteBM" value="Close Contract" onclick="closecontract()" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="refreshdata" NAME="refreshdata" onclick="refreshdata()" value="Refresh Data" class="btn btn-warning mt-2 mr-2"/>
			<input type="button" id="AddBM" NAME="AddBM" value="New Contract" onclick="renewcontract()" class="btn btn-info mt-2 mr-2"/>
		</div>	
	</div>	
			<div id="detailcontractdiv"></div>
	
<script>
function renewcontract() {

OSContractID = OSContract.cells(OSContract.getSelectedId(),2).getValue();

	if (OSContractID !=''){
		LoadM('detailcontractdiv','os-contract-renew?ID=' + OSContractID);
	}
return true;
}

function closecontract() {

OSContractID = OSContract.cells(OSContract.getSelectedId(),2).getValue();

	if (OSContractID !=''){
		LoadM('detailcontractdiv','close-contract?ID=' + OSContractID);
	}
return true;
}

function OSContractKlik() {

OSContractID = OSContract.cells(OSContract.getSelectedId(),2).getValue();

	if (OSContractID !=''){
		LoadM('detailcontractdiv','os-contract-detail?ID=' + OSContractID);
	}
return true;
}
OSContract = new dhtmlXGridObject('OSContract');
OSContract.setImagePath("./dhtmlx/imgs/");
OSContract.setHeader("No.,Name,NIK,Gender,Marital Status,Age,Last Position,Contract No,Start Date,End Date,Day to Expire");
OSContract.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter");
OSContract.setInitWidths("70,150,100,100,100,100,100,120,100,100,100");
OSContract.setColAlign("center,left,center,center,center,center,center,center,center,center,right");
OSContract.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
OSContract.setColSorting("int,str,str,str,str,str,str,str,str,str,int");
OSContract.attachEvent("onRowDblClicked", OSContractKlik);
OSContract.setSkin("dhx_skyblue");
OSContract.init();
refreshdata();

function refreshdata(){
	OSContract.clearAndLoad('xml-os-contract');

}
</script>
@endsection