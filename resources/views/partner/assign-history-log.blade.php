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
		<h5>Assign Sales History Log</h5>
		<div id="AssignHistoryLog" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
		<div>
			<input type="button" id="ekselAssignHistoryLogDet" name="ekselAssignHistoryLogDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="AssignHistoryLog.toExcel('./export/generate.php');" />
		</div>
	</div>
</div>
<script>
	AssignHistoryLog = new dhtmlXGridObject('AssignHistoryLog');
	AssignHistoryLog.setImagePath("./dhtmlx/imgs/");
	AssignHistoryLog.setHeader("No, Assignment ID, Status Assignment,Sales,Propose Date, Proposed By, Action Status, Action By, Action Date");
	AssignHistoryLog.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter,#rspan");
	// AssignHistoryLog.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	AssignHistoryLog.setInitWidths("60,180,100,150,150,150,250,150,200");
	AssignHistoryLog.setColAlign("center,center,center,center,center,center,left,center,center");
	AssignHistoryLog.setColSorting("int,str,str,str,str,str,str,str,str");
	AssignHistoryLog.setColTypes("ro,ro,ro,ro,ro,ro,ro,ro,ro");
	AssignHistoryLog.enableSmartRendering(true, 150);
	// AssignHistoryLog.setNumberFormat("0,000", 7, ".", ",");
	AssignHistoryLog.setSkin("dhx_skyblue");
	AssignHistoryLog.init();
	refreshdataLog();

	function refreshdataLog() {
		AssignHistoryLog.clearAndLoad('xml-partner-assign-history-log?ID={{ $_GET["ID"] }}');

	}
</script>