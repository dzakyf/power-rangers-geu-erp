<?php if ((auth()->user()->Priv_ID ?? '') === 'MITRA') { abort(403); } ?>
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
		<h5>Assign Sales History Detail Log</h5>
		<div id="AssignSalesDetailLog" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselAssignSalesDetailLogDet" name="ekselAssignSalesDetailLogDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="AssignSalesDetailLog.toExcel('./export/generate.php');" />
		</div>
	</div>
</div>
<div class="mt-3" id="divlistAssignDetail"></div>
<script>
	AssignSalesDetailLog = new dhtmlXGridObject('AssignSalesDetailLog');
	AssignSalesDetailLog.setImagePath("./dhtmlx/imgs/");
	AssignSalesDetailLog.setHeader("No,Task ID,Created Person,Full Name, Date, Active Contact, Partnership Interest, Capital Estimation (Modal/RP), Created Date, Source, Response, Kualifikasi, Klasifikasi, Status, Note Cancellation,Action Status, Action By, Action Date,");
	AssignSalesDetailLog.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// AssignSalesDetailLog.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	AssignSalesDetailLog.setInitWidths("60,150,100,200,100,120,120,200,100,100,100,100,100,100,100,300,150,200");
	AssignSalesDetailLog.setColAlign("center,left,left,left,left,left,left,left,left,left,left,left,left,left,left,left,center,center");
	AssignSalesDetailLog.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str,str");
	AssignSalesDetailLog.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro,ro");
	AssignSalesDetailLog.enableSmartRendering(true, 150);
	// AssignSalesDetailLog.setNumberFormat("0,000", 7, ".", ",");
	AssignSalesDetailLog.setSkin("dhx_skyblue");
	AssignSalesDetailLog.init();
	refreshdataDetailLog();

	function refreshdataDetailLog() {
		AssignSalesDetailLog.clearAndLoad('xml-partner-assign-history-detail-log?ID={{ $_GET["ID"] }}');
	}
</script>