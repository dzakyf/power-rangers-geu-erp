<div class="card mt-3">
	<div class="card-body">
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">WAREHOUSE JOURNAL</div>
		<div id="WarehouseJournalDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseJournalDetailDet" name="ekselWarehouseJournalDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseJournalDetail.toExcel('./export/generate.php');" />
			<input type="button" id="detailWarehouseJournalDetail" name="detailWarehouseJournalDetail" value="Detail Operational" class="btn btn-info mt-2 mr-2" onclick="WarehouseJournalDetailKlik()" />
			<input type="button" id="newWarehouseJournalDetail" name="newWarehouseJournalDetail" value="New Operational" class="btn btn-success mt-2 mr-2" onclick="WarehouseJournalDetailNew()" />
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	function WarehouseJournalDetailNew() {

		LoadM('divlistOpportunityDetail', 'partner-warehouse-journal-detail-new?Task={{ $_GET["Task"] }}');
		return true;
	}
	function WarehouseJournalDetailKlik() {

		WarehouseJournalDetailID = WarehouseJournalDetail.getSelectedId();
		if (WarehouseJournalDetailID != null) {
			LoadM('divlistOpportunityDetail', 'partner-warehouse-journal-detail-detail?Task=' + WarehouseJournalDetailID);
		}else {
			alert("Pilih baris data");
		}
		return true;
	}
	WarehouseJournalDetail = new dhtmlXGridObject('WarehouseJournalDetail');
	WarehouseJournalDetail.setImagePath("./dhtmlx/imgs/");
	WarehouseJournalDetail.setHeader("No,Tanggal Journal, Nama akun, Nominal, Referensi, Catatan, Dokumen pendukung");
	WarehouseJournalDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// WarehouseJournalDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseJournalDetail.setInitWidths("60,100,150,200,100,100,200");
	WarehouseJournalDetail.setColAlign("center,center,left,right,left,left,left");
	WarehouseJournalDetail.setColSorting("int,str,int,int,int,str,str");
	WarehouseJournalDetail.setColTypes("ron,ro,ro,ron,ro,ro,ro");
	WarehouseJournalDetail.enableSmartRendering(true, 150);
	WarehouseJournalDetail.attachEvent("onRowDblClicked", WarehouseJournalDetailKlik);
	WarehouseJournalDetail.setNumberFormat("0,000", 2, ".", ",");
	WarehouseJournalDetail.setNumberFormat("0,000", 3, ".", ",");
	WarehouseJournalDetail.setNumberFormat("0,000", 4, ".", ",");
	WarehouseJournalDetail.setSkin("dhx_skyblue");
	WarehouseJournalDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		WarehouseJournalDetail.clearAndLoad('xml-partner-warehouse-journal-detail?Task={{$_GET["Task"]}}');

	}
</script>