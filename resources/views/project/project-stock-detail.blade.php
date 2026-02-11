<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">REQUESTED INVENTORY</div>
<div id="SRManagement" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
<div class="badan">
	<input type="button" name="ExportProduct" id="ExportProduct" class="btn btn-success mt-2 mr-2" value="New" onclick="addbaruPRProject();" />
</div>

<div id="PurchaseManagementDiv"></div>
<script>
		function addbaruPRProject() {

			SRManagementID = SRManagement.getSelectedId();
			LoadM('PurchaseManagementDiv','project-stock-add?ID=<?php echo $_GET['ID']?>');
		}
		function SRManagementDoubleClick() {

			SRManagementID = SRManagement.getSelectedId();
			LoadM('PurchaseManagementDiv','project-stock-approval-detail?ID=' + SRManagementID);
		}

		SRManagement = new dhtmlXGridObject('SRManagement');
		SRManagement.setImagePath("dhtmlx/imgs/");
		SRManagement.setHeader("No.,SR No,SR Date,Supplies Category,Department,Status,Remark,Date Trx,Created By");
		SRManagement.attachHeader("Search,#text_filter,,#text_filter,#text_filter,#text_filter,#text_filter, ,#text_filter");
		SRManagement.setInitWidths("60,210,90,100,100,100,120,120,100");
		SRManagement.setColAlign("center,left,center,center,center,left,center,center,center");
		SRManagement.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
		SRManagement.setColSorting("int,str,str,str,str,str,str");
		SRManagement.setEditable(true);
		SRManagement.attachEvent("onRowDblClicked", SRManagementDoubleClick);
		SRManagement.setSkin("dhx_skyblue");
		SRManagement.init();
		</script>

