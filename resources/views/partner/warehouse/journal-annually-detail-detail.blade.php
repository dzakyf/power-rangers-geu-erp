<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">WAREHOUSE JOURNAL</div>
		<div id="WarehouseJournalAnnuallyDetailDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseJournalAnnuallyDetailDetailDet" name="ekselWarehouseJournalAnnuallyDetailDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseJournalAnnuallyDetailDetail.toExcel('./export/generate.php');" />
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	WarehouseJournalAnnuallyDetailDetail = new dhtmlXGridObject('WarehouseJournalAnnuallyDetailDetail');
	WarehouseJournalAnnuallyDetailDetail.setImagePath("./dhtmlx/imgs/");
	WarehouseJournalAnnuallyDetailDetail.setHeader("No,Tanggal Journal, Nama akun, Nominal, Referensi, Catatan, Dokumen pendukung");
	WarehouseJournalAnnuallyDetailDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseJournalAnnuallyDetailDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseJournalAnnuallyDetailDetail.setInitWidths("60,100,150,200,100,100,200");
	WarehouseJournalAnnuallyDetailDetail.setColAlign("center,center,left,right,left,left,left");
	WarehouseJournalAnnuallyDetailDetail.setColSorting("int,str,int,int,int,str,str");
	WarehouseJournalAnnuallyDetailDetail.setColTypes("ron,ro,ro,ron,ro,ro,ro");
	WarehouseJournalAnnuallyDetailDetail.enableSmartRendering(true, 150);
	WarehouseJournalAnnuallyDetailDetail.setNumberFormat("0,000", 2, ".", ",");
	WarehouseJournalAnnuallyDetailDetail.setNumberFormat("0,000", 3, ".", ",");
	WarehouseJournalAnnuallyDetailDetail.setNumberFormat("0,000", 4, ".", ",");
	WarehouseJournalAnnuallyDetailDetail.setSkin("dhx_skyblue");
	WarehouseJournalAnnuallyDetailDetail.init();
	refreshdataDetailDetail();

	function refreshdataDetailDetail() {
		WarehouseJournalAnnuallyDetailDetail.clearAndLoad('xml-partner-warehouse-journal-annually-detail-detail?Task={{$taskid}}&task={{$date}}&accno={{$accno}}');
	}
</script>