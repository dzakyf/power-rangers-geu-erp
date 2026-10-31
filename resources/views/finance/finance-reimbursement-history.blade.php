<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

$LSP = Auth::user()->Location;
$Saya =  Auth::user()->username;



?>

@extends('layout.mainlayout-finance')

@section('content-wrapper')
@csrf
<!-- Main Content Header -->
<div class="main-content-header">
	<h1>Finance</h1>
	<ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="#">Reimbursement</a>
		</li>
		<li class="breadcrumb-item active">
			<span class="active">History</span>
		</li>
	</ol>
</div>

<div class="row">
	<div id="ProjectReimbursementHistory" style="width:auto;height:320px; background-color:white;overflow:hidden"></div>
	<div id="divlistProjectReimbursement"></div>

</div>
<script>
	function ProjectReimbursementHistoryDoubleClick() {

		ProjectReimbursementHistoryID = ProjectReimbursementHistory.getSelectedId();
		tipe = ProjectReimbursementHistory.cells(ProjectReimbursementHistory.getSelectedId(), 1).getValue();
		tgl = ProjectReimbursementHistory.cells(ProjectReimbursementHistory.getSelectedId(), 8).getValue();

		LoadM('divlistProjectReimbursement', 'finance-reimbursement-history-detail?ID=' + ProjectReimbursementHistoryID + "&Task_ID=" + tipe);

		return true;
	}

	ProjectReimbursementHistory = new dhtmlXGridObject('ProjectReimbursementHistory');
	ProjectReimbursementHistory.setImagePath("./dhtmlx/imgs/");
	ProjectReimbursementHistory.setHeader("No.,Task No,Reimbursement Type,Description,User Request,Amount,Paid,Remain,Days Open,Date Created,Payment Type,Status,Created By,Department,Reference");
	ProjectReimbursementHistory.attachHeader("&nbsp;,#text_filter,#text_filter,#text_filter,#text_filter,#numeric_filter,#numeric_filter,#numeric_filter,#numeric_filter,#rspan,#text_filter,#combo_filter,#combo_filter,#combo_filter,#combo_filter");
	ProjectReimbursementHistory.attachFooter("Total,#cspan,#cspan,#cspan,#cspan,#stat_total,#stat_total,#stat_total,,,,,,,");
	ProjectReimbursementHistory.setInitWidths("70,160,150,150,100,100,100,100,100,100,100,100,100,80,100");
	ProjectReimbursementHistory.setColAlign("center,center,left,left,center,right,right,right,center,center,center,center,center,center,center");
	ProjectReimbursementHistory.setColTypes("ro,ro,ro,ro,ro,ron,ron,ron,ro,ro,ro,ro,ro,ro,ro");
	ProjectReimbursementHistory.setColSorting("int,str,str,str,str,str,str,str,str,str,str,str");
	ProjectReimbursementHistory.setEditable(false);
	ProjectReimbursementHistory.attachEvent("onRowDblClicked", ProjectReimbursementHistoryDoubleClick);
	ProjectReimbursementHistory.setNumberFormat("0,000", 5, ".", ",");
	ProjectReimbursementHistory.setNumberFormat("0,000", 6, ".", ",");
	ProjectReimbursementHistory.setNumberFormat("0,000", 7, ".", ",");
	ProjectReimbursementHistory.setSkin("dhx_skyblue");
	ProjectReimbursementHistory.setSkin("dhx_skyblue");
	ProjectReimbursementHistory.init();
	refreshdata();

	function refreshdata() {
		ProjectReimbursementHistory.clearAndLoad("xml-finance-reimbursement-history");

	}
</script>
@endsection