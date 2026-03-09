<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">SUPPLIES WAREHOUSE</div>
		<div id="WarehouseMonitoringDetailSupplies" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseMonitoringDetailSuppliesDet" name="ekselWarehouseMonitoringDetailSuppliesDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoringDetailSupplies.toExcel('./export/generate.php');" />
		</div>
	</div>
</div>

<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">OUT WAREHOUSE</div>
		<div id="WarehouseMonitoringDetailRequest" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseMonitoringDetailRequestDet" name="ekselWarehouseMonitoringDetailRequestDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoringDetailRequest.toExcel('./export/generate.php');" />
		</div>
	</div>
</div>

<div id="divlistOpportunityDetailList"></div>

<script>
	WarehouseMonitoringDetailSupplies = new dhtmlXGridObject('WarehouseMonitoringDetailSupplies');
	WarehouseMonitoringDetailSupplies.setImagePath("./dhtmlx/imgs/");
	WarehouseMonitoringDetailSupplies.setHeader("No,Tanggal Penerimaan, Tipe Penjemputan, Nama POO, Sumber, Coverage, Volume Diterima, Harga / Liter, Total Diterima, Volume Reject (Jika Ada), Bukti Timbangan / Barang");
	WarehouseMonitoringDetailSupplies.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseMonitoringDetailSupplies.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseMonitoringDetailSupplies.setInitWidths("60,100,150,200,100,120,100,150,100,100,100,100");
	WarehouseMonitoringDetailSupplies.setColAlign("center,left,left,left,left,left,right,right,right,right,left");
	WarehouseMonitoringDetailSupplies.setColSorting("int,str,str,str,str,str,int,int,int,int,str");
	WarehouseMonitoringDetailSupplies.setColTypes("ro,ro,ro,ro,ro,ron,ron,ron,ron,ro,ro");
	WarehouseMonitoringDetailSupplies.enableSmartRendering(true, 150);
	WarehouseMonitoringDetailSupplies.setNumberFormat("0,000", 6, ".", ",");
	WarehouseMonitoringDetailSupplies.setNumberFormat("0,000", 7, ".", ",");
	WarehouseMonitoringDetailSupplies.setNumberFormat("0,000", 8, ".", ",");
	WarehouseMonitoringDetailSupplies.setNumberFormat("0,000", 9, ".", ",");
	// WarehouseMonitoringDetailSupplies.attachEvent("onRowDblClicked", WarehouseMonitoringDetailSuppliesKlik);
	WarehouseMonitoringDetailSupplies.setSkin("dhx_skyblue");
	WarehouseMonitoringDetailSupplies.init();
	refreshdataDetailSupplies();

	function refreshdataDetailSupplies() {
		WarehouseMonitoringDetailSupplies.clearAndLoad('xml-partner-warehouse-monitoring-detail-supplies?Task={{$_GET["Task"]}}&date={{$_GET["date"]}}');

	}

	WarehouseMonitoringDetailRequest = new dhtmlXGridObject('WarehouseMonitoringDetailRequest');
	WarehouseMonitoringDetailRequest.setImagePath("./dhtmlx/imgs/");
	WarehouseMonitoringDetailRequest.setHeader("No,Tanggal Keluar, Jumlah Stock Keluar, Harga Rata-rata, Total Pendapatan, Referensi, Catatan");
	WarehouseMonitoringDetailRequest.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseMonitoringDetailRequest.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseMonitoringDetailRequest.setInitWidths("60,100,150,200,100,100,200");
	WarehouseMonitoringDetailRequest.setColAlign("center,center,right,right,right,left,left");
	WarehouseMonitoringDetailRequest.setColSorting("int,str,int,int,int,str,str");
	WarehouseMonitoringDetailRequest.setColTypes("ron,ro,ron,ron,ron,ro,ro");
	WarehouseMonitoringDetailRequest.enableSmartRendering(true, 150);
	WarehouseMonitoringDetailRequest.setNumberFormat("0,000", 2, ".", ",");
	WarehouseMonitoringDetailRequest.setNumberFormat("0,000", 3, ".", ",");
	WarehouseMonitoringDetailRequest.setNumberFormat("0,000", 4, ".", ",");
	WarehouseMonitoringDetailRequest.setSkin("dhx_skyblue");
	WarehouseMonitoringDetailRequest.init();
	refreshdataDetailRequest();

	function refreshdataDetailRequest() {
		WarehouseMonitoringDetailRequest.clearAndLoad('xml-partner-warehouse-monitoring-detail-request?Task={{$_GET["Task"]}}&date={{$_GET["date"]}}');

	}
</script>