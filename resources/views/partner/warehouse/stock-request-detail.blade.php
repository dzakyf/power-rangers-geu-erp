<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">WAREHOUSE OUT</div>
		<div id="WarehouseListDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseListDetailDet" name="ekselWarehouseListDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseListDetail.toExcel('./export/generate.php');" />
			<input type="button" id="detailWarehouseListDetail" name="newWarehouseListDetail" value="New Warehouse Out" class="btn btn-success mt-2 mr-2" onclick="WarehouseListDetailNew()" />
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	function WarehouseListDetailNew() {

		LoadM('divlistOpportunityDetail', 'partner-warehouse-request-detail-new?Task={{ $_GET["Task"] }}');
		return true;
	}
	WarehouseListDetail = new dhtmlXGridObject('WarehouseListDetail');
	WarehouseListDetail.setImagePath("./dhtmlx/imgs/");
	WarehouseListDetail.setHeader("No,Tanggal Keluar, Jumlah Stock Keluar, Harga Rata-rata, Total Pendapatan, Referensi, Catatan");
	WarehouseListDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseListDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseListDetail.setInitWidths("60,100,150,200,100,100,200");
	WarehouseListDetail.setColAlign("center,center,right,right,right,left,left");
	WarehouseListDetail.setColSorting("int,str,int,int,int,str,str");
	WarehouseListDetail.setColTypes("ron,ro,ron,ron,ron,ro,ro");
	WarehouseListDetail.enableSmartRendering(true, 150);
	WarehouseListDetail.setNumberFormat("0,000", 2, ".", ",");
	WarehouseListDetail.setNumberFormat("0,000", 3, ".", ",");
	WarehouseListDetail.setNumberFormat("0,000", 4, ".", ",");
	WarehouseListDetail.setSkin("dhx_skyblue");
	WarehouseListDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		WarehouseListDetail.clearAndLoad('xml-partner-warehouse-request-detail?Task={{$_GET["Task"]}}');

	}
</script>