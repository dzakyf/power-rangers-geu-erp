<div class="card mt-3">
	<div class="card-body">
		<div>
			Date Filter :: <input type="text" name="date_filter" id="date_filter" value="{{ $date }}" size="10" readonly>
			<button class="btn btn-success" onclick="refreshdataDetail()">Refresh</button>
		</div>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">WAREHOUSE JOURNAL</div>
		<div id="WarehouseJournalAnnuallyDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseJournalAnnuallyDetailDet" name="ekselWarehouseJournalAnnuallyDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseJournalAnnuallyDetail.toExcel('./export/generate.php');" />
			<input type="button" id="detailWarehouseJournalAnnuallyDetail" name="detailWarehouseJournalAnnuallyDetail" value="Detail Operational" class="btn btn-info mt-2 mr-2" onclick="WarehouseJournalAnnuallyDetailKlik()" />
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["date_filter"]);
	myCalendar.setDateFormat("%Y");

	function WarehouseJournalAnnuallyDetailKlik() {

		WarehouseJournalAnnuallyDetailID = WarehouseJournalAnnuallyDetail.getSelectedId();
		if (WarehouseJournalAnnuallyDetailID != null) {
			LoadM('divlistOpportunityDetail', 'partner-warehouse-journal-annually-detail-detail?Task=' + WarehouseJournalAnnuallyDetailID + "&taskid={{$_GET['Task']}}");
		}else {
			alert("Pilih baris data");
		}
		return true;
	}

	WarehouseJournalAnnuallyDetail = new dhtmlXGridObject('WarehouseJournalAnnuallyDetail');
	WarehouseJournalAnnuallyDetail.setImagePath("./dhtmlx/imgs/");
	WarehouseJournalAnnuallyDetail.setHeader("No,Tanggal Journal, Nama akun, Nominal");
	WarehouseJournalAnnuallyDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter");
	// WarehouseJournalAnnuallyDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseJournalAnnuallyDetail.setInitWidths("60,100,150,200");
	WarehouseJournalAnnuallyDetail.setColAlign("center,center,left,right");
	WarehouseJournalAnnuallyDetail.setColSorting("int,str,str,int");
	WarehouseJournalAnnuallyDetail.setColTypes("ron,ro,ro,ron");
	WarehouseJournalAnnuallyDetail.enableSmartRendering(true, 150);
	WarehouseJournalAnnuallyDetail.attachEvent("onRowDblClicked", WarehouseJournalAnnuallyDetailKlik);
	WarehouseJournalAnnuallyDetail.setNumberFormat("0,000", 3, ".", ",");
	WarehouseJournalAnnuallyDetail.setSkin("dhx_skyblue");
	WarehouseJournalAnnuallyDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		WarehouseJournalAnnuallyDetail.clearAndLoad('xml-partner-warehouse-journal-annually-detail?Task={{$_GET["Task"]}}' + "&date=" + $('#date_filter').val());

	}
</script>