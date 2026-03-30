<div class="card mt-3">
    <div class="card-body">
        <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">LAPORAN KEUANGAN</div>
        <div id="ProfitSharingLkMonthly" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        <div>
            <input type="button" id="ekselProfitSharingLkMonthlyDet" name="ekselProfitSharingLkMonthlyDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ProfitSharingLkMonthly.toExcel('./export/generate.php');" />
            {{-- <input type="button" id="detailProfitSharingLkMonthly" name="detailProfitSharingLkMonthly" value="Detail Warehouse Profit Sharing LK Monthly" class="btn btn-info mt-2 mr-2" onclick="ProfitSharingLkMonthlyLKManually()" /> --}}
        </div>
    </div>
</div>
<div id="divlistOpportunityDetail"></div>

<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["date_filter"]);
	myCalendar.setDateFormat("%Y-%m");
	// function ProfitSharingLkMonthlyLKManually() {

	// 	ProfitSharingLkMonthlyID = ProfitSharingLkMonthly.getSelectedId();
	// 	if (ProfitSharingLkMonthlyID != null) {
	// 		LoadM('divlistOpportunityDetail', 'partner-profit-sharing-lk-monthly-detail?Task=' + ProfitSharingLkMonthlyID);
	// 	}else {
	// 		alert("Pilih baris data");
	// 	}
	// 	return true;
	// }
	ProfitSharingLkMonthly = new dhtmlXGridObject('ProfitSharingLkMonthly');
	ProfitSharingLkMonthly.setImagePath("./dhtmlx/imgs/");
	ProfitSharingLkMonthly.setHeader(",Description, Value, Satuan, Percentage");
	ProfitSharingLkMonthly.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter");
	// ProfitSharingLkMonthly.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ProfitSharingLkMonthly.setInitWidths("60,200,150,100,80");
	ProfitSharingLkMonthly.setColAlign("center,left,right,center,right");
	ProfitSharingLkMonthly.setColSorting("int,str,int,str,str");
	ProfitSharingLkMonthly.setColTypes("ron,ro,ron,ro,ro,ro");
	ProfitSharingLkMonthly.enableSmartRendering(true, 150);
	ProfitSharingLkMonthly.setNumberFormat("0,000", 2, ".", ",");
	// ProfitSharingLkMonthly.attachEvent("onRowDblClicked", ProfitSharingLkMonthlyLKManually);
	ProfitSharingLkMonthly.setSkin("dhx_skyblue");
	ProfitSharingLkMonthly.init();
	refreshdataDetail();

	function refreshdataDetail() {
		let type = "{{ strtolower($warehouse->type) ?? 'booth' }}";
		ProfitSharingLkMonthly.clearAndLoad('xml-partner-profit-sharing-lk-monthly?Task={{$warehouse->warehouse_task_id}}&date={{ $date ?? date("Y-m") }}' + "&type=" + type);
	}
</script>