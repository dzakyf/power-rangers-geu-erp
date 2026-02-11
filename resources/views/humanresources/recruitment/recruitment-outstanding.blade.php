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
                <a href="#">Recruitment</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Outstanding Report</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="RecruitmentLists" style="width:auto;height:520px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselRecruitmentListsDet" name="ekselRecruitmentListsDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="RecruitmentLists.toExcel('./export/generate.php');"  />
			
		</div>

	</div>	
		<div id="divlistRecruitment"></div>
<script>

function RecruitmentListsKlik() {

RecruitmentListsID = RecruitmentLists.getSelectedId();

if (RecruitmentListsID !=''){
	LoadM('divlistRecruitment','recruitment-lists-detail?ID=' + RecruitmentListsID);
}
return true;
}
RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
RecruitmentLists.setImagePath("./dhtmlx/imgs/");
RecruitmentLists.setHeader("No.,Recruitment Type,Name,Gender,PO No,Requisition,Last Education,Last Education Major,Recruiter,Document Completion,For Interview,Decline Invitation,Interviewed,Pending,Rejected,Business Approval,Candidate Analysis,Decline Offered,Hired");

RecruitmentLists.setInitWidths("70,100,100,100,100,200,100,100,100,120,60,60,60,60,60,60,0,60,60");
RecruitmentLists.setColAlign("center,left,center,center,center,center,center,center,center,center,center,center");
RecruitmentLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str,ra_str");
RecruitmentLists.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
RecruitmentLists.enableKeyboardSupport(true);
RecruitmentLists.attachEvent("onRowDblClicked", RecruitmentListsKlik);
RecruitmentLists.setSkin("dhx_skyblue");
RecruitmentLists.init();
refreshdata();

function refreshdata(){
	RecruitmentLists.clearAndLoad('xml-recruitment-outstanding');
	RecruitmentLists.sortRows(0,"str","des");

}



</script>
@endsection