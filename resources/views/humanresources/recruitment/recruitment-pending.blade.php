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
                <a href="#">Recruitment</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Pending Candidates</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="RecruitmentLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="RecruitmentLists.toExcel('./export/generate.php');"  />
			
		</div>

	</div>	
		<div id="divlistRecruitment"></div>
<script>

function RecruitmentListsKlik() {

RecruitmentListsID = RecruitmentLists.getSelectedId();

if (RecruitmentListsID !=''){
	LoadM('divlistRecruitment','recruitment-pending-detail?ID=' + RecruitmentListsID);
}
return true;
}
RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
RecruitmentLists.setImagePath("./dhtmlx/imgs/");
RecruitmentLists.setHeader("No.,Task ID,Name,Gender,Birth Date,Position,Last Education,Reference,Reference Desc,Status,Aging Remain(days)");
RecruitmentLists.setInitWidths("70,150,100,100,100,100,100,120,100,100,100");
RecruitmentLists.setColAlign("center,left,center,center,center,center,center,center,center,center");
RecruitmentLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
RecruitmentLists.setColSorting("int,str,str,str,str,str,str,str");
RecruitmentLists.enableKeyboardSupport(true);
RecruitmentLists.attachEvent("onRowDblClicked", RecruitmentListsKlik);
RecruitmentLists.setSkin("dhx_skyblue");
RecruitmentLists.init();
refreshdata();

function refreshdata(){
	RecruitmentLists.clearAndLoad('xml-recruitment-pending');

}


</script>
@endsection