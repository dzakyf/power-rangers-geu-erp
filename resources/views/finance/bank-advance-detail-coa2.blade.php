<div id="gridCOA" style="width:auto;height:280px; background-color:white;overflow:hidden"></div>
<div>
<input type="button" id="ClosebankWin"  value="Close" />                
</div>

<script>
function CloseWin() {
	parent.winCOA.window("WCOA").close();
}

parent.winCOA.window("WCOA").progressOff();

$('#ClosebankWin').click(function(){
	
CloseWin();							   
							   
							   });

function EventCOA(){
	COAID = gridCOA.getSelectedId();
	COANo = gridCOA.cells(gridCOA.getSelectedId(),1).getValue();
	DescCOA = gridCOA.cells(gridCOA.getSelectedId(),3).getValue();
	PGridbank.setUpdated(Gridbank.getSelectedId(),true);
	Gridbank.cells(Gridbank.getSelectedId(),8).setValue(COANo);
	Gridbank.cells(Gridbank.getSelectedId(),9).setValue(DescCOA);
	CloseWin();	
}


gridCOA = new dhtmlXGridObject('gridCOA');
gridCOA.setImagePath("./dhtmlx/imgs/");
gridCOA.setHeader("No.,Account No,Account Description,Reference");
gridCOA.attachHeader("&nbsp;,#text_filter,#text_filter,#combo_filter");
gridCOA.setInitWidths("70,100,250,200,100");
gridCOA.setColAlign("center,center,left,left");
gridCOA.setColTypes("ro,ro,ro,ro");
gridCOA.attachEvent("onRowDblClicked", EventCOA);
gridCOA.setColSorting("int,str,str,str");
gridCOA.setSkin("dhx_skyblue");
gridCOA.init();
gridCOA.loadXML('xml-finance-bank-advance-detail-coa2');

</script>
