<div id="gridCOA" style="width:auto;height:280px; background-color:white;overflow:hidden"></div>
<div>
	<input type="button" id="CloseBankWin" value="Close" />
</div>

<script>
	function CloseWin() {
		parent.winCOA.window("WCOA").close();
	}

	parent.winCOA.window("WCOA").progressOff();

	$('#CloseBankWin').click(function() {

		CloseWin();

	});

	function EventCOA() {
		COAID = gridCOA.getSelectedId();
		COANo = gridCOA.cells(gridCOA.getSelectedId(), 1).getValue();
		reference = gridCOA.cells(gridCOA.getSelectedId(), 4).getValue();

		PCTRX.setUpdated(CTRX.getSelectedId(), true);
		CTRX.cells(CTRX.getSelectedId(), 5).setValue(COANo);
		CTRX.cells(CTRX.getSelectedId(), 10).setValue(reference);
		CloseWin();
	}


	gridCOA = new dhtmlXGridObject('gridCOA');
	gridCOA.setImagePath("./dhtmlx/imgs/");
	gridCOA.setHeader("No.,Account No,Account Description,Group Bal, Referensi");
	gridCOA.attachHeader("&nbsp;,#text_filter,#text_filter,#combo_filter");
	gridCOA.setInitWidths("70,100,150,150,auto");
	gridCOA.setColAlign("center,center,left,left,left");
	gridCOA.setColTypes("ro,ro,ro,ro,ro");
	gridCOA.attachEvent("onRowDblClicked", EventCOA);
	gridCOA.setColSorting("int,str,str,str,str");
	gridCOA.setSkin("dhx_skyblue");
	gridCOA.init();
	gridCOA.loadXML('xml-accounting-report-coa');
</script>