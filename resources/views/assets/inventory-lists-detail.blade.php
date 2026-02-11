<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">HISTORICAL OF PRODUCT CODE <?php echo $_GET['IdProd']?></div>
		<div id="InventoryListsDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselInventoryListsDetailDet" name="ekselInventoryListsDetailDet" value="Export Excel" class="btn btn-info mt-2 mr-2" onclick="InventoryListsDetail.toExcel('./export/generate.php');"  />
		</div>

	</div>	
<script>
InventoryListsDetail = new dhtmlXGridObject('InventoryListsDetail');
InventoryListsDetail.setImagePath("./dhtmlx/imgs/");
InventoryListsDetail.setHeader("No.,Date,In/Out,PO Num/SR No,Created By,Request By,Qty,Current Stock");
InventoryListsDetail.setInitWidths("60,150,80,220,120,120,120,120");
InventoryListsDetail.setColAlign("right,center,left,left,left,left,right,right");
InventoryListsDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro");
InventoryListsDetail.setColSorting("int,str,str,str,str,date,str,str");
InventoryListsDetail.setSkin("dhx_skyblue");
InventoryListsDetail.init();
refreshdatas();

function refreshdatas(){
	InventoryListsDetail.clearAndLoad('xml-inventory-lists-detail?IdProd=<?php echo $_GET['IdProd']?>');

}


</script>
