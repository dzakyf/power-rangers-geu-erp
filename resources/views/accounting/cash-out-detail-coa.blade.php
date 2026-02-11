<div id="gridCOA" style="width:auto;height:280px; background-color:white;overflow:hidden"></div>
<div>
<input type="button" id="CloseCashWin"  value="Close" />                
</div>

<script>
function CloseWin() {
	parent.winCOA.window("WCOA").close();
}

parent.winCOA.window("WCOA").progressOff();

$('#CloseCashWin').click(function(){
	
CloseWin();							   
							   
							   });

function EventCOA(){
	COAID = gridCOA.getSelectedId();
	COANo = gridCOA.cells(gridCOA.getSelectedId(),1).getValue();
	DescCOA = gridCOA.cells(gridCOA.getSelectedId(),2).getValue();
	PGridCash.setUpdated(GridCash.getSelectedId(),true);
	GridCash.cells(GridCash.getSelectedId(),1).setValue(COANo);
	CloseWin();	
}


gridCOA = new dhtmlXGridObject('gridCOA');
gridCOA.setImagePath("./dhtmlx/imgs/");
gridCOA.setHeader("No.,Account No,Account Description,Group Bal");
gridCOA.attachHeader("&nbsp;,#text_filter,#text_filter,#combo_filter");
gridCOA.setInitWidths("70,100,250,auto");
gridCOA.setColAlign("center,center,left,left");
gridCOA.setColTypes("ro,ro,ro,ro");
gridCOA.attachEvent("onRowDblClicked", EventCOA);
gridCOA.setColSorting("int,str,str,str");
gridCOA.setSkin("dhx_skyblue");
gridCOA.init();
gridCOA.loadXML('xml-accounting-cash-out-detail-coa');

</script>
