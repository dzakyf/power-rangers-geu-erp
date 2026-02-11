<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	
	?>

<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">COST LISTS</div>
<div id="ProjectReimbursementDetail" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="ProjectReimbursementDetailExp" NAME="ProjectReimbursementDetailExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="ProjectReimbursementDetail.toExcel('./export/generate.php');" />
		   
		<input type="button" id="ProjectReimbursementDetailAdd1" NAME="ProjectReimbursementDetailAdd1" value="New Reimbursement" class="btn btn-success mt-2 mr-2" />
		<input type="button" id="ProjectReimbursementDetailAdd2" NAME="ProjectReimbursementDetailAdd2" value="New PUM" class="btn btn-success mt-2 mr-2" />
	   
	</div>
	
<div id="ProjectReimbursementDetailDiv2"></div>	
<script>


$('#ProjectReimbursementDetailAdd2').click(function(){

	idnya = ProjectReimbursementDetail.uid();
	ProjectReimbursementDetail.addRow(idnya,[ProjectReimbursementDetail.getRowsNum()+1,'','PUM','','',''],0);
	PProjectReimbursementDetail.sendData();
	setTimeout(function(){
		refreshdetailreimbursement();
	},2000);
		

});

$('#ProjectReimbursementDetailAdd1').click(function(){

	idnya = ProjectReimbursementDetail.uid();
	ProjectReimbursementDetail.addRow(idnya,[ProjectReimbursementDetail.getRowsNum()+1,'','REIMBURSEMENT','','',''],0);
	PProjectReimbursementDetail.sendData();
	setTimeout(function(){
		refreshdetailreimbursement();

	},2000);

});
function refreshdetailreimbursement(){
	
	ProjectReimbursementDetail.clearAndLoad("xml-project-reimbursement-detail?ID=<?php echo $_GET['ID']?>");

}

function ProjectReimbursementDetailDoubleClick() {
	
	ProjectReimbursementDetailID = ProjectReimbursementDetail.getSelectedId();
	tipe = ProjectReimbursementDetail.cells(ProjectReimbursementDetail.getSelectedId(),2).getValue();
	tgl = ProjectReimbursementDetail.cells(ProjectReimbursementDetail.getSelectedId(),8).getValue();
		if (tipe =='REIMBURSEMENT'){
			LoadM('ProjectReimbursementDetailDiv2','project-reimbursement-detail-reimbursement?ID=' + ProjectReimbursementDetailID+"&Tgl=" + tgl);
		}else{
			LoadM('ProjectReimbursementDetailDiv2','project-reimbursement-detail-advance?ID=' + ProjectReimbursementDetailID+"&Tgl=" + tgl);
		}
	return true;
}


ProjectReimbursementDetail = new dhtmlXGridObject('ProjectReimbursementDetail');
ProjectReimbursementDetail.setImagePath("./dhtmlx/imgs/");
ProjectReimbursementDetail.setHeader("No.,Task No,Reimbursement Type,Description,User Request,Amount,Remain,Days Open,Date Created,Status,Created By,Department,Currency");
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
PProjectReimbursementDetail  = new dataProcessor("project-reimbursement-detail-update");
PProjectReimbursementDetail .setTransactionMode({
 mode: "POST",
  total:true,
  headers:{
	'X-CSRF-TOKEN': "<?php echo csrf_token() ?>"
  }	,
  payload: {"Reference":"<?php echo $_GET['ID'] ?>"}

})
PProjectReimbursementDetail.init(ProjectReimbursementDetail);
ProjectReimbursementDetail.init();
refreshdetailreimbursement();
</script>
