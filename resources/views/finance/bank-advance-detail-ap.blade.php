<div id="APLISTING" style="width:auto;height:250px; background-color:white;overflow:hidden"></div>
<div>
<input type="button" value="Close" id="rejectInv" onclick="CloseWin()"   class="btn btn-danger mt-2 mr-2"  style="width:160px;"/>
</div>
	</div>	
<div id="divlistcustomer"></div>
	
<script>
	
function detailapklik() {
		COAID = APLISTING.getSelectedId();
		COANo = APLISTING.cells(APLISTING.getSelectedId(), 1).getValue();
		dateAP = APLISTING.cells(APLISTING.getSelectedId(), 2).getValue();
		remark = APLISTING.cells(APLISTING.getSelectedId(), 3).getValue();
		uang = APLISTING.cells(APLISTING.getSelectedId(), 4).getValue();
		dept = APLISTING.cells(APLISTING.getSelectedId(), 6).getValue();
		Gridbank.cells(Gridbank.getSelectedId(), 1).setValue("211.1");
		Gridbank.cells(Gridbank.getSelectedId(), 2).setValue(dateAP);
		Gridbank.cells(Gridbank.getSelectedId(), 3).setValue(COANo);
		Gridbank.cells(Gridbank.getSelectedId(), 4).setValue(remark);
		Gridbank.cells(Gridbank.getSelectedId(), 5).setValue(uang);
		Gridbank.cells(Gridbank.getSelectedId(), 6).setValue(1);
		Gridbank.cells(Gridbank.getSelectedId(), 7).setValue(dept);
		Gridbank.cells(Gridbank.getSelectedId(), 10).setValue(uang);
		Gridbank.cells(Gridbank.getSelectedId(), 11).setValue(0);
		CloseWin();
	}
function CloseWin() {
		parent.winCOA.window("WCOA").close();
	}

	
	
	
APLISTING = new dhtmlXGridObject('APLISTING');
APLISTING.setImagePath("./dhtmlx/imgs/");
APLISTING.setHeader("No.,AP No,AP Date,Remark,Sisa AP,Due Date,Department");
APLISTING.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
APLISTING.setInitWidths("60,120,100,100,150,100,100,100,60,200,280,120,100");
APLISTING.setColAlign("center,center,center,center,center,right,right,center,center,center,left,center,center");
APLISTING.setColTypes("ro,ro,ro,ro,ron,ro,ro,ro,ro,ro,ro");
APLISTING.setEditable(true);
APLISTING.setSkin("dhx_skyblue");
APLISTING.setDateFormat("%Y-%m-%d");
APLISTING.setNumberFormat("0,000",4,".",",");
APLISTING.attachEvent("onRowDblClicked", detailapklik);
APLISTING.init();
refreshdata();

function refreshdata(){
	APLISTING.clearAndLoad('xml-finance-bank-advance-detail-ap');

}
</script>
