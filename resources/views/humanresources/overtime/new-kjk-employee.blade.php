<div id="gridKaryawan" style="width:auto;height:300px; background-color:white;overflow:hidden"></div>

<script>
function CloseWin() {
	parent.winCOA.window("WCOA").close();
}

parent.winCOA.window("WCOA").progressOff();

$('#ClosebankWin').click(function(){
	CloseWin();							   
});

function EventCOA(){
	COAID = "<?php echo $_GET['id']?>";
	COANo = gridKaryawan.cells(gridKaryawan.getSelectedId(),1).getValue();
	DescCOA = gridKaryawan.cells(gridKaryawan.getSelectedId(),2).getValue();
	dx1 = gridKaryawan.cells(gridKaryawan.getSelectedId(),6).getValue();
	dx2 = gridKaryawan.cells(gridKaryawan.getSelectedId(),7).getValue();
	var salgroup = gridKaryawan.cells(gridKaryawan.getSelectedId(),8).getValue();
	KJK.cells(COAID,2).setValue(COANo);
	KJK.cells(COAID,3).setValue(DescCOA);
	KJK.cells(COAID,4).setValue(dx1);
	KJK.cells(COAID,5).setValue(dx2);
	KJK.cells(COAID,10).setValue(salgroup);
	CloseWin();	
}


gridKaryawan = new dhtmlXGridObject('gridKaryawan');
gridKaryawan.setImagePath("./dhtmlx/imgs/");
gridKaryawan.setHeader("No.,NIK,Name,Gender,Position,Work Area,Allowance,Rate,Salary Group");
gridKaryawan.attachHeader("#rspan,#text_filter,#text_filter,#combo_filter,#text_filter,#text_filter,,,");
gridKaryawan.setInitWidths("70,100,250,100,100,100,10,10,100");
gridKaryawan.setColAlign("center,center,left,left,center,center,center,center");
gridKaryawan.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
gridKaryawan.attachEvent("onRowDblClicked", EventCOA);
gridKaryawan.setColSorting("int,str,str,str,str,str,str,str");
gridKaryawan.setSkin("dhx_skyblue");
gridKaryawan.init();
gridKaryawan.loadXML('xml-kjk-employee');

</script>
