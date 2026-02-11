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
        <h1>Finance</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Reimbursement</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Approval</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		<div id="ProjectReimbursementDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div id="divlistProjectReimbursement"></div>

	</div>	
<script>


function ProjectReimbursementDetailDoubleClick() {
	
	ProjectReimbursementDetailID = ProjectReimbursementDetail.getSelectedId();
	tipe = ProjectReimbursementDetail.cells(ProjectReimbursementDetail.getSelectedId(),2).getValue();
	tgl = ProjectReimbursementDetail.cells(ProjectReimbursementDetail.getSelectedId(),8).getValue();
		
	LoadM('divlistProjectReimbursement','finance-reimbursement-detail-reimbursement?ID=' + ProjectReimbursementDetailID);
		
	return true;
}

ProjectReimbursementDetail = new dhtmlXGridObject('ProjectReimbursementDetail');
ProjectReimbursementDetail.setImagePath("./dhtmlx/imgs/");
ProjectReimbursementDetail.setHeader("No.,Task No,Reimbursement Type,Description,User Request,Amount,Paid,Remain,Days Open,Date Created,Payment Type,Status,Created By,Department,Reference");
ProjectReimbursementDetail.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter");
ProjectReimbursementDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,,,,,,,");
ProjectReimbursementDetail.setInitWidths("70,160,150,150,100,100,100,100,100,100,100,100,100,80,100");
ProjectReimbursementDetail.setColAlign("center,center,left,left,center,right,right,right,center,center,center,center,center,center,center");
ProjectReimbursementDetail.setColTypes("ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro,ro,ro,ro");
ProjectReimbursementDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
ProjectReimbursementDetail.setEditable(true);
ProjectReimbursementDetail.attachEvent("onRowDblClicked", ProjectReimbursementDetailDoubleClick);
ProjectReimbursementDetail.setNumberFormat("0,000",5,".",",");
ProjectReimbursementDetail.setNumberFormat("0,000",6,".",",");
ProjectReimbursementDetail.setNumberFormat("0,000",7,".",",");
ProjectReimbursementDetail.setSkin("dhx_skyblue");
PProjectReimbursementDetail  = new dataProcessor("finance-reimbursement-detail-update");
PProjectReimbursementDetail .setTransactionMode({
 mode: "POST",
  total:true,
  headers:{
	'X-CSRF-TOKEN': "<?php echo csrf_token() ?>"
  }	

})
PProjectReimbursementDetail.init(ProjectReimbursementDetail);
ProjectReimbursementDetail.setSkin("dhx_skyblue");
ProjectReimbursementDetail.init();
refreshdata();

function refreshdata(){
	ProjectReimbursementDetail.clearAndLoad("xml-finance-reimbursement-approval");

}


</script>
@endsection