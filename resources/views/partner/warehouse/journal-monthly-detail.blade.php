<div class="card mt-3">
	<div class="card-body">
		<div>
			Date Filter :: <input type="text" name="date_filter" id="date_filter" value="{{ $date }}" size="10" readonly>
			<button class="btn btn-success" onclick="refreshdataDetail()">Refresh</button>
		</div>
		<div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">WAREHOUSE JOURNAL</div>
		<div id="WarehouseJournalMonthlyDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselWarehouseJournalMonthlyDetailDet" name="ekselWarehouseJournalMonthlyDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="WarehouseJournalMonthlyDetail.toExcel('./export/generate.php');" />
			<input type="button" id="detailWarehouseJournalMonthlyDetail" name="detailWarehouseJournalMonthlyDetail" value="Detail Operational" class="btn btn-info mt-2 mr-2" onclick="WarehouseJournalMonthlyDetailKlik()" />
		</div>
	</div>
</div>

<div id="divlistOpportunityDetail"></div>

<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["date_filter"]);
	myCalendar.setDateFormat("%Y-%m");

	function WarehouseJournalMonthlyDetailKlik() {

		WarehouseJournalMonthlyDetailID = WarehouseJournalMonthlyDetail.getSelectedId();
		if (WarehouseJournalMonthlyDetailID != null) {
			LoadM('divlistOpportunityDetail', 'partner-warehouse-journal-monthly-detail-detail?Task=' + WarehouseJournalMonthlyDetailID + "&taskid={{$_GET['Task']}}");
		}else {
			alert("Pilih baris data");
		}
		return true;
	}

	WarehouseJournalMonthlyDetail = new dhtmlXGridObject('WarehouseJournalMonthlyDetail');
	WarehouseJournalMonthlyDetail.setImagePath("./dhtmlx/imgs/");
	WarehouseJournalMonthlyDetail.setHeader("No,Tanggal Journal, Nama akun, Nominal");
	WarehouseJournalMonthlyDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter");
	// WarehouseJournalMonthlyDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	WarehouseJournalMonthlyDetail.setInitWidths("60,100,150,200");
	WarehouseJournalMonthlyDetail.setColAlign("center,center,left,right");
	WarehouseJournalMonthlyDetail.setColSorting("int,str,str,int");
	WarehouseJournalMonthlyDetail.setColTypes("ron,ro,ro,ron");
	WarehouseJournalMonthlyDetail.enableSmartRendering(true, 150);
	WarehouseJournalMonthlyDetail.attachEvent("onRowDblClicked", WarehouseJournalMonthlyDetailKlik);
	WarehouseJournalMonthlyDetail.setNumberFormat("0,000", 3, ".", ",");
	WarehouseJournalMonthlyDetail.setSkin("dhx_skyblue");
	WarehouseJournalMonthlyDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		WarehouseJournalMonthlyDetail.clearAndLoad('xml-partner-warehouse-journal-monthly-detail?Task={{$_GET["Task"]}}' + "&date=" + $('#date_filter').val());

	}
</script>