<?php if ((auth()->user()->Priv_ID ?? '') === 'MITRA') { abort(403); } ?>
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;


?>

@extends('layout.mainlayout-mitra')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Partner</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Opportunity</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">Assignment</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="AssignHistory" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div>
        <input type="button" id="ekselAssignHistoryDet" name="ekselAssignHistoryDet" class="btn btn-warning mt-2 mr-2" value="Export Excel" onclick="AssignHistory.toExcel('./export/generate.php');" />
		<input type="button" id="detailassign" name="detailassign" value="Detail Assignment Leads" class="btn btn-info mt-2 mr-2" onclick="detailassign()" />
		<input type="button" id="detailhistory" name="detailhistory" value="Detail History Log" class="btn btn-info mt-2 mr-2" onclick="detailhistory()" />
	</div>
	<div class="mt-3" id="divlistLog"></div>
	<div class="mt-3" id="divlistAssign"></div>
</div>
<script>
	function detailhistory() {

		AssignHistoryID = AssignHistory.getSelectedId();
		$("#divlistLog").show();
		if (AssignHistoryID != null) {
			$("#divlistAssign").hide();
			LoadM('divlistLog', 'partner-assign-history-log?ID=' + AssignHistory.cells(AssignHistoryID, 1).getValue());
		}
		return true;
	}
	function AssignHistoryKlik() {

		AssignHistoryID = AssignHistory.getSelectedId();
		$("#divlistAssign").show();
		if (AssignHistoryID != null) {
			$("#divlistLog").hide();
			LoadM('divlistAssign', 'partner-assign-history-detail?ID=' + AssignHistory.cells(AssignHistoryID, 1).getValue());
		}
		return true;
	}
	AssignHistory = new dhtmlXGridObject('AssignHistory');
	AssignHistory.setImagePath("./dhtmlx/imgs/");
	AssignHistory.setHeader("No, Assignment ID, Status Assignment,Sales,Propose Date, Proposed By");
	AssignHistory.attachHeader("#rspan,#text_filter,#text_filter,#text_filter,#text_filter,#text_filter");
	// AssignHistory.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#cspan,#cspan,#stat_total");
	AssignHistory.setInitWidths("60,180,100,150,150,150");
	AssignHistory.setColAlign("center,center,center,center,center,center");
	AssignHistory.setColSorting("int,str,str,str,str,str");
	AssignHistory.setColTypes("ro,ro,ro,ro,ro,ro");
	AssignHistory.enableSmartRendering(true, 150);
	// AssignHistory.setNumberFormat("0,000", 7, ".", ",");
	AssignHistory.attachEvent("onRowDblClicked", AssignHistoryKlik);
	AssignHistory.setSkin("dhx_skyblue");
	AssignHistory.init();
	refreshdata();

	function refreshdata() {
		AssignHistory.clearAndLoad('xml-partner-assign-history');

	}

	function detailassign() {
		AssignHistoryKlik();
	}
</script>
@endsection