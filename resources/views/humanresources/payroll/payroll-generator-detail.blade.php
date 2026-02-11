			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LIST OF BENEFIT AND COSTS FOR GROUP <?php echo $_GET['ID']?></div>
			<div id="GroupPayroll_Lists" style="width:auto;height:200px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="GroupPayroll_Lists.toExcel('./export/generate.php');"  />
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
GroupPayroll_Lists.setEditable(false);
GroupPayroll_Lists.setNumberFormat("0,000",6,".",",");
GroupPayroll_Lists.setSkin("dhx_skyblue");
GroupPayroll_Lists.init();
GroupPayroll_Lists.enableMultiline(true);

Reload_GroupPayroll_Lists();

function Reload_GroupPayroll_Lists(){
	GroupPayroll_Lists.clearAndLoad("xml-payroll-employee-detail?ID=<?php echo $_GET['ID']?>");	

}


</script>
