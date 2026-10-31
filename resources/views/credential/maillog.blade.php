<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	
	?>

@extends('layout.mainlayout-credential')

@section('content-wrapper')
@csrf
    <!-- Main Content Header -->
    <div class="main-content-header">
        <h1>Manage</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Maillog</a>
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
	
<script>


CredentialList = new dhtmlXGridObject('CredentialList');
CredentialList.setImagePath("./dhtmlx/imgs/");
CredentialList.setHeader("No,Email,Subject,Content,Status,Send Date,Insert Date,linkkey");
CredentialList.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
CredentialList.setInitWidths("60,120,100,200,100,100,120,120");
CredentialList.setColAlign("center,left,left,left,center,left,left,left");
CredentialList.setColSorting("int,str,str,str,str,str,str,str");
CredentialList.setColTypes("ro,ed,ed,ed,ed,ro,ro,ro");
PCTRX = new dataProcessor("credential-maillog-update");
PCTRX.setTransactionMode({
    mode: "POST",
    total: true,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
    },

})
PCTRX.init(CredentialList);
CredentialList.setSkin("dhx_skyblue");
CredentialList.init();
refreshdata();

function refreshdata(){
	CredentialList.clearAndLoad('xml-credential-maillog');

}


</script>
@endsection