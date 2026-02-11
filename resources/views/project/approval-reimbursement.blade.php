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
        <h1>Project Management</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">APPROVAL</a>
            </li>
            <li class="breadcrumb-item active">
                <span class="active">Reimbursement</span>
            </li>
        </ol>
    </div> 
   
	<div class="row">
		
<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">Outstanding Reimbursement Lists</div>
<div id="ProjectReimbursementDetail" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="ProjectReimbursementDetailExp" NAME="ProjectReimbursementDetailExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="ProjectReimbursementDetail.toExcel('./export/generate.php');" />
		   
		<input type="button" id="ProjectReimbursementDetailAdd1" NAME="ProjectReimbursementDetailAdd1" value="Approve" class="btn btn-success mt-2 mr-2" />
		<input type="button" id="ProjectReimbursementDetailAdd2" NAME="ProjectReimbursementDetailAdd2" value="Reject" class="btn btn-danger mt-2 mr-2" />
	   
	</div>

</div>		
<div id="ProjectReimbursementDetailDiv2"></div>	
<script>


$('#ProjectReimbursementDetailAdd2').click(function(){
	dhtmlx.confirm('Reject this reimbursement?', 'Reimbursement', function(r) {
			
		if (r==true){
			ProjectReimbursementDetail.deleteSelectedRows();
			PProjectReimbursementDetail.sendData();
			setTimeout(function(){
				refreshdetail();
				alert('the reimbursement already rejected!');
			},2000);
		}
	});
		

});

$('#ProjectReimbursementDetailAdd1').click(function(){

	idnya = ProjectReimbursementDetail.getSelectedId();
	PProjectReimbursementDetail.setUpdated(idnya,true);
	PProjectReimbursementDetail.sendData();
	setTimeout(function(){
		refreshdetail();
		alert('reimbursement task ' + idnya + ' approved!');
	},2000);

});
function refreshdetail(){
	
	ProjectReimbursementDetail.clearAndLoad("xml-approval-reimbursement");

}

function ProjectReimbursementDetailDoubleClick() {
	
	ProjectReimbursementDetailID = ProjectReimbursementDetail.getSelectedId();
	tipe = ProjectReimbursementDetail.cells(ProjectReimbursementDetail.getSelectedId(),2).getValue();
	tgl = ProjectReimbursementDetail.cells(ProjectReimbursementDetail.getSelectedId(),8).getValue();
		if (tipe =='REIMBURSEMENT'){
			LoadM('ProjectReimbursementDetailDiv2','approval-reimbursement-detail?ID=' + ProjectReimbursementDetailID+"&Tgl=" + tgl);
		}else{
			LoadM('ProjectReimbursementDetailDiv2','approval-advance-detail?ID=' + ProjectReimbursementDetailID);
		}
	return true;
}


ProjectReimbursementDetail = new dhtmlXGridObject('ProjectReimbursementDetail');
ProjectReimbursementDetail.setImagePath("./dhtmlx/imgs/");
ProjectReimbursementDetail.setHeader("No.,Task No,Reimbursement Type,Description,User Request,Amount,Remain,Days Open,Date Created,Status,Created By,Department,Reference");
ProjectReimbursementDetail.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#rspan,#combo_filter,#combo_filter,#combo_filter,#combo_filter");
ProjectReimbursementDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,,,,,,");
ProjectReimbursementDetail.setInitWidths("70,160,150,150,100,100,100,100,100,100,100,80,100");
ProjectReimbursementDetail.setColAlign("center,center,left,left,center,right,right,center,center,center,center,center,center");
ProjectReimbursementDetail.setColTypes("ro,ro,ro,ed,ro,ron,ron,ro,ro,ro,ro,ro,ro");
ProjectReimbursementDetail.setColSorting("int,str,str,str,str,str,str,str,str,str");
ProjectReimbursementDetail.setEditable(true);
ProjectReimbursementDetail.attachEvent("onRowDblClicked", ProjectReimbursementDetailDoubleClick);
ProjectReimbursementDetail.setNumberFormat("0,000",5,".",",");
ProjectReimbursementDetail.setNumberFormat("0,000",6,".",",");
ProjectReimbursementDetail.setSkin("dhx_skyblue");
PProjectReimbursementDetail  = new dataProcessor("approval-reimbursement-update");
PProjectReimbursementDetail .setTransactionMode({
 mode: "POST",
  total:true,
  headers:{
	'X-CSRF-TOKEN': "<?php echo csrf_token() ?>"
  }	,

})
PProjectReimbursementDetail.init(ProjectReimbursementDetail);
ProjectReimbursementDetail.init();
refreshdetail();
</script>


@endsection