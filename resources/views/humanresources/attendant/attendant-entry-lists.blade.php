<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DATA ATTENDANTS</div>
		<div id="AttendantList" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselAttendantListDet" name="ekselAttendantListDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="AttendantList.toExcel('./export/generate.php');"  />
			<input type="button" id="deletsaa" name="deletsaaa" value="UPDATE DATA" onclick="SubmitGrid()"class="btn btn-success mt-2 mr-2"  />
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
AttendantList.attachEvent("onEditCell", function(stage,rId,cInd,nValue,oValue){ 
		if(stage==1 && AttendantList.editor.obj) { 
			AttendantList.editor.obj.onkeypress = function(e){
				e = e || window.event; 
				if((e.keyCode >= 47) || (e.keyCode == 0)){ 
					var text = this.value; 
					if(text.length > 6){ 
						return false;
					} else{
						
					}
				} 
			} 
		} 
		return true; 
});

PAttendantList = new dataProcessor("attendant-entry-lists-update?ID=<?php echo $_GET['ID']?>");
PAttendantList.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PAttendantList.setTransactionMode("POST", true);
PAttendantList.setUpdateMode("Off");
PAttendantList.init(AttendantList);
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
