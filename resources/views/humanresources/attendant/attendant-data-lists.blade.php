<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DATA ATTENDANTS</div>
		<div id="AttendantList" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselAttendantListDet" name="ekselAttendantListDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="AttendantList.toExcel('./export/generate.php');"  />
		</div>

	</div>	
<script>
AttendantList = new dhtmlXGridObject('AttendantList');
AttendantList.setImagePath("./dhtmlx/imgs/");
AttendantList.setHeader("No,Date,Day Name,Category,Start Time,End Time,Shift 1,Shift 2,Shift 3,Exist");
AttendantList.setInitWidths("60,100,100,300,100,100,100,100,100,100");
AttendantList.setColAlign("center,center,center,center,center,center,center,center,center,center");
AttendantList.setColSorting("int,str,str,str,str,str,str,str,str");
AttendantList.setColTypes("ro,ro,ro,ro,time,time,ra_str,ra_str,ra_str,ro");
AttendantList.enableSmartRendering(true, 150);
AttendantList.setDateFormat("%H:%m"); 
AttendantList.setSkin("dhx_skyblue");
AttendantList.init();
refreshdatas();

function refreshdatas(){
	AttendantList.clearAndLoad('xml-attendant-entry-lists?ID=<?php echo $_GET['ID']?>',setupdatenya);

}

function setupdatenya(){
	var out = 0;
	for (var i = 0; i < AttendantList.getRowsNum(); i++) {
		rowIdMov=AttendantList.getRowId(i);
		PAttendantList.setUpdated(rowIdMov,true);
	}
	
	return out;

}

function SubmitGrid(){
	PAttendantList.sendData();
	alert('Data Attendant Updated!');
	setTimeout(function(){
		$("#AttendantEntryDiv").html('');
		Reload_AttendantEntry();
	},2000);
}


</script>
