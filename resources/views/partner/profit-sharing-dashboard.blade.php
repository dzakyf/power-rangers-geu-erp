<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;

?>
<div class="mt-3">
	<div class="card">
		<div class="card-body">
		<div>
			<input type="text" id="date_filter" name="date_filter" value="{{ date('Y') }}" size="10" readonly>
			<select name="type" id="type">
				<option value="booth">booth</option>
				<option value="mobil">mobil</option>
			</select>
			<button class="btn btn-success" onclick="refreshdataDetail()">Refresh</button>
		</div>
        <div class="JudulContent" style="color:#699;background-image:url(hdr_back.gif);">DASHBOARD PROFIT SHARING</div>
        <div id="ProfitSharingDetailDashboard" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
        <div>
            <input type="button" id="ekselProfitSharingDetailDashboardDet" name="ekselProfitSharingDetailDashboardDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="ProfitSharingDetailDashboard.toExcel('./export/generate.php');" />
        </div>
		</div>
	</div>

</div>

<script>
	var myCalendar;
	myCalendar = new dhtmlXCalendarObject(["date_filter"]);
	myCalendar.setDateFormat("%Y-%m");
	ProfitSharingDetailDashboard = new dhtmlXGridObject('ProfitSharingDetailDashboard');
	ProfitSharingDetailDashboard.setImagePath("./dhtmlx/imgs/");
	ProfitSharingDetailDashboard.setHeader("No,Bulan, Target Penjualan, Acv Penjualan, Biaya Operasional, Target Nett Profit, Acv Net Profit, GAP Penjualan, Gap Net Profit, Kesesuaian BEP, Status");
	ProfitSharingDetailDashboard.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// ProfitSharingDetailDashboard.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	ProfitSharingDetailDashboard.setInitWidths("60,100,150,150,80,150,150,150,150,150,150");
	ProfitSharingDetailDashboard.setColAlign("center,left,right,right,right,right,right,right,right,right,left,left");
	ProfitSharingDetailDashboard.setColSorting("int,str,int,int,int,int,int,int,int,int,str,str");
	ProfitSharingDetailDashboard.setColTypes("ron,ro,ron,ron,ron,ron,ron,ron,ron,ron,ro,ro");
	ProfitSharingDetailDashboard.enableSmartRendering(true, 150);
	ProfitSharingDetailDashboard.setNumberFormat("0,000", 2, ".", ",");
	ProfitSharingDetailDashboard.setNumberFormat("0,000", 3, ".", ",");
	ProfitSharingDetailDashboard.setNumberFormat("0,000", 4, ".", ",");
	ProfitSharingDetailDashboard.setNumberFormat("0,000", 5, ".", ",");
	ProfitSharingDetailDashboard.setNumberFormat("0,000", 6, ".", ",");
	ProfitSharingDetailDashboard.setNumberFormat("0,000", 7, ".", ",");
	ProfitSharingDetailDashboard.setNumberFormat("0,000", 8, ".", ",");
	ProfitSharingDetailDashboard.setSkin("dhx_skyblue");
	ProfitSharingDetailDashboard.init();
	refreshdataDetail();

	function refreshdataDetail() {
		ProfitSharingDetailDashboard.clearAndLoad('xml-partner-profit-sharing-dashboard?date=' + $('#date_filter').val() + "&type=" + $('#type').val() + '&mitra={{$user}}');
	}
</script>