<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">ADD EMPLOYEES</div>
		<div id="Employee_add" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselEmployee_addDet" name="ekselEmployee_addDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="Employee_add.toExcel('./export/generate.php');"  />
			<input type="button" id="deletsaa" name="deletsaaa" value="Set Employee to Group <?php echo $_GET['ID']?>" onclick="SubmitGrid()"class="btn btn-success mt-2 mr-2"  />
		</div>

	</div>	
<script>
Employee_add = new dhtmlXGridObject('Employee_add');
Employee_add.setImagePath("./dhtmlx/imgs/");
Employee_add.setHeader("Selected,NIK,Name,Gender,Last Education,Position,Work Area,Employee Status");
Employee_add.attachHeader("#master_checkbox,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
Employee_add.setInitWidths("60,150,100,100,200,120,120,100");
Employee_add.setColAlign("center,left,left,left,left,left,left,center");
Employee_add.setColSorting("int,str,str,str,str,str,str,str");
Employee_add.setColTypes("ch,ro,ro,ro,ro,ro,ro,ro");
Employee_add.enableSmartRendering(true, 150);
PEmployee_add = new dataProcessor("payroll-group-detail-add-update?ID=<?php echo $_GET['ID']?>");
PEmployee_add.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	  
    })
	
PEmployee_add.setTransactionMode("POST", true);
PEmployee_add.setUpdateMode("Off");
PEmployee_add.init(Employee_add);
Employee_add.setSkin("dhx_skyblue");
Employee_add.init();
refreshdatas();

function refreshdatas(){
	Employee_add.clearAndLoad('xml-payroll-group-detail-add?ID=<?php echo $_GET['ID']?>');

}



function SubmitGrid(){
	PEmployee_add.sendData();
	alert('Data Employee Updated!');
	setTimeout(function(){
		$("#Div_Payroll_Group").html('');
		refreshdatagroupdetail();
	},2000);
}


</script>
