<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
	
	$LSP = Auth::user()->Location;
	$Saya =  Auth::user()->username;
	
	
	
	?>
 <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REIMBURSEMENT LISTS</div>
<div id="ProjectReimbursementDetail" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
	<div id="divtom">
		<input type="button" id="ProjectReimbursementDetailExp" NAME="ProjectReimbursementDetailExp" value="Export" class="btn btn-warning mt-2 mr-2" onclick="ProjectReimbursementDetail.toExcel('./export/generate.php');" />
		   
		<input type="button" id="ProjectReimbursementDetailAdd1" NAME="ProjectReimbursementDetailAdd1" value="New Reimbursement" class="btn btn-success mt-2 mr-2" />
		<input type="button" id="ProjectReimbursementDetailAdd2" NAME="ProjectReimbursementDetailAdd2" value="New PUM" class="btn btn-success mt-2 mr-2" />
	   
	</div>
	
<div id="ProjectReimbursementDetailDiv"></div>	
<script>


$('#ProjectReimbursementDetailAdd2').click(function(){

	idnya = ProjectReimbursementDetail.uid();
	ProjectReimbursementDetail.addRow(idnya, [ProjectReimbursementDetail.getRowsNum()+1,'','<?php echo date("Y-m-d");?>',akuncash,'',''],0);
			

});

$('#ProjectReimbursementDetailAdd1').click(function(){

	LoadM('ProjectReimbursementDetailDiv','project-reimbursement-out-add');


});
function refreshdata2(){
	
	ProjectReimbursementDetail.clearAndLoad("xml-project-reimbursement-detail");

}

function ProjectReimbursementDetailDoubleClick() {
	
	ProjectReimbursementDetailID = ProjectReimbursementDetail.getSelectedId();
	kurs = ProjectReimbursementDetail.cells(ProjectReimbursementDetail.getSelectedId(),5).getValue();
	txacc = ProjectReimbursementDetail.cells(ProjectReimbursementDetail.getSelectedId(),11).getValue();
	tglx = ProjectReimbursementDetail.cells(ProjectReimbursementDetail.getSelectedId(),8).getValue();
		if (ProjectReimbursementDetailID !=''){
			LoadM('ProjectReimbursementDetailDiv','finance-bank-advance-detail?ID=' + ProjectReimbursementDetailID + "&PUM=" + txacc + "&Tgl=" + tglx+ "&Currency=" + kurs);
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
ProjectReimbursementDetail.setColTypes("ro,ro,ro,ro,ro,ron,ron,ro,ro,ro,ro,ro,ro");
ProjectReimbursementDetail.setColSorting("int,str,str,str,str,str,str,str,str,str");
ProjectReimbursementDetail.setEditable(true);
ProjectReimbursementDetail.attachEvent("onRowDblClicked", ProjectReimbursementDetailDoubleClick);
ProjectReimbursementDetail.setNumberFormat("0,000",5,".",",");
ProjectReimbursementDetail.setNumberFormat("0,000",6,".",",");
ProjectReimbursementDetail.setSkin("dhx_skyblue");
PProjectReimbursementDetail = new dataProcessor("project-reimbursement-detail-update");
	PProjectReimbursementDetail.setTransactionMode({
	 mode: "POST",
	  total:true,
	  headers:{
		'X-CSRF-TOKEN': "{{ csrf_token() }}"
	  }	

	})
PProjectReimbursementDetail.init(ProjectReimbursementDetail);
ProjectReimbursementDetail.init();
refreshdata2();
</script>