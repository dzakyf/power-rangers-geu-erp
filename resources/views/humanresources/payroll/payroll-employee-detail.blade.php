			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LIST OF BENEFIT AND COSTS FOR GROUP <?php echo $_GET['ID']?></div>
			<div id="GroupPayroll_Lists" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="GroupPayroll_Lists.toExcel('./export/generate.php');"  />
				<input type="button" id="RefreshBM" NAME="RefreshBM" value="Refresh Data"class="btn btn-info mt-2 mr-2" />
			<input type="button" id="HapusdataPayrollListsGroup" NAME="HapusdataPayrollListsGroup" value="Delete Data" class="btn btn-danger mt-2 mr-2"  />                
			<input type="button" id="UpdateGroupPayroll_Lists" NAME="UpdateGroupPayroll_Lists" value="UPDATE" class="btn btn-info mt-2 mr-2"/>
			<input type="button" id="AddDataPayrollLists" NAME="AddDataPayrollLists" value="Add Data" class="btn btn-info mt-2 mr-2"/>
		</div>

		<div id="GroupPayroll_ListsDiv"></div>


	
	</div>
<script>

GroupPayroll_Lists = new dhtmlXGridObject('GroupPayroll_Lists');
GroupPayroll_Lists.setImagePath("./dhtmlx/imgs/");
GroupPayroll_Lists.setHeader("No,ID,Name,Fix Allowance,PPH,Category,Amounts,Note,Created By,Created Date");
GroupPayroll_Lists.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,,");
GroupPayroll_Lists.setInitWidths("80,100,250,100,100,100,100,200,100,100");
GroupPayroll_Lists.setColAlign("center,left,left,center,left,center,right,left,center,center,center");
GroupPayroll_Lists.setColTypes("ro,ro,ro,coro,coro,coro,edn,ro,ro,ro");
GroupPayroll_Lists.setColSorting("int,str,str,str,str,str,str,str,str,str,str");
GroupPayroll_Lists.setEditable(true);
GroupPayroll_Lists.setNumberFormat("0,000",6,".",",");
GroupPayroll_Lists.setSkin("dhx_skyblue");
PGroupPayroll_Lists = new dataProcessor("payroll-employee-detail-update");
	PGroupPayroll_Lists.setTransactionMode({
     mode: "POST",
	  total:true,
      headers:{
        'X-CSRF-TOKEN': "{{ csrf_token() }}"
      }	,
	    payload: {"ID":"<?php echo $_GET['ID'] ?>"}
	  
    })
PGroupPayroll_Lists.setUpdateMode("Off");
PGroupPayroll_Lists.init(GroupPayroll_Lists);
GroupPayroll_Lists.init();
GroupPayroll_Lists.enableMultiline(true);

Reload_GroupPayroll_Lists();
GroupPayroll_Lists.getCombo(3).put('1','YES');
GroupPayroll_Lists.getCombo(3).put('0','NO');
GroupPayroll_Lists.getCombo(4).put('1','YES');
GroupPayroll_Lists.getCombo(4).put('0','NO');
GroupPayroll_Lists.getCombo(5).put('BASIC','BASIC');
GroupPayroll_Lists.getCombo(5).put('ALLOWANCE','ALLOWANCE');
GroupPayroll_Lists.getCombo(5).put('DEDUCTION','DEDUCTION');

function Reload_GroupPayroll_Lists(){
	GroupPayroll_Lists.clearAndLoad("xml-payroll-employee-detail?ID=<?php echo $_GET['ID']?>");	

}

$("#UpdateGroupPayroll_Lists").click(function(){
		PGroupPayroll_Lists.sendData();
		alert("Data was Saved!");
		setTimeout(function(){
			
			Reload_GroupPayroll_Lists();
		},2000);
});
$("#HapusdataPayrollListsGroup").click(function(){
		GroupPayroll_Lists.deleteSelectedRows();
		PGroupPayroll_Lists.sendData();
		alert("Data were deleted!");
		Reload_GroupPayroll_Lists();

		
});
$("#AddDataPayrollLists").click(function(){
	LoadM('GroupPayroll_ListsDiv','payroll-group-lists-add?ID=<?php echo $_GET['ID']?>');

});


$("#RefreshBM").click(function(){
	Reload_GroupPayroll_Lists();		
});



</script>
