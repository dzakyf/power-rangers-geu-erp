<div id="GridReferences" style="width:auto;height:280px; background-color:white;overflow:hidden"></div>
<div>
<input type="button" id="closeRef"  value="Close" />                
</div>

<script>
function CloseWin() {
	parent.winreference.window("Wreference").close();
}

parent.winreference.window("Wreference").progressOff();

$('#closeRef').click(function(){
	
CloseWin();							   
							   
							   });

function EventCOA(){
	COANo = GridReferences.cells(GridReferences.getSelectedId(),3).getValue();
	PBankOUT.setUpdated(BankOUT.getSelectedId(),true);
	BankOUT.cells(BankOUT.getSelectedId(),11).setValue(COANo);
	CloseWin();	
}


GridReferences = new dhtmlXGridObject('GridReferences');
GridReferences.setImagePath("./dhtmlx/imgs/");
GridReferences.setHeader("No.,PO Initial,Opportunity,PO No,Subject,PO Date,Reference,Description,Amount");
GridReferences.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
GridReferences.setInitWidths("70,150,100,120,200,100,100,100,100");
GridReferences.setColAlign("center,center,left,center,left,center,center,left,right");
GridReferences.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ron");
GridReferences.attachEvent("onRowDblClicked", EventCOA);
GridReferences.setColSorting("int,str,str,str,str,str,str,str,int");
GridReferences.setSkin("dhx_skyblue");
GridReferences.setNumberFormat("0,000",8,".",",");
GridReferences.init();
GridReferences.loadXML('xml-finance-reference');

</script>
