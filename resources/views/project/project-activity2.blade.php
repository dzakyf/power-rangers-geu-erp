<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ACTIVITY LISTS</div>
<div id="ActivityLists" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
<div>
	<input type="button" id="ekselActivityListsDet" name="ekselActivityListsDet" class="btn btn-info mt-2 mr-2" value="Export Excel" onclick="ActivityLists.toExcel('./export/generate.php');"  />
	<input type="button" id="addactivity" name="addactivity" value="Add Activity" class="btn btn-success mt-2 mr-2" onclick="addactivity()"  />
	<!--<input type="button" id="delets" name="delets" value="Delete Activity" class="btn btn-danger mt-2 mr-2" onclick="delsActivity()"  />-->
	<input type="button" id="edtss" name="edtss" value="Edit Activity" class="btn btn-info mt-2 mr-2" onclick="EditActivity()"  />
</div>

</div>	
	<div id = 'divlistActivity'></div>

<script>

			
ActivityLists = new dhtmlXGridObject('ActivityLists');
ActivityLists.setImagePath("./dhtmlx/imgs/");
ActivityLists.setHeader("No,Task ID,Reference,Sales Person,Date,Activity Type,Priority,Activity Desc,Company Name,Activity Subject,Note");
ActivityLists.setInitWidths("60,100,100,100,100,100,100,200,200,200,200");
ActivityLists.setColAlign("center,center,center,left,center,center,center,center,center,center,left");
ActivityLists.setColSorting("int,str,str,str,str,str,str,str,str");
ActivityLists.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
PActivityLists = new dataProcessor("project-activity2-update");
PActivityLists.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PActivityLists.setTransactionMode("POST", true);
PActivityLists.setUpdateMode("Off");
PActivityLists.init(ActivityLists);
ActivityLists.setSkin("dhx_skyblue");
ActivityLists.init();

ActivityListsRefresh();
function ActivityListsRefresh(){
	ActivityLists.clearAndLoad('xml-project-activity2?Task_ID=<?php echo $_GET['Task_No']?>');
	
}


function addactivity() {
	LoadM('divlistActivity','project-activity-add?Task_ID=<?php echo $_GET['Task_ID']?>');
}
function EditActivity() {
	taskid = ActivityLists.getSelectedId();
	LoadM('divlistActivity','project-activity-detail?ID=' + taskid);
}

function delsActivity(){
	dhtmlx.confirm('Delete this Activity?', 'Activity', function(r) {
			
			if (r==true){
				ActivityLists.deleteSelectedRows();
				PActivityLists.sendData();
					alert('Data Activity Deleted!');
					setTimeout(function(){
						ActivityListsRefresh();
					},1000);
			}
	});
}

function SubmitGrid_Activity(){
	ActivityListsProc.sendData();
	alert('Data Successfully Updated!');
	ActivityLists.clearAll();
	ActivityListsRefresh();	
}


</script>
