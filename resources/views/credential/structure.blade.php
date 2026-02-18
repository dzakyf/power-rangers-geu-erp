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
        <h1>Credentials</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Role</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Data Lists</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="CredentialList" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselCredentialListDet" name="ekselCredentialListDet"  class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="CredentialList.toExcel('./export/generate.php');"  />
		</div>

	</div>	
		<div id="divlistOpportunity"></div>
<script>

function CredentialListKlik() {

CredentialListID = CredentialList.getSelectedId();

if (CredentialListID !=''){
LoadM('divlistOpportunity','credential-structure-detail?ID=' + CredentialListID);
}
return true;
}
CredentialList = new dhtmlXGridObject('CredentialList');
CredentialList.setImagePath("./dhtmlx/imgs/");
CredentialList.setHeader("No,Username,Name,NIK,Department,Employee Category,Employee Position,Alocation,Phone,Email,Status");
CredentialList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
CredentialList.setInitWidths("60,100,200,100,100,120,120,100,100,100,100");
CredentialList.setColAlign("center,center,left,center,center,center,center,center,center,center,center");
CredentialList.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
CredentialList.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
CredentialList.attachEvent("onRowDblClicked", CredentialListKlik);
CredentialList.setSkin("dhx_skyblue");
CredentialList.init();
refreshdata();

function refreshdata(){
	CredentialList.clearAndLoad('xml-credential-structure');

}


</script>
@endsection