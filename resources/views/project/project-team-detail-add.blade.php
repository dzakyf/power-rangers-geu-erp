<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ADD NEW TEAM</div>
		<div id="ProjectTeamAdd" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselProjectTeamAddDet" name="ekselProjectTeamAddDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="ProjectTeamAdd.toExcel('./export/generate.php');"  />
			<input type="button" id="deletsaa" name="deletsaaa" value="Update" onclick="SubmitGrid()"class="btn btn-success mt-2 mr-2"  />
		</div>

	</div>	
<script>
ProjectTeamAdd = new dhtmlXGridObject('ProjectTeamAdd');
ProjectTeamAdd.setImagePath("./dhtmlx/imgs/");
ProjectTeamAdd.setHeader("Selected,NIK,Name,Gender,Last Education,Position,Work Area,Employee Status");
ProjectTeamAdd.attachHeader("#master_checkbox,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
ProjectTeamAdd.setInitWidths("60,150,100,100,200,120,120,100");
ProjectTeamAdd.setColAlign("center,left,left,left,left,left,left,center");
ProjectTeamAdd.setColSorting("int,str,str,str,str,str,str,str");
ProjectTeamAdd.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro");
ProjectTeamAdd.enableSmartRendering(true, 150);
PProjectTeamAdd = new dataProcessor("project-team-detail-add-update");
PProjectTeamAdd.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  payload: {"Task_ID":"<?php echo $_GET['Task_ID']?>"}

	  
    })
	
PProjectTeamAdd.setTransactionMode("POST", true);
PProjectTeamAdd.setUpdateMode("Off");
PProjectTeamAdd.init(ProjectTeamAdd);
ProjectTeamAdd.setSkin("dhx_skyblue");
ProjectTeamAdd.init();
refreshdatas();

function refreshdatas(){
	ProjectTeamAdd.clearAndLoad('xml-project-team-detail-add');

}



function SubmitGrid(){
	PProjectTeamAdd.sendData();
	alert('Data Team Updated!');
	ProjectTeamAdd.clearAll();
	
	$("#DivProjectTeamDetail").html('');
	refreshdatateamdetail();
}


</script>
