<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;


?>
<div class="card">
	<div class="card-body">
		<h5>Assign Sales History Detail</h5>
		<div id="AssignSalesDetail" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselAssignSalesDetailDet" name="ekselAssignSalesDetailDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="AssignSalesDetail.toExcel('./export/generate.php');" />
			<input type="button" id="historyleads" name="historyleads" value="Leads History" class="btn btn-info mt-2 mr-2" onclick="historyleads()" />
		</div>
	</div>
</div>
<div class="mt-3" id="divlistAssignDetail"></div>
<script>
	function AssignSalesDetailKlik() {

		AssignSalesDetailID = AssignSalesDetail.getSelectedId();
		if (AssignSalesDetailID != '') {
			LoadM('divlistAssignDetail', 'partner-assign-history-detail-log?ID=' + AssignSalesDetailID);
		}
		return true;
	}
	AssignSalesDetail = new dhtmlXGridObject('AssignSalesDetail');
	AssignSalesDetail.setImagePath("./dhtmlx/imgs/");
	AssignSalesDetail.setHeader("No,Task ID,Created Person,Full Name, Date, Active Contact, Partnership Interest, Capital Estimation (Modal/RP), Created Date, Source, Response, Kualifikasi, Klasifikasi, Status, Note Cancellation");
	AssignSalesDetail.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan");
	// AssignSalesDetail.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	AssignSalesDetail.setInitWidths("60,150,100,200,100,120,120,200,100,100,100,100,100,100,100");
	AssignSalesDetail.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left");
	AssignSalesDetail.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	AssignSalesDetail.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	AssignSalesDetail.enableSmartRendering(true, 150);
	// AssignSalesDetail.setNumberFormat("0,000", 7, ".", ",");
	AssignSalesDetail.attachEvent("onRowDblClicked", AssignSalesDetailKlik);
	AssignSalesDetail.setSkin("dhx_skyblue");
	AssignSalesDetail.init();
	refreshdataDetail();

	function refreshdataDetail() {
		AssignSalesDetail.clearAndLoad('xml-partner-assign-history-detail?ID={{ $_GET["ID"] }}');

	}

	function historyleads() {
		AssignSalesDetailKlik();
	}
</script>