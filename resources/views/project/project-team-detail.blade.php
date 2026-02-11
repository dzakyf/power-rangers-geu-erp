<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TEAM AND PEOPLE LISTS</div>
<div id="ProjectTeamDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
<div>
	<input type="button" id="ekselProjectTeamDetailDet" name="ekselProjectTeamDetailDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="ProjectTeamDetail.toExcel('./export/generate.php');"  />
	<input type="button" id="ProjectTeamDetaildel" name="ProjectTeamDetaildel" value="Delete Team" onclick="delsSales()"  class="btn btn-danger mt-2 mr-2" />
	<input type="button" id="ProjectTeamDetailadd" name="ProjectTeamDetailadd" value="Add New Team" onclick="addNew()" class="btn btn-success mt-2 mr-2"  />
</div>
<div id="DivProjectTeamDetail"></div>
<script>
function addNew() {

LoadM('DivProjectTeamDetail','project-team-detail-add?Task_ID=<?php echo $_GET['Task_ID']?>');
}

ProjectTeamDetail = new dhtmlXGridObject('ProjectTeamDetail');
ProjectTeamDetail.setImagePath("./dhtmlx/imgs/");
ProjectTeamDetail.setHeader("No,NIK,Name,Teritory,Location,Position,Registered Date");
ProjectTeamDetail.setInitWidths("60,150,100,100,200,120,120,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
ProjectTeamDetail.setColAlign("center,left,left,left,left,left,left,center,center,center,center,center,center,center,center,center");
ProjectTeamDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
ProjectTeamDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
ProjectTeamDetail.enableSmartRendering(true, 150);
PProjectTeamDetail = new dataProcessor("project-team-detail-update");
PProjectTeamDetail.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PProjectTeamDetail.setTransactionMode("POST", true);
PProjectTeamDetail.setUpdateMode("Off");
PProjectTeamDetail.init(ProjectTeamDetail);
ProjectTeamDetail.setSkin("dhx_skyblue");
ProjectTeamDetail.init();
refreshdatateamdetail();

function refreshdatateamdetail(){
	ProjectTeamDetail.clearAndLoad('xml-project-team-detail?Task_ID=<?php echo $_GET['Task_ID']?>');

}

function delsSales(){
	dhtmlx.confirm('Cancel this Sales?', 'Sales', function(r) {
			
			if (r==true){
				ProjectTeamDetail.deleteSelectedRows();
				PProjectTeamDetail.sendData();
					alert('Data Successfully Deleted!');
					setTimeout(function(){
						refreshdatateamdetail();
					},1000);
			}
	})
}


function UpdateGrid_Sales() {
	var out = 0;
    for (var i = 0; i < ProjectTeamDetail.getRowsNum(); i++) {
		rowIdMov=ProjectTeamDetail.getRowId(i);
        ProjectTeamDetailProc.setUpdated(rowIdMov,true);
    }
	return out;
}

function SubmitGrid_Sales(){
	ProjectTeamDetailProc.sendData();
	alert('Data Successfully Updated!');
	ProjectTeamDetail.clearAll();
	refreshdatateamdetail();
}


</script>
