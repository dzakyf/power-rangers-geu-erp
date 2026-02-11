<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">PURCHASE REQUESTED</div>
<div id="PurchaseManagement" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
<div class="badan">
	<input type="button" name="ExportProduct" id="ExportProduct" class="btn btn-success mt-2 mr-2" value="New" onclick="addbaruPRProject();" />
</div>

<div id="PurchaseManagementDiv"></div>
<script>
	function addbaruPRProject() {

		PurchaseManagementMID = PurchaseManagement.getSelectedId();
		LoadM('PurchaseManagementDiv', 'project-purchase-add?ID=<?php echo $_GET['ID'] ?>');
	}

	function PurchaseManagementDoubleClick() {

		PurchaseManagementMID = PurchaseManagement.getSelectedId();
		LoadM('PurchaseManagementDiv', 'purchasing-request-approval-detail?ID=' + PurchaseManagementMID);
	}

	function refreshdata() {
		PurchaseManagement.clearAndLoad('xml-purchasing-request?ID=<?php echo $_GET['ID']; ?>');

	}

	PurchaseManagement = new dhtmlXGridObject('PurchaseManagement');
	PurchaseManagement.setImagePath("dhtmlx/imgs/");
	PurchaseManagement.setHeader("No.,PR No,PR Date,Category,Sub Category,Purchase Type,Status,Remark,Date Trx,Created By");
	PurchaseManagement.setInitWidths("70,170,100,100,100,100,100,100,120,100");
	PurchaseManagement.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	PurchaseManagement.setColAlign("center,center,center,center,center,center,center,left,center,center");
	PurchaseManagement.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	PurchaseManagement.setColSorting("int,str,str,str,str,str,str,str");
	PurchaseManagement.setEditable(false);
	PurchaseManagement.attachEvent("onRowDblClicked", PurchaseManagementDoubleClick);
	PurchaseManagement.setSkin("dhx_skyblue");
	PurchaseManagement.init();
	refreshdata()
	//PurchaseManagement.loadXML("xml-purchasing-request");
</script>