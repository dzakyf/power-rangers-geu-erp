			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SALARY DETAIL OF <?php echo $_GET['ID']?></div>
			<div id="PayrollDetail" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="PayrollDetail.toExcel('./export/generate.php');"  />

			<input type="button" id="ekselPODListDet2" name="ekselPODListDet2" value="Posting Now" class="btn btn-success mt-2 mr-2" onclick="postingnow();"  />
			</div>

		<div id="GroupPayroll_ListsDiv"></div>


	
<script>


PayrollDetail = new dhtmlXGridObject('PayrollDetail');
PayrollDetail.setImagePath("./dhtmlx/imgs/");
PayrollDetail.setHeader("No,Payroll ID,No Urut,Name,Fix Allowance,CoA,Category,Amount");
PayrollDetail.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter");
PayrollDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total");
PayrollDetail.setInitWidths("70,100,100,200,100,100,100,100");
PayrollDetail.setColAlign("center,left,center,left,center,center,center,right");
PayrollDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron");
PayrollDetail.setColSorting("int,str,str,str,str,str,str,str,str");
PayrollDetail.setNumberFormat("0,000",7,".",",");
PayrollDetail.setSkin("dhx_skyblue");
PayrollDetail.setEditable(false);
PayrollDetail.enableMultiline(true);
PayrollDetail.init();
PayrollDetail.clearAndLoad('xml-accounting-payroll-issued-detail?Task_ID=<?php echo $_GET['ID']?>');

function postingnow(){
	
		dhtmlx.confirm('Posting Data Now?', 'Salary Posting', function(r) {
					
				if (r==true){
					$.blockUI();
					LoadM('GroupPayroll_ListsDiv','accounting-payroll-issued-posting?Task_ID=<?php echo $_GET['ID']?>');					
					
				}
			})
}

</script>
