<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">WAREHOUSE JOURNAL</div>
		<div id="WarehouseJournalMonthlyDetailDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseJournalMonthlyDetailDetailDet" name="ekselWarehouseJournalMonthlyDetailDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseJournalMonthlyDetailDetail.toExcel('./export/generate.php');" />
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	WarehouseJournalMonthlyDetailDetail = new dhtmlXGridObject('WarehouseJournalMonthlyDetailDetail');
	WarehouseJournalMonthlyDetailDetail.setImagePath("./dhtmlx/imgs/");
	WarehouseJournalMonthlyDetailDetail.setHeader("No,Tanggal Journal, Nama akun, Nominal, Referensi, Catatan, Dokumen pendukung");
	WarehouseJournalMonthlyDetailDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseJournalMonthlyDetailDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseJournalMonthlyDetailDetail.setInitWidths("60,100,150,200,100,100,200");
	WarehouseJournalMonthlyDetailDetail.setColAlign("center,center,left,right,left,left,left");
	WarehouseJournalMonthlyDetailDetail.setColSorting("int,str,int,int,int,str,str");
	WarehouseJournalMonthlyDetailDetail.setColTypes("ron,ro,ro,ron,ro,ro,ro");
	WarehouseJournalMonthlyDetailDetail.enableSmartRendering(true, 150);
	WarehouseJournalMonthlyDetailDetail.setNumberFormat("0,000", 2, ".", ",");
	WarehouseJournalMonthlyDetailDetail.setNumberFormat("0,000", 3, ".", ",");
	WarehouseJournalMonthlyDetailDetail.setNumberFormat("0,000", 4, ".", ",");
	WarehouseJournalMonthlyDetailDetail.setSkin("dhx_skyblue");
	WarehouseJournalMonthlyDetailDetail.init();
	refreshdataDetailDetail();

	function refreshdataDetailDetail() {
		WarehouseJournalMonthlyDetailDetail.clearAndLoad('xml-partner-warehouse-journal-monthly-detail-detail?Task={{$taskid}}&task={{$date}}&accno={{$accno}}');
	}
</script>