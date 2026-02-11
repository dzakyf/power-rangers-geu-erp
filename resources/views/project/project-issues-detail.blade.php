<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ISSUE LISTS</div>
<div id="IssueListsDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
<div>
	<input type="button" id="ekselIssueListsDetailDet" name="ekselIssueListsDetailDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="IssueListsDetail.toExcel('./export/generate.php');"  />
	<input type="button" id="IssueListsDetaildel" name="IssueListsDetaildel" value="Delete Issue" onclick="DelIssues()"  class="btn btn-danger mt-2 mr-2" />
	<input type="button" id="IssueListsDetailadd" name="IssueListsDetailadd" value="Add New Issue" onclick="addNew()" class="btn btn-success mt-2 mr-2"  />

	<input type="button" id="showlogg" name="showlogg" value="Show Logs" onclick="showlogs()" class="btn btn-info mt-2 mr-2"  />

</div>
<div id="DivIssueListsDetail"></div>
<script>

function IssueListsDetailKlik() {

	IssueListsDetailID = IssueListsDetail.getSelectedId();

	if (IssueListsDetailID !=''){
		LoadM('DivIssueListsDetail','project-issues-detail-edit?ID=' + IssueListsDetailID);
	}
	return true;
}
function addNew() {

LoadM('DivIssueListsDetail','project-issues-detail-add?Task_ID=<?php echo $_GET['Task_ID']?>');
}
function showlogs() {

	IssueListsDetailID = IssueListsDetail.getSelectedId();

	if (IssueListsDetailID !=''){
		LoadM('DivIssueListsDetail','project-issues-detail-log?ID=' + IssueListsDetailID);
	}
}
IssueListsDetail = new dhtmlXGridObject('IssueListsDetail');
IssueListsDetail.setImagePath("./dhtmlx/imgs/");
IssueListsDetail.setHeader("No,Date,Resolution Date,Priority,Issue Name,Category,Description,Status,Risk Information,Loss Revenue,Solving Method,Assignment");
IssueListsDetail.setInitWidths("60,100,100,100,100,200,100,200,100,100,200,100,100,100,100,100,100,100,100,100,100,100,100");
IssueListsDetail.setColAlign("center,center,center,center,left,center,left,center,left,right,left,center,center,center,center,center");
IssueListsDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
IssueListsDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
IssueListsDetail.enableSmartRendering(true, 150);
PIssueListsDetail = new dataProcessor("project-issues-detail-update");
PIssueListsDetail.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PIssueListsDetail.setTransactionMode("POST", true);
PIssueListsDetail.setUpdateMode("Off");
PIssueListsDetail.init(IssueListsDetail);
IssueListsDetail.attachEvent("onRowDblClicked", IssueListsDetailKlik);
IssueListsDetail.setSkin("dhx_skyblue");
IssueListsDetail.setNumberFormat("0,000",9,".",",");
IssueListsDetail.init();
refreshdataissuedetail();

function refreshdataissuedetail(){
	IssueListsDetail.clearAndLoad('xml-project-issues-detail?Task_ID=<?php echo $_GET['Task_ID']?>');

}

function DelIssues(){
	dhtmlx.confirm('Cancel this Issues?', 'Issues', function(r) {
			
			if (r==true){
				IssueListsDetail.deleteSelectedRows();
				PIssueListsDetail.sendData();
					alert('Data Successfully Deleted!');
					setTimeout(function(){
						$("#DivIssueListsDetail").html('');
						refreshdataissuedetail();
					},1000);
			}
	})
}
</script>
