 @include('layout.plugins')
<?php
error_reporting(0);
?>


<table cellpadding="0" cellspacing="0" width="100%" align="center" style="padding-top:10;" border="0">
    <tr height="30">
        <td class="badan" colspan="6">
			<div id="InsuranceGrid" style="width:auto;height:330px; background-color:white;overflow:hidden"></div>
			
        </td>
	</tr>
   
 
</table>

<script>
function tambahdatainsuransiS(){
	InsuranceGrid.addRow(InsuranceGrid.uid(),[InsuranceGrid.getRowsNum()+1,""],0);
}
			InsuranceGrid = new dhtmlXGridObject('InsuranceGrid');
			InsuranceGrid.setImagePath("./dhtmlx/imgs/");
			InsuranceGrid.setHeader("No.,Employee Name,On Behalf,On Behalf Status,Insurances,Card No,Polis No,In Salary,Polis Type,Polis Benefit,Polis Reimbursement,Polis Status,Date Start, End Date,Date Submission,Employee Collateral,Salary Component");
			InsuranceGrid.setInitWidths("60,120,220,100,100,100,100,100,100,100,100,100,100,100,100,100,100");
			InsuranceGrid.setColAlign("center,center,center,left,center,center,center,center,center,center,center,center,center,center,center,center,center");
			InsuranceGrid.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
			InsuranceGrid.setColTypes("ro,ro,ed,coro,ro,ro,ed,ch,ed,ed,ed,ed,ed,ed,ed,ed,ed");
			InsuranceGrid.setDateFormat("%Y-%m-%d"); 
			PInsuranceGrid = new dataProcessor("recruitment-lists-detail-insurance-update");
			PInsuranceGrid.setTransactionMode({
			 mode: "POST",
			  total:true,
			  headers:{
				'X-CSRF-TOKEN': "{{ csrf_token() }}"
			  }	,
			  payload: {"Task_ID":"<?php echo $_GET['ID'] ?>"}

			})
			PInsuranceGrid.init(InsuranceGrid);
			InsuranceGrid.setEditable(true);
			InsuranceGrid.setSkin("dhx_skyblue");
			InsuranceGrid.enableEditEvents(true,false,true);
			InsuranceGrid.init();
			InsuranceGrid.loadXML("xml-recruitment-lists-detail-insurance?ID=<?php echo $_GET['ID'];?>");
			InsuranceGrid.getCombo(3).put("OWNER","WIFE");
			InsuranceGrid.getCombo(3).put("ISTRI","ISTRI");
			InsuranceGrid.getCombo(3).put("ANAK KE 1","ANAK KE 1");
			InsuranceGrid.getCombo(3).put("ANAK KE 2","ANAK KE 2");
			InsuranceGrid.getCombo(3).put("ANAK KE 3","ANAK KE 3");
			InsuranceGrid.getCombo(3).put("ANAK KE 4","ANAK KE 4");
			
</script>