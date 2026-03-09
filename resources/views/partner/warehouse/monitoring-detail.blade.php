<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">WAREHOUSE</div>
		<div id="WarehouseMonitoringDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseMonitoringDetailDet" name="ekselWarehouseMonitoringDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseMonitoringDetail.toExcel('./export/generate.php');" />
			<input type="button" id="detailWarehouseMonitoringDetail" name="newWarehouseMonitoringDetail" value="Detail Warehouse" class="btn btn-success mt-2 mr-2" onclick="WarehouseMonitoringDetailKlik()" />
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	function WarehouseMonitoringDetailKlik() {

		WarehouseMonitoringDetailID = WarehouseMonitoringDetail.getSelectedId();
		if (WarehouseMonitoringDetailID != null) {
			LoadM('divlistOpportunityDetail', 'partner-warehouse-monitoring-detail-list?Task={{$_GET["Task"]}}' + "&date=" + WarehouseMonitoringDetailID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseMonitoringDetail = new dhtmlXGridObject('WarehouseMonitoringDetail');
	WarehouseMonitoringDetail.setImagePath("./dhtmlx/imgs/");
	WarehouseMonitoringDetail.setHeader("No,Tanggal Penjualan, Total Stok, AAC, Nilai Inventory, Uji Kualitas UCO, , Volume Keluar, Sisa Volume Stock, Harga Transfer, Total Pendapatan, Bukti Pickup, Bukti Pembayaran");
	WarehouseMonitoringDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,FFA,M&I,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseMonitoringDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseMonitoringDetail.setInitWidths("60,150,150,150,200,120,120,150,150,150,150,150,150");
	WarehouseMonitoringDetail.setColAlign("center,center,right,right,right,left,left,right,right,right,right, center, center");
	WarehouseMonitoringDetail.setColSorting("int,str,int,int,int,str,str,int,int,int,int,ro,ro");
	WarehouseMonitoringDetail.setColTypes("ron,ro,ron,ron,ron,ro,ro,ron,ron,ron,ron,ro,ro");
	WarehouseMonitoringDetail.enableSmartRendering(true, 150);
	WarehouseMonitoringDetail.setNumberFormat("0,000", 2, ".", ",");
	WarehouseMonitoringDetail.setNumberFormat("0,000", 3, ".", ",");
	WarehouseMonitoringDetail.setNumberFormat("0,000", 4, ".", ",");
	WarehouseMonitoringDetail.setNumberFormat("0,000", 7, ".", ",");
	WarehouseMonitoringDetail.setNumberFormat("0,000", 8, ".", ",");
	WarehouseMonitoringDetail.setNumberFormat("0,000", 9, ".", ",");
	WarehouseMonitoringDetail.setNumberFormat("0,000", 10, ".", ",");
	WarehouseMonitoringDetail.attachEvent("onRowDblClicked", WarehouseMonitoringDetailKlik);
	WarehouseMonitoringDetail.setSkin("dhx_skyblue");
	WarehouseMonitoringDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		WarehouseMonitoringDetail.clearAndLoad('xml-partner-warehouse-monitoring-detail?Task={{$_GET["Task"]}}');

	}
</script>