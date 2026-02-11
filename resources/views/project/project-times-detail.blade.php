<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">TIME TRACKING</div>
<div id="LayoutObjc" style="width:auto;height:700px; background-color:white;overflow:hidden"></div>

<script>
			myLayout = new dhtmlXLayoutObject("LayoutObjc", "2U");
		
			myLayout.cells("a").setText("Costs");
			myLayout.cells("b").setText("Documents");
		//	myLayout.cells("d").setText("Timeline");
		//myLayout.cells("b").attachURL("opportunity-timeline-file?ID=<?php echo $_GET['Task_ID']?>");
//myLayout.cells("a").attachObject("diagram");

GridCosts = myLayout.cells("a").attachGrid();
GridCosts.setImagePath("./dhtmlx/imgs/");
GridCosts.setHeader("No,Cost ID,Cost Type,Date,Amounts");
GridCosts.attachFooter("Total,#cspan,#cspan,#cspan,#stat_total");
GridCosts.setInitWidths("60,100,100,100,100");
GridCosts.setColAlign("center,left,center,left,right,right");
GridCosts.setColSorting("int,str,str,str,str,str");
GridCosts.setColTypes("ro,ro,ro,ro,ron");
GridCosts.setNumberFormat("0,000",4,".",",");
GridCosts.setSkin("dhx_skyblue");
GridCosts.init();
refreshdataxx();

function refreshdataxx(){
	GridCosts.clearAndLoad('xml-project-times-costs?ID=<?php echo $_GET['Task_ID']?>');

}
function gridDocDownload() {
ProjecxID = gridDoc.cells(gridDoc.getSelectedId(),6).getValue();
window.open("https://erp/ksps.co.id/erpksps/public/project_file/" + ProjecxID);
return true;
}
gridDoc = myLayout.cells("b").attachGrid();
gridDoc.setImagePath("./dhtmlx/imgs/");
gridDoc.setHeader("No,Category,Doc No,Reference,Date,,pathfile");
gridDoc.setInitWidths("50,100,100,100,100,100,0");
gridDoc.setColAlign("center,center,center,center,center");
gridDoc.setColSorting("int,str,str,str,str,str");
gridDoc.setColTypes("ro,ro,ro,ro,ro,img,ro");
gridDoc.attachEvent("onRowDblClicked", gridDocDownload);

gridDoc.setSkin("dhx_skyblue");
gridDoc.init();
refreshdoc();

function refreshdoc(){
	gridDoc.clearAndLoad('xml-project-times-documents?ID=<?php echo $_GET['Task_ID']?>');

}

// var orgChartData = [
// 	{
// 		id: "1",
// 		text: "Yogie",
// 		title: "Project Manager",
// 		img: "../common/img/avatar-1.png"
// 	},
// 	{
// 		id: "2",
// 		text: "Irawan",
// 		title: "Finance SPV",
// 		img: "../common/img/avatar-2.png"
// 	},
// 	{
// 		id: "3",
// 		text: "Surya",
// 		title: "Admin SPV",
// 		img: "../common/img/avatar-3.png"
// 	},
// 	{
// 		id: "2.1",
// 		text: "Amira",
// 		title: "IT SPV",
// 		img: "../common/img/avatar-4.png"
// 	},
// 	{
// 		id: "3.1",
// 		text: "Didik",
// 		title: "Jonathan Lane",
// 		img: "../common/img/avatar-5.png"
// 	},
// 	{ id: "1-2", from: "1", to: "2", type: "line", points: [{ x: 100, y: 100 }] },
// 	{ id: "1-3", from: "1", to: "3", type: "line" },
// 	{ id: "2-2.1", from: "2", to: "2.1", type: "line" },
// 	{ id: "3-3.1", from: "3", to: "3.1", type: "line" },
// ];


// 	var diagram = new dhx.Diagram("diagram", { type: "org" });
// 			diagram.data.parse(orgChartData);



</script>
