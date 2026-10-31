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
                <span class="active">Interview</span>
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
	LoadM('divlistRecruitment','recruitment-interview-detail?ID=' + RecruitmentListsID);
}
return true;
}
RecruitmentLists = new dhtmlXGridObject('RecruitmentLists');
RecruitmentLists.setImagePath("./dhtmlx/imgs/");
RecruitmentLists.setHeader("No.,Task ID,Name,Gender,Birth Date,Requisition,Last Education,Last Education Major,Institution,Reference,Reference Desc,Status,Remaining Time(days)");
RecruitmentLists.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#combo_filter,#rspan");
RecruitmentLists.setInitWidths("70,150,100,100,100,200,100,100,100,120,100,100,100");
RecruitmentLists.setColAlign("center,left,center,center,center,center,center,center,center,center,center,center");
RecruitmentLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
RecruitmentLists.setColSorting("int,str,str,str,str,str,str,str,str,str");
RecruitmentLists.enableKeyboardSupport(true);
PRecruitmentLists = new dataProcessor("recruitment-document-submit");
PRecruitmentLists.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PRecruitmentLists.setTransactionMode("POST", true);
PRecruitmentLists.setUpdateMode("Off");
PRecruitmentLists.init(RecruitmentLists);
RecruitmentLists.attachEvent("onRowDblClicked", RecruitmentListsKlik);
RecruitmentLists.setSkin("dhx_skyblue");
RecruitmentLists.init();
refreshdata();

function refreshdata(){
	RecruitmentLists.clearAndLoad('xml-recruitment-data');

}


</script>
@endsection