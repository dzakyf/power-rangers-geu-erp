			<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SALARY DETAIL OF <?php echo $_GET['ID']?></div>
			<div id="PayrollDetail" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>
			<input type="button" id="ekselPODListDet" name="ekselPODListDet" value="Export Excel" class="btn btn-warning mt-2 mr-2" onclick="PayrollDetail.toExcel('./export/generate.php');"  />
			</div>

		<div id="GroupPayroll_ListsDiv"></div>


	
<script>

function PayrollDetailKlik() {

PayrollDetailID = PayrollDetail.cells(PayrollDetail.getSelectedId(),2).getValue();

if (PayrollDetailID !=''){
	window.open('print-salary?Task_ID=<?php echo $_GET['ID']?>&NIK=' + PayrollDetailID);
	//LoadM('GroupPayroll_ListsDiv','payroll-approval-detail-detail?Task_ID=<?php echo $_GET['ID']?>&ID=' + PayrollDetailID);
	
}
return true;
}
PayrollDetail = new dhtmlXGridObject('PayrollDetail');
PayrollDetail.setImagePath("./dhtmlx/imgs/");
PayrollDetail.setHeader("No,Name,NIK,Gender,Group Category,Salary Group,Position,Basic Salary,Allowance,Overtime,KJK,WHT Allowance,Deduction,PPH,THP");
PayrollDetail.attachHeader("#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter");
PayrollDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total,#stat_total");
PayrollDetail.setInitWidths("70,150,100,100,100,100,100,100,100,120,100,100,100,100,100");
PayrollDetail.setColAlign("center,left,center,center,center,center,center,right,right,right,right,right,right,right,right");
PayrollDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ron,ron,ron,ron,ron,ron,ron,ron");
PayrollDetail.setColSorting("int,str,str,str,str,str,str,str,str");
PayrollDetail.setNumberFormat("0,000",7,".",",");
PayrollDetail.setNumberFormat("0,000",8,".",",");
PayrollDetail.setNumberFormat("0,000",9,".",",");
PayrollDetail.setNumberFormat("0,000",10,".",",");
PayrollDetail.setNumberFormat("0,000",11,".",",");
PayrollDetail.setNumberFormat("0,000",12,".",",");
PayrollDetail.setNumberFormat("0,000",13,".",",");
PayrollDetail.setNumberFormat("0,000",14,".",",");
PayrollDetail.attachEvent("onRowDblClicked", PayrollDetailKlik);
PayrollDetail.setSkin("dhx_skyblue");
PayrollDetail.setEditable(false);
PayrollDetail.enableMultiline(true);
PayrollDetail.init();
PayrollDetail.clearAndLoad('xml-payroll-approval-detail?Task_ID=<?php echo $_GET['ID']?>');



</script>
